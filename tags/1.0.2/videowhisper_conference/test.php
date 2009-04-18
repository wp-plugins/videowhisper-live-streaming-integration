<?

 include("../wp-config.php");
	global $userdata;
	get_currentuserinfo();
	var_dump($userdata);

echo $userdata->user_nicename;
 ?>