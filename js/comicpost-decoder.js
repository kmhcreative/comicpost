/*	STRING DECODER
	=======================
	Adapted from code by Andrew Lock for protecting email addresses:
	https://andrewlock.net/simple-obfuscation-of-email-addresses-using-javascript/
	
	This script will find and decode any content protected from bots
	in the HTML source code.
	
*/
	

var COMICPOST = COMICPOST | {};
COMICPOST = function() {

	function decodeString(encodedString) {
		// Holds the final output
		var string = ""; 

		// Extract the first 2 letters
		var keyInHex = encodedString.substr(0, 2);

		// Convert the hex-encoded key into decimal
		var key = parseInt(keyInHex, 16);

		// Loop through the remaining encoded characters in steps of 2
		for (var n = 2; n < encodedString.length; n += 2) {

			// Get the next pair of characters
			var charInHex = encodedString.substr(n, 2)

			// Convert hex to decimal
			var char = parseInt(charInHex, 16);

			// XOR the character with the key to get the original character
			var output = char ^ key;

			// Append the decoded character to the output
			string += String.fromCharCode(output);
		}
		return string;
	}
	// Find all the elements on the page that use class="kmh-protected"
	var allElements = document.getElementsByClassName('comicpost-protected');

	// Loop through all the elements, and update them
	for (var i = allElements.length-1; i > -1; i--) {
		unProtect(allElements[i])
	}

	function unProtect(el) {
		var linktypes = [ 'url', 'tel', 'mailto', 'callto' ];	// must match types in PHP encode
		// fetch the hex-encoded string
		var encoded = el.getAttribute('data-content');
		// decode the email, using the decodeEmail() function from before
		var decoded = decodeString(encoded);

		// See if the element is a link or not
		if (el.tagName.toLowerCase() == 'a' ) {
			console.log('element is A tag.  href ='+el.href);
			
			if (el.href.match(/#/)) {
				if ( el.getAttribute('data-type') == 'url' ) {
					if ( el.getAttribute('data-url') ) {	// url is encoded separate from content
						console.log('URL is in data-url attribute');
						el.href = ""+decodeString( el.getAttribute('data-url') )+""; 
					} else {
						console.log('Content is URL');
						el.href = ""+decoded+"";	// assume the content was a valid url
					}
				} else {	// it some other kind of link (mailto:, tel:, tel:+, tel+1, callto: )
					console.log('type is '+el.getAttribute('data-type'));
					if ( el.getAttribute('data-type').match('tel:') ) { // clean up number
						decoded = decoded.replace('-','').replace('.','').replace('(','').replace(')',''); 
					};
					el.href = ""+el.getAttribute('data-type')+decoded+"";
				}
			}
		} else if (el.tagName.toLowerCase() == 'img'){
			// data-type is assumed to be "image" and data-content is src url
			el.src = ""+decoded+"";
		} else if (el.getAttribute('data-type') == 'background'){
			// encoded CSS background image
			console.log(el.getAttribute('style'));
			el.setAttribute('style', el.getAttribute('style')+"background-image:url('"+decoded+"');");
		} else {
			// encoded text
			el.textContent = ""+decoded+"";
		}
		
		// clean-up on aisle three!
		if (el.getAttribute('data-content')){ el.removeAttribute('data-content'); }
		if (el.getAttribute('data-type')){ el.removeAttribute('data-type'); }
		if (el.getAttribute('data-url')){ el.removeAttribute('data-url'); }
		// remove the protected class
		el.className = el.className.replace('comicpost-protected','');
	}
	
	return {
		decodeString : decodeString,
		unProtect    : unProtect
	
	}
}();