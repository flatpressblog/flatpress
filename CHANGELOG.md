# Currently testing: [FlatPress 1.5 "Stringendo" Release Candidate 1](https://github.com/flatpressblog/flatpress/releases/tag/1.5.rc1)
## Changed requirements
- FlatPress 1.5 runs under PHP up to **8.5**; minimum required PHP version increases to **7.2**.

## General
- Template engine Smarty:
  - Updated to version 5.7.0 with PHP 8.5 support ([#651](https://github.com/flatpressblog/flatpress/pull/651))<br><sub><i>
Smarty 5 now always runs in multibyte mode. Make sure you use the PHP [multibyte extension](https://www.php.net/manual/en/book.mbstring.php) in production for optimal performance.</i></sub>
  - No code changes required with new, stable Smarty version.
  - FlatPress automatically loads the latest PSR-4 stub.
  - The new Smarty Modifier ``|ver``  uses a new core function, ``utils_asset_ver()``, to assign the FlatPress version to Java scripts and stylesheets. This ensures that the visitor's browser only uses the updated files. ([#629](https://github.com/flatpressblog/flatpress/issues/629))
- Caching:
  - Fewer race conditions thanks to local cache, optionally supported by APCu. ([#667](https://github.com/flatpressblog/flatpress/issues/667), [#673](https://github.com/flatpressblog/flatpress/pull/673), [#675](https://github.com/flatpressblog/flatpress/pull/675), [#679](https://github.com/flatpressblog/flatpress/pull/679), [#687](https://github.com/flatpressblog/flatpress/pull/687), [#690](https://github.com/flatpressblog/flatpress/pull/690), [#701](https://github.com/flatpressblog/flatpress/pull/701), [#729](https://github.com/flatpressblog/flatpress/pull/729), [#730](https://github.com/flatpressblog/flatpress/pull/730))

- Admin area:
    - The inactivity timeout can be changed using the Flatpress protect plugin. Default = 1 hour ([#693](https://github.com/flatpressblog/flatpress/issues/693))
    - Uploader revised to multi-file uploader  ([#656](https://github.com/flatpressblog/flatpress/pull/656), [#46](https://github.com/flatpressblog/flatpress/issues/46))
    - Widget panel revisited  ([#659](https://github.com/flatpressblog/flatpress/pull/659))
        - Stylesheet is now also loaded by themes that do not have a design for the widget panel.
        - From left to right/ Available widgets to widget bar
        - Flex version with wrapper and responsive
        - Drag & drop now also available for mobile devices
    -  New APCu Control Panel with cache clearing function ([#701](https://github.com/flatpressblog/flatpress/pull/701))

### Security
- Detection of an HTTP/HTTPS connection ``is_https()`` is significantly more reliable and less susceptible to spoofing. Improved detection for public proxies/CDNs, including Azure and Cloudflare. ([#672](https://github.com/flatpressblog/flatpress/pull/672))

### Bugfixes
- Correct output when a historical character set encoding is set. ([#670](https://github.com/flatpressblog/flatpress/pull/670))
- If ``$_SERVER ['HTTPS'] = off`` is set in the web server, an HTTP connection is now correctly recognized. ([#671](https://github.com/flatpressblog/flatpress/issues/671))
- ``theme_style_exists()`` now returns ``''`` if the style directory is missing. Previously, the theme root was returned incorrectly. ([#678](https://github.com/flatpressblog/flatpress/pull/678))
- Fixes the display of orphaned widgets when a plugin has been deactivated and prevents duplicate or missing widget outputs, so that only widgets from active plugins are output. ([#726](https://github.com/flatpressblog/flatpress/pull/726))

## Plugins
### Changes
- Archives plugin: update to version 1.1.1
  - Toggles in Themes, based on FlatMaas 2 by Drudo
  - Added request-local and APCu caching. ([#679](https://github.com/flatpressblog/flatpress/pull/679))
- Newsletter plugin: update to version 1.7.3
  - Unwanted requests and bots are now intercepted more effectively: Suspicious IP addresses are automatically added to a block list, which is cleaned daily.
  - Email addresses are now checked much more thoroughly – including domain and server checks – to detect typos, invalid, or undeliverable addresses.
  - An up-to-date list of disposable email domains is automatically downloaded from [GitHub](https://raw.githubusercontent.com/disposable-email-domains/disposable-email-domains/refs/heads/main/disposable_email_blocklist.conf) once a month and integrated, so that disposable addresses are rejected immediately and removed from the subscriber list.
  - In addition, the plugin limits the number of login attempts per IP and sorts out incorrect addresses before they are sent, ensuring that the newsletter is reliably delivered only to valid recipients.
  - Even more against race conditions
  - Batch shipping shows shipping status ([#649](https://github.com/flatpressblog/flatpress/pull/649))
- FlatPress Protect plugin: update to version 1.2.1
  - iFrames can only be embedded from the same domain.
  - It is now possible to change the idle timeout for admin sessions. ([#693](https://github.com/flatpressblog/flatpress/issues/693))
- BBCode plugin: update to version 2.0.0
  - Memoization and optional APCu caches added ([#680](https://github.com/flatpressblog/flatpress/pull/680))
  - Font button added ([#689](https://github.com/flatpressblog/flatpress/issues/689))
  - BBcode toolbar gallery selection added ([#714](https://github.com/flatpressblog/flatpress/pull/714))
- Stats plugin to Storage plugin:
  - The stats plugin has been renamed as part of the modernization and can be found in the uploader submenu ([#363](https://github.com/flatpressblog/flatpress/issues/363))
    - Free/used web space is displayed.
    - The storage space used by images and files is displayed.
    - APCu support has been added for optimal performance
    - The 10 most commented posts are only displayed if the Postviews plugin is active
    - A slightly more modern, responsive design
- Media Manager plugin: update to version 2.0.0 ([#685](https://github.com/flatpressblog/flatpress/pull/685))
    - Preview images on mouseover ([#732](https://github.com/flatpressblog/flatpress/pull/732))
    - The folder icon now indicates whether the gallery or a single image in the directory is used in entries or not
    - Performance:
        - (initial call, root view): Entry scan reduced from 2× to 1×
        - No entry reads for subsequent calls.
- PrettyURLs plugin: update to version 3.0.2
    - Added request-local and APCu caching. ([#690](https://github.com/flatpressblog/flatpress/pull/690))
    - A green hook indicates the best automatically determined mode.
    - Modes that are not supported by the web server are grayed out.
    - If Pretty is saved in the configuration but is not supported, downgrade to one of the remaining modes.
    - Pretty URLs for static pages and feed URLs.
    - Pretty URLs for the RSS and Atom feeds of the LastComment plugin.
    - Mixed-mode URLs are redirected to the correct URL to improve search engine rankings.
- Calendar plugin: update to version 1.2.1
    - Optional APCu support with file fallback added ([#694](https://github.com/flatpressblog/flatpress/pull/694))

### Bugfixes
- Newsletter plugin: update to version 1.7.3
    - Fixes "Invalid CSRF token" when the widget is visible in the admin area footer.
- Seo Metatag Info plugin: update to version 2.2.5
    - Fixed: Theme without style causes PHP warning
- Support plugin: update to version 1.1.1
    - Fixed: Theme without style causes PHP warning
    - mbstring query for Smarty 5 added
- Media Manager plugin: update to version 2.0.0
    - Fix Media Manager usage detection for images in subfolders and galleries. ([#547](https://github.com/flatpressblog/flatpress/issues/547))
- PrettyURLs plugin: update to version 3.0.2
    - Unified 301 canonical redirect for plain ``?entry=<id>`` and plain ``?x=entry:<id>``. ([#104](https://github.com/flatpressblog/flatpress/issues/104))
    - Unified 301 canonical redirect for plain ``?page=<id>``, ``?page<n>``and ``x=feed:<rss2|atom>``. ([#93](https://github.com/flatpressblog/flatpress/issues/93))
    - Unified 301 canonical redirect for plain ``?x=cat:<n>``. ([#709](https://github.com/flatpressblog/flatpress/pull/709))
    - Fixes Deprecated: ``strpos(): Passing null to parameter #1 ($haystack) of type string``.
- Calendar plugin: update to version 1.2.1
    - The link "Previous month with entries" now also works if there are no entries in the previous month.
- GDPR Video embed: update to version 1.1.1
    - An issue in the French and Italian language files that prevented the JS from loading has been fixed. Thank you for reporting the issue to [macadoum from the support forum](https://forum.flatpress.org/viewtopic.php?t=938&start=20#p3334).
- QuickSpamFilter plugin update to version 3.5.2
    - Set default bad words are now visible to the admin
    - In addition to ``[url`` and ``href``, generic URLs are now also blocked by default. Thank you for reporting the issue to [macadoum from the support forum](https://forum.flatpress.org/viewtopic.php?t=938&start=20#p3334).
- BBCode plugin update to version 2.0.0
    - Sorted/unsorted lists are displayed correctly in comments. ([#762](https://github.com/flatpressblog/flatpress/issues/762))

## Themes
### Changes
- Added edit button comment admin controls
- Leggero theme:
    - If a SEO metatag description of the post is available, it will be displayed as an introduction to the post. [@wjar forum entry](https://forum.flatpress.org/viewtopic.php?t=424&start=10#p3208)
### Bugfixes
- Leggero theme:
    - After a fresh installation, the correct time format is now displayed instead of the default format  ``%b %e, %Y``. ([#662](https://github.com/flatpressblog/flatpress/pull/662))

## Internationalization
- Added Basque translation by [@xbhrnnd](https://github.com/xbhrnnd)
- Reworked French translation by [@finkiki](https://github.com/finkiki) ([#754](https://github.com/flatpressblog/flatpress/pull/754), [#759](https://github.com/flatpressblog/flatpress/pull/759))
- Reworked German translation by [@RainerBielefeld](https://github.com/RainerBielefeld) ([#747](https://github.com/flatpressblog/flatpress/pull/747))
- Minor corrections to the Italian translation by [@eagleman](https://github.com/eagleman)

# 2025-07-15: [FlatPress 1.4.1](https://github.com/flatpressblog/flatpress/releases/tag/1.4.1)

## General
### Bugfixes
 - Sending e-mails now also works if the recipient requests the RFC 5322 header. ([#631](https://github.com/flatpressblog/flatpress/issues/631))
 - Sending an e-mail is again possible under PHP7.1 to PHP7.3. ([#630](https://github.com/flatpressblog/flatpress/issues/630))
 - Setup now also works with open_basedir restrictions and when reading is not possible due to access rights. ([qbwdp @Forum post](https://forum.flatpress.org/viewtopic.php?p=3109#p3102))
- Login page:
  - Removed cleanup of special characters in the password for admin login ([#627](https://github.com/flatpressblog/flatpress/issues/627))

## Plugins
### Additions
- Newsletter: Provides a complete, privacy-conscious newsletter solution for FlatPress, without a database and with minimal configuration effort. 

### Bugfixes
- BBcode plugin: update to version 1.9.1
  - The font-tag works as documented in the [wiki](https://wiki.flatpress.org/doc:plugins:bbcode#text_formatting) ([#635](https://github.com/flatpressblog/flatpress/issues/635))
- Comment Center plugin: update to version 1.1.4
  - Akismet client now uses SSL/HTTPS, as fallback HTTP ([#638](https://github.com/flatpressblog/flatpress/issues/638))
  - A comment recognized as spam is not a technical problem ([#639](https://github.com/flatpressblog/flatpress/issues/639))

# 2025-05-30: [FlatPress 1.4 "Notturno"](https://github.com/flatpressblog/flatpress/releases/tag/1.4)

## General
- The fixed "Stats" panel has been converted into a plugin ([#363](https://github.com/flatpressblog/flatpress/issues/363))
- FlatPress anonymizes the IPv4 address of the visitor. IPv6 addresses are replaced by a hash. ([#105](https://github.com/flatpressblog/flatpress/issues/105))
- The determination of the time format has been made more robust

## Changes
- Template engine:
  - Smarty updated to version 4.5.5 with PHP 8.4 support ([#376](https://github.com/flatpressblog/flatpress/pull/376), [#390](https://github.com/flatpressblog/flatpress/issues/390))
- Login page:
  - Instructs search engines not to index the page ([#450](https://github.com/flatpressblog/flatpress/pull/450))
- Admin area:
  - Optional natural sorting for static pages (Hidden improvement suggestion from [NHWS](https://nhws.localinfo.jp/))
  - The cache is automatically emptied when the theme or style is changed.
  - Setting permissions via the maintenance panel now takes all FlatPress files and directories into account. A distinction is made between content, core and other. ([#502](https://github.com/flatpressblog/flatpress/pull/502))
  - You can now change the admin password in the configuration menu or create another administrator ([#516](https://github.com/flatpressblog/flatpress/issues/516))
  - Added support for the webp image format. ([#611](https://github.com/flatpressblog/flatpress/issues/611))

## Bugfixes
- Contact form / comment function:
  - Entering the website is now correct without http(s):// ([#419](https://github.com/flatpressblog/flatpress/issues/419))
  - Compatibility to PHP with OPcache:
    - Positive feedback when the contact form or comment form has been sent correctly. ([#420](https://github.com/flatpressblog/flatpress/issues/420))
- Atom feed: Fixes parsing error ([#429](https://github.com/flatpressblog/flatpress/issues/429))
- Comment Atom feed: Fixed pharsing error if the commenter had not specified a website. ([#508](https://github.com/flatpressblog/flatpress/pull/508))
- Admin area:
  - Charset dropdown selection instead of an input field ([#340](https://github.com/flatpressblog/flatpress/issues/340))
  - The author entered in the configuration is now the author of the entries and static pages ([#483](https://github.com/flatpressblog/flatpress/issues/483))
  - Compatibility to PHP with OPcache:
    - Changes in input fields and drop-down menus are immediately reflected in the configuration panel. ([#213](https://github.com/flatpressblog/flatpress/issues/213), [#244](https://github.com/flatpressblog/flatpress/issues/244))
    - Activating or deactivating plugins are immediately reflected in the plugin management panel. ([#213](https://github.com/flatpressblog/flatpress/issues/213), [#244](https://github.com/flatpressblog/flatpress/issues/244))
  - OPcache is deactivated when the theme panel is called up so that newly activated themes or styles are displayed immediately. ([#213](https://github.com/flatpressblog/flatpress/issues/213), [#244](https://github.com/flatpressblog/flatpress/issues/244))
  - The validation of the standard format for date and time has been extended to include some Japanese characters. (hidden hint from [NHWS](https://nhws.localinfo.jp/)) (RC1) ([#531](https://github.com/flatpressblog/flatpress/pull/531))
  - Theme or style thumbnails are displayed after permissions are restored (hidden hint from [NHWS](https://nhws.localinfo.jp/)) (RC1) ([#532](https://github.com/flatpressblog/flatpress/pull/532))
  - When deactivating the last widget under PHP 8.4, the penultimate widget is only displayed once (RC1) ([#555](https://github.com/flatpressblog/flatpress/issues/555))
  - A defined HTML form and the id admin-{$panel}-{$subtab} is not output twice. ([#613](https://github.com/flatpressblog/flatpress/issues/613))

## Security
- The session-cookie are now somewhat more secure against CSRF attacks. ([#481](https://github.com/flatpressblog/flatpress/issues/481))
- BBcode, Cookiebanner and Emoticons plugin: removed unsafe href onclick HTML method ([#422](https://github.com/flatpressblog/flatpress/issues/422), [#477](https://github.com/flatpressblog/flatpress/pull/477))
- BBcode, PhotoSwipe and Emoticons plugin: Scripts equipped with a nonce to enable stricter [CSP](https://en.wikipedia.org/wiki/Content_Security_Policy) ([#422](https://github.com/flatpressblog/flatpress/issues/422), [#477](https://github.com/flatpressblog/flatpress/pull/477))
-  E-mail function with header injection protection and rate limiting ([#539](https://github.com/flatpressblog/flatpress/issues/539))
-  CSRF protection added for the comment function ([#534](https://github.com/flatpressblog/flatpress/issues/534))
-  CSRF protection added for the contact form ([#541](https://github.com/flatpressblog/flatpress/issues/541))
-  Blocking of SQL injection patterns in the comment function and in the contact form ([#534](https://github.com/flatpressblog/flatpress/issues/534)) [Many thanks to Laborix for testing](https://forum.flatpress.org/viewtopic.php?t=828)
- Admin area login:
  - Allow admin login attempts only every 30 seconds to make brute force attacks more difficult. ([#87](https://github.com/flatpressblog/flatpress/issues/87))
  - CSRF protection added for the login page ([#542](https://github.com/flatpressblog/flatpress/pull/542))
  - The fp-user or fp-pass cookie is no longer set when logging in. Admin login and authentication via PHP sessions. ([#488](https://github.com/flatpressblog/flatpress/pull/488))<br><sub><i>When installing a release update package, previously saved login information becomes invalid due to the change from cookie authentication to session authentication! The user must be recreated by executing the setup - see [FAQ](https://wiki.flatpress.org/doc:techfaq#i_ve_lost_my_password).</i></sub>
- Admin area:
  - PrettyURLs plugin: To edit the .htacces file directly, the FlatPress Protect plugin option must first be activated. ([#379](https://github.com/flatpressblog/flatpress/issues/379))
  - Upload panel: More resistant to RCE attacks and traversal attacks ([#451](https://github.com/flatpressblog/flatpress/issues/451), [#114](https://github.com/flatpressblog/flatpress/issues/114))
    - Upload of hidden files is no longer possible. ([#486](https://github.com/flatpressblog/flatpress/pull/486))
  - Delete entry and delete static page are now more secure against XSS and CSRF attacks ([#220](https://github.com/flatpressblog/flatpress/issues/220))
  - Plugin management now more secure against XSS attacks ([#220](https://github.com/flatpressblog/flatpress/issues/220))
  - Widget management: Scripts equipped with a nonce to enable stricter [CSP](https://en.wikipedia.org/wiki/Content_Security_Policy) ([#422](https://github.com/flatpressblog/flatpress/issues/422), [#477](https://github.com/flatpressblog/flatpress/pull/477))
  - XSS vulnerabilities in the configuration menu -> International settings closed. ([#487](https://github.com/flatpressblog/flatpress/pull/487), [#340](https://github.com/flatpressblog/flatpress/issues/340))
  - Logout after one hour if inactive. ([#488](https://github.com/flatpressblog/flatpress/pull/488))
  - XSS vulnerability in the editor for static pages fixed. ([#490](https://github.com/flatpressblog/flatpress/pull/490))
  - Fixed disclosure of Exif metadata when uploading images. ([#492](https://github.com/flatpressblog/flatpress/pull/492))
  - Prevention of symlink attacks by checking the path when setting file and directory permissions ([#502](https://github.com/flatpressblog/flatpress/pull/502))
  - Removed an XSS vulnerability in the category management panel. ([#574](https://github.com/flatpressblog/flatpress/pull/574))

## Plugins
### Additions
- GDPR Video embed: Simple two-click solution for GDPR-compliant embedding of YouTube, Facebook and Vimeo videos. ([#260](https://github.com/flatpressblog/flatpress/issues/260))

### Reductions
- LightBox2 plugin (can still be obtained from the [flatpress-extras repo](https://github.com/flatpressblog/flatpress-extras)) ([#359](https://github.com/flatpressblog/flatpress/issues/359))
- LastComments Admin plugin (can still be obtained from the [flatpress-extras repo](https://github.com/flatpressblog/flatpress-extras)) ([#559](https://github.com/flatpressblog/flatpress/issues/559))

### Changes
- SEO Meta Tag Info plugin: update to version 2.2.4
  - Integration of Open Graph tags ([#366](https://github.com/flatpressblog/flatpress/issues/366))
  - If an HTTP root directory is stored in the server configuration file and is not empty, a predefined robots.txt can be created and edited via the SEO panel in the admin area. ([#427](https://github.com/flatpressblog/flatpress/pull/427))
- FavIcon plugin: update to  version 1.1.0
  - Support for iOS Safari, Android Chrome, Windows 10 and Mac OS Safari added ([#416](https://github.com/flatpressblog/flatpress/pull/416), [#428](https://github.com/flatpressblog/flatpress/pull/428))
- BBcode plugin: update to version 1.9.0
  - The editor toolbar can be deactivated again as in version 1.2.1 when using an alternative editor (e.g. [Wysiwyg editor](https://wiki.flatpress.org/res:plugins:ckeditor)). ([#436](https://github.com/flatpressblog/flatpress/pull/436))
  - BBcode toolbar, if BBcode for comments is allowed ([#437](https://github.com/flatpressblog/flatpress/pull/437))
  - The fp-content/attachs directory is hidden if the file has been included with the URL tag ([#443](https://github.com/flatpressblog/flatpress/pull/443))
- The Commentcenter plugin has been given a lower priority so that other comment filters (e.g. qspam) can do their work first. ([#449](https://github.com/flatpressblog/flatpress/pull/449))
- PrettyURLs plugin: update to version 3.0.1
  - To prevent accidental changes to the .htacces file, the creation or editing of this file must first be activated via the FlatPress Protect plugin ([#477](https://github.com/flatpressblog/flatpress/pull/477))
- FlatPress Protect plugin: update to version 1.1.0
  - Insecure inline Java scripts are not executed by the visitor's browser by default. You can allow the execution of insecure Java code if, for example, a plugin contains a Java script that is not equipped with a nonce. ([#477](https://github.com/flatpressblog/flatpress/pull/477))
  - It is also possible to enable/disable the htaccess edit field to create or edit the file in the PrettyURLs plugin without having to disable the FlatPress Protect plugin. ([#477](https://github.com/flatpressblog/flatpress/pull/477))
  - The removal of metadata when uploading images can be deactivated for better image quality. ([#492](https://github.com/flatpressblog/flatpress/pull/492))
- Support plugin: update to version 1.1.0
  - The file and directory permissions are read for some outputs before a write test is performed. This leads to a more reliable indication of whether a file is writable or not. ([#502](https://github.com/flatpressblog/flatpress/pull/502))
- LastComments plugin: update to version 1.1.1
  - Generates an RSS and Atom feed that displays the latest comments. ([#509](https://github.com/flatpressblog/flatpress/pull/509))
  - Output of comments in the widget without BBcode tags
- Feed plugin: update to version 1.0.1
  - RSS image replaced with RSS icon (woff2) ([#515](https://github.com/flatpressblog/flatpress/pull/515))
- Media Manager plugin and Gallery captions: update to version 1.0.1
  - Show image in a popup instead of in the same tab
- jQuery plugin: update to Version 2.2.1
  - Updated jQuery and jQuery UI to their current versions
- Thumbnails plugin: update to version 1.1.0
  - Added support for the webp image format. ([#611](https://github.com/flatpressblog/flatpress/issues/611))

### Bugfixes
- BBcode plugin: update to version 1.9.0
  - File or image selection possible after activating the option “Allow BBcode in comments” option ([#391](https://github.com/flatpressblog/flatpress/issues/391))
  - BBcode create a valid simple URL ([#442](https://github.com/flatpressblog/flatpress/issues/442))
  - Files and images are now sorted correctly alphabetically in the toolbar ([#537](https://github.com/flatpressblog/flatpress/issues/537))
- DateChanger plugin: Update to version 1.0.6
  - Correct date format in the DateChanger toolbar for the languages Czech, English, Japanese and Russian. Hidden reported by [NHWS](https://nhws.localinfo.jp/). Many thanks for testing to [WineMan from the support forum](https://forum.flatpress.org/viewtopic.php?p=2823#p2829)
- Calendar plugin: Update to version 1.2.0
  - Two new functions which only output a “Next” or “Previous” link if there is at least one entry in the month. ([#128](https://github.com/flatpressblog/flatpress/issues/128))
  - The “Next”, “Previous” and “Day” links now always contain a 4-digit year.
  - The set language is now taken into account when determining the first day of the week. ([#73](https://github.com/flatpressblog/flatpress/issues/73))
  - Links from single-digit months are now always two-digit.
- BlockParser plugin: Update to version 1.0.1
  - Compatibility to PHP with OPcache:
    - The list of activated pages is displayed immediately after activation/deactivation. ([#213](https://github.com/flatpressblog/flatpress/issues/213), [#244](https://github.com/flatpressblog/flatpress/issues/244))
- PhotoSwipe plugin: update to version 2.0.4
  - The overlay buttons are no longer displayed in the RSS and Atom feed. ([#506](https://github.com/flatpressblog/flatpress/pull/506))
  - External images are displayed correctly. ([#520](https://github.com/flatpressblog/flatpress/pull/520))
  - Correct grouping: Only images from the same gallery are taken into account.
  - It is ensured that the overlay structure is always in the DOM. ([#572](https://github.com/flatpressblog/flatpress/issues/572))
  - An image with an link can be created if the “Popup” parameter contains false. ([#548](https://github.com/flatpressblog/flatpress/issues/548))
    - <i>Must be documented in the wiki</i>
  - After closing the overlay, the page remains accessible for screen readers. ([#622](https://github.com/flatpressblog/flatpress/issues/622))
- Media Manager plugin: update to version 1.0.1
  - Files and directories are sorted numerically, alphabetically. ([#537](https://github.com/flatpressblog/flatpress/issues/537))
- SEO Meta Tag Info plugin: update to version 2.2.4
  - The determination of the page URL now also works if FlatPress is operated behind a load balancer or reverse proxy.
  - No hyphen after the blog title if there is no description for the entry 
- Commentcenter plugin: update to version 1.1.3
  - Deleting non-existent comments no longer leads to a fatal error ([#593](https://github.com/flatpressblog/flatpress/issues/593))

### Security
- SEO Meta Tag Info plugin:
  - Removed a cross-site scripting (XSS) vulnerability. ([#491](https://github.com/flatpressblog/flatpress/pull/491))
- Gallery captions plugin:
  - Removed a cross-site scripting (XSS) vulnerability. ([#574](https://github.com/flatpressblog/flatpress/pull/574))

## Setup
### Bugfixes
- The setup now also recognizes the browser language when using Firefox

## Themes
- The Leggero theme now also indicates that comment feeds can be subscribed to ([#515](https://github.com/flatpressblog/flatpress/pull/515))
- Invidual scrollbar for the Leggero v2 style
- The Leggero v2 style now supports UltraWide monitors ([#476](https://github.com/flatpressblog/flatpress/issues/476))
  
### Bugfixes
- The link "Add comment" now leads to the comment form instead of jumping to top ([#474](https://github.com/flatpressblog/flatpress/issues/474))

## Internationalization
- Reworked translations: Japanese (Thanks to [NHWS](https://nhws.localinfo.jp/))
- Month selection localized in the search form ([#158](https://github.com/flatpressblog/flatpress/issues/158))
- Administration area: Optional localization for the description of themes and styles ([#453](https://github.com/flatpressblog/flatpress/pull/453))
- Minor corrections to the Italian language pack. Grazie mille eagleman
- Turkish language package by [oldmouseclick](https://github.com/oldmouseclick)

# 2024-05-04: [FlatPress 1.3.1](https://github.com/flatpressblog/flatpress/releases/tag/1.3.1)
## Bugfixes
- fixed incorrent HTTP/HTTPS differentiation ([#251](https://github.com/flatpressblog/flatpress/issues/251), [#371](https://github.com/flatpressblog/flatpress/issues/371), [#378](https://github.com/flatpressblog/flatpress/issues/378))

## Security
- added secure prefix for cookies ([#155](https://github.com/flatpressblog/flatpress/issues/155))

# 2024-04-07: [FlatPress 1.3 "Andante"](https://github.com/flatpressblog/flatpress/releases/tag/1.3)
## Changed requirements
- FlatPress 1.3 runs under PHP up to **8.3**; minimum required PHP version increases to **7.1**.
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
- DateChanger plugin added: Allows you to change the publication date for (new) entries.
- Feed plugin added: Displays the RSS and Atom feed via a widget ([#317](https://github.com/flatpressblog/flatpress/issues/317))
- CookieBanner plugin added: Discreet reference to the use of cookies ([#325](https://github.com/flatpressblog/flatpress/issues/325))
- Emoticons plugin added: Allows accessible emoticons via an editor toolbar. Suggested by [@DeltaLima](https://github.com/DeltaLima)
- Support plugin added: Support data for the FlatPress admin and the community can be accessed via the admin maintenance menu.

### Changes
- jQuery plugin: Updated jQuery (3.5.1 => 3.6.1) and jQueryUI (1.12.1 => 1.13.2)
- Media Manager plugin shows 50 items per page, not 10
- BBCode plugin: Added "h4" icon to editor toolbar ([#201](https://github.com/flatpressblog/flatpress/issues/201))
- BBCode plugin: Facebook-Video now uses the latest video player API and the lazy loading mechanism of the browser; also now has localized languages with language tag ([#252](https://github.com/flatpressblog/flatpress/issues/252)) - see also https://developers.facebook.com/docs/javascript/internationalization
- BBCode plugin: Added optional "target" attribute to the "url" element - ([PR270](https://github.com/flatpressblog/flatpress/pull/270) by [@sjustesen](https://github.com/sjustesen))
- Comment center plugin (Akismet) revised to enable a more understandable operation ([#273](https://github.com/flatpressblog/flatpress/issues/273))
- Comment center plugin: The admin must authorize comments (set as default) ([#101](https://github.com/flatpressblog/flatpress/issues/101))
- Removed Akismet plugin: Akismet spam check is already included in the comment center plugin.<br><sub><i>Before updating FlatPress to 1.3, enter your Akismet key into the Comment Center plugin, and delete the Akismet plugin.</i></sub>

### Bugfixes
- LastCommentsAdmin plugin will not even attempt to delete or rebuild LastComments caches if LastComments plugin is not available ([#43](https://github.com/flatpressblog/flatpress/issues/43))
- Comment Center plugin: Fixed errors on the config page ([#90](https://github.com/flatpressblog/flatpress/issues/90))
- Comment Center plugin: Fixed error on sending mails with umlaut subjects ([#211](https://github.com/flatpressblog/flatpress/issues/211))
- Akismet plugin: Fixed PHP warnings ([#83](https://github.com/flatpressblog/flatpress/issues/83))
- BBCode plugin: Allows local video files ("attachs/video.mp4") and outputs valid HTML ([#192](https://github.com/flatpressblog/flatpress/issues/192))
- BBCode plugin: Initial settings after fresh install shown correctly ([#102](https://github.com/flatpressblog/flatpress/issues/102))
- Calendar plugin: Fixed incorrect text output when Russian is set as language
- Footnotes plugin: Compatibility with [Markdown plugin](https://github.com/flatpressblog/flatpress-extras/tree/master/fp-plugins/markdown) established ([#322](https://github.com/flatpressblog/flatpress/issues/322))
- PrettyURLs plugin: Works properly again with non-Latin characters in entry titles and category names ([#281](https://github.com/flatpressblog/flatpress/issues/281))

## Setup
- Reworked Installer ([#266](https://github.com/flatpressblog/flatpress/issues/266))
  - Image files, which are not used by the installer, were removed.
  - In the setup CSS, unused IDs, classes and incorrect references to fonts have been removed.
  - The installer header now shines in a simple FlatPress style.
  - Added missing language files for Greek, Spanish and French ([#214](https://github.com/flatpressblog/flatpress/issues/214))
  - The installer tries to write permissions recursively for owners and groups, which had to be done manually before.
  - Setup determines local time zone and UTC offset automatically ([#99](https://github.com/flatpressblog/flatpress/issues/99)).

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
  - URLs to the wiki or other external pages are now opened in a second tab in the administration area
  - External URLs in the administration area are now exclusively HTTPS
  - The number of views is now also displayed for the active PostViews plugin when comments are locked ([#346](https://github.com/flatpressblog/flatpress/issues/346))
  - Comments: "The Name and Comment fields are mandatory fields." should not be displayed if the admin is logged in. ([#367](https://github.com/flatpressblog/flatpress/issues/367))

## Internationalization
- Added translation: Slovenian, Danish and Russian ([#278](https://github.com/flatpressblog/flatpress/issues/278))
- Reworked translations: Spanish, Portuguese, Dutch, and Italian
- Fixed wrong pt-br country code ([#100](https://github.com/flatpressblog/flatpress/issues/100))
- German translation for Comment Center plugin added ([#148](https://github.com/flatpressblog/flatpress/issues/148))
- Fixed not-yet-translated phrases in Blog view and Admin Area ([#171](https://github.com/flatpressblog/flatpress/issues/171)), ([#276](https://github.com/flatpressblog/flatpress/issues/276))
- Contact form: Admin notification mail is now localized ([#205](https://github.com/flatpressblog/flatpress/issues/205))
- Setup tries to determine local language automatically ([#197](https://github.com/flatpressblog/flatpress/issues/197), [#216](https://github.com/flatpressblog/flatpress/issues/216), [#262](https://github.com/flatpressblog/flatpress/issues/262))
- The HTML of the installer now has a lang attribute in the html start tag to specify the language.
- BBCode plugin: Localized toolbar button tooltips
- Footnotes plugin: Hard-coded output now localized ([#322](https://github.com/flatpressblog/flatpress/issues/322))
- Admin comment edit panel: Error messages localized ([#304](https://github.com/flatpressblog/flatpress/issues/304))

## Bugfixes
- Plugin management page: Removed empty warning messages box
- Fixed error at prev link on first / next link on last entry ([#95](https://github.com/flatpressblog/flatpress/issues/95))
- Logout redirects to home page again ([#119](https://github.com/flatpressblog/flatpress/issues/119))
- Fixed disappearing non-Latin characters in page title ([#49](https://github.com/flatpressblog/flatpress/issues/49) and [#91](https://github.com/flatpressblog/flatpress/issues/91))
- Worked around strftime() marked as deprecated as of PHP 8.1 ([#92](https://github.com/flatpressblog/flatpress/issues/92)) - thx @bohwaz
- Comments and contact form: Fixed error on sending mails with umlaut subjects ([#207](https://github.com/flatpressblog/flatpress/issues/207), [#209](https://github.com/flatpressblog/flatpress/issues/209))
- Added missing properties in order to prevent "Dynamic properties are deprecated" error under PHP 8.2 ([#115](https://github.com/flatpressblog/flatpress/issues/115))
- Admin maintenance panel: Check file access rights after reset
- Admin comment edit panel: Validation added ([#304](https://github.com/flatpressblog/flatpress/issues/304))
- Fixed broken links in the administration area
- After clearing the theme and template cache, the list of recent comments is rebuilt ([#85](https://github.com/flatpressblog/flatpress/issues/85))

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
