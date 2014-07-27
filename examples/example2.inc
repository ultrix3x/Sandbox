<?php
$fp = fopen('file:///test/../example2.txt', 'r');
$GLOBALS['datafile'] = fread($fp, 1024);
fclose($fp);
echo $GLOBALS['datafile'];
