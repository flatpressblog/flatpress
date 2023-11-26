# Under development: [FlatPress 1.3 "Andante"](https://github.com/flatpressblog/flatpress/releases/tag/1.3)
## Changed requirements
- FlatPress 1.3 runs under PHP up to **8.2**; minimum required PHP version increases to **7.1**.
- Also, the PHP extension [**intl**](https://www.php.net/manual/book.intl.php) becomes mandatory.

## General
- Template engine Smarty updated to version 4.3.1 ([#94](https://github.com/flatpressblog/flatpress/issues/94), [#227](https://github.com/flatpressblog/flatpress/issues/227))<br><sub><i>The Smarty API has changed significantly from v2 to v4 - [please make sure your themes and plugins continue to work with the new Smarty version](https://wiki.flatpress.org/doc:tips:smarty2to4)!</i></sub>
- Added [SECURITY.md](https://github.com/flatpressblog/flatpress/blob/master/SECURITY.md)
- [README](https://github.com/flatpressblog/flatpress/blob/master/README.md): added "help and support" section
- Re-activated useful "Stats" panel in Admin Area / Entries
- "Follow on Mastodon" added as an alternative to X (Twitter) in the welcome entry

## Plugins
### Additions
- PhotoSwipe plugin added: Displays images and galleries with [PhotoSwipe](https://photoswipe.com/) ([#109](https://github.com/flatpressblog/flatpress/issues/109), [#253](https://github.com/flatpressblog/flatpress/issues/253), [#255](https://github.com/flatpressblog/flatpress/issues/255))
- Gallery captions plugin added: Manages image captions for gallery images ([#108](https://github.com/flatpressblog/flatpress/issues/108))
- SEO Meta Tag Info plugin added: Manages SEO meta tags ([#145](https://github.com/flatpressblog/flatpress/issues/145))
- FlatPress Protect plugin added: Adds HTTP headers for hardening your blog ([#146](https://github.com/flatpressblog/flatpress/issues/146))

### Changes
- jQuery plugin: Updated jQuery (3.5.1 => 3.6.1) and jQueryUI (1.12.1 => 1.13.2)
- Media Manager plugin shows 50 items per page, not 10
- BBCode plugin: Added "h4" icon to editor toolbar ([#201](https://github.com/flatpressblog/flatpress/issues/201))
- BBCode plugin: Facebook-Video now uses the latest video player API and the lazy loading mechanism of the browser; also now has localized languages with language tag ([#252](https://github.com/flatpressblog/flatpress/issues/252)) - see also https://developers.facebook.com/docs/javascript/internationalization
- Akismet and comment center plugin revised to enable a more understandable operation ([#273](https://github.com/flatpressblog/flatpress/issues/273))

### Bugfixes
- LastCommentsAdmin plugin will not even attempt to delete or rebuild LastComments caches if LastComments plugin is not available ([#43](https://github.com/flatpressblog/flatpress/issues/43))
- Comment Center plugin: Fixed errors on the config page ([#90](https://github.com/flatpressblog/flatpress/issues/90))
- Comment Center plugin: Fixed error on sending mails with umlaut subjects ([#211](https://github.com/flatpressblog/flatpress/issues/211))
- Akismet plugin: Fixed PHP warnings ([#83](https://github.com/flatpressblog/flatpress/issues/83))
- BBCode plugin: Allows local video files ("attachs/video.mp4") and outputs valid HTML ([#192](https://github.com/flatpressblog/flatpress/issues/192))
- BBCode plugin: Initial settings after fresh install shown correctly ([#102](https://github.com/flatpressblog/flatpress/issues/102))
- Fixed broken links in the administration area

## Setup
- Reworked Installer ([#266](https://github.com/flatpressblog/flatpress/issues/266))
  - Image files, which are not used by the installer, were removed.
  - In the setup CSS, unused IDs, classes and incorrect references to fonts have been removed.
  - The installer header now shines in a simple FlatPress style.
  - Added missing language files for Greek, Spanish and French ([#214](https://github.com/flatpressblog/flatpress/issues/214))
  - The installer tries to write permissions to the fp-content directory recursively for owners and groups, which had to be done manually before.

## Themes
- Reworked "Leggero v2" style, Admin Area now responsive ([#259](https://github.com/flatpressblog/flatpress/issues/259))
  - Adjusted the alignment of the calendar widget and the search widget
  - The theme now adapts better at screen widths between 720px and 768px
  - Media queries were created for individual device classes (smartphone, netbook, laptop and PC) in order to achieve a better display, especially for mobile devices
  - The overall appearance is now not so angular/edgy
  - A single PhotoSwipe image or a whole gallery is now centered in the responsive design  ([#150](https://github.com/flatpressblog/flatpress/issues/150))
  - BBcode videos are no longer chopped off in responsive design, but adjusted to the width and center aligned
  - A left or right aligned BBcode video will now be centered if the screen < 960 px
  - The BBcode toolbar adapted for a better display at the screen width of 640px
  - The menu and submenu in the administration area now also has a "slightly" more modern design
  - Template and CSS from Uploader > Gallery: image texts; button and table adapted to Leggero V2 style
  - Text within the pre element is now printed completely by line break
  - Fixes a problem in the admin area when rendering font-sizes in Safari, Chrome and Firefox (iPhone/iPad) ([#256](https://github.com/flatpressblog/flatpress/issues/256))
  - Added "background-attachment: fix" -workaround for mobile devices.
  - Admin area now has Leggero-v2 style background instead of white background.

- Further fixes in "Leggero" theme
  - All Leggero theme css files now comply with [CSS level 3](https://jigsaw.w3.org/css-validator/)
  - Fixed searchbox glitch in FlatMaas revisited style ([#97](https://github.com/flatpressblog/flatpress/issues/97))
  - Fixed missing bullets in preview ([#98](https://github.com/flatpressblog/flatpress/issues/98))
  - CSS of the Leggero style had some glitches on mobile devices
  - Invalid HTML output fixed ([#106](https://github.com/flatpressblog/flatpress/issues/106), [#156](https://github.com/flatpressblog/flatpress/issues/156))
  - Removed unneccessary external font resource ([#112](https://github.com/flatpressblog/flatpress/issues/112))
  - "Add comment" link has its own line ([#135](https://github.com/flatpressblog/flatpress/issues/135))
  - Removed legacy/invalid CSS ([#133](https://github.com/flatpressblog/flatpress/issues/133), [#134](https://github.com/flatpressblog/flatpress/issues/134))
  - Fixed description of Leggero and Leggero v2 styles ([#137](https://github.com/flatpressblog/flatpress/issues/137))
  - Obsolete bullet points removed ([#136](https://github.com/flatpressblog/flatpress/issues/136))
  - Updated preview image ([#139](https://github.com/flatpressblog/flatpress/issues/139))
  - Fixed comments date format ([#237](https://github.com/flatpressblog/flatpress/issues/237))
  - Fixed several layout/CSS glitches ([#140](https://github.com/flatpressblog/flatpress/issues/140), [#144](https://github.com/flatpressblog/flatpress/issues/144), [#201](https://github.com/flatpressblog/flatpress/issues/201), [#247](https://github.com/flatpressblog/flatpress/issues/247), [#249](https://github.com/flatpressblog/flatpress/issues/249))
  - Lucida Console [code] ... [/code] is now correct as a font in the CSS file
  - In the admin area, the configuration panel has been revised
  - Fixed vertical alignment of BBCode toolbar in write panel
  - Removes obsolete acronym element in the language files and replaces it with the abbr element
  - The menu bar in Leggero style is now centered if the screen width is less than 768px

## Internationalization
- Added translation: Slovenian, Danish
- Reworked translations: Spanish, Portuguese, Dutch, and Italian
- Fixed wrong pt-br country code ([#100](https://github.com/flatpressblog/flatpress/issues/100))
- German translation for Comment Center plugin added ([#148](https://github.com/flatpressblog/flatpress/issues/148))
- Fixed not-yet-translated phrases in Blog view and Admin Area ([#171](https://github.com/flatpressblog/flatpress/issues/171))
- Contact form: Admin notification mail is now localized ([#205](https://github.com/flatpressblog/flatpress/issues/205))
- Setup tries to determine local language automatically ([#197](https://github.com/flatpressblog/flatpress/issues/197), [#216](https://github.com/flatpressblog/flatpress/issues/216), [#262](https://github.com/flatpressblog/flatpress/issues/262))
- The HTML of the installer now has a lang attribute in the html start tag to specify the language.

## Bugfixes
- Plugin management page: Removed empty warning messages box
- Fixed error at prev link on first / next link on last entry ([#95](https://github.com/flatpressblog/flatpress/issues/95))
- Logout redirects to home page again ([#119](https://github.com/flatpressblog/flatpress/issues/119))
- Fixed disappearing non-Latin characters in page title ([#49](https://github.com/flatpressblog/flatpress/issues/49) and [#91](https://github.com/flatpressblog/flatpress/issues/91))
- Worked around strftime() marked as deprecated as of PHP 8.1 ([#92](https://github.com/flatpressblog/flatpress/issues/92)) - thx @bohwaz
- Comments and contact form: Fixed error on sending mails with umlaut subjects ([#207](https://github.com/flatpressblog/flatpress/issues/207), [#209](https://github.com/flatpressblog/flatpress/issues/209))
- Added missing properties in order to prevent "Dynamic properties are deprecated" error under PHP 8.2 ([#115](https://github.com/flatpressblog/flatpress/issues/115))

## Security
- Possible XSS prevented: Session cookie missed the "secure" and "httponly" flags
- Proper check of uploaded files ([#152](https://github.com/flatpressblog/flatpress/issues/152), [#170](https://github.com/flatpressblog/flatpress/issues/170), [#217](https://github.com/flatpressblog/flatpress/issues/217))
- Possible XSS prevented: Admin Area URL ([#153](https://github.com/flatpressblog/flatpress/issues/153))
- Possible XSS prevented: Upload of misc. XML file types ([#172](https://github.com/flatpressblog/flatpress/issues/172), [#178](https://github.com/flatpressblog/flatpress/issues/178), [#188](https://github.com/flatpressblog/flatpress/issues/188))
- Directory browsing prevented ([#174](https://github.com/flatpressblog/flatpress/issues/174))
- Possible XSS in setup prevented ([#176](https://github.com/flatpressblog/flatpress/issues/176))
- Possible XSS in Media Manager plugin prevented ([#177](https://github.com/flatpressblog/flatpress/issues/177))
- Possible path traversal in Media Manager plugin prevented ([#179](https://github.com/flatpressblog/flatpress/issues/179))
- Possible XSSs in Admin Area prevented ([#180](https://github.com/flatpressblog/flatpress/issues/180), [#183](https://github.com/flatpressblog/flatpress/issues/183), [#187](https://github.com/flatpressblog/flatpress/issues/187))
- Possible XSS in comments prevented ([#186](https://github.com/flatpressblog/flatpress/issues/186))
- Possible CSRFs in Admin Area prevented ([#64](https://github.com/flatpressblog/flatpress/issues/64))
- Possible XSS in FlatPress Installer prevented ([#220](https://github.com/flatpressblog/flatpress/issues/220))
- Write permission for others removed by default ([#173](https://github.com/flatpressblog/flatpress/issues/173))

# 2021-06-19: [FlatPress 1.2.1](https://github.com/flatpressblog/flatpress/releases/tag/1.2.1)
## Bugfixes
- BOM in French language files lead to blank page in admin area (see [#82](https://github.com/flatpressblog/flatpress/issues/82))
## Translations
- Added Dutch language pack by Macmee


# 2021-03-20: [FlatPress 1.2 "Legato"](https://github.com/flatpressblog/flatpress/releases/tag/1.2)
## General
- FlatPress now runs smoothly with PHP 7.4 and PHP 8
- Performance: Lazy loading for images
- GDPR compatibility: Data of commenters are not stored in cookies any more
- SEO: Added XML sitemap for search engines ([details](https://forum.flatpress.org/viewtopic.php?f=4&t=126))
- Leggero v2 is default theme (see [#57](https://github.com/flatpressblog/flatpress/issues/57))
- Leggero v1 is now responsive
- Updated Smarty to release 2.6.31
- Added [CONTRIBUTORS.md](https://github.com/flatpressblog/flatpress/blob/master/CONTRIBUTORS.md)
## Plugins
- BBCode plugin:
  - Added image attribute "loading", default is "lazy"
  - Enhanced \[video\] element accepts video URLs for YouTube, Vimeo and Facebook
  - Added "mail" tag (replaces the Protected Mail Links plugin)
  - Selectboxes of attachments and images in the editor toolbar are sorted by name
- jQuery plugin: Updated jQuery and jQuery UI to their current versions
- CommentCenter plugin is part of the FP standard distribution
- PrettyUrls and Comment Center are activated by default, LastComments and LastCommentsAdmin are not
- Protected Mail Links plugin removed
## Security
- Overhauled v0.812.2 fix for local file inclusion vulnerability ([more details](http://www.guanting.com/security/exploit/information/27269.html))
- Comments are sanitized properly (see [#62](https://github.com/flatpressblog/flatpress/issues/62))
- Uploader checks for forbidden files more carefully
- User password isn't hashed with MD5 any more (see [#59](https://github.com/flatpressblog/flatpress/issues/59))
## Bugfixes
- Mail adresses are accepted in a broader range (see [#48](https://github.com/flatpressblog/flatpress/issues/48))
- HTTPS URLs in the contact form are now handled correctly (see [#55](https://github.com/flatpressblog/flatpress/issues/55))
- Fixed redirects after login
- Fixed "syntax error, unexpected '\['" bug, reported [here](https://forum.flatpress.org/viewtopic.php?f=2&t=131)
- Setup sets date and FP version for the freshly created static pages
- Annoying translation error in German language pack fixed
- ... and many more!
## Translations
- Added French language pack by Marc Thibeault and Dimitri Soufflet, reworked by Gee
- Added Japanese Language Pack by [NORTH HILL WORK STUDIO](https://nhws.localinfo.jp/)
- Added Brazilian Portuguese language pack by randy
- Added Italian language pack by Giacomo Margarito
- Added Spanish language pack by karelv
- Reworked German language pack by Detlef 

# 2019-02-22: [FlatPress 1.1 "Da capo"](https://github.com/flatpressblog/flatpress/releases/tag/1.1)
## General
- Languages added: Greek, German, Czech (feel free to send in *your* language packs!)
- Admin: Fancyfied editor toolbar with more BBCode elements
- Changelog: Missing releases added
## Plugins
- Plugin added: Protected Mail Links
- jQuery plugin: jQuery files are loaded locally now
- Readmore plugin: Now localized (feel free to send in *your* translation!)
- Searchbox plugin: Full text search enabled by default
- Footnotes plugin: Usage how-to added
- Lightbox plugin: Slimbox version updated, broken overlay fixed
## Security
- Security fix: Possible CSRF attack prevented (see [details](https://www.exploit-db.com/exploits/39870 "exploit-db.com/exploits/39870"))
## Bugfixes
- Correct handling of special characters in URLs (see [PR11](https://github.com/flatpressblog/flatpress/pull/11 "Pull request #11"))
- HTTPS allowed in comment URLs
- BBCode element "video" serves Youtube videos in iFrame instead of SWF object
- Leggero theme: No more mixed content warning (see [#31](https://github.com/flatpressblog/flatpress/issues/31))
- Update checker works with HTTPS URL (see [#36](https://github.com/flatpressblog/flatpress/issues/36))

# 2018-12-16: [FlatPress 1.0.3.php7](https://github.com/flatpressblog/flatpress/releases/tag/v1.0.3.php7)
First release after Edoardo handed over the project ownership to Arvid. "Emergency release" to bring FlatPress back to the present.
- Runs under PHP7
- HTTPS support
- Plugins added: Last comments admin, Media manager

# 2015-06-12: [FlatPress 1.0.3](https://github.com/flatpressblog/flatpress/releases/tag/v1.0.3)
- This release fixes an XSS (CVE-2014-100036).
- Bonus: a new style for Leggero theme by @MarcThibeault and other UI enhancements by @MarcThibeault and @liquibyte

# 2013-12-11: [FlatPress 1.0.2](https://github.com/flatpressblog/flatpress/releases/tag/v1.0.2)
Another bugfix release.
- Fixes errors in the rushed patched vulnerability in v1.0.1.
- Clears some issues with strict standards.
- Timezone now defaults to UTC. You can set your own time offset in the configuration panel of the admin area

# 2013-11-21: [FlatPress 1.0.1](https://github.com/flatpressblog/flatpress/releases/tag/v1.0.1)
Bugfix release. 
- Addresses Issue #3 http://www.exploit-db.com/exploits/29515/

# 2012-01-11: FlatPress 1.0 "Solenne"
- ...still to be documented...

# 2010-11-07: 0.10xxx
# 2009-10-10: 0.9xx Arioso
# 2008-12-24: 0.8xx Fortissimo

2008-05-07: FlatPress 0.803 Vivace
======================
GENERAL
-------
+ Rewritten bootstrap, index centralized
+ New database backend (soon to be rewroked ;))
+ New draft system
+ New secure hashing algorhytms for passwords
+ New widget system
+ Post view count moved to plugin PostViews
+ Rewritten main config file
+ Allowing custom appearance for date/time
+ New URLs (still compatible)
+ Allow static pages as home
+ mysite.com/flatpress/?random post goodie :)


PLUGINS
-------
+ New PostViews plugin
+ New favicon plugin
+ New prettyurls plugin, supporting pathinfo! (check plugin for help)
+ Added GUI to BlockParser
+ Added GUI to Akismet
F Fixed accessible antispam
F BBCode now allows inline HTML (check plugin for help): this
  allows WYSIWYG lovers to install their favourite editor (e.g.
  TinyMCE, see the forum for more)
F Modified and cleaned interactions of BBCode with thumbs and 
  lightbox plugins
F Lightbox shouldn't crash IE7 anymore
F Akismet shouldn't timeout anymore
U jsUtils is mootools 1.1 full (complete download)

ADMIN PANEL
-----------
+ New GUI
+ Allowing plugins to add panels
+ Validating now without sessions
+ New Widget GUI
+ New Plugin GUI
+ New Theme/Styles GUI
+ New Options (formerly config) GUI


0.703.6.2 (2007-11-26)
======================

- FIXED: removed /test.php 
- FIXED: version number


0.703.6.1 (2007-10-23)
======================

FIXED: typo in admin.entry.delete.php


0.703.6 (2007-10-19)
====================

- FIXED:  XSS vulnerabilities in comments.tpl and contact.tpl
- FIXED: Backported from Crescendo+1 fix for XSS in $_GET fields
- FIXED: bug in static handling (THEME_LEGACY_MODE not checked)
- FIXED: Moved html escaping from default-filters to bbcode plugin
- FIXED: Added option to bbcode plugin to allow inline html! (no more ugly [html] tags! :)


0.703.5 (2007-09-22)
====================

- FIXED: severe bug with 
- FIXED: smaller one with commslock


0.703.4 (2007-09-19)
====================
 
FIXED: several XSS vulnerabilities


0.703.3 (2007-09-18)
====================

FIXED: XSS in search.php


0.703.2 (2007-07-13)
====================

FIXED: input validation problems


0703.1 Crescendo (July 10, 2007)
===============================

Small bug fixes
- FIXED:   bbcode: [u] tag missing
- FIXED:	 bbcode/syntaxhighlighter: [code=MY_SYNTAX] works again
- FIXED:	 fixed error handling with missing categories
 

0.703 Crescendo Final (June 27, 2007)
=====================================

- UPDATED: jsUtils : Mootools 1.11
- FIXED:	 URL issues with BBCODE
- FIXED:	 small issues with thumb plugin


Crescendo RC2 (June 3, 2007)
============================

- FIXED: spaces in file names are escaped as dashes "-" when uploaded
- FIXED: various bbcode issues
- FIXED: scale/width bbcode/thumb issues
- MDFD: now thumb creates a .thumb dir for each subdir of images/
- FIXED: leggero CSS
- FIXED: double entity encoding
- ADDED: (since RC1): when loggedin trying to open a non-existent
        static page will bring you to the "add new static" panel


Crescendo RC1 (May 29, 2007)
============================

- FIXED: plugin/bbcode: broken non-local urls 
- FIXED: core/FPDB archive function: /?y=nn didn't work if a month wasn't specified
- FIXED: core/entry/cache : buggy workarounded function (see previous) is now fixed
- FIXED: core/users : session was not kept if user IP changed
- FIXED: core/rss : template now works, fixed core accordingly
- ADDED: core/rss : full content support
- UPDATED: plugin/jsUtils, upgraded to mootools 1.1
- UPDATED: plugin/lightbox updated accordingly to slimbox 1.4
- RMVD:	temporarily removed prettyurls plugin 
	(todo: remove from default config); 
	I'm working to a newer cooler version, but 
	it will require probably some changes in core, so no-go for this
	release
- ADDED: Lang/it-it: added some strings I forgot

Crescendo beta1 (May 17, 2007)
============================

- added: 		some entry/cache hooks
- added:		many plugin translations thanx to cimangi (http://luielei.altervista.org/)
- added:		panel notifications for plugins
- added:		new theme, new icons (updated old admin css)
- fixed:		lightbox updated and fixed
- fixed:		removed quote escaping in entries (removed and added fix for old versions)
- fixed:		directory deletion under php5 (thx cimangi)
- fixed:		entry_delete did not remove visit counter (cimangi)  
- fixed:		session retaining in control panel under certain conditions (smartyvalidate)
- changed:	some behaviours in cache; need some rework as introduced a little bug... d'oh! 


Crescendo alpha (Feb 10, 2007)
===============

- fixed:		utils_mail()
- fixed: 		bbcode url trim
- fixed:		bbcode remote image timeouts 
- changed:	WHOLE new POST behaviour (no longer "POSTDATA" messages)
- changed:	new theme tags (almost finished). support for old themes; soon deprecated
- changed:	graphics for the old theme (almost finished) 
- changed:	a whole bunch of graphic thingies
- changed:	plugin organization
- added: 		[video] tag support http://flatpress.nowhereland.it/index.php?entry=entry070210-211548
- added: 		update checker (experimental)
- added:		error/success notification system with fancy graphics :P

NOTE: italian language is still there until the wiki is ready
