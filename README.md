# ComicPost
<img src="images/comicpost_logo.png" width="400"/>
A simple webcomic plugin with content restriction options to prevent AI scraping.

**Version:** 0.1

**Requires WordPress Version:** 3.5 or higher, PHP 5+

**Compatible up to:** 6.7.1

**Beta Version Disclaimer**

This plugin is still being tested.  It seems pretty solid, but use it at your own risk.

## Description

ComicPost is a relatively lightweight and simple webcomics plugin that *should* work with pretty much any theme that displays Featured Images with posts. This also may be the first WordPress webcomics plugin to specifically address the issue of Artificial Intelligence image scrapers stealing and training on comic artwork without permission or compensation. To combat that there are a number of **Content Restriction** options and a built-in automatic **Watermarking** feature.

This plugin is intended as an easy (but not drop-in) replacement for ComicPress/Comic Easel because it uses the same default custom post-type and chapter taxonomy, so any existing comics will automatically show up in it and work with it without having to migrate/import anything. But a prior installation of either ComicPress or Comic Easel are not necessary.

## Installation

### Using Admin Upload

1. Download the GitHub archive as a ZIP file.
2. Go to your _Dashboard > Plugins > Add New_ and press the "Upload Plugin" at the top of the page.
3. Browse to where you downloaded the ZIP file and select it.
4. Press the "Install Now" button.
5. On your _Dashboard > Plugins_ page activate "ComicPost"
6. Go to _Dashboard > Comics > Options_ and configure it.

### Using FTP
  
2. Unzip it so you have a "comicpost-master" folder
3. FTP upload it into your WordPress blog’s _~/wp-content/plugins/_ folder.
4. Go to your _Dashboard > Plugins_ and activate “ComicPost”
5. Go to _Dashboard > Comics > Options_ to configure it.


## Options

### General Settings

**Comic Navigation:**
This option determines how the "Previous" and "Next" buttons work in the comic navigation.
 
* _Within Chapters_ - goes to the previous/next comic within the current chapter (chapter-only navigation). If you use this you should provide some way for readers to navigate to other chapters.

* _Traverse Chapters_ - will go to the previous/next comic in the _current_ chapter until it reaches either the start or end of the chapter, then it will jump to the next comic (by date, relative to the current comic) in an adjacent chapter.

* _Ignore Chapters_ - will go to the previous/next post relative to the date of the current comic, ignoring the chapters (date-only navigation).

**Navigation Location** 
The comic navigation can be placed either _Above_ or _Below_ the comic image.

**Navigation Style**
Ideally you should be styling the navigation links/buttons in your site's THEME. But if you were using ComicPress/Comic Easel before and the pre-styled navigation the are reproduced here to ease transitioning your site to ComicPost.

**Keyboard Navigation:**
Tick this box if you would like to enable navigating your comics using keys on your keyboard. For reference they are:
← = Previous
→ = Next
↑ = First in Chapter ("Page-Up" also)
↓ = Last in Chapter ("Page-Down" also)
"Home" = Oldest Comic
"End" = Newest Comic

**Comic Size:** 
Normally the comic image will be whatever size your theme uses for displaying Featured Images. You can override that here regarding which image WordPress uses, but be aware the theme may still limit or alter the actual display size.

**Archive Thumbnail Links:**
In many themes entries in the archives use only the post title as the link to the full post. Tick this checkbox if you want the comic thumbnail image to also be a link to the full comic post.

**Archive Post Count**
Enter the number of comic posts you would like to appear in the comic archives per page. This overrides the maximum post count under Settings > Reading in the main menu, but only for comic and chapter archives.

**Chapter Archive Views**
* _Theme/None_ - uses whatever appearance is set in your site theme.
* _Vertical View_ - single column of just comic images.
* _Multiple Views_ - adds buttons allowing the reader to switch between four different views.
Note: This requires your theme to actually show Featured Images on archive pages, it will NOT work with every theme, and may require additional tweaks to your theme stylesheet. You may need to enable "Archive Thumbnail Links" to allow navigating to comic posts.

### Watermarking
Watermarking your comic images is one of the most effective ways to protect them because information about the provenance and ownership of the image travels with it whereever it may end up. Your unique watermark placed consistently on your images will make it obvious an AI was trained on your images when it reproduces a garbled version of the watermark as well. **It is highly recommended you watermark your comic images.**

**Watermark Comics**
You can enable/disable automatic watermarking on upload. If you enable it you can choose between _Tile_ and _Center_ for how the watermark image is overlaid on your comic.

**Watermark Tile Size**
If you chose _Tile_ this is where you set the Tile Size in pixels. The number represents both the height and width as tiles must be square.

**Watermark Image Source**
Choose whether to use a _Generated_ or _Custom_ image for your watermark.

**Choose Custom Watermark**
If you chose _Custom_ for the source this is where you point ComicPost to the image you want to use. It should be a square PNG image, preferably black and white, with a transparent background. If it is not square it will be scaled to fit before it is applied.

**CREATE WATERMARK FILE**
This button will create a NEW watermark image based on the "Image Source" setting. It will either use your "Custom Image" or it will use the "Generated Watermark" settings.  It will _replace_ any existing _watermark.png_ image.

**DELETE WATERMARK FILE**
This button will _delete_ any existing _watermark.png_ image file.

**Watermark Opacity**
Set the opacity of the watermark between 10% and 100%. Depending on the image being used some setting may be too faint. You may have to play with this generating images with different settings until you find the right opacity.

**Watermark Preview**

#### Generated Watermark Settings

**Watermark Image Size**
The watermark image size in pixels, this is for both the height and width as the watermark image needs to be square.

**Watermark Text**
The text to place on the generated watermark image. This defaults to the name of your blog but can be anything. While the text size is adjusted to make whatever you enter fit, if your text is too long the font will be too small to be legible, so keep it brief.

**Watermark Font**
Here you select the typeface for the lettering on your watermark. Your options are:
* Black-Ops
* Cascadia-Code
* Liberation Sans
* Sarina
* Tiza
* Unitblock

**Text Orientation**
Whether you want the text to run _Horizontal_ or _Diagonal_ across the watermark image.  Diagonal is recommended because it will cover more of your image and should be obvious it is not part of the image.

**Keep Clean Copy**
If you enable this ComicPost will _try_ to create a "Clean" (not watermarked) copy of your comic image on the server alongside the automatically watermarked version.  This copy will **NOT**:
* have the watermark on it
* have any thumbnails automatically created for it
* appear in your Media Library
* have any links to it on your website
* use "lazy loading"
* use "source sets"

It is intended to be difficult for any automated system to find and only to be shown to registered, logged-in users of your website. **It is strongly recommended that you do NOT enable this feature!** It pretty much defeats the purpose of all the other methods of protecting your comic artwork.


**Clean Copy Suffix**
If you enable "Keep Clean Copy" the file name of that copy will be appended with whatever unique suffix you enter in the text box. This is to make it impossible for any automated system to guess what the file name might actually be.  This, of course, assumes that directory browsing is _disabled_ on your server so bots cannot simply crawl through and find your files.  **NOTE: If you change this suffix later, any existing clean copies will no longer be able to be found by ComicPost.**  If you ever need to delete the clean copies from your server you will have to do so via FTP or with your webhost's file manager. WordPress will have no means of managing these files or even know they exist.

**Show Clean Copies**
This is the option that will actually show the clean copies to your readers, but only if they are logged into your website.

### Content Restriction

The following features are intended to prevent people and some bots from easily downloading, scraping, indexing, or printing your comic images. These are not foolproof and they come with tradeoffs in appearance and performance. They should really only be applied to PUBLICLY ACCESSIBLE comic images. Your archives should, ideally, be hidden behind a login or paywall instead. Though they attempt to preserve alt-text consider that these features may adversely affect site accessibility for people with disabilities. Note that, apart from the encoding options below, the others rely on stylesheets being loaded and will not prevent bots from reading your image URLs. **WARNING: These settings ONLY apply to Comic Posts. If you attach a comic image to a regular post, page, or some other custom post type none of the following settings will be applied to that image.**

**Advanced Meta Tags**
**Adds the "NoAI" and "NoImageAI" advanced meta tags** to the HEAD section of every page of your website, which are intended to signal to web crawlers, bots, and scrapers that you do not wish your content to be used to train AI models. Please note that whether or not a bot honors this or not is voluntary and some AI companies have already explicitly said they will not. You should also put a more detailed notice in your Terms of Service page (see "Shortcodes" section for suggested text)

**Comics Under Glass**
This will move the comic out of the image tag and turn it into a background image of a DIV element. This disables the ability of anyone to right-click on the image and save, download, or open it in a new tab or window, it also disables the ability to drag the image out of the browser window to download it.

**Printing Comics**
This option allows you to restrict printing your comic pages from your website. It requires that "Comics Under Glass" be enabled.  You have three options:
* Allow Printing _(i.e., do nothing)_
* Watermark Prints
* Disable Printing

The _Disable Printing_ option will use a print media query to prevent the comic image from being printed. As it has been moved to a background image the only way to normally print it would be to include background images in the print dialog, but this overrides the image being shown.

The _Watermark Prints_ option does not use the same watermark from the "Watermarking" section. This is a generated overlay that only appears when someone tries to print from your website. The overlay is, itself, a background image just like the comic itself, so there is no way to print the comic without also printing the watermark overlay. If the user turns off "Print Backgrounds" in the print dialog no image will print at all. 

* _Print Watermark Method_ - here you can choose whether to tile or center the generated watermark image.
* _Print Watermark Text_ - enter what you want the watermark to say.
* _Print Watermark Opacity_ - set how prominent the watermark is on top of your comic.

**Encode Comic URLs**
Enabling this option will encode and obfuscate the URLs to your comic image files. Even bots that crawl or scrape your site HTML code will not be able to find them. Those that can fully render the page, however, could still capture an image of your comic (but you can also enable the faux watermarking feature). Decoding the URLs requires JavaScript. Note that, by design, this will disable lazy loading and source sets. **WARNING: This feature can adversely affect website performance and requires javascript!**

**Apply to Archives**
Tick this checkbox to apply all the above enabled settings to the comic archive images as well. Depending on how many entries are shown on your archive pages this option may adversely affect the performance of your website.

**Apply to Public Posts ONLY**
Applies the options above only to PUBLIC comic posts. If you check this box NONE of the above options will be applied to your comic images for users who are logged into your website.

**Omit Comics from Galleries**
Tick this checkbox to omit comic images attached to comic posts from being included in image galleries. You should only use this option if you are not actually watermarking your comic images. **WARNING: This intercept gallery code output and alters it. A simpler solution is to make sure you never add un-watermarked comic images to a gallery.**

**Hide Old Comics**
This will hide from PUBLIC view all comics older than the selected time period.  Only users who are logged in will be able to see the older comics.  This option only hides the comic image, not the entire comic post.  There is an additional checkbox that will **COMPLETELY** hide old comic posts from users who are not logged in.  However, you may want to customize your 404 page in your theme so visitors know _why_ the comic was not found, and offer them a chance to register to read it, otherwise your site may simply look like its broken or full of dead links.

You can choose to hide comics older than 1 day, 3 days, 1 week, 2 weeks, 1 month, 3 months, 6 months, 1 year, 3 years, or 5 years.

**Require Login**
Check this box and all your comic posts will be private, for registered and logged-in users only, including the newest comics. (Note you can override this with the "Insert Comic" shortcode to show select comics on non comic post-type pages, so you can still give people a taste even if you lock the rest down).

**Secure Comic Content**
Check this box to also require users be logged in to read any TEXT content on a comic post.  Most of the Content Restriction options are focused on hiding the comic IMAGES, this one expands it to the text content as well.  But ONLY on comic posts. This has no effect on regular blog posts or pages or any other custom post-type.

**Secure Shortcode Comics**
If you would like even comics placed with the "Insert Comic" shortcode to normally require users be logged in to see them (however, you can override this on a case-by-case basis).  Basically it just flips the default from inserted comics normally being visible to normally not being visible unless a user is logged in.


### Social Media

**Rating System**
A built-in system for rating comics. _This is not tied to any social media platform._ Choose between:
* _None_ - self-explanatory, there will be no rating system.
* _Five-Star Ratings_ - When a user leaves a comment they can rate the comic out of five stars. The rating is tied to the comment, posts will show the averaged rating.
* _Post Likes Ratings_ - Users can press a "Like" button, rating is tied to post, post shows tally of all likes, nobody else knows what a user liked.

**Require Star Rating**
_If Rating System_ is set to "Five-Star Ratings" checking this box will make rating the comic a requirement to submit a comment. Once a user has rated a comic they will not be asked to rate it again. _Requiring rating a comic is not recommended._

**Post Like Style**
If you selected "Post Like Ratings" under _Rating System_ this setting determines how the "Likes" look.
* _Custom/Theme_* - looks however you style it in your theme.
* _Thumbs-Up_ - looks kind of like Facebook's Like buttons.
* _Heart_ - like button has a little heart in a red circle.
* _Star_ - like button has a little star in a gold circle.

**Post Likes Button Text**
Text to appear on the Like Button (default is "Like")

**Post Unlike Button Text**
Text to appear on Unlike Button (default is "Unlike"). When someone has already liked a comic they have the option to "unlike" it.

**Post Like Action Name**
Text to describe the action of having liked a comic (default is "liked"). For example if you changed the button text to "Favorite" this would be "favorited" or if you changed it to "Love" this would be "loved."

The options below will add social media sharing meta data to your website. If you are using an SEO or social media plugin it probably already does that so you may want to leave these blank/disabled. If you are not using any SEO or social media plugins and want to make it easier for readers to share your content consider enabling some or all of these options.

**Facebook OpenGraph Meta**

**Default Facebook Image:** If a post has no “Featured Image” this is the image that will be displayed as the thumbnail when somebody shares the link on Facebook (if left blank ComicPost will _not_ inject _any_ Facebook `<meta>` tags).

**Bluesky:** Ticking this box adds meta tags for sharing link cards on Bluesky.

**Mastodon ID:** Enter the @username@instance ID associated with your Mastodon account and the Mastodon verification code will be added to your website.

### Admin Settings

**Thumbnail Size in Manage Posts**
Adjusts the thumbnail size shown in the _Comics > All Comics_ post management page on the backend of the site. You can adjust it from 50 pixels to 300 pixels wide.

**Remove Admin Bar**
That black bar that normally appears across the top of your website when you're logged in?  This will remove it from the FRONT end of your website. I hate that bar, it's ugly. For most of your users it is useless. I always have an option to disable it.

### Shortcodes

**Insert Comic**
Allows you to insert a comic anywhere with a link to the comic post. By design it can show comics regardless of required login or hiding old comics. It can add additional security features above the global settings but not reduce them.
*Parameters:*
* _comicnav_ = "true|false" : whether or not to include comic navigation below the comic image.
* _size_ = "thumbnail|medium|large|full" : the size of comic image to display
* _protect_ = "encode,glass,noprint" : single or comma-separated list of protections to apply.
* _orderby_ = "ASC|DESC : start at beginning or start at end, ignored if single.
* _number_ = "1" : offset from start/end (depending on orderby)
* _chapter_ = "slug_for_chapter" : which chapter to grab, ignored if single.
* _single_ = "true|false" + id="12" : shows comic post by ID number.

*Example Usage:*
`[insertcomic size="full" chapter="chapter-one" orderby="DESC" comicnave="true"]`
`[insertcomic size="large" single="true" id="8045" protect="glass"]`
`[insertcomic size="medium" chapter="chapter-three" orderby="ASC" number="5" comicnav="true"]`

**Archive Comic List**
Adds a unordered list of Comic Chapters anywhere. When a user selects a chapter from the list they are immediately taken the Archives for that chapter.
*Parameters:*
* _include_ = "1,slug,Name" : single or comma-separated list of chapter IDs, slugs, or names to include. Will not automatically include tree of sub-chapters.
* _exclude_ = "1,slug,Name" : single or comma-separated list of chapter IDs, slugs, or names to exclude. WILL automaticlaly exclude the tree of sub-chapters.
* _emptychaps_ = "show|hide" : whether or not to show chapters with no comic posts in them
* _thumbnails_ = "true|false" : whether or not to show chapter thumbnail or not. It uses the image from the first post in the chapter, assuming there is one.
* _order_ = "ASC|DESC" : whether to display the list in ascending or descending order.
* _orderby_ = "name|slug|ID" : what to order the list by, remember that name and slug are sorted alphabetically.
* _postdate_ = "first|last" : whether the chapter date shown should be by the first or last comic posted in it.
* _dateformat_ = "site|Y-m-d" : whether to use the date format for the site defined in Settings > General or some other date format.
* _description_ = "true|false" : whether to include the Chapter Description or not. This is instended for short descriptions like "#1" or "Ep.1" If it is longer you will need to custom style the list to display it.
* _comments_ = "true|false" : whether to include a count of the total number of comments on all posts in the chapter.
* _showratings_ = "true|false" : whether to include the cumulative ratings for the chapter (only works if you have enabled either Post Likes or Five-Star Ratings).
* _liststyle_ = "flat|indent|custom" : the unordered list style. The "indent" option visually indicates the chapter hierarchy by shifting sub-chapters to the right. You can also declare a class name (list-style-custom) for custom styling, where "custom" is whatever you want.
* _title_ = "Chapters|custom" : This is the title of the Chapter List, if any. You could change this to "Episodes" or "" for no heading.

*Example Usage:*
`[comicpost-chapter-list]` (would show all chapters and subchapters with default layout)
`[comicpost-chapter-list exclude="124,chapter-one,Title Three"]`
`[comicpost-chapter-list thumbnails="false" comments="false" postdate="false" liststyle="indented"]` (barebones hierarchical list of just chapter titles)
`[comicpost-chapter-list dateformat="Y/m/d" description="true" showratings="true"]` (would show all elements plus using a custom date format)

**Archive Drop-Down**
Adds a drop-down list of Comic Chapters anywhere. When a user selects a chapter they are immediately taken to the Archives for that chapter.
*Parameters:*
* _include_ = "1,slug,name" : single or comma-seperated list of chapter IDs, slugs, or names to include. Will NOT automatically include tree of sub-chapters.
* _exclude_ = "1,2,3,4" : single or comma-separted list of chapter IDs, slugs, or names to exclude. WILL automatically excluse tree of sub-chapters.
* _emptychaps_ = "show|hide" : whether or not to include chapters that have no comic posts in them.
* _title_ = "Select Chapter|custom" : first item in the drop-down says what it selects. If set to "" it uses the default title.

*Example Usage:*
`[comicpost-archive-dropdown]` (would show ALL chapters and sub-chapters)
`[comicpost-archive-dropdown exclude="124,142,143,168"]` (excludes 4 chapters and all their sub-chapters).

**Social Sharing Buttons**
Adds Social Media sharing buttons for your readers to share content to their own social media account.
*Parameters:*
* _type_ = "text|label|small|medium|large" : type and size of button to show
* _include_ = "facebook,threads,mastodon..." : list of social buttons to include
* _exclude_ = "bluesky,linkedin,rss..." : list of social buttons to exclude

*Valid Social Sites:*
facebook, threads, bluesky, mastodon, tumblr, reddit, linkedin, pinterest, rss, email

*Example Usage:*
`[comicpost-share type="large" include="facebook,threads,mastodon"]` (would show three 32x32 pixel social sharing icon buttons)
`[comicpost-share type="label" exclude="rss,email"]` (would show 8 buttons with small icons and text labels, omitting RSS and email buttons).

**Data Encoder**
The same functions that can encode your comic URLs to hide them from spambots and scrapers can be used to protect ANY arbitrary text content on your site via the "protect" shortcode. The shortcode works whether you enabled "Encode Comic URLs" or not.
*Parameters:* _(all are optional)_
* _key_ = "0-255" : optional value for encoding, omit and it picks a random number.
* _type_ = "mailto:|tel:+1|calto:|skype:" : any valid <a> tag protocol.
* _placeholder_ = "Hidden Content" : what the protected element should say on it, if anything.

*Example Usage:*
`[protect]Arbitrary content[/protect]`
`[protect type="mailto:"]jane.doe@example.com[/protect]`
`[protect type="tel:+1"](555) 555-5555[/protect]`
`[protect placeholder="Hidden from View Source"]Arbitrary content we want hidden[/protect]`

This gets encoded on the server side into a long string. For example, the phone number above might turn into: "4c64797979656c7979796179797979" which most bots would not see as a phone number when crawling the page source code. A JavaScript on the frontend of the site decodes it. Bots or scrapers that use rendered content would not be fooled, so do not rely on this. Any data you do not want any bots or scrapers getting should be placed behind a login.

**Non-Public**
A shortcode to Content Restrict anything so only logged-in users can see it. Note that this does not take eithr roles or capabilities into account, it ONLY checks whether or not the user is logged in or not.
*Parameters:* _(optional)_
* _placeholder_ = "Members Only Content" : what the protected element should say on it, if anything.

*Example Usage:*
`[nonpublic]Arbitrary content[/nonpublic]`
`[nonpublic placeholder="Special Offer for Registered Users! Be sure to log in to see it."]50% OFF for all orders today using the coupon code "2202"[/nonpublic]`

**User Ratings List**
A shortcode to display a list of which comic posts a user has rated and how many stars they gave it. The intended use is on a frontend user dashboard or profile. Only the user themselves can see this information.
*Parameters:* _(optional)_
* _from_ = "1 year ago" : start date for comments with ratings.
* _to_ = "tomorrow" : end date for comments with ratings.
from/to can be any valid PHP strtotime() English date format.
comics = "thumbnail|medium|large|full|none" : whether to include a comic thumbnail or not and what size.
* _dummy_ = "true|false|placeholder" : show/hide list if empty, or show and populate with placeholders to maintain layout.
Use "true" if styling in your theme, "placeholder" for default styling

*Example Usage:*
`[userratings]` (default parameters) 
`[userratings comic="medium"]` (larger comic image)

**Top Comics List**
A shortcode to display a list of the top-rated comics on the website. Obviously this only works if you have enabled the Ratings System under the Social Media tab. By default the layout is the same as the Chapter List.
*Parameters:*
* _comic_ = "none|thumbnail|medium" : whether to include a thumbnail and what size (only two sizes are available).
* _number_ = "5|n" : number of comics to show. Default is a "Top Five" list.
* _showrating_ = "true|false" : whether to show the number or Likes or Stars
* _rank_ = "true|false" : whether to include the rank number in front of the title.
* _postdate_ = "true|false" : whether to include the post date or not.
* _dateformat_ = "site|Y-m-d" : "site" uses the setting under Settings > General, or any valid date format.
* _comments_ = "true|false" : whether to show comment count or not.
* _from_ = "1 year ago" : (optional) if you want to limit the date range for the top comics list. In this example it would only retrieve comics up to 1 year ago instead of the default which is all comics from all time.
* _to_ = "tomorrow" : (optional) if you want to limit the date range for the top comics list include the end date for the range. In this case through tomorrow.
* _title_ = "" : Anything you want additionally put after the "Top Comics" header.
* _liststyle_ = "flat|custom" : the class for the unordered list style.
* _chapters_ = "1,slug,Name" : (optional) comma-separated list of term IDs, slugs, or Chapter Names to limit the list. If omitted the default is all comics from all chapters. If you limit the list you may want to add something to the title indicating what chapter(s) are from."

*Example Usage:*
`[topcomics number="10" comments="false"]` (shows at "Top 10" without comment count)
`[topcomics comic="none" showrating="false" postdate="false" comment="false"]` (a bare-bones "Top 5" list with just the comic title and rank number.

**User Likes List**
A shortcode to display a list of which comic posts a user has "liked." The intended usage is on a frontend user dashboard or profile. Only the user themselves can see this information.
*Parameters:* _(optional)_
* _comic_ = "thumbnail|medium|large|full|none" : whether to include a comic thumbnail or not and what size.
* _dummy_ = "true|false|placeholder" : show/hide list if empty, or show and populate with placeholders to maintain layout.
Use "true" if styling in your theme, "placeholder" for default styling

*Example Usage:*
`[userlikes]` (default parameters)
`[userlikes comic="large"]` (large comic image)

**User Comments List**
A shortcode to display a list of comments the user has made on the site. The intended usage is on a frontend user dashboard or profile. Only the user themselves can see this information.
*Parameters:* _(optional)_
* _from_ = "1 year ago" : start date for getting comments.
* _to_ = "tomorrow" : end date for getting comments.
from/to can be any valid PHP strtotime() English date format.
* _dummy_ = "true|false|placeholder" : show/hide list if empty, or show and populate with placeholders to maintain layout.
Use "true" if styling in your theme, "placeholder" for default styling

*Example Usage:*
`[usercomments]` (default settings, comments from 1 year ago through today)
`[usercomments from="10 September 2016" to="last Monday"]`


## Changelog

Version 0.1

* Initial public release.

## Developers

K.M. Hansen (@kmhcreative) - Lead Developer
http://www.kmhcreative.com

Includes some code from other projects:

Philip Hofer (@frumph) http://frumph.net

Benjamin T. McCormick (Tovias) http://www.racomics.com

Tyler Martin (@mindfaucet) http://mindfaucet.net

Mary Varn npccomic.com

## License

GPLv3 or later
http://www.gnu.org/licenses/gpl-3.0.html

