# How-To's with Sandbox

## Call a Sandbox functions directly

```php
// Include Sandbox
include_once('../sandbox.php');
// Initialize Sandbox
Sandbox::Init();
// Register String functions
SandboxFunctionsString::Init();
// Call sha1 function via Sandbox
echo SandboxFunctions::Instance()->sha1('Hello');
```
This is quite alot more code than just
```php
echo crypt('Hello');
```
But this is more to show how the Sandbox works.

## Calling a registered Sandbox function directly

```php
// Include Sandbox
include_once('../sandbox.php');
// Initialize Sandbox
Sandbox::Init();
// Register a sha1 function
SandboxFunctions::AddFunction('sha1', function($data) {
  return sha1($data);
});
// Call sha1 function via Sandbox
echo SandboxFunctions::Instance()->sha1('Hello');
```

This is more or less the same code. But the big difference is that the String "library" in Sandbox isn't loaded. Instead the sha1 function is registered manually. In this case it's registered as an anonymous function.

## Load and run a script
```php
// Include Sandbox
include_once('../sandbox.php');
// Initialize Sandbox
Sandbox::Init();
// Compile and run
echo Sandbox::Compile(file_get_contents('./script.php'), true);
```

## Load and run a script with cache
```php
// Include Sandbox
include_once('../sandbox.php');
// Define in which folder the cache should be
define('SANDBOX_CACHE_PATH', __DIR__.'/.cache/');
// Initialize Sandbox
Sandbox::Init();
// Compile and run
echo Sandbox::Compile(file_get_contents('./script.php'), true);
```

## Load, compile and save a script with cache
```php
// Include Sandbox
include_once('../sandbox.php');
// Define in which folder the cache should be
define('SANDBOX_CACHE_PATH', __DIR__.'/.cache/');
// Initialize Sandbox
Sandbox::Init();
// Compile
$code = Sandbox::Compile(file_get_contents('./script.php'), false);
// Save the compiled code
file_put_contents('./script.compiled.inc', $code);
```

## Run a script that has been compiled earlier
```php
// Include Sandbox
include_once('../sandbox.php');
// Initialize Sandbox
Sandbox::Init();
// Load and run
Sandbox::Run(file_get_contents('./script.compiled.inc'));
```

