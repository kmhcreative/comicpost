<?php
/*
	ComicPost
	This file contains all the "Front-End" functions
*/

/* 	CSS HOOK TO DIFFERENTLY STYLE COMIC TITLES, STORIES, OR CHAPTERS
	================================================================
	This appends the BODY tag classes with the "chapter" slug and
	then finds the rest of the hierachy back to the root chapter.
	Target them in CSS with "story-{tax->slug}"
*/

function comicpost_chapter_to_class($classes = '', $post){
	if (!empty($post) && $post->post_type == 'comic'){
		$terms = wp_get_object_terms( $post->ID, 'chapters');
		foreach ($terms as $term){
			$classes[] = 'story-'.$term->slug;
			// get root ancestor
			$ancestors = get_ancestors( $term->term_id, 'chapters' );
			if (!empty($ancestors)){
				$classes[] = 'story-'.$term->slug;
			    foreach ( $ancestors as $ancestor ){
			    	$story = get_term( $ancestor, 'chapters');
			    	$classes[] = 'story-'.$story->slug;
			    }
			}
		}

	}
	if (!empty($post) && is_page() ){
		$classes[] = 'page-'.$post->post_name;
	}
	return $classes;
};

function comicpost_body_class($classes = '') {
	global $post, $wp_query;
	return comicpost_chapter_to_class($classes, $post);
};
add_filter('body_class', 'comicpost_body_class');

/* Over-ride Archive sort order so comics can be read in order */
add_action( 'pre_get_posts', 'comicpost_new_sort_order'); 
function comicpost_new_sort_order($query){
	if(!is_admin() && is_archive() && (is_post_type_archive( "comic" ))||is_tax("chapters")) {
		//Set the order ASC or DESC
		$query->set( 'order', 'ASC' );
		//Set the orderby
		$query->set( 'orderby', 'date' );

		// get options...
		$options = get_option('comicpost_options');		
		// adjust number of items to return per page
		if ( $options['archive_post_count'] != get_option( 'posts_per_page' ) ){
			$query->set( 'posts_per_page', $options['archive_post_count'] );
		}

		// restrict time
		if ( $options['hide_old_comics'] != 'x' && $options['hide_old_comic_posts'] && !is_user_logged_in() ){
			$days = (int) $options['hide_old_comics'];
			$date_query = array(
				'after' => date('Y-m-d', strtotime('-'.$days.' day')),
			);
			$query->set( 'date_query', $date_query );
		}
	};
};

/*	If COMPLETELY Hide Old Comics is enabled remove them from main query.
	To prevent your site from looking broken, though, you may want to add a conditional to your 404 page like this:
			global $wp_query;
			$options = get_option('comicpost_options');
			if ( $options['hide_old_comics'] != 'x' && $wp_query && $wp_query->query_vars['post_type'] && $wp_query->query_vars['post_type'] == 'comic' ){...
*/

add_action( 'pre_get_posts', 'comicpost_hide_old_posts');
function comicpost_hide_old_posts( $query ){
	if (!is_admin() && !is_home() && $query->is_main_query()){
		$options = get_option('comicpost_options');
		if ( (!empty($query->query_vars['post_type']) && $query->query_vars['post_type'] == 'comic') &&  $options['hide_old_comics'] != 'x' && $options['hide_old_comic_posts'] && !is_user_logged_in() ){
			$days = (int) $options['hide_old_comics'];
			$date_query = array(
				'after' => date('Y-m-d', strtotime('-'.$days.' day')),
			);
			$query->set( 'date_query', $date_query );
		}
	}

}

// Check if Comic Post TEXT Content should be hidden from Guests
function comicpost_hide_from_guests( $content ) {
    global $post;
    if ( $post->post_type == 'comic' ) {
    	$options = get_option('comicpost_options');
        if ( !empty($options['content_login']) && !is_user_logged_in() ) {
            $content = '<p>You must be <a href="'.esc_url( wp_login_url( get_permalink() ) ).'" alt="Login">logged in</a> to see this content.</p>';
        }
    }
    return $content;
}

add_filter( 'the_content', 'comicpost_hide_from_guests', 10, 1 );


// Check Post Date for Beig OLD
function comicpost_is_old( $days = 0 ){
	if ($days == 0) { return false; }
	$days = (int) $days;
	$offset = $days*60*60*24;
	if ( get_post_time() < date('U') - $offset){
		return true;
	} else {
		return false;
	}
}

/* Utility Encoding Functions */
	function comicpost_JS_charCodeAt($str, $index) {
		$utf16 = mb_convert_encoding($str, 'UTF-16LE', 'UTF-8');
		return ord($utf16[$index*2]) + (ord($utf16[$index*2+1]) << 8);
	}

	function comicpost_make2DigitsLong($value){
		if (strlen($value) === 1) {
			$value = '0'.$value;
		}
		return $value;
	}
	function comicpost_encodeString( $content, $key = null) {
		// if key is empty generate a random number
		if (!$key) {
			$key = floor(rand(0,255));
		}
		// Hex encode the key
		$encodedKey = base_convert(strval($key), 10, 16);
		// ensure it is two digits long
		$encodedString = comicpost_make2DigitsLong($encodedKey);

		// loop through every character in the email
		for($n=0; $n < strlen($content); $n++) {

			// Get the code (in decimal) for the nth character
			$charCode = comicpost_JS_charCodeAt($content,$n);

			// XOR the character with the key
			$encoded = $charCode ^ $key;

			// Hex encode the result, and append to the output string
			$value = base_convert(strval($encoded), 10, 16);

			$encodedString .= comicpost_make2DigitsLong($value);

		}
		return $encodedString;	
	}

function comicpost_scripts_and_styles() {
	$options = get_option('comicpost_options');
	if (!empty($options['keyboard_navigation'])){
			wp_enqueue_script( 'cp_navigation',  comicpost_pluginfo('plugin_url') . 'js/comicpost-navigation.js', array(), '0.1', true );
	}
	// decoder is always loaded in case shortcode encoding was used
	wp_enqueue_script( 'string_decoder', comicpost_pluginfo('plugin_url') . 'js/comicpost-decoder.js', array(), '0.1', true );
	wp_enqueue_script( 'mastodon_share', comicpost_pluginfo('plugin_url') . 'js/some.js', array(), '1.0', true );
	// comicpost stylesheets...
	wp_enqueue_style( 'comicpost_nav',  comicpost_pluginfo('plugin_url') . 'css/comicpost-nav.css', '', '0.1');
	wp_enqueue_style( 'comicpost_share', comicpost_pluginfo('plugin_url') .'css/social-buttons.css', '', '0.1');
	// comicpost ratings systems
	wp_register_style( 'comicpost-rating-styles', comicpost_pluginfo('plugin_url') . 'css/comicpost-ratings.css', '', '0.1');
	wp_enqueue_style( 'dashicons' );
	wp_enqueue_style( 'comicpost-rating-styles' );
}
add_action( 'wp_enqueue_scripts', 'comicpost_scripts_and_styles' );	

// clean image helper function
function comicpost_check_image_file_exists( $url ){
	$response = wp_remote_head( $url );
	return 200 === wp_remote_retrieve_response_code( $response );
};
// wrapper for wp_get_attachment_image_src that also looks for clean version
function comicpost_get_attachment_image_src( $attachment_id, $size = 'thumbnail', $icon = false ){
	/* Returns Array or false
		[0] = url,    sting
		[1] = width,  integer
		[2] = height, integer
		[3] = boolean true|false whether image is resized or not
		[4] = is_intermediate ? no clue what this means
		[5] = clean | ''
	*/
	$options = get_option('comicpost_options');
	$image = wp_get_attachment_image_src( $attachment_id, $size, $icon );
	// if show clean copies to logged in users is checked and clean suffix field is not empty
	if ( !empty($options['show_clean_to_loggedin']) && $options['watermark_clean_suffix'] != '' && is_user_logged_in() ){
		// see if you can find a clean copy of the image
		$filename  = pathinfo( $image[0], PATHINFO_FILENAME );
		$suffix = $options['watermark_clean_suffix'];
		$newfilepath = str_replace($filename, $filename.'_'.$suffix, $image[0]);
		if ( comicpost_check_image_file_exists( $newfilepath ) ){
			$image[0] = $newfilepath;
			$image[5] = 'clean';
			return $image;
		} else {
			$image[5] = '';
			return $image;
		}
	} else {
		$image[5] = '';
		return $image;
	}
}


/*	COMIC IMAGE FUNCTION
	====================
	This function is used by Featured Image, Insert Comic Shortcode, Archives, and Search
*/
function comicpost_comic_image ($html, $post_id, $post_thumbnail_id, $size, $attr){	
		// get any user options
		$options = get_option('comicpost_options');
		// normally we want comic nav on comic posts but not in archives or searches
		if (is_archive() || is_search()){
			$comicnav = false;
		} else {
			$comicnav = true;
		}

		$link = false;		// on comic posts we don't want it linked to itself
		
		// get any shortcode options
		$shortcode = false;
		$protect   = '';
		$login     = false;
		$classes   = [];
		if (!empty($attr['shortcode'])){
			$shortcode = true;
			if (!empty($attr['comicnav'])){ 
				$comicnav = $attr['comicnav'];
			} else {
				$comicnav = false;
			}
			if (!empty($attr['protect'])){ $protect = $attr['protect'];}
			if (!empty($attr['link'])){ 
				$link = $attr['link'];	// shortcode option
			} else {
				$link = true; // normally a shortcode would link to the comic post
			}
			if (!empty($attr['login'])){
				if ($attr['login'] == 'required'){$login = true; };
			}
			$classes = comicpost_chapter_to_class($classes, get_post($post_id));
		}
		// turn protect string into an array
		$protect = explode(',',$protect);
		// if shortcode disables printing add class
		if ( in_array('noprint',$protect) ){
			$classes[] = 'noprint';
		}
		// make archive thumbnails into links?
		if ( !empty($link) || (!empty($options['archive_thumb_links']) && (is_archive() || is_search())) ){
			// see if it is a link override
			if ( wp_http_validate_url( $link )){
				$link1 = '<a href="' . $link . '" rel="bookmark" title="' .get_the_title(). '">';
				$link2 = '</a>';
			} else {
				$link1 = '<a href="' . get_permalink($post_id) . '" rel="bookmark" title="' . get_the_title() . '">';
				$link2 = '</a>';
			}
		} else {
			$link1 = '';
			$link2 = '';
		}
		// do not add nav or widget spaces to archive thumbnails
		if ( (is_archive() || is_search()) && empty($options['apply_to_archives'])){
			$html = $link1.wp_get_attachment_image( $post_thumbnail_id, $size, false, $attr ).$link2;
			return $html;
		}
		// Get Comic Navigation
		if ($comicnav == true && !is_archive() && !is_search() ){
			ob_start();
			comicpost_display_comic_navigation();
			$nav = ob_get_clean();		
		} else {
			$nav = '';
		}
		// Get Widget Spaces Around comic
		if (!$shortcode && !is_archive() && !is_search() ){			
			if ( $options['enable_comic_widgets'] ) {
				$classes[] = 'widgetized';
				ob_start();
				dynamic_sidebar('comicpost-sidebar-over-comic');
				$comic_over = '<div class="over-comic">'.ob_get_clean().'</div>';
				$comic_flex_start = '<div class="middleflex">';
				ob_start();
				dynamic_sidebar('comicpost-sidebar-left-of-comic');
				$comic_left = '<div class="left-of-comic">'.ob_get_clean().'</div>';
				ob_start();
				dynamic_sidebar('comicpost-sidebar-right-of-comic');
				$comic_right = '<div class="right-of-comic">'.ob_get_clean().'</div>';
				$comic_flex_end = '</div>';
				ob_start();
				dynamic_sidebar('comicpost-sidebar-under-comic');
				$comic_under = '<div class="under-comic">'.ob_get_clean().'</div>';
			} else {
				$comic_over = $comic_flex_start = $comic_left = $comic_right = $comic_flex_end = $comic_under = '';
			}
			if ( $options['set_comicpost_size'] != 'theme' ){
				$size = $options['set_comicpost_size'];
			}
		} else {
			$comic_over = '';
			$comic_flex_start = '';
			$comic_left = '';
			$comic_right = '';
			$comic_flex_end = '';
			$comic_under = '';
		}
		// Hide Old Comic Images but NOT entire old Comic Posts?  Shortcode intentionally overrides this.
		if ( $shortcode == false && $options['hide_old_comics'] != 'x' && empty($options['hide_old_comic_posts']) && !is_user_logged_in() ){
			if ( comicpost_is_old( $options['hide_old_comics'] ) ){
				$image = wp_get_attachment_image_src($post_thumbnail_id, $size);
				$html = '<div class="comicpost-wrap">'.$comic_over.$comic_left.
				'<div class="comic require-login"><p>ARCHIVED. <a href="'.esc_url( wp_login_url( get_permalink() ) ).'" alt="Login">Log in</a> to read this comic.</p><svg width="'.$image[1].'" height="'.$image[2].'" viewBox="0 0 100 100" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xml:space="preserve" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;max-width:100%;height:auto;" role="img">
				</svg></div>'.$comic_right.$comic_under;
				if ( !is_archive() && !is_search() ){ $html .= '<div class="comic-foot"></div>';}
				$html .= '</div>';
				return $html;
			}
		}

		// Show comics only to logged in users, otherwise do this
		if ( ($login == true || !empty($options['require_login'])) && !is_user_logged_in() ){
			$image = wp_get_attachment_image_src($post_thumbnail_id, $size);
			$html = '<div class="comicpost-wrap">'.$comic_over.$comic_left.
			'<div class="comic require-login"><p>You must be <a href="'.esc_url( wp_login_url( get_permalink() ) ).'" alt="Login">logged in</a> to read this comic.</p><svg width="'.$image[1].'" height="'.$image[2].'" viewBox="0 0 100 100" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xml:space="preserve" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;max-width:100%;height:auto;" role="img">
			</svg></div>'.$comic_right.$comic_under;
			if ( !is_archive() && !is_search() ){ $html .= '<div class="comic-foot"></div>';}
			$html .= '</div>';
			return $html;
		}
		
		$alt_text = esc_html(get_post_meta($post_thumbnail_id, '_wp_attachment_image_alt', true));
		$apply_to_all = true;
		
		// Comic Security Features
		$comic_container1 = '<div class="comic">';
		$comic_container2 = '';
		$comic_container3 = '</div>';
		// If only applied to public-facing images, check if user is logged in
		if (!empty($options['apply_to_public']) && is_user_logged_in() && empty($protect) ){
			$image = [];
			$apply_to_all = false;
		} else {
			if ( $options['comics_under_glass'] || in_array('glass',$protect) ){
				$image = comicpost_get_attachment_image_src($post_thumbnail_id, $size);
				if (!empty($image)){
					if ( $options['encode_comic_urls'] || in_array('encode',$protect) ){
						$xtra_class = " comicpost-protected";
						$xtra_attr  = 'data-type="background"';
						$xtra_data  = 'data-content="'.comicpost_encodeString($image[0]).'"';
						$xtra_bg    = '';
					} else {
						$xtra_bg	= "background-image:url('".$image[0]."');";
						$xtra_data  = '';
						$xtra_attr  = '';
						$xtra_class = '';
					}	 
					$comic_container1 = '<div class="comic'.$xtra_class.'" '.$xtra_attr.' '.$xtra_data.' style="position:relative;width:fit-content;margin:0 auto;background-color:transparent;background-repeat:no-repeat;background-position:center center;background-size:contain;'.$xtra_bg.'">';
					$comic_container2 = $link1.'<div class="comicpost-glass" style="position:absolute;top:0;left:0;height:100%;width:100%;background:transparent url(\''.comicpost_pluginfo('plugin_url').'images/clear.png\');"></div>'.$link2;
					$comic_container3 = '</div>';
				}
			} else {
				$image = [];
			}	
		}
		// now bust apart the classes into a space-separated string
		$classes = implode(' ',$classes);			
		$html = '<div class="comicpost-wrap comic-id-'.$post_id.' '.$classes.'">';
		if ($comicnav == true){
			$html .= '<div class="insert-header">';
			if ($options['navigation_location'] == 'above'){
				$html .= $nav;
			};
			$html .= '</div>';
		}
		$html .= '' 
		. $comic_over 
		. $comic_flex_start
		. $comic_left
		. $comic_container1;
		if (!empty($image)){
			/* 	if $image is populated comic image was moved into background image in $comic_container1 so replace it with clear SVG image here
				this attempts to preserve image title and alt-text for screen readers, though accessibility support for SVGs is uneven. This also
				takes advantage of a quirk of SVG images that means the "intrinsic size" of the image is whatever height and width we give it,
				whereas with any raster image it uses the actual dimensions and ignores the inline height and width as soon as we define either
				height or width as "auto" in our stylesheet. That's why this is using an empty SVG image.
			*/
			$html .= '<svg width="'.$image[1].'" height="'.$image[2].'" viewBox="0 0 100 100" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xml:space="preserve" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;max-width:100%;height:auto;" aria-labelledby="title_'.$post_id.' desc_'.$post_id.'" role="img">
			<title id="title_'.$post_id.'">'.get_the_title().'</title><desc id="desc_'.$post_id.'">'.$alt_text.'</desc>
			</svg>';


		} else if ( $options['encode_comic_urls'] && $apply_to_all ){
			// $image is not populated and we are encoding urls and applying to all comic images				
			$image = comicpost_get_attachment_image_src($post_thumbnail_id, $size);
			$xtra_class = " comicpost-protected";
			$xtra_attr  = 'data-type="image"';
			$xtra_data  = 'data-content="'.comicpost_encodeString($image[0]).'"';
			$html .= $link1.'<img src="'.comicpost_pluginfo('plugin_url').'images/clear.png" class="attachment-'.$size.' size-'.$size.$xtra_class.'" '.$xtra_attr.' '.$xtra_data.' width="'.$image[1].'" height="'.$image[2].'" decoding="async" alt="'.$alt_text.'"/>'.$link2;
		} else {
			// $image is no populated but we need to check for a clean image
			$image = comicpost_get_attachment_image_src($post_thumbnail_id, $size);
			if ($image[5] == 'clean'){
				$html .= $link1.'<img src="'.$image[0].'" class="attachment-'.$size.' size-'.$size.'" width="'.$image[1].'" height="'.$image[2].'" decoding="async" alt="'.$alt_text.'"/>'.$link2;
			} else {
				// $image is not populated, urls are not being encoded, and/or we are not applying comic security to all images so deliver standard thumbnail
 				$html .= $link1.wp_get_attachment_image( $post_thumbnail_id, $size, false, $attr ).$link2;
 			}
 		}
 		
		$html .= $comic_container2 . $comic_container3	
		. $comic_right . $comic_flex_end . $comic_under;
		if ($comicnav == true){
			$html .= '<div class="comic-foot">';
			if ($options['navigation_location'] == 'below'){ 
				$html .= $nav;
			}
			$html .= '</div>';
		}
	$html .= '</div>';

	return $html;


}


/* No injection, we'll just filter the Featured Image HTML and make it into a Comic Area */

function featured_image_to_comic ($html, $post_id, $post_thumbnail_id, $size, $attr){
	if (get_post_type($post_id) == 'comic' && has_post_thumbnail($post_id) ){
		$html = comicpost_comic_image($html, $post_id, $post_thumbnail_id, $size, $attr);
	}
	return $html;
} 
add_filter( 'post_thumbnail_html', 'featured_image_to_comic', 20, 5 );

add_action( 'wp_footer', 'comicpost_discourage_printing', 5);
function comicpost_discourage_printing(){
	$options = get_option('comicpost_options');
	if ( !empty($options['apply_to_public']) && is_user_logged_in() ){
		// do not apply anti-printing measures
	} else {
		if ( $options['discourage_printing'] != 'allowprint' ){
		 	if ($options['discourage_printing'] == 'fauxmark' ){
				if ( !empty($options['faux_watermark_text']) ){
					$text = $options['faux_watermark_text'];
				} else {
					$text = get_bloginfo();
				}
				if ( !empty($options['faux_watermark_opacity']) ){
					$opacity = $options['faux_watermark_opacity'];
				} else {
					$opacity = '.25';
				}
				$method = '';
				if ( !empty($options['faux_watermark_method']) ){
					if ( $options['faux_watermark_method'] == 'center' ){
						$method = 'background-repeat: no-repeat !important; background-size: contain !important; background-position: center center !important;';
					}
				};
?>
<style type="text/css">
@media print {
	.comicpost-glass {
		background-image: url('data:image/svg+xml,<svg width="100px" height="100px" viewBox="0 0 100 100" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xml:space="preserve" xmlns:serif="http://www.serif.com/" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;"><g transform="matrix(0.707107,0.707107,-0.707107,0.707107,13.2715,18.0373)"><text x="0px" y="0px" style="font-family:ArialMT, Arial, sans-serif;font-size:12px;"><?php echo $text; ?></text></g></svg>') !important;
		opacity: <?php echo $opacity; ?>;
		<?php echo $method; ?>
	}
};
</style>
<?php 		}
			if ( $options['discourage_printing'] == 'noprinting' ){
?>
<style type="text/css">
@media print {
	.comicpost-glass {
		opacity: 1;
		box-shadow: inset 1000px 1000px 1000px white;
	}
	div.comic {
		background: none !important;
		box-sizing: border-box;
		border: 3px solid black;
	}
	.comicpost-glass::before {
		content: "SORRY, PRINTING IS NOT ALLOWED";
		text-align: center;
		font-size: 36px;
		display: block;
	}
}
</style>
<?php	
			}
		}; // end of discourage_printing check
	};
};

function remove_admin_bar() {
	if (!is_admin()) {
		$options = get_option('comicpost_options');
		if ($options['remove_admin_bar']){
			add_filter('show_admin_bar', '__return_false');
			remove_action( 'wp_head', '_admin_bar_bump_cb');
		}
	}
};
add_action('admin_bar_init','remove_admin_bar');


function my_post_gallery( $output, $attr) {
	// if not enabled in options bail	
	$options = get_option('comicpost_options');
	if (empty($options['omit_from_galleries'])){
		return;
	}
	// otherwise proceed with the omitting...
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
        'order'      => 'ASC',
        'orderby'    => 'menu_order ID',
        'id'         => $post->ID,
        'itemtag'    => 'dl',
        'icontag'    => 'dt',
        'captiontag' => 'dd',
        'columns'    => 3,
        'size'       => 'thumbnail',
        'include'    => '',
        'exclude'    => ''
    ), $attr));

    $id = intval($id);
    if ( 'RAND' == $order )
        $orderby = 'none';

    if ( !empty($include) ) {
        $include = preg_replace( '/[^0-9,]+/', '', $include );
        $_attachments = get_posts( array('include' => $include, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );
        $attachments = array();
        // here is where the omitting magic happens:
        foreach ( $_attachments as $key => $val ) {
        	$omit = false;
        	if ($val->post_parent){
        		$parent = $val->post_parent;
        		if (get_post_type($parent) == 'comic' ){
        			$omit = true;
        		}
        	}
        	if (!$omit){
            	$attachments[$val->ID] = $_attachments[$key];
            }
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

    $itemtag = tag_escape($itemtag);
    $captiontag = tag_escape($captiontag);
    $columns = intval($columns);
    $itemwidth = $columns > 0 ? floor(100/$columns) : 100;
    $float = is_rtl() ? 'right' : 'left';

    $selector = "gallery-{$instance}";

    $output = apply_filters('gallery_style', "
        <style type='text/css'>
            #{$selector} {
                margin: auto;
            }
            #{$selector} .gallery-item {
                float: {$float};
                margin-top: 10px;
                text-align: center;
                width: {$itemwidth}%;           }
            #{$selector} img {
                border: 2px solid #cfcfcf;
            }
            #{$selector} .gallery-caption {
                margin-left: 0;
            }
        </style>
        <!-- see gallery_shortcode() in wp-includes/media.php -->
        <div id='$selector' class='gallery galleryid-{$id}'>");

    $i = 0;
    foreach ( $attachments as $id => $attachment ) {
        $link = isset($attr['link']) && 'file' == $attr['link'] ? wp_get_attachment_link($id, $size, false, false) : wp_get_attachment_link($id, $size, true, false);

        $output .= "<{$itemtag} class='gallery-item'>";
        $output .= "
            <{$icontag} class='gallery-icon'>
               $link
            </{$icontag}>";
        if ( $captiontag && trim($attachment->post_excerpt) ) {
            $output .= "
                <{$captiontag} class='gallery-caption'>
                " . wptexturize($attachment->post_excerpt) . "
                </{$captiontag}>";
        }
        $output .= "</{$itemtag}>";
        if ( $columns > 0 && ++$i % $columns == 0 )
            $output .= '<br style="clear: both" />';
    }

    $output .= "
            <br style='clear: both;' />
        </div>\n";

    return $output;
}
add_filter( 'post_gallery', 'my_post_gallery', 10, 2 );


function comicpost_meta_tags(){
	global $post;
	global $wp;
	$options = get_option('comicpost_options');
	if (!empty($options['add_noai_meta'])){
		echo '<!--// NoAI meta inserted by ComicPost //-->
		<meta name="robots" content="noai, noimageai"/>';	
	}
	if (!empty($options['facebook_meta']) || !empty($options['bluesky_meta'])){
		echo '<!--// OpenGraph Data inserted by ComicPost //-->
		<meta property="og:locale" content="'.get_bloginfo('language').'"/>
		<meta property="og:type" content="website"/>
		<meta property="og:title" content="'.get_bloginfo('name').' -" />
		<meta property="og:url" content="'.home_url( add_query_arg( array(), $wp->request ) ).'"/>
		<meta property="og:site_name" content="'.get_bloginfo('name').'"/>
		<meta property="og:description" content="'.get_bloginfo('description').'"/>';
		if (is_singular() && has_post_thumbnail( $post->ID) ){
			$thumbnail_src = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'large' );
			echo '<meta property="og:image" content="'.esc_attr( $thumbnail_src[0] ).'"/>';
			echo '<meta property="og:image:width" content="'.esc_attr( $thumbnail_src[1] ).'"/>';
			echo '<meta property="og:image:height" content="'.esc_attr( $thumbnail_src[2] ).'"/>';
		} else {
			if (!empty($options['fallback_thumbnail']) ){
				$thumbnail_src = $options['fallback_thumbnail'];
			} else {
				$thumbnail_src = get_site_icon_url();
			}
			if (!empty($thumbnail_src)){
				echo '<meta property="og:image" content="'.esc_attr( $thumbnail_src ).'"/>';
			}
		}
	}
	if (!empty($options['mastodon_id'])){
		$url = '';
		// see if it starts with "@" and has another "@" in it
		if ($options['mastodon_id'][0] == '@' && substr_count($options['mastodon_id'],'@') == 2){
			// okay, it looks like a mastodon id, so let's split it
			$parts = explode("@",$options['mastodon_id']);
			// turn it into a url
			$url = 'https://'.$parts[2].'/@'.$parts[1];
		}
		if ( filter_var( $url, FILTER_VALIDATE_URL ) ){
			echo '<!--// Mastodon Verification Code inserted by ComicPost //-->
			<link rel="me" href="'.$options['mastodon_id'].'"/>';
		}
	}
};
add_action('wp_head', 'comicpost_meta_tags', 2, 1);

function comicpost_archive_views(){
	$options = get_option('comicpost_options');
	if ($options['archive_views'] != 'theme'){
//	if (!empty($options['archive_viewbuttons'])){
		if(!is_admin() && is_archive() && (is_post_type_archive( "comic" ))||is_tax("chapters")) {
			if ($options['archive_views'] == 'multiple'){
		?>
	
<script type="text/javascript">

// Vanilla JS version
document.addEventListener('DOMContentLoaded', function() {
    // get articles list
    var articles = document.getElementsByTagName('article');
    // if no articles bail early...
    if (articles.length == 0){
        console.log('No articles so cannot include views buttons, nothing to view.');
        return;
    }
    // see if views_gridbox exists, add it if necessary
    var gridbox = document.getElementById('views_gridbox');
    if (!gridbox){
        var gridbox = document.createElement('div');
        gridbox.id = "views_gridbox";
        articles[0].parentNode.insertBefore(gridbox,articles[0]);
    }
    for(var a=0; a < articles.length; a++){
         gridbox.appendChild(articles[a]);
    }
    // create viewbuttons
    var views = document.createElement('div');
    views.id = "views";
    views.innerHTML = '<p><span>View:</span>'+ 
'	<button class="viewbutton" type="button" value="one-up"><span>Single</span></button>'+
'	<button class="viewbutton" type="button" value="two-up"><span>Double</span></button>'+
'	<button class="viewbutton" type="button" value="three-up"><span>Triple</span></button>'+
'	<button class="viewbutton" type="button" value="four-up"><span>Quad</span></button>'+
'	</p>';

    gridbox.parentNode.insertBefore(views,gridbox);

    var loc = location.href;
    var view = loc.split('?');
    if (view.length > 1){
        document.body.classList.add(view[1]);
    } else {
        document.body.classList.add("one-up");
    }
    var viewbuttons = document.querySelectorAll('.viewbutton');
    for(var i=0;i<viewbuttons.length;i++){
        viewbuttons[i].addEventListener('click', function(){
            var opts = ['one-up','two-up','three-up','four-up'];
            for(i=0;i<opts.length;i++){
                if (this.value != opts[i]){
                    document.body.classList.remove(opts[i]);
                } else {
                    document.body.classList.add(this.value);
                }
            }
            var append = "?"+this.value;
            var links = document.querySelectorAll('.comic-archive-nav > a, .nav-links > a, a.page-numbers');
            for(var j=0;j<links.length;j++){
                var base  = links[j].href.split('?');
                links[j].href = base[0]+append;
            }
        });
    }
});
</script>
<?php } else if ($options['archive_views'] == 'vertical'){
 ?>
 <script type="text/javascript">

// Vanilla JS version
document.addEventListener('DOMContentLoaded', function() {
    // get articles list
    var articles = document.getElementsByTagName('article');
    // if no articles bail early...
    if (articles.length == 0){
        console.log('No articles so cannot include views buttons, nothing to view.');
        return;
    }
    // see if views_gridbox exists, add it if necessary
    var gridbox = document.getElementById('views_gridbox');
    if (!gridbox){
        var gridbox = document.createElement('div');
        gridbox.id = "views_gridbox";
        articles[0].parentNode.insertBefore(gridbox,articles[0]);
    }
    for(var a=0; a < articles.length; a++){
         gridbox.appendChild(articles[a]);
    }
    // add the one-up class to the body
    document.body.classList.add("one-up");
});
</script>
<?php } else {
	// do nothing
} ?>		
<style type="text/css">
body.archive .entry {
	font-size: 18px;
	line-height: 18px;
}
body.archive .entry p {
	display: none;
}
body.archive .entry p.comic-thumbnail-in-archive {
	display: inline;
}
body.archive a.post-edit-link {
	font-size: 16px;
}

body.archive.tax-chapters article,
body.archive.post-type-archive-comic article {
	overflow: hidden;
	border: none;
	margin: 0 !important;
	padding: 0 !important;
	text-align: center;
	box-sizing: border-box;
	width: auto !important;
}
body.archive.tax-chapters a.post-thumbnail,
body.archive.post-type-archive-comic a.post-thumbnail {
	background: none;
}
	body.archive.tax-chapters a.post-thumbnail:hover,
	body.archive.post-type-archive-comic a.post-thumbnail:hover {
		background: none;
	}
body.archive.tax-chapters .entry-header,
body.archive.post-type-archive-comic .entry-header {
	display: none;
}
body.archive.tax-chapters .entry-title,
body.archive.post-type-archive-comic .entry-title {
	display: none;
}
body.archive.tax-chapters .comments-link,
body.archive.post-type-archive-comic .comments-link {
	display: none;
}
body.archive.tax-chapters .entry-content,
body.archive.post-type-archive-comic .entry-content {
	display: none;
}
body.archive.tax-chapters .entry-meta,
body.archive.post-type-archive-comic .entry-meta {
	display: none;
}
body.archive.tax-chapters img[class*="attachment-"],
body.archive.post-type-archive-comic img[class*="attachment-"] {
	max-width: 100%;
	margin: 0 auto;
}
	body.archive.tax-chapters article.not-found .entry-title,
	body.archive.tax-chapters article.no-results .entry-title {
		display: block;
	}
	body.archive.tax-chapters article.not-found .entry-content,
	body.archive.tax-chapters article.no-results .entry-content {
		display: block;
		text-align: left;
	}

body.archive #views {
	text-align: center;
	margin: 0px auto 40px auto;
}
	body.archive #views button.viewbutton {
		width: auto;
	}
	#views_gridbox {
		display: grid;
	}
	body.archive.one-up #views_gridbox {
		grid-template-columns: 100%;
	}
	body.archive.two-up #views_gridbox {
		grid-template-columns: 49% 49%;
	}
	body.archive.three-up #views_gridbox {
		grid-template-columns: 33% 33% 33%;
	}
	body.archive.four-up #views_gridbox {
		grid-template-columns: 24% 24% 24% 24%;
	}
		body.archive.one-up button.viewbutton[value="one-up"],
		body.archive.two-up button.viewbutton[value="two-up"],
		body.archive.three-up button.viewbutton[value="three-up"],
		body.archive.four-up button.viewbutton[value="four-up"] {
			border: 1px solid white;
			color: white;
			background-color: black;
		}
		body.archive #views button::before {
			font-family: sans-serif;
			font-weight: bold;
			font-size: larger;
			display: block;
		}
			button[value="one-up"]::before {
				content: '\2337';
			}
			button[value="two-up"]::before {
				content: '\2337\2337';
			}
			button[value="three-up"]::before {
				content: '\2337\2337\2337';
			}
			button[value="four-up"]::before {
				content: '\2337\2337\2337\2337';
			}
body.search.search-results article img.wp-post-image,
body.search.search-results article .comic img,
body.archive.category article img.wp-post-image,
body.archive.category article .comic img {
	display: block;
	margin: 24px auto;
}

</style>
<?php
		}
	}
};
// delay until archives have fully loaded...
add_action( 'wp_footer', 'comicpost_archive_views', 99, 1);

/* RATINGS SYSTEM
	 mostly from: https://www.wppagebuilders.com/add-star-rating-in-wordpress-comment/
*/



// Create Ratings Interface
add_action( 'comment_form_logged_in_after', 'comicpost_comment_rating_rating_field');
add_action( 'comment_form_after_fields', 'comicpost_comment_rating_rating_field');

function comicpost_comment_rating_rating_field() {
   global $post;
   $options = get_option('comicpost_options');
   // don't do this at all if star ratings are not enabled
   if ( $options['rating_system'] != 'stars' ){
   		return;
   }
	// only do this on comic posts
   if (get_post_type($post->ID) == 'comic' && !comicpost_user_already_rated_comic($post->ID) ){
   
		if ( isset( $_POST['comicpost_commentsrating_noncename']) ){
			if ( !wp_verify_nonce( $_POST['comicpost_commentsrating_noncename'], 'comicpost-comments-rating') ){
				echo "<div class='error'><p>Authentication failed</p></div>";
				return;
			}
		}
   
	?>
	<label for="rating">Rate This Comic<?php 
	if ( !empty($options['star_rating_required']) ){ 
		echo '<span class="required">*</span>'; 
	}; 
	?></label>
	<fieldset class="comments-rating">
		<span class="rating-container">
			<?php for ( $i=5; $i >= 1; $i-- ){ ?>
				<input type="radio" id="rating-<?php echo esc_attr( $i ); ?>" name="rating" value="<?php echo esc_attr( $i ); ?>" />
				<label for="rating-<?php echo esc_attr( $i ); ?>"><?php echo esc_html( $i ); ?></label>
			<?php } ?>
			<input type="radio" id="rating-0" class="star-cb-clear" name="rating" value="0"/>
			<label for="rating-0">0</label>
			<?php wp_nonce_field( 'comicpost-comments-rating', 'comicpost_commentsrating_noncename'); ?>
		</span>
	</fieldset>
	<?php
   } else {
   	return;
   }
};

function comicpost_user_already_rated_comic($postid){
//	global $post;
	$already_rated = false;
	if ( is_user_logged_in() && get_post_type($postid) == 'comic'){
		// what to get
		$args = array(
			'post_id' => $postid,
			'status'  => 'approve'
		);
		// get the current user id
		$user = get_current_user_id();
		// get the comments on this post id
		$comments = get_comments( $args ); 
		// search through all the comments for user id
		foreach( $comments as $comment ){
			// does the comment have a rating? Did this user leave it?
			if (get_comment_meta( $comment->comment_ID, 'rating', true ) && $comment->user_id == $user){
				$already_rated = true;
			}
		}
	}
	return $already_rated;
}

// Save the rating submitted by user
add_action( 'comment_post', 'comicpost_comment_rating_save_comment_rating', 10, 3);
function comicpost_comment_rating_save_comment_rating( $comment_id, $comment_approved, $commentdata ){
	$postid  = $commentdata['comment_post_ID']; /* no access to global $post in here */
	$options = get_option('comicpost_options');
  	if ( get_post_type($postid) != 'comic' || $options['rating_system'] != 'stars' ){
  		return;
  	}
	if ( ( isset( $_POST['rating'] )) && ( '' !== $_POST['rating'] ) ){
		$rating = intval( $_POST['rating'] );
		add_comment_meta( $comment_id, 'rating', $rating );
	}
};

// Make the rating required?
add_filter( 'preprocess_comment', 'comicpost_comment_rating_require_rating' );
function comicpost_comment_rating_require_rating( $commentdata ){
  $postid = $commentdata['comment_post_ID']; /* no access to global $post in here */
  $options = get_option('comicpost_options');
  if (get_post_type($postid) != 'comic' || $options['rating_system'] != 'stars'){
  	return $commentdata;
  }
  if ( comicpost_user_already_rated_comic($postid) == false  && $options['star_rating_required'] ){
	if ( !is_admin() && ( !isset( $_POST['rating'] ) || 0 === intval( $_POST['rating'] ) ) ){
			wp_die( __( 'Error: You did not add a rating. Hit the Back button on your browser and re-submit your comment with a rating.') );
	}
  }
  return $commentdata;
};

// Display the rating on a submitted comment
add_filter( 'comment_text', 'comicpost_comment_rating_display_rating');
function comicpost_comment_rating_display_rating( $comment_text ){
   global $post;
   $options = get_option('comicpost_options');
  // only do this for comic posts
  if (get_post_type($post->ID) == 'comic' && $options['rating_system'] == 'stars'){
	if ( $rating = get_comment_meta( get_comment_ID(), 'rating', true ) ){
		$stars = '<p class="stars">';
		for ( $i=1; $i <= $rating; $i++ ){
			$stars .= '<span class="dashicons dashicons-star-filled"></span>';
		}
		$stars .= '</p>';
		$comment_text = $comment_text . $stars;
		return $comment_text;
	} else {
		return $comment_text;
	}
  } else {
  	return $comment_text;
  }
}

// Get the average rating for a post
function comicpost_comment_rating_get_average_ratings( $id ){
	$comments = get_approved_comments( $id );
	if ( $comments ){
		$i = 0;
		$total = 0;
		foreach( $comments as $comment ){
			$rate = get_comment_meta( $comment->comment_ID, 'rating', true );
			if ( isset( $rate ) && '' !== $rate ){
				$i++;
				$total += $rate;
			}
		}
		if ( 0 === $i ) {
			$average = false;
		} else {
			$average = round( $total / $i, 1 );
			// save it in post meta so we can sort by it
			update_post_meta( $id, 'comicpost_stars', $average);
		}
		return $average;
	} else {
		return false;
	}
};

// Display the average rating above the_content
add_filter( 'the_content', 'comicpost_comment_rating_display_average_rating' );
function comicpost_comment_rating_display_average_rating( $content ){
  global $post;
  $options = get_option('comicpost_options');
  if (get_post_type($post->ID) == 'comic' && $options['rating_system'] == 'stars' && !is_archive() && !is_home() ){
	if ( false === comicpost_comment_rating_get_average_ratings( $post->ID ) ){
		return $content;
	}
	$stars = '';
	$average = comicpost_comment_rating_get_average_ratings( $post->ID );
	$width = round( $average * 20, 1);
	$custom_content = '<p class="average-rating">Average rating is: '.$average.' <span class="rating-stars"><span class="rating-empty"></span><span class="rating-full" style="width: '.$width.'%;"></span></span></p>';
	$custom_content .= $content;
	return $custom_content;
  } else {
  	return $content;
  }
};

/* POST LIKE RATINGS SYSTEM
	=========================
*/

/*  Returns array of posts user has liked */
function comicpost_get_user_post_likes(){
	// if user is not logged in never mind
	if ( !is_user_logged_in() ){
		return;
	}
	$likes = get_user_meta(
		get_current_user_id(),
		'comicpost_likes',
		true
	);
	if (empty($likes)){
		$likes = array(); // make sure it is an array
	}
	return $likes;
}

/* Check if this user liked this post before */
function comicpost_user_already_liked_comic($post_id){
	$likes = comicpost_get_user_post_likes();
	$already_liked = array_search( $post_id, $likes);
	// it is either a key index or false, remember index 0 is falsy so check specifically for === or !== false
	return $already_liked; 
}

/* Set the user post likes both for user_meta and post_meta */
function comicpost_set_user_post_like(){
	// if user is not logged in never mind
	if ( !is_user_logged_in() ){
		return;
	}
	global $post;
	// get the users current likes
	$likes = comicpost_get_user_post_likes();

	// search array for current post-id
	$already_liked = comicpost_user_already_liked_comic($post->ID);
	if ( $already_liked !== false ){
		$plusminus = 'remove';
		array_splice( $likes, $already_liked, 1); // remove it from the array
	} else {
		$plusminus = 'add';
		$likes[] = $post->ID;
	}
	// update the saved user post likes
	update_user_meta(
		get_current_user_id(),
		'comicpost_likes',
		$likes
	);
	// increment post likes
	comicpost_set_post_likes($post->ID,$plusminus);	
}

/* Get current Post Like Count 
	Makes sure it resturns zero instead of boolean false
	(cp uses ternery operator on post_meta but I put this
	in because there is a comicpost_set_post_likes function)
*/
function comicpost_get_post_likes($post_id){
	$like_count = get_post_meta(
		$post_id,
		'comicpost_likes',
		true
	);
	if (empty($like_count)){
		$like_count = 0;	// we don't want false we want an integer
	}
	return $like_count;
}

/* Add or Remove Post Like */
function comicpost_set_post_likes($post_id, $plusminus){
//	$like_count = comicpost_get_post_likes($post_id);
	$like_count = get_post_meta($post_id,'comicpost_likes',true) ?: 0;
	if ($plusminus == "remove"){
		$like_count--;
	} else {
		$like_count++;
	}
	update_post_meta(
		$post_id,
		'comicpost_likes',
		$like_count
	);
}
add_filter( 'the_content', 'comicpost_display_post_likes' );
function comicpost_display_post_likes( $content ){
	global $post;
	$options = get_option('comicpost_options');
	if (get_post_type($post->ID) == 'comic' && $options['rating_system'] == 'likes' && !is_archive() &&!is_home() ){
		$like_style = $options['post_like_style'];
		$like_button_text   = $options['post_like_button_text'];
		$unlike_button_text = $options['post_unlike_button_text'];
		$like_love_star_text= $options['post_liking_action'];
		if ( is_user_logged_in() ){
			if ( isset( $_POST['comicpost_submitlike_noncename']) ){
					if ( !wp_verify_nonce( $_POST['comicpost_submitlike_noncename'], 'comicpost-submit-like') ){
						echo "<div class='error'><p>Authentication failed</p></div>";
						return;
					}
					comicpost_set_user_post_like();	
					$like_count = get_post_meta($post->ID,'comicpost_likes',true) ?: 0;
					$already_liked = comicpost_user_already_liked_comic($post->ID);	
			} else {
				$like_count = get_post_meta($post->ID,'comicpost_likes',true) ?: 0;
				// check if user liked this post before
				$already_liked = comicpost_user_already_liked_comic($post->ID);
			}
	
			
			if ( $already_liked !== false ){
				$button = '<button type="submit" title="Unlike" class="button-unlike"><span class="like-value">'.esc_attr($unlike_button_text).'</span></button>';
				if ( $like_count == 1 ){
					$like_text = 'You '.esc_attr($like_love_star_text).' this.';
				} else if ( $like_count == 2 ){
					$like_text = 'You and 1 other person '.esc_attr($like_love_star_text).' this';
				} else {
					$like_text = 'You and '.($like_count-1).' other people '.esc_attr($like_love_star_text).' this.';
				}
			} else {
				if ( $like_count == 1 ){ $who = 'person'; } else { $who = 'people'; }
				$button = '<button type="submit" title="Like" class="button-like"><span class="like-value">'.esc_attr($like_button_text).'</span></button>';
				$like_text = $like_count.' '.$who.' '.esc_attr($like_love_star_text).' this.';
			}
			if ($like_count > 0 ){
				$class = "has-likes";
			} else {
				$class = "no-likes";
				$like_text = 'Be the first to '.esc_attr($like_button_text).' this!';
			}
			if ($like_style == "custom"){
				$like_style = '';
			} else {
				$like_style = ' '.$like_style;
			}
	
			ob_start();
				wp_nonce_field( 'comicpost-submit-like', 'comicpost_submitlike_noncename');
			$nonce = ob_get_clean();
			
			$custom_content = '	<form method="post" action="" id="comicpost_submit_likes_form" class="comicpost-likes-block '.$class.$like_style.'">'.
			$nonce.$button.' <span class="like-description">'.$like_text.'</span>'.
			'</form>';
			$custom_content .= $content;
		} else { 
			$like_count = get_post_meta($post->ID,'comicpost_likes',true) ?: 0;
			if ($like_count > 0){
				if ($like_count == 1){
					$personpeople = 'person';
				} else {
					$personpeople = 'people';
				}
				$class = "has-likes";
				$like_text = $like_count.' '.$personpeople.' '.esc_attr($like_love_star_text).' this.';
			} else {
				$class = "no-likes";
				$like_text = 'Be the first to '.$like_button_text.' this!';
			}
			$custom_content = '<p class="comicpost-likes-block '.$like_style.'">'.
			'<span class="like-description">'.$like_text.' (You must <a href="'.esc_url( wp_login_url( get_permalink() ) ).'">log in</a> to '.esc_attr(strtolower($like_button_text)).' posts)</span>';
			$custom_content .= $content;
		}
			return $custom_content;
  } else {
  	return $content;
  }

}