# Sandbox

## Functions

### public static function Init()

### public static function Autoload($class)

  public static function Instance() {
    if(static::$instance === null) {
      $class = get_called_class();
      new $class;
    }
    return static::$instance;
  }
  
### protected static function LoadFromCache($cacheKey, $expire)

### protected static function SaveToCache($cacheKey, $code)

### public static function Compile($code, $run = false, $expire = 3600)

### public static function Run($code)
