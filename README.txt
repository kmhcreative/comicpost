=== ComicPost ===
Author URI: http://www.kmhcreative.com
Plugin URI: https://github.com/kmhcreative/comicpost
Contributors: kmhcreative
Tags: comics, webcomics, webtoon
Requires at least: 3.5
Requires PHP: 5.3
Tested up to: 6.7.1
Stable Tag: 0.1
License: GPLv3
Licence URI: http://www.gnu.org/licenses/gpl-3.0.html

A simple webcomic plugin with content restriction options to prevent AI scraping.

=== Description ===

ComicPost is a relatively lightweight and simple webcomics plugin that *should* work with pretty much any theme that displays Featured Images with posts. This also may be the first WordPress webcomics plugin to specifically address the issue of Artificial Intelligence image scrapers stealing and training on comic artwork without permission or compensation. To combat that there are a number of **Content Restriction** options and a built-in automatic **Watermarking** feature.

This plugin is intended as an easy (but not drop-in) replacement for ComicPress/Comic Easel because it uses the same default custom post-type and chapter taxonomy, so any existing comics will automatically show up in it and work with it without having to migrate/import anything. But a prior installation of either ComicPress or Comic Easel are not necessary.

== Beta Version Disclaimer ==

This plugin is still being tested.  It seems pretty solid, but use it at your own risk.

== Installation ==

= Using Admin Upload =

1. Download the GitHub archive as a ZIP file.
2. Go to your _Dashboard > Plugins > Add New_ and press the "Upload Plugin" at the top of the page.
3. Browse to where you downloaded the ZIP file and select it.
4. Press the "Install Now" button.
5. On your _Dashboard > Plugins_ page activate "ComicPost"
6. Go to _Dashboard > Comics > Options_ and configure it.

= Using FTP =
  
2. Unzip it so you have a "comicpost-master" folder
3. FTP upload it into your WordPress blog’s _~/wp-content/plugins/_ folder.
4. Go to your _Dashboard > Plugins_ and activate “ComicPost”
5. Go to _Dashboard > Comics > Options_ to configure it.

== Features ==

= Comic Management =
* Custom comic post-type
* Custom chapters hierarchical taxonomy
* Schedule comic posts
* Host multiple series/titles on one site
* Integration with Ryuzine Press
* Integration with ZappBar

= Watermarking =
* Automatically watermark comics on upload
* Generated or Custom watermark image
* Tiled or Centered
* Optionally retain/display "clean" copies

= Content Restriction =
* Disable right-click or drag-to-download of comics
* Watermark or Disable printing comic images
* Encode comic URLs
* Omit comics from galleries
* Hide Old Comics
* Require Login

= Social Media =
* Choose between TWO built-in comic rating systems:
	* Five-Star Ratings tied to user comments
	* Comic Post "Like" button
	* Or use no rating system at all
* Include OpenGraph social media headers
* Add Mastodon verification code

= Shortcodes =
* Insert Comic
* Archive Chapter List
* Archive Drop-Down List
* Social Media Share Buttons
* Data Encoder
* Members-Only Content
* Top Comics List
* User Ratings List
* User Likes List
* User Comments List

== Changelog ==

= Version 0.1 =
* Initial public release