<?php
/**
 * BU-specific additions to CMB2
 * 01. Filter to include metabox on front page
 * 02. Custom Field Type: Linkpicker
 * 03. Setting limits on Repeatables
 */


/**
 * 01. Filter to include metabox on front page
 * @author Ed Townend
 * @link https://github.com/WebDevStudios/CMB2/wiki/Adding-your-own-show_on-filters#example-front-page-show_on-filter
 *
 * @param bool $display
 * @param array $meta_box
 * @return bool display metabox
 *
 * Usage: 'show_on' => array( 'key' => 'front-page', 'value' => '' )
 */

function ed_metabox_include_front_page( $display, $meta_box ) {
	if ( 'front-page' !== $meta_box['show_on']['key'] )
		return $display;

	// Get the current ID
	if ( isset( $_GET['post'] ) ) {
		$post_id = $_GET['post'];
	} elseif ( isset( $_POST['post_ID'] ) ) {
		$post_id = $_POST['post_ID'];
	}

	//return false early if there is no ID
	if( !isset( $post_id ) ) return false;

	//Get ID of page set as front page, 0 if there isn't one
	$front_page = get_option('page_on_front');

	if ( $post_id == $front_page ) {
		//there is a front page set and we're on it!
		return $display;
	}

}
add_filter( 'cmb2_show_on', 'ed_metabox_include_front_page', 10, 2 );



/**
 * 02. Custom Field Type: Linkpicker
 * @author Steve
 *
 * @param bool $display
 * @param array $meta_box
 * @return bool display metabox
 *
 */
function cmb2_render_callback_for_linkpicker( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {

	wp_enqueue_script( 'bu_cmb2_linkpicker', plugin_dir_url( __FILE__ ) . '/bu-additions/bu-cmb2-linkpicker.js' );

	echo $field_type_object->input( array( 'type' => 'text' ) );
	echo '<input class="button" value="Add or Choose Link" type="button">';
}
add_action( 'cmb2_render_linkpicker', 'cmb2_render_callback_for_linkpicker', 10, 5 );




/**
 * 03. Setting Limits on Repeatables
 * Set a max limit on the number of times a repeating group can be added.
 * @link https://github.com/WebDevStudios/CMB2-Snippet-Library/blob/master/javascript/limit-number-of-repeat-groups.php
 */
function js_limit_group_repeat( $post_id, $cmb ) {
	// Grab the custom attribute to determine the limit
	$limit = absint( $cmb->prop( 'rows_limit' ) );
	$limit = $limit ? $limit : 0;
	?>
	<script type="text/javascript">
	jQuery(document).ready(function($){
		// Only allow 3 groups
		var limit            = <?php echo $limit; ?>;
		var fieldGroupId     = '_cmb2_repeat_group';
		var $fieldGroupTable = $( document.getElementById( fieldGroupId + '_repeat' ) );

		var countRows = function() {
			return $fieldGroupTable.find( '> .cmb-row.cmb-repeatable-grouping' ).length;
		};

		var disableAdder = function() {
			$fieldGroupTable.find('.cmb-add-group-row.button').prop( 'disabled', true );
		};

		var enableAdder = function() {
			$fieldGroupTable.find('.cmb-add-group-row.button').prop( 'disabled', false );
		};

		$fieldGroupTable
			.on( 'cmb2_add_row', function() {
				if ( countRows() >= limit ) {
					disableAdder();
				}
			})
			.on( 'cmb2_remove_row', function() {
				if ( countRows() < limit ) {
					enableAdder();
				}
			});
	});
	</script>
	<?php
}
add_action( 'cmb2_after_post_form_field_group_test', 'js_limit_group_repeat', 10, 2 );
