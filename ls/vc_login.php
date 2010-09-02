<?php
include("../../../../wp-config.php");

$options = get_option('VWliveStreamingOptions');
$rtmp_server = $options['rtmp_server'];
$rtmp_amf = $options['rtmp_amf'];
$userName =  $options['userName']; if (!$userName) $userName='user_nicename';
$canBroadcast = $options['canBroadcast'];
$broadcastList = $options['broadcastList'];
	
global $current_user;
get_currentuserinfo();

$loggedin=0;
$msg="";

function inList($item, $data)
{
	$list=explode(",",$data);
	foreach ($list as $listing) if ($item==trim($listing)) return 1;
	return 0;
}
	

//username
if ($current_user->$userName) $username=urlencode($current_user->$userName);
$username=preg_replace("/[^0-9a-zA-Z]/","-",$username);

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
if ($room_name&&$room_name!=$username) 
{
$userlabel=$username;
$username=$room_name;
$room=$room_name;
}

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

?>server=<?=$rtmp_server?>&serverAMF=<?=$rtmp_amf?>&room=<?=$username?>&welcome=Welcome!&username=<?=$username?>&userlabel=<?=$userlabel?>&userType=3&webserver=&msg=<?=$msg?>&loggedin=<?=$loggedin?>&linkcode=<?=urlencode($linkcode)?>&embedcode=<?=urlencode($embedcode)?>&embedvcode=<?=urlencode($embedvcode)?>&imagecode=<?=urlencode($imagecode)?>&room_limit=&showTimer=1&showCredit=1&disconnectOnTimeout=1&camWidth=320&camHeight=240&camFPS=15&micRate=11&camBandwidth=40960&bufferLive=2&bufferFull=16&showCamSettings=1&advancedCamSettings=1&camMaxBandwidth=81920&configureSource=1&generateSnapshots=1&snapshotsTime=60000&onlyVideo=<?=$options['onlyVideo']?>&noEmbeds=<?=$options['noEmbeds']?>&loadstatus=1