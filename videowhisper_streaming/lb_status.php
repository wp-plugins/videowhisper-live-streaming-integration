<?
include("../wp-config.php");
include("inc.php");
//append_log("status:$s|$u|$r|$m");

$table_name = $wpdb->prefix . "vw_sessions";
$wpdb->flush();
	

	$s=$_POST['s'];
	$u=$_POST['u'];
	$r=$_POST['r'];
	$m=$_POST['m'];

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
?>loaded=1