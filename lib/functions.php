<?php
class SandboxFunctions {
  protected static $instance = null;
  protected static $functions = array();
  
  public function __construct() {
    static::$instance = $this;
  }
  
  public static function Instance() {
    if(static::$instance === null) {
      $class = get_called_class();
      new $class();
    }
    return static::$instance;
  }
  
  public function __call($name, $arguments) {
    if(isset(static::$functions[$name])) {
      return call_user_func_array(static::$functions[$name], $arguments);
    }
    return null;
  }
  
  public static function __callStatic($name, $arguments) {
    if(isset(static::$functions[$name])) {
      return call_user_func_array(static::$functions[$name], $arguments);
    }
    return null;
  }
  
  public function AddFunction($functionName, $callable) {
    static::$functions[$functionName] = $callable;
  }
  
  public function ReplaceFunction($functionName, $callable) {
    static::$functions[$functionName] = $callable;
  }
  
  public function RemoveFunction($functionName) {
    if(isset(static::$functions[$functionName])) {
      unset(static::$functions[$functionName]);
    }
  }
  
}

class SandboxCall {
  protected static $events = array();
  protected $callable;
  protected $functionName;
  protected static $functionList = array();
  protected static $allowDirectAdd = false;
  protected static $allowPassthru = true;
    
  function __construct($functionName, $passthru = false) {
    if(is_callable(array($this, 'wrap_'.$functionName))) {
      $this->callable = array($this, 'wrap_'.$functionName);
      $this->functionName = $functionName;
    } elseif(isset(static::$functionList[$functionName])) {
      $this->callable = static::$functionList[$functionName];
      $this->functionName = $functionName;
    } elseif($passthru && static::$allowPassthru) {
      $this->callable = $functionName;
      $this->functionName = $functionName;
    } else {
      $this->callable = null;
      $this->functionName = '';
    }
    if($this->functionName != '') {
      SandboxFunctions::Instance()->AddFunction($functionName, $this);
    }
  }
  
  function __invoke() {
    if($this->callable !== null) {
      $args = func_get_args();
      return call_user_func_array($this->callable, $args);
    }
    return null;
  }

  public function isValid() {
    return ($this->callable !== null);
  }

  public static function Init() {
    
  }
  
  public static function AddEvent($event, $callback) {
    if(!isset(static::$events[$event])) {
      static::$events[$event] = array();
    }
    if(is_callable($callback)) {
      static::$events[$event][] = $callback;
    }
  }
}
?>