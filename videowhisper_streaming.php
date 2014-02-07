<?php
/*
Plugin Name: VideoWhisper Live Streaming
Plugin URI: http://www.videowhisper.com/?p=WordPress+Live+Streaming
Description: Live Streaming
Version: 4.29.5
Author: VideoWhisper.com
Author URI: http://www.videowhisper.com/
Contributors: videowhisper, VideoWhisper.com
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if (!class_exists("VWliveStreaming"))
{
    class VWliveStreaming {

        function VWliveStreaming() { //constructor

        }

        static function install() {
            // do not generate any output here

            flush_rewrite_rules();

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

            add_filter("the_content", array('VWliveStreaming','channel_page'));
            add_filter( 'query_vars', array('VWliveStreaming','channel_query_vars'));


            //shortcodes
            add_shortcode('videowhisper_livesnapshots', array( 'VWliveStreaming', 'shortcode_livesnapshots'));
            add_shortcode('videowhisper_broadcast', array( 'VWliveStreaming', 'shortcode_broadcast'));
            add_shortcode('videowhisper_watch', array( 'VWliveStreaming', 'shortcode_watch'));
            add_shortcode('videowhisper_video', array( 'VWliveStreaming', 'shortcode_video'));
            add_shortcode('videowhisper_hls', array( 'VWliveStreaming', 'shortcode_hls'));
            add_shortcode('videowhisper_channel_manage',array( 'VWliveStreaming', 'shortcode_manage'));
            add_shortcode('videowhisper_channels',array( 'VWliveStreaming', 'shortcode_channels'));


            //ajax
            add_action( 'wp_ajax_vwls_trans', array('VWliveStreaming','vwls_trans') );
            add_action( 'wp_ajax_nopriv_vwls_trans', array('VWliveStreaming','vwls_trans'));
            add_action( 'wp_ajax_vwls_broadcast', array('VWliveStreaming','vwls_broadcast'));

            add_action( 'wp_ajax_vwls', array('VWliveStreaming','vwls_calls'));
            add_action( 'wp_ajax_nopriv_vwls', array('VWliveStreaming','vwls_calls'));

            add_action( 'wp_ajax_vwls_channels', array('VWliveStreaming','vwls_channels'));
            add_action( 'wp_ajax_nopriv_vwls_channels', array('VWliveStreaming','vwls_channels'));


            //update page if not exists or deleted
            $page_id = get_option("vwls_page_manage");
            $page_id2 = get_option("vwls_page_channels");

            if (!$page_id || $page_id == "-1" || !$page_id2 || $page_id2 == "-1")  add_action('wp_loaded', array('VWliveStreaming','updatePages'));

           //check db and update if necessary
            $vw_db_version = "1.2";

            global $wpdb;
            $table_name = $wpdb->prefix . "vw_sessions";
            $table_name2 = $wpdb->prefix . "vw_lwsessions";
            $table_name3 = $wpdb->prefix . "vw_lsrooms";


            $installed_ver = get_option( "vwls_db_version" );


            if( $installed_ver != $vw_db_version )
            {

                //echo "---$installed_ver != $vw_db_version---";

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

                if (!$installed_ver) add_option("vwls_db_version", $vw_db_version);
                else update_option( "vwls_db_version", $vw_db_version );

                $wpdb->flush();
            }


        }


        function updatePages()
        {


            $options = VWliveStreaming::getAdminOptions();

            //if not disabled create
            if ($options['disablePage']=='0')
            {
                global $user_ID;
                $page = array();
                $page['post_type']    = 'page';
                $page['post_content'] = '[videowhisper_channel_manage]';
                $page['post_parent']  = 0;
                $page['post_author']  = $user_ID;
                $page['post_status']  = 'publish';
                $page['post_title']   = 'Broadcast Live';

                $page_id = get_option("vwls_page_manage");
                if ($page_id>0) $page['ID'] = $page_id;

                $pageid = wp_insert_post ($page);
                update_option( "vwls_page_manage", $pageid);
            }

            if ($options['disablePageC']=='0')
            {
                global $user_ID;
                $page = array();
                $page['post_type']    = 'page';
                $page['post_content'] = '[videowhisper_channels]';
                $page['post_parent']  = 0;
                $page['post_author']  = $user_ID;
                $page['post_status']  = 'publish';
                $page['post_title']   = 'Channels';

                $page_id = get_option("vwls_page_channels");
                if ($page_id>0) $page['ID'] = $page_id;

                $pageid = wp_insert_post ($page);
                update_option( "vwls_page_channels", $pageid);
            }

        }

        function deletePages()
        {
            $options = VWliveStreaming::getAdminOptions();

            if ($options['disablePage'])
            {
                $page_id = get_option("vwls_page_manage");
                if ($page_id > 0)
                {
                    wp_delete_post($page_id);
                    update_option( "vwls_page_manage", -1);
                }
            }

            if ($options['disablePageC'])
            {
                $page_id = get_option("vwls_page_channels");
                if ($page_id > 0)
                {
                    wp_delete_post($page_id);
                    update_option( "vwls_page_channels", -1);
                }
            }

        }


        //if any key matches any listing
        function inList($keys, $data)
        {
            if (!$keys) return 0;

            $list=explode(",", strtolower(trim($data)));

            foreach ($keys as $key)
                foreach ($list as $listing)
                    if ( strtolower(trim($key)) == trim($listing) ) return 1;

                    return 0;
        }


        function shortcode_manage()
        {

            function count_user_posts_by_type( $userid, $post_type = 'channel' )
            {
                global $wpdb;
                $where = get_posts_by_author_sql( $post_type, true, $userid );
                $count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts $where" );
                return apply_filters( 'get_usernumposts', $count, $userid );
            }

            //can user create room?
            $options = get_option('VWliveStreamingOptions');
            $maxChannels = $options['maxChannels'];

            $canBroadcast = $options['canBroadcast'];
            $broadcastList = $options['broadcastList'];
            $userName =  $options['userName']; if (!$userName) $userName='user_nicename';

            $loggedin=0;

            global $current_user;
            get_currentuserinfo();
            if ($current_user->$userName) $username = $current_user->$userName;

            //access keys
            $userkeys = $current_user->roles;
            $userkeys[] = $current_user->user_login;
            $userkeys[] = $current_user->ID;
            $userkeys[] = $current_user->user_email;
            $userkeys[] = $current_user->display_name;

            switch ($canBroadcast)
            {
            case "members":
                if ($username) $loggedin=1;
                else $htmlCode .= "<a href=\"/\">Please login first or register an account if you don't have one!</a>";
                break;
            case "list";
                if ($username)
                    if (VWliveStreaming::inList($userkeys, $broadcastList)) $loggedin=1;
                    else $htmlCode .= "<a href=\"/\">$username, you are not allowed to setup rooms.</a>";
                    else $htmlCode .= "<a href=\"/\">Please login first or register an account if you don't have one!</a>";
                    break;
            }

            if (!$loggedin)
            {
                $htmlCode .='<p>This pages allows creating and managing broadcasting channels for register members that have this feature enabled.</p>';
                return $htmlCode;
            }

            $this_page    =   "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $channels_count = count_user_posts_by_type($current_user->ID, 'channel');

            //setup
            $postID = $_POST['editPost'];

            if ($postID)
            {
                if ($postID <= 0 && $channels_count >= $maxChannels)
                    $htmlCode .= "<div class='error'>Maximum ". $options['maxChannels']." channels allowed per user!</div>";
                else
                {
                    $name = sanitize_file_name($_POST['newname']);

                    $post = array(
                        'post_content'   => sanitize_text_field($_POST['description']),
                        'post_name'      => $name,
                        'post_title'     => $name,
                        'post_author'    => $current_user->ID,
                        'post_type'      => 'channel',
                        'post_status'    => 'publish'
                    );

                    $category = (int) $_POST['newcategory'];

                    if ($postID>0)
                    {
                        $channel = get_post( $postID );
                        if ($channel->post_author == $current_user->ID)                    $post['ID'] = $postID;
                        else return "<div class='error'>Not allowed!</div>";
                        $htmlCode .= "<div class='update'>Channel $name was updated!</div>";
                    }
                    else $htmlCode .= "<div class='update'>Channel $name was created!</div>";

                    $postID = wp_insert_post($post);
                    if ($postID) wp_set_post_categories($postID, array($category));

                    $channels_count = count_user_posts_by_type($current_user->ID, 'channel');

                }

            }


            //list
            $htmlCode .= "<h3>My Channels ($channels_count/$maxChannels)</h3>";
            $args = array(
                'author'           => $current_user->ID,
                'orderby'          => 'post_date',
                'order'            => 'DESC',
                'post_type'        => 'channel',
            );

            $channels = get_posts( $args );
            if (count($channels))
            {
                require_once( ABSPATH . 'wp-admin/includes/image.php' );

                $htmlCode .= '<table>';

                foreach ($channels as $channel)
                {


                    //update thumb
                    $stream = sanitize_file_name(get_the_title($channel->ID));
                    $dir = $options['uploadsPath']. "/_snapshots";
                    $thumbFilename = "$dir/$stream.jpg";

                    if ( file_exists($thumbFilename) && (get_the_post_thumbnail( $postID ) == ''))
                    {
                        $wp_filetype = wp_check_filetype(basename($thumbFilename), null );

                        $attachment = array(
                            'guid' => $thumbFilename,
                            'post_mime_type' => $wp_filetype['type'],
                            'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $thumbFilename, ".jpg" ) ),
                            'post_content' => '',
                            'post_status' => 'inherit'
                        );

                        $attach_id = wp_insert_attachment( $attachment, $thumbFilename, $postID );
                    }
                    else
                    {
                        $attach_id = get_post_thumbnail_id($channel->ID );
                        $thumbFilename = get_attached_file($attach_id);
                    }

                    $attach_data = wp_generate_attachment_metadata( $attach_id, $thumbFilename );
                    wp_update_attachment_metadata( $attach_id, $attach_data );


                    $htmlCode .= '<tr><td><a href="' . get_permalink($channel->ID) . '"><h4>' . $channel->post_title . '</h4>' .  get_the_post_thumbnail($channel->ID, 'medium') . '</a></td>';
                    $htmlCode .= '<td width="210px">';
                    $htmlCode .= '<BR><BR><a class="videowhisperButton" href="' . get_permalink($channel->ID) . '/broadcast"> <img src="' .plugin_dir_url(__FILE__). 'ls/templates/live/i_webcam.png" align="absmiddle">Broadcast</a>';
                    $htmlCode .= '<BR> <a class="videowhisperButton" href="' . get_permalink($channel->ID) . '"> <img src="' .plugin_dir_url(__FILE__). 'ls/templates/live/i_uchat.png" align="absmiddle">Watch</a>';
                    $htmlCode .= '<BR> <a class="videowhisperButton" href="' . get_permalink($channel->ID) . '/video"> <img src="' .plugin_dir_url(__FILE__). 'ls/templates/live/i_uvideo.png" align="absmiddle">Video</a>';
                    $htmlCode .= '<BR> <a class="videowhisperButton" href="' . $this_page . '?editChannel=' . $channel->ID . '"> <img src="' .plugin_dir_url(__FILE__). 'ls/templates/live/i_tools.png" align="absmiddle">Setup</a>';
                    $htmlCode .= '</td></tr>';
                }
                $htmlCode .= '</table>';
            }
            else
                $htmlCode .= "<div class='warning'>You don't have any channels, yet!</div>";

            $htmlCode .= <<<HTMLCODE
<style type="text/css">
.videowhisperButton {
	-moz-box-shadow:inset 0px 1px 0px 0px #ffffff;
	-webkit-box-shadow:inset 0px 1px 0px 0px #ffffff;
	box-shadow:inset 0px 1px 0px 0px #ffffff;
	-webkit-border-top-left-radius:6px;
	-moz-border-radius-topleft:6px;
	border-top-left-radius:6px;
	-webkit-border-top-right-radius:6px;
	-moz-border-radius-topright:6px;
	border-top-right-radius:6px;
	-webkit-border-bottom-right-radius:6px;
	-moz-border-radius-bottomright:6px;
	border-bottom-right-radius:6px;
	-webkit-border-bottom-left-radius:6px;
	-moz-border-radius-bottomleft:6px;
	border-bottom-left-radius:6px;
	text-indent:0;
	border:1px solid #dcdcdc;
	display:inline-block;
	color:#666666;
	font-family:Verdana;
	font-size:15px;
	font-weight:bold;
	font-style:normal;
	height:50px;
	line-height:50px;
	width:200px;
	text-decoration:none;
	text-align:center;
	text-shadow:1px 1px 0px #ffffff;
	background-color:#e9e9e9;

}

.videowhisperButton:hover {
	background-color:#f9f9f9;
}

.videowhisperButton:active {
	position:relative;
	top:1px;
}
</style>

HTMLCODE;

            //setup
            $newCat = -1;

            if ($_GET['editChannel'])
            {
                $editPost = (int) $_GET['editChannel'];

                $channel = get_post( $editPost );
                if ($channel->post_author != $current_user->ID) return "<div class='error'>Not allowed!</div>";

                $newDescription = $channel->post_content;
                $newName = $channel->post_title;

                $cats = wp_get_post_categories( $editPost);
                if (count($cats)) $newCat = array_pop($cats);
            }

            if (!$editPost) {
                $editPost = -1;
                $newName = sanitize_file_name($username);
                if ($channels_count) $newName .= '_' . base_convert(time()-1225000000,10,36);
            }

            $categories = wp_dropdown_categories('show_count=1&hierarchical=1&echo=0&name=newcategory&selected=' . $newCat);

            if ($editPost > 0 || $channels_count < $maxChannels)
                $htmlCode .= <<<HTMLCODE
<script language="JavaScript">
		function censorName()
			{
				document.adminForm.room.value = document.adminForm.room.value.replace(/^[\s]+|[\s]+$/g, '');
				document.adminForm.room.value = document.adminForm.room.value.replace(/[^0-9a-zA-Z_\-]+/g, '-');
				document.adminForm.room.value = document.adminForm.room.value.replace(/\-+/g, '-');
				document.adminForm.room.value = document.adminForm.room.value.replace(/^\-+|\-+$/g, '');
				if (document.adminForm.room.value.length>0) return true;
				else
				{
				alert("A channel name is required!");
				return false;
				}
			}
</script>


<h3>Setup Channel</h3>
<form method="post" action="$this_page"  name="adminForm">
<table>
<tr><td>Name</td><td><input name="newname" type="text" id="newname" value="$newName" size="20" maxlength="64" onChange="censorName()"/></td></tr>
<tr><td>Category</td><td>$categories</td></tr>
<tr><td>Description</td><td><textarea name='description' id='description'>$newDescription</textarea></td></tr>
<tr><td></td><td><input class="videowhisperButton" type="submit" name="button" id="button" value="Setup" /></td></tr>
</table>
<input type="hidden" name="editPost" id="editPost" value="$editPost" />
</form>

HTMLCODE;


            return $htmlCode;

        }


        function shortcode_channels($atts)
        {
            $ajaxurl = admin_url() . 'admin-ajax.php?action=vwls_channels&pp=' . $atts['perPage'];


            $htmlCode = <<<HTMLCODE
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script>
var aurl = '$ajaxurl';

	function loadChannels(){
		$.ajax({
			url: aurl,
			success: function(data) {
				$("#videowhisperChannels").html(data);
			}
		});
	}

	$(function(){
		loadChannels();
		setInterval("loadChannels()", 10000);
	});

</script>

<div id="videowhisperChannels">
    Loading Channels...
</div>

<style type="text/css">

.videowhisperChannel
{
position: relative;
display:inline-block;

	border:1px solid #dcdcdc;
	background-color:#777;
	padding: 0px;
	margin: 2px;
}

.videowhisperChannel:hover {
	background-color:#fff;
}

.videowhisperChannel IMG
{
padding: 0px;
margin: 0px;
border: 0px;
}

.videowhisperTitle
{
position: absolute;
top:5px;
left:5px;
font-size: 20px;
color: #FFF;
text-shadow:1px 1px 1px #333;
}

.videowhisperTime
{
position: absolute;
bottom:8px;
left:5px;
font-size: 15px;
color: #FFF;
text-shadow:1px 1px 1px #333;
}

.videowhisperButton {
	-moz-box-shadow:inset 0px 1px 0px 0px #ffffff;
	-webkit-box-shadow:inset 0px 1px 0px 0px #ffffff;
	box-shadow:inset 0px 1px 0px 0px #ffffff;
	-webkit-border-top-left-radius:6px;
	-moz-border-radius-topleft:6px;
	border-top-left-radius:6px;
	-webkit-border-top-right-radius:6px;
	-moz-border-radius-topright:6px;
	border-top-right-radius:6px;
	-webkit-border-bottom-right-radius:6px;
	-moz-border-radius-bottomright:6px;
	border-bottom-right-radius:6px;
	-webkit-border-bottom-left-radius:6px;
	-moz-border-radius-bottomleft:6px;
	border-bottom-left-radius:6px;
	text-indent:0;
	border:1px solid #dcdcdc;
	display:inline-block;
	color:#666666;
	font-family:Verdana;
	font-size:15px;
	font-weight:bold;
	font-style:normal;
	height:50px;
	line-height:50px;
	width:200px;
	text-decoration:none;
	text-align:center;
	text-shadow:1px 1px 0px #ffffff;
	background-color:#e9e9e9;

}

.videowhisperButton:hover {
	background-color:#f9f9f9;
}

.videowhisperButton:active {
	position:relative;
	top:1px;
}


</style>

HTMLCODE;

            return $htmlCode;
        }

        function shortcode_watch($atts)
        {
            if (is_single())
                if (get_post_type( get_the_ID() ) == 'channel') $stream = get_the_title(get_the_ID());

                if (!$stream) $stream = $atts['channel']; //parameter channel="name"
                if (!$stream) $stream = $_GET['n'];

                $stream = sanitize_file_name($stream);


            if (!$stream)
            {
                return "Watch Error: Missing channel name!";
            }


            $swfurl = plugin_dir_url(__FILE__) . "ls/live_watch.swf?n=" . urlencode($stream);
            $swfurl .= "&prefix=" . urlencode(admin_url() . 'admin-ajax.php?action=vwls&task=');
            $swfurl .= '&extension='.urlencode('_none_');
            $swfurl .= '&ws_res=' . urlencode( plugin_dir_url(__FILE__) . 'ls/');

            $bgcolor="#333333";

            $htmlCode = <<<HTMLCODE
<div id="videowhisper_container_$stream">
<object id="videowhisper_watch_$stream" width="100%" height="100%" type="application/x-shockwave-flash" data="$swfurl">
<param name="movie" value="$swfurl"></param><param bgcolor="$bgcolor"><param name="scale" value="noscale" /> </param><param name="salign" value="lt"></param><param name="allowFullScreen"
value="true"></param><param name="allowscriptaccess" value="always"></param>
</object>
</div>

<br style="clear:both" />

<style type="text/css">
<!--

#videowhisper_container_$stream
{
width: 100%;
height: 400px;
border: solid 3px #999;
}

-->
</style>

HTMLCODE;

            return $htmlCode;
        }



        function shortcode_hls($atts)
        {
            if (is_single())
                if (get_post_type( get_the_ID() ) == 'channel') $stream = get_the_title(get_the_ID());

                if (!$stream) $stream = $atts['channel']; //parameter channel="name"
                if (!$stream) $stream = $_GET['n'];

                $stream = sanitize_file_name($stream);

            $width=$atts['width']; if (!$width) $width = "100%";
            $height=$atts['height']; if (!$height) $height = "100%";

            if (!$stream)
            {
                return "Watch HLS Error: Missing channel name!";
            }

            $options = VWliveStreaming::getAdminOptions();

            $htmlCode = <<<HTMLCODE
<video id="videowhisper_hls_$stream" width="$width" height="$height" autobuffer autoplay controls>
 <source src="${options['httpstreamer']}i_$stream/playlist.m3u8" type='video/mp4'>
    <div class="fallback">
	    <p>You must have an HTML5 capable browser with HLS support (Ex. Safari).</p>
	</div>
</video>

HTMLCODE;
            return $htmlCode;
        }



        function shortcode_video($atts)
        {
            if (is_single())
                if (get_post_type( get_the_ID() ) == 'channel') $stream = get_the_title(get_the_ID());

                if (!$stream) $stream = $atts['channel']; //parameter channel="name"
                if (!$stream) $stream = $_GET['n'];

                $stream = sanitize_file_name($stream);



            $width=$atts['width']; if (!$width) $width = "100%";
            $height=$atts['height'];
            if (!$height)  $height = '360px';

            if (!$stream)
            {
                return "Watch Video Error: Missing channel name!";
            }


            $swfurl = plugin_dir_url(__FILE__) . "ls/live_video.swf?n=" . urlencode($stream);
            $swfurl .= "&prefix=" . urlencode(admin_url() . 'admin-ajax.php?action=vwls&task=');
            $swfurl .= '&extension='.urlencode('_none_');
            $swfurl .= '&ws_res=' . urlencode( plugin_dir_url(__FILE__) . 'ls/');

            $bgcolor="#333333";

            $htmlCode = <<<HTMLCODE
<div id="videowhisper_container_$stream">
<object id="videowhisper_video_$stream" width="100%" height="100%" type="application/x-shockwave-flash" data="$swfurl">
<param name="movie" value="$swfurl"></param><param bgcolor="$bgcolor"><param name="scale" value="noscale" /> </param><param name="salign" value="lt"></param><param name="allowFullScreen"
value="true"></param><param name="allowscriptaccess" value="always"></param>
</object>
</div>

<br style="clear:both" />

<style type="text/css">
<!--

#videowhisper_container_$stream
{
position: relative;
width: $width;
height: $height;
border: solid 1px #999;
}

-->
</style>
$auto
HTMLCODE;

            return $htmlCode;
        }


        function shortcode_broadcast($atts)
        {

            $options = get_option('VWliveStreamingOptions');

            if ($options['postChannels'])
            {
                if (is_single())
                    if (get_post_type( get_the_ID() ) == 'channel') $stream = get_the_title(get_the_ID());
            }

            if (!$stream) $stream = $atts['channel'];

            if ($options['anyChannels'])
                if (!$stream) $stream = $_GET['n'];


                if ($options['userChannels'])
                    if (!$stream) //username
                        {
                        $userName =  $options['userName']; if (!$userName) $userName='user_nicename';
                        global $current_user;
                        get_currentuserinfo();
                        if ($current_user->$userName) $username = $current_user->$userName;
                        $stream = $username;
                    }

                $stream = sanitize_file_name($stream);


            if (!$stream)
            {
                return "Broadcast Error: Missing channel name!";
            }

            $swfurl = plugin_dir_url(__FILE__) . "ls/live_broadcast.swf?room=" . urlencode($stream);
            $swfurl .= "&prefix=" . urlencode(admin_url() . 'admin-ajax.php?action=vwls&task=');
            $swfurl .= '&extension='.urlencode('_none_');
            $swfurl .= '&ws_res=' . urlencode( plugin_dir_url(__FILE__) . 'ls/');

            $bgcolor="#333333";

            $htmlCode = <<<HTMLCODE
<div id="videowhisper_container">
<object width="100%" height="100%" type="application/x-shockwave-flash" data="$swfurl">
<param name="movie" value="$swfurl"></param><param bgcolor="$bgcolor"><param name="scale" value="noscale" /> </param><param name="salign" value="lt"></param><param name="allowFullScreen"
value="true"></param><param name="allowscriptaccess" value="always"></param>
</object>
</div>

<br style="clear:both" />

<style type="text/css">
<!--

#videowhisper_container
{
width: 100%;
height: 500px;
border: solid 3px #999;
}

-->
</style>

HTMLCODE;

            if ($stream)
            {

                $options =  VWliveStreaming::getAdminOptions();

                $userName =  $options['userName']; if (!$userName) $userName='user_nicename';

                //username
                global $current_user;
                get_currentuserinfo();
                if ($current_user->$userName) $username=sanitize_file_name($current_user->$userName);

                //access keys
                if ($current_user)
                {
                    $userkeys = $current_user->roles;
                    $userkeys[] = $current_user->user_login;
                    $userkeys[] = $current_user->ID;
                    $userkeys[] = $current_user->user_email;
                    $userkeys[] = $current_user->display_name;
                }

                $admin_ajax = admin_url() . 'admin-ajax.php';

                if (VWliveStreaming::inList($userkeys, $options['premiumList'])) //premium broadcasters can transcode
                    if ($options['transcoding'])
                        $htmlCode .= <<<HTMLCODE
<div id="vwinfo">
iOS Transcoding (iPhone/iPad)<BR>
<a href='#' class="button" id="transcoderon">ON</a>
<a href='#' class="button" id="transcoderoff">OFF</a>
<div id="result">A stream must be broadcast for transcoder to start.</div>
<p align="right">(<a href="javascript:void(0)" onClick="vwinfo.style.display='none';">hide</a>)</p>
</div>

<style type="text/css">
<!--

#vwinfo
{
	float: right;
	width: 25%;
	position: absolute;
	bottom: 10px;
	right: 10px;
	text-align:left;
	padding: 10px;
	margin: 10px;
	background-color: #666;
	border: 1px dotted #AAA;
	z-index: 1;

	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#999', endColorstr='#666'); /* for IE */
	background: -webkit-gradient(linear, left top, left bottom, from(#999), to(#666)); /* for webkit browsers */
	background: -moz-linear-gradient(top,  #999,  #666); /* for firefox 3.6+ */

	box-shadow: 2px 2px 2px #333;


	-moz-border-radius: 9px;
	border-radius: 9px;
}

#vwinfo > a {
	color: #F77;
	text-decoration: none;
}

#vwinfo > .button {
	-moz-box-shadow:inset 0px 1px 0px 0px #f5978e;
	-webkit-box-shadow:inset 0px 1px 0px 0px #f5978e;
	box-shadow:inset 0px 1px 0px 0px #f5978e;
	background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #db4f48), color-stop(1, #944038) );
	background:-moz-linear-gradient( center top, #db4f48 5%, #944038 100% );
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#db4f48', endColorstr='#944038');
	background-color:#db4f48;
	border:1px solid #d02718;
	display:inline-block;
	color:#ffffff;
	font-family:Verdana;
	font-size:12px;
	font-weight:normal;
	font-style:normal;
	text-decoration:none;
	text-align:center;
	text-shadow:1px 1px 0px #810e05;
	padding: 5px;
	margin: 2px;
}
#vwinfo > .button:hover {
	background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #944038), color-stop(1, #db4f48) );
	background:-moz-linear-gradient( center top, #944038 5%, #db4f48 100% );
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#944038', endColorstr='#db4f48');
	background-color:#944038;
}

-->
</style>

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
<script type="text/javascript">
	$.ajaxSetup ({
		cache: false
	});
	var ajax_load = "Loading...";

	$("#transcoderon").click(function(){
		$("#result").html(ajax_load).load("$admin_ajax?action=vwls_trans&task=mp4&stream=$stream");
	});

	$("#transcoderoff").click(function(){
	$("#result").html(ajax_load).load("$admin_ajax?action=vwls_trans&task=close&stream=$stream");
	});
</script>
HTMLCODE;
            }

            return $htmlCode ;
        }


        function channel_page($content)
        {

            $options = VWliveStreaming::getAdminOptions();
            if (!$options['postChannels']) return $content;

            if (!is_single()) return $content;
            $postID = get_the_ID() ;
            if (get_post_type( $postID ) != 'channel') return $content;

            global $wp_query;
            if( array_key_exists( 'broadcast' , $wp_query->query_vars ) )
                $addCode = "" . '[videowhisper_broadcast]';
            elseif( array_key_exists( 'video' , $wp_query->query_vars ) )
                $addCode = "" . '[videowhisper_video]';
            elseif( array_key_exists( 'hls' , $wp_query->query_vars ) )
                $addCode = "" . '[videowhisper_hls]';
            else $addCode = "" . '[videowhisper_watch]';

            //set thumb
            $stream = sanitize_file_name(get_the_title($postID));
            $dir = $options['uploadsPath']. "/_snapshots";
            $thumbFilename = "$dir/$stream.jpg";

            if ( file_exists($thumbFilename) && (get_the_post_thumbnail( $postID ) == ''))
            {
                $wp_filetype = wp_check_filetype(basename($thumbFilename), null );

                $attachment = array(
                    'guid' => $thumbFilename,
                    'post_mime_type' => $wp_filetype['type'],
                    'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $thumbFilename, ".jpg" ) ),
                    'post_content' => '',
                    'post_status' => 'inherit'
                );

                $attach_id = wp_insert_attachment( $attachment, $thumbFilename, $postID );
                set_post_thumbnail($postID, $attach_id);


                require_once( ABSPATH . 'wp-admin/includes/image.php' );
                $attach_data = wp_generate_attachment_metadata( $attach_id, $thumbFilename );
                wp_update_attachment_metadata( $attach_id, $attach_data );

            }


            return $addCode . $content;
        }

        function channel_query_vars( $query_vars ){
            // array of recognized query vars
            $query_vars[] = 'broadcast';
            $query_vars[] = 'video';
            $query_vars[] = 'hls';
            return $query_vars;
        }

        // Register Custom Post Type
        function channel_post() {

            $options = VWliveStreaming::getAdminOptions();
            if (!$options['postChannels']) return;

            //only if missing
            if (post_type_exists('channel')) return;
            
            $labels = array(
                'name'                => _x( 'Channels', 'Post Type General Name', 'text_domain' ),
                'singular_name'       => _x( 'Channel', 'Post Type Singular Name', 'text_domain' ),
                'menu_name'           => __( 'Channels', 'text_domain' ),
                'parent_item_colon'   => __( 'Parent Channel:', 'text_domain' ),
                'all_items'           => __( 'All Channels', 'text_domain' ),
                'view_item'           => __( 'View Channel', 'text_domain' ),
                'add_new_item'        => __( 'Add New Channel', 'text_domain' ),
                'add_new'             => __( 'New Channel', 'text_domain' ),
                'edit_item'           => __( 'Edit Channel', 'text_domain' ),
                'update_item'         => __( 'Update Channel', 'text_domain' ),
                'search_items'        => __( 'Search Channels', 'text_domain' ),
                'not_found'           => __( 'No Channels found', 'text_domain' ),
                'not_found_in_trash'  => __( 'No Channels found in Trash', 'text_domain' ),
            );
            $args = array(
                'label'               => __( 'channel', 'text_domain' ),
                'description'         => __( 'Video Channels', 'text_domain' ),
                'labels'              => $labels,
                'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'comments', 'custom-fields', 'page-attributes', ),
                'taxonomies'          => array( 'category', 'post_tag' ),
                'hierarchical'        => false,
                'public'              => true,
                'show_ui'             => true,
                'show_in_menu'        => true,
                'show_in_nav_menus'   => true,
                'show_in_admin_bar'   => true,
                'menu_position'       => 5,
                'can_export'          => true,
                'has_archive'         => true,
                'exclude_from_search' => false,
                'publicly_queryable'  => true,
                'capability_type'     => 'page',
            );
            register_post_type( 'channel', $args );
            
            add_rewrite_endpoint( 'broadcast', EP_ALL );
            add_rewrite_endpoint( 'video', EP_ALL );
            add_rewrite_endpoint( 'hls', EP_ALL );

            flush_rewrite_rules();

        }


        function vwls_channels() //list channels
            {

            function path2url($file, $Protocol='http://')
            {
                return $Protocol.$_SERVER['HTTP_HOST'].str_replace($_SERVER['DOCUMENT_ROOT'], '', $file);
            }

            function format_age($t)
            {
                if ($t<60) return "LIVE";
                return sprintf("%d%s%d%s%d%s", floor($t/86400), 'd ', ($t/3600)%24,'h ', ($t/60)%60,'m ago');
            }

            $options = VWliveStreaming::getAdminOptions();

            $perPage = (int) $_GET['pp'];
            if (!$perPage) $perPage = $options['perPage'];

            $page = (int) $_GET['p'];
            $offset = $page * $perPage;

            ob_clean();

            $dir = $options['uploadsPath']. "/_thumbs";


            global $wpdb;
            $table_name3 = $wpdb->prefix . "vw_lsrooms";


            $items =  $wpdb->get_results("SELECT * FROM `$table_name3` ORDER BY edate DESC LIMIT $offset, ". $perPage);
            if ($items) foreach ($items as $item)
                {
                    echo '<div class="videowhisperChannel">';
                    echo '<div class="videowhisperTitle">' . $item->name. '</div>';
                    echo '<div class="videowhisperTime">' . format_age(time() -  $item->edate) . '</div>';

                    $thumbFilename = "$dir/" . $item->name . ".jpg";

                    $postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_name = '" . $item->name . "' and post_type='channel' LIMIT 0,1" );

                    if ($postID) $url = get_post_permalink($postID);
                    else $url = plugin_dir_url(__FILE__) . 'ls/channel.php?n=' . urlencode($item->name);


                    if (file_exists($thumbFilename)) echo '<a href="' . $url . '"><IMG src="' . path2url($thumbFilename) . '" width="' . $options['thumbWidth'] . 'px" height="' . $options['thumbHeight'] . 'px"></a>';
                    else echo '<a href="' . $url . '"><IMG SRC="' . plugin_dir_url(__FILE__). 'screenshot-3.jpg" width="' . $options['thumbWidth'] . 'px" height="' . $options['thumbHeight'] . 'px"></a>';
                    echo "</div>";
                }

            $ajaxurl = admin_url() . 'admin-ajax.php?action=vwls_channels&pp='.$perPage;

            echo "<BR>";
            if ($page>0) echo ' <a class="videowhisperButton" href="JavaScript: void()" onclick="aurl=\'' . $ajaxurl.'&p='.($page-1). '\'; loadChannels();">Previous</a> ';

            if (count($items) == $perPage) echo ' <a class="videowhisperButton" href="JavaScript: void()" onclick="aurl=\'' . $ajaxurl.'&p='.($page+1). '\'; loadChannels();">Next</a> ';


            die;
        }

        function vwls_broadcast() //dedicated broadcasting page
            {
            ob_clean();
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>VideoWhisper Live Broadcast</title>
</head>
<body bgcolor="<?php echo $bgcolor?>">
<style type="text/css">
<!--
BODY
{
	padding-right: 6px;
	margin: 0px;
	background: #333;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 12px;
	color: #EEE;
}
-->
</style>
<?php
            include(plugin_dir_path( __FILE__ ) . "ls/flash_detect.php");

            echo do_shortcode('[videowhisper_broadcast]');

            die;
        }




        function vwls_trans()
        {


            ob_clean();

            $stream = sanitize_file_name($_GET['stream']);

            if (!$stream)
            {
                echo "No stream name provided!";
                return;
            }

            $options = VWliveStreaming::getAdminOptions();

            $uploadsPath = $options['uploadsPath'];
            if (!file_exists($uploadsPath)) mkdir($uploadsPath);

            $upath = $uploadsPath . "/$stream/";
            if (!file_exists($upath)) mkdir($upath);

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
                    $cmd ="/usr/local/bin/ffmpeg -s 480x360 -r 15 -vb 512k -vcodec libx264 -coder 0 -bf 0 -analyzeduration 0 -level 3.1 -g 30 -maxrate 768k -acodec libfaac -ac 2 -ar 22050 -ab 96k
-x264opts vbv-maxrate=364:qpmin=4:ref=4 -threads 4 -rtmp_pageurl \"http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] . "\" -rtmp_swfurl \"http://".$_SERVER['HTTP_HOST']."\" -f flv " .
                        $rtmp_server . "/i_". $stream . " -i " . $rtmp_server ."/". $stream . " >&$log_file & ";
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

                $admin_ajax = admin_url() . 'admin-ajax.php';

                echo "<BR><a target='_blank' href='".$admin_ajax . "?action=vwls_trans&task=html5&stream=$stream'> Preview </a>";
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
                    echo "Transcoder not found for '$stream'!";
                }

                break;
            case "html5";
?>
<p>iOS live stream link (open with Safari or test with VLC): <a href="<?php echo $options['httpstreamer']?>i_<?php echo $stream?>/playlist.m3u8"><br />
  <?php echo $stream?> Video</a></p>


<p>HTML5 live video embed below should be accessible <u>only in <B>Safari</B> browser</u> (PC or iOS):</p>
<?php
                echo do_shortcode('[videowhisper_hls channel="'.$stream.'"]');
?>
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



        function shortcode_livesnapshots()
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


                    $postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_name = '" . $item->room . "' and post_type='channel' LIMIT 0,1" );
                    if ($postID) $url = get_post_permalink($postID);
                    else $url = plugin_dir_url(__FILE__) . 'ls/channel.php?n=' . urlencode($item->name);


                    $urli = $root_url . "wp-content/plugins/videowhisper-live-streaming-integration/ls/snapshots/".urlencode($item->room). ".jpg";
                    if (!file_exists("wp-content/plugins/videowhisper-live-streaming-integration/ls/snapshots/".urlencode($item->room). ".jpg")) $urli = $root_url .
                            "wp-content/plugins/videowhisper-live-streaming-integration/ls/snapshots/no_video.png";

                    $livesnapshotsCode .= "<div style='border: 1px dotted #390; width: 240px; padding: 1px'><a href='$urlc'><IMG width='240px' SRC='$urli'><div ><B>".$item->room."</B>
(".($count[0]->no+1).") ".($item->message?": ".$item->message:"") ."</div></a></div>";
                }
            else  $livesnapshotsCode .= "<div>No broadcasters online.</div>";

            $livesnapshotsCode .=  "</div> ";

            $options = get_option('VWliveStreamingOptions');
            $state = 'block' ;
            if (!$options['videowhisper']) $state = 'none';
            $livesnapshotsCode .= '<div id="VideoWhisper" style="display: ' . $state . ';"><p>Powered by VideoWhisper <a href="http://www.videowhisper.com/?p=WordPress+Live+Streaming">Live Video
Streaming Software</a>.</p></div>';


            echo $livesnapshotsCode;
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

                    $postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_name = '" . $item->room . "' and post_type='channel' LIMIT 0,1" );
                    if ($postID) $url = get_post_permalink($postID);
                    else $url = plugin_dir_url(__FILE__) . 'ls/channel.php?n=' . urlencode($item->name);


                    echo "<li><a href='" . $url . "'><B>".$item->room."</B>
(".($count[0]->no+1).") ".($item->message?": ".$item->message:"") ."</a></li>";
                }
            else echo "<li>No broadcasters online.</li>";
            echo "</ul>";

            $options = get_option('VWliveStreamingOptions');

            if ($options['userChannels']||$options['anyChannels'])
                if (is_user_logged_in())
                {
                    $userName =  $options['userName']; if (!$userName) $userName='user_nicename';
                    global $current_user;
                    get_currentuserinfo();
                    if ($current_user->$userName) $username = $current_user->$userName;
                    $username = sanitize_file_name($username);
                    ?><a href="<?php echo $root_url; ?>wp-content/plugins/videowhisper-live-streaming-integration/ls/?n=<?php echo $username ?>"><img src="<?php echo $root_url;
                    ?>wp-content/plugins/videowhisper-live-streaming-integration/ls/templates/live/i_webcam.png" align="absmiddle" border="0">Video Broadcast</a>
	<?php
                }

            $state = 'block' ;
            if (!$options['videowhisper']) $state = 'none';
            echo '<div id="VideoWhisper" style="display: ' . $state . ';"><p>Powered by VideoWhisper <a href="http://www.videowhisper.com/?p=WordPress+Live+Streaming">Live Video Streaming
Software</a>.</p></div>';
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
            $upload_dir = wp_upload_dir();

            $adminOptions = array(
                'userName' => 'user_nicename',
                'postChannels' => '1',
                'userChannels' => '1',
                'anyChannels' => '0',
                'disablePage' => '0',
                'disablePageC' => '0',
                'thumbWidth' => '240',
                'thumbHeight' => '180',
                'perPage' =>'6',


                'postName' => 'custom',

                'rtmp_server' => 'rtmp://localhost/videowhisper',
                'rtmp_amf' => 'AMF3',
                'httpstreamer' => 'http://localhost:1935/videowhisper-x/',

                'canBroadcast' => 'members',
                'broadcastList' => 'Super Admin, Administrator, Editor, Author',
                'maxChannels' => '2',

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

                'uploadsPath' => $upload_dir['basedir'] . '/vwls',

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

            $page_id = get_option("vwls_page_manage");
            if ($page_id != '-1' && $options['disablePage']!='0') VWliveStreaming::deletePages();

            $page_idC = get_option("vwls_page_channels");
            if ($page_idC != '-1' && $options['disablePageC']!='0') VWliveStreaming::deletePages();


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
    <a href="options-general.php?page=videowhisper_streaming.php&tab=shortcodes" class="nav-tab <?php echo $active_tab=='shortcodes'?'nav-tab-active':'';?>">Shortcodes</a>
    <a href="options-general.php?page=videowhisper_streaming.php&tab=live" class="nav-tab <?php echo $active_tab=='live'?'nav-tab-active':'';?>">Live!</a>
</h2>

<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">

<?php
            switch ($active_tab)
            {
            case 'general':

                $broadcast_url = admin_url() . 'admin-ajax.php?action=vwls_broadcast&n=';
                $root_url = get_bloginfo( "url" ) . "/";


                $userName =  $options['userName']; if (!$userName) $userName='user_nicename';
                global $current_user;
                get_currentuserinfo();
                if ($current_user->$userName) $username = $current_user->$userName;
                $username = sanitize_file_name($username);

?>
<h3>General Integration Settings</h3>
<h4>Username</h4>
<select name="userName" id="userName">
  <option value="display_name" <?php echo $options['userName']=='display_name'?"selected":""?>>Display Name</option>
  <option value="user_login" <?php echo $options['userName']=='user_login'?"selected":""?>>Login (Username)</option>
  <option value="user_nicename" <?php echo $options['userName']=='user_nicename'?"selected":""?>>Nicename</option>
</select>

<h4>Post Channels</h4>
<select name="postChannels" id="postChannels">
  <option value="1" <?php echo $options['postChannels']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['postChannels']?"":"selected"?>>No</option>
</select>
<BR>Enables special post types (channels) and static urls for easy access to broadcast, watch and preview video.
<BR><?php echo $root_url; ?>channel/chanel-name/broadcast
<BR><?php echo $root_url; ?>channel/chanel-name/
<BR><?php echo $root_url; ?>channel/chanel-name/video

<h4>Maximum Broadcating Channels</h4>
<input name="maxChannels" type="text" id="maxChannels" size="2" maxlength="4" value="<?php echo $options['maxChannels']?>"/>
<BR>Maximum channels users are allowed to create from frontend if channel posts are enabled.

<h4>User Channels</h4>
<select name="userChannels" id="userChannels">
  <option value="1" <?php echo $options['userChannels']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['userChannels']?"":"selected"?>>No</option>
</select>
<BR>Enables users to start channel with own name by accessing a common static broadcasting link.
<BR><a href="<?php echo $broadcast_url; ?>"><img src="<?php echo $root_url; ?>wp-content/plugins/videowhisper-live-streaming-integration/ls/templates/live/i_webcam.png" align="absmiddle"
border="0"><?php echo $broadcast_url; ?></a>

<h4>Custom Channels</h4>
<select name="anyChannels" id="anyChannels">
  <option value="1" <?php echo $options['anyChannels']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['anyChannels']?"":"selected"?>>No</option>
</select>
<BR>Enables users to start channel by passing any channel name in link.
<BR><a href="<?php echo $broadcast_url . urlencode($username); ?>"><img src="<?php echo $root_url; ?>wp-content/plugins/videowhisper-live-streaming-integration/ls/templates/live/i_webcam.png"
align="absmiddle" border="0"><?php echo $broadcast_url . urlencode($username); ?></a>

<h4>Floating Logo / Watermark</h4>
<input name="overLogo" type="text" id="overLogo" size="80" maxlength="256" value="<?php echo $options['overLogo']?>"/>
<?php echo $options['overLogo']?"<BR><img src='".$options['overLogo']."'>":'';?>
<h4>Logo Link</h4>
<input name="overLink" type="text" id="overLink" size="80" maxlength="256" value="<?php echo $options['overLink']?>"/>

<h4>Page for Management</h4>
<p>Add channel management page (Page ID <a href='post.php?post=<?php echo get_option("vwls_page_manage"); ?>&action=edit'><?php echo get_option("vwls_page_manage"); ?></a>) with shortcode [videowhisper_channel_manage]</p>
<select name="disablePage" id="disablePage">
  <option value="0" <?php echo $options['disablePage']=='0'?"selected":""?>>Yes</option>
  <option value="1" <?php echo $options['disablePage']=='1'?"selected":""?>>No</option>
</select>

<h4>Page for Channels</h4>
<p>Add channel list page (Page ID <a href='post.php?post=<?php echo get_option("vwls_page_channels"); ?>&action=edit'><?php echo get_option("vwls_page_channels"); ?></a>) with shortcode [videowhisper_channels]</p>
<select name="disablePageC" id="disablePageC">
  <option value="0" <?php echo $options['disablePageC']=='0'?"selected":""?>>Yes</option>
  <option value="1" <?php echo $options['disablePageC']=='1'?"selected":""?>>No</option>
</select>

<h4>Channel Thumb Width</h4>
<input name="thumbWidth" type="text" id="thumbWidth" size="4" maxlength="4" value="<?php echo $options['thumbWidth']?>"/>

<h4>Channel Thumb Height</h4>
<input name="thumbHeight" type="text" id="thumbHeight" size="4" maxlength="4" value="<?php echo $options['thumbHeight']?>"/>
<BR><a href="options-general.php?page=videowhisper_streaming.php&tab=stats&regenerateThumbs=1">Regenerate Thumbs</a>

<h4>Default Channels Per Page</h4>
<input name="perPage" type="text" id="perPage" size="3" maxlength="3" value="<?php echo $options['perPage']?>"/>



<h4>Show VideoWhisper Powered by</h4>
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
<h4>RTMP Address</h4>
<p>To run this, make sure your hosting environment meets all <a href="http://www.videowhisper.com/?p=Requirements" target="_blank">requirements</a>.<BR>If you don't have a videowhisper rtmp address
yet (from a managed rtmp host), go to <a href="http://www.videowhisper.com/?p=RTMP+Applications" target="_blank">RTMP Application   Setup</a> for  installation details.</p>
<input name="rtmp_server" type="text" id="rtmp_server" size="100" maxlength="256" value="<?php echo $options['rtmp_server']?>"/>
<BR> A public accessible rtmp hosting server is required with custom videowhisper rtmp side. Ex: rtmp://your-server/videowhisper
<?php submit_button(); ?>


<h4>HTTP Streaming URL</h4>
This is used for accessing transcoded streams on HLS playback. Usually available with <a href="http://www.videowhisper.com/?p=Wowza+Media+Server+Hosting">Wowza Hosting</a> .<br>
<input name="httpstreamer" type="text" id="httpstreamer" size="100" maxlength="256" value="<?php echo $options['httpstreamer']?>"/>
<BR>External players and encoders (if enabled) are not monitored or controlled by this plugin.
<BR>Application folder must match rtmp application. Ex. http://localhost:1935/videowhisper-x/ works when publishing to rtmp://localhost/videowhisper-x .

<h4>Disable Bandwidth Detection</h4>
<p>Required on some rtmp servers that don't support bandwidth detection and return a Connection.Call.Fail error.</p>
<select name="disableBandwidthDetection" id="disableBandwidthDetection">
  <option value="0" <?php echo $options['disableBandwidthDetection']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['disableBandwidthDetection']?"selected":""?>>Yes</option>
</select>

<h4>Token Key</h4>
<input name="tokenKey" type="text" id="tokenKey" size="32" maxlength="64" value="<?php echo $options['tokenKey']?>"/>
<BR>A <a href="http://www.videowhisper.com/?p=RTMP+Applications#settings">secure token</a> can be used with Wowza Media Server.

<h4>Web Key</h4>
<input name="webKey" type="text" id="webKey" size="32" maxlength="64" value="<?php echo $options['webKey']?>"/>
<BR>A web key can be used for <a href="http://www.videochat-scripts.com/videowhisper-rtmp-web-authetication-check/">VideoWhisper RTMP Web Session Check</a>.
<?php
                $admin_ajax = admin_url() . 'admin-ajax.php';

                echo "<BR>webLogin:  $admin_ajax"."?action=vwls&task=rtmp_login&s=";
                echo "<BR>webLogout: $admin_ajax"."?action=vwls&task=rtmp_logout&s=";
?>

<h4>RTMFP Address</h4>
<p> Get your own independent RTMFP address by registering for a free <a href="https://www.adobe.com/cfusion/entitlement/index.cfm?e=cirrus" target="_blank">Adobe Cirrus developer key</a>. This is
required for P2P support.</p>
<input name="serverRTMFP" type="text" id="serverRTMFP" size="80" maxlength="256" value="<?php echo $options['serverRTMFP']?>"/>
<h4>P2P Group</h4>
<input name="p2pGroup" type="text" id="p2pGroup" size="32" maxlength="64" value="<?php echo $options['p2pGroup']?>"/>
<h4>Support RTMP Streaming</h4>
<select name="supportRTMP" id="supportRTMP">
  <option value="0" <?php echo $options['supportRTMP']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['supportRTMP']?"selected":""?>>Yes</option>
</select>
<h4>Always do RTMP Streaming</h4>
<p>Enable this if you want all streams to be published to server, no matter if there are registered subscribers or not (in example if you're using server side video archiving and need all streams
published for recording).</p>
<select name="alwaysRTMP" id="alwaysRTMP">
  <option value="0" <?php echo $options['alwaysRTMP']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['alwaysRTMP']?"selected":""?>>Yes</option>
</select>
<h4>Support P2P Streaming</h4>
<select name="supportP2P" id="supportP2P">
  <option value="0" <?php echo $options['supportP2P']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['supportP2P']?"selected":""?>>Yes</option>
</select>

<h4>Always do P2P Streaming</h4>
<select name="alwaysP2P" id="alwaysP2P">
  <option value="0" <?php echo $options['alwaysP2P']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['alwaysP2P']?"selected":""?>>Yes</option>
</select>

<h4>Uploads Path</h4>
<p>Path where logs and snapshots will be uploaded. Make sure you use a location outside plugin folder to avoid losing logs on updates and plugin uninstallation.</p>
<input name="uploadsPath" type="text" id="uploadsPath" size="80" maxlength="256" value="<?php echo $options['uploadsPath']?>"/>


<?php
                break;
            case 'broadcaster':
?>
<h3>Video Broadcasting</h3>
Options for video broadcasting.
<h4>Who can broadcast video channels</h4>
<select name="canBroadcast" id="canBroadcast">
  <option value="members" <?php echo $options['canBroadcast']=='members'?"selected":""?>>All Members</option>
  <option value="list" <?php echo $options['canBroadcast']=='list'?"selected":""?>>Members in List</option>
</select>
<h4>Members allowed to broadcast video (comma separated user names, roles, emails, IDs)</h4>
<textarea name="broadcastList" cols="64" rows="3" id="broadcastList"><?php echo $options['broadcastList']?>
</textarea>


<h4>Maximum Broadcating Time (0 = unlimited)</h4>
<input name="broadcastTime" type="text" id="broadcastTime" size="7" maxlength="7" value="<?php echo $options['broadcastTime']?>"/> (minutes/period)

<h4>Maximum Channel Watch Time (total cumulated view time, 0 = unlimited)</h4>
<input name="watchTime" type="text" id="watchTime" size="10" maxlength="10" value="<?php echo $options['watchTime']?>"/> (minutes/period)

<h4>Usage Period Reset (0 = never)</h4>
<input name="timeReset" type="text" id="timeReset" size="4" maxlength="4" value="<?php echo $options['timeReset']?>"/> (days)


<h4>Default Webcam Resolution</h4>
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

<h4>Default Webcam Frames Per Second</h4>
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


<h4>Video Stream Bandwidth</h4>
<input name="camBandwidth" type="text" id="camBandwidth" size="7" maxlength="7" value="<?php echo $options['camBandwidth']?>"/> (bytes/s)
<h4>Maximum Video Stream Bandwidth (at runtime)</h4>
<input name="camMaxBandwidth" type="text" id="camMaxBandwidth" size="7" maxlength="7" value="<?php echo $options['camMaxBandwidth']?>"/> (bytes/s)

<h4>Video Codec</h4>
<select name="videoCodec" id="videoCodec">
  <option value="H264" <?php echo $options['videoCodec']=='H264'?"selected":""?>>H264</option>
  <option value="H263" <?php echo $options['videoCodec']=='H263'?"selected":""?>>H263</option>
</select>

<h4>H264 Video Codec Profile</h4>
<select name="codecProfile" id="codecProfile">
  <option value="main" <?php echo $options['codecProfile']=='main'?"selected":""?>>main</option>
  <option value="baseline" <?php echo $options['codecProfile']=='baseline'?"selected":""?>>baseline</option>
</select>

<h4>H264 Video Codec Level</h4>
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

<h4>Sound Codec</h4>
<select name="soundCodec" id="soundCodec">
  <option value="Speex" <?php echo $options['soundCodec']=='Speex'?"selected":""?>>Speex</option>
  <option value="Nellymoser" <?php echo $options['soundCodec']=='Nellymoser'?"selected":""?>>Nellymoser</option>
</select>

<h4>Speex Sound Quality</h4>
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

<h4>Nellymoser Sound Rate</h4>
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

<h4>Disable Embed/Link Codes</h4>
<select name="noEmbeds" id="noEmbeds">
  <option value="0" <?php echo $options['noEmbeds']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['noEmbeds']?"selected":""?>>Yes</option>
</select>
<h4>Show only Video</h4>
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
<h4>Members that broadcast premium channels (Premium members: comma separated user names, roles, emails, IDs)</h4>
<textarea name="premiumList" cols="64" rows="3" id="premiumList"><?php echo $options['premiumList']?>
</textarea>


<h4>Who can watch premium channels</h4>
<select name="canWatchPremium" id="canWatchPremium">
  <option value="all" <?php echo $options['canWatchPremium']=='all'?"selected":""?>>Anybody</option>
  <option value="members" <?php echo $options['canWatchPremium']=='members'?"selected":""?>>All Members</option>
  <option value="list" <?php echo $options['canWatchPremium']=='list'?"selected":""?>>Members in List</option>
</select>
<h4>Members allowed to watch premium channels (comma separated usernames, roles, IDs)</h4>
<textarea name="watchListPremium" cols="64" rows="3" id="watchListPremium"><?php echo $options['watchListPremium']?>
</textarea>

<h4>Show Floating Logo/Watermark</h4>
<select name="pLogo" id="pLogo">
  <option value="0" <?php echo $options['pLogo']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['pLogo']?"selected":""?>>Yes</option>
</select>

<h4>Enable Transcoding</h4>
<select name="transcoding" id="transcoding">
  <option value="0" <?php echo $options['transcoding']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['transcoding']?"selected":""?>>Yes</option>
</select>

<h4>Always do RTMP Streaming (required for Transcoding)</h4>
<p>Enable this if you want all streams to be published to server, no matter if there are registered subscribers or not. Stream on server is required for transcoding to start.</p>
<select name="alwaysRTMP" id="alwaysRTMP">
  <option value="0" <?php echo $options['alwaysRTMP']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['alwaysRTMP']?"selected":""?>>Yes</option>
</select>


<h4>Maximum Broadcating Time (0 = unlimited)</h4>
<input name="pBroadcastTime" type="text" id="pBroadcastTime" size="7" maxlength="7" value="<?php echo $options['pBroadcastTime']?>"/> (minutes/period)

<h4>Maximum Channel Watch Time (total cumulated view time, 0 = unlimited)</h4>
<input name="pWatchTime" type="text" id="pWatchTime" size="10" maxlength="10" value="<?php echo $options['pWatchTime']?>"/> (minutes/period)

<h4>Usage Period Reset (same as for regular channels, 0 = never)</h4>
<input name="timeReset" type="text" id="timeReset" size="4" maxlength="4" value="<?php echo $options['timeReset']?>"/> (days)

<h4>Video Stream Bandwidth</h4>
<input name="pCamBandwidth" type="text" id="pCamBandwidth" size="7" maxlength="7" value="<?php echo $options['pCamBandwidth']?>"/> (bytes/s)

<h4>Maximum Video Stream Bandwidth (at runtime)</h4>
<input name="pCamMaxBandwidth" type="text" id="pCamMaxBandwidth" size="7" maxlength="7" value="<?php echo $options['pCamMaxBandwidth']?>"/> (bytes/s)

<?php
                break;
            case 'watcher':
?>
<h3>Video Watcher</h3>
Settings for video subscribers that watch the live channels using watch or plain video interface.
<h4>Who can watch video</h4>
<select name="canWatch" id="canWatch">
  <option value="all" <?php echo $options['canWatch']=='all'?"selected":""?>>Anybody</option>
  <option value="members" <?php echo $options['canWatch']=='members'?"selected":""?>>All Members</option>
  <option value="list" <?php echo $options['canWatch']=='list'?"selected":""?>>Members in List</option>
</select>
<h4>Members allowed to watch video (comma separated usernames, roles, IDs)</h4>
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
                    if ($t<30) return "LIVE";
                    return sprintf("%d%s%d%s%d%s", floor($t/86400), 'd ', ($t/3600)%24,'h ', ($t/60)%60,'m');
                }
                if ($_GET['regenerateThumbs'])
                {
                    $dir=$options['uploadsPath'];
                    $dir .= "/_snapshots";
                    echo '<div class="info">Regenerating thumbs for listed channels.</div>';
                }

                global $wpdb;
                $table_name = $wpdb->prefix . "vw_sessions";
                $table_name2 = $wpdb->prefix . "vw_lwsessions";
                $table_name3 = $wpdb->prefix . "vw_lsrooms";

                $items =  $wpdb->get_results("SELECT * FROM `$table_name3` ORDER BY edate DESC LIMIT 0, 100");
                echo "<table class='wp-list-table widefat'><thead><tr><th>Channel</th><th>Last Access</th><th>Broadcast Time</th><th>Watch Time</th><th>Last Reset</th><th>Type</th></tr></thead>";
                if ($items) foreach ($items as $item)
                    {
                        echo "<tr><th>".$item->name;

                        if ($_GET['regenerateThumbs'])
                        {
                            //
                            $stream=$item->name;
                            $filename = "$dir/$stream.jpg";

                            if (file_exists($filename))
                            {
                                //generate thumb
                                $thumbWidth = $options['thumbWidth'];
                                $thumbHeight = $options['thumbHeight'];

                                $src = imagecreatefromjpeg($filename);
                                list($width, $height) = getimagesize($filename);
                                $tmp = imagecreatetruecolor($thumbWidth, $thumbHeight);

                                $dir = $options['uploadsPath']. "/_thumbs";
                                if (!file_exists($dir)) mkdir($dir);

                                $thumbFilename = "$dir/$stream.jpg";
                                imagecopyresampled($tmp, $src, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);
                                imagejpeg($tmp, $thumbFilename, 95);
                            } else echo "<div class='warning'>Snapshot missing!</div>";

                            echo "</th><td>".format_age(time() - $item->edate)."</td><td>".format_time($item->btime)."</td><td>".format_time($item->wtime)."</td><td>".format_age(time() - $item->rdate)."</td><td>".($item->type==2?"Premium":"Standard")."</td></tr>";
                            //
                        }
                    }
                echo "</table>";
                break;

            case 'shortcodes';
?>

<h3>ShortCodes</h3>
<ul>
  <li><h4>[videowhisper_watch channel=&quot;Channel Name&quot;]</h4>
    Displays watch interface with video and discussion.
  </li>
  <li><h4>[videowhisper_video channel=&quot;Channel Name&quot; width=&quot;480px&quot; height=&quot;360px&quot;]</h4>
  Displays video only interface. </li>
  <li><h4>[videowhisper_hls channel=&quot;Channel Name&quot; width=&quot;480px&quot; height=&quot;360px&quot;]</h4>
  Displays HTML5 HLS (HTTP Live Streaming) video interface. Transcoding must be enabled and active for stream to show.</li>
  <li>
    <h4>[videowhisper_broadcast]</h4>
    Shows broadcasting interface. Channel name is detected depending on  settings, post type, user.
   </li>
     <li>

	     <h4>[videowhisper_channels perPage="4"]</h4>
	     Lists channels with snapshots, ordered by most recent online and with pagination.
     </li>

     <li>
	     <h4>[videowhisper_livesnapshots]</h4>
	     Displays full size snapshots of online channels. No pagination.
     </li>
     <li>
     <h4>
     [videowhisper_channel_manage]
     </h4>
	     Displays channel management page.
     </li>
</ul>





  <?php
                break;
            case 'live':
                $root_url = get_bloginfo( "url" ) . "/";

                $userName =  $options['userName']; if (!$userName) $userName='user_nicename';
                global $current_user;
                get_currentuserinfo();
                if ($current_user->$userName) $username = $current_user->$userName;
                $username = sanitize_file_name($username);

                $broadcast_url = admin_url() . 'admin-ajax.php?action=vwls_broadcast&n=';

                if ($options['userChannels']||$options['anyChannels'])
                {
?>

<h3>Channel '<?php echo $username; ?>'</h3>
<ul>
<li>
<a href="<?php echo $broadcast_url . urlencode($username); ?>"><img src="<?php echo $root_url; ?>wp-content/plugins/videowhisper-live-streaming-integration/ls/templates/live/i_webcam.png"
align="absmiddle" border="0">Start Broadcasting</a>
</li>
<li>
<a href="<?php echo $root_url; ?>wp-content/plugins/videowhisper-live-streaming-integration/ls/channel.php?n=<?php echo $username; ?>"><img src="<?php echo $root_url;
                    ?>wp-content/plugins/videowhisper-live-streaming-integration/ls/templates/live/i_uvideo.png" align="absmiddle" border="0">View Channel</a>
</li>
</ul>
<p>To allow users to broadcast from frontend (as configured in settings), <a href='widgets.php'>enable the widget</a> and/or channel posts and frontend management page. On some templates/setups you also need to add the page to site menu.
</p>
<?php
                }
?>
<h4>Recent Channels</h4>
<?php

                echo do_shortcode('[videowhisper_channels]');

                break;
            }

            if (!in_array($active_tab, array('live','stats', 'shortcodes')) ) submit_button(); ?>

</form>
</div>
	 <?php
        }


        //calls

        function vwls_calls()
        {
            function sanV(&$var, $file=1, $html=1, $mysql=1) //sanitize variable depending on use
                {
                if (!$var) return;

                if (get_magic_quotes_gpc()) $var = stripslashes($var);

                if ($file) $var = sanitize_file_name($var);

                if ($html&&!$file)
                {
                    $var=strip_tags($var);
                }

                if ($mysql&&!$file)
                {
                    $forbidden=array("'", "\"", "´", "`", "\\", "%");
                    foreach ($forbidden as $search)  $var=str_replace($search,"",$var);
                    $var=mysql_real_escape_string($var);
                }
            }

            //if any key matches any listing
            function inList($keys, $data)
            {
                if (!$keys) return 0;

                $list=explode(",", strtolower(trim($data)));

                foreach ($keys as $key)
                    foreach ($list as $listing)
                        if ( strtolower(trim($key)) == trim($listing) ) return 1;

                        return 0;
            }

            global $wpdb;
            global $current_user;

            ob_clean();

            switch ($_GET['task'])
            {
            case 'vw_snapshots':
                $options = get_option('VWliveStreamingOptions');

                $dir=$options['uploadsPath'];
                if (!file_exists($dir)) mkdir($dir);
                $dir .= "/_snapshots";
                if (!file_exists($dir)) mkdir($dir);

                if (isset($GLOBALS["HTTP_RAW_POST_DATA"]))
                {
                    $stream = $_GET['name'];
                    sanV($stream);
                    if (strstr($stream,'.php')) exit;
                    if (!$stream) exit;

                    // get bytearray
                    $jpg = $GLOBALS["HTTP_RAW_POST_DATA"];

                    // save file
                    $filename = "$dir/$stream.jpg";
                    $fp=fopen($filename ,"w");
                    if ($fp)
                    {
                        fwrite($fp,$jpg);
                        fclose($fp);
                    }

                    //generate thumb
                    $thumbWidth = $options['thumbWidth'];
                    $thumbHeight = $options['thumbHeight'];

                    $src = imagecreatefromjpeg($filename);
                    list($width, $height) = getimagesize($filename);
                    $tmp = imagecreatetruecolor($thumbWidth, $thumbHeight);

                    $dir = $options['uploadsPath']. "/_thumbs";
                    if (!file_exists($dir)) mkdir($dir);

                    $thumbFilename = "$dir/$stream.jpg";
                    imagecopyresampled($tmp, $src, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);
                    imagejpeg($tmp, $thumbFilename, 95);

                }
                ?>loadstatus=1<?php
                break;

                case 'lb_logout':
                echo strip_tags($_GET['message']);
                break;
                
                case 'vw_logout':
                ?>loggedout=1<?php
                break;

            case 'vw_extlogin':

                //esternal login GET u=user, p=password

                $options = get_option('VWliveStreamingOptions');

                $rtmp_server = $options['rtmp_server'];
                $rtmp_amf = $options['rtmp_amf'];
                $userName =  $options['userName']; if (!$userName) $userName='user_nicename';
                $canBroadcast = $options['canBroadcast'];
                $broadcastList = $options['broadcastList'];

                $tokenKey = $options['tokenKey'];
                $webKey = $options['webKey'];

                $serverRTMFP = $options['serverRTMFP'];
                $p2pGroup = $options['p2pGroup'];
                $supportRTMP = $options['supportRTMP'];
                $supportP2P = $options['supportP2P'];
                $alwaystRTMP = $options['alwaystRTMP'];
                $alwaystP2P = $options['alwaystP2P'];
                $disableBandwidthDetection = $options['disableBandwidthDetection'];

                $loggedin=0;
                $msg="";

                $creds = array();
                $creds['user_login'] = $_GET['u'];
                $creds['user_password'] = $_GET['p'];
                $creds['remember'] = true;

                $current_user = wp_signon( $creds, false );

                if( is_wp_error($current_user))
                {
                    $msg = urlencode("Login failed: " . $current_user->get_error_message()) ;
                    $debug = $msg;
                }
                else
                {
                    //logged in
                }

                get_currentuserinfo();



                //username
                if ($current_user->$userName) $username=urlencode($current_user->$userName);
                sanV($username);

                switch ($canBroadcast)
                {
                case "members":
                    if ($username) $loggedin=1;
                    else $msg=urlencode("<a href=\"/\">Please login first or register an account if you don't have one! Click here to return to website.</a>");
                    break;
                case "list";
                    if ($username)
                        if (inList($username, $broadcastList)) $loggedin=1;
                        else $msg=urlencode("<a href=\"/\">$username, you are not in the broadcasters list.</a>");
                        else $msg=urlencode("<a href=\"/\">Please login first or register an account if you don't have one! Click here to return to website.</a>");
                        break;
                }

                //broadcaster
                $userlabel="";
                $room_name=$_GET['room_name'];
                sanV($room_name);

                if ($room_name&&$room_name!=$username)
                {
                    $userlabel=$username;
                    $username=$room_name;
                    $room=$room_name;
                }

                $canKick = 1;
                if ($loggedin)  //approve session for rtmp check
                    {
                    //this generates a session file record for rtmp login check
                    sanV($username);

                    if ($username)
                    {
                        $ztime=time();
                        $info = "VideoWhisper=1&login=1&webKey=$webKey&start=$ztime&canKick=$canKick";

                        $dir=$options['uploadsPath'];
                        if (!file_exists($dir)) mkdir($dir);
                        @chmod($dir, 0777);
                        $dir.="/_sessions";
                        if (!file_exists($dir)) mkdir($dir);
                        @chmod($dir, 0777);

                        $dfile = fopen($dir."/$username","w");
                        fputs($dfile,$info);
                        fclose($dfile);
                        $debug = "$username-sessionCreated";
                    }

                }


                ?>firstParameter=fix&server=<?php echo $rtmp_server?>&serverAMF=<?php echo $rtmp_amf?>&tokenKey=<?php echo $tokenKey?>&serverRTMFP=<?php echo urlencode($serverRTMFP)?>&p2pGroup=<?php
                echo $p2pGroup?>&supportRTMP=<?php echo $supportRTMP?>&supportP2P=<?php echo $supportP2P?>&alwaysRTMP=<?php echo $alwaysRTMP?>&alwaysP2P=<?php echo $alwaysP2P?>&disableBandwidthDetection=<?php echo
                $disableBandwidthDetection?>&room=<?php echo $username?>&welcome=Welcome!&username=<?php echo $username?>&userlabel=<?php echo $userlabel?>&overLogo=<?php echo
                urlencode($options['overLogo'])?>&overLink=<?php echo urlencode($options['overLink'])?>&userType=3&webserver=&msg=<?php echo $msg?>&loggedin=<?php echo
                $loggedin?>&room_limit=&showTimer=1&showCredit=1&disconnectOnTimeout=1&camWidth=480&camHeight=360&camFPS=15&camBandwidth=40960&videoCodec=<?php echo $options['videoCodec']?>&codecProfile=<?php echo
                $options['codecProfile']?>&codecLevel=<?php echo $options['codecLevel']?>&soundCodec=<?php echo $options['soundCodec']?>&soundQuality=<?php echo $options['soundQuality']?>&micRate=<?php echo
                $options['micRate']?>&bufferLive=0.5&bufferFull=8&showCamSettings=1&advancedCamSettings=1&camMaxBandwidth=81920&configureSource=1&generateSnapshots=1&snapshotsTime=60000&onlyVideo=<?php echo
                $options['onlyVideo']?>&noEmbeds=<?php echo $options['noEmbeds']?>&loadstatus=1&debug=<?php echo $debug?><?php
                break;

            case 'vw_extchat':

                $options = get_option('VWliveStreamingOptions');

                $updated = $_POST['t'];
                $room = $_POST['r'];

                //do not allow uploads to other folders
                sanV($room);
                sanV($updated);

                if (!$room) exit;

                if ($room!="null")
                {
                    $dir=$options['uploadsPath'];
                    if (!file_exists($dir)) @mkdir($dir);
                    @chmod($dir, 0755);
                    $dir .= "/".$room;
                    if (!file_exists($dir)) @mkdir($dir);
                    @chmod($dir, 0755);
                    $dir .= "/external";
                    if (!file_exists($dir)) @mkdir($dir);
                    @chmod($dir, 0755);

                    $day=date("y-M-j",time());
                    $fname="$dir/$day.html";


                    $chatText="";

                    if (file_exists($fname))
                    {
                        $chatData = implode('', file($fname));

                        $chatLines=explode(";;\r\n",$chatData);

                        foreach ($chatLines as $line)
                        {
                            $items = explode("\",\"", $line);
                            if (trim($items[0], " \"") > $updated) $chatText .= trim($items[1], " \"");
                        }

                    }
                    $ztime = time();
                }
                ?>chatText=<?php echo urlencode($chatText)?>&updateTime=<?php echo $ztime?><?php
                break;

            case 'vv_login':

                $options = get_option('VWliveStreamingOptions');
                $rtmp_server = $options['rtmp_server'];
                $rtmp_amf = $options['rtmp_amf'];
                $userName =  $options['userName']; if (!$userName) $userName='user_nicename';
                $canWatch = $options['canWatch'];
                $watchList = $options['watchList'];

                $tokenKey = $options['tokenKey'];
                $serverRTMFP = $options['serverRTMFP'];
                $p2pGroup = $options['p2pGroup'];
                $supportRTMP = $options['supportRTMP'];
                $supportP2P = $options['supportP2P'];
                $alwaystRTMP = $options['alwaystRTMP'];
                $alwaystP2P = $options['alwaystP2P'];
                $disableBandwidthDetection = $options['disableBandwidthDetection'];

                get_currentuserinfo();

                $loggedin=0;
                $msg="";
                $visitor=0;

                //username
                if ($current_user->$userName) $username=urlencode($current_user->$userName);
                $username=preg_replace("/[^0-9a-zA-Z]/","-",$username);

                //access keys
                if ($current_user)
                {
                    $userkeys = $current_user->roles;
                    $userkeys[] = $current_user->user_login;
                    $userkeys[] = $current_user->ID;
                    $userkeys[] = $current_user->user_email;
                    $userkeys[] = $current_user->display_name;
                }

                $roomName=$_GET['room_name'];
                sanV($roomName);
                if ($username==$roomName) $username.="_".rand(10,99);//allow viewing own room - session names must be different

                //check room
                global $wpdb;
                $table_name3 = $wpdb->prefix . "vw_lsrooms";
                $wpdb->flush();

                $sql = "SELECT * FROM $table_name3 where name='$roomName'";
                $channel = $wpdb->get_row($sql);
                $wpdb->query($sql);

                if (!$channel)
                {
                    $msg = urlencode("Channel $roomName not found!");
                }
                else
                {

                    if ($channel->type>=2) //premium
                        {
                        if (!$options['pLogo']) $options['overLogo']=$options['overLink']='';
                        $canWatch = $options['canWatchPremium'];
                        $watchList = $options['watchPremium'];
                        $msgp = urlencode(" This is a premium channel.");
                    }


                    switch ($canWatch)
                    {
                    case "all":
                        $loggedin=1;
                        if (!$username)
                        {
                            $username="VW".base_convert((time()-1224350000).rand(0,10),10,36);
                            $visitor=1; //ask for username
                        }
                        break;
                    case "members":
                        if ($username) $loggedin=1;
                        else $msg=urlencode("<a href=\"/\">Please login first or register an account if you don't have one! Click here to return to website.</a>") . $msgp;
                        break;
                    case "list";
                        if ($username)
                            if (inList($userkeys, $watchList)) $loggedin=1;
                            else $msg=urlencode("<a href=\"/\">$username, you are not in the allowed watchers list.</a>") . $msgp;
                            else $msg=urlencode("<a href=\"/\">Please login first or register an account if you don't have one! Click here to return to website.</a>") . $msgp;
                            break;
                    }

                }



                $userType=0;
                $canKick = 0;
                if ($loggedin)  //approve session for rtmp check
                    {
                    //this generates a session file record for rtmp login check
                    sanV($username);

                    if ($username)
                    {
                        $ztime=time();
                        $info = "VideoWhisper=1&login=1&webKey=$webKey&start=$ztime&canKick=$canKick";

                        $dir=$options['uploadsPath'];
                        if (!file_exists($dir)) mkdir($dir);
                        @chmod($dir, 0777);
                        $dir.="/_sessions";
                        if (!file_exists($dir)) mkdir($dir);
                        @chmod($dir, 0777);

                        $dfile = fopen($dir."/$username","w");
                        fputs($dfile,$info);
                        fclose($dfile);
                        $debug = "$username-sessionCreated";
                    }

                }

                ?>server=<?php echo $rtmp_server?>&serverAMF=<?php echo $rtmp_amf?>&tokenKey=<?php echo $tokenKey?>&serverRTMFP=<?php echo urlencode($serverRTMFP)?>&p2pGroup=<?php echo
                $p2pGroup?>&supportRTMP=<?php echo $supportRTMP?>&supportP2P=<?php echo $supportP2P?>&alwaysRTMP=<?php echo $alwaysRTMP?>&alwaysP2P=<?php echo $alwaysP2P?>&disableBandwidthDetection=<?php echo
                $disableBandwidthDetection?>&bufferLive=0.5&bufferFull=8&welcome=Welcome!&username=<?php echo $username?>&userType=<?php echo $userType?>&msg=<?php echo $msg?>&loggedin=<?php echo
                $loggedin?>&visitor=<?php echo $visitor?>&showCredit=1&disconnectOnTimeout=1&offlineMessage=Channel+Offline&overLogo=<?php echo urlencode($options['overLogo'])?>&overLink=<?php echo
                urlencode($options['overLink'])?>&loadstatus=1&debug=<?php echo $debug?><?php
                break;

            case 'vs_login':

                //vs_login.php controls watch interface (video & chat & user list) login

                $options = get_option('VWliveStreamingOptions');
                $rtmp_server = $options['rtmp_server'];
                $rtmp_amf = $options['rtmp_amf'];
                $userName =  $options['userName']; if (!$userName) $userName='user_nicename';
                $canWatch = $options['canWatch'];
                $watchList = $options['watchList'];

                $tokenKey = $options['tokenKey'];
                $serverRTMFP = $options['serverRTMFP'];
                $p2pGroup = $options['p2pGroup'];
                $supportRTMP = $options['supportRTMP'];
                $supportP2P = $options['supportP2P'];
                $alwaystRTMP = $options['alwaystRTMP'];
                $alwaystP2P = $options['alwaystP2P'];
                $disableBandwidthDetection = $options['disableBandwidthDetection'];

                get_currentuserinfo();

                $loggedin=0;
                $msg="";
                $visitor=0;

                //username
                if ($current_user->$userName) $username=urlencode($current_user->$userName);
                $username=preg_replace("/[^0-9a-zA-Z]/","-",$username);

                //access keys
                if ($current_user)
                {
                    $userkeys = $current_user->roles;
                    $userkeys[] = $current_user->user_login;
                    $userkeys[] = $current_user->ID;
                    $userkeys[] = $current_user->user_email;
                    $userkeys[] = $current_user->display_name;
                }

                $roomName=$_GET['room_name'];
                sanV($roomName);

                if ($username==$roomName) $username.="_".rand(10,99);//allow viewing own room - session names must be different

                //check room
                global $wpdb;
                $table_name3 = $wpdb->prefix . "vw_lsrooms";
                $wpdb->flush();

                $sql = "SELECT * FROM $table_name3 where name='$roomName'";
                $channel = $wpdb->get_row($sql);
                $wpdb->query($sql);

                if (!$channel)
                {
                    $msg = urlencode("Channel $roomName not found!");
                }
                else
                {

                    if ($channel->type>=2) //premium
                        {
                        if (!$options['pLogo']) $options['overLogo']=$options['overLink']='';
                        $canWatch = $options['canWatchPremium'];
                        $watchList = $options['watchPremium'];
                        $msgp = urlencode(" This is a premium channel.");
                    }


                    switch ($canWatch)
                    {
                    case "all":
                        $loggedin=1;
                        if (!$username)
                        {
                            $username="VW".base_convert((time()-1224350000).rand(0,10),10,36);
                            $visitor=1; //ask for username
                        }
                        break;
                    case "members":
                        if ($username) $loggedin=1;
                        else $msg=urlencode("<a href=\"/\">Please login first or register an account if you don't have one! Click here to return to website.</a>") . $msgp;
                        break;
                    case "list";
                        if ($username)
                            if (inList($userkeys, $watchList)) $loggedin=1;
                            else $msg=urlencode("<a href=\"/\">$username, you are not in the allowed watchers list.</a>") . $msgp;
                            else $msg=urlencode("<a href=\"/\">Please login first or register an account if you don't have one! Click here to return to website.</a>") . $msgp;
                            break;
                    }

                }


                $userType=0;
                $canKick = 0;
                if ($loggedin)  //approve session for rtmp check
                    {
                    //this generates a session file record for rtmp login check
                    sanV($username);

                    if ($username)
                    {
                        $ztime=time();
                        $info = "VideoWhisper=1&login=1&webKey=$webKey&start=$ztime&canKick=$canKick";

                        $dir=$options['uploadsPath'];
                        if (!file_exists($dir)) mkdir($dir);
                        @chmod($dir, 0777);
                        $dir.="/_sessions";
                        if (!file_exists($dir)) mkdir($dir);
                        @chmod($dir, 0777);

                        $dfile = fopen($dir."/$username","w");
                        fputs($dfile,$info);
                        fclose($dfile);
                        $debug = "$username-sessionCreated";
                    }

                }

                //replace bad words or expressions
                $filterRegex=urlencode("(?i)(fuck|cunt)(?-i)");
                $filterReplace=urlencode(" ** ");

                //fill your layout code between <<<layoutEND and layoutEND;
                $layoutCode=<<<layoutEND
layoutEND;

                if (!$welcome) $welcome="Welcome on <B>".$roomName."</B> live streaming channel!";

                ?>server=<?php echo $rtmp_server?>&serverAMF=<?php echo $rtmp_amf?>&tokenKey=<?php echo $tokenKey?>&serverRTMFP=<?php echo urlencode($serverRTMFP)?>&p2pGroup=<?php echo
                $p2pGroup?>&supportRTMP=<?php echo $supportRTMP?>&supportP2P=<?php echo $supportP2P?>&alwaysRTMP=<?php echo $alwaysRTMP?>&alwaysP2P=<?php echo $alwaysP2P?>&disableBandwidthDetection=<?php echo
                $disableBandwidthDetection?>&bufferLive=0.5&bufferFull=8&welcome=<?php echo urlencode($welcome)?>&username=<?php echo $username?>&userType=<?php echo $userType?>&msg=<?php echo $msg?>&loggedin=<?php
                echo $loggedin?>&visitor=<?php echo $visitor?>&showCredit=1&disconnectOnTimeout=1&offlineMessage=Channel+Offline&overLogo=<?php echo urlencode($options['overLogo'])?>&overLink=<?php echo
                urlencode($options['overLink'])?>&disableVideo=0&disableChat=0&disableUsers=0&layoutCode=<?php echo urlencode($layoutCode)?>&fillWindow=0&filterRegex=<?php echo $filterRegex?>&filterReplace=<?php
                echo $filterReplace?>&loadstatus=1<?php
                break;

            case 'vc_login':

                $options = get_option('VWliveStreamingOptions');

                $rtmp_server = $options['rtmp_server'];
                $rtmp_amf = $options['rtmp_amf'];
                $userName =  $options['userName']; if (!$userName) $userName='user_nicename';
                $canBroadcast = $options['canBroadcast'];
                $broadcastList = $options['broadcastList'];

                $tokenKey = $options['tokenKey'];
                $webKey = $options['webKey'];

                $serverRTMFP = $options['serverRTMFP'];
                $p2pGroup = $options['p2pGroup'];
                $supportRTMP = $options['supportRTMP'];
                $supportP2P = $options['supportP2P'];
                $alwaysRTMP = $options['alwaysRTMP'];
                $alwaysP2P = $options['alwaysP2P'];
                $disableBandwidthDetection = $options['disableBandwidthDetection'];

                $camRes = explode('x',$options['camResolution']);

                get_currentuserinfo();

                $loggedin=0;
                $msg="";


                //username
                if ($current_user->$userName) $username=urlencode($current_user->$userName);
                sanV($username);

                //access keys
                if ($current_user)
                {
                    $userkeys = $current_user->roles;
                    $userkeys[] = $current_user->user_login;
                    $userkeys[] = $current_user->ID;
                    $userkeys[] = $current_user->user_email;
                    $userkeys[] = $current_user->display_name;
                }

                switch ($canBroadcast)
                {
                case "members":
                    if ($username) $loggedin=1;
                    else $msg=urlencode("<a href=\"/\">Please login first or register an account if you don't have one! Click here to return to website.</a>");
                    break;
                case "list";
                    if ($username)
                        if (inList($userkeys, $broadcastList)) $loggedin=1;
                        else $msg=urlencode("<a href=\"/\">$username, you are not in the broadcasters list.</a>");
                        else $msg=urlencode("<a href=\"/\">Please login first or register an account if you don't have one! Click here to return to website.</a>");
                        break;
                }

                //broadcaster
                $userlabel="";
                $room_name=$_GET['room_name'];
                sanV($room_name);

                if ($room_name&&$room_name!=$username)
                {
                    $userlabel=$username;
                    $username=$room_name;
                    $room=$room_name;
                }

                if (!$room) $room = $username;

                if (!$room)
                {
                    $loggedin=0;
                    $msg=urlencode("<a href=\"/\">Can't enter: Room missing!</a>");
                }

                if (!$username)
                {
                    $loggedin=0;
                    $msg=urlencode("<a href=\"/\">Can't enter: Username missing!</a>");
                }


                //channel name
                if ($loggedin)
                {
                    $table_name = $wpdb->prefix . "vw_sessions";
                    $table_name3 = $wpdb->prefix . "vw_lsrooms";
                    global $wpdb;

                    $wpdb->flush();
                    $ztime=time();

                    //online broadcasting session
                    $sql = "SELECT * FROM $table_name where session='$username' and status='1'";
                    $session = $wpdb->get_row($sql);

                    if (!$session)
                        $sql="INSERT INTO `$table_name` ( `session`, `username`, `room`, `message`, `sdate`, `edate`, `status`, `type`) VALUES ('$username', '$username', '$room', '', $ztime, $ztime,
1, 1)";
                    else
                        $sql="UPDATE `$table_name` set edate=$ztime, room='$room', username='$username' where session='$username' and status='1'";

                    $wpdb->query($sql);

                    $exptime=$ztime-30;
                    $sql="DELETE FROM `$table_name` WHERE edate < $exptime";
                    $wpdb->query($sql);

                    //setup/update channel, premium & time reset
                    if (inList($userkeys, $options['premiumList'])) //premium room
                        {
                        $rtype=2;
                        $camBandwidth=$options['pCamBandwidth'];
                        $camMaxBandwidth=$options['pCamMaxBandwidth'];
                        if (!$options['pLogo']) $options['overLogo']=$options['overLink']='';

                    }else
                    {
                        $rtype=1;
                        $camBandwidth=$options['camBandwidth'];
                        $camMaxBandwidth=$options['camMaxBandwidth'];
                    }



                    $sql = "SELECT * FROM $table_name3 where owner='$username' and name='$room'";
                    $channel = $wpdb->get_row($sql);

                    if (!$channel)
                        $sql="INSERT INTO `$table_name3` ( `owner`, `name`, `sdate`, `edate`, `rdate`,`status`, `type`) VALUES ('$username', '$room', $ztime, $ztime, $ztime, 1, $rtype)";
                    elseif ($options['timeReset'] && $channel->rdate < $ztime - $options['timeReset']*24*3600) //time to reset in days
                        $sql="UPDATE `$table_name3` set edate=$ztime, type=$rtype, rdate=$ztime, wtime=0, btime=0 where owner='$username' and name='$room'";
                    else
                        $sql="UPDATE `$table_name3` set edate=$ztime, type=$rtype where owner='$username' and name='$room'";

                    $wpdb->query($sql);

                    //update online broadcasters list
                    $exptime=$ztime-30;
                    $sql="DELETE FROM `$table_name` WHERE edate < $exptime";
                    $wpdb->query($sql);

                }

                $canKick = 1;
                if ($loggedin)  //approve session for rtmp check
                    {
                    //this generates a session file record for rtmp login check
                    sanV($username);

                    if ($username)
                    {
                        $ztime=time();
                        $info = "VideoWhisper=1&login=1&webKey=$webKey&start=$ztime&canKick=$canKick";

                        $dir=$options['uploadsPath'];
                        if (!file_exists($dir)) mkdir($dir);
                        @chmod($dir, 0777);
                        $dir.="/_sessions";
                        if (!file_exists($dir)) mkdir($dir);
                        @chmod($dir, 0777);

                        $dfile = fopen($dir."/$username","w");
                        fputs($dfile,$info);
                        fclose($dfile);
                        $debug = "$username-sessionCreated";
                    }

                }


                function path2url($file, $Protocol='http://') {
                    return $Protocol.$_SERVER['HTTP_HOST'].str_replace($_SERVER['DOCUMENT_ROOT'], '', $file);
                }
                $uploadsPath = $options['uploadsPath'];
                if (!$uploadsPath) { $upload_dir = wp_upload_dir(); $uploadsPath = $upload_dir['basedir'] . '/vwls'; }

                $day = date("y-M-j",time());
                $chatlog_url = path2url($uploadsPath."/$room/Log$day.html");

                $swfurlp = "&prefix=" . urlencode(admin_url() . 'admin-ajax.php?action=vwls&task=');
                $swfurlp .= '&extension='.urlencode('_none_');
                $swfurlp .= '&ws_res=' . urlencode( plugin_dir_url(__FILE__) . 'ls/');

                $linkcode=$base."channel.php?n=".urlencode($username);
                $imagecode=path2url($uploadsPath."/_snapshots/".urlencode($username).".jpg");
                $swfurl= plugin_dir_url(__FILE__) . "ls/live_watch.swf?n=".urlencode($username) . $swfurlp;
                $swfurl2=plugin_dir_url(__FILE__) . "ls/live_video.swf?n=".urlencode($username) . $swfurlp;




                $embedcode =<<<EMBEDEND
<object width="100%" height="100%"><param name="movie" value="$swfurl" type="application/x-shockwave-flash" data="=$swfurl"/><param name="base" value="$base" /><param name="allowFullScreen"
value="true" /><param name="allowscriptaccess" value="always" /></object>
EMBEDEND;
                $embedvcode =<<<EMBEDEND2
<object width="100%" height="100%"><param name="movie" value="$swfurl2" type="application/x-shockwave-flash" data="$swfurl2" /><param name="base" value="$base" /><param name="scale"
value="exactfit"/><param name="allowFullScreen" value="true" /><param name="allowscriptaccess" value="always" /></object>
EMBEDEND2;

                $chatlog="The transcript log of this chat is available at <U><A HREF=\"$chatlog_url\" TARGET=\"_blank\">$chatlog_url</A></U>.";
                if (!$welcome) $welcome="Welcome to broadcasting interface for channel '$room'! . $chatlog";


                ?>firstParameter=fix&server=<?php echo $rtmp_server?>&serverAMF=<?php echo $rtmp_amf?>&tokenKey=<?php echo $tokenKey?>&serverRTMFP=<?php echo urlencode($serverRTMFP)?>&p2pGroup=<?php
                echo $p2pGroup?>&supportRTMP=<?php echo $supportRTMP?>&supportP2P=<?php echo $supportP2P?>&alwaysRTMP=<?php echo $alwaysRTMP?>&alwaysP2P=<?php echo $alwaysP2P?>&disableBandwidthDetection=<?php echo
                $disableBandwidthDetection?>&room=<?php echo $username?>&welcome=<?php echo urlencode($welcome); ?>&username=<?php echo $username?>&userlabel=<?php echo $userlabel?>&overLogo=<?php echo
                urlencode($options['overLogo'])?>&overLink=<?php echo urlencode($options['overLink'])?>&userType=3&webserver=&msg=<?php echo $msg?>&loggedin=<?php echo $loggedin?>&linkcode=<?php echo
                urlencode($linkcode)?>&embedcode=<?php echo urlencode($embedcode)?>&embedvcode=<?php echo urlencode($embedvcode)?>&imagecode=<?php echo
                urlencode($imagecode)?>&room_limit=&showTimer=1&showCredit=1&disconnectOnTimeout=1&camWidth=<?php echo $camRes[0];?>&camHeight=<?php echo $camRes[1];?>&camFPS=<?php echo
                $options['camFPS']?>&camBandwidth=<?php echo $camBandwidth?>&videoCodec=<?php echo $options['videoCodec']?>&codecProfile=<?php echo $options['codecProfile']?>&codecLevel=<?php echo
                $options['codecLevel']?>&soundCodec=<?php echo $options['soundCodec']?>&soundQuality=<?php echo $options['soundQuality']?>&micRate=<?php echo
                $options['micRate']?>&bufferLive=2&bufferFull=2&showCamSettings=1&advancedCamSettings=1&camMaxBandwidth=<?php echo
                $camMaxBandwidth?>&configureSource=1&generateSnapshots=1&snapshotsTime=60000&onlyVideo=<?php echo $options['onlyVideo']?>&noEmbeds=<?php echo $options['noEmbeds']?>&loadstatus=1&debug=<?php echo
                $debug?><?php
                break;

            case 'vc_chatlog':

                //Public and private chat logs
                $private=$_POST['private']; //private chat username, blank if public chat
                $username=$_POST['u'];
                $session=$_POST['s'];
                $room=$_POST['r'];
                $message=$_POST['msg'];
                $time=$_POST['msgtime'];

                //do not allow uploads to other folders
                sanV($room);
                sanV($private);
                sanV($session);
                if (!$room) exit;
                
                $message = strip_tags($messae,'<p><a><img><font><b><i><u>');

                //generate same private room folder for both users
                if ($private)
                {
                    if ($private>$session) $proom=$session ."_". $private; else $proom=$private ."_". $session;
                }

                $options = get_option('VWliveStreamingOptions');
                $dir=$options['uploadsPath'];
                if (!file_exists($dir)) mkdir($dir);
                @chmod($dir, 0777);
                $dir.="/$room";
                if (!file_exists($dir)) mkdir($dir);
                @chmod($dir, 0777);
                if ($proom) $dir.="/$proom";
                if (!file_exists($dir)) mkdir($dir);
                @chmod($dir, 0777);

                $day=date("y-M-j",time());

                $dfile = fopen($dir."/Log$day.html","a");
                fputs($dfile,$message."<BR>");
                fclose($dfile);
                ?>loadstatus=1<?php
                break;

            case 'v_status':

                /*
POST Variables:
u=Username
s=Session, usually same as username
r=Room
ct=session time (in milliseconds)
lt=last session time received from this script in (milliseconds)
*/

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

                $timeUsed = (int) $timeUsed;
                $currentTime = (int) $currentTime;
                $lastTime = (int) $lastTime;

                //exit if no valid session name or room name
                if (!$s) exit;
                if (!$r) exit;

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
                        $sql="UPDATE `$table_name3` set wtime = " . $channel->wtime . " where name='$r'";
                        $wpdb->query($sql);
                    }



                }

                ?>timeTotal=<?php echo $maximumSessionTime?>&timeUsed=<?php echo $timeUsed?>&lastTime=<?php echo $currentTime?>&disconnect=<?php echo $disconnect?>&loadstatus=1<?php
                break;

            case 'rtmp_logout':

                //rtmp server notifies client disconnect here
                $session = $_GET['s'];
                sanV($session);
                if (!$session) exit;

                $options = get_option('VWliveStreamingOptions');
                $dir=$options['uploadsPath'];

                echo "logout=";
                $filename1 = $dir ."/_sessions/$session";
                if (file_exists($filename1))
                {
                    echo unlink($filename1);
                }
                ?><?php
                break;

            case 'rtmp_login':

                //rtmp server should check login like rtmp_login.php?s=$session
                $session = $_GET['s'];
                sanV($session);
                if (!$session) exit;

                $options = get_option('VWliveStreamingOptions');
                $dir=$options['uploadsPath'];

                $filename1 = $dir ."/_sessions/$session";
                if (file_exists($filename1))
                {
                    echo implode('', file($filename1));
                }
                else
                {
                    echo "VideoWhisper=1&login=0";
                }
                ?><?php
                break;

            case 'lb_status':

                /*
Broadcaster status updates.

POST Variables:
u=Username
s=Session, usually same as username
r=Room
ct=session time (in milliseconds)
lt=last session time received from this script in (milliseconds)
cam, mic = 0 none, 1 disabled, 2 enabled
*/

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
                sanV($m,0);
                
                $timeUsed = (int) $timeUsed;
                $currentTime = (int) $currentTime;
                $lastTime = (int) $lastTime;

                //exit if no valid session name or room name
                if (!$s) exit;
                if (!$r) exit;

                //only registered users can broadcast
                if (!is_user_logged_in()) exit;

                $table_name = $wpdb->prefix . "vw_sessions";
                $table_name3 = $wpdb->prefix . "vw_lsrooms";
                $wpdb->flush();

                $ztime=time();

                //room info
                $sql = "SELECT * FROM $table_name3 where owner='$u' and name='$r'";
                $channel = $wpdb->get_row($sql);
                $wpdb->query($sql);

                if (!$channel) $disconnect = urlencode("Channel $r not found!");
                else
                {
                    //user online
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

                    $maximumSessionTime = $maximumBroadcastTime; //broadcaster

                    //update time
                    $dS = floor(($currentTime-$lastTime)/1000);
                    if ($dS>180 || $dS<0) $disconnect = urlencode("Web server out of sync!"); //Updates should be faster than 3 minutes; fraud attempt?
                    else
                    {
                        $channel->btime += $dS;
                        $timeUsed = $channel->btime * 1000;

                        if ($maximumBroadcastTime && $maximumBroadcastTime < $channel->btime ) $disconnect = urlencode("Allocated broadcasting time ended!");
                        if ($maximumWatchTime && $maximumWatchTime < $channel->wtime ) $disconnect = urlencode("Allocated watch time ended!");

                        $maximumSessionTime *=1000;

                        //update
                        $sql="UPDATE `$table_name3` set edate=$ztime, btime = " . $channel->btime . " where owner='$u' and name='$r'";
                        $wpdb->query($sql);
                    }

                }


                ?>timeTotal=<?php echo $maximumSessionTime?>&timeUsed=<?php echo $timeUsed?>&lastTime=<?php echo $currentTime?>&disconnect=<?php echo $disconnect?>&loadstatus=1<?php
                break;

            case 'translation':
                ?><translations>
<t text="Video is Disabled" translation="Video is Disabled"/>
<t text="Bold" translation="Bold"/>
<t text="Sound is Enabled" translation="Sound is Enabled"/>
<t text="Publish a video stream using the settings below without any spaces." translation="Publish a video stream using the settings below without any spaces."/>
<t text="Click Preview for Streaming Settings" translation="Click Preview for Streaming Settings"/>
<t text="DVD NTSC" translation="DVD NTSC"/>
<t text="DVD PAL" translation="DVD PAL"/>
<t text="Video Source" translation="Video Source"/>
<t text="Send" translation="Send"/>
<t text="Cinema" translation="Cinema"/>
<t text="Update Show Title" translation="Update Show Title"/>
<t text="Public Channel: Click to Copy" translation="Public Channel: Click to Copy"/>
<t text="Channel Link" translation="Channel Link"/>
<t text="Kick" translation="Kick"/>
<t text="Embed Channel HTML Code" translation="Embed Channel HTML Code"/>
<t text="Open In Browser" translation="Open In Browser"/>
<t text="Embed Video HTML Code" translation="Embed Video HTML Code"/>
<t text="Snapshot Image Link" translation="Snapshot Image Link"/>
<t text="SD" translation="SD"/>
<t text="External Encoder" translation="External Encoder"/>
<t text="Source" translation="Source"/>
<t text="Very Low" translation="Very Low"/>
<t text="Low" translation="Low"/>
<t text="HDTV" translation="HDTV"/>
<t text="Webcam" translation="Webcam"/>
<t text="Resolution" translation="Resolution"/>
<t text="Emoticons" translation="Emoticons"/>
<t text="HDCAM" translation="HDCAM"/>
<t text="FullHD" translation="FullHD"/>
<t text="Preview Shows as Compressed" translation="Preview Shows as Compressed"/>
<t text="Rate" translation="Rate"/>
<t text="Very Good" translation="Very Good"/>
<t text="Preview Shows as Captured" translation="Preview Shows as Captured"/>
<t text="Framerate" translation="Framerate"/>
<t text="High" translation="High"/>
<t text="Toggle Preview Compression" translation="Toggle Preview Compression"/>
<t text="Latency" translation="Latency"/>
<t text="CD" translation="CD"/>
<t text="Your connection performance:" translation="Your connection performance:"/>
<t text="Small Delay" translation="Small Delay"/>
<t text="Sound Effects" translation="Sound Effects"/>
<t text="Username" translation="Nickname"/>
<t text="Medium Delay" translation="Medium Delay"/>
<t text="Toggle Microphone" translation="Toggle Microphone"/>
<t text="Video is Enabled" translation="Video is Enabled"/>
<t text="Radio" translation="Radio"/>
<t text="Talk" translation="Talk"/>
<t text="Viewers" translation="Viewers"/>
<t text="Toggle External Encoder" translation="Toggle External Encoder"/>
<t text="Sound is Disabled" translation="Sound is Disabled"/>
<t text="Sound Fx" translation="Sound Effects"/>
<t text="Good" translation="Good"/>
<t text="Toggle Webcam" translation="Toggle Webcam"/>
<t text="Bandwidth" translation="Bandwidth"/>
<t text="Underline" translation="Underline"/>
<t text="Select Microphone Device" translation="Select Microphone Device"/>
<t text="Italic" translation="Italic"/>
<t text="Select Webcam Device" translation="Select Webcam Device"/>
<t text="Big Delay" translation="Big Delay"/>
<t text="Excellent" translation="Excellent"/>
<t text="Apply Settings" translation="Apply Settings"/>
<t text="Very High" translation="Very High"/>
</translations>
			<?php
                break;

            case 'ads':

                /* Sample local ads serving script ; Or use http://adinchat.com compatible ads server to setup http://adinchat.com/v/your-campaign-id

POST Variables:
u=Username
s=Session, usually same as username
r=Room
ct=session time (in milliseconds)
lt=last session time received (from web status script)

*/

                $room=$_POST[r];
                $session=$_POST[s];
                $username=$_POST[u];

                $currentTime=$_POST[ct];
                $lastTime=$_POST[lt];

                $ztime=time();

                //fill ad to show
                $ad="<B>Sample Ad</B><BR>Edit ads in ads task. Also edit vs_login task to setup adsInterval in milliseconds (0 to disable ad calls), adsTimeout to setup time in milliseconds until
first ad is shown.  Also see <a href=\"http://www.adinchat.com\" target=\"_blank\"><U><B>AD in Chat</B></U></a> compatible ad management server.";

                ?>x=1&ad=<?php echo urlencode($ad)?>&loadstatus=1<?php
                break;
            } //end case
            die();
        }
    }


}

//instantiate
if (class_exists("VWliveStreaming")) {
    $liveStreaming = new VWliveStreaming();
}

//Actions and Filters
if (isset($liveStreaming)) {

    register_activation_hook( __FILE__, array(&$liveStreaming, 'install' ) );

    add_action( 'init', array(&$liveStreaming, 'channel_post'));

    add_action("plugins_loaded", array(&$liveStreaming, 'init'));
    add_action('admin_menu', array(&$liveStreaming, 'menu'));

    /* Only load code that needs BuddyPress to run once BP is loaded and initialized. */
    function liveStreamingBP_init()
    {
        if (class_exists('BP_Group_Extension')) require( dirname( __FILE__ ) . '/bp.php' );
    }

    add_action( 'bp_init', 'liveStreamingBP_init' );
}
?>
