<?
include("../wp-config.php");

get_currentuserinfo();

$visitor=1;
$loggedin=$msg="";
if ($userdata->user_nicename) 
{
$username=urlencode($userdata->user_nicename."@");
$visitor=0;
}

include("inc.php");
if (!$username) $username="VW".base_convert((time()-1224350000).rand(0,10),10,36);
?>server=<?=$rtmp_server?>&welcome=Welcome!&username=<?=$username?>&msg=&visitor=<?=$visitor?>&loggedin=1&loadstatus=1