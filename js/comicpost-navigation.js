/*	COMICPOST NAVIGATION
	=======================
	This script allows navigating ComicPost posts using keyboard
	Note that if you have multiple comic navigation sets on a page
	(for example if you used the insertcomic shortcode) this will
	iterate through them until it finds a valid navigation url or
	does not find any.
*/
	
	document.onkeyup = function(evt) {
		evt = evt || window.event;
		comicpostKeyNav(evt);
	}
	 
	// KEYBOARD CONTROLS //
function comicpostKeyNav(e) {
		if ( e.keyCode == 33 || e.keyCode == 38 ){ // First In Chapter
			comicpost_get_nav_url('first-chap');
		}
		if ( e.keyCode == 34 || e.keyCode == 40 ){ // Last In Chapter
			comicpost_get_nav_url('last-chap');
		}
		if ( e.keyCode == 35 ){	// Newest
			comicpost_get_nav_url('newest');
		}
		if ( e.keyCode == 36 ){	// Oldest
			comicpost_get_nav_url('oldest');
		}
		if ( e.keyCode == 37 ){	// Previous
			comicpost_get_nav_url('previous');
		}
		if ( e.keyCode == 39 ){	// Next
			comicpost_get_nav_url('next');
		}
};

function comicpost_get_nav_url( whichone ){
	if (!whichone){ return; }
	var nav_buttons = document.getElementsByClassName('comic-nav-base');
	for (var n=0; n < nav_buttons.length; n++){
		if (nav_buttons[n].className.match('comic-nav-'+whichone)){ // if whichone is in className string
			if (nav_buttons[n].href){ // if it has an href attribute
				window.location = nav_buttons[n].href;
			}
		}
	}
}