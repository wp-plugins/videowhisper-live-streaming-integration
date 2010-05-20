<?php
include("inc.php");
$username="VV".base_convert((time()-1224350000).rand(0,10),10,36);
?>server=<?=$rtmp_server?>&serverAMF=<?=$rtmp_amf?>&bufferLive=0.5&bufferFull=16&welcome=Welcome!&username=<?=$username?>&userType=0&msg=&visitor=1&loggedin=1&showCredit=1&disconnectOnTimeout=1&offlineMessage=Channel+Offline&loadstatus=1