<?php
include_once('../sandbox.php');
Sandbox::Init();
SandboxFunctionsString::Init();

echo SandboxFunctions::Instance()->sha1('Hello');
echo PHP_EOL;