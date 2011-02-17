=== CataBlog ===
Contributors: macguru2000
Donate link: http://catablog.illproductions.com/donate/
Tags: admin, image, images, posts, Post, links, catalog, gallery, portfolio, product catalog, discography, library, collection, store, organize, media, photo, thumbnail, product, listing, list
Requires at least: 3.0
Tested up to: 3.1
Stable tag: 1.1

CataBlog is a comprehensive and effortless tool that allows you to create catalogs, stores and galleries for your blog.



== Description ==

- ANOTHER BETA AVAILABLE, CHECK OTHER VERSIONS FOR CATABLOG 1.1.6

CataBlog allows you to catalog pretty much anything you would want and share it through your blog in a simple but elegant gallery. Upload images, give them titles, links, descriptions and then save them into your catalog. Use categories to organize and filter your catalog items into multiple different catalogs. Show off your photos in high resolution too with the LightBox effect, perfect for simple galleries. Easy, intuitive and smart design makes it trivial to keep track of all your different catalogs and create amazing e-stores, galleries, lists and more.

There may also be beta versions available in the [other versions section](http://wordpress.org/extend/plugins/catablog/download/ "Download Other Versions of CataBlog"), check the [Changelog](http://wordpress.org/extend/plugins/catablog/changelog/ "CataBlog Versions Change Log") to see what new features were added.

If you have a support question, please open a new support ticket here or check out the documentation before contacting me, thanks.

Highlighted Features:

* Import and Export features now supports CSV format.
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

Please remember that CataBlog is written, maintained, supported and documented by Zachary Segal. Please feel free to stop by and visit http://catablog.illproductions.com and http://www.illproductions.com for more information about CataBlog and Zachary anytime.


== Installation ==

1. Upload `catablog` to the `/wp-content/plugins/` directory
1. Activate the plugin through the `Plugins` menu in WordPress
1. If you want you may also network activate CataBlog. Note that each new site you make will require an installation step, simply do so when reminded and all will be good.
1. Create catalog items by uploading image files
1. Sprinkle the `[catablog]` and `[catablog category='dog']` ShortCodes throughout your blog to show your catalog



== Frequently Asked Questions ==

= Where is the documentation =

[Click Here](http://catablog.illproductions.com/documentation/introduction-and-installation/ "CataBlog Documentation") for the official documentation.

= What browsers do you support =

The CataBlog Admin section is made and tested to work best with these browsers:

1. Internet Explorer 8
1. FireFox 3
1. Safari 5
1. Chrome 8
1. JavaScript Should Be Enabled

= I installed CataBlog, now where is it? =

Look for CataBlog in your WordPress Admin Panel right underneath the Comments section.

= How do I add a new item to my catalog? =

Login to the Admin Panel of your WordPress blog and go to the CataBlog section by clicking its icon right below the Comments section. Now you can click "Add New" next to the page title or in the CataBlog menu itself.

= How do I customize my catalog's layout? =

You can easily override CataBlog's CSS classes to create your own design and easily incorporate CataBlog into your site's layout. The recommended way to do this would be to create a catablog.css style file in your theme's directory and add your CSS override code in there. Read more about it here http://catablog.illproductions.com/documentation.

= Where can I learn more about CataBlog? =

Go to http://catablog.illproductions.com, it is a place to learn and share more about your experiences with CataBlog.



== Screenshots ==

1. Use the list view for a bird's eye of the entire catalog.
2. Use the grid view for bigger thumbnails and easier sorting.
3. Easy and familiar forms for making and editing catalog items.
4. Turn your catalog into a photo gallery using custom templates.
5. Display your catalog photos in high resolution with the LightBox.



== Changelog ==

= 1.1.6 =
* Fix: Better multiple image per catalog item support.
* Fix: catablog-image CSS class reverted to stop upgrade bugs.
* Fix: Default template reverted to stop upgrade bugs and new subimages template added.
* Fix: Optimized next and previous item links in edit panel.
* Fix: Long item descriptions are truncated in the admin list view.
* Fix: Successful form submissions now forwards you to the appropriate url.
* Fix: Lazy loaded thumbnail images in admin list view refined.
* Fix: Better file upload validation and error messages.
* New: List view now renders the description in html
* New: Template drop down menus now lists all files in the directory.

= 1.1.5 =
* New: Added multiple image per catalog item support.
* New: Thumbnail images in the admin list view are now lazy loaded.
* New: Navigation controls in the admin edit panel.
* Fix: Rendering thumbnail and fullsize images are now separate threads.

= 1.1 =
* New: Added CSV (comma separated values) support for importing and exporting catalog data.

= 1.0.2 =
* New: New grid template default for showing product grids that use the item's link.
* New: Title link relationship field now in title options.
* New: Support for inserting tab characters into the template code.
* New: Only load CataBlog frontend JavaScript and CSS on pages with the shortcode.
* New: Thumbnail preview now has an image in options panel.
* Fix: Updated gallery template default to link to the full size image.
* Fix: Title link target attribute is now free form text in title options.

= 1.0.1 =
* New: Added %IMAGE-FULLSIZE% token to the HTML template feature.

= 1.0 =
* New: Rescan the originals folder for new images to add to the database.
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



== Upgrade Notice ==

= 1.1.6 =

Beware: this version is not ready for prime time and contains some experimental features and code.

= 1.1.5 = 

Beware: this version is not ready for prime time and contains some experimental features and code.

= 1.0.2 =

Beware, CataBlog's JavaScript and CSS file may not load on a page that only invokes the template function and doesn't use the shortcode.

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



