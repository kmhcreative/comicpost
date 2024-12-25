<?php
/*	One-stop shopping for all your comic shortcode needs!
*/

add_shortcode('comicpost-chapter-list', 'comicpost_chapter_list');
add_shortcode('comicpost-archive-dropdown','comicpost_archive_dropdown');
add_shortcode('insertcomic','comicpost_show_comic_shortcode');
add_shortcode('comicpost-latest-comic', 'comicpost_show_latest_comic');
add_shortcode('comicpost-share', 'comicpost_share_shortcode');
add_shortcode('protect', 'comicpost_protect_string');
add_shortcode('notpublic','comicpost_hide_from_public');
add_shortcode('userlikes','comicpost_user_likes_list');
add_shortcode('userratings','comicpost_user_ratings_list');
add_shortcode('usercomments','comicpost_user_comments_list');
add_shortcode('topcomics','comicpost_top_comics');

/* 	Utility function
	This normalizes term names or slugs to term_ids
*/
function comicpost_get_term_ids( $list, $tax ){
	$include_list  = explode(',', $list);
	$includes = array(); // holding pen
	foreach( $include_list as $included ){
		$add = $included;
		if (!is_numeric($included)){ // not a number
			if (get_term_by('name', $included, $tax)){ // check if by name
				$add = get_term_by('name', $included, $tax)->term_id;
			} else if (get_term_by('slug', $included, $tax)){ // check if by slug
				$add = get_term_by('slug', $included, $tax)->term_id;
			} else { // not a valid term for tax
				$add = null;
			}
		}
		if (!empty($add)){
			$includes[] = $add; // add it to holding pen
		}
	}
	// cast array back to string
	$list = implode(',', $includes);
	// return it
	return $list;
}


/*	Add a list of Comic Chapters anywhere
	Example usage: 	[comicpost-chapter-list] // list with ALL Chapters and sub-chapters in default style
					[comicpost-chapter-list exclude="124,142,143,168"] // list excluding 4 Chapters and all their sub-chapters
*/
function comicpost_chapter_list( $atts, $content='' ){
	extract( shortcode_atts( array(
	    'include' => '',
		'exclude' => '',
		'emptychaps' => 'hide',
		'thumbnails' => 'true',
		'order' => 'ASC',
		'orderby' => 'name',
		'postdate' => 'last',
		'dateformat'  => 'site',
		'description' => 'false',
		'comments' => 'true',
		'showratings' => 'false',
		'liststyle'  => 'flat',
		'title' => 'Chapters'
	), $atts) );
	// PASSED AS STRINGS NOT BOOLEANS!!
	if ($include != 'all'){
		$include = comicpost_get_term_ids( $include, 'chapters');
	}
	// set show/hide empties
	if ($emptychaps == 'hide'){
		$hide_empty = 1;
		$hide_if_empty = true;
	} else {
		$hide_empty = 0;
		$hide_if_empty = false;
	}
	$exclude = comicpost_get_term_ids( $exclude, 'chapters');
	$my_walker = new Walker_Chapter();	
	// allow multiples to be on one page
	$uid = wp_unique_id();
	// Build arguments for drop-down
	$args = array(
	    'include'	    => $include,
		'exclude' 		=> $exclude,
		'exclude_tree' 	=> $exclude,
		'hierarchical'  => 1,
		'depth'			=> 0,
		'hide_empty'    => $hide_empty,
		'hide_if_empty' => $hide_if_empty,
		'taxonomy'		=> 'chapters',
		'title_li'		=> $title,
		'walker'		=> $my_walker,
		  'order'		=> $order,
		  'orderby'     => $orderby,
		  'thumbnail'   => $thumbnails,
		  'comments'    => $comments,
		  'showratings' => $showratings,
		  'liststyle'   => $liststyle,
		  'postdate'    => $postdate,
		  'dateformat'  => $dateformat,
		  'description' => $description,
		'echo'			=> 0
	);
	$output = '<ul id="chapter_list_'.$uid.'" class="chapter-list">';
	// get chapter terms	
	$terms = get_terms( $args );
	$output .= wp_list_categories( $args ).'</ul>';

	return $output;
}

function comicpost_chapter_thumbnail($chapter,$firstlast = 'first',$size = 'thumbnail'){
	if ( $firstlast == 'first' ){
		$order = 'ASC';
	} else if ( $firstlast == 'last' ){
		$order = 'DESC';
	} else {
		return;
	}
	$args = array(
		'showposts' => 1,
		'post_type' => 'comic',
		'tax_query' => array(
			array(
				'taxonomy' => 'chapters',
				'terms'    => $chapter->term_id
			)
		),
		'posts_per_page' => '1',
		'order' => $order
	);
	$firstlast_post = null;
	$image = array();
	// get first post
	$firstlast_post_query = new WP_Query( $args );
	$posts = $firstlast_post_query->get_posts();

	if( !empty( $posts )){
		$firstlast_post = array_shift( $posts );
	}
	if ($firstlast_post){
		if (has_post_thumbnail( $firstlast_post->ID )){
			$image = wp_get_attachment_image_src( get_post_thumbnail_id( $firstlast_post->ID), $size );
		}
	}
	return $image;
}

function comicpost_get_chapter_comment_count($chapter,$status = 'all'){
	if ($status == 'true'){
		$status = 'all';
	};
	$args = array(
		'posts_per_page' => -1,
		'post_type'   => 'comic',
		'tax_query'   => array(
			array(
				'taxonomy' => 'chapters',
				'terms'    => $chapter->term_id
			)
		)
	);
	$posts = null;
	$count = 0;
	$posts_query = new WP_Query( $args );
	if ( $posts = $posts_query->get_posts() ){
		foreach ( $posts as $post ){
			$comments = get_comment_count( $post->ID );
			$count = ($count + $comments[$status]);
		}
	}
	return $count;
}

function comicpost_get_chapter_average_rating($chapter, $type = 'stars'){
	$args = array(
		'posts_per_page' => -1,
		'post_type'		 => 'comic',
		'tax_query'		 => array(
			array(
				'taxonomy' => 'chapters',
				'terms'    => $chapter->term_id
			)
		)
	);
	$posts = null;
	$count = 0;
	$post_rating   = 0;
	$total_ratings = 0;
	$posts_query = new WP_Query( $args );
	if ( $posts = $posts_query->get_posts() ){
		foreach( $posts as $post ){
			if ( $type == 'likes' ){
//				$post_rating = comicpost_get_post_likes( $post->ID );
				$post_rating = get_post_meta( $post->ID, 'comicpost_likes', true) ?: 0;
			} else {
//				$post_rating = comicpost_comment_rating_get_average_ratings( $post->ID );
				$post_rating = get_post_meta( $post->ID, 'comicpost_stars', true) ?: 0;
			}
			if ($post_rating > 0 ){
				$count++;
				$total_ratings = $total_ratings + $post_rating;
			}
		}
	}
	if ( $type == 'likes' ){
		$average = $total_ratings;
	} else {
		if ($count > 0){
			$average = round( $total_ratings / $count, 1 );
		} else {
			$average = 0;
		}
	}
	return $average;
}

function comicpost_get_chapter_post_date($chapter, $order = 'last', $format = 'site' ){
	// determine if chapter date is when it started or ended
	if ($order == 'first') {
		$order = 'ASC';
	} else {
		$order = 'DESC';
	}
	$args = array(
		'posts_per_page' => 1,
		'post_type'   => 'comic',
		'tax_query'   => array(
			array(
				'taxonomy' => 'chapters',
				'terms'    => $chapter->term_id
			)
		),
		'order' => $order
	);
	$post = null;
	$date = '';
	$posts_query = new WP_Query( $args );
	// get one post
	if ( $posts = $posts_query->get_posts() ){
		$post = array_shift( $posts );
	}
	// if we have a post get the date
	if ($post){
		$date = $post->post_date;
	}
	// if we have a date convert it
	if (!empty($date)){
		if ( $format != 'site'){
			$date = get_date_from_gmt( $date, $format );
		} else { // site setting
			// post date converted to preferred date format in General Settings
			$date = get_date_from_gmt( $date, get_option('date_format') );
		}
	}
	// return it
	return $date;
}



// custom chapter walker list output
class Walker_Chapter extends Walker_Category {
	function start_lvl(&$output, $depth=0, $args=array() ){
		$output .= "\n<ul class='chapter-list'>\n";
	}
	function end_lvl(&$output, $depth=0, $args=array() ){
		$output .= "</ul>\n";
	}
	function start_el(&$output, $item, $depth=0, $args=array(), $current_object_id = 0){
		$options = get_option('comicpost_options');
		$elements = 1; // it will always have chapter name
		if ( $args['thumbnail'] == 'first' || $args['thumbnail'] == 'last' ){
			$image = comicpost_chapter_thumbnail($item,$args['thumbnail']);
			if (!empty($image)){
				$thumbnail = '<img class="chapter-thumbnail" src="'.$image[0].'" width="'.$image[1].'" height="'.$image[2].'" alt="Chapter thumbnail image for'.esc_attr($item->name).'."/>';
				$elements++;
			} else {
				$thumbnail = '';
			}
		} else {
			$thumbnail = '';
		}
		if ( $args['postdate'] == 'false' ){
			$postdate = '';
		} else {
			if ($args['postdate'] != 'first' && $args['postdate'] != 'last'){
				$args['postdate'] = 'last';
			}
			$postdate = '<span class="chapter-postdate">'.comicpost_get_chapter_post_date($item, $args['postdate'], $args['dateformat']).'</span>';
			$elements++;
		}
		if ( $args['comments'] == 'false' ){
			$comments = '';
		} else {
			$comments = '<span class="chapter-comments">'.comicpost_get_chapter_comment_count($item,$args['comments']).'</span>'; 
			$elements++;
		}
		if ( $args['description'] == 'false' ){
			$description = '';
		} else {
			$description = ' <span class="chapter-description">'.esc_attr($item->description).'</span>';
			$elements++;
		}
		if ( $args['showratings'] == 'false'){
			$rating = '';
		} else {
			// find out if this is star ratings or post likes
			if ( $options['rating_system'] == 'stars' ){
				$stars  = comicpost_get_chapter_average_rating( $item, 'stars' );
				if ($stars > 0){
					$rate_txt  = $stars;
					$title_txt = 'rated '.$stars.' stars';
				} else {
					$rate_txt  = '&ndash;.&ndash;';
					$title_txt = 'no rating';
				}
				$width  = round( $stars * 20, 1);
				$rating = ' <span class="chapter-rating"><span class="star-ratings" title="'.$title_txt.'"><span class="empty-rating"></span><span class="full-rating" style="width: '.$width.'%;"></span><span class="rating-text">'.$rate_txt.'</span></span></span>';
				$elements++;
			} else if ( $options['rating_system'] == 'likes'){
				$likes  = comicpost_get_chapter_average_rating( $item, 'likes' );
				$likes_style = $options['post_like_style'];
				$like_action = $options['post_liking_action'];
				$rating = ' <span class="chapter-rating"><span class="like-ratings rating-text '.$likes_style.'" title="'.$likes.' '.$like_action.' this">'.$likes.'</span></span>';
				$elements++;
			} else { // rating_system = 'none'
				$rating = '<!--// ratings included but no rating system is enabled //-->';
			} 
		}
		if ( $args['liststyle'] == 'indent' ){
			$list_style = " list-style-indent";
		} else if ( $args['liststyle'] != 'flat'){
			$list_style = " list-style-".$args['liststyle'];
		} else {
			$list_style = "";
		}
		$output .= '<li class="chapter-list-item'.$list_style.' elements-'.$elements.'"><a href="'.home_url('chapters/'.$item->slug).'/" class="chapter-list-item-link">'.$thumbnail.'<span class="chapter-title">'.esc_attr($item->name).'</span> '.$postdate.' '.$comments.' '.$rating.' '.$description.'</a>';

	}
	function end_el(&$output, $item, $depth=0, $args=array() ){
		$output .= "</li>\n";
	}
};



/*	Add a drop-down list of Comic Chapters anywhere
	Example usage: 	[comicpost-archive-dropdown] // drop-down with ALL Chapters and sub-chapters
					[comicpost-archive-dropdown exclude="124,142,143,168"] // drop-down excluding 4 Chapters and all their sub-chapters
*/
function comicpost_archive_dropdown( $atts, $content='' ){
	extract( shortcode_atts( array(
	    'include' => '',
		'exclude' => '',
		'emptychaps' => true,
		'title' => 'Select Chapter'
	), $atts) );
	// PASSED AS STRINGS NOT BOOLEANS!!
	if ($include != 'all'){
		$include = comicpost_get_term_ids( $include, 'chapters');
	}
	// set show/hide empties
	if ($emptychaps == 'hide'){
		$hide_empty = 1;
		$hide_if_empty = true;
	} else {
		$hide_empty = 0;
		$hide_if_empty = false;
	}
	$exclude = comicpost_get_term_ids( $exclude, 'chapters');
	if ($title == ''){
		$title = 'Select Chapter';
	}
	// allow multiples to be on one page
	$uid = wp_unique_id();
	// figure out if we need slug or term_id based on permalink structure

	// Build arguments for drop-down
	$args = array(
	    'include'	    => $include,
		'exclude' 		=> $exclude,
		'exclude_tree' 	=> $exclude,
		'hierarchical'  => 1,
		'depth'			=> 0,
		'hide_empty'    => $hide_empty,
		'hide_if_empty' => $hide_if_empty,
		'show_option_none' => $title,
		'id' 			=> 'comicpost_chapter_drop'.$uid.'',
		'name'			=> 'comicpost_chapter_drop'.$uid.'',
		'taxonomy'		=> 'chapters',
		'selected'		=> 'chapters',
		'value_field'	=> 'slug',
		'echo'			=> 0
	);
	$select  = wp_dropdown_categories( $args );
	// get chapter terms	
	$terms = get_terms( 'chapters' );
	if (empty($terms)){
		// if there are no terms dropdown would be empty, so bail...
		return;
	} else {
		// if permalink structure is empty URL ends in ?chapters=slug
		if (empty(get_option('permalink_structure'))){
			$linkfront = explode('=', get_term_link( $terms[0] ));
			$linkfront = $linkfront[0].'=';
		} else {
			if (!empty($terms[0])){
				$linkfront = dirname( get_term_link( $terms[0] ) ).'/'; // its an object, no 2nd param needed
			} else {
				$linkfront = get_option('home').'/chapters/';
			}
		}
	}
    $replace = "<select$1 onchange=\"location.href='".$linkfront."'+this.options[this.selectedIndex].value\">";
    $select  = preg_replace( '#<select([^>]*)>#', $replace, $select );

	return $select;
}

/*	Insert a specific comic anywhere with a link to the comic post page.
		comicnav="true|false"	: whether or not to include comic navigation links below image
		size="thumbnail|medium|large|full" : the size of the image to display
		protect="encode,glass,noprint" : single or comma-separated list of protection overrides
		orderby="ASC|DESC" : start at beginning or start at end, ignored if single
		number="1" : offset from start or end (depending on orderby, ignored is single) 
		chapter="slug_for_chapter" : which chapter to grab, ignored if single.
		slug = "slug-name" : show only that comic post (overrides chapter)
		id = "n" : where "n" is a specific post ID, only shows that comic post (overrides chapter)
		
		Example Usage:
		[insertcomic size="full" chapter="chapter-one" orderby="DESC" comicnav="true"] : That would insert the full size comic image of the last (latest) comic in "Chapter One" with comic navigation below.
		[insertcomic size="large" id="8045" protect="glass"] : would insert comic post ID 8045 large image under glass protection, no navigation links below it.
		[insertcomic size="medium" chapter="chapter-three" orderby="ASC" comicnav="true" number="5"] : would insert the fifth comic in "Chapter Three" medium-sized image with navigation links below.
*/
function comicpost_show_comic_shortcode($atts, $content = '') {
	extract( shortcode_atts( array(
					'comicnav' => false,
					'protect'  => '',
					'size' => 'thumbnail',
					'slug' => '',
					'chapter' => '',
					'orderby' => 'DESC',
					'month' => '',
					'day' => '',
					'year' => '',
					'number' => '1',
					'link' => true,
					'id' => '',
					'login' => ''
					), $atts ) );
	global $post;
	if ($id){
		$args = array(
		   'p' => $id,
		   'posts_per_page' => '1',
		   'post_type' => 'comic',
		   'ignore_sticky_posts' => 1
				);
	} else {
		$args = array(
			'name' => $slug,
			'orderby' => $orderby,
			'showposts' => $number,
			'post_type' => 'comic',
			'chapters' => $chapter,
			'exclude' => $post->ID,
			'year' => $year,
			'month' => $month,
			'day' => $day
				);
		}
	$thumbnail_query = new WP_Query($args);
	$output = '';
	$archive_image = '';
	// turn protect into array
	$protect = explode(',',$protect);
	
	// make sure comic image function knows this is coming from shortcode
	$atts['shortcode'] = true;

	if ($thumbnail_query->have_posts()) {
		while ($thumbnail_query->have_posts()) : $thumbnail_query->the_post();
			// If comics have unique styling based on chapter add that to container
			$terms = wp_get_object_terms( $post->ID, 'chapters');
			$classes = [];
			foreach ($terms as $term) {
				$classes[] = 'story-'.$term->slug;
			}
			$classes = implode(' ',$classes);
			// now get the permalink to the comic post
			$the_permalink = get_permalink($post->ID);
			$options = get_option('comicpost_options');
			
			if ( has_post_thumbnail($post->ID)){
				$post_thumbnail_id = get_post_thumbnail_id($post->ID);
			
				$output = comicpost_comic_image('', $post->ID, $post_thumbnail_id, $size, $atts);
			} 
		endwhile;
	}
	// prevent inserted comic from hijacking the current post/page query
	wp_reset_query();
	return $output;
};

/* 	Simplified shortcode for just displaying the latest comic in a chapter
	also if someone was using ComicPost Lite Plugin this will prevent latest
	comic embeds from breaking.
*/
function comicpost_show_latest_comic($atts, $content = '') {
	extract( shortcode_atts( array(
			'chapter' => '',
			'size' => 'large',
			'link'    => true
		), $atts) );
		if ( !empty($chapter) ){
			$chapter = comicpost_get_term_ids( $chapter, 'chapters' );
		} else {
			return;
		}
		return do_shortcode('[insertcomic size="'.$size.'" chapter="'.$chapter.'" link="'.$link.'"]');
}


/* Social Media SHARE Buttons Shortcode */

function comicpost_share_shortcode( $atts, $content = null ) {
	$options = get_option('comicpost_options');
	$logo = get_site_icon_url();
	global $post, $wp;
	if (is_archive()) {
		$title = get_the_archive_title();
		$permalink = home_url( add_query_arg( array(), $wp->request ) );
		$shortlink = home_url( add_query_arg( array(), $wp->request ) );
		$thumbnail = $logo;
	} else if (is_search()) {
		$title = 'Search results for: '.get_search_query();
		$permalink = get_search_link();
		$shortlink = get_search_link();
		$thumbnail = $logo;
	} else if (is_single() || is_page() ) {
		$title = get_the_title($post->ID);
		$permalink = get_permalink($post->ID);
		$shortlink = wp_get_shortlink($post->ID);
		$thumbnail = wp_get_attachment_url( get_post_thumbnail_id($post->ID) );
	} else if (is_home()){
		$title = get_bloginfo( 'name' );
		$permalink = get_site_url();
		$shortlink = get_site_url();
		if ($post) {
		$thumbnail = wp_get_attachment_url( get_post_thumbnail_id($post->ID) );
		} else {
		$thumbnail = $logo;
		}
	} else {
		return;
	}
	extract(shortcode_atts(array(
		'type' => 'label',	// text, label (default), small, medium, large
		'include' => '',
		'exclude' => '',
		), $atts));

		if ($include != '' && $include != 'all') {
			$include = strtolower($include);
			$include = explode(",",$include);
		} else {
			$include = array('facebook','threads','bluesky','mastodon','tumblr','reddit','linkedin','pinterest','rss','email');	
		}
		if ($exclude != null && $exclude != '') {
			$exclude = strtolower($exclude);
			$exclude = explode(",",$exclude);
		} else {
			$exclude = array();
		}
	$social = '<div class="cp-sharethis '.$type.'">';
	if ( in_array('facebook',$include) && !in_array('facebook',$exclude) ) {
	$social .= '<a href="https://www.facebook.com/sharer.php?u='.urlencode($permalink).'&amp;t='.urlencode($title).'" title="Share on Facebook" rel="nofollow" target="_blank" onclick="event.preventDefault();window.open(this.href,\'_blank\',\'height=400,width=700\');" class="cp-share facebook"><span>Facebook</span></a>';
	}
	if ( in_array('threads',$include) && !in_array('threads',$exclude) ){
	$social .= '<a href="https://www.threads.net/intent/post?text='.urlencode($title).'%0A%0Z'.urlencode($permalink).'" title="Share on Threads" rel="nofollow" target="_blank" onclick="event.preventDefault();window.open(this.href,\'_blank\',\'height=400,width=700\');" class="cp-share threads"><span>Threads</span></a>';
	}
	if ( in_array('bluesky',$include) && !in_array('bluesky',$exclude) ) {
	$social .=  '<a href="http://bsky.app/intent/compose?text='.urlencode($title).'%20$'.urlencode($shortlink).'" title="Share on Bluesky" rel="nofollow" target="_blank" onclick="event.preventDefault();window.open(this.href,\'_blank\',\'height=400,width=700\');" class="cp-share bluesky"><span>Bluesky</span></a>';
	}
	if ( in_array('tumblr',$include) && !in_array('tumblr',$exclude) ){
	$social .= '<a href="http://tumblr.com/widgets/share/tool?canonicalUrl='.urlencode($permalink).'" title="Share on Tumblr" rel="nofollow" target="_blank" onclick="event.preventDefault();window.open(this.href,\'_blank\',\'height=400,width=700\');" class="cp-share tumblr"><span>Tumblr</span></a>';
	}
	if ( in_array('mastodon',$include) && !in_array('mastodon',$exclude) ){
	$social .= '<a href="'.$permalink.'" title="Share on Mastodon" rel="nofollow" target="_blank" onclick="event.preventDefault();some.share(this.href);event.stopImmediatePropagation();" class="cp-share mastodon"><span>Mastodon</span></a>';
	}	
	if ( in_array('reddit',$include) && !in_array('reddit',$exclude) ) {	
	$social .=  '<a href="http://www.reddit.com/submit?url='.urlencode($permalink).'&amp;title='.urlencode($title).'" title="Share on Reddit" rel="nofollow" target="_blank" onclick="event.preventDefault();window.open(this.href,\'_blank\',\'height=400,width=700\');" class="cp-share reddit"><span>Reddit</span></a>';
	}
	if ( in_array('linkedin',$include) && !in_array('linkedin',$exclude) ) {
	$social .=  '<a href="http://www.linkedin.com/shareArticle?mini=true&amp;title='.urlencode($title).'&amp;url='.urlencode($shortlink).'" title="Share on LinkedIn" rel="nofollow" target="_blank" onclick="event.preventDefault();window.open(this.href,\'_blank\',\'height=400,width=700\');" class="cp-share linkedin"><span>LinkedIn</span></a>';
	}
	if ( in_array('pinterest',$include) && !in_array('pinterest',$exclude) ) {
	$social .=  '<a href="http://pinterest.com/pin/create/button/?url='.urlencode($permalink).'&media='.urlencode($thumbnail).'" title="Pin this!" rel="nofollow" target="_blank" onclick="event.preventDefault();window.open(this.href,\'_blank\',\'height=400,width=700\');" class="cp-share pinterest"><span>Pinterest</span></a>';
	}
	if ( in_array('rss',$include) && !in_array('rss',$exclude) ) {
	$social .=  '<a href="'.get_site_url().'/?feed=rss" title="RSS Feed" rel="nofollow" target="_blank" onclick="event.preventDefault();window.open(this.href,\'_blank\',\'height=400,width=700\');" class="cp-share rss-feed"><span>RSS Feed</span></a>';
	}
	if ( in_array('email',$include) && !in_array('email',$exclude) ) {
	$social .=  '<a href="mailto:?subject=Sharing: '.$title.'&amp;body=%0AThought you might be interested in this:%0A%0A'.$title.'%0A%0A'.urlencode($permalink).'%0A%0A'.urlencode($thumbnail).'" title="Share by E-mail" rel="nofollow" target="_blank" class="cp-share cp-mail"><span>E-mail Link!</span></a>';

	}
	$social .= '</div>';

	return $social;
}

// Register Encode This Shortcode //
function comicpost_protect_string( $atts, $content = null ) {
	extract(shortcode_atts(array(
		'key' => '',	// optional value 0-255 or a random value is picked
		'type' => '',	// URL or any valid <a> protocol ( mailto:, tel:+, callto:, skype:, etc)
		'placeholder' => '[Hidden Content]', // What protected element should say
		), $atts));			
	$encodedString = comicpost_encodeString( $content, $key );

	// is the content or type a URL?
	if ( $type == 'url' || preg_match( '/http:|https:/' , $content) || preg_match( '/http:|https:/' , $type) ) {
		if ( preg_match( '/^https?:\/\//' , $type) ) {
			// [protect type="https://www.somesite.com"]Some Website[/protect]
			$url = 'data-url="'.comicpost_encodeString( $type ).'" ';
			$type = 'url';
		} else if ( preg_match( '/^https?:\/\//' , $content) ) {
			// [protect]https://www.somesite.com[/protect]	(type="url" is not necessary)
			$url =  '';
			$type = 'url';
		} else {
			// [protect type="url"]www.somesite.com[/protect]
			$url = 'data-url="'.comicpost_encodeString( 'https://'.$content ).'" '; // assume SSL these days
			$type = 'url';
		}
		$protected_string = '<a href="#" class="comicpost-protected" data-type="'.$type.'" data-content="'.$encodedString.'" '.$url.'aria-live="polite">'.$placeholder.'</a>';
	} else if ( $type ) {	
		// type is something, just not a URL
		$protected_string = '<a href="#" class="comicpost-protected" data-type="'.$type.'" data-content="'.$encodedString.'" aria-live="polite">'.$placeholder.'</a>';
	} else {
		$protected_string = '<span class="comicpost-protected" data-content="'.$encodedString.'" aria-live="polite">'.$placeholder.'</span>';
	}
	return $protected_string;
}

function comicpost_hide_from_public( $atts, $content = null ){
	extract(shortcode_atts(array(
		'placeholder' => '', // what hidden element should say to those who are not logged in
		), $atts));
	
	if ( is_user_logged_in() ){
		return $content;
	} else {
		if (!empty($placeholder)){
			$placeholder = '['.$placeholder.']';
		}
		return $placeholder;
	}
}

/* 	USER COMMENT AND RATING LISTS
	=============================
	These are intended to be used on a custom Dashboard or Profile page
	so users can see what they've liked, starred, or commented on within
	the last year (unless you override and show some other range)
*/

/* Shows a list of comics the current user has liked */
function comicpost_user_likes_list( $atts, $content = null ){
	extract(shortcode_atts(array(
		'comic' => 'thumbnail',
		'dummy' => false
	), $atts));
	$options = get_option('comicpost_options');
	if ($options['rating_system'] == 'likes' && is_user_logged_in() ){
		$likes = comicpost_get_user_post_likes();
		$style = $options['post_like_style'];
		$action = $options['post_liking_action'];
		if ( $likes ){
			$content = '<h2 class="user-likes-title '.$style.'">Comics you have '.$action.':</h2><ul class="liked-comics-list">';
			foreach ($likes as $like){
				if ($comic != 'none' && has_post_thumbnail( $like )){
					$image = wp_get_attachment_image_src( get_post_thumbnail_id( $like ), $comic );
					$img_html = '<img src="'.$image[0].'" width="'.$image[1].'" height="'.$image[2].'"/>';
				} else {
					$img_html = '';
				}
				$content .= '<li>'.$img_html.'<a href="'.get_permalink($like).'" class="liked-comic">'.get_the_title( $like ).'</a></li>';
			}
			$content .= '</ul>';
		} else {
			if ( $dummy ){
				if ($dummy === 'placeholder') { $restyle = ' placeholders'; } else { $restyle = ''; }
				$content = '<h2 class="user-likes-title '.$style.' empty'.$restyle.'">Comics you have '.$action.':</h2><ul class="liked-comics-list empty'.$restyle.'">
				<li><span class="thumbnail"></span><span class="title"></span></li>
				<li><span class="thumbnail"></span><span class="title"></span></li>
				<li><span class="thumbnail"></span><span class="title"></span></li>
				</ul>';
			}
		}
	}
	return $content;
}

/* Shows a list of the top comics by rating
*/
function comicpost_top_comics( $atts, $content = null ){
	extract(shortcode_atts(array(
		'comic' => 'thumbnail',
		'number' => 5,
		'showrating' => true,
		'rank' => true,
		'postdate' => true,
		'dateformat' => 'site',
		'comments' => true,
		'from' => null,
		'to'   => null,
		'title' => '',
		'liststyle' => 'chapter-list',
		'chapters' => ''
	), $atts));
	$options = get_option('comicpost_options');
	
	if ($from && $to){
		$date_query = array(
			'after' => $from,
			'before'=> $to,
			'inclusive' => true
		);
	} else {
		$date_query = array();
	}
	
	if ($chapters){
		$term_ids = comicpost_get_term_ids( $chapters, 'chapters' );
		$tax_query = array(
			array(
				'taxonomy' => 'chapters',
				'terms'    => $term_ids
			)
		);
	} else {
		$tax_query = array();
	}
	
	
	if ($options['rating_system'] == 'likes') {
		$style   = $options['post_like_style'];
		$action  = $options['post_liking_action'];
		$metakey = 'comicpost_likes';
		$orderby = 'meta_value_num';
	} else if ($options['rating_system'] == 'stars') {
		$style   = 'five-star';
		$action  = 'Rated';
		$metakey = 'comicpost_stars';
		$orderby = 'meta_value_num';
	} else {
		$style   = '';
		$action  = '';
		$metakey = '';
		$orderby = 'comment_count';
	}
		$args = array(
			'post_type' => 'comic',
			'post_status' => 'publish',
			'numberposts' => $number,
			'meta_key'	=> $metakey,
			'orderby' => $orderby,
			'order' => 'DESC',
			'date_query' => $date_query,
			'tax_query'  => $tax_query
		);
	
	$posts = get_posts( $args );
	if ( $posts ){
		$content = '<h2 class="top_comics_likes '.$style.'">Top '.ucfirst($action).' Comics'.esc_attr($title).'</h2><ul class="chapter-list top-comics">';
		$ranking  = 0;
		foreach( $posts as $post ){
			$elements = 1;
			if ( $comic != 'none' && has_post_thumbnail( $post->ID )){
				if ( $comic != 'thumbnail' && $comic != 'medium' ){
					 $comic = 'thumbnail'; // disallow large or full-size image
				}
				$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), $comic );
				if (!empty($image)){
					$thumbnail = '<img class="chapter-thumbnail" src="'.$image[0].'" width="'.$image[1].'" height="'.$image[2].'"/>';
					$elements++;
				} else {
					$thumbnail = '';
				}
			} else {
				$thumbnail = '';
			}	
			if ( !$postdate ){
				$date = '';
			} else {
				if ($dateformat == 'site') {
					$date_format = get_option('date_format');
				} else {
					$date_format = $dateformat;
				}
				$date = '<span class="chapter-postdate">'.get_the_date($date_format,$post->ID).'</span>';
				$elements++;
			}
			if ( !$comments ){
				$comment_count = '';
			} else {
				$comment_count = get_comment_count($post->ID);
				$comment_count = '<span class="chapter-comments">'.$comment_count['approved'].'</span>'; 
				$elements++;
			}
			if ( $showrating ){
				// find out if this is star ratings or post likes
				if ( $options['rating_system'] == 'stars' ){
					$stars  = get_post_meta($post->ID, 'comicpost_stars',true) ?: 0;
					if ($stars > 0){
						$rate_txt = $stars;
					} else {
						break;
					}
					$width  = round( $stars * 20, 1);
					$rating = ' <span class="chapter-rating"><span class="star-ratings"><span class="empty-rating"></span><span class="full-rating" style="width: '.$width.'%;"></span><span class="rating-text">'.$rate_txt.'</span></span></span>';
					$elements++;
				} else if ( $options['rating_system'] == 'likes'){
					$likes  = get_post_meta($post->ID, 'comicpost_likes',true) ?: 0;
					if ($likes == 0) { break; }
					$likes_style = $options['post_like_style'];
					$rating = ' <span class="chapter-rating"><span class="like-ratings rating-text '.$likes_style.'">'.$likes.'</span></span>';
					$elements++;
				} else { // rating_system = 'none'
					$rating = '<!--// ratings included but no rating system is enabled //-->';
				} 
			}
			// prepends to title
			if ( $rank ) {
				$ranking++;
				$show_ranking = $ranking.'. ';
			} else {
				$show_ranking = '';
			}
			if ( $liststyle ){
				$list_style = " list-style-".esc_attr($liststyle);
			} else {
				$list_style = "";
			}
			$content .= '<li class="chapter-list-item'.$list_style.' elements-'.$elements.'"><a href="'.get_permalink($post->ID).'/" class="chapter-list-item-link">'.$thumbnail.'<span class="chapter-title">'.$show_ranking.get_the_title($post->ID).'</span> '.$date.$comment_count.$rating.'</a></li>';
		}
	
	} // if $posts
	return $content;
}

/* Shows a list of comics the current user rated and what rating they gave
	Note that this only shows ratings for the last year
*/
function comicpost_user_ratings_list( $atts, $content = null ){
	extract(shortcode_atts(array(
		'from' => '1 year ago',
		'to'   => 'tomorrow',
		'comic' => 'thumbnail',
		'dummy' => false
	), $atts));
	$options = get_option('comicpost_options');
	if ( $options['rating_system'] == 'stars' && is_user_logged_in() ){
		global $current_user;
		$args = array(
			'user_id' => $current_user->ID,
			'status'  => 'approve',
			'date_query' => array(
				'after' => $from,
				'before' => $to,
				'inclusive' => true
			)
		);
		$comments = get_comments( $args );
		if ( $comments ){
			$content = '<h2 class="user-ratings-title">Comics you rated:</h2><ul class="user-ratings-list">';
			foreach( $comments as $comment ){
				if (get_comment_meta( $comment->comment_ID, 'rating', true )){
					$stars = get_comment_meta( $comment->comment_ID, 'rating');
					if ($comic != 'none' && has_post_thumbnail( $comment->comment_post_ID )){
						$image = wp_get_attachment_image_src( get_post_thumbnail_id( $comment->comment_post_ID ), $comic );
						$img_html = '<img src="'.$image[0].'" width="'.$image[1].'" height="'.$image[2].'"/>';
					} else {
						$img_html = '';
					}
					$content .= '<li>'.$img_html.'<span class="user-stars">'.$stars[0].'</span>: <a href="'.get_permalink( $comment->comment_post_ID ).'">'.get_the_title( $comment->comment_post_ID ).'</a></li>';
				}
			}
			$content .= '</ul>';
		} else {
			if ( $dummy ){
				if ($dummy === 'placeholder') { $restyle = ' placeholders';} else { $restyle = '';}
				$content = '<h2 class="user-ratings-title empty'.$restyle.'">Comics you rated:</h2><ul class="user-ratings-list empty'.$restyle.'">
				<li><span class="thumbnail"></span><span class="user-stars"></span><span class="title"></span></li>
				<li><span class="thumbnail"></span><span class="user-stars"></span><span class="title"></span></li>
				<li><span class="thumbnail"></span><span class="user-stars"></span><span class="title"></span></li>
				</ul>';
			}
		}
	}
	return $content;
}

function comicpost_user_comments_list( $atts, $content = null ){
	extract(shortcode_atts(array(
		'from' => '1 year ago',
		'to'   => 'tomorrow',
		'dummy' => false
	), $atts));
	if ( is_user_logged_in() ){
		global $current_user;
		$args = array(
			'user_id' => $current_user->ID,
			'status'  => 'approve',
			'date_query' => array(
				'after'  => $from,
				'before' => $to,
				'inclusive' => true
			)
		);
		$comments = get_comments( $args );
		if ($comments){
			$content = '<h2 class="user-comments-title">Your Comments</h2><ul class="user-comments-list">';
			foreach( $comments as $comment ){
				$content .= '<li><a href="'.get_comment_link( $comment->comment_ID ).'">'.get_the_title( $comment->comment_post_ID ).'</a></li>';
			}
			$content .= '</ul>';
		} else {
			if ( $dummy ){
				if ($dummy === 'placeholder'){ $restyle = ' placeholders'; } else { $restyle = ''; }
				$content = '<h2 class="user-comments-title empty'.$restyle.'">Your Comments</h2><ul class="user-comments-list empty'.$restyle.'">
				<li><span class="title"></span></li>
				<li><span class="title"></span></li>
				<li><span class="title"></span></li>
				<li><span class="title"></span></li>
				<li><span class="title"></span></li>
				</ul>';
			}
		}
	}
	return $content;
}

// Polyfill for ClassicPress 1.x
if (!function_exists('wp_unique_id')){
	function wp_unique_id( $prefix = '' ) {
		static $id_counter = 0;
		return $prefix . (string) ++$id_counter;
	};
}