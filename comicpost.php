<?php
/*
Plugin Name: ComicPost
Plugin URI: http://www.github.com/kmhcreative/comicpost
Description: The anti-AI webcomic plugin
Version: 0.1
Author: K.M. Hansen (kmhcreative)
Author URI: http://www.kmhcreative.com
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

/*  Copyright 2024  K.M. Hansen  (email : software@kmhcreative.com)

    ComicPost is for posting comics. It creates a "comic" post-type
    and a "Chapters" taxonomy, or uses them if they're already present
    because you have ComicPress or ComicEasel installed previously. It
    includes various options that *try* to mitigate the problem of your
    content being scraped for AI training.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

/* Minimum Version Checks */
	function comicpost_wp_version_check(){
		// if not using minimum WP and PHP versions, bail!
		$wp_version = get_bloginfo('version');
		global $pagenow;
		if ( is_admin() && $pagenow=="plugins.php" && ($wp_version < 3.5 || PHP_VERSION < 5.6 ) ) {
		echo "<div class='notice notice-error is-dismissible'><p><b>ERROR:</b> ComicPost is <em>activated</em> but requires <b>WordPress 3.5</b> and <b>PHP 5.6</b> or greater to work.  You are currently running <b>Wordpress <span style='color:red;'>".$wp_version."</span></b> and <b>PHP <span style='color:red;'>".PHP_VERSION."</span></b>. Please upgrade.</p></div>";
			return;
		}
	};
	add_action('admin_notices', 'comicpost_wp_version_check');




// ComicEaselLite Plugin Info Function
function comicpost_pluginfo($whichinfo = null) {
	global $comicpost_pluginfo;
	if (empty($comicpost_pluginfo) || $whichinfo == 'reset') {
		// Important to assign pluginfo as an array to begin with.
		$comicpost_pluginfo = array();
		$comicpost_coreinfo = wp_upload_dir();
		$comicpost_addinfo = array(
				// if wp_upload_dir reports an error, capture it
				'error' => $comicpost_coreinfo['error'],
				// upload_path-url
				'base_url' => trailingslashit($comicpost_coreinfo['baseurl']),
				'base_path' => trailingslashit($comicpost_coreinfo['basedir']),
				// plugin directory/url
				'plugin_file' => __FILE__,
				'plugin_url' => plugin_dir_url(__FILE__),
				'plugin_path' => plugin_dir_path(__FILE__),
				'plugin_basename' => plugin_basename(__FILE__),
				'version' => '0.1'
		);
		// Combine em.
		$comicpost_pluginfo = array_merge($comicpost_pluginfo, $comicpost_addinfo);
	}
	if ($whichinfo) {
		if (isset($comicpost_pluginfo[$whichinfo])) {
			return $comicpost_pluginfo[$whichinfo];
		} else return false;
	}
	return $comicpost_pluginfo;
}

add_action( 'init', 'create_comicpost_type' );

function create_comicpost_type() {
$icon = "PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+CjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+Cjxzdmcgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgdmlld0JveD0iMCAwIDE2IDE2IiB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHhtbG5zOnNlcmlmPSJodHRwOi8vd3d3LnNlcmlmLmNvbS8iIHN0eWxlPSJmaWxsOmN1cnJlbnRDb2xvcjtmaWxsLXJ1bGU6ZXZlbm9kZDtjbGlwLXJ1bGU6ZXZlbm9kZDtzdHJva2UtbGluZWpvaW46cm91bmQ7c3Ryb2tlLW1pdGVybGltaXQ6MjsiPgogICAgPGcgdHJhbnNmb3JtPSJtYXRyaXgoLTAuMDA4MTcwNDksMCwwLDAuMDA4MTcwNDksMTYuMzY3NiwtMC41MDQzMDcpIj4KICAgICAgICA8cGF0aCBmaWxsPSJjdXJyZW50Q29sb3IiIGQ9Ik02My4zMzksNjEuNzIzTDE5MS4xLDE0MzEuMTFMMTA2My4zOCwxNTQ1LjFMMTk4MC4zOCwyMDE5Ljk5TDE0NTcuODQsMTUwNi4wM0wxNzU4LjY1LDE1MzguODdMMTk4NC45MiwyMzkuNTczTDYzLjMzOSw2MS43MjNaTTEwNTguNDYsNzQwLjEyOUMxMTM3LjIsNzY2LjczMyAxMTkwLjI3LDgwNi43NjMgMTIxNy42Niw4NjAuMjIxQzEyNTQuMTksOTM1LjU0NiAxMjkwLjEyLDk3OC4yMzUgMTMyNS40Myw5ODguMjg2QzEzNDMuMDUsOTkzLjk3IDEzNTkuNjIsOTkyLjMzOCAxMzc1LjExLDk4My4zODlDMTM5MC42MSw5NzQuNDQgMTQwNC40OCw5NjAuMzg5IDE0MTYuNzMsOTQxLjIzNUMxNDI4Ljk4LDkyMi4wODIgMTQzOS4yNyw4OTkuMzI4IDE0NDcuNiw4NzIuOTcyQzE0NTUuOTQsODQ2LjYxNiAxNDYxLjM1LDgxOC45ODMgMTQ2My44Myw3OTAuMDc0QzE0NjUuNDUsNzYyLjcwMiAxNDY0LjM1LDczMi4xNTEgMTQ2MC41Myw2OTguNDJDMTQ1Ni43MSw2NjQuNjg5IDE0NTAuNjcsNjMyLjQyNSAxNDQyLjQxLDYwMS42MjhDMTQzNC4xNSw1NzAuODMgMTQyMy45OCw1NDQuMDI0IDE0MTEuODksNTIxLjIwOEMxMzk5LjgsNDk4LjM5MiAxMzg2LjE1LDQ4NC40NyAxMzcwLjkzLDQ3OS40NDFDMTM1NS40Miw0NzQuOTI1IDEzNDMuMDgsNDc3LjM3NSAxMzMzLjkyLDQ4Ni43OTFDMTMyNC43Niw0OTYuMjA3IDEzMTYuNDEsNTA3LjU3OSAxMzA4Ljg1LDUyMC45MDhDMTMwNC43OSw1MjguMDg1IDEyOTguODEsNTM1LjU1OCAxMjkwLjkzLDU0My4zMjZDMTI4MS42NSw1NTIuMzM5IDEyNzEuMzEsNTU5LjI1MSAxMjU5LjksNTY0LjA1OUMxMjQ4LjQ4LDU2OC44NjggMTIzNS4yNyw1NzEuMzM4IDEyMjAuMjUsNTcxLjQ2OEMxMjA1LjI0LDU3MS41OTggMTE4OC4yOCw1NjkuMzE2IDExNjkuNCw1NjQuNjIxQzExNTAuNTIsNTU5LjkyNyAxMTI4LjU1LDU1Mi42OTMgMTEwMy41MSw1NDIuOTIxQzEwOTAuNDYsNTM3Ljc0MyAxMDgwLjUxLDUzMi45MjcgMTA3My42NSw1MjguNDcyQzEwNjYuNzksNTI0LjAxNyAxMDYxLjg4LDUxOS43OTggMTA1OC45Myw1MTUuODEzQzEwNTUuOTgsNTExLjgyOSAxMDU0LjMxLDUwNy43MTUgMTA1My45NCw1MDMuNDcxQzEwNTMuNTYsNDk5LjIyNyAxMDUzLjMzLDQ5NC43MjcgMTA1My4yNSw0ODkuOTdDMTA1My45LDQ4MC4yMzYgMTA1NS44LDQ2OS44NDIgMTA1OC45Myw0NTguNzg4QzEwNjIuMDcsNDQ3LjczNCAxMDY2LjE2LDQzNi41MzEgMTA3MS4yMSw0MjUuMTgyQzEwNzYuMjUsNDEzLjgzMiAxMDgxLjgyLDQwMi43NzQgMTA4Ny45MiwzOTIuMDA5QzEwOTQuMDMsMzgxLjI0MyAxMTAwLjIyLDM3MS41MzggMTEwNi41MSwzNjIuODk0QzExMTguNDUsMzQ3Ljk1IDExMzMuMDksMzM2Ljg0MyAxMTUwLjQxLDMyOS41NzVDMTE2Ny43NCwzMjIuMzA2IDExOTMuOCwzMTkuODU2IDEyMjguNiwzMjIuMjI0QzEyNDUuMjcsMzI0LjY4OSAxMjYzLjY3LDMyOC4xMDQgMTI4My43OCwzMzIuNDY4QzEzMDMuOSwzMzYuODMxIDEzMjQuNjUsMzQyLjIxOCAxMzQ2LjAzLDM0OC42MjlDMTM2Ny40MiwzNTUuMDQgMTM4OC45NywzNjIuMzg0IDE0MTAuNjksMzcwLjY2QzE0MzIuNDEsMzc4LjkzNyAxNDUzLjA5LDM4Ny44MTggMTQ3Mi43NCwzOTcuMzA1QzE0OTguNzMsNDEwLjI5NiAxNTI1LjI2LDQyNi45NDUgMTU1Mi4zMyw0NDcuMjUyQzE1NzkuMzksNDY3LjU2IDE2MDMuMTUsNDkyLjc3NCAxNjIzLjYxLDUyMi44OTdDMTY0OS42OCw1NjEuNTAxIDE2NzAuNDksNjAxLjc0IDE2ODYuMDIsNjQzLjYxNEMxNzAxLjU2LDY4NS40ODggMTcxMi4wOSw3MjcuMjk2IDE3MTcuNjIsNzY5LjAzOEMxNzIzLjE1LDgxMC43OCAxNzI0LDg1MS4yODQgMTcyMC4xNiw4OTAuNTVDMTcxNi4zMiw5MjkuODE2IDE3MDcuODgsOTY1Ljg2OSAxNjk0LjgyLDk5OC43MDdDMTY4MS43NywxMDMxLjU1IDE2NjQuNjMsMTA1OS45NCAxNjQzLjQxLDEwODMuOUMxNjIyLjE4LDExMDcuODYgMTU5Ni44OCwxMTI1LjUzIDE1NjcuNDksMTEzNi45MUMxNTU4LjEzLDExNDEuMTcgMTU0Ny43MywxMTQ0LjE5IDE1MzYuMywxMTQ1Ljk2QzE1MjQuODcsMTE0Ny43MyAxNTEyLjg3LDExNDguNjggMTUwMC4yOSwxMTQ4LjgxQzE0ODcuNywxMTQ4LjkzIDE0NzUuMDksMTE0OC4yIDE0NjIuNDQsMTE0Ni42MUMxNDQ5Ljc5LDExNDUuMDIgMTQzNy41NiwxMTQyLjk4IDE0MjUuNzYsMTE0MC41MUMxMzk5LjAyLDExMzIuNSAxMzY5LjE2LDExMjMuMjYgMTMzNi4xNiwxMTEyLjgxQzEzMDMuMTcsMTEwMi4zNSAxMjY4LjA5LDEwODguMjIgMTIzMC45MiwxMDcwLjQxQzEyMTIuODcsMTA2MS44IDExOTUuMTMsMTA1Mi4wMiAxMTc3LjcxLDEwNDEuMDdDMTE2MC4yOCwxMDMwLjExIDExNDMuOSwxMDE4LjU1IDExMjguNTUsMTAwNi4zOUMxMTEzLjIxLDk5NC4yMjYgMTA5OS4yOCw5ODIuMDA2IDEwODYuNzgsOTY5LjcyOUMxMDc0LjI4LDk1Ny40NTEgMTA2My43OCw5NDUuOTQgMTA1NS4yNyw5MzUuMTk0QzEwMzkuOSw5MTMuMjYxIDEwMjYuMjUsODg4LjkxIDEwMTQuMzIsODYyLjE0M0MxMDAyLjM5LDgzNS4zNzUgOTk0Ljg4NSw4MDkuMzcgOTkxLjgxNyw3ODQuMTI3Qzk5MC44ODEsNzczLjUxNyA5OTAuNzczLDc2NC4yMDUgOTkxLjQ5Miw3NTYuMTkxQzk5Mi4yMTIsNzQ4LjE3NyA5OTQuODE5LDc0Mi4wNDQgOTk5LjMxMyw3MzcuNzk0QzEwMDMuODEsNzMzLjU0MyAxMDEwLjg1LDczMS41NCAxMDIwLjQ0LDczMS43ODNDMTAzMC4wNCw3MzIuMDI2IDEwNDIuNzEsNzM0LjgwOCAxMDU4LjQ2LDc0MC4xMjlaTTcyOS45NjcsMTExMy41OEM2ODkuMDcxLDExMDEuNDUgNjQ4LjE3NCwxMDg2Ljg5IDYwNy4yNzgsMTA2OS45MUM1NjYuMzgxLDEwNTIuOTIgNTI3LjQxLDEwMzQuMTIgNDkwLjM2MiwxMDEzLjQ5QzQ1My4zMTUsOTkyLjg3IDQxOS4xNTQsOTcwLjgzIDM4Ny44OCw5NDcuMzc2QzM1Ni42MDYsOTIzLjkyMSAzMzAuMzg1LDkwMC4wNjIgMzA5LjIxNSw4NzUuNzk4QzI5Ni4yMjQsODYxLjI0IDI4NC4zMTYsODQ2LjI3OCAyNzMuNDksODMwLjkxMUMyNjIuNjY1LDgxNS41NDQgMjU0LjAwNCw3OTkuNTcgMjQ3LjUwOSw3ODIuOTlDMjQxLjAxNCw3NjYuNDEgMjM3LjA0NCw3NDkuMDIxIDIzNS42MDEsNzMwLjgyNEMyMzQuMTU4LDcxMi42MjYgMjM2LjMyMyw2OTMuMDEzIDI0Mi4wOTYsNjcxLjk4NUMyNTQuMTI1LDYzMC43MzcgMjY5Ljc2Miw1OTUuOTU5IDI4OS4wMDcsNTY3LjY1MUMzMDguMjUyLDUzOS4zNDQgMzI5LjE4Miw1MTMuNDYzIDM1MS43OTUsNDkwLjAwOEMzNzAuMDc4LDQ3Mi4yMTUgMzkxLjQ4OSw0NTkuODgxIDQxNi4wMjcsNDUzLjAwNkM0NDAuNTY1LDQ0Ni4xMzIgNDY2LjkwNyw0NDMuMDk5IDQ5NS4wNTMsNDQzLjkwN0M1MjMuMiw0NDQuNzE2IDU1Mi42NjksNDQ4LjU1OCA1ODMuNDYyLDQ1NS40MzNDNjE0LjI1NCw0NjIuMzA3IDY0NC44MDcsNDcxLjIwNCA2NzUuMTE4LDQ4Mi4xMjJDNzA1LjQzLDQ5My4wNDEgNzM0Ljg5OSw1MDUuMTczIDc2My41MjcsNTE4LjUxOEM3OTIuMTU0LDUzMS44NjMgODE4LjQ5Niw1NDUuMDA1IDg0Mi41NTMsNTU3Ljk0NkM4NDguODA4LDU2MS4xODEgODU0LjgyMiw1NzguMzY4IDg2MC41OTYsNjA5LjUwNkM4NjYuMzY5LDY0MC42NDQgODcyLjM4Myw2NzkuNjY4IDg3OC42MzgsNzI2LjU3N0M4ODQuODkzLDc3My40ODcgODkxLjE0OCw4MjQuNjQzIDg5Ny40MDMsODgwLjA0NEM5MDMuNjU3LDkzNS40NDYgOTEwLjAzMiw5ODkuMjMgOTE2LjUyOCwxMDQxLjRDOTIzLjAyMywxMDkzLjU2IDkyOS43NTksMTE0MC40NyA5MzYuNzM1LDExODIuMTJDOTQzLjcxMiwxMjIzLjc4IDk1MC44MDksMTI1My45IDk1OC4wMjYsMTI3Mi41MUM5NjMuMzE4LDEyOTMuNTQgOTYyLjk1NywxMzEwLjUyIDk1Ni45NDMsMTMyMy40NkM5NTAuOTI5LDEzMzYuNCA5NDEuNjY3LDEzNDcuMTIgOTI5LjE1NywxMzU1LjYxQzkxNi42NDgsMTM2NC4xIDkwMS42MTIsMTM3MC45OCA4ODQuMDUxLDEzNzYuMjNDODY2LjQ5LDEzODEuNDkgODQ4LjgwOCwxMzg2Ljk1IDgzMS4wMDYsMTM5Mi42MUM4MTMuMjA0LDEzOTYuNjUgODAxLjA1NSwxMzk2LjY1IDc5NC41NiwxMzkyLjYxQzc4OC4wNjUsMTM4OC41NyA3ODIuODkyLDEzODAuMDggNzc5LjA0MywxMzY3LjEzQzc3Mi4zMDcsMTMyNS44OSA3NjQuODUsMTI4NC4yMyA3NTYuNjcsMTI0Mi4xOEM3NDguNDkxLDEyMDAuMTIgNzM5LjU5LDExNTcuMjYgNzI5Ljk2NywxMTEzLjU4Wk02ODMuNzc4LDkwNC45MTRDNjczLjE5Myw4NjAuNDMxIDY2My4wOSw4MTguMTcyIDY1My40NjcsNzc4LjEzN0M2NDMuODQ0LDczOC4xMDMgNjMzLjc0LDY5OS44ODggNjIzLjE1NSw2NjMuNDkyQzU5OS41OCw2NTIuOTc4IDU3Ni42MDYsNjQ1LjA5MiA1NTQuMjMzLDYzOS44MzVDNTMxLjg2LDYzNC41NzggNTExLjY1Miw2MzEuNzQ4IDQ5My42MSw2MzEuMzQzQzQ3NS41NjcsNjMwLjkzOSA0NjAuNjUyLDYzMi43NTkgNDQ4Ljg2NCw2MzYuODAyQzQzNy4wNzYsNjQwLjg0NiA0MzAuMjIsNjQ2LjkxMiA0MjguMjk2LDY1NUM0MjMuMDAzLDY2OS41NTggNDI0LjU2Nyw2ODcuNzU2IDQzMi45ODcsNzA5LjU5M0M0NDEuNDA3LDczMS40MyA0NTYuNDQyLDc1NC4wNzYgNDc4LjA5Myw3NzcuNTMxQzQ5OS43NDQsODAwLjk4NiA1MjcuNzcsODI0LjAzNiA1NjIuMTcyLDg0Ni42ODJDNTk2LjU3Myw4NjkuMzI4IDYzNy4xMDgsODg4LjczOSA2ODMuNzc4LDkwNC45MTRaIi8+CiAgICA8L2c+Cjwvc3ZnPgo=";
	register_post_type( 'comic',
		array(
		'labels' => array(
			'menu_name' => ('Comics'),
			'name' => __( 'Comic Posts' ),
			'all_items'  => ('All Comics'),
			'singular_name' => __( 'Comic' ),
			'add_new' => __( 'Add New Comic' ),
			'add_new_item' => __( 'Add New Comic' ),
			'edit' => __( 'Edit' ),
			'edit_item' => __( 'Edit Comic Post' ),
			'new_item' => __( 'New Comic' ),
			'view' => __( 'View Comic' ),
			'view_item' => __( 'View Comic' ),
			'search_items' => __( 'Search Comics' ),
			'exclude_from_search' => false,
			'not_found' => __( 'No Comics found' ),
			'not_found_in_trash' => __( 'No Comics found in Trash' )
		),
		'show_in_rest' => true,	// support for Gutenberg Editor
		'supports' => array( 'title', 'editor', 'excerpt', 'author', 'comments', 'thumbnail', 'custom-fields', 'revisions', 'trackbacks', 'shortlinks', 'publicize' ),
			'public' => true,
			'public_queryable' => true,
			'query_var' => 'comic',
			'capability_type' => 'post',
			'hierarchical' => false,
			'can_export' => true,
			'menu_position' => 5,
			'menu_icon' => 'data:image/svg+xml;base64,' . $icon,
			'taxonomies' => array('post_tag'),
			'slug' => 'comic',
			'has_archive' => true,
			'show_in_nav_menus' => true,
			'show_ui' => true
		)
	);
	// New Taxonomy for Comic Posts
	register_taxonomy('chapters', 'comic',
		array(
		// Hierarchical taxonomy (like categories)
		'hierarchical' => true,
		'public' => true,
		'show_ui' => true,
		'query_var' => true,
		'show_tagcloud' => false,
		'has_archive' => true,
		'show_admin_column' => true,
		'show_in_rest' => true,	// support for Gutenberg Editor
		// This array of options controls the labels displayed in the WordPress Admin UI
		'labels' => array(
			'name' => _x( 'Chapters', 'taxonomy general name' ),
			'singular_name' => _x( 'Chapter', 'taxonomy singular name' ),
			'search_items' =>  __( 'Search Chapters' ),
			'all_items' => __( 'All Chapters' ),
			'parent_item' => __( 'Parent Chapter' ),
			'parent_item_colon' => __( 'Parent Chapter:' ),
			'edit_item' => __( 'Edit Chapter' ),
			'update_item' => __( 'Update Chapter' ),
			'add_new_item' => __( 'Add New Chapter' ),
			'new_item_name' => __( 'New Chapter Name' ),
			'menu_name' => __( 'Chapters' )
		),
		// Control slugs for this taxonomy
		'rewrite' => array( 
			'slug' => 'chapters', 
			'with_front' => true, 
			'feeds' => true 
		),
	));
}


function comicpost_activation() 
{

	// FLUSH PERMALINKS //
    // First, we "add" the custom post type via the above written function.
    // Note: "add" is written with quotes, as CPTs don't get added to the DB,
    // They are only referenced in the post_type column with a post entry, 
    // when you add a post of this CPT.
    create_comicpost_type();
    // ATTENTION: This is *only* done during plugin activation hook in this example!
    // You should *NEVER EVER* do this on every page load!!
    flush_rewrite_rules();
}
// Add Custom Post Type and Flush Rewrite Rules
register_activation_hook( __FILE__, 'comicpost_activation' );

// Define default option settings
function comicpost_add_defaults_fn($reset = false) {
	$options = array(	
		'comic_navigation'     => 'traverse',
		'navigation_location'  => 'below',
		'navigation_style'	   => 'none',
		'enable_comic_widgets' => false,
		'set_comicpost_size'   => 'theme',
		'archive_thumb_links'  => true,
		'archive_post_count'   => get_option( 'posts_per_page' ),
		'archive_views'  	   => 'theme',
		
		'watermark_method'	   => 'none',
		'watermark_tile_size'  => '300',
		'watermark_source'	   => 'generate',
		'watermark_opacity'	   => '.50',
		'watermark_image_size' => '300',
		'watermark_text'	   => ''.get_bloginfo().'',
		'watermark_font'	   => 'unitblock-font',
		'watermark_orientation'=> 'diagonal',
		'watermark_clean_copy' => false,
		'watermark_clean_suffix' => 'clean_'.rand().'',
		'show_clean_to_logged_in' => false,
		
		'comics_under_glass'   => false,
		'discourage_printing'  => 'allowprint',
		'faux_watermark_method'=> 'tile',
		'faux_watermark_text'  => ''.get_bloginfo().'',
		'faux_watermark_opacity'=> '.25',
		'encode_comic_urls'    => false,
		'apply_to_archives'    => false,
		'apply_to_public'	   => false,
		'omit_from_galleries'  => false,
		'hide_old_comics'      => 'x',
		'hide_old_comic_posts' => false,
		'require_login'		   => false,
		'content_login'		   => false,
		'shortcode_login'	   => false,
		
		'rating_system'		   => 'none',
		'star_rating_required' => false,
		'post_like_style'	   => 'like-style-love',
		'post_like_button_text'=> 'Like',
		'post_unlike_button_text' => 'Unlike',
		'post_liking_action'   => 'liked',
		'facebook_meta'		   => false,
		'fallback_thumbnail'   => '',
		'twitter_meta'		   => false,
		'twitter_id'		   => '',
		'mastodon_meta'	       => false,
		'mastodon_id'          => '',
		
		'manageposts_thumbnail_size' => '120',
		'remove_admin_bar'		=> false
	);
	if ( $reset === true) {
		update_option('comicpost_options', $options);		
	} else {
		$dbcheck = get_option('comicpost_options');
		foreach ($options as $key => &$value){ // & passes as reference
			if (isset($dbcheck[$key])){ // if option is set
				if ($dbcheck[$key] != $value){ // if value is not default
					$value = $dbcheck[$key];   // update value to custom setting in db
				}
			} else {
				// option is not set, use default
			}
		}
		update_option('comicpost_options', $options);
	}
		
}
// Add Database Fields If Needed //
register_activation_hook(__FILE__, 'comicpost_add_defaults_fn');


// Create ComicPost Specific Sidebars regardless if they already exist.
function comicpost_register_sidebars() {

		foreach (array(
					__('Over Comic', 'comicpost'),
					__('Left of Comic','comicpost'),
					__('Right of Comic', 'comicpost'),
					__('Under Comic', 'comicpost')				
					) as $sidebartitle) {
			register_sidebar(array(
						'name'=> $sidebartitle,
						'id' => 'comicpost-sidebar-'.sanitize_title($sidebartitle),
						'description' => __('ComicPost Sidebar Location', 'comicpost'),
						'before_widget' => "<div id=\"".'%1$s'."\" class=\"widget ".'%2$s'."\">\r\n<div class=\"widget-head\"></div>\r\n<div class=\"widget-content\">\r\n",
						'after_widget'  => "</div>\r\n<div class=\"clear\"></div>\r\n<div class=\"widget-foot\"></div>\r\n</div>\r\n",
						'before_title'  => "<h2 class=\"widgettitle\">",
						'after_title'   => "</h2>\r\n"
						));
		}

}

add_action('widgets_init', 'comicpost_register_sidebars');




// Load the Core Functions //
if (is_admin()){
	@require('functions/comicpost_admin_functions.php');
	@require('options/class.settings-api.php');
	@require('options/comicpost_admin_options.php');
} else { // is not admin
	@require('functions/comicpost_frontend_functions.php');
	@require('functions/comicpost_shortcodes.php');
	@require('functions/comicpost_navigation.php');
}
// Plugin Update Check can no longer be inside if-else

@require('plugin-update-checker/plugin-update-checker.php');
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;
$ComicPostUpdateChecker = PucFactory::buildUpdateChecker(
'https://github.com/kmhcreative/comicpost',
	__FILE__,'comicpost'
);
$ComicPostUpdateChecker->getVcsApi()->enableReleaseAssets();

?>