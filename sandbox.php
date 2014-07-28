<?php
class Sandbox {
  protected static $instance = null;
  
  public function __construct() {
    if(static::$instance !== null) {
      throw new Exception('Singleton error: Object already created');
    }
    static::$instance = $this;
  }
  
  public static function Init() {
    $class = get_called_class();
    spl_autoload_register($class.'::Autoload');
  }
  
  public static function Autoload($class) {
    $filename = false;
    switch($class) {
      case 'SandboxStream':
        $filename = __DIR__.'/lib/stream.php';
        break;
      case 'SandboxSuper':
        $filename = __DIR__.'/lib/super.php';
        break;
      case 'SandboxFunctions':
      case 'SandboxCall':
        $filename = __DIR__.'/lib/functions.php';
        break;
      case 'SandboxClasses':
      case 'SandboxClass':
        $filename = __DIR__.'/lib/classes.php';
        break;
      default:
        if(substr($class, 0, 16) == 'SandboxFunctions') {
          $filename = __DIR__.'/lib/functions/'.strtolower(substr($class, 16)).'.php';
        } elseif(substr($class, 0, 14) == 'SandboxClasses') {
          $filename = __DIR__.'/lib/classes/'.strtolower(substr($class, 14)).'.php';
        }
        break;
    }
    if(($filename !== false) && file_exists($filename)) {
      include_once($filename);
    }
  }
  
  public static function Instance() {
    if(static::$instance === null) {
      $class = get_called_class();
      new $class;
    }
    return static::$instance;
  }
  
  protected static function LoadFromCache($cacheKey, $expire) {
    if(defined('SANDBOX_CACHE_PATH')) {
      $filename = SANDBOX_CACHE_PATH.$cacheKey;
      if(file_exists($filename)) {
        $data = unserialize(file_get_contents($filename));
        if(is_array($data) && isset($data['code']) && isset($data['timestamp']) && (($data['timestamp'] + $expire) >= time())) {
          echo "CACHE";
          return $data['code'];
        }
      }
    }
    return false;
  }
  
  protected static function SaveToCache($cacheKey, $code) {
    if(defined('SANDBOX_CACHE_PATH')) {
      $filename = SANDBOX_CACHE_PATH.$cacheKey;
      $data = array();
      $data['code'] = $code;
      $data['timestamp'] = time();
      file_put_contents($filename, serialize($data));
    }
  }
  
  public static function Compile($code, $run = false, $expire = 3600) {
    $cacheKey = sha1($code);
    $cacheData = static::LoadFromCache($cacheKey, $expire);
    if($cacheData !== false) {
      if($run) {
        return static::Run($cacheData);
      }
      return $cacheData;
    }
    $includeSandboxSuper = false;
    $rawTokens = token_get_all($code);
    $tokens = array();
    $newTokens = array();
    /***************
     Remove unneccesary whitespace
     Remove comments
     Merge namespaces
    ***************/
    $skipNext = 0;
    foreach($rawTokens as $idx => $token) {
      if($skipNext > 0) {
        $skipNext--;
        continue;
      }
      if(is_array($token)) {
        switch($token[0]) {
          case T_NS_SEPARATOR:
            $lastToken = $rawTokens[($idx - 1)];
            $nextToken = $rawTokens[($idx + 1)];
            if(is_array($lastToken) && ($lastToken[0] == T_STRING)) {
              $token[0] = T_STRING;
              $token[1] = $lastToken[1].$token[1];
              $token[2] = $lastToken[2];
              array_pop($tokens);
            }
            if(is_array($nextToken) && ($nextToken[0] == T_STRING)) {
              $token[0] = T_STRING;
              $token[1] .= $nextToken[1];
              $skipNext = 1;
            }
            $tokens[] = $token;
            break;
          case T_WHITESPACE:
            // Skip superfluous whitespaces
            if(isset($rawTokens[($idx - 1)])) {
              $lastToken = $rawTokens[($idx - 1)];
            } else {
              $lastToken = null;
            }
            if(isset($rawTokens[($idx + 1)])) {
              $nextToken = $rawTokens[($idx + 1)];
            } else {
              $nextToken = null;
            }
            $ws = $token[1];
            if(!is_array($nextToken) && in_array($nextToken, array('=', '(', ')'))) {
              $ws = false;
            } elseif(!is_array($lastToken) && in_array($lastToken, array('=', ';', '(', ')'))) {
              $ws = false;
            } elseif(preg_match('/[\n\r]/Us', $ws)) {
              $ws = PHP_EOL;
            } elseif(preg_match('/[\s]/Us', $ws)) {
              $ws = ' ';
            }
            if($ws !== false) {
              $token[1] = $ws;
              $tokens[] = $token;
            }
            break;
          case T_COMMENT:
            // This code will be dropped
            break;
          default:
            $tokens[] = $token;
        }
      } else {
        $tokens[] = $token;
      }
    }
    $lastIndex = false;
    $lastToken = null;
    $skipNext = 0;
    foreach($tokens as $index => $token) {
      if($skipNext > 0) {
        $skipNext--;
        continue;
      }
      $nextIndex = ($index + 1);
      if(isset($tokens[$nextIndex])) {
        $nextToken = $tokens[$nextIndex];
      } else {
        $nextIndex = false;
        $nextToken = null;
      }

      if(is_array($token)) {
        switch($token[0]) {
          case T_VARIABLE:
            switch($token[1]) {
              case '$GLOBALS':
                $token[1] = 'SandboxSuper::$GLOBALS';
                $newTokens[] = $token;
                $includeSandboxSuper = true;
                break;
              case '$_GET':
                $token[1] = 'SandboxSuper::$GET';
                $newTokens[] = $token;
                $includeSandboxSuper = true;
                break;
              case '$_POST':
                $token[1] = 'SandboxSuper::$POST';
                $newTokens[] = $token;
                $includeSandboxSuper = true;
                break;
              case '$_REQUEST':
                $token[1] = 'SandboxSuper::$REQUEST';
                $newTokens[] = $token;
                $includeSandboxSuper = true;
                break;
              case '$_SESSION':
                $token[1] = 'SandboxSuper::$SESSION';
                $newTokens[] = $token;
                $includeSandboxSuper = true;
                break;
              case '$_SERVER':
                $token[1] = 'SandboxSuper::$SERVER';
                $newTokens[] = $token;
                $includeSandboxSuper = true;
                break;
              case '$_COOKIE':
                $token[1] = 'SandboxSuper::$COOKIE';
                $newTokens[] = $token;
                $includeSandboxSuper = true;
                break;
              default:
                $newTokens[] = $token;
                break;
            }
            break;
          case T_DOUBLE_COLON:
            if(isset($tokens[($nextIndex + 1)])) {
              $nextNextToken = $tokens[($nextIndex + 1)];
              if(is_array($nextNextToken)) {
                die(print_r($nextNextToken, true));
              } elseif($nextNextToken == '(') {
                array_pop($newTokens);
                $callToken = array();
                $callToken[0] = T_STRING;
                $callToken[1] = 'SandboxClasses::StaticCall(\''.$lastToken[1].'\',\''.$nextToken[1].'\'';
                $callToken[2] = 2;
                $skipNext += 2;
                if(is_array($tokens[($nextIndex + 2)]) || ($tokens[($nextIndex + 2)] != ')')) {
                  $callToken[1] .= ',';
                }
                $newTokens[] = $callToken;
              } else {
                array_pop($newTokens);
                $constToken = array();
                $constToken[0] = T_STRING;
                $constToken[1] = 'SandboxClasses::StaticConst(\''.$lastToken[1].'\',\''.$nextToken[1].'\')';
                $constToken[2] = 2;
                $skipNext += 1;
                $newTokens[] = $constToken;
              }
            }
//            $newTokens[] = $token;
            break;
          case T_STRING:
            if($nextToken !== null) {
              if(!is_array($nextToken)) {
                if($nextToken == '(') {
                  if(!is_array($lastToken) || (is_array($lastToken) && !in_array($lastToken[0], array(T_OBJECT_OPERATOR, T_DOUBLE_COLON)))) {
                    $token[1] = 'SandboxFunctions::Instance()->'.$token[1];
                    $newTokens[] = $token;
                  } else {
                    $newTokens[] = $token;
                  }
                } else {
                  $newTokens[] = $token;
                }
              } else {
                $newTokens[] = $token;
              }
            } else {
              $newTokens[] = $token;
            }
            break;
          case T_INCLUDE:
            $token[1] = 'SandboxFunctions::Instance()->doinclude';
            $newTokens[] = $token;
            break;
          case T_INCLUDE_ONCE:
          case T_INCLUDE:
            $token[1] = 'SandboxFunctions::Instance()->doinclude';
            $newTokens[] = $token;
            break;
          case T_REQUIRE:
          case T_INCLUDE:
            $token[1] = 'SandboxFunctions::Instance()->doinclude';
            $newTokens[] = $token;
            break;
          case T_REQUIRE_ONCE:
          case T_INCLUDE:
            $token[1] = 'SandboxFunctions::Instance()->doinclude';
            $newTokens[] = $token;
            break;
          case T_WHITESPACE:
            $newTokens[] = $token;
            break;
          case T_INCLUDE:
            $newTokens[] = $token;
            break;
          case T_OPEN_TAG_WITH_ECHO:
          case T_ECHO:
            $newTokens[] = $token;
            break;
          case T_OPEN_TAG:
            $newTokens[] = $token;
            break;
          case T_CLOSE_TAG:
            $newTokens[] = $token;
            break;
          case T_CONSTANT_ENCAPSED_STRING:
            $newTokens[] = $token;
            break;
          default:
            $token[] = token_name($token[0]);
            $newTokens[] = $token;
//            print_r($token);
            break;
        }
      } else {
        $newTokens[] = $token;
      }
      
      $lastIndex = $index;
      $lastToken = $token;
    }
    $compiled = '';
    foreach($newTokens as $token) {
      if(is_array($token)) {
        $compiled .= $token[1];
      } else {
        $compiled .= $token;
      }
    }
    // Fix includes and requires without ( and )
    if(preg_match_all('/\-\>(do(include|require)(once)?)([^\;]+);/', $compiled, $matches, PREG_SET_ORDER)) {
      foreach($matches as $match) {
        $arg = trim($match[4]);
        if(substr($arg, 0, 1) != '(') {
          $arg = '('.$arg;
        }
        if(substr($arg, -1, 1) != ')') {
          $arg .= ')';
        }
        $replace = str_replace($match[4], $arg,$match[0]);
        $compiled = str_replace($match[0], $replace, $compiled);
      }
    }
    if($includeSandboxSuper) {
      $compiled = '<'.'?php '.PHP_EOL.'SandboxSuper::Init(); ?'.'>'.$compiled;
      $compiled = preg_replace('/\?\>\s*\<\?php/Us', '', $compiled);
    }
    static::SaveToCache($cacheKey, $compiled);
    if($run) {
      return static::Run($compiled);
    }
    return $compiled;
  }
  
  public static function Run($code) {
    $result = false;
    $oldErrorLevel = error_reporting(0);
    if(class_exists('SandboxStream') && in_array('sandbox', stream_get_wrappers())) {
      $key = sha1(microtime(true));
      file_put_contents('sandbox://'.$key, $code);
      $result = include('sandbox://'.$key);
    } else {
      $filename = tempnam(sys_get_temp_dir(), 'sandbox').'.php';
      file_put_contents($filename, $code);
      $result = include_once($filename);
      unlink($filename);
    }
    error_reporting($oldErrorLevel);
    return $result;
  }
  
}


?>