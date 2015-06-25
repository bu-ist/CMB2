<?php

/**
 * 04. Custom Field Type: Post Chooser
 * @author Steve
 *
 * @param bool $display
 * @param array $meta_box
 * @return bool display metabox
 *
 */
function cmb2_render_callback_for_postchooser( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {
	/*
		In:
			Options
				post_types - array

		Out:
			cmb2 input (hidden) - to store the data
			<a> to show current Title and open modal
			js json object of post names/id's for typeahead

		Front:
			Enqueue typeahead
			Enqueue postchooser script (button click, post selected, hidden input change)
			Enqueue styles
	*/

	$options = $field->args[ 'options' ];
	$given_id = $field->args[ 'id' ];
	$group_id = $given_id . '_group';
	$post_types = array( 'post' );
	?>
	<div class="post-chooser-group" data-selector-id="<?php echo( $group_id ); ?>">
		<a class="thickbox select-post" href="#TB_inline?width=600&height=550&inlineId=<?php echo( $group_id ); ?>">
			<span class="selection-title"><?php echo( ( $escaped_value ) ? get_the_title( $escaped_value ) : '[Select New]' ); ?></span>
		</a>
		<?php echo $field_type_object->input( array( 'type' => 'hidden', 'class' => 'regular-text selection-id' ) ); ?>
	</div>
	<?php

	if( $options['post_types'] ){
		$post_types = $options['post_types'];
	}
	// Modal UI for story, resource and topic selection
	echo( bu_post_chooser_modal( $group_id, 'post', array( 'post_type' => $post_types ) ) );


}
add_action( 'cmb2_render_postchooser', 'cmb2_render_callback_for_postchooser', 10, 5 );


/**
 * Render the object selection modal UI.
 *
 * @param int $id unique identifier for modal.
 * @param str $object_type Object type for selection (post|taxonomy).
 * @param array $args array of query arguments.
 */


function bu_post_chooser_modal( $id, $object_type = 'post', array $args = array() ) {
	$objects = bu_post_chooser_get_objects( $object_type, $args );

	if ( empty( $objects ) ) {
		return;
	}

	// Load 10 most recently created objects
	$recent_objects = array_slice( $objects, 0, 10 );
	$recent_object_options = array();
	foreach ( $recent_objects as $object ) {
		$recent_object_options[] = sprintf( '<option value="%s">%s</option>', $object['id'], $object['name'] );
	}

	// Make the rest available for the typeahead script
	echo "<script type='text/javascript'>var cmbuObjects = window.cmbuObjects || {}; cmbuObjects['". $id . "'] = " . json_encode( $objects ) . ';</script>';

?>
	<div id="<?php echo esc_attr( $id ); ?>" class="post-chooser-modal">
		<div id="<?php echo esc_attr( $id ); ?>-inner">
			<h2>Pick from the latest posts:</h2>
			<select class="choose-dropdown">
				<option>Choose...</option>
				<?php echo implode( "\n", $recent_object_options ); ?>
			</select>

			<h2>Or find one:</h2>
			<div class="choose-typeahead" class="typeahead-box">
				<input class="typeahead" type="text" placeholder="Search..." />
			</div>

			<div class="selection-remove">
				<h2>Or remove this selection:</h2>
				<button class="remove-selection">Remove Post</button>
			</div>

			<div class="selection-new">
				<hr>
				<h2>You have selected:</h2>
				<div class="current-selection"></div>
				<button class="confirm-selection">Confirm Selection</button>
			</div>

			<input class="selection-input" type="hidden" />
		</div>
	</div> <!-- /#<?php echo $id; ?> -->
<?php
}


/**
 * Returns a list of objects formatted for typeahead display.
 */
function bu_post_chooser_get_objects( $object_type, array $args ) {
	$defaults = array(
		'post_type' => array( 'post' )
	);
	$args = wp_parse_args( $args, $defaults );
	$objects = array();

	$query_args = array(
		'post_type'			=> $args['post_type'],
		'posts_per_page'		=> -1,
		'orderby'			=> 'date',
		'order'				=> 'desc'
		);
	$objects = get_posts( $query_args );

	// Format post objects for typeahead
	$objects = array_map( function( $post ) {
		return array( 'name' => $post->post_title, 'id' => $post->ID );
	}, $objects );

	return $objects;
}


function bu_post_chooser_scripts() {
	wp_enqueue_style( 'post-chooser', plugin_dir_url( __FILE__ ) . 'bu-post-chooser.css', array() );
	wp_enqueue_script( 'typeahead', plugin_dir_url( __FILE__ ) . 'typeahead.bundle.min.js', array( 'jquery' ), '0.10.4' );
	wp_enqueue_script( 'post-chooser', plugin_dir_url( __FILE__ ) . 'bu-post-chooser.js', array( 'jquery', 'typeahead' ) );
}

add_action( 'admin_enqueue_scripts', 'bu_post_chooser_scripts' );

