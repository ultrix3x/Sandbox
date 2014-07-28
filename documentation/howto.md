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
include_once('../sandbox.php');
Sandbox::Init();
SandboxFunctions::AddFunction('sha1', function($data) {
  return sha1($data);
});

echo SandboxFunctions::Instance()->sha1('Hello');
```

This is more or less the same code. But the big difference is that the String "library" in Sandbox isn't loaded. Instead the sha1 function is registered manually. In this case it's registered as an anonymous function.

