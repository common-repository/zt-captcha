=== ZT Captcha ===
Tags: captcha, simple captcha, login, register, comment
Requires at least: 4.4
Contributors: teamzt
Tested up to: 6.5.4
Stable tag: 1.0.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires PHP: 7.1
Last Update: 2023-08-29

The captcha plugin keeps WordPress sites safe from spam and password hacks by requiring a simple test to prove you're human, not a computer.

== Description ==

The ZT Captcha plugin is a robust security tool for WordPress websites, offering a range of features that allow administrators to customize the captcha functionality. Here are the key features:

**1.	Form Integration:** Administrators can enable or disable the captcha on various default WordPress forms such as:

- Login form.
- Registration form.
- Reset password form.
- Comment form.

**2.	Captcha Types:** Administrators can select different types of captchas:

- Numeric captcha.
- Alphabetic captcha.
- Mathematical captcha with random option.
- Mathematical captcha with algebraic operations.

**3.	Captcha Customization:** Administrators can customize the captcha string by:

- Including special characters.
- Including uppercase letters.
- Setting a limit on the number of captcha digits.

This plugin is designed to enhance the security of WordPress websites by implementing a human verification test to protect against potential spam and unauthorized access attempts.


== Installation ==

1. Install ZT Captcha either via the WordPress.org plugin directory, or by uploading the files to your server
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to the ZT Captcha menu and set your captcha display settings.

**Installing via FTP**

Login to your hosting space via an FTP software, e.g. FileZilla.   
Unzip the downloaded ZT Captcha by WordPress plugin folder without making any changes to the folder.   
Upload the ZT Captcha by WordPress plugin into the following location wp-content>wp-plugins.   
Login to the WordPress Administrator Panel.   
Activate ZT Captcha plugin by going to Plugins and pressing Activate button.  


== Frequently Asked Questions ==

= Is it work for cutom login,comment,register forms generated from plugins? =

Yes ,it work's if Wordpress standard hooks are used in the forms.

= Is it requires PHP GD extention? =

Yes ,it requires PHP GD extention for more help please go through the page https://bobcares.com/blog/php-install-gd-extension/.


== Screenshots ==

1.  assets/screenshot-1.png
2.  assets/screenshot-2.png
3.  assets/screenshot-3.png

== Upgrade Notice ==

First Release

== Changelog ==

= 1.0 =

 First Release

 = 1.0.1 =

*  New: Added Captcha for woocommerce lost password form.
*  Update: All functionality was updated for WordPress 6.3. 

 = 1.0.2 =

*   Addressed comment form redirect issue, when captcha not verified, ensuring error messages are presented within the same page for a smoother user flow.

*  Update: All functionality was updated for WordPress 6.4.2 

 = 1.0.3 =

*  Update: All functionality was updated for WordPress 6.5.2

 = 1.0.4 =

*  Update: All functionality was updated for WordPress 6.5.4