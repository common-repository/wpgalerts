=== WPGAlerts ===

Contributors: datainterlock

Version: 1.0

License: GNU General Public License v3 or later

License URI: http://www.gnu.org/licenses/gpl-3.0.html

Requires at Least: 3.7

Tested up to: 3.9

Stable tag: trunk

Tags: google, alert, alerts, news

Add Google Alerts to any WordPress Page or Text Widget with a [WPGAlerts] short code.

== Description ==

WPGAlerts allows you to add custom Google Alert articles to your WordPress website. Display up to 20 of these articles including the Author, Content and a Title linked to the full article. WPGAlerts is an easy way to get current, relevant news onto your WordPress site.

== Installation ==

1. Upload all files to the `/wp-content/plugins/wpg-alerts` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Configure WPGAlerts through the 'Settings/WPGAlerts' menu in WordPress.
4. Add Google Alert XML feed links to the WPGAlerts menu in WordPress.
5. Choose which articles to show and check 'Approved' then 'Update'.
6. Add the shortcode [WPGAlerts] to any page or text widget to display the articles.

== Frequently Asked Questions ==

= I'm unable to delete articles =

WPGAlerts checks the alerts whenever you open the WordPress/WPGAlerts menu and updates the articles available. Because of this, any article that is currently listed on the alert will be automatically replaced when you attempt to delete it. The only way to remove the article is to (a) wait for the article to no longer be listed on the alert feed and then delete it or (b) delete the feed and then delete the article. You can also 'Delete Old Articles' which will delete all articles from the database. WPGAlerts will then reload the XML alerts and the database will then only contain the newest articles from the feeds.

= Articles are not updating =

Currently WPGAlerts only updates the articles available when you visit the WPGAlerts page from the WordPress Admin panel. Because all articles require approval, WPGAlerts does not automatically check feeds.

== Screenshots ==

1. The main screen showing both the Google Alerts XML and the articles pulled from that alert.
2. The configuration options under Settings/WPGAlerts
3. Configuring a Google Alert

== Changelog ==
= 1.0 =
*Initial stable release