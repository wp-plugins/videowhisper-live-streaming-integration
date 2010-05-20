<?php
include("../../../../wp-config.php");

get_currentuserinfo();

$loggedin=0;
$msg="";

if ($userdata->user_nicename) $username=urlencode($userdata->user_nicename);
$username=preg_replace("/[^0-9a-zA-Z]/","-",$username);

if ($username) $loggedin=1; else $msg=urlencode("<a href=\"/\">Please login first or register an account if you don't have one! Click here to return to website.</a>");

$options = get_option('VWliveStreamingOptions');
$rtmp_server = $options['rtmp_server'];
$rtmp_amf = 'AMF3';

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

?>server=<?=$rtmp_server?>&serverAMF=<?=$rtmp_amf?>&room=<?=$username?>&welcome=Welcome!&username=<?=$username?>&userType=3&webserver=&msg=<?=$msg?>&loggedin=<?=$loggedin?>&linkcode=<?=urlencode($linkcode)?>&embedcode=<?=urlencode($embedcode)?>&embedvcode=<?=urlencode($embedvcode)?>&imagecode=<?=urlencode($imagecode)?>&room_limit=&showTimer=1&showCredit=1&disconnectOnTimeout=1&camWidth=320&camHeight=240&camFPS=15&micRate=11&camBandwidth=40960&bufferLive=2&bufferFull=16&showCamSettings=1&advancedCamSettings=1&camMaxBandwidth=81920&configureSource=1&generateSnapshots=1&snapshotsTime=60000&onlyVideo=<?=$options['onlyVideo']?>&noEmbeds=<?=$options['noEmbeds']?>&loadstatus=1