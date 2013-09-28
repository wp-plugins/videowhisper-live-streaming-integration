<?php
//esternal login GET u=user, p=password

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
$alwaystRTMP = $options['alwaystRTMP'];
$alwaystP2P = $options['alwaystP2P'];
$disableBandwidthDetection = $options['disableBandwidthDetection'];

$loggedin=0;
$msg="";	
	
$creds = array(); 
$creds['user_login'] = $_GET['u']; 
$creds['user_password'] = $_GET['p']; 
$creds['remember'] = true; 
     
global $current_user;
$current_user = wp_signon( $creds, false );  

if( is_wp_error($current_user)) 
    { 
	$msg = urlencode("Login failed: " . $current_user->get_error_message()) ;
	$debug = $msg;
    } 
    else 
    { 
     //logged in
   }         
      
get_currentuserinfo();

function inList($item, $data)
{
	$list=explode(",",$data);
	foreach ($list as $listing) if ($item==trim($listing)) return 1;
	return 0;
}
	

//username
if ($current_user->$userName) $username=urlencode($current_user->$userName);
sanV($username);

switch ($canBroadcast)
{
	case "members":
		if ($username) $loggedin=1;
		else $msg=urlencode("<a href=\"/\">Please login first or register an account if you don't have one! Click here to return to website.</a>");
	break;
	case "list";
		if ($username)
			if (inList($username, $broadcastList)) $loggedin=1;
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


?>firstParameter=fix&server=<?=$rtmp_server?>&serverAMF=<?=$rtmp_amf?>&tokenKey=<?=$tokenKey?>&serverRTMFP=<?=urlencode($serverRTMFP)?>&p2pGroup=<?=$p2pGroup?>&supportRTMP=<?=$supportRTMP?>&supportP2P=<?=$supportP2P?>&alwaysRTMP=<?=$alwaysRTMP?>&alwaysP2P=<?=$alwaysP2P?>&disableBandwidthDetection=<?=$disableBandwidthDetection?>&room=<?=$username?>&welcome=Welcome!&username=<?=$username?>&userlabel=<?=$userlabel?>&overLogo=<?=urlencode($options['overLogo'])?>&overLink=<?=urlencode($options['overLink'])?>&userType=3&webserver=&msg=<?=$msg?>&loggedin=<?=$loggedin?>&linkcode=<?=urlencode($linkcode)?>&embedcode=<?=urlencode($embedcode)?>&embedvcode=<?=urlencode($embedvcode)?>&imagecode=<?=urlencode($imagecode)?>&room_limit=&showTimer=1&showCredit=1&disconnectOnTimeout=1&camWidth=480&camHeight=360&camFPS=15&camBandwidth=40960&videoCodec=<?=$options['videoCodec']?>&codecProfile=<?=$options['codecProfile']?>&codecLevel=<?=$options['codecLevel']?>&soundCodec=<?=$options['soundCodec']?>&soundQuality=<?=$options['soundQuality']?>&micRate=<?=$options['micRate']?>&bufferLive=0.5&bufferFull=8&showCamSettings=1&advancedCamSettings=1&camMaxBandwidth=81920&configureSource=1&generateSnapshots=1&snapshotsTime=60000&onlyVideo=<?=$options['onlyVideo']?>&noEmbeds=<?=$options['noEmbeds']?>&loadstatus=1&debug=<?=$debug?>