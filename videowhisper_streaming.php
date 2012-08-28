<?php
/*
Plugin Name: VideoWhisper Live Streaming
Plugin URI: http://www.videowhisper.com/?p=WordPress+Live+Streaming
Description: Live Streaming
Version: 4.25.2
Author: VideoWhisper.com
Author URI: http://www.videowhisper.com/
Contributors: videowhisper, VideoWhisper.com
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
	  
	    //check db
	  	$vw_db_version = "1.1";

		global $wpdb;
		$table_name = $wpdb->prefix . "vw_sessions";
		$table_name2 = $wpdb->prefix . "vw_lwsessions";
			
		$installed_ver = get_option( "vw_db_version" );

		if( $installed_ver != $vw_db_version ) 
		{
		$wpdb->flush();
		
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
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Video Whisper: Sessions - 2009@videowhisper.com' AUTO_INCREMENT=1 ;
		
		DROP TABLE IF EXISTS `$table_name2`;
		CREATE TABLE `$table_name2` (
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
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Video Whisper: Sessions - 2009@videowhisper.com' AUTO_INCREMENT=1 ;
		";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);

			if (!$installed_ver) add_option("vw_db_version", $vw_db_version);
			else update_option( "vw_db_version", $vw_db_version );
			
		$wpdb->flush();
		}
			

	}
	
	function widgetContent()
	{
		global $wpdb;
		$table_name = $wpdb->prefix . "vw_sessions";
		$table_name2 = $wpdb->prefix . "vw_lwsessions";
		
		$root_url = get_bloginfo( "url" ) . "/";
		
		//clean recordings
		$exptime=time()-30;
		$sql="DELETE FROM `$table_name` WHERE edate < $exptime";
		$wpdb->query($sql);
			
		$wpdb->flush();
		
		$items =  $wpdb->get_results("SELECT * FROM `$table_name` where status='1' and type='1'");

		echo "<ul>";
		if ($items)	foreach ($items as $item) 
		{
			$count =  $wpdb->get_results("SELECT count(*) as no FROM `$table_name2` where status='1' and type='1' and room='".$item->room."'");
			
			echo "<li><a href='" . $root_url ."wp-content/plugins/videowhisper-live-streaming-integration/ls/channel.php?n=".urlencode($item->room)."'><B>".$item->room."</B> (".($count[0]->no+1).") ".($item->message?": ".$item->message:"") ."</a></li>";
		}
		else echo "<li>No broadcasters online.</li>";
		echo "</ul>";

	?><a href="<?php echo $root_url; ?>wp-content/plugins/videowhisper-live-streaming-integration/ls/"><img src="<?php echo $root_url; ?>wp-content/plugins/videowhisper-live-streaming-integration/ls/templates/live/i_webcam.png" align="absmiddle" border="0"> Video Broadcast</a>
	<?
	
		$options = get_option('VWliveStreamingOptions');
		$state = 'block' ;
		if (!$options['videowhisper']) $state = 'none';	
		echo '<div id="VideoWhisper" style="display: ' . $state . ';"><p>Powered by VideoWhisper <a href="http://www.videowhisper.com/?p=WordPress+Live+Streaming">Live Video Streaming Software</a>.</p></div>';
	}

	function widget($args) {
	  extract($args);
	  echo $before_widget;
	  echo $before_title;?>Live Streaming<?php echo $after_title;
	  VWliveStreaming::widgetContent();
	  echo $after_widget;
	}

	function menu() {
	  add_options_page('Live Streaming Options', 'Live Streaming', 9, basename(__FILE__), array('VWliveStreaming', 'options'));
	}
	
	function getAdminOptions() {
				
				$root_url = get_bloginfo( "url" ) . "/";
				
				$adminOptions = array(
				'userName' => 'display_name',
				'rtmp_server' => 'rtmp://localhost/videowhisper',
				'rtmp_amf' => 'AMF3',
				
				'canBroadcast' => 'members',
				'broadcastList' => '',
				'canWatch' => 'all',
				'watchList' => '',
				'onlyVideo' => '0',
				'noEmbeds' => '0',
				
				'videoCodec'=>'H264',
				'codecProfile' => 'main',
				'codecLevel' => '3.1',
				
				'soundCodec'=> 'Speex',
				'soundQuality' => '9',
				'micRate' => '22',
				
				'overLogo' => $root_url .'wp-content/plugins/videowhisper-live-streaming-integration/ls/logo.png',
				'overLink' => 'http://www.videowhisper.com',
				
				'tokenKey' => 'VideoWhisper',
				'serverRTMFP' => 'rtmfp://stratus.adobe.com/f1533cc06e4de4b56399b10d-1a624022ff71/',
				'p2pGroup' => 'VideoWhisper',
				'supportRTMP' => '1',
				'supportP2P' => '0',
				'alwaysRTMP' => '0',
				'alwaysP2P' => '0',
				'disableBandwidthDetection' => '1',
				'videowhisper' => 0
				);
			
				$options = get_option('VWliveStreamingOptions');
				if (!empty($options)) {
					foreach ($options as $key => $option)
						$adminOptions[$key] = $option;
				}            
				update_option('VWliveStreamingOptions', $adminOptions);
				return $adminOptions;
	}
	
	function options() 
	{
		$options = VWliveStreaming::getAdminOptions();

		if (isset($_POST['updateSettings'])) 
		{
				if (isset($_POST['rtmp_server'])) $options['rtmp_server'] = $_POST['rtmp_server'];
				if (isset($_POST['noEmbeds'])) $options['noEmbeds'] = $_POST['noEmbeds'];
				if (isset($_POST['onlyVideo'])) $options['onlyVideo'] = $_POST['onlyVideo'];
				if (isset($_POST['userName'])) $options['userName'] = $_POST['userName'];
				
				if (isset($_POST['canBroadcast'])) $options['canBroadcast'] = $_POST['canBroadcast'];
				if (isset($_POST['broadcastList'])) $options['broadcastList'] = $_POST['broadcastList'];
				if (isset($_POST['canWatch'])) $options['canWatch'] = $_POST['canWatch'];
				if (isset($_POST['watchList'])) $options['watchList'] = $_POST['watchList'];
				
				if (isset($_POST['videoCodec'])) $options['videoCodec'] = $_POST['videoCodec'];
				if (isset($_POST['codecProfile'])) $options['codecProfile'] = $_POST['codecProfile'];
				if (isset($_POST['codecLevel'])) $options['codecLevel'] = $_POST['codecLevel'];
				
				if (isset($_POST['soundCodec'])) $options['soundCodec'] = $_POST['soundCodec'];
				if (isset($_POST['soundQuality'])) $options['soundQuality'] = $_POST['soundQuality'];
				if (isset($_POST['micRate'])) $options['micRate'] = $_POST['micRate'];

				if (isset($_POST['overLogo'])) $options['overLogo'] = $_POST['overLogo'];
				if (isset($_POST['overLink'])) $options['overLink'] = $_POST['overLink'];
				
				if (isset($_POST['tokenKey'])) $options['tokenKey'] = $_POST['tokenKey'];
				if (isset($_POST['serverRTMFP'])) $options['serverRTMFP'] = $_POST['serverRTMFP'];
				if (isset($_POST['p2pGroup'])) $options['p2pGroup'] = $_POST['p2pGroup'];
				if (isset($_POST['supportRTMP'])) $options['supportRTMP'] = $_POST['supportRTMP'];
				if (isset($_POST['supportP2P'])) $options['supportP2P'] = $_POST['supportP2P'];
				if (isset($_POST['alwaystRTMP'])) $options['alwaystRTMP'] = $_POST['alwaystRTMP'];
				if (isset($_POST['alwaystP2P'])) $options['alwaystP2P'] = $_POST['alwaystP2P'];
				if (isset($_POST['disableBandwidthDetection'])) $options['disableBandwidthDetection'] = $_POST['disableBandwidthDetection'];
				if (isset($_POST['videowhisper'])) $options['videowhisper'] = $_POST['videowhisper'];

				update_option('VWliveStreamingOptions', $options);
		}
			
	  ?>
<div class="wrap">
<div id="icon-options-general" class="icon32"><br></div>
<h2>VideoWhisper Live Streaming Settings</h2>
</div>

<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">

<h3>General Settings</h3>
<h5>RTMP Address</h5>
<p>To run this, make sure your hosting environment meets all <a href="http://www.videowhisper.com/?p=Requirements" target="_blank">requirements</a>.  If you don't have a videowhisper rtmp address yet (from a managed rtmp host), go to <a href="http://www.videowhisper.com/?p=RTMP+Applications" target="_blank">RTMP Application   Setup</a> for  installation details.</p>
<input name="rtmp_server" type="text" id="rtmp_server" size="80" maxlength="256" value="<?=$options['rtmp_server']?>"/>
<h5>Username</h5>
<select name="userName" id="userName">
  <option value="display_name" <?=$options['userName']=='display_name'?"selected":""?>>Display Name</option>
  <option value="user_login" <?=$options['userName']=='user_login'?"selected":""?>>Login (Username)</option>
  <option value="user_nicename" <?=$options['userName']=='user_nicename'?"selected":""?>>Nicename</option>  
</select>
<h5>Disable Bandwidth Detection</h5>
<p>Required on some rtmp servers that don't support bandwidth detection and return a Connection.Call.Fail error.</p>
<select name="disableBandwidthDetection" id="disableBandwidthDetection">
  <option value="0" <?=$options['disableBandwidthDetection']?"":"selected"?>>No</option>
  <option value="1" <?=$options['disableBandwidthDetection']?"selected":""?>>Yes</option>
</select>

<h5>Floating Logo / Watermark</h5>
<input name="overLogo" type="text" id="overLogo" size="80" maxlength="256" value="<?=$options['overLogo']?>"/>
<h5>Logo Link</h5>
<input name="overLink" type="text" id="overLink" size="80" maxlength="256" value="<?=$options['overLink']?>"/>

<h5>Show VideoWhisper Powered by</h5>
<select name="videowhisper" id="videowhisper">
  <option value="0" <?=$options['videowhisper']?"":"selected"?>>No</option>
  <option value="1" <?=$options['videowhisper']?"selected":""?>>Yes</option>
</select>
<h5>Token Key</h5>
<input name="tokenKey" type="text" id="tokenKey" size="32" maxlength="64" value="<?=$options['tokenKey']?>"/>
<h5>RTMFP Address</h5>
<p> Get your own independent RTMFP address by registering for a free <a href="https://www.adobe.com/cfusion/entitlement/index.cfm?e=cirrus" target="_blank">Adobe Cirrus developer key</a>. This is required for P2P support.</p>
<input name="serverRTMFP" type="text" id="serverRTMFP" size="80" maxlength="256" value="<?=$options['serverRTMFP']?>"/>
<h5>P2P Group</h5>
<input name="p2pGroup" type="text" id="p2pGroup" size="32" maxlength="64" value="<?=$options['p2pGroup']?>"/>
<h5>Support RTMP Streaming</h5>
<select name="supportRTMP" id="supportRTMP">
  <option value="0" <?=$options['supportRTMP']?"":"selected"?>>No</option>
  <option value="1" <?=$options['supportRTMP']?"selected":""?>>Yes</option>
</select>
<h5>Always do RTMP Streaming</h5>
<p>Enable this if you want all streams to be published to server, no matter if there are registered subscribers or not (in example if you're using server side video archiving and need all streams published for recording).</p>
<select name="alwaystRTMP" id="alwaystRTMP">
  <option value="0" <?=$options['alwaystRTMP']?"":"selected"?>>No</option>
  <option value="1" <?=$options['alwaystRTMP']?"selected":""?>>Yes</option>
</select>
<h5>Support P2P Streaming</h5>
<select name="supportP2P" id="supportP2P">
  <option value="0" <?=$options['supportP2P']?"":"selected"?>>No</option>
  <option value="1" <?=$options['supportP2P']?"selected":""?>>Yes</option>
</select>
<h5>Always do P2P Streaming</h5>
<select name="alwaysP2P" id="alwaysP2P">
  <option value="0" <?=$options['alwaysP2P']?"":"selected"?>>No</option>
  <option value="1" <?=$options['alwaysP2P']?"selected":""?>>Yes</option>
</select>

<h3>Video Broadcaster</h3>
<h5>Who can broadcast video</h5>
<select name="canBroadcast" id="canBroadcast">
  <option value="members" <?=$options['canBroadcast']=='members'?"selected":""?>>All Members</option>
  <option value="list" <?=$options['canBroadcast']=='list'?"selected":""?>>Members in List</option>  
</select>
<h5>Members allowed to broadcast video (comma separated usernames)</h5>
<textarea name="broadcastList" cols="64" rows="3" id="broadcastList"><?=$options['broadcastList']?>
</textarea>

<h5>Video Codec</h5>
<select name="videoCodec" id="videoCodec">
  <option value="H264" <?=$options['videoCodec']=='H264'?"selected":""?>>H264</option>
  <option value="H263" <?=$options['videoCodec']=='H263'?"selected":""?>>H263</option>  
</select>

<h5>H264 Video Codec Profile</h5>
<select name="codecProfile" id="codecProfile">
  <option value="main" <?=$options['codecProfile']=='main'?"selected":""?>>main</option>
  <option value="baseline" <?=$options['codecProfile']=='baseline'?"selected":""?>>baseline</option>  
</select>

<h5>H264 Video Codec Level</h5>
<input name="codecLevel" type="text" id="codecLevel" size="32" maxlength="64" value="<?=$options['codecLevel']?>"/> (1, 1b, 1.1, 1.2, 1.3, 2, 2.1, 2.2, 3, 3.1, 3.2, 4, 4.1, 4.2, 5, 5.1)

<h5>Sound Codec</h5>
<select name="soundCodec" id="soundCodec">
  <option value="Speex" <?=$options['soundCodec']=='Speex'?"selected":""?>>Speex</option>
  <option value="Nellymoser" <?=$options['soundCodec']=='Nellymoser'?"selected":""?>>Nellymoser</option>  
</select>

<h5>Speex Sound Quality</h5>
<input name="soundQuality" type="text" id="soundQuality" size="3" maxlength="3" value="<?=$options['soundQuality']?>"/> (0-10)

<h5>Nellymoser Sound Rate</h5>
<input name="micRate" type="text" id="micRate" size="3" maxlength="3" value="<?=$options['micRate']?>"/> (11/22/44)

<h5>Disable Embed/Link Codes</h5>
<select name="noEmbeds" id="noEmbeds">
  <option value="0" <?=$options['noEmbeds']?"":"selected"?>>No</option>
  <option value="1" <?=$options['noEmbeds']?"selected":""?>>Yes</option>
</select>
<h5>Show only Video</h5>
<select name="onlyVideo" id="onlyVideo">
  <option value="0" <?=$options['onlyVideo']?"":"onlyVideo"?>>No</option>
  <option value="1" <?=$options['onlyVideo']?"onlyVideo":""?>>Yes</option>
</select>
<h3>Video Watcher</h3>
<h5>Who can watch video</h5>
<select name="canWatch" id="canWatch">
  <option value="all" <?=$options['canWatch']=='all'?"selected":""?>>Anybody</option>
  <option value="members" <?=$options['canWatch']=='members'?"selected":""?>>All Members</option>
  <option value="list" <?=$options['canWatch']=='list'?"selected":""?>>Members in List</option>  
</select>
<h5>Members allowed to watch video (comma separated usernames)</h5>
<textarea name="watchList" cols="64" rows="3" id="watchList"><?=$options['watchList']?>
</textarea>

<div class="submit">
  <input type="submit" name="updateSettings" id="updateSettings" value="<?php _e('Update Settings', 'VWliveStreaming') ?>" />
</div>

</form>

	 <?
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

/* Only load code that needs BuddyPress to run once BP is loaded and initialized. */
function liveStreamingBP_init() 
{
    require( dirname( __FILE__ ) . '/bp.php' );
}

add_action( 'bp_init', 'liveStreamingBP_init' );
}



?>
