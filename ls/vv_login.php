<?php
include("../../../../wp-config.php");

$options = get_option('VWliveStreamingOptions');
$rtmp_server = $options['rtmp_server'];
$rtmp_amf = $options['rtmp_amf'];
$userName =  $options['userName']; if (!$userName) $userName='user_nicename';
$canWatch = $options['canWatch'];
$watchList = $options['watchList'];
	
global $current_user;
get_currentuserinfo();

$loggedin=0;
$msg="";
$visitor=0;

function inList($item, $data)
{
	$list=explode(",",$data);
	foreach ($list as $listing) if ($item==trim($listing)) return 1;
	return 0;
}
	
//username
if ($current_user->$userName) $username=urlencode($current_user->$userName);
$username=preg_replace("/[^0-9a-zA-Z]/","-",$username);

switch ($canWatch)
{
	case "all":
	$loggedin=1;
	if (!$username) 
	{
		$username="VW".base_convert((time()-1224350000).rand(0,10),10,36);
		$visitor=1; //ask for username
	}
	break;
	case "members":
		if ($username) $loggedin=1;
		else $msg=urlencode("<a href=\"/\">Please login first or register an account if you don't have one! Click here to return to website.</a>");
	break;
	case "list";
		if ($username)
			if (inList($username, $watchList)) $loggedin=1;
			else $msg=urlencode("<a href=\"/\">$username, you are not in the allowed watchers list.</a>");
		else $msg=urlencode("<a href=\"/\">Please login first or register an account if you don't have one! Click here to return to website.</a>");
	break;
}

$roomName=$_GET['room_name'];
if ($username==$roomName) $username.="_".rand(10,99);//allow viewing own room - session names must be different

		
$userType=0;

?>server=<?=$rtmp_server?>&serverAMF=<?=$rtmp_amf?>&bufferLive=0.5&bufferFull=16&welcome=Welcome!&username=<?=$username?>&userType=<?=$userType?>&msg=<?=$msg?>&loggedin=<?=$loggedin?>&visitor=<?=$visitor?>&showCredit=1&disconnectOnTimeout=1&offlineMessage=Channel+Offline&loadstatus=1