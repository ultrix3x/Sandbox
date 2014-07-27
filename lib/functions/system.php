<?php
class SandboxFunctionsSsystem extends SandboxCall {
  protected $fileList;
  
  public function __construct() {
    $this->fileList = array();
  }
  
  public static function Init() {
    $class = get_called_class();
    new $class('doinclude', true);
  }
  
  public function wrap_doinclude($filename) {
    
  }
  
  public function wrap_doincludeonce($filename) {
    
  }
  
  public function wrap_dorequire($filename) {
    
  }
  
  public function wrap_dorequireonce($filename) {
    
  }
  
}
?>