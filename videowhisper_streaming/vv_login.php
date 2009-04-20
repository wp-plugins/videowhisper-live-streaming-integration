<?
include("inc.php");
$username="VV".base_convert((time()-1224350000).rand(0,10),10,36);
?>server=<?=$rtmp_server?>&welcome=Welcome!&username=<?=$username?>&msg=&visitor=1&loggedin=1&loadstatus=1