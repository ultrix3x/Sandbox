[ ] PDO attributes
How to solve this:
$pdo = new PDO($dsn);
Becomes
$pdo = SandboxClasses::Instance()->PDO($dsn);
But how to handle
$case = PDO::ATTR_CASE;
