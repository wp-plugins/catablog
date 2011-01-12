=== CataBlog ===
Contributors: macguru2000
Donate link: http://catablog.illproductions.com/donate/
Tags: plugin, admin, image, images, posts, Post, page, links, catalog, gallery, discography, library, collection, paypal, organize, media, photo, thumbnail, product, rolodex, manifest, listing, list, category, categories, custom post type, custom post, custom taxonomy
Requires at least: 3.0
Tested up to: 3.1
Stable tag: 1.0

CataBlog is a comprehensive and effortless tool that allows you to create catalogs and galleries for your blog.

== Description ==

CataBlog allows you to catalog pretty much anything you would want and share it through your blog in a simple but elegant gallery. Upload images, give them titles, links, descriptions and then save them into your catalog. Use categories to organize and filter your catalog items into multiple different catalogs. Show off your photos in high resolution too with the LightBox effect, perfect for simple galleries. Easy, intuitive and smart design makes it trivial to keep track of all your different catalogs and create amazing e-stores, galleries, lists and more.

If you have a support question, please open a new support ticket here or check out the documentation at the link below before contacting me directly, thanks.
http://catablog.illproductions.com/documentation/introduction-and-installation/

Highlighted Features:

* New script to automatically import image files into the database.
* New Admin Panel Controls: grid view, bulk actions and a category filter.
* Compatible with WordPress MultiSite and Network Activation.
* Use Categories to organize and control your catalog.
* The Options Page is well organized and supports many configurations.
* Template controls let you choose exactly how the HTML tags are rendered.
* Easy management of your catalog with superiorly designed admin controls.
* Import and Export features for saving and loading catalogs.
* Database is modified by native WordPress functions only. 

Pre Version 0.9.5 Upgrades:

* Please export your database to xml before upgrading, just incase.
* Backup the catablog folder in your WordPress site's upload folder.

== Installation ==

1. Upload `catablog` to the `/wp-content/plugins/` directory
1. Activate the plugin through the `Plugins` menu in WordPress
1. If you want you may network activate CataBlog at this point. Note that each new site you make will require an installation step, simply do so when reminded and another individual installation for your new site will be available.
1. Create catalog items by uploading image files
1. Sprinkle the `[catablog]` and `[catablog category='dog']` shortcodes throughout your blog to show your catalog

== Frequently Asked Questions ==

= What browsers do you support =

The CataBlog Admin section is made and tested to work best with these browsers:

1. Internet Explorer 8
1. FireFox 3
1. Safari 5
1. Chrome 8


= I installed CataBlog, now where is it? =

Look for CataBlog in your WordPress Admin Panel right underneath the Comments section.

= How do I add a new item to my catalog? =

Login to the Admin Panel of your WordPress blog and go to the CataBlog section by clicking its icon right below the Comments section. Now you can click "Add New" next to the page title or in the CataBlog menu itself.

= How do I customize my catalog's layout? =

You can easily override CataBlog's CSS classes to create your own design and easily incorporate CataBlog into your site's layout. The recommended way to do this would be to create a catablog.css style file in your theme's directory and add your CSS override code in there. Read more about it here http://catablog.illproductions.com/documentation.

= Where can I learn more about CataBlog? =

Go to http://catablog.illproductions.com, it is a place to learn and share more about your experiences with CataBlog.

== Screenshots ==

1. Use the list view for a bird's eye view of the entire catalog.
2. Use the grid view for bigger thumbnails and easier sorting.
3. Easy and familiar form for making and editing catalog items.
4. Turn your catalog into a photo gallery using custom templates.
5. Display your catalog photos in high resolution with the LightBox.

== Changelog ==

= 1.0 =
* New: Rescan the CataBlog originals folder for new images to add to the database.
* New: Category filter in the CataBlog Admin Panel for quick filter previews.
* New: Grid mode for easier resorting and photo gallery usages.
* New: Bulk selection of items for the delete function.
* Fix: Small interface bugs in LightBox when using an old browser.

= 0.9.9 =
* Fix: Added support for WordPress MultiSite.
* Fix: Lightbox now has keyboard shortcuts for next, previous and close.
* Fix: Lightbox now has a close button in upper right corner.
* Fix: Lightbox has better support for custom template layouts.
* Fix: Sanitization of the title, which is used for the image filename, now removes special characters. 

= 0.9.8 =
* Fix: Creating and deleting categories now have load indicators for slow servers.
* Fix: Having a catablog.css file in your theme directory does not stop the default catablog.css file from loading.
* Fix: Better requirements checking when activating.

= 0.9.7 =
* Fix: Only tries to read EXIF data from images if the function exists.

= 0.9.6 =
* New: Description field options for controlling WordPress filtering and line breaks.
* New: Progress indicator for thumbnail and LightBox image rendering.
* New: Newly added images will read exif orientation and rotate accordingly.
* New: New or edited Item's will store their meta in a single array.
* Fix: Edit Item form CSS now flows properly with the page.
* Fix: Empty tags fields now import to the database correctly.
* Fix: No longer checks unreliable mime-type info when importing XML.
* Fix: All categories are now removed when "Clear All" is checked on XML import.

= 0.9.5 =
* Fix: CataBlog no longer modifies the database directly, preferring built in WordPress functions instead.
* New: Tags have been replaced with a more WordPress like categories feature.
* New: Options page now has a better organized tabbed interface.
* Fix: Various small user interface tweaks and bug fixes.

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
* New: JavaScript Lightbox type effect can now be enabled for larger previews of your catalog item's thumbnails.
* Fix: Catalog items are not sortable in the list view until you enter sort mode, helping stop accidental modifications.

= 0.8.1 =
* Fix: Memory leak fix, the plugin is more efficient with system resources and memory.
* Fix: Now the plugin automatically creates the wp-content/uploads directory on installation if it doesn't already exist.

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

= 1.0 =

Nothing Urgent for CataBlog 0.9.5 and Above.
http://catablog.illproductions.com for more information and specifics.

= 0.9.8 =

No Upgrade Notices for CataBlog 0.9.5 and Above.

= 0.9.7 =

Note: This version should be used over 0.9.6 or 0.9.6.1, thanks!

= 0.9.5 =

WARNING: Complete removal of all database manipulation code from CataBlog. This is very good, as all data storage and retrieval will be delegated by built in WordPress functions. To upgrade you will be required to Export and then Import your catalog data. After a successful Import, you may remove the old data from your database in the CataBlog > Options > Systems section . You should leave the images folder alone, as nothing much has changed when it comes to uploads and storage.

= 0.9.3 =

NOTE: Small upgrade here, no really big changes in the database that a few savings of the view templates wouldn't fix anyways.

= 0.9.2 =

NOTE: Use this version over 0.9.1 in all cases they are identical except one line of code that may cause problems with other plugins.

= 0.9.1 =

NOTE: A small upgrade to the code, nothing major.

= 0.9.0 =

NOTE: A column in the database has been renamed because the original label was a reserved mysql keyword. This update was well tested and should not provide any problems, but just incase you should backup your database with the xml export feature before you upgrade. Thanks.

== Arbitrary section ==


CataBlog is written, maintained, supported and documented by Zachary Segal. Please feel free to stop by and visit http://catablog.illproductions.com and http://illproductions.com for more information about CataBlog and Zachary.



