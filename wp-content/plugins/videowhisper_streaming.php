<?php
/*
Plugin Name: VideoWhisper Live Streaming
Plugin URI: http://www.videowhisper.com/?p=WordPress+Live+Streaming
Description: Live Streaming
Version: 1.0
Author: VideoWhisper.com
Author URI: http://www.videowhisper.com/
*/

$vw_db_version = "1.0";
$table_name = $wpdb->prefix . "vw_sessions";
$wpdb->flush();
	
$installed_ver = get_option( "vw_db_version" );

   if( $installed_ver != $vw_db_version ) {

      $sql = "DROP TABLE IF EXISTS `$table_name`;
CREATE TABLE `$table_name` (
  `id` int(11) NOT NULL auto_increment,
  `session` varchar(64) NOT NULL,
  `username` varchar(64) NOT NULL,
  `room` varchar(64) NOT NULL,
  `message` text NOT NULL,
  `sdate` int(11) NOT NULL,
  `edate` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `type` tinyint(4) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `status` (`status`),
  KEY `type` (`type`),
  KEY `room` (`room`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Video Whisper: Sessions - 2009@videowhisper.com' AUTO_INCREMENT=1 ;";

      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      dbDelta($sql);

	  if (!$installed_ver) add_option("vw_db_version", $jal_db_version);
	  else update_option( "vw_db_version", $jal_db_version );
  }

	$exptime=time()-30;
	$sql="DELETE FROM `$table_name` WHERE edate < $exptime";
    $wpdb->query($sql);
	

function liveStreaming()
{
	global $wpdb,$table_name;
	$items =  $wpdb->get_results("SELECT * FROM `$table_name` where status='1' and type='1'");

	echo "<ul>";
	if ($items)	foreach ($items as $item) echo "<li><a href='videowhisper_streaming/channel.php?n=".urlencode($item->room)."'><B>".$item->room."</B>".($item->message?": ".$item->message:"") ."</a></li>";
	else echo "<li>No broadcasters online.</li>";
	echo "</ul>";


?><a href="/videowhisper_streaming/"><img src="videowhisper_streaming/templates/live/i_webcam.png" align="absmiddle" border="0"> Video Broadcast</a>
<?
}

function widget_vwStreaming($args) {
  extract($args);
  echo $before_widget;
  echo $before_title;?>Live Streaming<?php echo $after_title;
  liveStreaming();
  echo $after_widget;
}

function vwStreaming_init()
{
  register_sidebar_widget(__('VideoWhisper Streaming'), 'widget_vwStreaming');
}
add_action("plugins_loaded", "vwStreaming_init");

$wpdb->flush();
?>
