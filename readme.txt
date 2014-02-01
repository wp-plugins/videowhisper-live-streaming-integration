=== VideoWhisper Live Streaming Integration ===
Contributors: videowhisper, VideoWhisper.com
Author: VideoWhisper.com
Author URI: http://www.videowhisper.com
Plugin Name: VideoWhisper Live Streaming Broadcast
Plugin URI: http://www.videowhisper.com/?p=WordPress+Live+Streaming
Donate link: http://www.videowhisper.com/?p=Invest
Tags: video, live, streaming, BuddyPress, broadcast, broadcasting, stream, tv, on air, chat, flash, fms, red5, wowza, audio, video chat, videochat, widget, plugin, media, av, livestream, station, channel, sidebar, webcam, cam, group, groups, tab, P2P
Requires at least: 2.7
Tested up to: 3.8
Stable tag: trunk

The VideoWhisper Live Streaming software can easily be used to add video broadcasting features to WordPress sites and live video streams on blog pages and other sites. 

== Description ==
VideoWhisper Live Streaming software integrates web applications to: 
1. Broadcast video live,
2. Embed live video streaming and chat, 
3. Embed live video streaming only.

Latest version includes:
* codec and quality settings
* iOS transcoding support for iPhone, iPad playback
* usage permissions by role, email, id, name
* premium channels
* limit broadcasting and watch time per channel
* stats
* P2P groups support for better, faster video streaming and lower rtmp server bandwidth usage
* channel setup and management page in frontend
* channel listings with live AJAX updates
 
Use this software for adding to your site, features like on justin tv, ustream tv, mogulus, livestream, stickam, blog tv, live yahoo or their clones and alternatives.  

This plugin uses the WordPress username to login existing users. If the user is not logged into WordPress a warning message is shown and the visitor can click that to get back to the main WordPress website for registration/login. Administrators can restrict access to broadcasting and watching to certain users.

Also includes a plugin that provides a linking widget. The widget also displays online broadcasters and their show names.

There is a settings page with multiple parameters and permissions (what users can broadcast and watch).

= BuddyPress integration =
If BuddyPress is installed this will add a Live Stream tab to the group where users can watch live video and chat realtime. Admins can broadcast anytime from Admin > Live Streaming.

= Special requirements =
This plugin has requirements beyond regular WordPress hosting specifications: a RTMP host is needed for persistent connections to manage live interactions and streaming. More details about this, including solutions are provided on the Installation section pages.

== Installation ==
* See latest version instructions on plugin homepage: 
http://www.videowhisper.com/?p=WordPress+Live+Streaming
* Before installing this make sure all hosting requirements are met: 
http://www.videowhisper.com/?p=Requirements
* Install the RTMP application using these instructions: 
http://www.videowhisper.com/?p=RTMP+Applications
* Install from repository or copy this plugin folder to your wordpress installation in your plugins folder. You should obtain wp-content/plugins/videowhisper-live-streaming-integration .
* Enable the plugin from Wordpress admin area and fill the "Settings", including rtmp address there.
* Enable the widget that will add links to the broadcasting interface and current live shows. If you have BuddyPress you can skip this step and allow only group broadcast.
* Insert online channel snapshots in posts and pages with [videowhisper livesnapshots] shortcode


== Screenshots ==
1. Live Broadcast (for publisher)
2. Live Video Watch (for active viewers, discuss online, see who else is watching)
3. Live Video Streaming (for passive viewers, simple live video)
4. Setup channels in fronted (depending on user permissions)
5. Channels listing with AJAX live updates

== Desktop Sharing / Screen Broadcasting ==
If your users want to broadcast their screen (when playing a game, using a program, tutoring various computer skills) they can do that easily just by using a screen sharing driver that simulates a webcam from desktop contents. Read more on http://www.videochat-scripts.com/screen-sharing-with-flash-video-chat-software/ . 

== Documentation ==
* Plugin Homepage : http://www.videowhisper.com/?p=WordPress+Live+Streaming
* Application Homepage : http://www.videowhisper.com/?p=Live+Streaming
* Forum : http://www.videowhisper.com/forum.php?ftid=14&t=Live-Streaming-video-streaming-live-broadcast

== Demo ==
* See BuddyPress integration live on http://livon.tv/
* See plan WordPress integration on http://www.videochat-scripts.com/live-streaming-on-wordpress-by-videowhisper/

== Extra ==
More information, the latest updates, other plugins and non-WordPress editions can be found at http://www.videowhisper.com/ .

== Changelog ==

= 4.27.4 =
* Channel posts with frontend management and automated snapshot
* Channel management page where users can setup channes from frontend
* Channels list page, automatically updated with AJAX, pagination
* Shortcodes watch, video, HTML5 HLS, broadcast

= 4.27.3 =
* Improved admin settings with tabs and more options
* Control access by roles, ID, email
* Limit broadcasting and watch time per channel
* Premium channels with better features and quality
* Transcoding for iPhone / iPad support
* Toggle Logo/Watermark
* Channel statistics
* Broadcast directly from backend without widget
* Broadcast link only for logged in users

= 4.27 =
* Broadcaster application v4.27
* Insert online channel snapshots in posts and pages with [videowhisper livesnapshots] shortcode
* RTMP web session check support
* External authentication

= 4.25 =
* Broadcaster application v4.25
* Video & sound codec settings
* Floating watermark settings

= 4.07 =
* Broadcaster application v4.07
* Widget includes counter of room participants for each room

= 4.05 =
* Integrated latest application versions (with broadcaster application v4.05) that include P2P. 
* Added more settings to control P2P / RTMP streaming, secure token if enabled, bandwidth detection.
* Fixed some possible security vulnerabilites for hosts with magic_quotes Off.

= 2.2 =
* BuddyPress integration: If BuddyPress is installed this will add a Live Stream tab to the group where users can watch live video and chat realtime. Admins can broadcast anytime from Admin > Live Streaming.

= 2.1 =
* Permissions for broadcasters (members, list) and watchers (all, members, list).
* Choose name to use in application (display name, login, nice name).

= 2.0 =
* Everything is in the plugin folder to allow automated updates.
* Settings page to fill rtmp address, some broadcaster options.

= 1.0.2 =
* Plugin to integrate live streaming installed in a videowhisper_streaming folder on site root.