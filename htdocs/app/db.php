<?php

$db_user='aagusti';
$db_pass='a';
$db_name='pbb_tangsel';
$db_port='5432';
$db_type='pgsql';
$db_host='192.168.56.1';

try{
    $db = new PDO("$db_type:host=$db_host;dbname=$db_name", $db_user, $db_pass);
}catch(PDOException $e){
    echo "Error ", $e->getmessage(),'<br>';
}
?>