<?php
include_once('../sandbox.php');
define('SANDBOX_CACHE_PATH', __DIR__.'/.cache/');
Sandbox::Init();
echo Sandbox::Compile(file_get_contents('./example3.inc'), false, 10);
?>