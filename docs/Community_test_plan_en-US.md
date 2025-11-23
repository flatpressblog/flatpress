## Summary

- FlatPress 1.5 – Community Test Plan v1.0

## Preparation
1) **Preparation**
- [ ] Download the latest developer version or the latest release candidate of [FlatPress](https://github.com/flatpressblog/flatpress).
- [ ] Download the latest version of the [Bulk Content Generator](https://github.com/flatpressblog/flatpress-extras/tree/master/fp-tools/gen-bulk).
- [ ] Install FlatPress on your web server. If you already have a FlatPress instance, install the latest version as a separate instance (e.g., `/fp15-dev`).
- [ ] Copy the Bulk Content Generator to the blog root directory to generate test data.
- [ ] If necessary, recursively adjust the ownership rights and file and directory permissions.
- [ ] Open the browser of your choice and then run the setup (``setup.php``).
- [ ] Then run the Bulk Content Generator (``gen-bulk.php?n=3000&k=10&seed=1234&spread=1080``) in your browser and wait until the summary is displayed.
- [ ] Log in to the admin area, click on "Rebuild the FlatPress index" in the Maintenance menu, and wait until the summary is displayed.
- [ ] Activate all plugins that are part of the distribution.
- [ ] Place missing widgets in the right widget bar.

2) **Checking the preparation**
- [ ] Log in to the admin area, open the uploader menu, then click on the Storage submenu.
- [ ] Check in the storage menu whether there are 3000 entries with 10 comments each.
- [ ] Check in the Storage menu whether 10 static pages are available.
- [ ] Check the PHP log file to see if any errors or warnings occurred during preparation.

3) **Check that the time offset is correct and correct it if necessary.**
- [ ] Log in to the admin area, open the configuration menu -> "International settings" section.
- [ ] Check the local time and correct the time offset if necessary.
- [ ] Create a new post and publish it.
- [ ] Check in the frontend whether your newly created post has the correct time.

## Test areas
1) **Check the browser console and PHP log file during interaction**
- [ ] Open your browser's developer tools (Ctrl + Shift + I).
- [ ] Check the developer tools console to see if any warnings or errors are displayed.
- [ ] Open a static page in the frontend (e.g., ``static.php?page=about``) and check the console to see if any warnings or errors are displayed.
- [ ] In the frontend, click on all links, if possible without leaving any gaps, and check the accessibility as well as for warnings or errors in the console.
- [ ] Log in to the admin area and click on all menus and submenus as completely as possible, checking accessibility and looking for warnings or errors in the console.
- [ ] Check the PHP log file to see if any errors or warnings occurred during the interactions.

2) **Performance & Caching**
- [ ] Access blog posts: Home page, entries, comments, category, search – loading should be consistently fast, no 500 errors/timeouts.
- [ ] Activate APCu (if available) and test again; note any differences.
- [ ] Open image lists/galleries and check whether media lists respond quickly.

3) **Admin area**
- [ ] Adjust the inactivity timeout under "Configuration → FlatPress Protect → Idle timeout for admin session"; check automatic logout after timeout.
- [ ] Uploader/media management: Upload files/images, check thumbnails, insert into post.
- [ ] Themes/Widgets: Change style, change widget order, control in frontend.

4) **Plugins (random samples)**
- [ ] Newsletter: Registration (valid/invalid/disposable emails), double opt-in, bulk mailing with status display.
- [ ] BBCode Editor: Test new buttons (font); preview and save.
- [ ] PrettyURLs: Activate, check links in the frontend.
- [ ] Storage (formerly Stats): Check capacity displays and "Top Comments" only with the Postviews plugin enabled.
- [ ] Media Manager 2.0: Folder icons, usage detection (gallery vs. single image).
- [ ] Archives: Open and close, link check.
- [ ] Calendar: Link check; previous vs. next month and daily links.
- [ ] SEO Metatag info: Enter a description for a newly created post; check in the frontend whether an introduction is displayed.

5) **Internationalization**
- [ ] Switch language between German/English/Basque; check frontend/backend for missing/incorrect translations.

6) **Safety & Robustness**
- [ ] Incorrect form entries (contact/comment/newsletter) with special characters/emojis; expected validation.
- [ ] Check the browser console and PHP log for warnings/errors during all actions.

### Reporting
Please report bugs with clear step-by-step descriptions, expected vs. actual behavior, screenshots, and PHP log extracts in the [GitHub issue tracker](https://github.com/flatpressblog/flatpress/issues).
