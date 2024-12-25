<?php
/*
	ComicPost Plugin
	OPTIONS
	on the Admin back-end.
*/

function comicpost_check_for_gd(){
	global $pagenow;
	// only run this check on the ComicPost Options page!
	if ($pagenow=="edit.php" && get_post_type() == "comic" && isset( $_GET['page'] ) && 'comicpost_settings' == $_GET['page']){
		if (!extension_loaded('gd')){
			echo '<div class="notice notice-error is-dismissible"><p>PHP Graphics Draw (GD) Extension not found!  You will not be able to use the ComicPost Watermark features.</div>';
		}
	};
}
add_action( 'admin_notices', 'comicpost_check_for_gd' );

// enqueue admin scripts
function comicpost_load_admin_scripts() {
    wp_enqueue_script('media-upload');
    wp_enqueue_script('thickbox');
    wp_enqueue_style('thickbox');
}
add_action( 'admin_enqueue_scripts', 'comicpost_load_admin_scripts' );

// Register new Options panel.
$comicpost_options_panel_args = [
	'submenu'		  => 'edit.php?post_type=comic',
    'title'           => 'Options',					// Menu and Page Title
    'option_name'     => 'comicpost_options',	// db options entry name
    'slug'            => 'comicpost_settings',// options page slug
    'user_capability' => 'manage_options',
    'tabs'			  => [
    	'tab-1' => esc_html__( 'General Settings', 'comicpost' ),
    	'tab-2' => esc_html__( 'Watermarking', 'comicpost' ),
    	'tab-3' => esc_html__( 'Content Restriction', 'comicpost'),
    	'tab-4' => esc_html__( 'Social Media', 'comicpost'),
    	'tab-5' => esc_html__( 'Admin Settings', 'comicpost' ),
    	'tab-6' => esc_html__( 'Shortcodes', 'comicpost' ),
    ],
];

$comicpost_options_panel_settings = [
	// GENERAL SETTINGS
	'comic_navigation' => [
		'label'		 => esc_html__( 'Comic Navigation', 'comicpost' ),
		'type'		 => 'select',
		'description'=> 'Determines how "Previous" and "Next" buttons work.<br><b>Within Chapter:</b> goes to the previous/next comic within the current chapter (single chapter navigation). "Oldest/Newest" are limited to the current title.<br>
<b>Traverse Chapters</b>: will go to the "Previous/Next" comic in the current chapter until it reaches either the start or end of that chapter, then it will jump to the next comic (by date relative to the current comic) in an adjacent chapter <i>in the same title hierarchy.</i> "Oldest/Newest" are also limited to the current title.<br>
<b>Cross Titles</b>: will go to the previous/next comic in the current chapter until it reaches either the start or end of that chapter, then it will jump to the next comic (by date relative to the current comic) in an adjacent chapter <i>even if it is in a different title hierarchy.</i> "Oldest/Newest" are first and last comics posted from any title.<br>
<b>Ignore Chapters</b>" will go to the previous/next post relative to the date of the current comic, ignoring the chapters/titles (pure date navigation). "Oldest/Newest" are first and lst comics posted from any title.<br><br>
<em>"Traverse Chapters" is the default because it is probably what most people want. "Ignore Chapters" can bounce between different titles on every previous/next (because it is purely by date posted). If you use "Chapter Only" make sure to provide readers with some way to get to other chapters, for example put the chapter dropdown shortcode in one of the comic widget spaces or in the post body. "Cross Titles" stays within a chapter hierarchy until it runs out of posts in that hierarchy, then it will jump to a post in an adjacent chapter in another hierarchy.</em>',
		'choices'	 => [
			'chapter'	=> esc_html__( 'Within Chapter', 'comicpost' ),
			'traverse'  => esc_html__( 'Traverse Chapters', 'comicpost' ),
			'cross'		=> esc_html__( 'Cross Titles', 'comicpost' ),
			'ignore'    => esc_html__( 'Ignore Chapters', 'comicpost' ),	
		],
		'tab'		 => 'tab-1',
	],
	'navigation_location'  => [
		'label'		 => esc_html__( 'Navigation Location', 'comicpost' ),
		'type'		 => 'select',
		'description' => 'Select where Comic Navigation controls should appear.',
		'choices'	 => [
			'above'	 => esc_html__( 'Above Comic', 'comicpost' ),
			'below'  => esc_html__( 'Below Comic', 'comicpost' ),
		],
		'tab'	=> 'tab-1',
	],
	'navigation_style'	 => [
		'label'		=> esc_html__( 'Navigation Style', 'comicpost' ),
		'type'		=> 'select',
		'description' => 'Select the appearance of the navigation controls. "None" assumes you will be styling these in your theme. The others are from Comic Easel and included if you are switching from that plugin or will not be styling the buttons in your theme.',
		'choices'	=> [
			'none'		=> esc_html__( 'None/Theme', 'comicpost' ),
			'box'		=> esc_html__( 'Box', 'comicpost' ),
			'comical' 	=> esc_html__( 'Comical', 'comicpost' ),
			'npc'		=> esc_html__( 'NPC', 'comicpost' ),
			'scifi' 	=> esc_html__( 'Sci Fi', 'comicpost' ),
			'silver' 	=> esc_html__( 'Silver', 'comicpost' ),
		],
		'tab'	=> 'tab-1',
	],
	'keyboard_navigation' => [
		'label'		 => esc_html__( 'Keyboard Navigation', 'comicpost' ),
		'type'		 => 'checkbox',
		'description' => 'If enabled will load a script allowing keyboard to be used to trigger comic navigation. The controls are:<br>&larr; = Previous<br>&rarr; = Next<br>&uarr; = First in Chapter ("Page-Up" also)<br>&darr; = Last in Chapter ("Page-Down" also)<br>"Home" = Oldest Comic<br>"End" = Newest Comic',
		'tab'	=> 'tab-1',
	],
    'enable_comic_widgets' => [
        'label'       => esc_html__( 'Widget Spaces', 'comicpost' ),
        'type'        => 'checkbox',
        'description' => 'Tick box to enable the widget spaces around the image on comic posts. They will still only appear if you actually put something in the widget space.',
        'tab'		  => 'tab-1',
    ],
    'set_comicpost_size' => [
        'label'       => esc_html__( 'Comic Size', 'comicpost' ),
        'type'        => 'select',
        'description' => 'Normally the comic image will be whatever size the theme uses for displaying a Featured Image. You can override this here, but be aware theme stylesheets may still limit or alter the display size.',
        'choices'     => [
            'theme' => esc_html__( 'theme', 'comicpost' ),
            'medium' => esc_html__( 'Medium', 'comicpost' ),
            'medium_large' => esc_html__( 'Medium Large', 'comicpost'),
            'large' => esc_html__( 'Large', 'comicpost' ),
            'full' => esc_html__( 'Full Size', 'comicpost' ),
        ],
        'tab'		  => 'tab-1',
    ],
    'archive_thumb_links' => [
    	'label'		 => esc_html__( 'Archive Thumbnail Links', 'comicpost' ),
    	'type'       => 'checkbox',
    	'description'=> 'Tick this box to make sure comic thumbnail images in archives are also links to the comic post instead of just the comic post titles.',
    	'tab'		 => 'tab-1',
    ],
    'archive_post_count'  => [
    	'label'		 => esc_html__( 'Archive Post Count', 'comicpost' ),
    	'type'       => 'number',
    	'description'=> 'Enter the number of comic posts you would like to appear per page in the comic archives. Ideally this should be how many comics you usually post in the smallest chapter division. For example if you post 20 comics per chapter set this to "20." Archive results are always paginated, so if you have really long chapters set it to decent number of items per page. <i>This overrides the maximum post count under Settings &gt; Reading in the main menu, but only for comic and chapter archives.</i>',
    	'tab'		 => 'tab-1',
    ],
    'archive_views' => [
    	'label'		 => esc_html__( 'Chapter Archive Views', 'comicpost'),
    	'type'	     => 'select',
    	'description'=> '<b>Theme/None:</b> uses whatever appearance is set in the theme<br><b>Vertical View:</b> single column of just comic images.<br><b>Multiple Views:</b> adds buttons allowing the reader to switch between four different views.<br><b>Note: This requires your theme to actually show Featured Images on archive pages, will NOT work with every theme, and may require additional tweaks to your theme stylesheet.</b> It tends to work better with older themes from before WordPress introduced blocks, and you may need to enable "Archive Thumbnail Links" above to allow navigating to the individual comic posts.',
    	'choices'	 => [
    		'theme'	   => esc_html__( 'Theme/None', 'comicpost' ),
    		'vertical' => esc_html__( 'Vertical View', 'comicpost' ),
    		'multiple' => esc_html__( 'Multiple Views', 'comicpost'),
    	],
    	'tab'	=> 'tab-1',
    ],
    
    
   // WATERMARKING   
    'watermark_head' => [
    	'label'		  => esc_html__( 'WATERMARKING COMICS', 'comicpost' ),
    	'type'		  => 'section',
    	'description' => 'Watermarking your comic images is one of the most effective ways to protect them because information about the provenance and ownership of the image travels with it whereever it may end up. Your unique watermark placed consistently on your images will make it obvious an AI was trained on your images when it reproduces a garbled version of the watermark as well. <b>It is highly recommended you watermark your comic images.</b>',
    	'tab'		  => 'tab-2',
    ], 
    'watermark_method' => [
    	'label'		=> esc_html__( 'Watermark Comics', 'comicpost' ),
    	'type'		=> 'select',
    	'description' => 'Automatically apply a watermark to each comic image as it is uploaded on a comic post.',
        'choices'     => [
            'none'     => esc_html__( 'NONE', 'comicpost' ),
            'tile' 	   => esc_html__( 'Tile', 'comicpost' ),
            'center'   => esc_html__( 'Center', 'comicpost' ),
        ],
        'tab'		  => 'tab-2',
    ],
	'watermark_tile_size' => [
			'label'		=> esc_html__( 'Watermark Tile Size', 'comicpost' ),
			'type'		=> 'number',
			'min'		=> '50',
			'max'		=> '500',
			'description' => 'If you selected "Tile" above this is the size for the tile repeated over the image. Smaller sizes may be too busy or illegible. 50-500 allowed, default is watermark image size.',
			'tab'		=> 'tab-2',
		],
    'watermark_source' => [
    	'label'		=> esc_html__( 'Watermark Image Source', 'comicpost' ),
    	'type'		=> 'select',
    	'description' => 'Select Watermark Image Source. If using a custom image:<br>* It must be placed in the "watermark" folder inside your "uploads" directory<br>* It must be a PNG image named "watermark.png"<br>* The dimensions must be SQUARE<br>It can be any size but 300x300 pixels is recommended.',
    	'choices'	=> [
    		'generate'	=> esc_html__( 'Generated' , 'comicpost' ),
    		'custom'    => esc_html__( 'Custom Image', 'comicpost'),
    	],
    	'tab'		=> 'tab-2',
    ],
    'custom_watermark_file' => [
    	'label'		=> esc_html__( 'Choose Custom Watermark', 'comicpost' ),
    	'type'		=> 'file',
    	'description' => 'If you selected "Custom" for Watermark Image you need to upload your custom watermark PNG image and SAVE SETTINGS. Then come back to this page and press the "Create Watermark File" button.',
    	'tab'	=> 'tab-2',
    ],
    'generate_watermark_png' => [
    	'label'		=> esc_html__( '', 'comicpost' ),
    	'type'		=> 'button',
    	'buttontext'=> 'CREATE WATERMARK FILE',
    	'description' => 'This will create a NEW watermark PNG file based on the "Image Source" setting above. It will either use your "Custom Image" or it will use the "Generated Watermakr" settings below.  <b>WARNING: This will <em>replace</em> any existing watermark.png file and cannot be undone.</b>',
    	'tab'	=> 'tab-2',
    ],
    'delete_watermark_png' => [
    	'label'		=> esc_html__( '', 'comicpost' ),
    	'type'	    => 'button',
    	'buttontext'=> 'DELETE WATERMARK FILE',
    	'description' => 'Delete existing Watermark PNG file.This button will delete any existing watermark image file.  <b>THIS CANNOT BE UNDONE!</b>',
    	'tab'	=> 'tab-2',
    ],
    'watermark_opacity' =>[
    	'label' 	  => esc_html__( 'Watermark Opacity', 'comicpost'), 
        'type'        => 'range',
        'description' => 'Watermark overlay opacity. Default is 50%.',
        'min'		  => '.10',
        'max'		  => '1',
        'step'		  => '.05',
        'list'		  => [
        	'.10' => esc_html__('10%', 'comicpost'),
        	'.25' => esc_html__('25%', 'comicpost'),
        	'.50' => esc_html__('50%', 'comicpost'),
        	'.75' => esc_html__('75%', 'comicpost'),
        	'1'	  => esc_html__('100%', 'comicpost'),
        ],
        'tab'		  => 'tab-2',
    ],	
    'generated_watermark_head' => [
    	'label'		  => esc_html__( '', 'comicpost' ),
    	'type'		  => 'section',
    	'description' => '<b>GENERATED WATERMARK SETTINGS</b>',
    	'tab'		  => 'tab-2',
    ],
	'watermark_image_size' => [
			'label'		=> esc_html__( 'Watermark Image Size', 'comicpost' ),
			'type'		=> 'number',
			'min'		=> '100',
			'max'		=> '500',
			'description' => 'Height/Width in pixels for watermark image. Smaller sizes may illegible, larger sizes may be to sparse or slow to process. 100-500 allowed, 300 pixels is the default.',
			'tab'		=> 'tab-2',
		],
    'watermark_text' => [
    	'label' => esc_html__( 'Watermark Text', 'comicpost'),
    	'type'	=> 'text',
    	'description' => 'Enter the text to display on the GENERATED Watermark Image. Default is blog name.',
    	'tab'	=> 'tab-2',
    ],
    'watermark_font'    =>[
    	'label'		=> esc_html__( 'Watermark Font', 'comicpost' ),
    	'type'		=> 'select',
    	'description' => 'Choose a font for your generated watermark text.',
    	'choices'	  => [
			'black-ops-one'	  => esc_html__( 'Black-Ops' , 'comicpost' ),
			'cascadia-code'   => esc_html__( 'Cascadia Code', 'comicpost' ),
			'liberation-sans' => esc_html__( 'Liberation Sans', 'comicpost'),
			'sarina'		  => esc_html__( 'Sarina', 'comicpost' ),
			'tiza'			  => esc_html__( 'Tiza', 'comicpost' ),
			'unitblock-font'  => esc_html__( 'Unitblock', 'comicpost'),
    	],
    	'tab'	=> 'tab-2',
    
    ],
    'watermark_orientation' => [
    	'label'	   	=> esc_html__( 'Text Orientation', 'comicpost' ),
    	'type'		=> 'select',
    	'description' => 'Select which way generated text will appear on Watermark image. It will be centered and scaled to fit.',
    	'choices'	  => [
    		'diagonal'		=> esc_html__( 'Diagonal Text', 'comicpost'),
    		'horizontal'	=> esc_html__( 'Horizontal Text', 'comicpost'),
    	],
    	'tab'	=> 'tab-2',
    ],

    'watermark_clean_copy' => [
    	'label'		=> esc_html__( 'Keep Clean Copy', 'comicpost' ),
    	'type'		=> 'checkbox',
    	'description' => 'Attempt to create a CLEAN copy on the server of every watermarked comic image. This copy will <b>NOT</b>:<br>* have the watermark on it.<br>* have any thumbnails automatically created for it<br>* appear in your Media Library<br>* have any links to it on your site.<br>* use lazy loading<br>* use source sets<br><br>It is only intended for use with the options below.<br><b>It is strongly recommended you NOT enable this feature.</b>',
    	'tab'	=> 'tab-2',
    ],
    'watermark_clean_suffix' => [
    	'label'		=> esc_html__( 'Clean Copy Suffix', 'comicpost' ),
    	'type'		=> 'text',
    	'description' => 'To make it harder for anyone to find the unlisted clean copies of your comic images a unique suffix is added to them. When ComicPost was activated a random suffix was created for you. But you can change this to whatever you like.<br><b>NOTE: If you change this suffix later, any existing clean copies will no longer be able to be found by ComicPost</b>',
    	'tab'		=> 'tab-2',
    ],
    'show_clean_to_loggedin' => [
    	'label'		=> esc_html__( 'Show Clean Copies', 'comicpost' ),
    	'type'		=> 'checkbox',
    	'description' => 'Show clean copies to logged-in readers, and Watermarked version to public. (if no clean copy can be found it will show the watermarked version even to logged-in readers).<br><b>The check for the clean copy WILL slow down loading comics and archives.<br>It is strongly recommended you do NOT enable this feature.</b>',
    	'tab'	=> 'tab-2',
],
    
 	// COMIC SECURITY   
    'comic_security_head' => [
    	'label'		  => esc_html__( 'COMIC SECURITY FEATURES', 'comicpost' ),
    	'type'		  => 'section',
    	'description' => 'The following features are intended to prevent people and some bots from easily downloading, scraping, indexing, or printing your comic images. These are not foolproof and they come with tradeoffs in appearance and performance. They should really only be applied to PUBLICLY ACCESSIBLE comic images. Your archives should, ideally, be hidden behind a login or paywall instead. Though they attempt to preserve alt-text consider that these features may adversely affect site accessibility for people with disabilities.  Note that, apart from the encoding options below, the others rely on stylesheets being loaded and will not prevent bots from reading your image URLs.<br><b>WARNING: The settings below ONLY apply to Comic Posts. If you attach a comic image to a regular post, page, or some other custom post type none of the following settings will be applied to that image.</b>',
    	'tab'		  => 'tab-3',
    ],
    'add_noai_meta'		 => [
    	'label'		  => esc_html__( 'Advanced Meta Tags', 'comicpost' ),
    	'type'		  => 'checkbox',
    	'description' => '<b>Adds the "NoAI" and "NoImageAI" advanced meta tags</b> to the HEAD section of every page of your website, which are intended to signal to web crawlers, bots, and scrapers that you do not wish your content to be used to train AI models. Please note that whether or not a bot honors this or not is voluntary and some AI companies have already explicitly said they will not. You should also put a more detailed notice in your Terms of Service page <i>(see "Shortcodes" section for suggested text)</i>',
    	'tab'		  => 'tab-3',
    ],
    'comics_under_glass' => [
    	'label'		  => esc_html__( 'Comics Under Glass', 'comicpost' ),
    	'type'		  => 'checkbox',
    	'description' => 'Moves comic from image tag to background image and adds an invisible layer over the comic that prevents right-clicking or dragging the image out of window to download.',
    	'tab'		  => 'tab-3',
    ],
    'discourage_printing' => [
    	'label'		 => esc_html__('Printing Comics', 'comicpost' ),
    	'type'       => 'select',
    	'description' => '<i>(requires Comics Under Glass above)</i> "Watermark Prints" overlays a generated SVG image on any print of your comic page. Printing is only possible with "background images" enabled in the print dialog, otherwise the space where the comic is will be blank.  "Disable Printing" will prevent the comic image from being printed at all.',
    	'choices'	  => [
    		'allowprint'=> esc_html__( 'Allow Printing', 'comicpost' ),
    		'fauxmark'  => esc_html__( 'Watermark Prints', 'comicpost' ),
    		'noprinting'=> esc_html__( 'Disable Printing', 'comicpost' ),
    	],
    	'tab' => 'tab-3',
    ],
    'faux_watermark_method' => [
    	'label'		=> esc_html__( '', 'comicpost' ),
    	'type'		=> 'select',
    	'description' => '<b>Print Watermark Method</b><br>Whether faux watermark should be tiled or centered.',
        'choices'     => [
            'tile' 	   => esc_html__( 'Tile', 'comicpost' ),
            'center'   => esc_html__( 'Center', 'comicpost' ),
        ],
        'tab'		  => 'tab-3',
    ], 
    'faux_watermark_text' => [
    	'label'		 => esc_html__( '', 'comicpost' ),
    	'type'		 => 'text',
    	'description'=> '<b>Print Watermark Text</b><br>Enter text to appear overlaid on watermarked prints.',
    	'tab' => 'tab-3',
    ],
    'faux_watermark_opacity' =>[
    	'label' 	  => esc_html__( '', 'comicpost'), 
        'type'        => 'range',
        'description' => 'Print Watermark SVG image overlay opacity. Default is 25%.',
        'min'		  => '.10',
        'max'		  => '1',
        'step'		  => '.05',
        'list'		  => [
        	'.10' => esc_html__('10%', 'comicpost'),
        	'.25' => esc_html__('25%', 'comicpost'),
        	'.50' => esc_html__('50%', 'comicpost'),
        	'.75' => esc_html__('75%', 'comicpost'),
        	'1'	  => esc_html__('100%', 'comicpost'),
        ],
        'tab'		  => 'tab-3',
    ],   
    'encode_comic_urls' => [
    	'label'		  => esc_html__( 'Encode Comic URLs', 'comicpost' ),
    	'type'		  => 'checkbox',
    	'description' => 'Enabling this option will encode and obfuscate the URLs to your comic image files. Even bots that crawl or scrape your site HTML code will not be able to find them. Those that can fully render the page, however, could still capture an image of your comic (but you can also enable the faux watermarking feature). Decoding the URLs requires JavaScript. Note that, by design, this will <em>disable</em> lazy loading and source sets.<br><b>WARNING: This feature can adversely affect website performance and requires javascript!</b>',
    	'tab'		  => 'tab-3',
    ],
    'apply_to_archives' => [
    	'label'		  => esc_html__( 'Apply To Archives', 'comicpost'),
    	'type'        => 'checkbox',
    	'description' => 'Apply Comic Security options to Comic Archive and Search Results thumbnail images too.<br><b>WARNING: This feature may adversely affect website performance!</b>',
    	'tab'		  => 'tab-3',
    ],
    'apply_to_public' => [
    	'label'	      => esc_html__( 'Apply to Public Posts ONLY', 'comicpost'),
    	'type'		  => 'checkbox',
    	'description' => 'Apply Comic Security options above ONLY to public comic posts. Do NOT apply them to images being viewed by users who are logged into your website.',
    	'tab'		  => 'tab-3',
    ],
    'omit_from_galleries' => [
    	'label'		 => esc_html__( 'Omit Comics From Galleries', 'comicpost'),
    	'type'		 => 'checkbox',
    	'description'=> 'Tick this box to omit comic images attached to comic posts from being included in image galleries. You should only use this if you are not watermarking your comic images.<br><b>WARNING: This intercepts and alters gallery output, a simpler solution is to make sure you never add un-watermarked comic images to a Gallery</b>',
    	'tab'		  => 'tab-3',
    ],
    'hide_old_comics' => [
    	'label'		  => esc_html__( 'Hide Old Comics', 'comicpost'),
    	'type'	      => 'select',
    	'description' => 'Hide comics older than selected time period from PUBLIC view. Users who are logged in will still be able to see them. This ONLY hides the comic image, not the entire Comic Post. Check the box below to do that. Alternatively you can also just hide the post TEXT content with "Secure Comic Content" below.',
    	'choices'	  => [
    		'x'		  => esc_html__('Show All', 'comicpost'),
    		'1'		  => esc_html__('1 Day', 'comicpost'),
    		'3'		  => esc_html__('3 Days', 'comicpost'),
    		'7'		  => esc_html__('1 Week', 'comicpost'),
    		'14'	  => esc_html__('2 Weeks', 'comicpost'),
    		'30'	  => esc_html__('1 Month', 'comicpost'),
    		'90'	  => esc_html__('3 Months', 'comicpost'),
    		'180'	  => esc_html__('6 Months', 'comicpost'),
    		'365'	  => esc_html__('1 Year', 'comicpost'),
    		'1095'	  => esc_html__('3 Years', 'comicpost'),
    		'1825'    => esc_html__('5 Years', 'comicpost'),
    	],
    	'tab'		  => 'tab-3',
    ],
    'hide_old_comic_posts' => [
    	'label'		=> '',
    	'type'	    => 'checkbox',
    	'description' => 'COMPLETELY hide old Comic Posts for users who are not logged in. For Comic Posts older than the timeframe set above users will see a 404 Not Found error page and old comics will be entirely filtered out of Archives and Search results. Only works if "Hide Old Comics" is enabled as well. You may want to customize your 404 page in your theme so public/guest users know <em>why</em> the comic was not found, and offer them a chance to register to and sign up.',
    	'tab'		=> 'tab-3',
    ],
    'require_login'   => [
    	'label'		  => esc_html__( 'Require Login', 'comicpost'),
    	'type'		  => 'checkbox',
    	'description' => 'Require users be logged in to see ANY of your comic IMAGES in comic posts and archives. Note that widget spaces around the comic will still be shown.',
    	'tab'		  => 'tab-3',    
    ],
	'content_login'   => [
		'label'		  => esc_html__( 'Secure Comic Content', 'comicpost'),
		'type'		  => 'checkbox',
		'description' => 'Require users be logged in to read ANY of your comic post TEXT content, even on new comic posts.',
		'tab'		  => 'tab-3',
	],
    'shortcode_login' => [
    	'label'		  => esc_html__( 'Secure Shortcode Comics', 'comicpost'),
    	'type'        => 'checkbox',
    	'description' => 'Require user login to read comics inserted with the shortcode. <b>NOTE: You can override this in the shortcode if you want to show one anyway.</b><br>Comics inserted with the shortcode also override "Hide Old Comics" even if the box is checked to completely hide old comic <em>posts</em> it has no effect on shortcode comic <em>image</em> visibility.',
    	'tab'		  => 'tab-3',
    ],

	// SOCIAL MEDIA  
	'rating_system'		=> [
		'label'		  => esc_html__( 'Rating System', 'comicpost' ),
		'type'		  => 'select',
		'description' => 'A built-in system for rating comics. <em>This is not tied to any social media platform.</em> Choose between:<br><b>None:</b> Self-explanatory, there will be no rating system.<br><b>Five-Star Ratings:</b> When a user leaves a comment they rate the post out of five stars. The rating is tied to comment, posts will show average rating.<br><b>Post Likes Ratings:</b> users can press a "Like" button, rating is tied to post, post shows tally of all likes, nobody else knows what a user liked.',
		'choices'	  => [
			'none'	  => esc_html__('None', 'comicpost'),
			'stars'   => esc_html__('Five-Star Ratings', 'comicpost'),
			'likes'   => esc_html__('Post Like Ratings', 'comicpost'),
		],
		'tab'	=> 'tab-4',
	],
	'star_rating_required' => [
		'label'		  => esc_html__( 'Require Star Rating', 'comicpost' ),
		'type'		  => 'checkbox',
		'description' => 'If <b>Rating System</b> is set to "Five-Star Ratings" checking this box will make rating the comic required to submit a comment. Once a user has rated a comic they will not be asked to rate it again.<br><i>Requiring a rating to comment is not recommended.</i>',
		'tab'		  => 'tab-4',
	],
	'post_like_style'    => [
		'label'		  => esc_html__( 'Post Like Style', 'comicpost'),
		'type'		  => 'select',
		'description' => 'If you selected "Post Like Ratings" above this setting determines how they look. If you select "Custom" you need to style it yourself in your theme.',
		'choices'	  => [
			'custom'	     => esc_html__('Custom/Theme', 'comicpost'),
			'like-style-like' => esc_html__('Thumbs-Up', 'comicpost'),
			'like-style-love' => esc_html__('Heart', 'comicpost'),
			'like-style-star' => esc_html__('Star', 'comicpost'),
		],
		'tab'    => 'tab-4',
	
	],
	'post_like_button_text' => [
		'label'		  => esc_html__( 'Post Like Button Text', 'comicpost'),
		'type'		  => 'text',
		'description' => 'Text to appear on Like Button (default is "Like")',
		'tab'		  => 'tab-4',	
	],
	'post_unlike_button_text' => [
		'label'		  => esc_html__( 'Post Unlike Button Text', 'comicpost'),
		'type'		  => 'text',
		'description' => 'Text to appear on Unlike Button (default is "Unlike"). When someone has already liked a post they have the option to "unlike" it.',
		'tab'         => 'tab-4',
	],
	'post_liking_action' => [
		'label'		  => esc_html__( 'Post Like Action Name', 'comicpost'),
		'type'        => 'text',
		'description' => 'Text to describe having liked a post (default is "liked"). For example if you changed the button text to "Favorite" this would be "favorited" or if you changed it to "Love" this would be "loved."',
		'tab'		  => 'tab-4',
	],
    'social_media_head' => [
    	'label'		  => esc_html__( 'Description', 'comicpost' ),
    	'type'		  => 'section',
    	'description' => 'The options below will add social media sharing meta data to your website. If you are using an SEO or social media plugin it probably already does that so you may want to leave these blank/disabled. If you are not using any SEO or social media plugins and want to make it easier for readers to share your content consider enabling some or all of these options.',
    	'tab'		  => 'tab-4',
    ],  
    'facebook_meta' => [
    	'label'	    => esc_html__( 'Facebook', 'comicpost' ),
    	'type'		=> 'checkbox',
    	'description' => 'Add Facebook OpenGraph meta to &lt;HEAD&gt; tag. If you have ANY of the comic security settings enabled your comic image will not be shared as the thumbnail. If you are using an SEO optimizing plugin it will probably override this.',
    	'tab'		=> 'tab-4',   
    ],
    'fallback_thumbnail' => [
    	'label'		=> esc_html__( 'Fallback Site Image', 'comicpost' ),
    	'type'		=> 'file',
    	'description' => 'When a page or post does not have a Featured/Comic image the thumbnail shown on Meta platforms will be whatever image you select here. <em>It should be at least 512 x 512 pixels.</em> If no image is set here it will fallback to the "Site Icon" (if any).',
    	'tab'	=> 'tab-4',
    ],
    'bluesky_meta'	=> [
    	'label'		=> esc_html__( 'Bluesky', 'comicpost' ),
    	'type'		=> 'checkbox',
    	'description' => 'Add Bluesky meta to &lt;HEAD&gt; tag. If you are using any SEO optimizing plugin it will probably override this.',
    	'tab'		=> 'tab-4',
    ],
    'mastodon_meta' => [
    	'label'		=> esc_html__( 'Mastodon', 'comicpost' ),
    	'type'		=> 'checkbox',
    	'description' => 'Add Mastodon verification code to &lt;HEAD&gt; tag to prove you control this website.',
    	'tab'		=> 'tab-4',
    ],
    'mastodon_id'	=> [
    	'label'		=> '',
    	'type'		=> 'text',
    	'description' => 'Enter our Mastodon ID (@handle@instance.net)',
    	'tab'		=> 'tab-4',
    ],

	// BACKEND SETTINGS    
    'manageposts_thumbnail_size' => [
        'label'       => esc_html__( 'Thumbnail Size in Manage Posts', 'comicpost' ),
        'type'        => 'select',
        'description' => 'Thumbnail width in pixels, the height will be automatic maintaining aspect ratio. Default is 120 pixels wide. This is how large you want thumbnails to appear in the back-end <strong>Comics &gt; All Comics</strong> screen.',
		'choices'	  => [
			'50'	  => esc_html__( '50 pixels', 'comicpost' ),
			'120'	  => esc_html__( '120 pixels', 'comicpost' ),
			'200'     => esc_html__( '200 pixels', 'comicpost' ),
			'300'	  => esc_html__( '300 pixels', 'comicpost' ),
		],
		'tab'		  => 'tab-5',
    ],
    'remove_admin_bar' => [
    	'label'	=> esc_html__( 'Remove Admin Bar', 'comicpost' ),
    	'type'	=> 'checkbox',
    	'description' => 'Removes the Admin Bar from the top of the FRONT END of your website, if it is interfering with your theme.',
    	'tab'	=> 'tab-5',
    ],
    
 	// SHORTCODE INFO   
    'shortcode_comic' => [
    	'label'		  => esc_html__( 'Insert Comic', 'comicpost' ),
    	'type'		  => 'section',
    	'description' => 'Allows you to insert a comic anywhere with a link to the comic post. By design it can show comics regardless of required login or hiding old comics. It can add additional security features above the global settings but not reduce them.<br> <b>Parameters:</b><br>
    	<b>comicnav</b> = "true|<i>false</i>" : whether or not to include comic navigation below the comic image.<br>
    	<b>size</b> = "<i>thumbnail</i>|medium|large|full" : the size of comic image to display<br>
    	<b>protect</b> = "encode,glass,noprint" : single or comma-separated list of protections to apply.<br>
    	<b>orderby</b> = "ASC|<i>DESC</i>" : start at beginning or start at end, ignored if single.<br>
    	<b>number</b> = "<i>1</i>" : offset from start/end (depending on orderby)<br>
    	<b>chapter</b> = "1,slug,Name" : which chapter to grab by ID, slug, or Name. Ignored if single is true.<br>
    	<b>slug</b> = "the-slug-name" : shows only the comic specifice by the slug name (overrides chapter and orderby).<br>
    	<b>id</b> = "1098" : shows only the comic specified by post ID (overrides chapter, orderby, and slug).<br>
    	<b>link</b> = "<i>true</i>|false|url" : whether to make the image a link to the comic post or not, or override with a custom url.<br>
    	<br><b>Example Usage:</b><br>
    	[insertcomic size="full" chapter="chapter-one" orderby="DESC" comicnave="true"]<br>
    	[insertcomic size="large" id="8045" protect="glass"] <i>(shows only post ID 8045)</i><br>
    	[insertcomic slug="big-big-trouble"] <i>(shows only post thumbnail with slug name "big-big-trouble")</i><br>
    	[insertcomic size="medium" chapter="chapter-three" orderby="ASC" number="5" comicnav="true"]<br>
    	<i>(shows medium image of fifth comic in chapter three with comic navigation below it)</i>',
    	'tab'		  => 'tab-6',
    ],
    'shortcode_comiclist' => [
    	'label'		=> esc_html__( 'Archive Comic List', 'comicpost'),
    	'type'		=> 'section',
    	'description' => 'Adds a unordered list of Comic Chapters anywhere. When a user selects a chapter from the list they are immediately taken the Archives for that chapter.<br>
    	<b>Parameters:</b><br>
    	<b>include</b> = "1,slug,Name" : single or comma-separated list of chapter IDs, slugs, or names to include. Will not automatically include tree of sub-chapters.<br>
    	<b>exclude</b> = "1,slug,Name" : single or comma-separated list of chapter IDs, slugs, or names to exclude. WILL automaticlaly exclude the tree of sub-chapters.<br>
    	<b>emptychaps</b> = "show|<i>hide</i>" : whether or not to show chapters with no comic posts in them<br>
    	<b>thumbnails</b> = "<i>true</i>|false" : whether or not to show chapter thumbnail or not. It uses the image from the first post in the chapter, assuming there is one.<br>
    	<b>order</b> = "<i>ASC</i>|DESC" : whether to display the list in ascending or descending order.<br>
    	<b>orderby</b> = "<i>name</i>|slug|ID" : what to order the list by, remember that name and slug are sorted alphabetically.<br>
    	<b>postdate</b> = "first|<i>last</i>" : whether the chapter date shown should be by the first or last comic posted in it.</br>
    	<b>dateformat</b> = "<i>site</i>|Y-m-d" : whether to use the date format for the site defined in <em>Settings > General</em> or some other date format.<br>
    	<b>description</b> = "true|<i>false</i>" : whether to include the Chapter Description or not. This is instended for short descriptions like "#1" or "Ep.1" If it is longer you will need to custom style the list to display it.<br>
    	<b>comments</b> = "<i>true</i>|false" : whether to include a count of the total number of comments on all posts in the chapter.<br>
    	<b>showratings</b> = "true|<i>false</i>" : whether to include the cumulative ratings for the chapter (only works if you have enabled either Post Likes or Five-Star Ratings).<br>
    	<b>liststyle</b> = "<i>flat</i>|indent|custom" : the unordered list style. The "indent" option visually indicates the chapter hierarchy by shifting sub-chapters to the right. You can also declare a class name (list-style-custom) for custom styling, where "custom" is whatever you want.<br>
    	<b>title</b> = "<i>Chapters</i>|custom" : This is the title of the Chapter List, if any. You could change this to "Episodes" or "" for no heading.<br>
    	<br><b>Example Usage:</b><br>
    	[comicpost-chapter-list] <em>(would show all chapters and subchapters with default layout)</em><br>
    	[comicpost-chapter-list exclude="124,chapter-one,Title Three"]<br>
    	[comicpost-chapter-list thumbnails="false" comments="false" postdate="false" liststyle="indented"] <em>(barebones hierarchical list of just chapter titles)</em><br>
    	[comicpost-chapter-list dateformat="Y/m/d" description="true" showratings="true"] <em>(would show all elements plus using a custom date format)</em>',
    	'tab'		=> 'tab-6',
    ],
 	'shortcode_dropdown' => [
 		'label'		=> esc_html__( 'Archive Drop-Down', 'comicpost' ),
 		'type'		=> 'section',
 		'description' => 'Adds a drop-down list of Comic Chapters anywhere. When a user selects a chapter they are immediately taken to the Archives for that chapter.<br>
 		<b>Parameters:</b><br>
 		<b>include</b> = "1,slug,Name" : single or comma-separated list of chapter IDs, slugs, or names to include. Will not automatically include tree of sub-chapters.<br>
 		<b>exclude</b> = "1,2,3,4" : single or comma-separted list of chapter IDs, slugs, or names to exclude. Automatically excludes tree of sub-chapters.<br>
 		<b>emptychaps</b> = "show|hide" : whether or not include chapters that have no comic posts in them.<br>
 		<b>title</b> = "Select Chapter|custom" : first item in drop-down that says what it selects. If set to "" it uses default.<br>
 		<br><b>Example Usage:</b><br>
 		[comicpost-archive-dropdown] (would show ALL chapters and sub-chapters)<br>
 		[comicpost-archive-dropdown exclude="124,142,143,168"] (excludes 4 chapters and all their sub-chapters).',
 		'tab'	=> 'tab-6', 
 	],
 	'shortcode_social' => [
 		'label'		=> esc_html__( 'Social Sharing Buttons', 'comicpost' ),
 		'type'		=> 'section',
 		'description' => 'Adds Social Media sharing buttons for your readers to share content to their own social media account.<br>
 		<b>Parameters:</b><br>
 		<b>type</b> = "text|label|small|medium|large" : type and size of button to show<br>
 		<b>include</b> = "facebook,threads,mastodon..." : list of social buttons to include<br>
 		<b>exclude</b> = "bluesky,linkedin,rss..." : list of social buttons to exclude<br><br>
 		<b>Valid Social Sites:</b><br>
 		facebook, threads, bluesky, mastodon, tumblr, reddit, linkedin, pinterest, rss, email<br><br>
 		<b>Example Usage:</b><br>
 		[comicpost-share type="large" include="facebook,threads,mastodon"] (would show three 32x32 pixel social sharing icon buttons)<br>
 		[comicpost-share type="label" exclude="rss,email"] (would show 8 buttons with small icons and text labels, omitting RSS and email buttons).',
 		'tab' => 'tab-6',
 	],
 	'shortcode_encoder' => [
 		'label'		=> esc_html__( 'Data Encoder', 'comicpost' ),
 		'type'		=> 'section',
 		'description' => 'The same functions that can encode your comic URLs to hide them from spambots and scrapers can be used to protect ANY arbitrary text content on your site via the "protect" shortcode. The shortcode works whether you enabled "Encode Comic URLs" or not.<br>
 		<b>Parameters:</b> (all are optional)<br>
 		<b>key</b> = "0-255" : optional value for encoding, omit and it picks a random number.<br>
 		<b>type</b> = "mailto:|tel:+1|calto:|skype:" : any valid &lt;a&gt; tag protocol.<br>
 		<b>placeholder</b> = "Hidden Content" : what the protected element should say on it, if anything.<br><br>
 		<b>Example Usage:</b><br>
 		[protect]Arbitrary content[/protect]<br>
 		[protect type="mailto:"]jane.doe@example.com[/protect]<br>
 		[protect type="tel:+1"](555) 555-5555[/protect]<br>
 		[protect placeholder="Hidden from View Source"]Arbitrary content we want hidden[/protect]<br><br>
 		This gets encoded on the server side into a long string. For example, the phone number above might turn into: "4c64797979656c7979796179797979" which most bots would not see as a phone number when crawling the page source code.  A JavaScript on the frontend of the site decodes it.  Bots or scrapers that use <i>rendered</i> content would not be fooled, so do not rely on this.  Any data you do not want any bots or scrapers getting should be placed behind a login.',
 		'tab'	=> 'tab-6',
 	],  
    'shortcode_notpublic' => [
    	'label'		=> esc_html__( 'Not Public', 'comicpost'),
    	'type'		=> 'section',
    	'description' => 'A shortcode to Content Restrict anything so only logged-in users can see it. Note that this does not take eithr roles or capabilities into account, it ONLY checks whether or not the user is logged in or not.<br>
    	<b>Parameters:</b> (optional)<br>
    	<b>placeholder</b> = "Members Only Content" : what the protected element should say on it, if anything.<br><br>
    	<b>Example Usage:</b><br>
    	[notpublic]Arbitrary content[/notpublic]<br>
    	[notpublic placeholder="Special Offer for Registered Users! Be sure to log in to see it."]50% OFF for all orders today using the coupon code "2202"[/notpublic]',
    	'tab' => 'tab-6',
    ],
    'shortcode_top_comics'   => [
    	'label'		=> esc_html__( 'Top Comics List', 'comicpost' ),
    	'type'      => 'section',
    	'description' => 'A shortcode to display a list of the top-rated comics on the website. Obviously this only works if you have enabled the Ratings System under the Social Media tab. By default the layout is the same as the Chapter List.<br>
    	<b>Parameters:</b><br>
    	<b>comic</b> = "none|<i>thumbnail</i>|medium" : whether to include a thumbnail and what size (only two sizes are available).<br>
    	<b>number</b> = "<i>5</i>|n" : number of comics to show. Default is a "Top Five" list.<br>
    	<b>showrating</b> = "<i>true</i>|false" : whether to show the number or Likes or Stars<br>
    	<b>rank</b> = "<i>true</i>|false" : whether to include the rank number in front of the title.<br>
    	<b>postdate</b> = "<i>true</i>|false" : whether to include the post date or not.<br>
    	<b>dateformat</b> = "<i>site</i>|Y-m-d" : "site" uses the setting under Settings &gt; General, or any valid date format.<br>
    	<b>comments</b> = "<i>true</i>|false" : whether to show comment count or not.<br>
    	<b>from</b> = "1 year ago" : (optional) if you want to limit the date range for the top comics list. In this example it would only retrieve comics up to 1 year ago instead of the default which is all comics from all time.<br>
    	<b>to</b> = "tomorrow" : (optional) if you want to limit the date range for the top comics list include the end date for the range. In this case through tomorrow.<br>
    	<b>title</b> = "" : Anything you want additionally put after the "Top Comics" header.<br>
    	<b>liststyle</b> = "<i>flat</i>|custom" : the class for the unordered list style.<br>
    	<b>chapters</b> = "1,slug,Name" : (optional) comma-separated list of term IDs, slugs, or Chapter Names to limit the list. If omitted the default is all comics from all chapters. If you limit the list you may want to add something to the title indicating what chapter(s) are from."<br><br>
    	<b>Example Usage:</b><br>
    	[topcomics number="10" comments="false"] (shows at "Top 10" without comment count)<br>
    	[topcomics comic="none" showrating="false" postdate="false" comment="false"] (a bare-bones "Top 5" list with just the comic title and rank number.',
    	'tab' => 'tab-6',
    ],
    'shortcode_user_ratings' => [
    	'label'		=> esc_html__( 'User Ratings List', 'comicpost' ),
    	'type'		=> 'section',
    	'description' => 'A shortcode to display a list of which comic posts a user has rated and how many stars they gave it. The intended use is on a frontend user dashboard or profile.  Only the user themselves can see this information.<br>
    	<b>Parameters:</b> (optional)<br>
    	<b>from</b> = "<i>1 year ago</i>" : start date for comments with ratings.<br>
    	<b>to</b> = "<i>tomorrow</i>" : end date for comments with ratings.<br>
    	<em>from/to can be any valid PHP strtotime() English date format.</em><br>
    	<b>comics</b> = "<i>thumbnail</i>|medium|large|full|none" : whether to include a comic thumbnail or not and what size.<br>
    	<b>dummy</b> = "true|<i>false</i>|placeholder" : show/hide list if empty, or show and populate with placeholders to maintain layout.<br><em>Use "true" if styling in your theme, "placeholder" for default styling</em><br><br>
    	<b>Example Usage</b><br>
    	[userratings]<br> (default parameters)
    	[userratings comic="medium"] (larger comic image)',
    	'tab'	=> 'tab-6',
    ],
    'shortcode_user_likes' => [
    	'label'		=> esc_html__( 'User Likes List', 'comicpost' ),
    	'type'		=> 'section',
    	'description' => 'A shortcode to display a list of which comic posts a user has "liked." The intended usage is on a frontend user dashboard or profile. Only the user themselves can see this information.<br>
    	<b>Parameters:</b> (optional)<br>
    	<b>comic</b> = "<i>thumbnail</i>|medium|large|full|none" : whether to include a comic thumbnail or not and what size.<br>
    	<b>dummy</b> = "true|</i>false</i>|placeholder" : show/hide list if empty, or show and populate with placeholders to maintain layout.<br><em>Use "true" if styling in your theme, "placeholder" for default styling</em><br><br>
    	<b>Example Usage:</b><br>
    	[userlikes] (default parameters)<br>
    	[userlikes comic="large"] (large comic image)',
    	'tab'	=> 'tab-6',
    ],
    'shortcode_user_comments' => [
    	'label'		=> esc_html__( 'User Comments List', 'comicpost' ),
    	'type'      => 'section',
    	'description' => 'A shortcode to display a list of comments the user has made on the site. The intended usage is on a frontend user dashboard or profile. Only the user themselves can see this information.<br>
    	<b>Parameters:</b> (optional)<br>
    	<b>from</b> = "<i>1 year ago</i>" : start date for getting comments.<br>
    	<b>to</b> = "<i>tomorrow</i>" : end date for getting comments.<br>
    	<em>from/to can be any valid PHP strtotime() English date format.</em><br>
    	<b>dummy</b> = "true|<i>false</i>|placeholder" : show/hide list if empty, or show and populate with placeholders to maintain layout.<br><em>Use "true" if styling in your theme, "placeholder" for default styling</em><br><br>
    	<b>Example Usage:</b><br>
    	[usercomments] (default settings, comments from 1 year ago through today)<br>
    	[usercomments from="10 September 2016" to="last Monday"]',
    	'tab'	=> 'tab-6',
    ],
    'anti_ai_terms' => [
    	'label'		=> esc_html__( 'Recommended TOS Text', 'comicpost' ),
    	'type'		=> 'section',
    	'description' => '<b>COPY &amp; PASTE THE FOLLOWING INTO YOUR WEBSITE TERMS OF SERVICE PAGE:</b><br><br>The owner of this website does not consent to the content on this website being used or downloaded by any third parties for the purposes of developing, training, or operating artificial intelligence or other machine learning systems ("Artificial Intelligence Purposes"), except a authorized by the owner in writing (excluding written electronic communication). Absent such consent, users of this website, including any third parties acessing the website through automated systems, are prohibited from using any of the content on this website for Artificial Intelligence Purposes. Users or automated systems that fail to respect these choices will be considered to have breached these Terms of Service. Pages on this website may include a robots meta tag with the "noai" and "noimageai" directive in the head section of the HTML code. Please note that even if such directives are not present on any web page or content file, this website still does not grant consent to use any content for Artificial Intelligence Purposes unless such consent is expressly contained within that page or content file.',
    	'tab' => 'tab-6',
    ],
];

new ComicPost_Options_Panel( $comicpost_options_panel_args, $comicpost_options_panel_settings );

function comicpost_admin_scripts_and_styles(){
		global $pagenow;
		global $typenow;
	// only run this check on the ComicPost Options page!
	if ($pagenow=="edit.php" && $typenow == "comic" && isset( $_GET['page'] ) && 'comicpost_settings' == $_GET['page']){
		$options = get_option('comicpost_options');
		$height   = $options['watermark_image_size'];
		$width    = $height;
		$tilesize = ($options['watermark_tile_size']/2);
		$opacity  = $options['watermark_opacity'];
		$wm_method = $options['watermark_method'];
		$wm_image = comicpost_pluginfo('base_url').'/comic_watermark/watermark.png';
		$upload_dir = wp_get_upload_dir();
		
		if ( isset( $_POST['comicpost_deletepng_noncename']) ){
			if ( !wp_verify_nonce( $_POST['comicpost_deletepng_noncename'], 'comicpost-delete-watermark') ){
				echo "<div class='error'><p>Authentication failed</p></div>";
				return;
			}
			wp_delete_file( $upload_dir['basedir'].'/comic_watermark/watermark.png');
		};
		if ( isset( $_POST['comicpost_genmark_noncename']) ){
			if ( !wp_verify_nonce( $_POST['comicpost_genmark_noncename'], 'comicpost-generate-watermark') ){
				echo "<div class='error'><p>Authentication failed</p></div>";
				return;
			}
			wp_delete_file( $upload_dir['basedir'].'/comic_watermark/watermark.png');
			if ( $options['watermark_source'] == "custom") {
				comicpost_generate_custom_watermark();
			} else {
				comicpost_generate_watermark_tile();
			}
		};
	?>
	
	<form method="post" action="" id="delete_watermark_form">
		<?php wp_nonce_field( 'comicpost-delete-watermark', 'comicpost_deletepng_noncename');?>
	</form>
	<form method="post" action="" id="generate_watermark_form">
		<?php wp_nonce_field( 'comicpost-generate-watermark', 'comicpost_genmark_noncename');?>
	</form>
	
	
	
	<script id="comicpost_admin_options_script" type="text/javascript">
		function comicpost_font_preview(){
			var font_value = document.getElementById('watermark_font').value;
			var the_text   = document.getElementById('watermark_text').value;
			var display_p  = document.getElementById('cp_font_preview');
			var font_name  = "Unitblock";
			switch(font_value) {
				case 'black-ops-one':
					font_name = "BlackOpsOne";
					break;
				case 'cascadia-code':
					font_name = "Cascadia";
					break;
				case 'liberation-sans':
					font_name = "LiberationSansBold";
					break;
				case 'sarina':
					font_name = "SarinaRegular";
					break;
				case 'tiza':
					font_name = "Tiza";
					break;
				case 'unitblock-font':
					font_name = "Unitblock";
					break;
				default:
					font_name = "Unitblock";
			} 
			display_p.innerText = the_text;
			display_p.style.fontFamily = font_name;
		};
		function comicpost_nav_preview(){
			var nav_show  = document.getElementsByClassName('comic-nav')[0];
			var nav_value = document.getElementById('navigation_style').value;
			nav_show.className = "comic-nav " + nav_value;		
		};
		function comicpost_update_opacity_preview(){
			var opacity = document.getElementById('watermark_opacity').value;
			document.getElementById('wm_prev').style.opacity = opacity;
		}
		function comicpost_preview_color_mode(color){
			var page = document.getElementById('wm_sample_bg');
			if (color == 'linebutton'){
				page.style.filter = 'saturate(0) contrast(50)';
			} else if ( color == 'graybutton'){
				page.style.filter = 'saturate(0)';
			} else {
				page.style.filter = '';
			}
		}
		function comicpost_preview_method(){
			var prev = document.getElementById('wm_prev');
			var view = document.getElementById('watermark_method').value;
			prev.className = view;
		}
	
		function comicpost_submit_delete_watermark_form(){
			var msg = "This will permanently DELETE any watermark PNG image. IT CANNOT BE UNDONE!";
			if (confirm(msg) == true){
				document.getElementById('delete_watermark_form').submit();
			}
		}
		function comicpost_submit_generate_watermark_form(){
			var msg = '';
			if (document.getElementById('watermark_source').value == "generate"){
				msg = "If you changed any Generated Watermark settings you need to SAVE THEM FIRST!  This will DELETE any existing watermark PNG image and generate a new one to REPLACE it.";
				if (confirm(msg) == true){
					document.getElementById('generate_watermark_form').submit();
				};
			} else {
				if (document.getElementById('custom_watermark_file').value == ''){
					// no custom source file
					alert('Watermark Image Source is set to "Custom" but no source image is set under "Choose Custom Watermark." Select a file, SAVE SETTINGS, and push the "Create Watermark File" button again.'); 	
					return;
				} else {
					msg = "Your Custom Image will be turned into a watermark PNG file. This will DELETE any existing watermark PNG image and create a new one to REPLACE it.";
					if (confirm(msg) == true){
						document.getElementById('generate_watermark_form').submit();
					}
				}
			};
		}
	
		function comicpost_add_previews(){
			// Navigation Preview	
			var nav_prev = document.createElement('div');
				nav_prev.id = "cp_nav_preview";
				nav_prev.innerHTML = '<p><strong>COMIC NAVIGATION PREVIEW:</strong><br><div class="comic-nav '+document.getElementById('navigation_style').value+'"><a href="#" class="comic-nav-base comic-nav-oldest">&lsaquo;&lsaquo; Oldest</a><a href="#" class="comic-nav-base comic-nav-first-chap">&lsaquo;&lsaquo; First in Chap</a><a href="#" class="comic-nav-base comic-nav-previous">&lsaquo; Prev</a><a href="#" class="comic-nav-base comic-nav-next">Next &rsaquo;</a><a href="#" class="comic-nav-base comic-nav-last-chap">Last in Chap &rsaquo;&rsaquo;</a><a href="#" class="comic-nav-base comic-nav-newest">Newest &rsaquo;&rsaquo;</a></div>';
			document.getElementById('navigation_style').parentNode.appendChild(nav_prev);
			document.getElementById('navigation_style').addEventListener('change',function(){ comicpost_nav_preview();});
	
			// Textmark Preview
			var font_prev = document.createElement('p');
				font_prev.id = "cp_font_preview";
				font_prev.innerText = document.getElementById('watermark_text').value;
				font_prev.style.fontFamily = document.getElementById('watermark_font').value;
			document.getElementById('watermark_font').parentNode.appendChild(font_prev);
				// Add event listener to drop-down
			document.getElementById('watermark_font').addEventListener('change',function(){ comicpost_font_preview();});
			comicpost_font_preview();
			
			// Site Image Preview
			var site_img = document.createElement('img');
				site_img.id = 'cp_site_image';
				site_img.src = document.getElementById('fallback_thumbnail').value;
				site_img.style = "max-width: 512px; height: auto;";
			document.getElementById('fallback_thumbnail').parentNode.appendChild(site_img);
			
			// Watermark Previews
			var mark_prev = document.createElement('div');
				mark_prev.id = "cp_mark_preview";
				mark_prev.innerHTML = '<p><strong>WATERMARK PREVIEW:</strong><br><button class="preview-button" type="button" id="colorbutton">Color Page</button> <button class="preview-button" type="button" id="graybutton">Grayscale Page</button> <button class="preview-button" type="button" id="linebutton">Line Art Page</button> (Mouse over pages to see full-size)<br></p><img src="<?php echo $wm_image; ?>" height="<?php echo $height; ?>" width="<?php echo $width; ?>" class="wm_image" /><div class="wm_sample_page"><div id="wm_sample_bg"></div><div id="wm_prev" class="<?php echo $wm_method; ?>"></div></div><br><small><em>Disclosure: The basis of the comic page image above is representative of the very problem this setting is supposed to address. The line art was AI generated by Stable Diffusion using training materials of unknown provenance.</em>';
			document.getElementById('watermark_opacity').parentNode.appendChild(mark_prev);
			document.getElementById('watermark_opacity').addEventListener('change',function(){ comicpost_update_opacity_preview();});
			var preview_buttons = document.getElementsByClassName('preview-button');
			for (var p=0; p < preview_buttons.length; p++){
				preview_buttons[p].addEventListener('click',function(){comicpost_preview_color_mode(this.id);},false);
			};
			document.getElementById('watermark_method').addEventListener("change", (e) => { comicpost_preview_method(); });
			document.getElementById('delete_watermark_png').addEventListener("click", (e) => { e.preventDefault();comicpost_submit_delete_watermark_form();});
			document.getElementById('generate_watermark_png').addEventListener("click", (e) => { e.preventDefault();comicpost_submit_generate_watermark_form();});
		};
		window.addEventListener("load", function(){comicpost_add_previews();});
		
			jQuery(document).ready(function($){
				$(".wpsf-browse").click(function() {
					jsid = this.id.replace('_button','');
					tb_show("", "media-upload.php?post_id=0&amp;type=image&amp;TB_iframe=true");
					window.original_send_to_editor = window.send_to_editor;
					window.send_to_editor = function(html) {
						var url = $(html).attr('href');
						if ( !url ) {
							url = $(html).attr('src');
						};
						$("#"+jsid+"").val(url);
						tb_remove();
						window.send_to_editor = window.original_send_to_editor;
					};
					return false;
				});
			});
	</script>
	<style id="comicpost_admin_styles">
		@import url("<?php echo comicpost_pluginfo('plugin_url'); ?>/fonts/stylesheet.css");
		@import url("<?php echo comicpost_pluginfo('plugin_url'); ?>/css/comicpost-nav.css");
		.comic-nav {
			max-width: 800px;
			padding: 20px;
			border: 1px solid black;
		}
		.preview-button {
			margin: 10px;
		}
		#delete_watermark_png {
			background-color: red;
			color: white;
			font-weight: bold;
			border-radius: 5px;
			padding: 10px;
		}
		#generate_watermark_png {
			background-color: limegreen;
			color: black;
			font-weight: bold;
			border-radius: 5px;
			padding: 10px;
		}
		#cp_font_preview {
			font-size: 24px;
			padding: 10px;
			text-align: center;
			border: 1px dashed black;
		}
		.wm_image {
			position: relative;
			height: <?php echo $height; ?>px;
			width:  <?php echo $width;  ?>px;
			display: block;
			float: left;
			border: 1px solid black;
			box-shadow: 5px 5px 10px rgba(0,0,0,.25);
		}
		.wm_sample_page {
			position: relative;
			float: left;
			display: block;
			height: 425px;
			width:  256px;
			/* this is HALF actual size */
			margin: 0 10px;
			background-color: white;
		}
		#wm_sample_bg {
			position: absolute;
			height: 425px;
			width:  256px;
			background-image: url("<?php echo comicpost_pluginfo('plugin_url'); ?>/images/preview_page.jpg");
			background-size: contain;
			background-position: center center;
			border: 1px solid #ccc;
			box-shadow: 5px 5px 10px rgba(0,0,0,.25);
			filter: saturate(1);
		}
			.wm_sample_page:hover {
				transform: scale(2);
				z-index: 10;
			}
			#wm_prev.tile {
				position: absolute;
				height: 425px;
				width:  256px;
				background-color: transparent;
				background-image: url("<?php echo $wm_image; ?>");
				background-position: top left;
				background-repeat: repeat;
				background-size: <?php echo $tilesize; ?>px <?php echo $tilesize; ?>px;
				opacity: <?php echo $opacity; ?>;
			}
			#wm_prev.center {
				position: absolute;
				height: 425px;
				width:  256px;
				background-color: transparent;
				background-image: url("<?php echo $wm_image; ?>");
				background-size: contain;
				background-position: center center;
				background-repeat: no-repeat;
				opacity: <?php echo $opacity; ?>;
			}
			#cp_mark_preview small {
				position: relative;
				display: block;
				clear: both;
				padding-top: 20px;
			}
			.comicpost-range {
				width: 300px !important;
			}
			.comicpost-datalist {
				display: flex;
				justify-content: space-between;
				width: 300px;
			}
			.comicpost-datalist option {
				margin-left: -10px;
			}
				
		@media screen and (max-width: 1100px) {	
			.wm_image, .wm_sample_bg {
				float: none;
				margin: 10px auto;
			}
		}
	</style>
<?php
	};
	if ($typenow == "comic"){
		?>
		<style type="text/css">
		/* Add ComicPost Icon to admin pages */
		body.comic_page_comicpost_settings h1::before,
		.wp-heading-inline::before {
				content: '';
				display: inline-block;
				height: 32px;
				width:  32px;
				background: transparent url("<?php echo comicpost_pluginfo('plugin_url'); ?>images/comicpost_logo.png") center center no-repeat;
				background-size: 32px 32px;
		  };		
		
		</style>
	<?php
	}
};
add_action('admin_footer', 'comicpost_admin_scripts_and_styles');