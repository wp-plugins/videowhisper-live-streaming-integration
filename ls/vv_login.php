<?php
include("../../../../wp-config.php");
include("incsan.php");

$options = get_option('VWliveStreamingOptions');
$rtmp_server = $options['rtmp_server'];
$rtmp_amf = $options['rtmp_amf'];
$userName =  $options['userName']; if (!$userName) $userName='user_nicename';
$canWatch = $options['canWatch'];
$watchList = $options['watchList'];

$tokenKey = $options['tokenKey'];
$serverRTMFP = $options['serverRTMFP'];
$p2pGroup = $options['p2pGroup'];
$supportRTMP = $options['supportRTMP'];
$supportP2P = $options['supportP2P'];
$alwaystRTMP = $options['alwaystRTMP'];
$alwaystP2P = $options['alwaystP2P'];
$disableBandwidthDetection = $options['disableBandwidthDetection'];

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
$debug = "$roomName:$username";
		
$userType=0;

$canKick = 0;
if ($loggedin) include_once("rtmp.inc.php"); //approve session for rtmp check

?>server=<?=$rtmp_server?>&serverAMF=<?=$rtmp_amf?>&tokenKey=<?=$tokenKey?>&serverRTMFP=<?=urlencode($serverRTMFP)?>&p2pGroup=<?=$p2pGroup?>&supportRTMP=<?=$supportRTMP?>&supportP2P=<?=$supportP2P?>&alwaysRTMP=<?=$alwaysRTMP?>&alwaysP2P=<?=$alwaysP2P?>&disableBandwidthDetection=<?=$disableBandwidthDetection?>&bufferLive=0.5&bufferFull=8&welcome=Welcome!&username=<?=$username?>&userType=<?=$userType?>&msg=<?=$msg?>&loggedin=<?=$loggedin?>&visitor=<?=$visitor?>&showCredit=1&disconnectOnTimeout=1&offlineMessage=Channel+Offline&overLogo=<?=urlencode($options['overLogo'])?>&overLink=<?=urlencode($options['overLink'])?>&loadstatus=1&debug=<?=$debug?>