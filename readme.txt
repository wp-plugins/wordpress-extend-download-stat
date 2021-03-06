=== Wordpress Extend Download Stat ===
Contributors: Zen
Donate link: http://zenverse.net/support/
Tags: download, statistics, number, wordpress, extend, theme, plugin
Requires at least: 2.0.2
Tested up to: 3.0.5
Stable tag: 1.3.1

Wordpress Extend Download Stat can retrieve the download stats of plugin or theme hosted at wordpress and display it using your preferred format.

== Description ==

Sometimes you need to display the number of downloads of your plugin or theme hosted by wordpress, Wordpress Extend Download Stat can retrieve it for you and display it using your preferred format. The retrieved data will be stored in your local server and you decide when it should re-synchronize the data.

**Features**

*   Retrieve download statistics and download URL by one click
*   It stores the statistics data in your local server
*   Auto synchronize the outdated data in the background using Ajax
*   Manage the plugin's behaviour and saved data at Plugin Option page
*   You can create custom format at plugin option page and use it for output
*   To make it easier, you can use media button to add download stat to post (see screenshot : Media Button)
*   Template tag function is available if you want to display stats in your template (see links below)
*   Template tag function is available if you want to make a "download" page (see links below)
*   Quickly load all stats of your plugins/themes using your wordpress extend username at plugin option page > Add New Data


**Usage**

*   To output download stat in blog post, use shortcode `[downloadstat]` in your post content / excerpt.
*   To make it easier, you can use the media button (see screenshot for more info) and follow the steps given.
*   See the link below for more info about using shortcode and template tag function

[How to use shortcode](http://zenverse.net/wordpress-extend-download-stat-plugin/#usage) | [How to use template tag functions](http://zenverse.net/using-template-tag-function-in-wordpress-extend-download-stat-plugin/) | 
[Plugin Page](http://zenverse.net/wordpress-extend-download-stat-plugin/) | [Plugin Author](http://zenverse.net/)

== Installation ==

1. Download the plugin package
2. Extract and upload the "wordpress-extend-download-stat" folder to your-wordpress-directory/wp-content/plugins/
3. Activate the plugin and its ready
4. Go to Admin Panel > Settings > WP Ex Download Stat and customise it to suit your needs.

== Frequently Asked Questions ==

= I can't add new data =
You need to use the URL to the statistics page, not to the main page. First of all, check whether the URL you type is the URL to the statistics page (it ends with /stats/). For example, URL to my wordpress theme demo bar plugin is `http://wordpress.org/extend/plugins/wordpress-theme-demo-bar/` and I should use `http://wordpress.org/extend/plugins/wordpress-theme-demo-bar/stats/`. 

= How to add download stat to post easily? =
Easiest method is to use the media button when you create or edit post (see screenshot : media button)

= How to display content inline =
Add autop="false" to the shortcode. For example, `Downloaded [downloadstat url="" autop="false" get="total] times`

== Screenshots ==
1. Add download stat to post easily using Media Button
2. Add download stat to post easily using Media Button
3. Plugin Option Page

== Changelog ==
= 1.3.1 =
* Fixed the item type (plugin or themes) problem.

= 1.3 =
* Fixed the unable to retrieve data problem, due to wordpress changed their website html codes & structure.

= 1.2.7 =
* Replaces one more `str_ireplace` function with `str_replace` in functions.php (I missed that in previous update)
 
= 1.2.6 =
* Replaces the `str_ireplace` function with `str_replace` so that users with PHP4 can use the plugin (please use lowercase letter for tags)
 
= 1.2.5 =
* Fixed some part that breaks when single or double quotes were used (eg: in name / format)

= 1.2.4 =
* Template Tag functions now accept arguments in array form
* Added 3 buttons under  "Plugin Support & Extra" menu for you to resync all data or delete all options

= 1.2.3 =
* Changed the default format to a more normal and suitable format
* Removed autop in template tag `wptdb_output()` (it should not have that option)

= 1.2.2 =
* Added `autoformat` to template tag function `wpeds_return_data_as_array`

= 1.2.1 =
* Formatted the numbers at the stats table (forgot to do that in version 1.2)
* Added template tag function `wpeds_return_data_as_array` (you might need it to make a "download" page)

= 1.2 =
* Added number format option
* Added resync all data feature
* Added "Overview" in plugin option page
* Improved some regular expression pattern
* Allows user to disable auto refresh after submit form in plugin option page
* Added feature : Add All Plugins/Themes created by (Wordpress Extend) Username

= 1.1 =
* Added template tag function `wpeds_output`
* Added error message for invalid shortcode
* Fixed a small mistake in auto resync-ing the data
* Added more definitions for outdated data (for you to run auto sync more frequently)
 
= 1.0 =
* First version of Wordpress Extend Download Stat