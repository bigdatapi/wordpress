<?php
/**
 * Create custom gallery output for the WP gallery shortcode
 *
 * @package WordPress
 * @subpackage Total
 * @since Total 1.0
*/

add_filter( 'post_gallery', 'wpex_custom_wp_gallery', 10, 2 );

function wpex_custom_wp_gallery( $output, $attr) {
	
	// load scripts
	global $post, $wp_locale;

	static $instance = 0;
	$instance++;

	// We're trusting author input, so let's at least make sure it looks like a valid orderby statement
	if ( isset( $attr['orderby'] ) ) {
		$attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );
		if ( !$attr['orderby'] )
			unset( $attr['orderby'] );
	}

	extract(shortcode_atts(array(
		'order'			=> 'ASC',
		'orderby'		=> 'menu_order ID',
		'id'			=> $post->ID,
		'columns'		=> 3,
		'include'		=> '',
		'exclude'		=> '',
		'img_height'	=> '',
		'img_width'		=> ''
	), $attr));

	$id = intval($id);
	if ( 'RAND' == $order ) $orderby = 'none';

	if ( !empty($include) ) {
		$include = preg_replace( '/[^0-9,]+/', '', $include );
		$_attachments = get_posts(
			array(
				'include'			=> $include,
				'post_status'		=> '',
				'inherit'			=> '',
				'post_type'			=> 'attachment',
				'post_mime_type'	=> 'image',
				'order'				=> $order,
				'orderby'			=> $orderby
			)
		);

	$attachments = array();
		foreach ( $_attachments as $key => $val ) {
			$attachments[$val->ID] = $_attachments[$key];
		}
	} elseif ( !empty($exclude) ) {
		$exclude = preg_replace( '/[^0-9,]+/', '', $exclude );
		$attachments = get_children( array('post_parent' => $id, 'exclude' => $exclude, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );
	} else {
		$attachments = get_children( array('post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );
	}

	if ( empty($attachments) )
		return '';

	if ( is_feed() ) {
		$output = "\n";
		foreach ( $attachments as $att_id => $attachment )
			$output .= wp_get_attachment_link($att_id, $size, true) . "\n";
		return $output;
	}

	// Define important vars
	$columns = intval($columns);
	$float = is_rtl() ? 'right' : 'left';

	$output = apply_filters('gallery_style', '<div id="gallery-'. $instance .'" class="gallery galleryid-'. $id .' wpex-gallery wpex-gallery-lightbox clr">');
		
		// Begin Loop
		$gallery_id = $id;
		$count=0;
		foreach ( $attachments as $id => $attachment ) {
			$count++;

			// Full image url
			$full_img = wp_get_attachment_url( $id );
		
			// Set image cropping sizes
			if ( $img_width == '' ) {
				$img_width = get_theme_mod( 'wpex_gallery_image_width', '400' );
			}
			if ( $img_height == '' ) {
				$img_height = get_theme_mod( 'wpex_gallery_image_height', '340' );
			}
			
			// Set correct cropping sizes
			if( $columns > 1 ) {
				$img_url = aq_resize( $full_img,  $img_width, $img_height, true );
			} else {
				$img_url = wp_get_attachment_url( $id );
			}
	
			// Start Gallery Item
			$output .= '<div class="gallery-item '. wpex_grid_class($columns) .' col col-'. $count .'">';
			
				// Display image
				$output .= '
					<div class="gallery-icon">
						<a href="'. $full_img .'" title="'. $attachment->post_title .'">
							<img src="'. $img_url .'" alt="'. get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ) .'" />
						</a>
					</div><!-- /gallery-icon -->';
				
				/* Show caption if there is one	
				if ( trim($attachment->post_excerpt) ) {
					$output .= '<div class="gallery-caption">'. wptexturize($attachment->post_excerpt) . '</div>';
				} */
				
			// Clear floats and close gallery item div
			$output .= "</div>";

					
			// Set vars to remove margin on last item of each row and clear floats
			if( $count == $columns ) {
				$count='0';
			}
			
		}

	// Clear floats and close gallery div
	$output .= "</div>\n";

	return $output;
}