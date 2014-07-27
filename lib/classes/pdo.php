<?php
class SandboxClassesPDO extends SandboxClass {
  public static function Init() {
    parent::Init();
    $class = get_called_class();
    new $class('PDO', true);
    new $class('PDOStatement', true);
    new $class('PDOException', true);
  }
}

?>