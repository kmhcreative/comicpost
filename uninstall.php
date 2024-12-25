<?
// Uninstall Script for ComicPost //

if( !defined( 'ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') )
    exit();
 
$option_name = 'comicpost_options';
if (is_multisite()){
	delete_site_option($option_name);
	delete_option($option_name);
} else {
	delete_option($option_name);
}
$upload_dir = wp_get_upload_dir();
if (file_exists( $upload_dir['basedir'].'/comic_watermark' ) && is_dir( $upload['basedir'].'/comic_watermark') ){
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
	$wp_filesystem->delete( $upload_dir['basedir'].'/comic_watermark', true);
};
// check if it is still there or not
if (file_exists( $upload_dir['basedir'].'/comic_watermark')){
	add_action('admin_notices', 'uninstallMsg');
}

function uninstallMsg(){
echo '<div class="error">
       <p>ComicPost was uninstalled, but was unable to remove the "comic_watermark" directory and contents from your upload directory. You will have to manually remove it.</p>
    </div>';
} 
?>
