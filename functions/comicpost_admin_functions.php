<?php
/*
	ComicPost Plugin
	This file has all the functions that only work
	on the Admin back-end
*/

function comicpost_set_custom_comic_columns( $columns ){
	$columns['comic_image'] = __( 'Comic Image', 'comicpost' );
	return $columns;
}

function comicpost_custom_comic_column( $column, $post_id ){
	if ($column == 'comic_image' ){
		$options = get_option('comicpost_options');
		if ( empty($options['manageposts_thumbnail_size']) ){
			$size = 120;
			$thumbnail = 'thumbnail';
		} else {
			$size = $options['manageposts_thumbnail_size'];
			if ( $size < 200 ){
				$thumbnail = 'thumbnail';
			} else {
				$thumbnail = 'medium';
			}
		}
			echo get_the_post_thumbnail( $post_id, $thumbnail );	
	// make sure image will fit:
	?><style type="text/css">
		.column-comic_image{
			width:<?php echo $size; ?>px;
		}
			.column-comic_image img {
				width: 100%;
				height: auto;
			}
	  </style>
	<?php
	}
}


add_filter( 'manage_comic_posts_columns', 'comicpost_set_custom_comic_columns' );
add_filter( 'manage_comic_posts_custom_column', 'comicpost_custom_comic_column', 10, 2);

/* Adds Drop-down list of Chapters to Filter Comic Post Management List */
function add_comicpost_taxonomy_filters() {
	global $typenow;
 
	// an array of all the taxonomyies you want to display. Use the taxonomy name or slug
	$taxonomies = array('chapters');
 
	// must set this to the post type you want the filter(s) displayed on
	if( $typenow == 'comic'){

		foreach ($taxonomies as $tax_slug) {
			$tax_obj = get_taxonomy($tax_slug);
			$tax_name = $tax_obj->labels->name;
			$terms = get_terms($tax_slug);
			if(count($terms) > 0) {
				echo "<select name='$tax_slug' id='$tax_slug' class='postform'>";
				echo "<option value=''>Show All $tax_name</option>";
				foreach ($terms as $term) {
					echo '<option value='. $term->slug, isset($_GET[$tax_slug]) && $_GET[$tax_slug] == $term->slug ? ' selected="selected"' : '','>' . $term->name .' (' . $term->count .')</option>'; 
				}
				echo "</select>";
			}
		}
	}
}
add_action( 'restrict_manage_posts', 'add_comicpost_taxonomy_filters' );

add_filter( 'wp_handle_upload', 'comicpost_auto_watermark_comic' );
function comicpost_auto_watermark_comic($file) {
	/*	This is Hook not function so 
		$file = array(
			['file'] => $new_file path on server
			['url']  => file URL on server
			['type'] => file mime type (format: image/png )
		);
	*/
	$options = get_option('comicpost_options');
//	Watermarking only works if the PHP extension is loaded	
	if (!extension_loaded('gd') || $options['watermark_method'] == 'none' ){
		// bail
	} else {
		// We only want to do this on Comic Posts
		$the_post_type = get_post_type( $_REQUEST['post_id'] );
		if ($the_post_type == 'comic'){
			$mimetypes = ['image/jpeg','image/png','image/gif'];
			// get the uploaded image type
			if (!in_array($file['type'],$mimetypes)){
				// cannot watermark whatever this is, bail!
				return $file;
			}
			
			$type = explode('/',$file['type']);
			if ($type[1] == 'jpeg'){ // determine if file extension is jpeg or jpg
				if (!preg_match('/\.jpeg/i', $file['file'])){
					$type[1] = 'jpg';
				}
			}
			
			/*	At this point the file has already been uploaded to the server
				all of the image manipulation below happens afterwards and then
				overwrites the existing image on the server before all the thumbnail
				sizes are created.  So we have an opportunity to create a "clean"
				copy of the image before the uploaded one gets overwritten with
				the watermarked version or the watermarked thumbnails are created.
			*/
			
		if (!empty($options['watermark_clean_copy'])){
			// get the image upload location on the server
			$upload_path  = $file['file'];
			$clean_suffix = $options['watermark_clean_suffix'];
			if (empty($clean_suffix)){ $clean_suffix = 'clean'; }; 
			$clean_path  = str_replace('.'.$type[1],'_'.$clean_suffix.'.'.$type[1],$upload_path);
			// create a CLEAN version of the file on the server
				global $wp_filesystem;
				require_once(ABSPATH . 'wp-admin/includes/file.php');
				// check that $wp_filesystem is of a WP_Filesystem type
				if ( !is_a( $wp_filesystem, 'WP_Filesystem_Base') ){
					// it is not of the right type so get site credentials
					$creds = request_filesystem_credentials( site_url() );
					wp_filesystem($creds);
				}
				// create a new instance
				WP_Filesystem();
			$wp_filesystem->copy( $upload_path, $clean_path );
		}				
			// get WP Upload Directory
			$upload_dir = wp_get_upload_dir();
			// first gt the width and height of uploaded image 
			list($width,$height) = getimagesize($file['file']);
			
			// now we need to create a transparent image the same size
			$watermark = imageCreateTrueColor ($width, $height);
			imagealphablending($watermark,false); // disable blending mode
			imagesavealpha($watermark,true); // save with alpha channel
			$background = imagecolorallocate($watermark, 0,0,0);
			imagecolortransparent($watermark, $background);


			// check if tile image does not exist
			if (!file_exists($upload_dir['basedir'].'/comic_watermark/watermark.png')){
				// oops! It doesn't exist, so let's go make one...
				comicpost_generate_watermark_tile();
			}
			// check again if it exists...
			if (!file_exists($upload_dir['basedir'].'/comic_watermark/watermark.png')){
				// still does not, something went wrong, bail...
				return $file;
			}
			// if we got this far the tile image exiss and we can proceeed...
					
			// load the watermark tile image
			$tile = imageCreateFromPNG ($upload_dir['basedir'].'/comic_watermark/watermark.png'); // transparent PNG
			// get width and height of tile image
			list($tile_width,$tile_height) = getimagesize($upload_dir['basedir'].'/comic_watermark/watermark.png');
			// Get/Set Tile Opacity
			$opacity = $options['watermark_opacity'];
			$transparency = 1-$opacity;
			// set alpha blending to false	
			imagealphablending($tile,false);
			// set save alpha to true
			imagesavealpha($tile,true);
			// imagefilter( image, filter, r,g,b,a) - using "invisible gray" because it works better
			imagefilter($tile, IMG_FILTER_COLORIZE, 128,128,128,intval(127*$transparency));
			// set tile height and width fallback to its actual size
			$new_width = $tile_width;
			$new_height = $tile_height;
			
			// Check whether this should be scaled and centered or tiled
			if ($options['watermark_method'] == 'center'){
				// get image orientation			
				if ($height > $width){ // portrait image
					$new_width = $width;
					$new_height = $width;
				} else { // it's landscape or square
					$new_width = $height;
					$new_height = $height;
				}
				// resize the tile
				$tile = imagescale($tile, $new_width, $new_height, IMG_BILINEAR_FIXED);
				// overlay tile on watermark layer
				imagecopy($watermark, $tile, 0,0,-intval(($width-$new_width)/2),-intval(($height-$new_height)/2), $width, $height);
			} else { // tile this image over the whole comic
				if (!empty($options['watermark_tile_size'])){
					$new_width  = $options['watermark_tile_size'];
					$new_height = $new_width;
				} else {
					$new_width  = 300;
					$new_height = 300;
				}
				// resize tile
				$tile = imagescale($tile, $new_width, $new_height, IMG_BILINEAR_FIXED);
				imageSetTile($watermark,$tile);
				// fill watermark height and width with tile...
				imageFilledRectangle($watermark, 0, 0, $width, $height, IMG_COLOR_TILED);
			}

			// get the image content but let PHP figure out what format it is...
			$comic = imagecreatefromstring(file_get_contents($file['file']));
			// if that failed for any reason bail...
			if (empty($comic)){
				return $file;
			}

			// overlay watermark on comic (note that imagecopymerge does NOT work for this!)
			imagecopy($comic, $watermark, 0, 0, 0, 0, $width, $height);
			
			imagesavealpha($comic,true);	// if PNG had transparency try to preserve it
			// output the file in the same format it was input
			if ($file['type'] == 'image/jpeg'){
				imagejpeg( $comic, $file['file'], 100);
			} else if ($file['type'] == 'image/png'){
				 imagepng( $comic, $file['file'], 0);
			} else if ($file['type'] == 'image/gif'){
				 imagegif( $comic, $file['file']);
			} else {
				// do nothing
			}

			// clean-up
			imagedestroy ($comic);
			imagedestroy ($watermark);
			imagedestroy ($tile);
		} // end of comic post type check
	}// end GD check    
    // return modified file to uploader
    return $file;
}

function comicpost_watermark_folder(){
	// get upload directory
	$upload_dir = wp_get_upload_dir();
	// check that the upload directory is writable
	if (is_writable($upload_dir['basedir'])){
		// lets use wp's function for checking/creating directory
		if ( wp_mkdir_p( $upload_dir['basedir'].'/comic_watermark' )){
			// check if blank index.php is in it
			if (!file_exists( $upload_dir['basedir'].'/comic_watermark/index.php' )){
				// not there, so lets copy the one from the plugin...
				global $wp_filesystem;
				require_once(ABSPATH . 'wp-admin/includes/file.php');
				// check that $wp_filesystem is of a WP_Filesystem type
				if ( !is_a( $wp_filesystem, 'WP_Filesystem_Base') ){
					// it is not of the right type so get site credentials
					$creds = request_filesystem_credentials( site_url() );
					wp_filesystem($creds);
				}
				// create a new instance
				WP_Filesystem();
				$wp_filesystem->copy( comicpost_pluginfo('plugin_path').'index.php', $upload_dir['basedir'].'/comic_watermark/index.php',false,false );
			}
		}
	} else {
		// upload directoyr is NOT writable so bail...
		echo "<div class='error'><p>Sorry, the Upload Directory is not writable.</p></div>";
	}
	// either WP created the directory or it failed for some reason, so check again and let caller know...
	if (is_dir( $upload_dir['basedir'].'/comic_watermark' )){
		return true;
	} else {
		return false;
	}
}

function comicpost_generate_custom_watermark(){
	// check user permissions
	if ( !current_user_can( 'upload_files' ) ){
		echo "<div class='error'><p>Sorry, you do not have the correct priveleges to upload or create files.</p></div>";
		return;
	}
	// get upload directory
	$upload_dir = wp_get_upload_dir();
	// make sure comic_watermark folder exists
	if ( comicpost_watermark_folder() ){
		// folder was created now or already existed
	} else {
		// folder does not exist and could not be created so bail...
		echo "<div class='error'><p>Watermark folder does not exist and apparently could not be created, so no file can be generated. Try creating the folder manually and uploading your file via FTP.</p></div>";
		return;
	}
	// get plugin options
	$options = get_option('comicpost_options');
	// retrieve the path of the Custom Image from the Media Library
	$custom_image = $options['custom_watermark_file'];

	if (empty($custom_image)){
		// no custom image is set so bail!
		echo "<div class='error'><p>No Custom Watermark Image is set!  Go to <em>ComicPost -&gt; Options -&gt; Watermarking</em> and upload/set a file under <b>Custom Watermark File</b></p></div>";
		return;
	} else {
		// check that image claims to be a PNG
		$file_type = pathinfo($custom_image, PATHINFO_EXTENSION);
		if ($file_type != "png"){
			echo "<div class='error'><p>File is not a PNG.</p></div>";
			return;
		} 
		// we can only do image manipulation if GD is loaded
		if (extension_loaded('gd')){
			$input_img = imageCreateFromPNG($custom_image);
		 	$img_size = $options['watermark_image_size'];
			$watermark = imagecreatetruecolor($img_size,$img_size);
						   imagealphablending($watermark, false);
						   imagesavealpha($watermark, true);
						   $alpha = imagecolorallocatealpha($watermark, 128,128,128, 127);
			// scale input image to desired watermark iamge size
			imagecopyresampled($watermark, $input_img, 0, 0, 0, 0, $img_size, $img_size, imagesx($input_img), imagesy($input_img));
			// write it to the watermark folder...
			imagepng($watermark, $upload_dir['basedir'].'/comic_watermark/watermark.png', 0);
			// clean-up
			imagedestroy($input_img);
			imagedestroy($watermark);
		} else {
			// no image manipulators so just try to copy the image
				global $wp_filesystem;
				require_once(ABSPATH . 'wp-admin/includes/file.php');
				// check that $wp_filesystem is of a WP_Filesystem type
				if ( !is_a( $wp_filesystem, 'WP_Filesystem_Base') ){
					// it is not of the right type so get site credentials
					$creds = request_filesystem_credentials( site_url() );
					wp_filesystem($creds);
				}
				// create a new instance
				WP_Filesystem();
			$wp_filesystem->copy( $custom_image, $upload_dir['basedir'].'/comic_watermark/watermark.png' );			
		}
	} 
}


function comicpost_generate_watermark_tile(){
	// Check permissions
	if ( !current_user_can( 'upload_files' ) ) {
		echo "<div class='error'><p>Sorry, you do not have the correct priveledges to upload or create files.</p></div>";
		return ; 
	}
	// get upload directory
	$upload_dir = wp_get_upload_dir();

	if ( comicpost_watermark_folder() ){
		// folder was created or exists
	} else {
		// folder does not exist and could not be created, so bail...
		echo "<div class='error'><p>Watermark folder does not exist and apparently could not be created, so no file can be generated. Try creating the folder manually and uploading your file via FTP.</p></div>";
		return;
	}

	$options  = get_option('comicpost_options');
	$img_size = $options['watermark_image_size'];
	if (empty($tile_size)){ $tile_size = 300; };
	
//	$refactor = $tile_size/hypot($tile_size,$tile_size);
	
//	$tile_size = $tile_size*$refactor;
	
	$wm_text = $options['watermark_text'];
	if (empty($wm_text)){ $wm_text = get_bloginfo(); }
	// fonts are TTF array is sub-folder => fontname
	$fonts = array(
		'black-ops-one'		=> 'BlackOpsOne-Regular.ttf',
		'cascadia-code' 	=> 'Cascadia.ttf',
		'liberation-sans'	=> 'LiberationSans-Bold.ttf',
		'sarina'			=> 'Sarina-Regular.ttf',
		'tiza'			 	=> 'tiza.ttf',
		'unitblock-font'	=> 'Unitblock.ttf'
	);
	$font_file = $fonts[$options['watermark_font']];

	$img = imagecreatetruecolor($img_size,$img_size);
			imagealphablending($img, false);
			imagesavealpha($img, true);
	// image, red, green, blue, alpha : 0 = opaque to 127 = transparent
	// using 'transparent gray' because image is black and white
	$transparent = imagecolorallocatealpha($img, 128,128,128, 127);
			imagefill($img, 0, 0, $transparent);

	$black = imagecolorallocate($img, 0,0,0);
	$white = imagecolorallocate($img, 255,255,255);
	
	$text = $wm_text;
	$font =  comicpost_pluginfo('plugin_path').'fonts/'.$options['watermark_font'].'/'.$font_file;
	
	// center text vertically and horizontally?
	// imageftbbox( font_size, angle, font_name, text_string )
	$box = imageftbbox(60, 0, $font, $text);
	$box_width = $box[2];
	$box_height= $box[5];
	// calculate font size from that
	$scale = ($img_size*.90 / $box_width);	
	// font size scaled will be
	$font_size = (60 * $scale);
	// write text into image...			
	imagettftext($img, $font_size, 0, intval(3+($img_size-($img_size*.90))/2), intval(3+(($img_size/2)+($font_size/2))), $white, $font, $text);
	imagettftext($img, $font_size, 0, ($img_size-($img_size*.90))/2, (($img_size/2)+($font_size/2)), $black, $font, $text);
	// Now rotate it...
	$rotated = imagerotate($img, 45, $transparent, 0);	
	// after rotation image is diagonal of original size
	$watermark = imagecreatetruecolor($img_size,$img_size);
			   imagealphablending($watermark, false);
			   imagesavealpha($watermark, true);
			   $alpha = imagecolorallocatealpha($watermark, 128,128,128, 127);
	// scale rotated image back to desired img size (note: imagescale always lost the transparent background)
	imagecopyresampled($watermark, $rotated, 0, 0, 0, 0, $img_size, $img_size, imagesx($rotated), imagesy($rotated));
	// write image to watermark folder...
	if ($options['watermark_orientation'] == 'horizontal'){	
		imagepng($img, $upload_dir['basedir'].'/comic_watermark/watermark.png', 0);
	} else {
		imagepng($watermark, $upload_dir['basedir'].'/comic_watermark/watermark.png', 0);
	}
	// clean up on aisle 3!
	imagedestroy($img);
	imagedestroy($rotated);
	imagedestroy($watermark);
}
