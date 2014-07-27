<?php
include_once('../sandbox.php');
Sandbox::Init();
SandboxFunctionsString::Init();
SandboxFunctionsFileSystem::Init();

echo SandboxFunctions::Instance()->crypt('Hello');
