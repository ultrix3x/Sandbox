<?php
include_once('../sandbox.php');
define('SANDBOX_CACHE_PATH', __DIR__.'/.cache/');
Sandbox::Init();
echo Sandbox::Compile(file_get_contents('./example4.inc'), false, 10);
echo PHP_EOL;