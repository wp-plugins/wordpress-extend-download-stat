=== Wordpress Extend Download Stat ===
Contributors: Zen
Donate link: http://zenverse.net/support/
Tags: download, statistics, number, wordpress, extend, theme, plugin
Requires at least: 2.0.2
Tested up to: 2.8.3
Stable tag: 1.0

Wordpress Extend Download Stat can retrieve the download stats of plugin or theme hosted at wordpress and display it using your preferred format.

== Description ==

Sometimes you need to display the number of downloads of your plugin or theme hosted by wordpress, Wordpress Extend Download Stat can retrieve it for you and display it using your preferred format. The retrieved data will be stored in your local server and you decide when it should re-synchronize the data.

**Features**

*   Retrieve download statistics and download URL by one click
*   It stores the statistics data in your local server
*   Auto synchronize the outdated data in the background using Ajax
*   Manage the plugin's behaviour and saved data at Plugin Option page
*   You can create custom format at plugin option page and use it for output
*   To make it easier, you can use media button to add downlaod stat to post (see screenshot : Media Button)


**Usage**

*   To output download stat in blog post, use shortcode `[downloadstat]` in your post content / excerpt.
*   To make it easier, you can use the media button (see screenshot for more info) and follow the steps given.
*   See the link below for more info

[How to use the shortcode](http://zenverse.net/wordpress-extend-download-stat-plugin/#usage) | [Plugin Page](http://zenverse.net/wordpress-extend-download-stat-plugin/) | [Plugin Author](http://zenverse.net/)

== Installation ==

1. Download the plugin package
2. Extract and upload the "wordpress-extend-download-stat" folder to your-wordpress-directory/wp-content/plugins/
3. Activate the plugin and its ready
4. Go to Admin Panel > Settings > WP Ex Download Stat and customise it to suit your needs.

== Frequently Asked Questions ==

= I can't add new data =
You need to use the URL to the statistics page, not to the main page. First of all, check whether the URL you type is the URL to the statistics page (it ends with /stats/). For example, URL to my wordpress theme demo bar plugin is `http://wordpress.org/extend/plugins/wordpress-theme-demo-bar/` and URL to its statistics page is `http://wordpress.org/extend/plugins/wordpress-theme-demo-bar/stats/`. 

= How to display content inline =
Add autop="false" to the shortcode. For example, `Downloaded [downloadstat url="" autop="false" get="total] times`

== Screenshots ==
1. Add download stat to post easily using Media Button
2. Add download stat to post easily using Media Button
3. Plugin Option Page

== Changelog ==
= 1.0 =
First version of Wordpress Extend Download Stat