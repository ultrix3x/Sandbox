- [ ] PDO attributes
  How to solve this:
```php
$pdo = new PDO($dsn);
```
  Becomes
```php
$pdo = SandboxClasses::Instance()->PDO($dsn);
```
  But how to handle
```php
$case = PDO::ATTR_CASE;
```
