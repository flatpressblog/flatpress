# FlatPress Mastodon Plugin User Guide

## What this plugin does

The plugin synchronizes FlatPress content with one Mastodon account in **both directions**:

- FlatPress entry -> Mastodon status
- FlatPress comment -> Mastodon reply
- FlatPress reply to a comment -> Mastodon reply to that reply
- Mastodon top-level status -> FlatPress entry
- Mastodon reply inside the imported thread -> FlatPress comment

The plugin also synchronizes **images** for FlatPress entries and Mastodon statuses.

When the FlatPress **Tag** plugin is active, the plugin also synchronizes **entry tags** in both directions:
- FlatPress entry tags -> Mastodon hashtags
- Mastodon hashtags -> FlatPress entry tags

## Important concept

Mastodon does not have a separate “comment” object in the way a blog does. In practice, comments are represented as **replies**.

The plugin works with the following mapping:

- **Entry -> Mastodon status without `in_reply_to_id`**
- **Comment on an entry -> Mastodon status with `in_reply_to_id` pointing to the entry status**
- **Comment on a comment -> Mastodon status with `in_reply_to_id` pointing to the parent reply**

## Requirements

### FlatPress side

You need:

- a working FlatPress ≥ 1.5.1 Stringendo installation
- PHP **7.2 to 8.5**
- the plugin files from this package

### PHP features used by the plugin

The code uses these PHP features and transports:

- **cURL** for HTTP requests when available
- **`allow_url_fopen`** as a fallback if cURL is not available
- **JSON**
- **DOMDocument / libxml** for best HTML-to-BBCode conversion
- **OpenSSL** to encrypt stored secrets when available

### Companion FlatPress plugins for the full feature set ###

For the complete FlatPress-side experience of synchronized Mastodon content, also activate:

- the FlatPress **BBCode** plugin
- the FlatPress **PhotoSwipe** plugin
- the FlatPress **Tag** plugin
- the FlatPress **Emoticons** plugin

Their responsibilities are:

- **BBCode** renders imported links, formatting, images and galleries instead of showing raw tags.
- **PhotoSwipe** provides the expected gallery and image presentation for synchronized media.
- **Tag** synchronizes FlatPress tags and Mastodon hashtags in both directions.
- **Emoticons** renders imported Mastodon emoji shortcodes as FlatPress emoticons.

### Recommended but not strictly required

- **OpenSSL enabled**  
  Without OpenSSL, secrets fall back to a plain base64 storage format.
- **DOM extension enabled**  
  Without DOM, Mastodon HTML import still has a fallback, but formatting quality can be lower.
- **A public FlatPress website**  
  If your FlatPress site only runs on `localhost`, Mastodon readers cannot open your blog links from the fediverse.

## Current limitations and behavior

### Visibility and privacy

- Exported FlatPress entries are posted to Mastodon with **public** visibility.
- Exported FlatPress comments are also posted as **public** replies.
- Imported Mastodon statuses with visibility **`private`** or **`direct`** are **not** imported into FlatPress.
- Imported Mastodon replies with visibility **`private`** or **`direct`** are also skipped.

### What is imported from Mastodon

The plugin fetches statuses from the **authenticated Mastodon account** and uses these rules:

- top-level statuses are imported as FlatPress entries
- replies are **not** fetched as top-level entries
- boosts / reblogs are **not** imported as entries
- for each imported top-level status, the plugin loads the thread context
- visible descendant replies in that thread are imported as FlatPress comments

This means you will see a Mastodon post as a **FlatPress entry** when it is a top-level post from the connected account.

You will see a Mastodon post as a **FlatPress comment** when it is a reply inside the thread of an imported top-level status.

### Scheduling

- The default daily synchronization time is **03:00**
- If the plugin has **never run before**, the first sync is due immediately
- The time can be changed in the plugin settings
- Scheduled sync runs only when a normal **web request** reaches FlatPress
- The automatic sync does **not** run during CLI execution
- The automatic sync is skipped for HTTP **POST** requests
- There is also a **Run synchronization now** button in the admin panel

In simple words: if nobody visits your FlatPress site after the scheduled time, the automatic sync will wait until the next normal page request.

### Decoupled deletion synchronization run
- Deletions are synchronized separately from content synchronization.
- Entries and comments prior to the set synchronization date remain unchanged on both sides.
- The admin option **Enable deletion synchronization** controls whether this follow-up delete pass runs at all. If the option is disabled, content synchronization still runs, but no follow-up deletions are executed and pending delete work is cleared.

### Tag support

- Tag synchronization works only when the FlatPress **Tag** plugin is active.
- FlatPress **entries** can export tags to Mastodon.
- FlatPress **comments** do not support tags and therefore do not export tags.
- Mastodon top-level statuses can import hashtags into FlatPress entries.
- Mastodon replies imported as FlatPress comments do not get tags.
- FlatPress tags are synchronized to Mastodon as **hashtags** with a leading `#`.
- Mastodon hashtags are synchronized to FlatPress **without** the leading `#`.
- If tags are removed on FlatPress or on Mastodon, the next synchronization updates the other side accordingly.

### Media support

- FlatPress **entries** can export images from `[img]` and `[gallery]`
- FlatPress **comments do not export images**
- Mastodon import currently handles **image** attachments
- Mastodon video and audio attachments are **not** imported by this plugin
- If a Mastodon instance allows only a limited number of media attachments, extra images are skipped and logged
- If media uploads succeed but the final Mastodon status creation or update fails in the same request, the plugin now performs a best-effort cleanup of those unattached Mastodon media uploads through `DELETE /api/v1/media/:id` before the request ends

### Authentication

The plugin stores these configuration values:

- Mastodon URL
- OAuth client ID
- OAuth client secret
- OAuth authorization code
- OAuth access token
- the **Enable deletion synchronization** toggle

However, the code uses the **OAuth access token** for API access.

That means:

- synchronization works only after an **OAuth access token** has been created and stored

## Files to install

Copy these items from the plugin package into your FlatPress installation:

- `fp-plugins/mastodon/`

After copying the plugin folder, log in to FlatPress and enable the plugin in the **Plugins** area.

## Where to find the plugin in FlatPress

After activation, open the admin area and go to:

- **Plugins**
- **Mastodon**

There you will find:

- connection settings
- OAuth helper
- manual synchronization
- last sync status
- counters for imported and exported entries and comments

## Step 1: Find your Mastodon URL

Your **Mastodon URL** is the base URL of the Mastodon server where your account exists.

Examples:

- `https://mastodon.social`
- `https://fosstodon.org`
- `https://example.instance`

Use only the server address, not a specific profile page.

Good examples:

- `https://mastodon.social`
- `https://mastodon.example`

Do **not** use something like:

- `https://mastodon.social/@yourname`
- `https://mastodon.social/web/statuses/1234567890`

## Step 2: Save the basic settings

In the plugin page, fill in:

- **Mastodon URL**
- **Daily sync time**

Then click:

- **Save settings**

## Step 3: Register the Mastodon app

In the **OAuth helper** section, click:

- **Register Mastodon app**

What the plugin does internally:

- it creates an OAuth application on your Mastodon instance
- it uses the app name **FlatPress Mastodon**
- it requests these scopes:

  - `read:accounts`
  - `read:statuses`
  - `write:statuses`
  - `write:media`

After successful registration, the plugin stores:

- client ID
- client secret
- authorization URL

## Step 4: Open the Authorization URL

After the app is registered, the plugin shows an **Authorization URL**.

Open that URL in your browser.

What you should do there:

1. log in to Mastodon if your server asks you to log in
2. review the app permissions
3. approve the app

The plugin registers the special out-of-band redirect URI:

- `urn:ietf:wg:oauth:2.0:oob`

Because of that, Mastodon does **not** send you back to FlatPress. Instead, Mastodon shows you an **authorization code** on screen.

## Step 5: Copy the authorization code

Copy the code shown by Mastodon after approval.

Then return to FlatPress and paste it into:

- **Authorization code**

## Step 6: Create the OAuth access token

Click:

- **Exchange code for token**

If the exchange is successful:

- the plugin stores the OAuth access token
- the authorization code field is cleared
- the token state in the admin page changes to **Access token available**

At this point, synchronization can start.

## Step 7: Run the first synchronization

Click:

- **Run synchronization now**

Also check:

- **Last sync**
- **Last error**
- sync counters
- `fp-content/mastodon/sync.log` for detailed technical messages

## How import and export work

## FlatPress -> Mastodon

### Entry export

A FlatPress entry is exported as:

- one Mastodon status
- **without** `in_reply_to_id`

The status text is built from:

- entry subject
- entry content converted from FlatPress formatting to Mastodon-friendly plain text
- a public entry link, if the FlatPress URL is public and usable

If the FlatPress site is only local, such as `localhost`, the plugin suppresses non-public links in Mastodon.

### Comment export

A FlatPress comment is exported as:

- one Mastodon status
- **with** `in_reply_to_id` pointing to the Mastodon status of the FlatPress entry

The text is based on the comment content.

If the comment author is not the default internal author names, the plugin prefixes the text with:

- `Comment by <name>:`

### Reply-to-comment export

If a FlatPress comment contains a valid parent comment reference, it is exported as:

- one Mastodon status
- **with** `in_reply_to_id` pointing to the Mastodon status of the parent comment

This keeps reply chains threaded on Mastodon.

### Tag export

When the FlatPress **Tag** plugin is active, the plugin reads the local entry tags and appends them to the exported Mastodon entry:

- on a **new line**
- at the **end** of the Mastodon entry
- as Mastodon **hashtags** with a leading `#`
- If no tags exist, no hashtag line is added.
- If tags were removed from the FlatPress entry, the next update of the already synchronized Mastodon status removes that hashtag line again.

### Image export

For FlatPress entries, the plugin detects:

- `[img=...]`
- `[gallery=...]`

The plugin uploads the referenced local image files to Mastodon and attaches them as media.

The raw BBCode tags themselves are removed from the exported Mastodon text.

FlatPress comments do not export images.

## Mastodon -> FlatPress

### Entry import

A Mastodon top-level status from the connected account is imported as a FlatPress entry when all of these are true:

- it belongs to the authenticated account
- it is fetched as a top-level status
- it is not a reblog / boost
- it is not excluded by visibility rules

The imported FlatPress entry contains:

- a generated subject line
- content converted from Mastodon HTML to FlatPress-style BBCode
- the Mastodon author name
- the Mastodon timestamp
- a footer link back to Mastodon
- synchronized tags from the Mastodon status, when the FlatPress **Tag** plugin is active

### Comment import

Replies from the imported Mastodon thread are imported as FlatPress comments.

The plugin reads the thread context of the imported top-level status and imports descendant replies.

If a reply is itself a reply to another reply, the plugin stores the local parent relation with `replyto`, so the reply structure is preserved in data.

### Tag import ===

When the FlatPress **Tag** plugin is active, the plugin imports Mastodon hashtags into the FlatPress entry:

- Mastodon hashtags are stored as FlatPress entry tags
- the leading `#` is removed
- only entry imports use tags; imported comments do not
- If tags were removed from the Mastodon status, the next allowed update of the synchronized FlatPress entry removes the corresponding local tags again.

### Image import

If a Mastodon status contains image attachments:

- one imported image becomes a FlatPress `[img=...]`
- multiple imported images become a FlatPress `[gallery=...]`

The files are stored under:

- `fp-content/images/mastodon/status-<mastodon-status-id>/`

If image descriptions exist on Mastodon, the plugin saves them as gallery captions where possible.

## When you will see a Mastodon post as a FlatPress entry or comment

### You will see it as a FlatPress entry when:

- it is a top-level status of the connected Mastodon account
- it is fetched from the account status timeline
- it is visible enough to import
- it passes the plugin filters

### You will see it as a FlatPress comment when:

- it is a descendant reply inside the thread of an imported top-level status
- it is visible enough to import
- it is not blocked because its parent reply was skipped

### You will not see it imported when:

- it has visibility `direct`
- it has visibility `private`
- it is only a boost / reblog
- it is outside the imported thread logic
- its parent reply is not importable and the reply chain cannot be preserved correctly

## Where the plugin stores its working data

The plugin uses these runtime files and folders:

- `fp-content/mastodon/state.json`  
  Stores mappings, sync state, and counters

- `fp-content/mastodon/sync.lock`  
  Prevents parallel sync runs

- `fp-content/mastodon/sync.log`  
  Technical log for troubleshooting

- `fp-content/mastodon/tmp/`  
  Temporary files during media import

Imported Mastodon images are stored in:

- `fp-content/images/mastodon/`

## Troubleshooting

### “No access token saved”

You have not completed the OAuth flow yet.

Solution:

1. save your instance URL
2. click **Register Mastodon app**
3. open the **Authorization URL**
4. approve the app on Mastodon
5. paste the shown code into FlatPress
6. click **Exchange code for token**

### “The synchronization finished successfully” but nothing new appears

Check these points:

- Is the post already mapped in `state.json`?
- Is the sync simply **not due** yet?
- Is the post private or direct on Mastodon?
- Is the FlatPress entry still a draft?
- Did Mastodon reject the post because of text length, media limits, or authorization?

Then check:

- `fp-content/mastodon/sync.log`
- the counters in the plugin admin page

### Tags are missing or not updated

Check these points:

- Is the FlatPress **Tag** plugin enabled?
- Are you synchronizing an **entry** and not a comment?
- Were the tags removed on one side and has a new sync already been run?
- Is the option to update existing local content from Mastodon enabled when you expect remote tag deletions or remote tag changes to update an already imported FlatPress entry?

### Images are missing on Mastodon

Check these points:

- Are the images inside a FlatPress entry and not only in comments?
- Are they referenced with `[img]` or `[gallery]`?
- Does the stored token include permission to upload media?
- Does your Mastodon server allow the image count and file type?

### Imported Mastodon images are missing in FlatPress

Check these points:

- Are the attachments really images and not video/audio?
- Can FlatPress write to `fp-content/images/mastodon/`?
- Can your server download the image files from the Mastodon media host?

## Security notes

- The plugin stores secrets in FlatPress configuration.
- If OpenSSL is available, the plugin encrypts stored secrets.
- If OpenSSL is not available, the plugin falls back to a weaker plain base64 storage format.
- Protect your FlatPress admin area and your server backups carefully.

## Optional regression test

The package also includes:

- `simulate_mastodon_plugin.php`

This script is intended for testing and regression checks. It is optional for normal daily use.

Typical CLI usage:

```bash
php simulate_mastodon_plugin.php
```

Optional read-only credential smoke test:

```bash
php simulate_mastodon_plugin.php --live-auth
```

## Practical recommendation

The safest order is:

1. install and activate the plugin
2. save Mastodon URL and sync time
3. register the Mastodon app
4. authorize the app on Mastodon
5. exchange the authorization code for a token
6. run a manual sync
7. check `last sync`, `last error`, and `sync.log`
8. only then rely on the automatic daily sync

## FAQ
  **Q:** Why aren't the texts of posts and comments formatted the same way in Mastodon as they are in FlatPress?
  **A:** The plugin intentionally uses the official REST API ''POST /api/v1/statuses'' or ''PUT /api/v1/statuses/:id''. There, Mastodon explicitly documents the status parameter as the //text content of the status//. For editable sources, you should use the plain-text representation, and the server generates the corresponding HTML from it.

  **Q:** Why doesn't the plugin support video and audio content?
  **A:** The plugin's synchronization relies on standard web requests. Every communication with Mastodon, every media upload, every import of a reply thread, and every additional file access takes time within the same PHP request, which is also responsible for rendering a page at the same time.

## Resources for Developers:
- [Functional Organization Chart](Function-Organigram.md)
