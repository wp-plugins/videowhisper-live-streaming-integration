<?
include("../wp-config.php");

get_currentuserinfo();

$loggedin=0;
$msg="";
if ($userdata->user_nicename)
$loggedin=1;
else $msg=urlencode("<a href=\"/\">Please login first or register an account if you don't have one! Click here to return to website.</a>");

$username=urlencode($userdata->user_nicename);

include("inc.php");

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
$swfurl=$base."live_watch.swf?n=".urlencode($username);
$swfurl2=$base."live_video.swf?n=".urlencode($username);
$embedcode =<<<EMBEDEND
<object width="640" height="350"><param name="movie" value="$swfurl"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="$swfurl" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="640" height="350"></embed></object>
EMBEDEND;
$embedvcode =<<<EMBEDEND2
<object width="320" height="240"><param name="movie" value="$swfurl2"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="$swfurl2" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="320" height="240"></embed></object>
EMBEDEND2;

?>server=<?=$rtmp_server?>&room=<?=$username?>&welcome=Welcome!&username=<?=$username?>&webserver=&msg=<?=$msg?>&loggedin=<?=$loggedin?>&linkcode=<?=urlencode($linkcode)?>&embedcode=<?=urlencode($embedcode)?>&embedvcode=<?=urlencode($embedvcode)?>&loadstatus=1