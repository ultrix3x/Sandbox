<?php
class SandboxClasses {
  protected static $instance = null;
  protected static $classes = array();
  
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
    if(isset(static::$classes[$name])) {
      $class = static::$classes[$name];
      return $class->DynamicInstance($arguments);
    }
    return null;
  }
  
  public static function __callStatic($name, $arguments) {
    if(isset(static::$classes[$name])) {
      $class = static::$classes[$name];
      return $class->StaticReference($arguments);
    }
    return null;
  }
  
  public static function StaticCall($class, $method) {
    $args = func_get_args();
    array_shift($args);
    array_shift($args);
    print_r($args);
    return '*';
  }
  
  public static function StaticConst($class, $const) {
    $args = func_get_args();
    array_shift($args);
    array_shift($args);
    print_r($args);
    return '#';
  }
  
  public function AddClass($className, $callable) {
    static::$classes[$className] = $callable;
  }
  
  public function ReplaceClass($className, $callable) {
    static::$classes[$className] = $callable;
  }
  
  public function RemoveClass($className) {
    if(isset(static::$classes[$className])) {
      unset(static::$classes[$className]);
    }
  }
  
}

class SandboxClass {
  protected $className;
  protected static $classList = array();
  protected static $allowDirectAdd = false;
  protected static $allowPassthru = true;
  
  public function __construct($className, $passthru = false) {
    $this->className = false;
    if(isset(static::$classList[$className])) {
      $this->className = $className;
    } elseif($passthru && static::$allowPassthru) {
      $this->className = $className;
      static::$classList[$className] = array('class'=>$className);
    }
    if($this->className !== false) {
      SandboxClasses::Instance()->AddClass($className, $this);
    }
  }
  
  public function DynamicInstance($args) {
    $class = $this->className;
    if($class === false) {
      return null;
    }
    if(!isset(static::$classList[$class])) {
      return null;
    }
    $classInfo = static::$classList[$class];
    $className = $classInfo['class'];
    switch(count($args)) {
      case 0:
        return new $className();
      case 1:
        return new $className($args[0]);
      case 2:
        return new $className($args[0], $args[1]);
      case 3:
        return new $className($args[0], $args[1], $args[2]);
      case 4:
        return new $className($args[0], $args[1], $args[2], $args[3]);
      case 5:
        return new $className($args[0], $args[1], $args[2], $args[3], $args[4]);
      case 6:
        return new $className($args[0], $args[1], $args[2], $args[3], $args[4], $args[5]);
      case 7:
        return new $className($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6]);
    }
    $cmd = '$obj = new '.$className.'(';
    $first = true;
    foreach($args as $index => $arg) {
      if($first === true) {
        $first = false;
      } else {
        $cmd .= ',';
      }
      $cmd .= '$args['.$index.']';
    }
    $cmd .= ');';
    eval($cmd);
    return $obj;
  }

  public function StaticReference($args) {
    $class = $this->className;
    if($class === false) {
      return null;
    }
    if(!isset(static::$classList[$class])) {
      return null;
    }
    $classInfo = static::$classList[$class];
    $className = $classInfo['class'];
    return $className;
  }
    
  public static function Init() {
  }
  
}


?>