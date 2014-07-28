<?php
$case = \PDO::ATTR_CASE($arg);
$case = \PDO::ATTR_CASE; // <-- Currently this fails
echo serialize($case);
