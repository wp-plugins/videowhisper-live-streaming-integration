=== VideoWhisper Live Streaming Integration ===
Contributors: videowhisper, VideoWhisper.com
Author: VideoWhisper.com
Author URI: http://www.videowhisper.com
Plugin Name: VideoWhisper Live Streaming Broadcast
Plugin URI: http://www.videowhisper.com/?p=WordPress+Live+Streaming
Donate link: http://www.videowhisper.com/?p=Invest
Tags: video, live, streaming, BuddyPress, broadcast, broadcasting, stream, tv, on air, chat, flash, fms, red5, wowza, audio, video chat, videochat, widget, plugin, media, av, livestream, station, channel, sidebar, webcam, cam, group, groups, tab, P2P
Requires at least: 2.7
Tested up to: 3.2.1
Stable tag: trunk

The VideoWhisper Live Streaming software can easily be used to add video broadcasting features to WordPress sites and live video streams on blog pages. Also integrates with BuddyPress groups if available.

== Description ==
Video Whisper Live Streaming software includes live video broadcast, embeddable live video watch and chat, embeddable live video streaming. Latest version includes P2P groups support for better, faster video streaming and lower rtmp server bandwidth usage.
Use this software to add features like on justin tv, ustream tv, mogulus, livestream, stickam, blog tv, live yahoo or their clones and alternatives.  

This plugin uses the WordPress username to login existing users. If the user is not logged into WordPress a warning message is shown and the visitor can click that to get back to the main WordPress website for registration/login. Administrators can restrict access to broadcasting and watching to certain users.

Also includes a plugin that provides a linking widget. The widget also displays online broadcasters and their show names.

There is a settings page with multiple parameters and permissions (what users can broadcast and watch).

BuddyPress integration: If BuddyPress is installed this will add a Live Stream tab to the group where users can watch live video and chat realtime. Admins can broadcast anytime from Admin > Live Streaming.

Special requirements: This plugin has requirements beyond regular WordPress hosting specifications: a RTMP host is needed for persistent connections to manage live interactions and streaming. More details about this, including solutions are provided on the Installation section pages.

== Installation ==
* See latest version instructions on plugin homepage: http://www.videowhisper.com/?p=WordPress+Live+Streaming
* Before installing this make sure all hosting requirements are met: http://www.videowhisper.com/?p=Requirements
* Install the RTMP application using these instructions: http://www.videowhisper.com/?p=RTMP+Applications
* Copy this plugin folder to your wordpress installation in your plugins folder. You should obtain wp-content/plugins/videowhisper-live-streaming-integration .
* Enable the plugin from Wordpress admin area and fill the "Settings", including rtmp address there.
* Enable the widget that will add links to the broadcasting interface and current live shows. If you have BuddyPress you can skip this step and allow only group broadcast.

== Screenshots ==
1. Live Broadcast (for publisher)
2. Live Video Watch (for active viewers, discuss online, see who else is watching)
3. Live Video Streaming (for passive viewers, simple live video)

== Desktop Sharing / Screen Broadcasting ==
If your users want to broadcast their screen (when playing a game, using a program, tutoring various computer skills) they can do that easily just by using a screen sharing driver that simulates a webcam from desktop contents. Read more on http://www.videochat-scripts.com/screen-sharing-with-flash-video-chat-software/ . 

== Documentation ==
* Plugin Homepage : http://www.videowhisper.com/?p=WordPress+Live+Streaming
* Application Homepage : http://www.videowhisper.com/?p=Live+Streaming
* Forum : http://www.videowhisper.com/forum.php?ftid=14&t=Live-Streaming-video-streaming-live-broadcast

== Demo ==
* See BuddyPress integration live on http://livon.tv/
* See it live on http://www.videochat-scripts.com/live-streaming-on-wordpress-by-videowhisper/

== Extra ==
More information, the latest updates, other plugins and non-WordPress editions can be found at http://www.videowhisper.com/ .

== Changelog ==
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