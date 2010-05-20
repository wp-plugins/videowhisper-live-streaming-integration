<?php
$username=$_POST['u'];
$session=$_POST['s'];
$room=$_POST['r'];
$message=$_POST['msg'];
$time=$_POST['msgtime'];

//do not allow uploads to other folders
if ( strstr($room,"/") || strstr($room,"..") ) exit;

$dir="uploads";
if (!file_exists($dir)) mkdir($dir);
$dir.="/$room";
if (!file_exists($dir)) mkdir($dir);

$day=date("y-M-j",time());

$dfile = fopen("uploads/$room/Log$day.html","a");
fputs($dfile,$message."<BR>");
fclose($dfile);
?>loadstatus=1