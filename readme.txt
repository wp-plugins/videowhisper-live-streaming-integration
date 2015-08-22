=== VideoWhisper Live Streaming Integration ===
Contributors: videowhisper, VideoWhisper.com
Author: VideoWhisper.com
Author URI: http://www.videowhisper.com
Plugin Name: VideoWhisper Live Streaming Broadcast
Plugin URI: http://www.videowhisper.com/?p=WordPress+Live+Streaming
Donate link: http://www.videowhisper.com/?p=Invest
Tags: video, live, BuddyPress, broadcast, stream, tv, widget, plugin, media, livestream, channel, sidebar, webcam, cam, twitch, hitbox, justin, ustream, stickam, iOS, wirecast, iPhone, iPad, script, clone, alternative, turnkey, myCRED
Requires at least: 2.7
Tested up to: 4.3
Stable tag: trunk

The VideoWhisper Live Streaming software can easily be used to add video broadcasting features to WordPress sites and live video streams on blog pages and other sites. 

== Description ==
VideoWhisper Live Streaming plugin allows site users and admins to broadcast live streaming channels. 

= Key Features =
* live video channels (custom post type)
* channel setup and management page in frontend
* channel listings with live AJAX updates
* web broadcast with codec and quality settings (H264, Speex)
* IP camera support (restream rtsp, rtmp, rtmps, udp streams)
* iOS transcoding support for iPhone, iPad playback
* automated detection of iOS
* usage permissions by role, email, id, name
* limit broadcasting and watch time per channel
* premium channels (unlimited levels)
* channel stats (broadcast/watch time, last activity)
* P2P groups support for better, faster video streaming and lower rtmp server bandwidth usage
* external broadcaster/player support with special RTMP side (Wirecast, Flash Media Live Encoder, Open Broadcaster Software, iOS GoCoder app)
* generate snapshots for external streams with special RTMP side
* video archive support with [Video Share VOD](http://wordpress.org/plugins/video-share-vod/  "Video Share / Video On Demand") WordPress Plugin
* paid channel support with myCRED integration (owner sets price)
* channel access list (owner sets list of user roles, logins, emails)
* custom floating logo, ads

For more details see the [WordPress Live Video Streaming](http://www.videowhisper.com/?p=WordPress+Live+Streaming "WordPress Live Video Streaming") Plugin Homepage ...

Use this software for setting up features like on Twitch TV, Justin TV, UStream tv, Mogulus, LiveStream, Stickam, Blog tv, Live yahoo or their clones and alternatives.

Includes a widget that can display online broadcasters and their show names.

= Live Site and Showcase =
* [LivOn.TV Live Video Broadcasting](http://livon.tv "LivOn.TV Live Video Broadcasting")
* [How to setup an alternative/clone site like Twitch, Hitbox, Livestream, JustinTv, UStream](http://www.turnkeyclone.com/twitch-tv-script-for-live-broadcasting/ "How to setup an alternative/clone site like Twitch, Hitbox, Livestream, JustinTv, UStream")

= Monetization =
* Membership Ready with Role Permissions: Can be used with membership/subscription plugins to setup paid membership types.
* Pay Per View Ready with Custom Post Type: Can be used with access control / sell content plugins to setup paid access to live broadcasts.
* Custom ads right in text chat box, for increased conversion

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
* Add new Channels, Broadcast Live pages to your menus if not automatically added
* Enable the widget that will add links to the broadcasting interface and current live shows. If you have BuddyPress or use the menus, you can skip this step and allow only group broadcast.
* Insert online channel listings, players with shortocdes documented on setttings page

== Screenshots ==
1. Live Broadcast (for publisher)
2. Live Video Watch (for active viewers, discuss online, see who else is watching)
3. Live Video Streaming (for passive viewers, simple live video)
4. Setup channels in fronted (depending on user permissions)
5. Channels listing with AJAX live updates

== Desktop Sharing / Screen Broadcasting ==
If your users want to broadcast their screen (when playing a game, using a program, tutoring various computer skills) they can do that easily just by using a screen sharing driver that simulates a webcam from desktop contents. Read more on http://www.videochat-scripts.com/screen-sharing-with-flash-video-chat-software/ .

Resulting videos can be managed and imported with [Video Share VOD](http://wordpress.org/plugins/video-share-vod/  "Video Share / Video On Demand") WordPress Plugin .

== Documentation ==
* Plugin Homepage : http://www.videowhisper.com/?p=WordPress+Live+Streaming
* Application Homepage : http://www.videowhisper.com/?p=Live+Streaming
* Developer Contact : http://www.videowhisper.com/tickets_submit.php
* Turnkey Site Project: http://www.turnkeyclone.com/twitch-tv-script-for-live-broadcasting/


== Demo ==
* Test it on demo site http://www.videochat-scripts.com/live-streaming-on-wordpress-by-videowhisper/
* Test it on live site http://livon.tv


== Extra ==
More information, the latest updates, other plugins and non-WordPress editions can be found at http://www.videowhisper.com/ .

== Changelog ==

= 4.32.37 =
* Unlimited premium channel levels
* Feature control by user roles/lists:
** custom/hide logo
** custom/hide ads
** transcode


= 4.32.21 =
* myCRED integration: allow selling access to channels
* channel access list (owner can configure user logins, emails, roles that can access)

= 4.32.8 =
* Improved iOS HLS transcoding reliability (retry and verify automatically)

= 4.32.8 =
* Navigation menus (setup in backend) for Channel Categories

= 4.32.7 =
* Improved channel AJAX listings: list by category in custom order

= 4.32.6 =
* Ban channel interface
* Web server load optimisation settings
* New channel meta

= 4.32.1 =
* Broadcasting application v4.32 (w. autopilot reconnect)

= 4.29.26 =
* Report log file usage in stats.

= 4.29.19 =
* Category and tag archive pages also include channels

= 4.29.17 =
* Display warning on channel page when channel time is exceeded or channel is offline

= 4.29.16 =
* Support for VideoWhisper Video Share / Video On Demand (VOD) plugin

= 4.29.8 =
* iOS detection, automated display of direct/transcoded HLS video 
* external encoder authentication, status monitoring with special RTMP side

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