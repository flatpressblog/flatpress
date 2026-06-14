# FlatPress 1.6.dev – Community Test Plan

## Summary

This test plan is for the community test of **FlatPress 1.6.dev v1.0**.

FlatPress 1.6.dev focuses on the changes made after FlatPress 1.5.1, especially:

- the move to the 1.6 development line,
- Smarty 5.8.0 and PHP 8.5 compatibility,
- improved caching for feeds and widgets,
- Location Migration Mode,
- the new Tag, Audio and Video and Mastodon plugins,
- SEO/Open Graph improvements,
- plugin updates and bug fixes,
- light/dark mode improvements for the Stringendo style,
- a new Chinese translation,
- and security hardening for comments and the contact form.

Please work through the plan step by step if possible. If you only have limited time, choose one of the quick test tracks below and then report what you tested.

## Quick test tracks

Choose the track that best matches your environment.

### Track A: Fresh installation

Use this track if you install FlatPress 1.6.dev from scratch.

- [ ] [Install FlatPress 1.6.dev](https://wiki.flatpress.org/en:doc:basic:installation) in a new test directory, for example `/fp16-dev-ebe6e85`.
- [ ] Run `setup.php`.
- [ ] Log in to the admin area.
- [ ] Create one post, one static page, and one comment.
- [ ] Activate the bundled plugins you want to test.
- [ ] Check the front end, admin area, feeds, and PHP error log.

### Track B: Update from FlatPress 1.5.1

Use this track if you already have a FlatPress 1.5.1 test blog.

- [ ] Make a full backup of the test blog.
- [ ] Copy the backup to a separate test directory.
- [ ] Update the copied test blog to FlatPress 1.6.dev.
- [ ] Check whether the blog URL and file paths are still correct.
- [ ] Test Location Migration Mode if the blog was moved to a different directory or domain.
- [ ] Rebuild the index and purge the theme/template cache.
- [ ] Check posts, static pages, comments, uploads, feeds, widgets, and plugins.

### Track C: Big blog and caching

Use this track if you can test a larger amount of content.

- [ ] Create or import many entries and comments.
- [ ] If possible, also test with APCu enabled.
- [ ] Open the home page, categories, archives, search results, feeds, and admin lists.
- [ ] Watch for slow pages, timeouts, blank pages, PHP warnings, and browser console (Ctrl + Shift + I) errors.

## Preparation

1) **Prepare a safe test environment**

- [ ] Do **not** test on your production blog without a complete backup.
- [ ] Note your PHP version.
- [ ] Note whether the PHP `mbstring` extension is enabled.
- [ ] Note whether APCu is available and enabled.
- [ ] Note your web server software, operating system, browser, and browser version.
- [ ] Clear the browser cache before testing.
- [ ] Disable browser extensions that may change pages, block scripts, or inject content.
- [ ] Keep the PHP error log open if you can.

2) **Install or update FlatPress**

- [ ] Download FlatPress 1.6.dev or the current 1.6 development package from the official GitHub release or master branch.
- [ ] Install it as a separate test instance, for example `/fp16-dev-ebe6e85`.
- [ ] If you are updating, use a copied test blog, not your live blog.
- [ ] Run `setup.php` for a fresh installation.
- [ ] Log in to the admin area.
- [ ] Go to **Maintain** and run **Rebuild the FlatPress index**.
- [ ] Go to **Maintain** and run **Purge theme and templates cache**.
- [ ] Activate the bundled plugins that you want to test.
- [ ] Add the needed widgets to a widget bar.

3) **Optional: create test data**

- [ ] Use the [Bulk Content Generator](https://github.com/flatpressblog/flatpress-extras/tree/master/fp-tools/gen-bulk) if you want to test many entries and comments.
- [ ] After generating test data, rebuild the index.
- [ ] Check the front end and admin area after rebuilding.

4) **Check date and time**

- [ ] Open **Configuration → International settings**.
- [ ] Check whether the local time is correct.
- [ ] Create a new post.
- [ ] Open the post in the front end.
- [ ] Expected: the displayed date and time are correct.

## Test areas

### 1. Basic front end and admin area

- [ ] Open the home page.
- [ ] Open a single post.
- [ ] Open a static page, for example `static.php?page=about`.
- [ ] Open a category page.
- [ ] Open the search page and search for a word that exists in a post.
- [ ] Add a comment to a post.
- [ ] Log in to the admin area.
- [ ] Open all main admin menus and submenus.
- [ ] Expected: pages load without blank screens, layout breaks, PHP warnings, or browser console errors.

### 2. Installation, update, and Location Migration Mode

Use this section especially when you update or move an existing test blog.

- [ ] Move a copy of a FlatPress test blog to a different directory or local test domain.
- [ ] Delete `fp-content/%%setup.lock`.
- [ ] Open the moved blog.
- [ ] Check and save the configuration in the admin area.
- [ ] Check whether links, images, uploads, feeds, and admin links point to the new location.
- [ ] Enable or test site migration mode according to the available administrator options.
- [ ] Expected: the moved blog works with the new location and does not keep broken links to the old path.

### 3. PHP, Smarty, and error logs

FlatPress 1.6.dev should work with PHP 7.2 up to current PHP 8.5 test environments.

- [ ] Test with at least one PHP version available to you.
- [ ] If possible, test with PHP 7.2, a current stable PHP 8.x version, and PHP 8.5.
- [ ] Check that the blog works with Smarty 5.8.0.
- [ ] Open the front end after purging the theme/template cache.
- [ ] Open the admin area after purging the theme/template cache.
- [ ] Expected: templates compile successfully and no Smarty-related fatal error appears.
- [ ] Check the PHP error log after browsing the front end and admin area.

### 4. Caching, APCu, feeds, and performance

- [ ] Open the home page several times.
- [ ] Open a category page several times.
- [ ] Open the Categories widget.
- [ ] Open RSS and Atom feeds for posts.
- [ ] Open RSS and Atom feeds for comments.
- [ ] If APCu is available, enable it and repeat the same checks.
- [ ] In the admin area, open the APCu status or cache overview if available.
- [ ] Expected: repeated requests stay fast, feeds remain readable, and there are no cache-related warnings or stale pages.
- [ ] Disable APCu again if you can and repeat a small sample.
- [ ] Expected: FlatPress also works without APCu.

### 5. Admin plugin management and widgets

- [ ] Open **Plugins** in the admin area.
- [ ] Check that plugin names and descriptions are readable.
- [ ] Expected: plugin management does not show broken quotes or strange typographic quote characters.
- [ ] Activate and deactivate a few bundled plugins.
- [ ] Add and remove widgets.
- [ ] Change widget order.
- [ ] Check the front end after each change.
- [ ] Expected: only widgets from active plugins are displayed, and the page remains usable.

### 6. Stringendo style, Leggero theme, and dark mode

- [ ] Select the Leggero theme with the Stringendo style.
- [ ] Open the front end in normal light mode.
- [ ] Switch your browser or operating system to dark mode.
- [ ] Reload the blog.
- [ ] Expected: the Stringendo style follows light/dark mode and remains readable.
- [ ] Check posts, comments, forms, widgets, and static pages.
- [ ] Check the CookieBanner plugin in light and dark mode.
- [ ] Expected: the cookie banner is readable and visually integrated in both modes.
- [ ] Check the page source.
- [ ] Expected: the Open Graph prefix is present in the page header.

### 7. Comment and contact form security regression tests

Do not try to attack a live site. Use harmless test strings only.

- [ ] Open a post with comments enabled.
- [ ] Submit a normal comment.
- [ ] Submit a comment containing special characters such as `<`, `>`, `"`, `'`, `&`, and emojis.
- [ ] Submit a comment containing harmless text that looks like HTML, for example `<b>test</b>`.
- [ ] Open the contact form if it is enabled.
- [ ] Repeat the same harmless special-character tests.
- [ ] Expected: input is displayed safely, no script runs, the page layout is not broken, and no PHP warning appears.
- [ ] Check the PHP error log.

### 8. Tag plugin

The Tag plugin is new in FlatPress 1.6.dev.

- [ ] Activate the Tag plugin.
- [ ] Create a new post with several tags.
- [ ] Use lowercase tags, uppercase tags, mixed-case tags, and tags with spaces or hyphens.
- [ ] Save the post.
- [ ] Open the post in the front end.
- [ ] Expected: the tags are displayed correctly.
- [ ] Click each tag.
- [ ] Expected: each tag page lists the correct posts.
- [ ] Edit the post and change the tags.
- [ ] Expected: removed tags disappear and new tags are searchable/clickable.
- [ ] Create a second post using one of the same tags.
- [ ] Expected: the tag page lists both posts.
- [ ] Deactivate the Tag plugin and open the front end.
- [ ] Expected: the blog does not crash and posts remain readable.

### 10. Audio und Video plugin

This plugin provides simple HTML5 players for audio and video files.

- [ ] Activate the Audio und Video plugin.
- [ ] Upload an `*.mp3` and an `*.mp4` file.
- [ ] Create a new post with an audio clip.
- [ ] Create a new post with a video clip, optionally including a short video or poster description
- [ ] Save the post.
- [ ] Open the post in the front end.
- [ ] Expected: The audio or video file plays in an HTML5 player.
- [ ] Deactivate the Audio und Video plugin and open the front end.
- [ ] Expected: the blog does not crash and posts remain readable.

### 10. Mastodon plugin

Use a test Mastodon account if possible. Do not test with private or sensitive content.

- [ ] Activate the Mastodon plugin.
- [ ] Read the included [Mastodon plugin documentation](https://github.com/flatpressblog/flatpress/blob/master/fp-plugins/mastodon/README.md).
- [ ] Enter the required Mastodon settings.
- [ ] Save the settings.
- [ ] Create a test post.
- [ ] Publish the post.
- [ ] Expected: the plugin posts to Mastodon only when configured to do so.
- [ ] Test a post with tags if the Tag plugin is active.
- [ ] Expected: tags and hashtags behave as described in the plugin settings.
- [ ] Test the optional one-way mode if available.
- [ ] Expected: one-way mode does not import or synchronize content in the opposite direction.
- [ ] Test the Mastodon profile widget.
- [ ] Expected: the profile widget displays the local profile/avatar cache and does not break the page if the Mastodon server is unavailable.
- [ ] Check times and dates shown for Mastodon content.
- [ ] Expected: time zones are displayed correctly.
- [ ] Check links in the Mastodon widget.
- [ ] Expected: internal and external links are handled safely and correctly.

### 11. SEO Meta Tag Info and Open Graph output

- [ ] Activate the SEO Meta Tag Info plugin.
- [ ] Create or edit a post.
- [ ] Add a description, keywords, and robots settings if available.
- [ ] Add a category and several tags.
- [ ] Save the post.
- [ ] Open the post in the front end.
- [ ] View the page source.
- [ ] Expected: standard SEO meta tags are present.
- [ ] Expected: `article:published_time` is present and contains an ISO-like date/time value.
- [ ] Expected: `article:section` reflects the selected category or subcategory.
- [ ] Expected: `article:tag` reflects the post tags.
- [ ] Expected: `article:author` reflects the blog author.
- [ ] Change the blog language.
- [ ] Expected: the plugin settings page does not show missing language-key errors.

### 12. BBCode and Markdown/autolink regressions

- [ ] Activate the BBCode plugin.
- [ ] Create a post using common BBCode tags such as `[b]`, `[i]`, `[url]`, `[img]`, and lists.
- [ ] Preview the post.
- [ ] Save the post.
- [ ] Expected: preview and saved post match.
- [ ] Check the BBCode toolbar.
- [ ] Expected: the HTML button is only shown when inline HTML is allowed.
- [ ] Disable inline HTML if the option is available.
- [ ] Add a plain URL or Markdown-style autolink.
- [ ] Expected: autolinks still work safely.
- [ ] Deactivate the BBCode plugin.
- [ ] Open existing posts and tag-related pages.
- [ ] Expected: the blog does not crash when BBCode is disabled.

### 13. jQuery-dependent user interface tests

FlatPress 1.6.dev includes a newer jQuery plugin with jQuery 4.0.0 and jQuery UI 1.14.2.

- [ ] Open admin pages that use JavaScript.
- [ ] Open the uploader and media-related dialogs.
- [ ] Test widget ordering.
- [ ] Test Comment Center actions.
- [ ] Test Archives widget toggles.
- [ ] Test PhotoSwipe image opening and gallery navigation.
- [ ] Test CookieBanner reset and display.
- [ ] Open the browser developer tools.
- [ ] Expected: no JavaScript errors appear in the console.

### 14. Newsletter plugin

- [ ] Activate the Newsletter plugin.
- [ ] Open the Newsletter admin page.
- [ ] Expected: the description is clear and readable.
- [ ] Register with a normal valid email address.
- [ ] Register the same email address again before confirmation.
- [ ] Expected: the new pending token replaces the old pending token.
- [ ] Try an invalid email address.
- [ ] Try a Unicode/EAI-style address if your mail setup supports it.
- [ ] Expected: validation is clear and no PHP warning appears.
- [ ] If your environment allows DNS checks, test a domain that does not exist.
- [ ] Expected: DNS and domain checks behave as described.
- [ ] If the local disposable-domain blocklist is missing, test first form processing.
- [ ] Expected: FlatPress attempts to fetch or use the blocklist without breaking the form.
- [ ] Check the PHP error log.

### 15. LastComments, PrettyURLs, and feeds

- [ ] Activate LastComments and PrettyURLs.
- [ ] Test every PrettyURLs mode that is available in your setup.
- [ ] Add a new comment.
- [ ] Open the LastComments widget.
- [ ] Open LastComments RSS and Atom feeds.
- [ ] Expected: the newest comments are shown, not the newest posts.
- [ ] Expected: comment links point to the correct comments.
- [ ] Disable PrettyURLs and repeat a small sample.
- [ ] Expected: feeds and comment links still work.

### 16. Other plugin regression tests

Run these tests if you have time.

#### Thumb plugin

- [ ] Insert an external image with width and height settings.
- [ ] Expected: a configured external image height is not overwritten.

#### FootNotes plugin

- [ ] Create a post with footnotes.
- [ ] Add normal text containing `*Text*`.
- [ ] Expected: `*Text*` is not incorrectly rendered as bold text.

#### PhotoSwipe plugin

- [ ] Create a post with a single image.
- [ ] Create a post with an image gallery.
- [ ] Open both in the front end.
- [ ] Expected: images open correctly and gallery navigation works.

#### Archives plugin

- [ ] Add the Archives widget.
- [ ] Open and close archive sections.
- [ ] Click month and year links.
- [ ] Expected: archive links work and no JavaScript error appears.

#### Comment Center plugin

- [ ] Add several comments.
- [ ] Open Comment Center in the admin area.
- [ ] Approve, reject, or edit comments.
- [ ] Expected: actions are saved correctly and the front end updates.

#### CookieBanner plugin

- [ ] Activate the CookieBanner plugin.
- [ ] Open the front end in a private browser window.
- [ ] Expected: the banner appears.
- [ ] Accept or close it.
- [ ] Expected: the banner does not reappear until reset or cookies are cleared.
- [ ] Test again in dark mode.

### 16. Internationalization

- [ ] Switch the blog language to English.
- [ ] Switch the blog language to German.
- [ ] Switch the blog language to Chinese.
- [ ] If you can, also test other bundled languages.
- [ ] Open the front end and admin area after each switch.
- [ ] Expected: pages remain usable and there are no missing language strings in normal screens.
- [ ] Check plugin admin panels for new or changed plugins: Tag, Mastodon, SEO Meta Tag Info, Newsletter, and CookieBanner.
- [ ] Expected: translated labels are readable, or missing translations are easy to identify and report.

### 17. Accessibility and mobile checks

- [ ] Test the front end on a narrow/mobile viewport.
- [ ] Test the admin area on a narrow/mobile viewport.
- [ ] Use keyboard navigation for menus, forms, and buttons.
- [ ] Check form labels in the comment form, contact form, newsletter form, and admin settings.
- [ ] Expected: important functions remain reachable and readable.

### 18. Final smoke test

Before you report your result, please run this short final check.

- [ ] Rebuild the FlatPress index.
- [ ] Purge the theme/template cache.
- [ ] Open the home page.
- [ ] Open one post.
- [ ] Open one static page.
- [ ] Open one feed.
- [ ] Open the admin dashboard.
- [ ] Check the browser console.
- [ ] Check the PHP error log.
- [ ] Expected: no new warning, fatal error, broken layout, or blank page appears.

## Reporting

Please report bugs with:

- FlatPress version or commit hash,
- fresh install or update test,
- PHP version,
- web server and operating system,
- browser and browser version,
- active theme/style,
- active plugins,
- whether APCu is enabled,
- exact steps to reproduce,
- expected result,
- actual result,
- screenshots if useful,
- relevant PHP log messages.

Report bugs in the [GitHub issue tracker](https://github.com/flatpressblog/flatpress/issues). If you are not familiar with GitHub, use the [FlatPress support forum](https://forum.flatpress.org/) instead.

**Thanks a lot for helping to test FlatPress 1.6.dev!**
