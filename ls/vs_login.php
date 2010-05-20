<?php
//vs_login.php controls watch interface (video & chat & user list) login
include("inc.php");
$username="VW".base_convert((time()-1224350000).rand(0,10),10,36);
$userType=0;
$visitor=1; //ask for username

//replace bad words or expressions
$filterRegex=urlencode("(?i)(fuck|cunt)(?-i)");
$filterReplace=urlencode(" ** ");

//fill your layout code between <<<layoutEND and layoutEND;
$layoutCode=<<<layoutEND
layoutEND;

if (!$welcome) $welcome="Welcome on <B>".$_GET['room_name']."</B> live streaming channel!";

?>server=<?=$rtmp_server?>&serverAMF=<?=$rtmp_amf?>&bufferLive=0.5&bufferFull=16&welcome=<?=urlencode($welcome)?>&username=<?=$username?>&userType=<?=$userType?>&msg=&visitor=<?=$visitor?>&loggedin=1&showCredit=1&disconnectOnTimeout=1&offlineMessage=Channel+Offline&disableVideo=0&disableChat=0&disableUsers=0&layoutCode=<?=urlencode($layoutCode)?>&fillWindow=0&filterRegex=<?=$filterRegex?>&filterReplace=<?=$filterReplace?>&loadstatus=1