<?php
//BuddyPress Integration

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

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
	
		echo do_shortcode('[videowhisper_broadcast channel="' .$bp->groups->current_group->slug. '"]');
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
		
		echo do_shortcode('[videowhisper_watch channel="' .$bp->groups->current_group->slug. '"]');
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
