=== If Menu ===
Contributors: andrei.igna
Tags: menu, if, rules, conditional, statements, hide, show, dispaly, roles, nav menu
Requires at least: 4
Tested up to: 4.8
Stable tag: trunk
License: GPL2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Display or hide menu items with user-defines rules

== Description ==

**If Menu** is a WordPress plugin which adds extra functionality for menu items, making it easy to hide or display menu items based on user-defined rules. Example:

* Display a menu item only if current `User is logged in`
* Hide menu items if `visiting from mobile device`
* Display menu items just for `Admins and Editors`

The plugin is easy to use, each menu item will have a “Change menu item visibility” option which will enable the selection of rules (example in Screenshots).

## Features

* Basic set of visibility rules
  * User state `User is logged in`
  * User roles `Admin` `Editor` `Author` etc
  * Page type `Front page` `Single page` `Single post`
  * Device `Is Mobile`
  * Language `Is RTL`
  * *more to be added with each plugin update*
* Multiple rules - mix multiple rules for a menu item visibility
  * show if `User is logged in` AND `Device is mobile`
  * show if `User is Admin` AND `Is front page`
* Support for adding your custom rules

Example of adding a new rule is described in the FAQ section

== Installation ==

To install the plugin, follow the steps below

1. Upload `if-menu` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Enable visibility rules for your Menu Items in Appearance -> Menus page

== Frequently Asked Questions ==

= If Menu is broken =

The code for modifying the menu items is limited and if other plugins/themes try to alter the menu items, this plugin will break.

This is an ongoing [issue with WordPress](http://core.trac.wordpress.org/ticket/18584) which hopefully will be fixed in a future release.

Try to use just one plugin that changes functionality for menu items.


= How can I add a custom rule for menu items? =

New rules can be added by any other plugin or theme.

Example of adding a new custom rule for displaying/hiding a menu item when current page is a custom-post-type.

`
// theme's functions.php or plugin file
add_filter('if_menu_conditions', 'my_new_menu_conditions');

function my_new_menu_conditions($conditions) {
  $conditions[] = array(
    'id'        =>  'single-my-custom-post-type',                       // unique ID for the rule
    'name'      =>  __('Single my-custom-post-type', 'i18n-domain'),    // name of the rule
    'condition' =>  function($item) {                                   // callback - must return Boolean
      return is_singular('my-custom-post-type');
    }
  );

  return $conditions;
}
`

= Where do I find conditional functions? =

WordPress provides [a lot of functions](http://codex.wordpress.org/Conditional_Tags) which can be used to create custom rules for almost any combination that a theme/plugin developer can think of.

= Who made that really cool icon =

Got the icons from here https://dribbble.com/shots/1045549-Light-Switches-PSD, so giving the credit to Louie Mantia

== Screenshots ==

1. Enable visibility rules for Menu Items
2. Example of visibility rules

== Changelog ==

= 0.6.3 =
*Release Date - 17 August 2017*

* New visibility rule - Language Is RTL
* Fix - Single rule works on servers with Eval disabled

= 0.6.2 =
*Release Date - 8 August 2017*

* Fix - Backwards compatibility with PHP < 5.4

= 0.6.1 =
*Release Date - 7 August 2017*

* Improvement - Change labels & texts for easier use
* Improvement - Better compatibility with latest versions of WordPress
* Improvement - Better compatibility with translation plugins
* Fix - Detection for conflict with other plugins

= 0.6 =
*Release Date - 27 August 2016*

* Improvement - Dynamic conditions based on default & custom user roles (added by plugins or themes) [thanks Daniele](https://wordpress.org/support/topic/feature-request-custom-roles)
* Improvement - Grouped conditions by User, Page or other types
* Fix - Filter menu items in admin section
* Fix - Better menu items filter saving code

= 0.5 =
*Release Date - 20 August 2016*

* Improvement - Support for WordPress 4.6
* Feature - New condition checking logged in user for current site in Multi Site [requested here](https://wordpress.org/support/topic/multi-site-user-is-logged-in-conditi
on)
* Feature - Added support for multi conditions [thanks for this ideea](https://wordpress.org/support/topic/more-than-one-condition-operators-1)
* Improvement - RO & DE translations

= 0.4.1 =
*Release Date - 13 December 2015*

* Fix - Fixes [issue](https://wordpress.org/support/topic/cant-add-items-to-menu-with-plugin-enabled) with adding new menu items

= 0.4 =
*Release Date - 29 November 2015*

* Improved compatibility with other plugins/themes using a [shared action hook for menu item fields](https://core.trac.wordpress.org/ticket/18584#comment:37)
* Enhancement - show visibility status in menu item titles

= 0.3 =

Small update

* Plugin icon
* Set as compatible with WordPress 4

= 0.2.1 =

Minor fixes

* [Fix](https://twitter.com/joesegal/status/480386235249082368) - Editing menus - show/hide conditions when adding new item (thanks [Joseph Segal](https://twitter.com/joesegal))

= 0.2 =

Update for compatibility with newer versions of WordPress

* [Feature](http://wordpress.org/support/topic/new-feature-power-to-the-conditions) - access to menu item object in condition callback (thanks [BramNL](http://wordpress.org/support/profile/bramnl))
* [Fix](http://wordpress.org/support/topic/save-is-requested-before-leaving-menu-page) - alert for leaving page even if no changes were made for menus (thanks [Denny](http://wordpress.org/support/profile/ddahly))
* Fix - update method in `Walker_Nav_Menu_Edit` to be compatible with newer version of WP
* [Fix](http://wordpress.org/support/topic/bugfix-for-readmetxt) - example in Readme (thanks [BramNL](http://wordpress.org/support/profile/bramnl))

= 0.1 =
* Plugin release. Included basic menu conditional statements
