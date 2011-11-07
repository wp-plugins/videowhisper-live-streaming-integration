<?php
/*
POST Variables:
u=Username
s=Session, usually same as username
r=Room
ct=session time (in milliseconds)
lt=last session time received from this script in (milliseconds)
*/


include("../../../../wp-config.php");
	
include("inc.php");
include("incsan.php");

//
	$cam=$_POST['cam'];
	$mic=$_POST['mic'];

	$currentTime=$_POST['ct'];
	$lastTime=$_POST['lt'];

	$s=$_POST['s'];
	$u=$_POST['u'];
	$r=$_POST['r'];
	$m=$_POST['m'];
	
	//sanitize variables
	sanV($s);
	sanV($u);
	sanV($r);
	sanV($m,0, 0);

	//exit if no valid session name or room name
	if (!$s) exit;
	if (!$r) exit;

	global $wpdb;
	$table_name = $wpdb->prefix . "vw_lwsessions";
	$wpdb->flush();
	
	$ztime=time();

	$sql = "SELECT * FROM $table_name where session='$s' and status='1'";
	$session = $wpdb->get_row($sql);
	if (!$session)
	{
	$sql="INSERT INTO `$table_name` ( `session`, `username`, `room`, `message`, `sdate`, `edate`, `status`, `type`) VALUES ('$s', '$u', '$r', '$m', $ztime, $ztime, 1, 1)";
    $wpdb->query($sql);
	}
	else
	{
	$sql="UPDATE `$table_name` set edate=$ztime, room='$r', username='$u', message='$m' where session='$s' and status='1'";
    $wpdb->query($sql);
	}

	$exptime=$ztime-30;
	$sql="DELETE FROM `$table_name` WHERE edate < $exptime";
    $wpdb->query($sql);


$maximumSessionTime=0; //900000ms=15 minutes; 0 for unlimited

$disconnect=""; //anything else than "" will disconnect with that message

	
?>timeTotal=<?=$maximumSessionTime?>&timeUsed=<?=$currentTime?>&lastTime=<?=$currentTime?>&disconnect=<?=$disconnect?>&loadstatus=1