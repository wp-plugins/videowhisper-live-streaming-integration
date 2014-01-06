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

$timeUsed=$currentTime=$_POST['ct'];
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
$table_name3 = $wpdb->prefix . "vw_lsrooms";
$wpdb->flush();

$ztime=time();



//room info
$sql = "SELECT * FROM $table_name3 where name='$r'";
$channel = $wpdb->get_row($sql);
$wpdb->query($sql);

if (!$channel) $disconnect = urlencode("Channel $r not found!");
else
{
	$ztime=time();

    //update viewer online
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


	//room usage
	// options in minutes
	// mysql in s
	// flash in ms (minimise latency errors)

	$options = get_option('VWliveStreamingOptions');

	if ($channel->type>=2) //premium
		{
		$maximumBroadcastTime =  60 * $options['pBroadcastTime'];
		$maximumWatchTime =  60 * $options['pWatchTime'];
	}
	else
	{
		$maximumBroadcastTime =  60 * $options['broadcastTime'];
		$maximumWatchTime =  60 * $options['watchTime'];
	}

	$maximumSessionTime = $maximumWatchTime;

	//update time
	$dS = floor(($currentTime-$lastTime)/1000);
	if ($dS>180 || $dS<0) $disconnect = urlencode("Web server out of sync!"); //Updates should be faster than 3 minutes; fraud attempt?
	else
	{
		$channel->wtime += $dS;
		$timeUsed = $channel->wtime * 1000;

		if ($maximumBroadcastTime && $maximumBroadcastTime < $channel->btime ) $disconnect = urlencode("Allocated broadcasting time ended!");
		if ($maximumWatchTime && $maximumWatchTime < $channel->wtime ) $disconnect = urlencode("Allocated watch time ended!");

		$maximumSessionTime *=1000;

		//update
		$sql="UPDATE `$table_name3` set edate=$ztime, wtime = " . $channel->wtime . " where name='$r'";
		$wpdb->query($sql);
	}



}

?>timeTotal=<?php echo $maximumSessionTime?>&timeUsed=<?php echo $timeUsed?>&lastTime=<?php echo $currentTime?>&disconnect=<?php echo $disconnect?>&loadstatus=1