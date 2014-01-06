<?php
/*
Plugin Name: VideoWhisper Live Streaming
Plugin URI: http://www.videowhisper.com/?p=WordPress+Live+Streaming
Description: Live Streaming
Version: 4.27.3
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

			add_filter("the_content",array('VWliveStreaming','post_shortcodes'));


			add_action( 'wp_ajax_vwls_trans', array('VWliveStreaming','vwls_trans') );
			add_action( 'wp_ajax_nopriv_vwls_trans', array('VWliveStreaming','vwls_trans') );

			//check db
			$vw_db_version = "1.2";

			global $wpdb;
			$table_name = $wpdb->prefix . "vw_sessions";
			$table_name2 = $wpdb->prefix . "vw_lwsessions";
			$table_name3 = $wpdb->prefix . "vw_lsrooms";


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
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Video Whisper: Broadcaster Sessions - 2009@videowhisper.com' AUTO_INCREMENT=1 ;

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
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Video Whisper: Subscriber Sessions - 2009@videowhisper.com' AUTO_INCREMENT=1 ;

		DROP TABLE IF EXISTS `$table_name3`;
		CREATE TABLE `$table_name3` (
		  `id` int(11) NOT NULL auto_increment,
		  `name` varchar(64) NOT NULL,
		  `owner` int(11) NOT NULL,
		  `sdate` int(11) NOT NULL,
		  `edate` int(11) NOT NULL,
		  `btime` int(11) NOT NULL,
		  `wtime` int(11) NOT NULL,
		  `rdate` int(11) NOT NULL,
		  `status` tinyint(4) NOT NULL,
		  `type` tinyint(4) NOT NULL,
		  `options` TEXT,
		  PRIMARY KEY  (`id`),
		  KEY `name` (`name`),
		  KEY `status` (`status`),
		  KEY `type` (`type`),
		  KEY `owner` (`owner`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Video Whisper: Rooms - 2014@videowhisper.com' AUTO_INCREMENT=1 ;
		";

				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				dbDelta($sql);

				if (!$installed_ver) add_option("vw_db_version", $vw_db_version);
				else update_option( "vw_db_version", $vw_db_version );

				$wpdb->flush();
			}

		}



		function vwls_trans()
		{
				function sanV(&$var)
			{
			if (!$var) return;

			if (get_magic_quotes_gpc()) $var = stripslashes($var);

				$var=preg_replace("/\.{2,}/","",$var); //allow only 1 consecutive dot
				$var=preg_replace("/[^0-9a-zA-Z\.\-\s_]/","",$var); //do not allow special characters
		}


			ob_clean();

			$stream = $_GET['stream'];
			sanV($stream);
			
			if (!$stream)
			{
				echo "No stream name provided!";
				return;
			}

			$options = VWliveStreaming::getAdminOptions();
			$upath = dirname(__FILE__) . "/ls/uploads/$stream/";
			$rtmp_server=$options['rtmp_server'];

			switch ($_GET['task'])
			{
			case 'mp4':

				if ( !is_user_logged_in() )
				{
					echo "Not authorised!";
					exit;
				}

				$cmd = "ps aux | grep '/i_$stream -i rtmp'";
				exec($cmd, $output, $returnvalue);
				//var_dump($output);

				$transcoding = 0;

				foreach ($output as $line) if (strstr($line, "ffmpeg"))
					{
						$columns = preg_split('/\s+/',$line);
						echo "Transcoder Already Active (".$columns[1]." CPU: ".$columns[2]." Mem: ".$columns[3].")";
						$transcoding = 1;
					}

				if (!$transcoding)
				{
					echo "Starting transcoder for '$stream'... <BR>";
					$log_file =  $upath . "videowhisper_transcode.log";
					$cmd ="/usr/local/bin/ffmpeg -s 480x360 -r 15 -vb 512k -vcodec libx264 -coder 0 -bf 0 -analyzeduration 0 -level 3.1 -g 30 -maxrate 768k -acodec libfaac -ac 2 -ar 22050 -ab 96k -x264opts vbv-maxrate=364:qpmin=4:ref=4 -threads 4 -rtmp_pageurl \"http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] . "\" -rtmp_swfurl \"http://".$_SERVER['HTTP_HOST']."\" -f flv " . $rtmp_server . "/i_". $stream . " -i " . $rtmp_server ."/". $stream . " >&$log_file & ";
					//echo $cmd;
					exec($cmd, $output, $returnvalue);
					exec("echo '$cmd' >> $log_file.cmd", $output, $returnvalue);

					$cmd = "ps aux | grep '/i_$stream -i rtmp'";
					exec($cmd, $output, $returnvalue);
					//var_dump($output);

					foreach ($output as $line) if (strstr($line, "ffmpeg"))
						{
							$columns = preg_split('/\s+/',$line);
							echo "Transcoder Started (".$columns[1].")<BR>";
						}

				}
				echo "<BR><a target='_blank' href='".get_bloginfo( "url" ) . "/wp-admin/admin-ajax.php?action=vwls_trans&task=html5&stream=$stream'> Preview </a>";
				break;


			case 'close':
				if ( !is_user_logged_in() )
				{
					echo "Not authorised!";
					exit;
				}

				$cmd = "ps aux | grep '/i_$stream -i rtmp'";
				exec($cmd, $output, $returnvalue);
				//var_dump($output);

				$transcoding = 0;
				foreach ($output as $line) if (strstr($line, "ffmpeg"))
					{
						$columns = preg_split('/\s+/',$line);
						$cmd = "kill -9 " . $columns[1];
						exec($cmd, $output, $returnvalue);
						echo "<BR>Closing ".$columns[1]." CPU: ".$columns[2]." Mem: ".$columns[3];
						$transcoding = 1;
					}

				if (!$transcoding)
				{
					echo "Transcoder not found for $stream";
				}

				break;
			case "html5";
?>
<p>iOS live stream link (open with Safari or test with VLC): <a href="<?php echo $options['httpstreamer']?>i_<?php echo $stream?>/playlist.m3u8"><br />
  <?php echo $stream?> Video</a></p>


<p>HTML5 live video embed below should be accessible <u>only in <B>Safari</B> browser</u> (PC or iOS):</p>
<video width="480" height="360" autobuffer autoplay controls="controls">o
  <p>&nbsp;</p>
 <source src="<?php echo $options['httpstreamer']?>i_<?php echo $stream?>/playlist.m3u8" type='video/mp4'>
    <div class="fallback">
	    <p>You must have an HTML5 capable browser.</p>
	</div>
</video>
<p> Due to HTTP based live streaming technology limitations, video can have 15s or more latency. Use a browser with flash support for faster interactions based on RTMP. </p>
<p>Most devices other than iOS, support regular flash playback for live streams.</p>
</div>
<style type="text/css">
<!--
BODY
{
	margin:0px;
	background: #333;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 14px;
	color: #EEE;
	padding: 20px;
}

a {
	color: #F77;
	text-decoration: none;
}
-->
</style>
<?php

				break;
			}
			die;
		}

		function post_shortcodes($content)
		{

			$result = $content;

			if (strstr($content, "[videowhisper livesnapshots]")) //post requires listing channels
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
				$sql="DELETE FROM `$table_name2` WHERE edate < $exptime";
				$wpdb->query($sql);
				$wpdb->flush();

				$items =  $wpdb->get_results("SELECT * FROM `$table_name` where status='1' and type='1'");

				$livesnapshotsCode .=  "<div>Live Channels";
				if ($items) foreach ($items as $item)
					{
						$count =  $wpdb->get_results("SELECT count(*) as no FROM `$table_name2` where status='1' and type='1' and room='".$item->room."'");
						$urlc = $root_url . "wp-content/plugins/videowhisper-live-streaming-integration/ls/channel.php?n=".urlencode($item->room);
						$urli = $root_url . "wp-content/plugins/videowhisper-live-streaming-integration/ls/snapshots/".urlencode($item->room). ".jpg";
						if (!file_exists("wp-content/plugins/videowhisper-live-streaming-integration/ls/snapshots/".urlencode($item->room). ".jpg")) $urli = $root_url . "wp-content/plugins/videowhisper-live-streaming-integration/ls/snapshots/no_video.png";

						$livesnapshotsCode .= "<div style='border: 1px dotted #390; width: 240px; padding: 1px'><a href='$urlc'><IMG width='240px' SRC='$urli'><div ><B>".$item->room."</B> (".($count[0]->no+1).") ".($item->message?": ".$item->message:"") ."</div></a></div>";
					}
				else  $livesnapshotsCode .= "<div>No broadcasters online.</div>";

				$livesnapshotsCode .=  "</div> ";

				$options = get_option('VWliveStreamingOptions');
				$state = 'block' ;
				if (!$options['videowhisper']) $state = 'none';
				$livesnapshotsCode .= '<div id="VideoWhisper" style="display: ' . $state . ';"><p>Powered by VideoWhisper <a href="http://www.videowhisper.com/?p=WordPress+Live+Streaming">Live Video Streaming Software</a>.</p></div>';


				$result = str_replace("[videowhisper livesnapshots]", $livesnapshotsCode, $result);
			}

			return $result;
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

			$sql="DELETE FROM `$table_name2` WHERE edate < $exptime";
			$wpdb->query($sql);
			$wpdb->flush();

			$items =  $wpdb->get_results("SELECT * FROM `$table_name` where status='1' and type='1'");

			echo "<ul>";
			if ($items) foreach ($items as $item)
				{
					$count =  $wpdb->get_results("SELECT count(*) as no FROM `$table_name2` where status='1' and type='1' and room='".$item->room."'");

					echo "<li><a href='" . $root_url ."wp-content/plugins/videowhisper-live-streaming-integration/ls/channel.php?n=".urlencode($item->room)."'><B>".$item->room."</B> (".($count[0]->no+1).") ".($item->message?": ".$item->message:"") ."</a></li>";
				}
			else echo "<li>No broadcasters online.</li>";
			echo "</ul>";

			if (is_user_logged_in())
			{
				$userName =  $options['userName']; if (!$userName) $userName='user_nicename';
				global $current_user;
				get_currentuserinfo();
				if ($current_user->$userName) $username=urlencode($current_user->$userName);
				$username=preg_replace("/\.{2,}/","",$username);
				$username=preg_replace("/[^0-9a-zA-Z\.\-\s_]/","",$username);

				?><a href="<?php echo $root_url; ?>wp-content/plugins/videowhisper-live-streaming-integration/ls/?n=<?php echo $username ?>"><img src="<?php echo $root_url; ?>wp-content/plugins/videowhisper-live-streaming-integration/ls/templates/live/i_webcam.png" align="absmiddle" border="0">Video Broadcast</a>
	<?php
			}

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
				'userName' => 'user_nicename',
				'rtmp_server' => 'rtmp://localhost/videowhisper',
				'rtmp_amf' => 'AMF3',
				'httpstreamer' => 'http://localhost:1935/videowhisper/',

				'canBroadcast' => 'members',
				'broadcastList' => 'Super Admin, Administrator, Editor, Author',
				'canWatch' => 'all',
				'watchList' => 'Super Admin, Administrator, Editor, Author, Contributor, Subscriber',
				'onlyVideo' => '0',
				'noEmbeds' => '0',

				'premiumList' => 'Super Admin, Administrator, Editor, Author',
				'canWatchPremium' => 'all',
				'watchListPremium' => 'Super Admin, Administrator, Editor, Author, Contributor, Subscriber',
				'pLogo' => '1',
				'broadcastTime' => '0',
				'watchTime' => '0',
				'pBroadcastTime' => '0',
				'pWatchTime' => '0',
				'timeReset' => '30',

				'camResolution' => '480x360',
				'camFPS' => '15',

				'camBandwidth' => '40960',
				'camMaxBandwidth' => '81920',
				'pCamBandwidth' => '65536',
				'pCamMaxBandwidth' => '163840',
				'transcoding' => '1',

				'videoCodec'=>'H264',
				'codecProfile' => 'main',
				'codecLevel' => '3.1',

				'soundCodec'=> 'Speex',
				'soundQuality' => '9',
				'micRate' => '22',

				'overLogo' => $root_url .'wp-content/plugins/videowhisper-live-streaming-integration/ls/logo.png',
				'overLink' => 'http://www.videowhisper.com',

				'tokenKey' => 'VideoWhisper',
				'webKey' => 'VideoWhisper',

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

			if (isset($_POST))
			{

				foreach ($options as $key => $value)
					if (isset($_POST[$key])) $options[$key] = $_POST[$key];
					update_option('VWliveStreamingOptions', $options);
			}


			$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'server';
?>


<div class="wrap">
<?php screen_icon(); ?>
<h2>VideoWhisper Live Streaming Settings</h2>

<h2 class="nav-tab-wrapper">
	<a href="options-general.php?page=videowhisper_streaming.php&tab=server" class="nav-tab <?php echo $active_tab=='server'?'nav-tab-active':'';?>">Server</a>
	<a href="options-general.php?page=videowhisper_streaming.php&tab=general" class="nav-tab <?php echo $active_tab=='general'?'nav-tab-active':'';?>">Integration</a>
    <a href="options-general.php?page=videowhisper_streaming.php&tab=broadcaster" class="nav-tab <?php echo $active_tab=='broadcaster'?'nav-tab-active':'';?>">Broadcast</a>
    <a href="options-general.php?page=videowhisper_streaming.php&tab=premium" class="nav-tab <?php echo $active_tab=='premium'?'nav-tab-active':'';?>">Premium</a>
    <a href="options-general.php?page=videowhisper_streaming.php&tab=watcher" class="nav-tab <?php echo $active_tab=='watcher'?'nav-tab-active':'';?>">Watch</a>
    <a href="options-general.php?page=videowhisper_streaming.php&tab=stats" class="nav-tab <?php echo $active_tab=='stats'?'nav-tab-active':'';?>">Stats</a>
    <a href="options-general.php?page=videowhisper_streaming.php&tab=live" class="nav-tab <?php echo $active_tab=='live'?'nav-tab-active':'';?>">Live!</a>
</h2>

<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">

<?php
			switch ($active_tab)
			{
			case 'general':
?>
<h3>General Integration Settings</h3>
<h5>Username</h5>
<select name="userName" id="userName">
  <option value="display_name" <?php echo $options['userName']=='display_name'?"selected":""?>>Display Name</option>
  <option value="user_login" <?php echo $options['userName']=='user_login'?"selected":""?>>Login (Username)</option>
  <option value="user_nicename" <?php echo $options['userName']=='user_nicename'?"selected":""?>>Nicename</option>
</select>

<h5>Floating Logo / Watermark</h5>
<input name="overLogo" type="text" id="overLogo" size="80" maxlength="256" value="<?php echo $options['overLogo']?>"/>
<h5>Logo Link</h5>
<input name="overLink" type="text" id="overLink" size="80" maxlength="256" value="<?php echo $options['overLink']?>"/>

<h5>Show VideoWhisper Powered by</h5>
<select name="videowhisper" id="videowhisper">
  <option value="0" <?php echo $options['videowhisper']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['videowhisper']?"selected":""?>>Yes</option>
</select>

<?php
				break;
			case 'server':
?>
<h3>Server Settings</h3>
Configure options for live interactions and streaming.
<h5>RTMP Address</h5>
<p>To run this, make sure your hosting environment meets all <a href="http://www.videowhisper.com/?p=Requirements" target="_blank">requirements</a>.<BR>If you don't have a videowhisper rtmp address yet (from a managed rtmp host), go to <a href="http://www.videowhisper.com/?p=RTMP+Applications" target="_blank">RTMP Application   Setup</a> for  installation details.</p>
<input name="rtmp_server" type="text" id="rtmp_server" size="100" maxlength="256" value="<?php echo $options['rtmp_server']?>"/>
<?php submit_button(); ?>


<h5>HTTP Streaming URL</h5>
This is used for accessing transcoded streams on HLS playback. Usually available with <a href="http://www.videowhisper.com/?p=Wowza+Media+Server+Hosting">Wowza Hosting</a> .
<input name="httpstreamer" type="text" id="httpstreamer" size="100" maxlength="256" value="<?php echo $options['httpstreamer']?>"/>
<BR>External players and encoders (if enabled) are not monitored or controlled by this plugin.

<h5>Disable Bandwidth Detection</h5>
<p>Required on some rtmp servers that don't support bandwidth detection and return a Connection.Call.Fail error.</p>
<select name="disableBandwidthDetection" id="disableBandwidthDetection">
  <option value="0" <?php echo $options['disableBandwidthDetection']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['disableBandwidthDetection']?"selected":""?>>Yes</option>
</select>

<h5>Token Key</h5>
<input name="tokenKey" type="text" id="tokenKey" size="32" maxlength="64" value="<?php echo $options['tokenKey']?>"/>
<BR>A <a href="http://www.videowhisper.com/?p=RTMP+Applications#settings">secure token</a> can be used with Wowza Media Server.

<h5>Web Key</h5>
<input name="webKey" type="text" id="webKey" size="32" maxlength="64" value="<?php echo $options['webKey']?>"/>
<BR>A web key can be used for <a href="http://www.videochat-scripts.com/videowhisper-rtmp-web-authetication-check/">VideoWhisper RTMP Web Session Check</a>.
<?php
				$root_url = get_bloginfo( "url" ) . "/wp-content/plugins/videowhisper-live-streaming-integration/ls/";
				echo "<BR>webLogin:  $root_url"."rtmp_login.php?s=";
				echo "<BR>webLogout: $root_url"."rtmp_logout.php?s=";
?>

<h5>RTMFP Address</h5>
<p> Get your own independent RTMFP address by registering for a free <a href="https://www.adobe.com/cfusion/entitlement/index.cfm?e=cirrus" target="_blank">Adobe Cirrus developer key</a>. This is required for P2P support.</p>
<input name="serverRTMFP" type="text" id="serverRTMFP" size="80" maxlength="256" value="<?php echo $options['serverRTMFP']?>"/>
<h5>P2P Group</h5>
<input name="p2pGroup" type="text" id="p2pGroup" size="32" maxlength="64" value="<?php echo $options['p2pGroup']?>"/>
<h5>Support RTMP Streaming</h5>
<select name="supportRTMP" id="supportRTMP">
  <option value="0" <?php echo $options['supportRTMP']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['supportRTMP']?"selected":""?>>Yes</option>
</select>
<h5>Always do RTMP Streaming</h5>
<p>Enable this if you want all streams to be published to server, no matter if there are registered subscribers or not (in example if you're using server side video archiving and need all streams published for recording).</p>
<select name="alwaysRTMP" id="alwaysRTMP">
  <option value="0" <?php echo $options['alwaysRTMP']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['alwaysRTMP']?"selected":""?>>Yes</option>
</select>
<h5>Support P2P Streaming</h5>
<select name="supportP2P" id="supportP2P">
  <option value="0" <?php echo $options['supportP2P']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['supportP2P']?"selected":""?>>Yes</option>
</select>
<h5>Always do P2P Streaming</h5>
<select name="alwaysP2P" id="alwaysP2P">
  <option value="0" <?php echo $options['alwaysP2P']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['alwaysP2P']?"selected":""?>>Yes</option>
</select>

<?php
				break;
			case 'broadcaster':
?>
<h3>Video Broadcasting</h3>
Options for video broadcasting.
<h5>Who can broadcast video channels</h5>
<select name="canBroadcast" id="canBroadcast">
  <option value="members" <?php echo $options['canBroadcast']=='members'?"selected":""?>>All Members</option>
  <option value="list" <?php echo $options['canBroadcast']=='list'?"selected":""?>>Members in List</option>
</select>
<h5>Members allowed to broadcast video (comma separated user names, roles, emails, IDs)</h5>
<textarea name="broadcastList" cols="64" rows="3" id="broadcastList"><?php echo $options['broadcastList']?>
</textarea>


<h5>Maximum Broadcating Time (0 = unlimited)</h5>
<input name="broadcastTime" type="text" id="broadcastTime" size="7" maxlength="7" value="<?php echo $options['broadcastTime']?>"/> (minutes/period)

<h5>Maximum Channel Watch Time (total cumulated view time, 0 = unlimited)</h5>
<input name="watchTime" type="text" id="watchTime" size="10" maxlength="10" value="<?php echo $options['watchTime']?>"/> (minutes/period)

<h5>Usage Period Reset (0 = never)</h5>
<input name="timeReset" type="text" id="timeReset" size="4" maxlength="4" value="<?php echo $options['timeReset']?>"/> (days)


<h5>Default Webcam Resolution</h5>
<select name="camResolution" id="camResolution">
<?php
				foreach (array('160x120','320x240','480x360', '640x480', '720x480', '720x576', '1280x720', '1440x1080', '1920x1080') as $optItm)
				{
?>
  <option value="<?php echo $optItm;?>" <?php echo $options['camResolution']==$optItm?"selected":""?>> <?php echo $optItm;?> </option>
  <?php
				}
?>
 </select>

<h5>Default Webcam Frames Per Second</h5>
<select name="camFPS" id="camFPS">
<?php
				foreach (array('1','8','10','12','15','29','30','60') as $optItm)
				{
?>
  <option value="<?php echo $optItm;?>" <?php echo $options['camFPS']==$optItm?"selected":""?>> <?php echo $optItm;?> </option>
  <?php
				}
?>
 </select>


<h5>Video Stream Bandwidth</h5>
<input name="camBandwidth" type="text" id="camBandwidth" size="7" maxlength="7" value="<?php echo $options['camBandwidth']?>"/> (bytes/s)
<h5>Maximum Video Stream Bandwidth (at runtime)</h5>
<input name="camMaxBandwidth" type="text" id="camMaxBandwidth" size="7" maxlength="7" value="<?php echo $options['camMaxBandwidth']?>"/> (bytes/s)

<h5>Video Codec</h5>
<select name="videoCodec" id="videoCodec">
  <option value="H264" <?php echo $options['videoCodec']=='H264'?"selected":""?>>H264</option>
  <option value="H263" <?php echo $options['videoCodec']=='H263'?"selected":""?>>H263</option>
</select>

<h5>H264 Video Codec Profile</h5>
<select name="codecProfile" id="codecProfile">
  <option value="main" <?php echo $options['codecProfile']=='main'?"selected":""?>>main</option>
  <option value="baseline" <?php echo $options['codecProfile']=='baseline'?"selected":""?>>baseline</option>
</select>

<h5>H264 Video Codec Level</h5>
<select name="codecLevel" id="codecLevel">
<?php
				foreach (array('1', '1b', '1.1', '1.2', '1.3', '2', '2.1', '2.2', '3', '3.1', '3.2', '4', '4.1', '4.2', '5', '5.1') as $optItm)
				{
?>
  <option value="<?php echo $optItm;?>" <?php echo $options['codecLevel']==$optItm?"selected":""?>> <?php echo $optItm;?> </option>
  <?php
				}
?>
 </select>

<h5>Sound Codec</h5>
<select name="soundCodec" id="soundCodec">
  <option value="Speex" <?php echo $options['soundCodec']=='Speex'?"selected":""?>>Speex</option>
  <option value="Nellymoser" <?php echo $options['soundCodec']=='Nellymoser'?"selected":""?>>Nellymoser</option>
</select>

<h5>Speex Sound Quality</h5>
<select name="soundQuality" id="soundQuality">
<?php
				foreach (array('0', '1','2','3','4','5','6','7','8','9','10') as $optItm)
				{
?>
  <option value="<?php echo $optItm;?>" <?php echo $options['soundQuality']==$optItm?"selected":""?>> <?php echo $optItm;?> </option>
  <?php
				}
?>
 </select>

<h5>Nellymoser Sound Rate</h5>
<select name="micRate" id="micRate">
<?php
				foreach (array('5', '8', '11', '22','44') as $optItm)
				{
?>
  <option value="<?php echo $optItm;?>" <?php echo $options['micRate']==$optItm?"selected":""?>> <?php echo $optItm;?> </option>
  <?php
				}
?>
 </select>

<h5>Disable Embed/Link Codes</h5>
<select name="noEmbeds" id="noEmbeds">
  <option value="0" <?php echo $options['noEmbeds']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['noEmbeds']?"selected":""?>>Yes</option>
</select>
<h5>Show only Video</h5>
<select name="onlyVideo" id="onlyVideo">
  <option value="0" <?php echo $options['onlyVideo']?"":"onlyVideo"?>>No</option>
  <option value="1" <?php echo $options['onlyVideo']?"onlyVideo":""?>>Yes</option>
</select>
<?php
				break;
			case 'premium':
?>
<h3>Premium Channels</h3>
Options for premium channels. Premium channels have special settings and features that can be defined here.
<h5>Members that broadcast premium channels (Premium members: comma separated user names, roles, emails, IDs)</h5>
<textarea name="premiumList" cols="64" rows="3" id="premiumList"><?php echo $options['premiumList']?>
</textarea>


<h5>Who can watch premium channels</h5>
<select name="canWatchPremium" id="canWatchPremium">
  <option value="all" <?php echo $options['canWatchPremium']=='all'?"selected":""?>>Anybody</option>
  <option value="members" <?php echo $options['canWatchPremium']=='members'?"selected":""?>>All Members</option>
  <option value="list" <?php echo $options['canWatchPremium']=='list'?"selected":""?>>Members in List</option>
</select>
<h5>Members allowed to watch premium channels (comma separated usernames, roles, IDs)</h5>
<textarea name="watchListPremium" cols="64" rows="3" id="watchListPremium"><?php echo $options['watchListPremium']?>
</textarea>

<h5>Show Floating Logo/Watermark</h5>
<select name="pLogo" id="pLogo">
  <option value="0" <?php echo $options['pLogo']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['pLogo']?"selected":""?>>Yes</option>
</select>

<h5>Enable Transcoding</h5>
<select name="transcoding" id="transcoding">
  <option value="0" <?php echo $options['transcoding']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['transcoding']?"selected":""?>>Yes</option>
</select>

<h5>Maximum Broadcating Time (0 = unlimited)</h5>
<input name="pBroadcastTime" type="text" id="pBroadcastTime" size="7" maxlength="7" value="<?php echo $options['pBroadcastTime']?>"/> (minutes/period)

<h5>Maximum Channel Watch Time (total cumulated view time, 0 = unlimited)</h5>
<input name="pWatchTime" type="text" id="pWatchTime" size="10" maxlength="10" value="<?php echo $options['pWatchTime']?>"/> (minutes/period)

<h5>Usage Period Reset (same as for regular channels, 0 = never)</h5>
<input name="timeReset" type="text" id="timeReset" size="4" maxlength="4" value="<?php echo $options['timeReset']?>"/> (days)

<h5>Video Stream Bandwidth</h5>
<input name="pCamBandwidth" type="text" id="pCamBandwidth" size="7" maxlength="7" value="<?php echo $options['pCamBandwidth']?>"/> (bytes/s)

<h5>Maximum Video Stream Bandwidth (at runtime)</h5>
<input name="pCamMaxBandwidth" type="text" id="pCamMaxBandwidth" size="7" maxlength="7" value="<?php echo $options['pCamMaxBandwidth']?>"/> (bytes/s)

<?php
				break;
			case 'watcher':
?>
<h3>Video Watcher</h3>
Settings for video subscribers that watch the live channels using watch or plain video interface.
<h5>Who can watch video</h5>
<select name="canWatch" id="canWatch">
  <option value="all" <?php echo $options['canWatch']=='all'?"selected":""?>>Anybody</option>
  <option value="members" <?php echo $options['canWatch']=='members'?"selected":""?>>All Members</option>
  <option value="list" <?php echo $options['canWatch']=='list'?"selected":""?>>Members in List</option>
</select>
<h5>Members allowed to watch video (comma separated usernames, roles, IDs)</h5>
<textarea name="watchList" cols="64" rows="3" id="watchList"><?php echo $options['watchList']?>
</textarea>
<?php

				break;
			case 'stats':
?>
<h3>Channels Stats</h3>
<?php

				function format_time($t,$f=':') // t = seconds, f = separator
					{
					return sprintf("%02d%s%02d%s%02d", floor($t/3600), $f, ($t/60)%60, $f, $t%60);
				}

				function format_age($t)
				{
					return sprintf("%d%s%d%s%d%s", floor($t/86400), 'd ', ($t/3600)%24,'h ', ($t/60)%60,'m');
				}

				global $wpdb;
				$table_name = $wpdb->prefix . "vw_sessions";
				$table_name2 = $wpdb->prefix . "vw_lwsessions";
				$table_name3 = $wpdb->prefix . "vw_lsrooms";

				$items =  $wpdb->get_results("SELECT * FROM `$table_name3` ORDER BY edate DESC LIMIT 0, 100");
				echo "<table class='wp-list-table widefat'><thead><tr><th>Channel</th><th>Last Access</th><th>Broadcast Time</th><th>Watch Time</th><th>Last Reset</th><th>Type</th></tr></thead>";
				if ($items) foreach ($items as $item)
						echo "<tr><th>".$item->name."</th><td>".format_age(time() - $item->edate)."</td><td>".format_time($item->btime)."</td><td>".format_time($item->wtime)."</td><td>".format_age(time() - $item->rdate)."</td><td>".($item->type==2?"Premium":"Standard")."</td></tr>";
					echo "</table>";
				break;


			case 'live':
				$root_url = get_bloginfo( "url" ) . "/";
				$userName =  $options['userName']; if (!$userName) $userName='user_nicename';
				global $current_user;
				get_currentuserinfo();
				if ($current_user->$userName) $username=urlencode($current_user->$userName);
				$username=preg_replace("/\.{2,}/","",$username);
				$username=preg_replace("/[^0-9a-zA-Z\.\-\s_]/","",$username);

?>
<h3>Channel '<?php echo $username; ?>'</h3>
Each user can have own channel. Channel name is based on username.
<ul>
<li>
<a href="<?php echo $root_url; ?>wp-content/plugins/videowhisper-live-streaming-integration/ls/?n=<?php echo $username; ?>"><img src="<?php echo $root_url; ?>wp-content/plugins/videowhisper-live-streaming-integration/ls/templates/live/i_webcam.png" align="absmiddle" border="0">Start Broadcasting</a>
</li>
<li>
<a href="<?php echo $root_url; ?>wp-content/plugins/videowhisper-live-streaming-integration/ls/channel.php?n=<?php echo $username; ?>"><img src="<?php echo $root_url; ?>wp-content/plugins/videowhisper-live-streaming-integration/ls/templates/live/i_uvideo.png" align="absmiddle" border="0">View Channel</a>
</li>
</ul>
<p>To allow users to broadcast from frontend (as configured in settings), <a href='widgets.php'>enable the widget</a>.
</p>
<h4>Online Channels</h4>
<?php

				global $wpdb;
				$table_name = $wpdb->prefix . "vw_sessions";
				$table_name2 = $wpdb->prefix . "vw_lwsessions";
				$table_name3 = $wpdb->prefix . "vw_lsrooms";

				//clean recordings
				$exptime=time()-30;
				$sql="DELETE FROM `$table_name` WHERE edate < $exptime";
				$wpdb->query($sql);
				$wpdb->flush();

				$sql="DELETE FROM `$table_name2` WHERE edate < $exptime";
				$wpdb->query($sql);
				$wpdb->flush();

				$items =  $wpdb->get_results("SELECT * FROM `$table_name` where status='1' and type='1' LIMIT 0,50");

				echo "<ul>";
				if ($items) foreach ($items as $item)
					{
						$count =  $wpdb->get_results("SELECT count(*) as no FROM `$table_name2` where status='1' and type='1' and room='".$item->room."'");

						echo "<li><a href='" . $root_url ."wp-content/plugins/videowhisper-live-streaming-integration/ls/channel.php?n=".urlencode($item->room)."'><B>".$item->room."</B> (".($count[0]->no+1).") ".($item->message?": ".$item->message:"") ."</a></li>";
					}
				else echo "<li>No broadcasters online.</li>";
				echo "</ul>";

				break;
			}

			if ($active_tab!='live' && $active_tab!='stats' ) submit_button(); ?>

</form>
</div>
	 <?php
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

	if (class_exists('BP_Group_Extension')) add_action( 'bp_init', 'liveStreamingBP_init' );
}



?>
