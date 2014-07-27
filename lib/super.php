<?php
class SandboxSuper implements ArrayAccess, Countable, Iterator, Serializable {
  public static $GLOBALS = array();
  public static $GET = array();
  public static $POST = array();
  public static $REQUEST = array();
  public static $SERVER = array();
  public static $SESSION = array();
  public static $COOKIE = array();
  
  protected $globalVar;
  protected $readOnly;
  protected $keys;
  protected $offset;
  
  public function __construct($globalVar, $readOnly = true, $offset = 'sandboxdata') {
    $globalVar = strtolower($globalVar);
    switch($globalVar) {
      case 'globals':
      case 'get':
      case 'post':
      case 'request':
      case 'server':
      case 'session':
      case 'cookie':
        $this->globalVar = $globalVar;
        break;
      default:
        $this->globalVar = false;
    }
    $this->offset = $offset;
    $this->updateKeys();
    $this->readOnly = $readOnly;
  }
  
  public function __get($name) {
    return $this->offsetGet($name);
  }

  public function __set($name, $value) {
    $this->offsetSet($name, $value);
  }
  
  public static function Init() {
    $class = get_called_class();
    static::$GLOBALS = new $class('GLOBALS', false);
    static::$GET = new $class('GET', true);
    static::$POST = new $class('POST', true);
    static::$REQUEST = new $class('REQUEST', true);
    static::$SERVER = new $class('SERVER', true);
    static::$SESSION = new $class('SESSION', true);
    static::$COOKIE = new $class('COOKIE', true);
  }
  
  public function updateKeys() {
    switch($this->globalVar) {
      case 'globals':
        if(!isset($GLOBALS[$this->offset])) {
          $GLOBALS[$this->offset] = array();
        } elseif(!is_array($GLOBALS[$this->offset])) {
          $GLOBALS[$this->offset] = array($GLOBALS[$this->offset]);
        }
        $this->keys = array_keys($GLOBALS[$this->offset]);
        break;
      case 'get':
        $this->keys = array_keys($_GET);
        break;
      case 'post':
        $this->keys = array_keys($_POST);
        break;
      case 'request':
        $this->keys = array_keys($_REQUEST);
        break;
      case 'server':
        $this->keys = array_keys($_SERVER);
        break;
      case 'session':
        if(session_id() != '') {
          $this->keys = array_keys($_SESSION);
        }
        break;
      case 'cookie':
        $this->keys = array_keys($_COOKIE);
        break;
    }
    if(!is_array($this->keys)) {
      $this->keys = array();
    }
    return $this->keys;
  }
  
  public function count() {
    return count($this->keys);
  }

  public function current() {
    $key = $this->key();
    return $this->offsetGet($key);
  }

  public function key() {
    if(key($this->keys) === null) {
      return null;
    }
    return current($this->keys);
  }

  public function next() {
    $key = next($this->keys);
    return $this->offsetGet($key);
  }

  public function offsetExists($offset) {
    return in_array($offset, $this->keys);
  }

  public function offsetGet($offset) {
    if(in_array($offset, $this->keys)) {
      switch($this->globalVar) {
        case 'globals':
          return $GLOBALS[$this->offset][$offset];
        case 'get':
          return $_GET[$offset];
        case 'post':
          return $_POST[$offset];
        case 'request':
          return $_REQUEST[$offset];
        case 'server':
          return $_SERVER[$offset];
        case 'session':
          return $_SESSION[$offset];
        case 'cookie':
          return $_COOKIE[$offset];
      }
    }
    return null;
  }

  public function offsetSet($offset, $value) {
    if($this->readOnly === false) {
      if($offset === null) {
        switch($this->globalVar) {
          case 'globals':
            $GLOBALS[$this->offset][] = $value;
            break;
          case 'get':
            $_GET[] = $value;
            break;
          case 'post':
            $_POST[] = $value;
            break;
          case 'request':
            $_REQUEST[] = $value;
            break;
          case 'server':
            $_SERVER[] = $value;
            break;
          case 'session':
            $_SESSION[] = $value;
            break;
          case 'cookie':
            $_COOKIE[] = $value;
            break;
        }
      } else {
        switch($this->globalVar) {
          case 'globals':
            $GLOBALS[$this->offset][$offset] = $value;
            break;
          case 'get':
            $_GET[$offset] = $value;
            break;
          case 'post':
            $_POST[$offset] = $value;
            break;
          case 'request':
            $_REQUEST[$offset] = $value;
            break;
          case 'server':
            $_SERVER[$offset] = $value;
            break;
          case 'session':
            $_SESSION[$offset] = $value;
            break;
          case 'cookie':
            $_COOKIE[$offset] = $value;
            break;
        }
      }
      $this->updateKeys();
    }
  }

  public function offsetUnset($offset) {
    if($this->readOnly === false) {
      if(in_array($offset, $this->keys)) {
        switch($this->globalVar) {
          case 'globals':
            unset($GLOBALS[$this->offset][$offset]);
            break;
          case 'get':
            unset($_GET[$offset]);
            break;
          case 'post':
            unset($_POST[$offset]);
            break;
          case 'request':
            unset($_REQUEST[$offset]);
            break;
          case 'server':
            unset($_SERVER[$offset]);
            break;
          case 'session':
            unset($_SESSION[$offset]);
            break;
          case 'cookie':
            unset($_COOKIE[$offset]);
            break;
        }
        $this->updateKeys();
      }
    }
  }

  public function rewind() {
    $key = reset($this->keys);
    return $this->offsetGet($key);
  }

  public function serialize() {
    $data = array();
    $data['readonly'] = $this->readOnly;
    switch($this->globalVar) {
      case 'globals':
        $data['super'] = array_merge($GLOBALS[$this->offset][$offset]);
        break;
      case 'get':
        $data['super'] = array_merge($_GET[$offset]);
        break;
      case 'post':
        $data['super'] = array_merge($_POST[$offset]);
        break;
      case 'request':
        $data['super'] = array_merge($_REQUEST[$offset]);
        break;
      case 'server':
        $data['super'] = array_merge($_SERVER[$offset]);
        break;
      case 'session':
        $data['super'] = array_merge($_SESSION[$offset]);
        break;
      case 'cookie':
        $data['super'] = array_merge($_COOKIE[$offset]);
        break;
    }
    return serialize($data);
  }

  public function unserialize($serialized) {
    
  }

  public function valid() {
    return ($this->key() !== null);
  }

}
?>