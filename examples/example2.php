<?php
include_once('../sandbox.php');
Sandbox::Init();

SandboxClassesPDO::Init();

SandboxClasses::Instance()->PDO('sqlite::memory:');

$code = file_get_contents('./example2.inc');
$compiled = Sandbox::Compile($code);

SandboxSuper::Init();
SandboxSuper::$GLOBALS['palle'] = 'kuling';

define('SANDBOX_ROOT_PATH', __DIR__.'/data/');
define('SANDBOX_STREAM_BLOCKSIZE', 2049);
SandboxFunctionsFileSystem::Init();
SandboxCall::AddEvent('filename', function($filename) {
  echo PHP_EOL."Someone is trying to load ".$filename.PHP_EOL;
  return $filename;
});

echo 'Code:'.PHP_EOL;
echo $compiled;
echo PHP_EOL;
echo PHP_EOL;

echo 'Run:'.PHP_EOL;
Sandbox::Run($compiled);
echo PHP_EOL;
echo PHP_EOL;

echo 'GLOBALS:'.PHP_EOL;
print_r($GLOBALS['sandboxdata']);
echo PHP_EOL;

