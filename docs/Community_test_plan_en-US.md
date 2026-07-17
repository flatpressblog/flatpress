# FlatPress 1.6.dev – Community Test Plan

## Summary

This test plan is for the community test of **FlatPress 1.6.dev v1.0**.

FlatPress 1.6.dev focuses on the changes made after FlatPress 1.5.1, especially:

- the move to the 1.6 development line,
- Smarty 5.8.4 and PHP 8.5 compatibility,
- improved caching for feeds and widgets,
- Location Migration Mode,
- the new Tag, Audio and Video and Mastodon plugins,
- SEO/Open Graph improvements,
- plugin updates and bug fixes,
- light/dark mode improvements for the Stringendo style,
- a new Chinese translation,
- and security hardening for comments and the contact form.

Please work through the plan step by step if possible. If you only have limited time, choose one of the quick test tracks below and then report what you tested.

## How to record results

For every section you run, record one result:

- **PASS** – the expected result was observed.
- **FAIL** – the result differed from the expectation.
- **BLOCKED** – the environment did not provide a required feature, account, extension, or permission.
- **NOT TESTED** – the section was intentionally skipped.

Never use a production blog for destructive, migration, deletion-synchronization, or security-regression tests.

## Quick test tracks

### Track A: Fresh installation

- [ ] Install this FlatPress 1.6.dev snapshot in a new test directory.
- [ ] Complete `setup.php`.
- [ ] Open the admin area once after setup.
- [ ] Create one entry, one static page, and one comment.
- [ ] Open the front end, admin area, RSS feed, Atom feed, and PHP error log.
- [ ] Run Sections 1, 2, 3, 4, 5, 6, 7, 19, and 21.

### Track B: Migration to another URL or directory

- [ ] Use only a disposable copy of an installed blog.
- [ ] Move the copy to a different directory, port, host name, or test domain.
- [ ] Run Section 2 completely.

### Track C: Plugin-focused test

- [ ] Activate only the dependencies required by the plugin under test.
- [ ] Run the relevant plugin section.
- [ ] Deactivate and reactivate the plugin once.
- [ ] Check the front end, admin area, browser console, and PHP error log after both changes.

### Track D: Mastodon basic test

- [ ] Use a disposable Mastodon account and non-sensitive test content.
- [ ] Run Sections 10 and 11.
- [ ] Run Section 12 when comments are enabled.
- [ ] Run Section 13 only when deletion and scheduling tests are safe.

### Track E: Large blog and cache test

- [ ] Use a copied blog with many entries and comments, or generate test content.
- [ ] Run Section 3 with APCu disabled.
- [ ] If available, enable APCu and repeat Section 3.
- [ ] Watch for timeouts, stale output, blank pages, PHP warnings, and browser-console errors.

## Preparation

### Test environment

- [ ] Confirm that the test instance is not the production instance.
- [ ] Make a complete backup before update, migration, or deletion tests.
- [ ] Record the FlatPress snapshot or commit hash.
- [ ] Record the PHP version.
- [ ] Record whether `mbstring` is enabled.
- [ ] Record whether `intl` is enabled.
- [ ] Record whether GD is enabled.
- [ ] Record whether cURL is enabled.
- [ ] Record whether `allow_url_fopen` is enabled.
- [ ] Record whether APCu is available and enabled for web requests.
- [ ] Record the web server, operating system, and hosting type.
- [ ] Record the browser and browser version.
- [ ] Record the active theme and style.
- [ ] Record all active plugins.
- [ ] Clear the browser cache.
- [ ] Open the browser developer console.
- [ ] Keep the PHP error log available when possible.

### Test data

Create clearly recognizable test data. Avoid real addresses, secrets, and personal content.

- [ ] Create an entry with a normal ASCII title.
- [ ] Create an entry with umlauts or other non-ASCII letters.
- [ ] Create an entry with a supplementary-plane emoji such as `😀`.
- [ ] Create a static page with an emoji in the title.
- [ ] Create at least two categories, including one subcategory.
- [ ] Create several comments, including one reply to another comment.
- [ ] Upload one JPEG or PNG image.
- [ ] Upload one MP3 file and one MP4 file when media tests are planned.
- [ ] Add at least two entries sharing one tag when Tag tests are planned.
- [ ] Rebuild the FlatPress index after bulk content generation.

## Test areas

### 1. Baseline front end and admin smoke test

- [ ] Open the home page.
- [ ] Open a single entry.
- [ ] Open a static page.
- [ ] Open a category and a subcategory.
- [ ] Open archive pages for a year and a month.
- [ ] Search for text that exists in an entry.
- [ ] Add a normal comment.
- [ ] Reply to an existing comment.
- [ ] Log in to the admin area.
- [ ] Open every main admin menu.
- [ ] Open every submenu belonging to an active bundled plugin.
- [ ] Edit and save an entry.
- [ ] Edit and save a static page.
- [ ] Delete a disposable entry or static page.
- [ ] Log out and log in again.
- [ ] Expected: no blank page, broken layout, PHP warning, uncaught exception, Smarty error, or browser-console error appears.

### 2. Fresh installation, update, canonical URL, and Location Migration Mode

#### Fresh installation

- [ ] Run `setup.php` in a new installation.
- [ ] Complete setup using a normal blog URL.
- [ ] Open the admin area after setup.
- [ ] Expected: setup completes without a blank page or permission warning that is unrelated to the actual host.
- [ ] Expected: FlatPress creates its installation lock.
- [ ] Expected: the admin area does not continue to treat the instance as uninstalled.
- [ ] Expected: when setup entry points can be hidden, no setup-hiding warning remains.
- [ ] Optional file-access check: verify that normal program files were not rewritten during setup and instance data was created under `fp-content/`.

#### Location Migration Mode

- [ ] Move an installed test copy to a different directory, port, host name, or test domain.
- [ ] Delete only `fp-content/%%setup.lock` in the moved copy.
- [ ] Open the moved blog through the new URL.
- [ ] Log in to the admin area through the new URL.
- [ ] Expected: the dashboard states that Location Migration Mode is active.
- [ ] Expected: the temporarily detected blog URL matches the new location.
- [ ] Follow the dashboard link to the configuration panel.
- [ ] Check the displayed Blog URL carefully.
- [ ] Save the configuration.
- [ ] Expected: the save completes the migration and recreates the installation lock.
- [ ] Reload the dashboard.
- [ ] Expected: the migration notice is gone.
- [ ] Open the home page, an entry, a static page, a category, an uploaded image, RSS, Atom, login, and admin pages.
- [ ] Expected: links use the new location and do not point to the old one.
- [ ] View the page source and check CSS, JavaScript, image, feed, and canonical/Open Graph URLs.
- [ ] Expected: assets and metadata use the new location.
- [ ] Edit and save one entry after migration.
- [ ] Expected: the saved entry remains reachable at the new location.
- [ ] Reopen the blog in a new browser session.
- [ ] Expected: migration mode does not reactivate by itself.
- [ ] Optional file-access check: verify that cache and compiled-template files were regenerated for the new location.
- [ ] Failure-path test on a disposable copy: make `fp-content/` unwritable before saving the migrated configuration.
- [ ] Expected: FlatPress reports that migration completion could not be written instead of silently claiming success.

#### Canonical scheme

- [ ] When the configured Blog URL uses HTTPS, open the equivalent HTTP URL.
- [ ] Expected: the request upgrades to the configured HTTPS URL without a redirect loop.
- [ ] When testing behind a reverse proxy or CDN, repeat the login, admin, entry, feed, and asset checks.
- [ ] Expected: the visible URL and generated links stay consistent with the configured public URL.

### 3. PHP, Smarty, caching, APCu, feeds, and freshness

#### PHP and Smarty

- [ ] Run the smoke test with at least one available PHP version.
- [ ] Optional matrix: repeat with PHP 7.2, one current PHP 8.x version, and PHP 8.5.
- [ ] Confirm that Smarty `5.8.4` is present in the tested snapshot.
- [ ] Purge the theme and template cache.
- [ ] Open the front end immediately after purging.
- [ ] Open the admin area immediately after purging.
- [ ] Expected: templates compile successfully without a Smarty fatal error.
- [ ] Repeat with `mbstring` enabled.
- [ ] Optional host test: repeat a feed request without `mbstring` when the environment allows this safely.
- [ ] Expected: the feed does not fail fatally.

#### Feed and Categories-widget block cache

- [ ] Add the Categories widget to a visible widget area.
- [ ] Open the home page twice.
- [ ] Open a category page twice.
- [ ] Open the post RSS and Atom feeds twice.
- [ ] Open the comment RSS and Atom feeds twice.
- [ ] Expected: repeated requests remain readable and stable.
- [ ] Create a new category and assign a new entry to it.
- [ ] Reload the Categories widget.
- [ ] Expected: the new category appears without serving a permanently stale fragment.
- [ ] Rename the category.
- [ ] Expected: the widget shows the renamed category.
- [ ] Add a new entry.
- [ ] Expected: post RSS and Atom feeds include the new entry.
- [ ] Edit the entry title and content.
- [ ] Expected: the feeds show the updated entry.
- [ ] Add a new comment.
- [ ] Expected: comment feeds and LastComments output include the new comment.
- [ ] Edit or delete the disposable comment.
- [ ] Expected: the affected feed/widget output is refreshed.
- [ ] Purge the theme/template cache and repeat one request.
- [ ] Expected: output remains correct after regeneration.

#### APCu

- [ ] Run the cache checks with APCu disabled or unavailable.
- [ ] Expected: FlatPress works correctly without APCu.
- [ ] If APCu is available for web requests, enable it.
- [ ] Open the APCu or cache overview in the admin area.
- [ ] Repeat the Categories-widget and feed freshness checks.
- [ ] Expected: output is current and no APCu warning appears.
- [ ] Clear the FlatPress/APCu cache from the admin control when available.
- [ ] Expected: the next request regenerates correct content.
- [ ] Disable APCu again and repeat a small sample.
- [ ] Expected: switching cache availability does not break pages, feeds, or widgets.

### 4. Plugin management, dependencies, and widgets

- [ ] Open the plugin-management page.
- [ ] Read every bundled plugin name and description.
- [ ] Expected: normal straight quotation marks are used and no broken typographic characters appear.
- [ ] Expected: allowed description links are clickable and no raw unsafe markup is displayed.
- [ ] Activate and deactivate several bundled plugins.
- [ ] Expected: the page immediately shows the actual activation state.
- [ ] Activate a plugin that provides a widget.
- [ ] Add its widget to a widget area.
- [ ] Reorder widgets with a mouse.
- [ ] Reorder widgets on a touch or narrow-screen device.
- [ ] Save the widget layout.
- [ ] Expected: the order persists on the front end.
- [ ] Deactivate the widget-providing plugin.
- [ ] Expected: its orphaned widget is not rendered.
- [ ] Reactivate the plugin.
- [ ] Expected: widget management remains usable without duplicate output.
- [ ] Remove all widgets from one area.
- [ ] Expected: the theme does not leave a broken empty column or wrapper.
- [ ] Check the PHP error log after activation, deactivation, and widget changes.

### 5. PrettyURLs, Unicode, emoji, and feed links

- [ ] Activate PrettyURLs.
- [ ] Open its admin page before saving any custom mode.
- [ ] Expected: a supported mode is detected without requiring an existing `.htaccess` file.
- [ ] Test every PrettyURLs mode offered as supported by the current web server.
- [ ] Expected: unsupported modes are not presented as safe choices.
- [ ] Create an entry title containing `😀`, accented letters, and non-Latin text.
- [ ] Save and open the entry.
- [ ] Expected: the generated slug is valid, stable, and reachable.
- [ ] Copy the entry URL into a new private browser window.
- [ ] Expected: the URL opens the same entry.
- [ ] Create a static-page title containing `😀`.
- [ ] Expected: the static-page URL is valid and reachable.
- [ ] Edit the emoji entry without changing its title.
- [ ] Expected: its URL remains usable.
- [ ] Open RSS and Atom feeds containing the emoji-titled entry.
- [ ] Expected: feed XML remains readable and the entry link works.
- [ ] Add a comment containing emoji to the entry.
- [ ] Expected: the comment anchor/link works in normal and PrettyURLs modes.
- [ ] Disable PrettyURLs and repeat the entry, static-page, comment-link, and feed checks.
- [ ] Expected: standard query-string URLs still work.
- [ ] Optional IIS test: use the available IIS/web.config configuration and repeat mode detection and routing checks.

### 6. Leggero/Stringendo, dark mode, widget zones, and Readmore

- [ ] Select the Leggero theme and Stringendo style.
- [ ] Open the front end in light mode.
- [ ] Switch the browser or operating system to dark mode.
- [ ] Reload the page.
- [ ] Expected: Stringendo follows the preference and remains readable.
- [ ] Check entries, static pages, comments, forms, code blocks, tables, widgets, links, focus indicators, and error messages in both modes.
- [ ] Add widgets to the left, right, and bottom areas that are available in the style.
- [ ] Test with zero, one, and several bottom widgets.
- [ ] Expected: columns and bottom areas align without overlap or empty broken containers.
- [ ] Open the same pages on a narrow/mobile viewport.
- [ ] Expected: widget zones stack or resize without hiding content.
- [ ] Open the admin area.
- [ ] Expected: front-end bottom-widget output is not injected into admin pages.
- [ ] Activate the Readmore plugin.
- [ ] Create a long entry that produces a Read more control.
- [ ] Check the control in light mode, dark mode, desktop width, and mobile width.
- [ ] Expected: the control is vertically aligned and remains keyboard reachable.
- [ ] Activate CookieBanner and open a private browser window.
- [ ] Expected: the banner is readable and visually integrated in both light and dark modes.
- [ ] View the page source.
- [ ] Expected: the Open Graph prefix is present on the HTML element/header markup as implemented by the theme.

### 7. Comment/contact security, BBCode, Parsedown, autolinks, and FootNotes

Use harmless strings only. Do not attack a public site.

#### Comment and contact form escaping

- [ ] Submit a normal comment.
- [ ] Submit a comment containing `<`, `>`, `"`, `'`, `&`, and emoji.
- [ ] Submit harmless text that looks like HTML, for example `<b>test</b>`.
- [ ] Submit harmless attribute-like text, for example `" onmouseover="test`.
- [ ] Repeat the harmless inputs in the contact form.
- [ ] Expected: no script runs, the page layout remains intact, and input is displayed or rejected safely.
- [ ] Expected: success and validation messages are readable.
- [ ] Check the PHP error log.

#### Lists and formatting in comments

- [ ] With the relevant formatting plugins active, submit an unordered list in a comment.
- [ ] Submit an ordered list in a comment.
- [ ] Preview or open the comment.
- [ ] Expected: list items are structured correctly and do not merge into surrounding text.
- [ ] Add normal line breaks and blank lines.
- [ ] Expected: line breaks are preserved according to the active parser settings.

#### BBCode

- [ ] Activate BBCode.
- [ ] Create an entry using `[b]`, `[i]`, `[url]`, `[img]`, ordered lists, and unordered lists.
- [ ] Preview and save the entry.
- [ ] Expected: preview and saved output agree.
- [ ] Check the editor toolbar.
- [ ] Allow inline HTML.
- [ ] Expected: the HTML toolbar button is visible.
- [ ] Disable inline HTML.
- [ ] Expected: the HTML toolbar button is hidden.
- [ ] With inline HTML disabled, add a plain URL and a Markdown-style autolink.
- [ ] Expected: safe autolinks still work.
- [ ] Use a `[font]` tag without a font value.
- [ ] Expected: no fatal error or broken output occurs.
- [ ] Use the font selector and save an entry.
- [ ] Expected: the selected/default font is rendered consistently.
- [ ] Deactivate BBCode while Tag is active.
- [ ] Expected: the admin area shows a clear dependency state or the Tag plugin remains safely usable through its bundled parser path; the blog must not crash.
- [ ] Re-enable BBCode and check existing entries again.

#### FootNotes and Markdown emphasis

- [ ] Activate FootNotes.
- [ ] Create an entry with at least one footnote.
- [ ] Add literal text `*Text*`.
- [ ] Expected: `*Text*` is not incorrectly converted to bold.
- [ ] Add normal italic/emphasis text supported by the active parser.
- [ ] Expected: intended emphasis still works.

#### Autolinks in comments

- [ ] Add a normal HTTP or HTTPS URL to a comment.
- [ ] Add a normal email address to a comment.
- [ ] Expected: allowed autolinks are rendered safely.
- [ ] Expected: links do not inject HTML and do not break comment layout.

### 8. Tag plugin

- [ ] Activate BBCode and the Tag plugin.
- [ ] Expected: Tag activates without an error.
- [ ] Create an entry with several tags.
- [ ] Include lowercase, uppercase, mixed-case, Unicode, spaces, hyphens, punctuation, and leading `#` in the input.
- [ ] Save the entry.
- [ ] Expected: invalid punctuation is removed safely.
- [ ] Expected: multi-word tags are normalized consistently.
- [ ] Expected: duplicates that differ only by case are not stored twice.
- [ ] Open the entry.
- [ ] Expected: its tags are shown once and link to tag result pages.
- [ ] Click every tag.
- [ ] Expected: each tag page lists the correct entries.
- [ ] Create a second entry sharing one tag.
- [ ] Expected: the shared tag page lists both entries.
- [ ] Edit the first entry and remove one tag.
- [ ] Expected: the removed tag no longer belongs to that entry.
- [ ] Add the Tag/Keywords cloud widget.
- [ ] Expected: tags are readable, sorted, and linked.
- [ ] Add the Related Entries widget.
- [ ] Open an entry with shared tags.
- [ ] Expected: related entries are shown without listing the current entry as a duplicate.
- [ ] Open an entry with no related entries.
- [ ] Expected: a readable empty-state message appears.
- [ ] Test tag pages and related links with PrettyURLs enabled.
- [ ] Test them again with PrettyURLs disabled.
- [ ] Expected: pagination, next/previous links, and comment links work in both modes.
- [ ] Edit tags repeatedly and reload widgets.
- [ ] Expected: tag and related-entry caches do not serve permanently stale results.
- [ ] Deactivate Tag.
- [ ] Expected: entries remain readable and the front end does not crash.
- [ ] Optional dependency test: deactivate BBCode before activating Tag.
- [ ] Expected: any dependency warning is understandable and plugin management remains usable.

### 9. AudioVideo plugin

- [ ] Activate BBCode and AudioVideo.
- [ ] Upload an MP3 and an MP4 file.
- [ ] Create an entry with `[audioplayer="attachs/file.mp3"]`.
- [ ] Create an entry with `[videoplayer="attachs/file.mp4"]`.
- [ ] Expected: both render as native HTML5 players.
- [ ] Test `controls`, `autoplay`, and `loop`.
- [ ] Expected: supported options are reflected without breaking the page.
- [ ] Test video `width` and `height`.
- [ ] Expected: the video remains usable on desktop and mobile widths.
- [ ] Upload a poster image and use the `poster` option.
- [ ] Expected: the poster appears before playback.
- [ ] Add text between opening and closing audio/video tags.
- [ ] Expected: the description is available as an accessible label/title.
- [ ] Test a missing media path.
- [ ] Expected: the page remains usable and no fatal error occurs.
- [ ] Open the entry in RSS and Atom feeds.
- [ ] Expected: feed output remains valid even if playback is not available in the feed reader.
- [ ] Deactivate AudioVideo and reopen the entry.
- [ ] Expected: the blog does not crash.
- [ ] Reactivate AudioVideo and verify that the players return.

### 10. Mastodon configuration, dependencies, OAuth, and instance information

Use a disposable Mastodon account and non-sensitive content.

#### Dependencies and admin page

- [ ] Activate Mastodon.
- [ ] Open **Plugins → Mastodon**.
- [ ] Expected: companion-plugin recommendations are readable.
- [ ] Activate BBCode, PhotoSwipe, AudioVideo, Tag, and Emoticons when their related tests are planned.
- [ ] Enter only the Mastodon server base URL, not a profile or status URL.
- [ ] Enter the username, daily sync time, and synchronization start date.
- [ ] Save the settings.
- [ ] Expected: saved values remain visible after reload.
- [ ] Change the scheduled window between the available choices.
- [ ] Expected: the selected value persists.
- [ ] Enter an invalid server URL.
- [ ] Expected: a clear validation or connection error appears without losing unrelated settings.

#### OAuth

- [ ] Click **Register Mastodon app**.
- [ ] Expected: an authorization URL is generated.
- [ ] Open the authorization URL.
- [ ] Approve the app using the test account.
- [ ] Copy the authorization code back into FlatPress.
- [ ] Click **Exchange code for token**.
- [ ] Expected: the admin page reports that a usable token is saved.
- [ ] Run a synchronization.
- [ ] Expected: the token works and is not displayed in clear text.
- [ ] Optional: clear the token.
- [ ] Expected: synchronization stops with a clear “no access token” message.
- [ ] Reconnect before continuing.

#### Instance information and status

- [ ] Click **Fetch Mastodon instance information**.
- [ ] Expected: the server version and available limits/capabilities are displayed or a clear unsupported/error state is shown.
- [ ] Reload the admin page.
- [ ] Expected: stored instance information remains readable.
- [ ] Check Last sync, Last deletion sync, Last error, and all counters.
- [ ] Expected: dates use the configured FlatPress time offset and are understandable.
- [ ] Run **Run synchronization now**.
- [ ] Expected: status and counters update consistently.
- [ ] Run a second time without changes.
- [ ] Expected: unchanged content is not counted repeatedly as newly imported/exported.
- [ ] Temporarily make the Mastodon server unavailable or use a harmless invalid test endpoint.
- [ ] Expected: the admin page reports the error and the FlatPress front end remains available.

### 11. Mastodon entry, tag, text, and media synchronization

#### FlatPress to Mastodon

- [ ] Create and publish a simple FlatPress entry after the configured start date.
- [ ] Run synchronization.
- [ ] Expected: one public Mastodon top-level status is created.
- [ ] Expected: the status contains adapted entry text and a public FlatPress link when the blog is publicly reachable.
- [ ] Edit the local entry.
- [ ] Run synchronization again.
- [ ] Expected: the mapped Mastodon content is updated instead of duplicated.
- [ ] Create a draft entry.
- [ ] Expected: a draft is not exported as a public status.
- [ ] Add Unicode and emoji to the entry.
- [ ] Expected: they survive export without broken encoding.
- [ ] With Emoticons active, include supported emoticon shortcodes.
- [ ] Expected: exported text is readable and does not contain broken parser artifacts.
- [ ] Add FlatPress tags.
- [ ] Expected: tags are exported as Mastodon hashtags when Tag is active.
- [ ] Disable Tag and repeat with a disposable entry.
- [ ] Expected: synchronization continues without tag-related failure.

#### Images, audio, and video export

- [ ] Add one local `[img]` image to an entry.
- [ ] Run synchronization.
- [ ] Expected: the image is attached to the Mastodon status.
- [ ] Add a local `[gallery]` with several images within the instance limit.
- [ ] Expected: allowed images are attached without duplicating the status.
- [ ] Add a known image description.
- [ ] Expected: the description is sent as media alt text when supported.
- [ ] Change only the image description and resynchronize.
- [ ] Expected: the description is updated when the server supports media updates, without unnecessary duplicate content.
- [ ] Add one `[audioplayer]` file.
- [ ] Expected: the audio attachment is uploaded and processed.
- [ ] Add one `[videoplayer]` file with a poster.
- [ ] Expected: the video is uploaded and a usable thumbnail/poster is used when supported.
- [ ] Try more attachments than the server permits.
- [ ] Expected: extra media is skipped or reported without aborting all synchronization.
- [ ] Try a file that exceeds the server limit.
- [ ] Expected: the error identifies the media problem and the FlatPress entry remains intact.
- [ ] Expected: media in FlatPress comments is not exported as Mastodon media.

#### Mastodon to FlatPress

- [ ] Publish a public top-level Mastodon status after the configured start date.
- [ ] Run synchronization.
- [ ] Expected: it becomes one FlatPress entry.
- [ ] Expected: the author/profile link is safe and external links open as intended without changing unrelated local links.
- [ ] Publish a followers-only/private status.
- [ ] Expected: it is not imported.
- [ ] Publish a direct status.
- [ ] Expected: it is not imported.
- [ ] Add Mastodon hashtags.
- [ ] Expected: they become FlatPress tags when Tag is active.
- [ ] Attach one image.
- [ ] Expected: it is downloaded and referenced as an image.
- [ ] Attach several images.
- [ ] Expected: they are represented as a gallery.
- [ ] Attach audio.
- [ ] Expected: it is stored below the Mastodon attachment area and rendered by AudioVideo.
- [ ] Attach video or GIFV with a preview image.
- [ ] Expected: it is rendered as video and the preview is used as a poster when available.
- [ ] Temporarily disable AudioVideo.
- [ ] Expected: imported content remains readable and the blog does not crash.
- [ ] Re-enable AudioVideo.
- [ ] Expected: imported audio/video players render again.

### 12. Mastodon replies, visitor opt-in, and Comment Center

#### Reply chains

- [ ] Synchronize a FlatPress entry to Mastodon.
- [ ] Add a FlatPress comment under that entry as the authenticated blog author.
- [ ] Run synchronization.
- [ ] Expected: it becomes a Mastodon reply under the mapped status.
- [ ] Add a reply to that FlatPress comment.
- [ ] Expected: the reply hierarchy is preserved on Mastodon.
- [ ] Add a Mastodon reply to the top-level status.
- [ ] Run synchronization.
- [ ] Expected: it becomes a FlatPress comment under the mapped entry.
- [ ] Add a Mastodon reply to another reply.
- [ ] Expected: it is attached to the matching local parent when possible.
- [ ] Add a reply from another Mastodon account inside the known thread.
- [ ] Expected: it is imported as a comment when allowed by visibility and synchronization options.
- [ ] Check imported comment dates.
- [ ] Expected: they reflect the Mastodon creation time using the configured FlatPress time offset.
- [ ] Check imported author/profile links with PrettyURLs enabled and disabled.
- [ ] Expected: local comment anchors work and external profile links are handled safely.

#### Visitor comment export opt-in

- [ ] Open the public comment form while comment/reply synchronization is enabled.
- [ ] Expected: a Mastodon/Fediverse export opt-in checkbox is shown to visitors.
- [ ] Submit a visitor comment without selecting the checkbox.
- [ ] Run synchronization.
- [ ] Expected: the local comment remains local and is not exported.
- [ ] Submit another visitor comment with the checkbox selected.
- [ ] Run synchronization.
- [ ] Expected: the comment is exported as a Mastodon reply.
- [ ] Trigger a harmless comment validation error after selecting the checkbox.
- [ ] Expected: the checkbox remains selected when the form is shown again.
- [ ] Disable comment/reply synchronization.
- [ ] Expected: the opt-in checkbox is hidden and visitor comments are not exported.
- [ ] Re-enable comment/reply synchronization.

#### Comment Center

- [ ] Activate Comment Center moderation.
- [ ] Submit an opted-in visitor comment that is held for moderation.
- [ ] Expected: the comment is not exported before approval.
- [ ] Approve the comment in Comment Center.
- [ ] Run synchronization.
- [ ] Expected: the approved opted-in comment is exported once.
- [ ] Submit another opted-in comment and reject/discard it.
- [ ] Expected: it is not exported later.
- [ ] Deactivate Comment Center.
- [ ] Submit an opted-in visitor comment through the direct save path.
- [ ] Expected: it can be exported after normal saving.
- [ ] Expected: Comment Center is optional; Mastodon and comments continue to work without it.
- [ ] Confirm that merely being logged into the admin area does not grant export permission to unrelated visitor comments.

### 13. Mastodon options, scheduling, old threads, profile widget, and deletion

Use only disposable synchronized content for deletion tests.

#### Options

- [ ] Enable **Optional one-way mode / Disable Mastodon-to-FlatPress import**.
- [ ] Create one local entry and one remote status.
- [ ] Run synchronization.
- [ ] Expected: local export works, but the remote status is not imported.
- [ ] Disable one-way mode.
- [ ] Expected: remote import resumes.
- [ ] Enable **Disable comment/reply sync**.
- [ ] Add local and remote replies.
- [ ] Expected: entries may synchronize, but comments/replies do not.
- [ ] Enable **Update existing local content from Mastodon**.
- [ ] Edit a previously imported Mastodon status.
- [ ] Expected: the mapped local content updates within the documented limits.
- [ ] Disable that option and edit the remote status again.
- [ ] Expected: existing local text is not overwritten by the remote edit.
- [ ] Toggle **Import comments as entries as well**.
- [ ] Expected: when disabled, known-thread replies stay in comments; when enabled, the documented additional entry behavior is visible without duplicate corruption.
- [ ] Toggle **Quote imported reply parent**.
- [ ] Expected: imported reply presentation follows the selected option without breaking nesting.
- [ ] Enable **Check known synchronized Mastodon threads for new replies**.
- [ ] Add a new reply to an older mapped thread.
- [ ] Expected: the reply is eventually imported within the configured/budgeted checks.

#### Start date and scheduled window

- [ ] Set a synchronization start date after an old local entry and old remote status.
- [ ] Run normal synchronization.
- [ ] Expected: older content outside the selected range is not newly synchronized.
- [ ] Move the start date backward in a small block.
- [ ] Run a manual/full synchronization.
- [ ] Expected: eligible older content is processed without duplicating already mapped content.
- [ ] Set the daily sync time a few minutes ahead.
- [ ] Wait until it is due, then open a normal front-end page with a GET request.
- [ ] Expected: the scheduled run starts on the first suitable request after the due time.
- [ ] Submit a normal form POST at the due time.
- [ ] Expected: the POST itself does not unexpectedly run the scheduled synchronization.
- [ ] Open the admin status afterwards.
- [ ] Expected: the shown sync time uses the configured FlatPress offset.

#### Profile widget

- [ ] Add the Mastodon profile widget.
- [ ] Run synchronization or refresh profile data.
- [ ] Expected: display name, account name, profile link, avatar, and available counts are shown.
- [ ] Expected: the avatar uses meaningful alt text.
- [ ] Reload the page several times.
- [ ] Expected: widget rendering uses the local cache and remains fast.
- [ ] Make the Mastodon server temporarily unavailable.
- [ ] Expected: the widget uses its last valid local cache or hides safely; it must not break the page.
- [ ] Change the remote avatar while keeping the same avatar URL when the server permits it.
- [ ] Refresh profile data.
- [ ] Expected: the local avatar cache is updated.
- [ ] Remove or corrupt the disposable profile cache.
- [ ] Expected: no secret appears in the front end and the widget fails safely until refreshed.

#### Deletion synchronization

- [ ] Enable deletion synchronization.
- [ ] Synchronize one disposable local entry and comment to Mastodon.
- [ ] Delete the local comment.
- [ ] Run normal synchronization.
- [ ] Expected: the locally deleted comment is not reimported before the delete pass.
- [ ] After the documented follow-up delay, trigger another normal request or run the deletion action.
- [ ] Expected: the mapped Mastodon reply is deleted when allowed.
- [ ] Delete the local entry.
- [ ] Expected: the mapped remote status is handled by deletion synchronization without damaging unrelated mappings.
- [ ] Import one disposable remote status and reply.
- [ ] Delete the remote reply and run deletion synchronization.
- [ ] Expected: the mapped local comment is removed or marked according to the configured deletion behavior.
- [ ] Delete the remote top-level status.
- [ ] Expected: the mapped local content is handled without orphaning unrelated entries.
- [ ] Disable deletion synchronization and repeat with new disposable content.
- [ ] Expected: no cross-side deletion occurs.
- [ ] Check normal-sync and deletion-sync counters separately.
- [ ] Expected: deletion counters may update on the later follow-up run, and do not overwrite normal counters.
- [ ] Run a second deletion pass.
- [ ] Expected: already handled deletions are not counted repeatedly.

### 14. SEO Meta Tag Info, Open Graph, robots, images, and language changes

- [ ] Activate SEO Meta Tag Info.
- [ ] Create or edit an entry.
- [ ] Add description, keywords, and robots settings.
- [ ] Add a category, subcategory, and several Tag-plugin tags.
- [ ] Save and open the entry.
- [ ] View the page source.
- [ ] Expected: standard SEO meta tags are present and safely escaped.
- [ ] Expected: `article:published_time` contains an ISO-8601-like date/time with the correct offset.
- [ ] Expected: `article:section` reflects the category/subcategory.
- [ ] Expected: `article:tag` reflects the entry tags without duplicates.
- [ ] Expected: `article:author` reflects the configured blog author.
- [ ] Expected: the Open Graph prefix is present in the theme markup.
- [ ] Add one local image to the entry.
- [ ] Expected: the selected/generated Open Graph image is reachable.
- [ ] Test with GD enabled when available.
- [ ] Expected: image handling produces no warning.
- [ ] Test without a suitable image.
- [ ] Expected: the plugin uses its documented fallback or omits the image safely.
- [ ] Edit SEO data and reload the page.
- [ ] Expected: updated metadata is not hidden by a stale cache.
- [ ] Change the blog language.
- [ ] Expected: the plugin admin page shows no missing-language-key error.
- [ ] Open the robots.txt panel.
- [ ] Test a writable valid web-root location.
- [ ] Expected: save succeeds and the file content is readable.
- [ ] Test a location where the file cannot be saved, using a disposable environment.
- [ ] Expected: a clear “cannot save” warning appears.
- [ ] Test with PrettyURLs enabled and disabled.
- [ ] Expected: robots and metadata controls do not become incorrectly coupled to the PrettyURLs choice.

### 15. Newsletter plugin

Use a mail-safe test environment and addresses you control.

- [ ] Activate LastEntries and Newsletter.
- [ ] Expected: the Newsletter dependency state is clear.
- [ ] Open the Newsletter admin page.
- [ ] Expected: the description is readable and uses normal quotation marks.
- [ ] Add the subscription widget.
- [ ] Submit without accepting the privacy checkbox.
- [ ] Expected: registration is rejected clearly.
- [ ] Submit an invalid email address.
- [ ] Expected: validation is clear and no PHP warning appears.
- [ ] Submit a normal valid address.
- [ ] Expected: one pending confirmation is created and a confirmation message is sent.
- [ ] Submit the same address again before confirming.
- [ ] Expected: a new pending token replaces the old pending token.
- [ ] Open the older confirmation link.
- [ ] Expected: it is rejected as invalid or expired.
- [ ] Open the newest confirmation link.
- [ ] Expected: the subscription is confirmed once.
- [ ] Open the newest link a second time.
- [ ] Expected: it does not create a duplicate subscriber.
- [ ] Use the unsubscribe link.
- [ ] Expected: the subscriber is removed and a success page appears.
- [ ] Test an EAI/Unicode local-part address when the mail setup supports it.
- [ ] Expected: the result follows the documented validation and no warning appears.
- [ ] Test an IDN domain with `intl` enabled.
- [ ] Expected: the domain is normalized/validated correctly.
- [ ] Optional: repeat an IDN-domain test without `intl`.
- [ ] Expected: unsupported non-ASCII domains are rejected clearly rather than queried incorrectly.
- [ ] Test a domain that does not exist when DNS functions are available.
- [ ] Expected: DNS/domain validation behaves as documented.
- [ ] Temporarily remove the disposable-domain blocklist in a disposable copy.
- [ ] Submit the first subscription request.
- [ ] Expected: FlatPress attempts to obtain/use the blocklist without breaking the form.
- [ ] Simulate an unavailable blocklist source.
- [ ] Expected: the form continues with other validation and logs the problem without a blank page.
- [ ] Open the subscriber list in the admin area.
- [ ] Expected: subscribers are shown once and dates/times are readable.
- [ ] Change the batch size and save.
- [ ] Expected: the setting persists.
- [ ] Use **Send now** with a valid admin session.
- [ ] Expected: the action starts once and reports its result.
- [ ] Delete a subscriber through the admin page.
- [ ] Expected: the subscriber is removed once.
- [ ] Check the PHP error log after subscription, confirmation, unsubscribe, and admin actions.

### 16. jQuery 4 and JavaScript-dependent bundled plugins

The bundled jQuery plugin provides jQuery 4.0.0 and jQuery UI 1.14.2. Edge Legacy, Internet Explorer 9–10, iOS 7, and the Android 4 stock browser are not supported by this path.

- [ ] Activate the jQuery plugin.
- [ ] Open the browser console.
- [ ] Open admin pages that use JavaScript.
- [ ] Test widget drag-and-drop with mouse and touch.
- [ ] Open uploader and media dialogs.
- [ ] Activate Archives and open/close archive sections.
- [ ] Click month and year links.
- [ ] Activate Comment Center and approve, reject, and edit disposable comments.
- [ ] Activate CookieBanner in a private window.
- [ ] Accept/close the banner.
- [ ] Expected: it stays dismissed until reset or cookies are cleared.
- [ ] Use the CookieBanner reset action.
- [ ] Expected: the banner appears again.
- [ ] Activate PhotoSwipe.
- [ ] Open one image.
- [ ] Open a gallery and navigate forward/backward.
- [ ] Close PhotoSwipe with mouse, touch, and keyboard.
- [ ] Expected: no JavaScript error appears in any tested flow.
- [ ] Deactivate the jQuery plugin.
- [ ] Reopen PhotoSwipe.
- [ ] Expected: the documented fallback path remains usable.
- [ ] Reactivate jQuery and repeat one sample.
- [ ] Expected: scripts are loaded once and controls are not duplicated.

### 17. LastComments, feeds, PrettyURLs, and Unicode

- [ ] Activate LastComments.
- [ ] Add its widget.
- [ ] Add several comments to different entries.
- [ ] Expected: the widget shows the newest comments, not the newest entries.
- [ ] Click every displayed comment link.
- [ ] Expected: each link opens the correct comment anchor.
- [ ] Open LastComments RSS and Atom feeds.
- [ ] Expected: they contain the newest comments.
- [ ] Expected: each feed item links to the formatted comment URL.
- [ ] Enable PrettyURLs and repeat.
- [ ] Disable PrettyURLs and repeat.
- [ ] Expected: both routing modes work.
- [ ] Add emoji and non-Latin text to a comment.
- [ ] Expected: widget and feeds preserve valid UTF-8.
- [ ] Edit or delete a disposable comment.
- [ ] Expected: widget and feed caches refresh and do not keep permanently stale data.

### 18. Thumb, external images, and PhotoSwipe regression tests

- [ ] Activate BBCode and Thumb.
- [ ] Insert an external image with explicit width and height.
- [ ] Open the entry.
- [ ] Expected: the configured external-image height is not overwritten.
- [ ] Reload the page.
- [ ] Expected: cached thumbnail behavior does not change the requested dimensions.
- [ ] Test a local JPEG, PNG, and GIF when supported by GD.
- [ ] Expected: thumbnails are generated or skipped safely according to available GD functions.
- [ ] Activate PhotoSwipe.
- [ ] Open the same explicitly sized external image.
- [ ] Expected: PhotoSwipe does not break the displayed dimensions.
- [ ] Create a single-image entry.
- [ ] Create a gallery entry.
- [ ] Expected: both open correctly and captions/alt text remain readable.
- [ ] Open RSS and Atom feeds containing the image entry.
- [ ] Expected: feed XML remains valid.

### 19. Chinese translation and internationalization

- [ ] Switch the blog language to English.
- [ ] Open the front end, admin area, setup-related pages available in the test instance, and changed plugin panels.
- [ ] Switch to German and repeat.
- [ ] Switch to Chinese (`zh-cn`) and repeat.
- [ ] Expected: Chinese can be selected and normal screens render without a blank page.
- [ ] Expected: Chinese text displays as UTF-8 without mojibake.
- [ ] Check navigation, configuration, entry editor, plugin management, widgets, comments, contact form, and maintenance.
- [ ] Check Tag, Mastodon, SEO Meta Tag Info, Newsletter, CookieBanner, PrettyURLs, and AudioVideo-related screens.
- [ ] Expected: labels are translated where provided; missing strings are identifiable and do not appear as PHP notices.
- [ ] Create and edit Chinese entry and static-page titles.
- [ ] Expected: saving, searching, categories, tags, PrettyURLs, and feeds remain usable.
- [ ] Check admin layout at desktop and narrow widths.
- [ ] Expected: longer or non-Latin labels do not overlap controls.
- [ ] Switch back to the original language.
- [ ] Expected: settings and content remain intact.

### 20. Accessibility, keyboard, and mobile checks

- [ ] Test the front end at a narrow/mobile viewport.
- [ ] Test the admin area at a narrow/mobile viewport.
- [ ] Navigate menus, forms, dialogs, and buttons with the keyboard.
- [ ] Check visible focus indicators.
- [ ] Check labels for comment, contact, newsletter, migration, SEO, and Mastodon forms.
- [ ] Check the Mastodon opt-in checkbox label.
- [ ] Check AudioVideo descriptions with a screen reader or accessibility inspector when available.
- [ ] Check image alt text in PhotoSwipe and the Mastodon profile widget.
- [ ] Zoom the browser to 200%.
- [ ] Expected: important controls remain reachable and text does not overlap.
- [ ] Test light and dark modes with keyboard navigation.
- [ ] Expected: focus and validation messages retain sufficient visual contrast.

### 21. Final smoke test and report

- [ ] Rebuild the FlatPress index.
- [ ] Purge the theme and template cache.
- [ ] Clear the FlatPress/APCu cache when available.
- [ ] Open the home page.
- [ ] Open one normal entry and one emoji-titled entry.
- [ ] Open one static page.
- [ ] Open one category and one tag page.
- [ ] Open one post feed and one comment feed.
- [ ] Open the admin dashboard.
- [ ] Open plugin management and widget management.
- [ ] Check the browser console.
- [ ] Check the PHP error log.
- [ ] Expected: no new warning, fatal error, broken layout, blank page, stale critical output, or redirect loop appears.

## Change-to-test traceability

| Change after 1.5.1 | Community-test section |
|---|---|
| Location Migration Mode | 2 |
| Smarty 5.8.4 and PHP 7.2–8.5 compatibility | 3 |
| ISO-8601 date output visible through SEO/Mastodon | 10, 12, 14 |
| Smarty block cache for feeds and Categories widget | 3 |
| Optional APCu fragment storage | 3 |
| Straight quotes and safe plugin descriptions | 4, 15 |
| Comment/contact XSS fix | 7 |
| 4-byte UTF-8/emoji PrettyURLs | 5 |
| Tag plugin and later Tag improvements | 8 |
| AudioVideo plugin | 9, 11 |
| Mastodon plugin and later synchronization work | 10–13 |
| SEO/Open Graph additions and fixes | 14 |
| BBCode HTML-button and autolink fixes | 7 |
| jQuery 4, jQuery UI 1.14.2, Archives, Comment Center, CookieBanner, PhotoSwipe | 16 |
| Newsletter validation, token, DNS, blocklist, and admin fixes | 15 |
| Thumb external-image height fix | 18 |
| LastComments routing/feed-link fixes | 17 |
| FootNotes `*Text*` fix | 7 |
| Stringendo dark mode, widget-zone, and Readmore changes | 6 |
| Chinese translation | 19 |

## Reporting

Report bugs with:

- FlatPress version or commit hash,
- fresh install, update, or migration test,
- PHP version,
- enabled PHP extensions relevant to the failure,
- web server, operating system, and hosting type,
- browser and browser version,
- active theme/style,
- active plugins and their versions,
- APCu state,
- PrettyURLs mode,
- Mastodon server/version when relevant,
- exact steps to reproduce,
- expected result,
- actual result,
- whether the issue reproduces after index rebuild and cache purge,
- screenshots when useful,
- relevant browser-console and PHP-log messages with secrets removed.

Never publish access tokens, password hashes, salts, private Mastodon content, subscriber addresses, user files, or complete instance configuration.

Report bugs in the [GitHub issue tracker](https://github.com/flatpressblog/flatpress/issues). If you are not familiar with GitHub, use the [FlatPress support forum](https://forum.flatpress.org/) instead.

**Thanks a lot for helping to test FlatPress 1.6.dev!**
