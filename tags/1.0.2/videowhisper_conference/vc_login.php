<?
include("../wp-config.php");

get_currentuserinfo();

$loggedin=$msg="";
if ($userdata->user_nicename)
$loggedin=1;
else $msg=urlencode("<a href=\"/\">Please login first or register an account if you don't have one! Click here to return to website.</a>");

$username=urlencode($userdata->user_nicename);
include("inc.php");
?>server=<?=$rtmp_server?>&room=Lobby&welcome=Welcome!&username=<?=$username?>&webserver=&msg=<?=$msg?>&tutorial=1&room_delete=0&room_create=0&file_upload=1&file_delete=1&loggedin=<?=$loggedin?>&loadstatus=1