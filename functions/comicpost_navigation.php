<?php
/*	ComicPost Navigation Functions
	==============================
	The default setting is for "Previous" and "Next" to traverse
	chapters when you reach the beginning or end or a chapter,
	respectively, it will go to the last post of the previous chapter
	or the first post of the next chapter.  If you would like to
	restrict navigation to only the current chapter un-tick the
	"Traverse Chapters" box in the plugin options. 
	
	Ideally a comic should ONLY be posted in one chapter, but 
	if it is not, for example if it is posted in a sub-sub-chapter
	the navigation functions will *try* to sort out which posts
	are the "First In Chapter" and "Last in Chapter" of that
	sub-sub-chapter level. Try not to post a comic in more than
	one hierarchy either because it will pick whichever is the
	lowest depth chapter, and if it is in two sub-chapters at 
	the same depth it will select whichever one is first in the
	array of assigned chapters. Which may not be the one you
	intended. Therefore try to only put comics in ONE chapter!
	
	If you don't want to show some navigation buttons you should
	hide them with your theme stylesheet. If you don't want text
	links you need to style them in your theme stylesheet.
*/

// Utility function to get lowest level chapter in a given array of hierachical chapters
function comicpost_get_lowest_chapter($terms){
	if (!is_array($terms) || empty($terms) ){
		return false;
	}
	$filter = function($terms) use (&$filter) {
		$return_terms = array();
		$term_ids = array();
		foreach ($terms as $t){
			$term_ids[] = $t->term_id;
		}
		foreach ( $terms as $t){
			if($t->parent == false || !in_array($t->parent, $term_ids) ){
				// remove this item from array
			} else {
				$return_terms[] = $t;
			}
		}
		if (count($return_terms) ){
			return $filter($return_terms);
		} else {
			return $terms;
		}
	};
	return $filter($terms);
}
// Utility function to return an array of the entire hierarchy to which a post belongs
function comicpost_get_chapter_hierarchy($post_id){
	$hierarchy = array();
	$terms = wp_get_object_terms( $post_id, 'chapters');
	foreach ( $terms as $term ){
		$ancestors = get_ancestors( $term->term_id, 'chapters' );
		if (!empty($ancestors)){
			foreach( $ancestors as $ancestor ){
				$story = get_term( $ancestor, 'chapters');
				$hierarchy[] = $story->slug;
			}
		} else { // it is the top-level "title" term
			$hierarchy[] = $term->slug;
		}
	}
	// array
	return $hierarchy;
}
// Utility function to check if two posts are part of the same hierarchy or not
function comicpost_check_adjacent_hierarchy( $current_post, $adjacent_post ){
	$current_post_hierarchy = comicpost_get_chapter_hierarchy( $current_post );
	$adjacent_post_hierarchy= comicpost_get_chapter_hierarchy( $adjacent_post);
	$shared_hierarchy = false;
	foreach( $current_post_hierarchy as $current_term ){
		foreach( $adjacent_post_hierarchy as $adjacent_term ){
			if ( $current_term == $adjacent_term ){
				$shared_hierarchy = true;
			}
		}
	}
	// boolean true | false
	return $shared_hierarchy;
}

function comicpost_get_first_comic_in_title(){
	global $post;
	$hierarchy = comicpost_get_chapter_hierarchy($post->ID);
	$title = end($hierarchy);
	$term  = get_term_by('slug', $title, 'chapters');
	return comicpost_get_terminal_post_of_chapter($term->term_id, true);
}

function comicpost_get_first_comic($in_chapter = false) {
	global $post;
	$current_chapter = comicpost_get_lowest_chapter( get_the_terms($post->ID, 'chapters') );
	$current_chapter_id = 0;
	if (is_array($current_chapter) && $in_chapter) {
		$current_chapter = reset($current_chapter);
		$current_chapter_id = $current_chapter->term_id;
	}
	return comicpost_get_terminal_post_of_chapter($current_chapter_id, true);
}

function comicpost_get_first_comic_permalink($cross_titles = false) {
	if ($cross_titles){
		$terminal = comicpost_get_first_comic(false);
	} else {
		$terminal = comicpost_get_first_comic_in_title();
	}
	return !empty($terminal) ? get_permalink($terminal->ID) : false;
}

function comicpost_get_first_comic_in_chapter_permalink() {
	$terminal = comicpost_get_first_comic(true);
	return !empty($terminal) ? get_permalink($terminal->ID) : false;
}

function comicpost_get_last_comic_in_title(){
	global $post;
	$hierarchy = comicpost_get_chapter_hierarchy($post->ID);
	$title = end($hierarchy);
	$term = get_term_by('slug', $title, 'chapters');
	return comicpost_get_terminal_post_of_chapter($term->term_id, false);
}

function comicpost_get_last_comic($in_chapter = false) {
	global $post;
	$current_chapter = comicpost_get_lowest_chapter( get_the_terms($post->ID, 'chapters') );
	$current_chapter_id = 0;
	if (is_array($current_chapter) && $in_chapter) {
		$current_chapter = reset($current_chapter); 
		$current_chapter_id = $current_chapter->term_id;
	}	
	return comicpost_get_terminal_post_of_chapter($current_chapter_id, false);
}

function comicpost_get_last_comic_permalink($cross_titles = false) {
	if ($cross_titles){
		$terminal = comicpost_get_last_comic(false);
	} else {
		$terminal = comicpost_get_last_comic_in_title();
	}
	return !empty($terminal) ? get_permalink($terminal->ID) : false;
}

function comicpost_get_last_comic_in_chapter_permalink() {
	$terminal = comicpost_get_last_comic(true);
	return !empty($terminal) ? get_permalink($terminal->ID) : false;
}

// Get Previous Comic OBJECT either IN chapter or ACROSS chapters
function comicpost_get_previous_comic($in_chapter = false) {
	return get_adjacent_post( $in_chapter, '', true, 'chapters');
}

// Gets LINK to Previous Comic ACROSS Chapters
function comicpost_get_previous_comic_permalink($cross_titles = false) {
	global $post;
	$prev_comic = get_adjacent_post( false, '', true, 'chapters');
	if (is_object($prev_comic) && isset($prev_comic->ID)) {
		if ($cross_titles || comicpost_check_adjacent_hierarchy($post->ID,$prev_comic->ID)){
			return get_permalink($prev_comic->ID);
		}
	}
	return false;
}

// Gets LINK to Previous Comic IN Chapter
function comicpost_get_previous_comic_in_chapter_permalink() {
	$prev_comic = get_adjacent_post( true, '', true, 'chapters');
	if (is_object($prev_comic) && isset($prev_comic->ID)) {
		return get_permalink($prev_comic->ID);
	}
	return false;
}

// Gets Next Comic OBJECT either IN chapter or ACROSS chapters
function comicpost_get_next_comic($in_chapter = false) {
	return get_adjacent_post( $in_chapter, '', false, 'chapters');
}
// Gets LINK to Next Comic ACROSS Chapters
function comicpost_get_next_comic_permalink($cross_titles = false) {
	global $post;
	$next_comic = get_adjacent_post( false, '', false, 'chapters');
	if (is_object($next_comic) && isset($next_comic->ID)) {
		if ($cross_titles || comicpost_check_adjacent_hierarchy($post->ID,$next_comic->ID)){
			return get_permalink($next_comic->ID);
		}
	}
	return false;
}
// Gets LINK Next Comic IN Chapter
function comicpost_get_next_comic_in_chapter_permalink() {
	$next_comic = get_adjacent_post( true, '', false, 'chapters');
	if (is_object($next_comic) && isset($next_comic->ID)) {
		return get_permalink($next_comic->ID);
	}
	return false;
}

// 0 means get the first of them all, no matter chapter, otherwise 0 = this chapter.
function comicpost_get_terminal_post_of_chapter($chapterID = 0, $first = true) {
	
	$sortOrder = $first ? "asc" : "desc";	
	
	if (!empty($chapterID)) {
		$chapter = get_term_by('id', $chapterID, 'chapters');
		$chapter_slug = $chapter->slug;
		$args = array(
				'chapters' => $chapter_slug,
				'order' => $sortOrder,
				'posts_per_page' => 1,
				'post_type' => 'comic'
				);
	} else {
		$args = array(
				'order' => $sortOrder,
				'posts_per_page' => 1,
				'post_type' => 'comic'
				);
	}
	
	$terminalComicQuery = new WP_Query($args);
	
	$terminalPost = false;
	if ($terminalComicQuery->have_posts()) {
		$terminalPost = reset($terminalComicQuery->posts);
	}
	return $terminalPost;
}

/* Main Comic Navigation Display */

function comicpost_display_comic_navigation() {
	global $post, $wp_query;
	$options = get_option('comicpost_options');
	if ($options['navigation_style'] != 'none'){
		$nav_style = ' '.$options['navigation_style'];
	} else {
		$nav_style = ' theme';
	};
	if ($options['comic_navigation'] == 'cross' || $options['comic_navigation'] == 'ignore'){
		$cross = true;
	} else {
		$cross = false;
	}
	// Get Nav Links
	$first_comic  = comicpost_get_first_comic_permalink($cross);
	  $prev_chap = comicpost_get_previous_comic_permalink($cross);
		$first_in_chap = comicpost_get_first_comic_in_chapter_permalink();
		$prev_in_chap  = comicpost_get_previous_comic_in_chapter_permalink();	
		$next_in_chap  = comicpost_get_next_comic_in_chapter_permalink();
		$last_in_chap = comicpost_get_last_comic_in_chapter_permalink();
	  $next_chap = comicpost_get_next_comic_permalink($cross);
	$last_comic = comicpost_get_last_comic_permalink($cross);
	
	$first_text = __('&lsaquo;&lsaquo; Oldest','comicpost');
		$prev_chap_text = __('&lsaquo; PREV', 'comicpost');
			$first_chap_text = __('&lsaquo;&lsaquo; First in Chap', 'comicpost');
			$prev_text  = __('&lsaquo; Prev', 'comicpost');
			$next_text  = __('Next &rsaquo;', 'comicpost');
			$last_chap_text  = __('Last in Chap &rsaquo;&rsaquo;', 'comicpost');
		$next_chap_text = __('NEXT &rsaquo;', 'comicpost');
	$last_text = __('Newest &rsaquo;&rsaquo;', 'comicpost');

?>
	<div class="comic-nav<?php echo $nav_style; ?>">
		<?php 
		if ( get_permalink() != $first_comic ) { ?><a href="<?php echo $first_comic ?>" class="comic-nav-base comic-nav-oldest<?php if ( get_permalink() == $first_comic ) { ?> comic-nav-inactive<?php } ?>"><?php echo $first_text; ?></a><?php } else { echo '<span class="comic-nav-base comic-nav-oldest comic-nav-void">'.$first_text.'</span>'; }
		if ( get_permalink() != $first_in_chap){ ?><a href="<?php echo $first_in_chap ?>" class="comic-nav-base comic-nav-first-chap<?php if ( get_permalink() == $first_in_chap ) { ?> comic-nav-inactive<?php } ?>"><?php echo $first_chap_text; ?></a><?php } else { echo '<span class="comic-nav-base comic-nav-first-chap comic-nav-void">'.$first_chap_text.'</span>'; }
		$prev_link = '';
		if ($options['comic_navigation'] == 'chapter'){
			// previous stays within the current chapter
			$prev_link = $prev_in_chap;
		} else if ( $options['comic_navigation'] == 'ignore'){
			// allow Previous to navigate to previous cmic by date ignoring chapters and titles
			if ($prev_chap != $prev_in_chap) {
				$prev_link = $prev_chap;
			} else if ($prev_in_chap){
				$prev_link = $prev_in_chap;
			} else {
				$prev_link = '';
			}		
		} else {
			// Traverse Chapters or Cross Titles (which it does is handled in "Get Nav Links" above)
			// allow Previous to navigate to last post of previous chapter
			if (empty($prev_in_chap) && !empty($prev_chap) ){
				$prev_link = $prev_chap;	
			} else {
				$prev_link = $prev_in_chap;
			}			
		}
		if ($prev_link) { ?><a href="<?php echo $prev_link ?>" class="comic-nav-base comic-nav-previous<?php if (!$prev_link) { ?> comic-nav-inactive<?php } ?>"><?php echo $prev_text; ?></a><?php } else { echo '<span class="comic-nav-base comic-nav-previous comic-nav-void ">'.$prev_text.'</span>'; }
		$next_link = '';
		if ($options['comic_navigation'] == 'chapter'){
			// next stays within the current chapter
			$next_link = $next_in_chap;
		} else if ( $options['comic_navigation'] == 'ignore'){
			// allow Next to navigate to next post by date, ignoring chapters and titles
			if ($next_chap != $next_in_chap) {
				$next_link = $next_chap;
			} else if ($next_in_chap){
				$next_link = $next_in_chap;
			} else {
				$next_link = '';
			}			
		} else {
			// Traverse Chapters or Cross Titles (which it does is handled in "Get Nav Links" above)
			// allow Next to navigate to the first post of next chapter
			if (empty($next_in_chap) && !empty($next_chap) ){
				$next_link = $next_chap;
			} else {
				$next_link = $next_in_chap;
			}		
		}
		if ($next_link) { ?><a href="<?php echo $next_link ?>" class="comic-nav-base comic-nav-next<?php if (!$next_link) { ?> comic-nav-inactive<?php } ?>"><?php echo $next_text; ?></a><?php } else { echo '<span class="comic-nav-base comic-nav-next comic-nav-void ">'.$next_text.'</span>'; }

		if ( get_permalink() != $last_in_chap ) { ?><a href="<?php echo $last_in_chap ?>" class="comic-nav-base comic-nav-last-chap<?php if ( get_permalink() == $last_in_chap ) { ?> comic-nav-inactive<?php } ?>"><?php echo $last_chap_text; ?></a><?php } else { echo '<span class="comic-nav-base comic-nav-last-chap comic-nav-void ">'.$last_chap_text.'</span>'; }
		if ( get_permalink() != $last_comic ) { ?><a href="<?php echo $last_comic ?>" class="comic-nav-base comic-nav-newest<?php if ( get_permalink() == $last_comic ) { ?> comic-nav-inactive<?php } ?>"><?php echo $last_text; ?></a><?php } else { echo '<span class="comic-nav-base comic-nav-newest comic-nav-void ">'.$last_text.'</span>'; } ?>
	</div>
		
	<?php
}
