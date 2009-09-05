<?php
/*
Plugin Name: VideoWhisper Video Conference
Plugin URI: http://www.videowhisper.com/?p=WordPress+Video+Conference
Description: Video Conference
Version: 1.0
Author: VideoWhisper.com
Author URI: http://www.videowhisper.com/
*/

function videoConference()
{
?><a target="_blank" href="/videowhisper_conference/"><IMG SRC="videowhisper_conference/templates/default/i_uvideo.png" border="0" align="absmiddle"> Video Conference</a>
<?
}

function widget_vwConference($args) {
  extract($args);
  echo $before_widget;
  echo $before_title;?>Video Conference<?php echo $after_title;
  videoConference();
  echo $after_widget;
}

function vwConference_init()
{
  register_sidebar_widget(__('VideoWhisper Conference'), 'widget_vwConference');
}
add_action("plugins_loaded", "vwConference_init");
?>
