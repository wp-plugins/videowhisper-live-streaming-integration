<?php
//BuddyPress Integration

class liveStreamingGroup extends BP_Group_Extension {	

var $visibility = 'public'; // 'public' will show your extension to non-group members, 'private' means you have to be a member of the group to view your extension.

var $enable_create_step = true; // If your extension does not need a creation step, set this to false
var $enable_nav_item = true; // If your extension does not need a navigation item, set this to false
var $enable_edit_item = true; // If your extension does not need an edit screen, set this to false

	function livestreaminggroup() {
		
		$this->name = 'Live Streaming';
		$this->slug = 'live-streaming';

		$this->create_step_position = 21;
		$this->nav_item_position = 31;
	}

	function create_screen() {
		if ( !bp_is_group_creation_step( $this->slug ) )
			return false;
		?>

		<p>To stream live video on this group just go to Admin > Live Streaming.</p>

		<?php
		wp_nonce_field( 'groups_create_save_' . $this->slug );
	}

	function create_screen_save() {
		global $bp;

		check_admin_referer( 'groups_create_save_' . $this->slug );

		/* Save any details submitted here */
		groups_update_groupmeta( $bp->groups->new_group_id, 'my_meta_name', 'value' );
	}

	function edit_screen() {
		if ( !bp_is_group_admin_screen( $this->slug ) )
			return false; 
	?>
				<h2><?php echo attribute_escape( $this->name ) ?></h2>
	<?php
		global $bp;
		$root_url = get_bloginfo( "url" ) . "/";	
		
		global $wpdb;
		$table_name = $wpdb->prefix . "vw_sessions";
		$wpdb->flush();

		$sql = "SELECT * FROM $table_name where session='" . $bp->groups->current_group->slug . "' and status='1'";
	
	$session = $wpdb->get_row($sql);
	if ($session)
	{
		?>
		<p>A live broadcast session is already in progress for this group. Click <a href="<?php echo $root_url."groups/". $bp->groups->current_group->slug . "/".$this->slug ."/"; ?> ">here</a> to watch.</p>
		<?php
	}
	else
	{
	
		$baseurl=$root_url . "wp-content/plugins/videowhisper-live-streaming-integration/ls/";
		$swfurl=$baseurl."live_broadcast.swf?room=".urlencode($bp->groups->current_group->slug);
		?>
	    <div id="videowhisper_livestreaming" style="height:500px" >
		<object width="100%" height="100%">
        <param name="movie" value="<?=$swfurl?>" /><param name="base" value="<?=$baseurl?>" /><param name="scale" value="noscale" /><param name="wmode" value="transparent" /><param name="salign" value="lt"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed width="100%" height="100%" scale="noscale" salign="lt" src="<?=$swfurl?>" base="<?=$baseurl?>" wmode="transparent" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true"></embed>
        </object>
		<noscript>
		<p align="center"><strong>Video Whisper <a href="http://www.videowhisper.com/?p=Live+Streaming">Live Web Video Streaming Software</a> requires the Adobe Flash Player:
		<a href="http://get.adobe.com/flashplayer/">Get Latest Flash</a></strong>!</p>
		</noscript>
		</div>
		<?php
	}	
	}

	function edit_screen_save() {
		global $bp;

		if ( !isset( $_POST['save'] ) )
			return false;

		check_admin_referer( 'groups_edit_save_' . $this->slug );

		/* Insert your edit screen save code here */

		/* To post an error/success message to the screen, use the following */
		if ( !$success )
			bp_core_add_message( __( 'There was an error saving, please try again', 'buddypress' ), 'error' );
		else
			bp_core_add_message( __( 'Settings saved successfully', 'buddypress' ) );

		bp_core_redirect( bp_get_group_permalink( $bp->groups->current_group ) . '/admin/' . $this->slug );
	}

	function display() {
		/* Use this function to display the actual content of your group extension when the nav item is selected */
		global $bp;
		$root_url = get_bloginfo( "url" ) . "/";
		
		$baseurl=$root_url . "wp-content/plugins/videowhisper-live-streaming-integration/ls/";
		$swfurl=$baseurl."live_watch.swf?n=".urlencode($bp->groups->current_group->slug);
		?>
	    <div id="videowhisper_livestreaming" style="height:350px" >
		<object width="100%" height="100%">
        <param name="movie" value="<?=$swfurl?>" /><param name="base" value="<?=$baseurl?>" /><param name="scale" value="noscale" /><param name="salign" value="lt"></param><param name="wmode" value="transparent" /><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed width="100%" height="100%" scale="noscale" salign="lt" src="<?=$swfurl?>" base="<?=$baseurl?>"  wmode="transparent"  type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true"></embed>
        </object>
		<noscript>
		<p align="center"><strong>Video Whisper <a href="http://www.videowhisper.com/?p=Live+Streaming">Live Web Video Streaming Software</a> requires the Adobe Flash Player:
		<a href="http://get.adobe.com/flashplayer/">Get Latest Flash</a></strong>!</p>
		</noscript>
		</div>
			<?
	}

	function widget_display() { ?>
		<div class="info-group">
			<h4><?php echo attribute_escape( $this->name ) ?></h4>
			<p>
				Group Live Streaming allows broadcasting a live video on the group.
			</p>
		</div>
		test
		<?php
	}
}


bp_register_group_extension( 'liveStreamingGroup' );
