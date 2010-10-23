=== CataBlog ===
Contributors: macguru2000
Donate link: http://catablog.illproductions.com/donate/
Tags: plugin, admin, image, images, posts, Post, page, links, catalog, gallery, discography, library, collection, paypal, organize, media, photo, thumbnail, product, rolodex, manifest, listing, list
Requires at least: 2.9
Tested up to: 3.0.1
Stable tag: 0.9.3

CataBlog is a comprehensive and effortless tool that allows you to create catalogs and galleries for your blog.

== Description ==

CataBlog allows you to catalog pretty much anything you would want and share it through your blog in a simple but elegant gallery. Upload images, give them titles, links, descriptions and then save them into your catalog. Use tags to organize and filter your catalog items into multiple different catalogs. Show off your photos in high resolution too, now with the new `lightbox effect` which can be turned on in one simple step. Easy, intuitive design makes it child's play to keep track of all your different catalogs and create amazing e-stores, galleries, lists and more.

Upgraders:

* Please export your database to xml before upgrading, just incase.
* Backup the `wp-content/uploads/catablog` folder to your local computer.

Highlighted Features:

* New view controls lets you choose exactly how your catalog's html is rendered.
* Built in view templates for catalogs, galleries and PayPal shopping carts.
* Easy management of your catalog with a superiorly designed admin control panels.
* Import and Export features for loading previously saved CataBlog database backups.
* The LightBox display shows full size photos along with the title, description and next/previous links.

NOTE: please export your database to xml before upgrading, just incase.

PLEASE: read notes about upgrading from versions older then 0.7.6

== Installation ==

1. Upload `catablog` to the `/wp-content/plugins/` directory
1. Activate the plugin through the `Plugins` menu in WordPress
1. Create catalog items by uploading image files
1. Sprinkle the `[catablog]` shortcode throughout your blog to show your catalog

== Frequently Asked Questions ==

= What browsers do you support =

The CataBlog Admin section is made to work best with these browsers:

1. Internet Explorer 8+
1. FireFox 3+
1. Opera 10+
1. Safari & Chrome


= I installed CataBlog, now where is it? =

Look for CataBlog in your WordPress Admin Panel right underneath the Comments section.

= How do I add a new item to my catalog? =

Login to the Admin Panel of your WordPress blog and go to the CataBlog section by clicking its icon right below the Comments section. Now you can click "Add New" next to the page title or in the CataBlog menu itself.

= How do I customize my catalog's layout? =

You can easily override CataBlog's CSS classes to create your own design and easily incorporate CataBlog into your site's layout. The recommended way to do this would be to create a catablog.css style file in your theme's directory and add your CSS override code in there. Read more about it here http://catablog.illproductions.com/documentation.

= Where can I learn more about CataBlog? =

Go to http://catablog.illproductions.com, it is a place to learn and share more about your experiences with CataBlog.

== Changelog ==

= 0.9.3 =
* New: View settings now have separate template code for items and their "buy now" buttons.
* Fix: Lightbox JavaScript effect is now more stable and flexible with html layouts.
* Fix: Hard returns will be rendered as line breaks again.

= 0.9.2 =
* Fix: Bug that could cause page not to render

= 0.9.1 =
* New: Unlock the catablog image folders for easy FTP uploading.
* New: Regenerate all catablog image content with a button click.
* Fix: Some people could not export due to a PHP config conflict.
* Fix: Small user interface tweaks

= 0.9.0 =
* New: Controls of the HTML code rendered by CataBlog.
* New: View templates for general catalogs, galleries and PayPal shopping carts.
* New: Edit item form has been redesigned into a friendlier form.
* Fix: Various tweaks and updates to the admin panel interfaces.

= 0.8.9 =
* Fix: Lightbox is now much prettier and has keyboard left and right arrow support.
* Fix: Base CataBlog CSS classes have more specific display and position properties.

= 0.8.8 =
* New: Set the target attribute for CataBlog generated links.

= 0.8.7 =
* New: Import feature for loading previously saved CataBlog backups.
* Fix: Lightbox next and previous link bugs.

= 0.8.6 =
* New: The title, description and next/previous buttons where added to the Lightbox popup.
* New: Export feature for saving the CataBlog database into a local XML file.
* New: Support for a stylesheet override file, simply add catablog.css in your theme directory.
* Fix: bugs and poor error messages, including a new check that the server is running PHP5.

= 0.8.5 =
* JavaScript Lightbox type effect can now be enabled for larger previews of your catalog item's thumbnails.
* Catalog items are not sortable in the list view until you enter sort mode, helping stop accidental modifications.

= 0.8.1 =
* Memory leak fix, the plugin is more efficient with system resources and memory.
* Now the plugin automatically creates the wp-content/uploads directory on installation if it doesn't already exist.

= 0.8 =
* Stable release candidate
* PayPal support, give CataBlog items a price and product code to easily setup a store.
* Allow users to decide if they want the thumbnails to be a cropped version of the original.
* Supports JPEG, GIF and PNG graphic formats including alpha transparency.

= 0.7.6 =
* Fixed readme.txt file so users will be warned of potential data loss when upgrading.

= 0.7.5 =
* Images now maintain their aspect ratio and are no longer distorted when being resized.
* Background color for resized images can be set in the options page.
* Catalog items can be reordered with a simple drag and drop interface.
* Simplified the installation code to make future upgrades smoother.
* More small tweaks, enhancements and bug fixes.

= 0.7.1 =
* Fixed upgrade bug and numerous other small bugs.

= 0.7.0 =
* You can now tag a catalog item with multiple one word tags.
* The shortcode now supports a tag attribute to filter the displayed items.
* Better form handling and error messages for admin panels.
* Complete code re-factor, plugin is now encapsulated in its own class.

= 0.6.5 =
* This version is the initial release.

== Upgrade Notice ==

= 0.9.3 =

NOTE: Small upgrade here, no really big changes in the database that a few savings of the view templates wouldn't fix anyways.

= 0.9.2 =

NOTE: Use this version over 0.9.1 in all cases they are identical except one line of code that may cause problems with other plugins.

= 0.9.1 =

NOTE: A small upgrade to the code, nothing major.

= 0.9.0 =

NOTE: A column in the database has been renamed because the original label was a reserved mysql keyword. This update was well tested and should not provide any problems, but just incase you should backup your database with the xml export feature before you upgrade. Thanks.

WARNING: If you are upgrading to this version from a version prior to 0.7.5 you will destroy all previously saved CataBlog data. Please back it up as necessary.

= 0.8.7 =

Added import/export of CataBlog data and fixed a few minor bugs and interface quirks.

WARNING: If you are upgrading to this version from a version prior to 0.7.5 you will destroy all previously saved CataBlog data. Please back it up as necessary.

= 0.8.5 =

Relatively minor upgrade offering a new features that lets people enlarge your thumbnails with a lightbox style effect.

WARNING: If you are upgrading to this version from a version prior to 0.7.5 you will destroy all previously saved CataBlog data. Please back it up as necessary.

= 0.8.1 =

ATTENTION: Please upgrade to this version immediately, a bug that let the plugin slowly use all your system RAM has been fixed. The plugin is much more efficient.

= 0.8.0 =

WARNING: If you are upgrading to this version from a version prior to 0.7.5 you will destroy all previously saved CataBlog data. Please back it up as necessary.

= 0.7.6 =

WARNING: If you are upgrading to this version from a version prior to 0.7.5 you will destroy all previously saved CataBlog data. Please back it up as necessary.

= 0.7.5 =

WARNING: If you upgrade to this version you will destroy all previously saved CataBlog data. Please back it up as necessary.

== Arbitrary section ==

Zachary Segal
http://www.illproductions.com
