<?php
//vs_login.php controls watch interface (video & chat & user list) login

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

//if any key matches any listing
function inList($keys, $data)
{
	if (!$keys) return 0;

	$list=explode(",", strtolower(trim($data)));

	foreach ($keys as $key)
		foreach ($list as $listing)
			if ( strtolower(trim($key)) == trim($listing) ) return 1;

			return 0;
}


//username
if ($current_user->$userName) $username=urlencode($current_user->$userName);
$username=preg_replace("/[^0-9a-zA-Z]/","-",$username);

//access keys
if ($current_user)
{
	$userkeys = $current_user->roles;
	$userkeys[] = $current_user->user_login;
	$userkeys[] = $current_user->ID;
	$userkeys[] = $current_user->user_email;
	$userkeys[] = $current_user->display_name;
}


$roomName=$_GET['room_name'];
sanV($roomName);

if ($username==$roomName) $username.="_".rand(10,99);//allow viewing own room - session names must be different

//check room
global $wpdb;
$table_name3 = $wpdb->prefix . "vw_lsrooms";
$wpdb->flush();

$sql = "SELECT * FROM $table_name3 where name='$roomName'";
$channel = $wpdb->get_row($sql);
$wpdb->query($sql);

if (!$channel)
{
	$msg = urlencode("Channel $roomName not found!");
}
else
{

	if ($channel->type>=2) //premium
		{
		if (!$options['pLogo']) $options['overLogo']=$options['overLink']='';
		$canWatch = $options['canWatchPremium'];
		$watchList = $options['watchPremium'];
		$msgp = urlencode(" This is a premium channel.");
	}


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
		else $msg=urlencode("<a href=\"/\">Please login first or register an account if you don't have one! Click here to return to website.</a>") . $msgp;
		break;
	case "list";
		if ($username)
			if (inList($userkeys, $watchList)) $loggedin=1;
			else $msg=urlencode("<a href=\"/\">$username, you are not in the allowed watchers list.</a>") . $msgp;
			else $msg=urlencode("<a href=\"/\">Please login first or register an account if you don't have one! Click here to return to website.</a>") . $msgp;
			break;
	}

}


$userType=0;
$canKick = 0;
if ($loggedin) include_once("rtmp.inc.php"); //approve session for rtmp check

//replace bad words or expressions
$filterRegex=urlencode("(?i)(fuck|cunt)(?-i)");
$filterReplace=urlencode(" ** ");

//fill your layout code between <<<layoutEND and layoutEND;
$layoutCode=<<<layoutEND
layoutEND;

if (!$welcome) $welcome="Welcome on <B>".$roomName."</B> live streaming channel!";

?>server=<?php echo $rtmp_server?>&serverAMF=<?php echo $rtmp_amf?>&tokenKey=<?php echo $tokenKey?>&serverRTMFP=<?php echo urlencode($serverRTMFP)?>&p2pGroup=<?php echo $p2pGroup?>&supportRTMP=<?php echo $supportRTMP?>&supportP2P=<?php echo $supportP2P?>&alwaysRTMP=<?php echo $alwaysRTMP?>&alwaysP2P=<?php echo $alwaysP2P?>&disableBandwidthDetection=<?php echo $disableBandwidthDetection?>&bufferLive=0.5&bufferFull=8&welcome=<?php echo urlencode($welcome)?>&username=<?php echo $username?>&userType=<?php echo $userType?>&msg=<?php echo $msg?>&loggedin=<?php echo $loggedin?>&visitor=<?php echo $visitor?>&showCredit=1&disconnectOnTimeout=1&offlineMessage=Channel+Offline&overLogo=<?php echo urlencode($options['overLogo'])?>&overLink=<?php echo urlencode($options['overLink'])?>&disableVideo=0&disableChat=0&disableUsers=0&layoutCode=<?php echo urlencode($layoutCode)?>&fillWindow=0&filterRegex=<?php echo $filterRegex?>&filterReplace=<?php echo $filterReplace?>&loadstatus=1