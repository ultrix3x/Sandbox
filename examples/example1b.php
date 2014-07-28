<?php
include_once('../sandbox.php');
Sandbox::Init();
SandboxFunctions::AddFunction('sha1', function($data) {
  return sha1($data);
});

echo SandboxFunctions::Instance()->sha1('Hello');
echo PHP_EOL;