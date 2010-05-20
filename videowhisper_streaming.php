<?php
/*
Plugin Name: VideoWhisper Live Streaming
Plugin URI: http://www.videowhisper.com/?p=WordPress+Live+Streaming
Description: Live Streaming
Version: 2.0
Author: VideoWhisper.com
Author URI: http://www.videowhisper.com/
*/


if (!class_exists("VWliveStreaming")) 
{
    class VWliveStreaming {
        
	function VWliveStreaming() { //constructor
		

        }


	function settings_link($links) {
	  $settings_link = '<a href="options-general.php?page=videowhisper_streaming.php">'.__("Settings").'</a>';
	  array_unshift($links, $settings_link);
	  return $links;
	}

	function init()
	{
	  $plugin = plugin_basename(__FILE__);
	  add_filter("plugin_action_links_$plugin",  array('VWliveStreaming','settings_link') );
	  
	  wp_register_sidebar_widget('liveStreamingWidget','VideoWhisper Streaming', array('VWliveStreaming', 'widget') );
	  $this->getAdminOptions();
	  
	    //check db
	  	$vw_db_version = "1.0";

		global $wpdb;
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
			
		$wpdb->flush();
	}
	
	function widgetContent()
	{
		global $wpdb;
		$table_name = $wpdb->prefix . "vw_sessions";
		$items =  $wpdb->get_results("SELECT * FROM `$table_name` where status='1' and type='1'");

		echo "<ul>";
		if ($items)	foreach ($items as $item) echo "<li><a href='wp-content/plugins/videowhisper-live-streaming-integration/ls/channel.php?n=".urlencode($item->room)."'><B>".$item->room."</B>".($item->message?": ".$item->message:"") ."</a></li>";
		else echo "<li>No broadcasters online.</li>";
		echo "</ul>";


	?><a href="wp-content/plugins/videowhisper-live-streaming-integration/ls/"><img src="wp-content/plugins/videowhisper-live-streaming-integration/ls/templates/live/i_webcam.png" align="absmiddle" border="0"> Video Broadcast</a>
	<?
	}

	function widget($args) {
	  extract($args);
	  echo $before_widget;
	  echo $before_title;?>Live Streaming<?php echo $after_title;
	  VWliveStreaming::widgetContent();
	  echo $after_widget;
	}

	function menu() {
	  add_options_page('Streaming Options', 'Video Streaming', 9, basename(__FILE__), array('VWliveStreaming', 'options'));
	}

	function options() {
		$options = get_option('VWliveStreamingOptions');

		if (isset($_POST['updateSettings'])) 
		{
				if (isset($_POST['rtmp_server'])) $options['rtmp_server'] = $_POST['rtmp_server'];
				if (isset($_POST['noEmbeds'])) $options['noEmbeds'] = $_POST['noEmbeds'];
				if (isset($_POST['onlyVideo'])) $options['onlyVideo'] = $_POST['onlyVideo'];
				update_option('VWliveStreamingOptions', $options);
		}
			
	  ?>
<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
<h2>VideoWhisper Live Streaming Settings</h2>
<p>To run this, make sure your hosting environment meets all <a href="http://www.videowhisper.com/?p=Requirements" target="_blank">requirements</a>.  If you don't have a videowhisper rtmp address yet (from a managed rtmp host), go to <a href="http://www.videowhisper.com/?p=RTMP+Applications" target="_blank">RTMP Application   Setup</a> for  installation details.</p>
<h3>RTMP Address</h3>
<input name="rtmp_server" type="text" id="rtmp_server" size="64" maxlength="256" value="<?=$options['rtmp_server']?>"/>
<h3>Broadcaster</h3>
<h4>Disable Embed/Link Codes</h4>
<select name="noEmbeds" id="noEmbeds">
  <option value="0" <?=$options['noEmbeds']?"":"selected"?>>No</option>
  <option value="1" <?=$options['noEmbeds']?"selected":""?>>Yes</option>
</select>
<h4>Show only Video</h4>
<select name="onlyVideo" id="onlyVideo">
  <option value="0" <?=$options['onlyVideo']?"":"onlyVideo"?>>No</option>
  <option value="1" <?=$options['onlyVideo']?"onlyVideo":""?>>Yes</option>
</select>
</fieldset>
<div class="submit">
  <input type="submit" name="updateSettings" id="updateSettings" value="<?php _e('Update Settings', 'VWliveStreaming') ?>" />
</div>

</form>
	 <?
	}

	function getAdminOptions() {
				$adminOptions = array('rtmp_server' => 'rtmp://localhost/videowhisper',
					'rtmp_amf' => 'AMF3',
					'onlyVideo' => '0',
					'noEmbeds' => '0');
				$options = get_option('VWliveStreamingOptions');
				if (!empty($options)) {
					foreach ($options as $key => $option)
						$adminOptions[$key] = $option;
				}            
				update_option('VWliveStreamingOptions', $adminOptions);
				return $adminOptions;
	}
}
} 

//instantiate

      if (class_exists("VWliveStreaming")) {
          $liveStreaming = new VWliveStreaming();
      }

//Actions and Filters   
if (isset($liveStreaming)) {
add_action("plugins_loaded", array(&$liveStreaming, 'init'));
add_action('admin_menu', array(&$liveStreaming, 'menu'));

}



?>
