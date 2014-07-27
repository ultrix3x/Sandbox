<?php
class SandboxFunctionsStandard extends SandboxCall {
  public static function Init() {
    $class = get_called_class();
//    new $class('substr', true);
  }
  
}
?>