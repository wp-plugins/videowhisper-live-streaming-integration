<?php
include_once("../../../../wp-config.php");
include("incsan.php");

$options = get_option('VWliveStreamingOptions');

$rtmp_server = $options['rtmp_server'];
$rtmp_amf = $options['rtmp_amf'];
$userName =  $options['userName']; if (!$userName) $userName='user_nicename';
$canBroadcast = $options['canBroadcast'];
$broadcastList = $options['broadcastList'];

$tokenKey = $options['tokenKey'];
$webKey = $options['webKey'];

$serverRTMFP = $options['serverRTMFP'];
$p2pGroup = $options['p2pGroup'];
$supportRTMP = $options['supportRTMP'];
$supportP2P = $options['supportP2P'];
$alwaysRTMP = $options['alwaysRTMP'];
$alwaysP2P = $options['alwaysP2P'];
$disableBandwidthDetection = $options['disableBandwidthDetection'];

$camRes = explode('x',$options['camResolution']);

global $current_user;
get_currentuserinfo();

$loggedin=0;
$msg="";


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
sanV($username);

//access keys
if ($current_user)
{
	$userkeys = $current_user->roles;
	$userkeys[] = $current_user->user_login;
	$userkeys[] = $current_user->ID;
	$userkeys[] = $current_user->user_email;
	$userkeys[] = $current_user->display_name;
}

switch ($canBroadcast)
{
case "members":
	if ($username) $loggedin=1;
	else $msg=urlencode("<a href=\"/\">Please login first or register an account if you don't have one! Click here to return to website.</a>");
	break;
case "list";
	if ($username)
		if (inList($userkeys, $broadcastList)) $loggedin=1;
		else $msg=urlencode("<a href=\"/\">$username, you are not in the broadcasters list.</a>");
		else $msg=urlencode("<a href=\"/\">Please login first or register an account if you don't have one! Click here to return to website.</a>");
		break;
}

//broadcaster
$userlabel="";
$room_name=$_GET['room_name'];
sanV($room_name);

if ($room_name&&$room_name!=$username)
{
	$userlabel=$username;
	$username=$room_name;
	$room=$room_name;
}

if (!$room) $room = $username;

if (!$room)
{
	$loggedin=0;
	$msg=urlencode("<a href=\"/\">Can't enter: Room missing!</a>");
}

if (!$username)
{
	$loggedin=0;
	$msg=urlencode("<a href=\"/\">Can't enter: Username missing!</a>");
}


//channel name
if ($loggedin)
{
	$table_name = $wpdb->prefix . "vw_sessions";
	$table_name3 = $wpdb->prefix . "vw_lsrooms";
	global $wpdb;

	$wpdb->flush();
	$ztime=time();

	//online broadcasting session
	$sql = "SELECT * FROM $table_name where session='$username' and status='1'";
	$session = $wpdb->get_row($sql);

	if (!$session)
		$sql="INSERT INTO `$table_name` ( `session`, `username`, `room`, `message`, `sdate`, `edate`, `status`, `type`) VALUES ('$username', '$username', '$room', '', $ztime, $ztime, 1, 1)";
	else
		$sql="UPDATE `$table_name` set edate=$ztime, room='$room', username='$username' where session='$username' and status='1'";

	$wpdb->query($sql);

	$exptime=$ztime-30;
	$sql="DELETE FROM `$table_name` WHERE edate < $exptime";
	$wpdb->query($sql);

	//setup/update channel, premium & time reset
	if (inList($userkeys, $options['premiumList'])) //premium room
	{
		$rtype=2; 
		$camBandwidth=$options['pCamBandwidth'];
		$camMaxBandwidth=$options['pCamMaxBandwidth'];
		if (!$options['pLogo']) $options['overLogo']=$options['overLink']='';

	}else
	{
		$rtype=1;
		$camBandwidth=$options['camBandwidth'];
		$camMaxBandwidth=$options['camMaxBandwidth'];
	}



	$sql = "SELECT * FROM $table_name3 where owner='$username' and name='$room'";
	$channel = $wpdb->get_row($sql);

	if (!$channel)
		$sql="INSERT INTO `$table_name3` ( `owner`, `name`, `sdate`, `edate`, `rdate`,`status`, `type`) VALUES ('$username', '$room', $ztime, $ztime, $ztime, 1, $rtype)";
	elseif ($options['timeReset'] && $channel->rdate < $ztime - $options['timeReset']*24*3600) //time to reset in days
		$sql="UPDATE `$table_name3` set edate=$ztime, type=$rtype, rdate=$ztime, wtime=0, btime=0 where owner='$username' and name='$room'";
	else
		$sql="UPDATE `$table_name3` set edate=$ztime, type=$rtype where owner='$username' and name='$room'";

	$wpdb->query($sql);

	//update online broadcasters list
	$exptime=$ztime-30;
	$sql="DELETE FROM `$table_name` WHERE edate < $exptime";
	$wpdb->query($sql);

}

$canKick = 1;
if ($loggedin) include_once("rtmp.inc.php"); //approve session for rtmp check

function baseURL() {
	$pageURL = 'http';
	if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	} else {
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}
	return substr($pageURL,0,strrpos($pageURL,"/"))."/";
}

$base=baseURL();
$linkcode=$base."channel.php?n=".urlencode($username);
$imagecode=$base."snapshots/".urlencode($username).".jpg";
$swfurl=$base."live_watch.swf?n=".urlencode($username);
$swfurl2=$base."live_video.swf?n=".urlencode($username);

$embedcode =<<<EMBEDEND
<object width="640" height="350"><param name="movie" value="$swfurl" /><param name="base" value="$base" /><param name="allowFullScreen" value="true" /><param name="allowscriptaccess" value="always" /><embed src="$swfurl" base="$base" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="640" height="350"></embed></object>
EMBEDEND;
$embedvcode =<<<EMBEDEND2
<object width="320" height="240"><param name="movie" value="$swfurl2" /><param name="base" value="$base" /><param name="scale" value="exactfit"/><param name="allowFullScreen" value="true" /><param name="allowscriptaccess" value="always" /><embed src="$swfurl2" base="$base" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="320" height="240" scale="exactfit"></embed></object>
EMBEDEND2;
?>firstParameter=fix&server=<?php echo $rtmp_server?>&serverAMF=<?php echo $rtmp_amf?>&tokenKey=<?php echo $tokenKey?>&serverRTMFP=<?php echo urlencode($serverRTMFP)?>&p2pGroup=<?php echo $p2pGroup?>&supportRTMP=<?php echo $supportRTMP?>&supportP2P=<?php echo $supportP2P?>&alwaysRTMP=<?php echo $alwaysRTMP?>&alwaysP2P=<?php echo $alwaysP2P?>&disableBandwidthDetection=<?php echo $disableBandwidthDetection?>&room=<?php echo $username?>&welcome=Welcome!&username=<?php echo $username?>&userlabel=<?php echo $userlabel?>&overLogo=<?php echo urlencode($options['overLogo'])?>&overLink=<?php echo urlencode($options['overLink'])?>&userType=3&webserver=&msg=<?php echo $msg?>&loggedin=<?php echo $loggedin?>&linkcode=<?php echo urlencode($linkcode)?>&embedcode=<?php echo urlencode($embedcode)?>&embedvcode=<?php echo urlencode($embedvcode)?>&imagecode=<?php echo urlencode($imagecode)?>&room_limit=&showTimer=1&showCredit=1&disconnectOnTimeout=1&camWidth=<?php echo $camRes[0];?>&camHeight=<?php echo $camRes[1];?>&camFPS=<?php echo $options['camFPS']?>&camBandwidth=<?php echo $camBandwidth?>&videoCodec=<?php echo $options['videoCodec']?>&codecProfile=<?php echo $options['codecProfile']?>&codecLevel=<?php echo $options['codecLevel']?>&soundCodec=<?php echo $options['soundCodec']?>&soundQuality=<?php echo $options['soundQuality']?>&micRate=<?php echo $options['micRate']?>&bufferLive=2&bufferFull=2&showCamSettings=1&advancedCamSettings=1&camMaxBandwidth=<?php echo $camMaxBandwidth?>&configureSource=1&generateSnapshots=1&snapshotsTime=60000&onlyVideo=<?php echo $options['onlyVideo']?>&noEmbeds=<?php echo $options['noEmbeds']?>&loadstatus=1&debug=<?php echo $debug?>