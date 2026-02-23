<?php
echo "Hash email address<br>";
$input = "barrygpyle@gmail.com";
$md5Hash = md5($input);
echo $md5Hash;