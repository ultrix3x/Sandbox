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
          echo "CAHCE";
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
    $tokens = token_get_all($code);
    $lastIndex = false;
    $lastToken = null;
    foreach($tokens as $index => $token) {
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
                $tokens[$index][1] = 'SandboxSuper::$GLOBALS';
                $includeSandboxSuper = true;
                break;
              case '$_GET':
                $tokens[$index][1] = 'SandboxSuper::$GET';
                $includeSandboxSuper = true;
                break;
              case '$_POST':
                $tokens[$index][1] = 'SandboxSuper::$POST';
                $includeSandboxSuper = true;
                break;
              case '$_REQUEST':
                $tokens[$index][1] = 'SandboxSuper::$REQUEST';
                $includeSandboxSuper = true;
                break;
              case '$_SESSION':
                $tokens[$index][1] = 'SandboxSuper::$SESSION';
                $includeSandboxSuper = true;
                break;
              case '$_SERVER':
                $tokens[$index][1] = 'SandboxSuper::$SERVER';
                $includeSandboxSuper = true;
                break;
              case '$_COOKIE':
                $tokens[$index][1] = 'SandboxSuper::$COOKIE';
                $includeSandboxSuper = true;
                break;
            }
            break;
          case T_STRING:
            if(($nextToken !== null) && (!is_array($nextToken)) && ($nextToken == '(')) {
              if(!is_array($lastToken) || (is_array($lastToken) && !in_array($lastToken[0], array(T_OBJECT_OPERATOR, T_DOUBLE_COLON)))) {
                $tokens[$index][1] = 'SandboxFunctions::Instance()->'.$token[1];
              }
            }
            break;
          case T_INCLUDE:
            $tokens[$index][1] = 'SandboxFunctions::Instance()->doinclude';
            break;
          case T_INCLUDE_ONCE:
            $tokens[$index][1] = 'SandboxFunctions::Instance()->doincludeonce';
            break;
          case T_REQUIRE:
            $tokens[$index][1] = 'SandboxFunctions::Instance()->dorequire';
            break;
          case T_REQUIRE_ONCE:
            $tokens[$index][1] = 'SandboxFunctions::Instance()->dorequireonce';
            break;
          case T_WHITESPACE:
            break;
          case T_OPEN_TAG_WITH_ECHO:
            break;
          case T_OPEN_TAG:
            break;
          case T_CLOSE_TAG:
            break;
          case T_CONSTANT_ENCAPSED_STRING:
            break;
          default:
            $token[] = token_name($token[0]);
            print_r($token);
            break;
        }
      } else {
      }
      
      $lastIndex = $index;
      $lastToken = $token;
    }
    $compiled = '';
    foreach($tokens as $token) {
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
    class_exists('SandboxStream');
    $key = sha1(microtime(true));
    file_put_contents('sandbox://'.$key, $code);
    return include('sandbox://'.$key);
  }
  
}


?>