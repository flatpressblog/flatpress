# FlatPress Mastodon Plugin User Guide

## What this plugin does

The Mastodon plugin connects one FlatPress installation with one Mastodon account.

It can synchronize content in both directions:

- FlatPress entry → Mastodon status
- FlatPress comment → Mastodon reply
- FlatPress reply to a comment → Mastodon reply to that reply
- Mastodon top-level status → FlatPress entry
- Mastodon reply inside a known imported thread → FlatPress comment

The plugin can also synchronize:

- images from FlatPress entries to Mastodon
- image attachments from Mastodon to FlatPress
- audio and video from FlatPress entries to Mastodon when the FlatPress **AudioVideo** plugin is active
- Mastodon audio and video attachments to FlatPress AudioVideo player tags
- FlatPress entry tags and Mastodon hashtags when the FlatPress **Tag** plugin is active

## Important concept

Mastodon does not have a separate “comment” object like a blog.

In Mastodon, comments are simply **replies** inside a thread.

The plugin therefore uses this mapping:

- **Entry** → Mastodon status without `in_reply_to_id`
- **Comment on an entry** → Mastodon reply to the entry status
- **Comment on a comment** → Mastodon reply to the parent reply

That means a synchronized discussion can continue on both sides as one thread.

## Requirements

You need:

- FlatPress 1.5.1 Stringendo or newer
- PHP **7.2 to 8.5**
- the plugin folder `fp-plugins/mastodon/`

The plugin uses:

- **cURL** when available
- **`allow_url_fopen`** as a fallback if cURL is not available
- **JSON**
- **DOMDocument / libxml** for the best HTML-to-BBCode conversion
- **OpenSSL** to protect stored secrets when available

## Recommended FlatPress plugins

For the best result with synchronized Mastodon content, also enable these FlatPress plugins:

- **BBCode**
- **PhotoSwipe**
- **AudioVideo**
- **Tag**
- **Emoticons**

They improve the result like this:

- **BBCode** renders imported formatting, links, images and galleries properly.
- **PhotoSwipe** improves image and gallery display.
- **AudioVideo** renders imported and synchronized audio/video attachments as HTML5 media players.
- **Tag** enables tag / hashtag synchronization.
- **Emoticons** renders imported emoji shortcodes more nicely.

## What the plugin currently does and does not do

### Visibility

- Exported FlatPress entries are posted to Mastodon as **public** statuses.
- Exported FlatPress comments are posted to Mastodon as **public** replies.
- Mastodon statuses or replies with visibility **private** or **direct** are not imported into FlatPress.

### Text and formatting

- FlatPress content is converted to Mastodon-friendly plain text during export.
- Mastodon HTML is converted to FlatPress-friendly BBCode during import.
- If you enable **Update existing local content from Mastodon**, already imported local entries and comments may be refreshed from Mastodon. In that mode the plugin keeps local texts within a practical size limit.

### Images and media

- FlatPress **entries** can export local images referenced by `[img]` and `[gallery]`.
- FlatPress **entries** can export local audio referenced by `[audioplayer]` and local video referenced by `[videoplayer]` when the AudioVideo plugin is active.
- FlatPress **comments do not export images, audio or video**.
- Mastodon image attachments can be imported into FlatPress.
- Mastodon audio attachments can be imported into FlatPress as `[audioplayer]` tags.
- Mastodon video and GIFV attachments can be imported into FlatPress as `[videoplayer]` tags; available preview images are stored as video posters.
- If the Mastodon server has media limits, extra media attachments are skipped instead of breaking the whole synchronization.
- When the plugin already knows a media description, it sends that description during the initial Mastodon media upload.
- If only the media description changes later, the plugin can update the description on supported Mastodon servers without re-uploading the file.
- Audio, video and GIFV uploads may be processed asynchronously by Mastodon. The plugin waits for the uploaded media to become ready and uses longer bounded polling windows for audio/video than for images.
- Remote audio/video imports try alternate direct media URLs when the first download URL is temporarily unavailable.

### Tags

- Tag synchronization works only when the FlatPress **Tag** plugin is active.
- FlatPress entry tags are exported as Mastodon hashtags.
- Mastodon hashtags are imported as FlatPress entry tags.
- Comments do not carry tags.

## How scheduling works

- The default daily synchronization time is **03:00**.
- You can change the time in the plugin settings.
- Automatic synchronization runs only on a normal website request after the due time.
- It does not run during CLI execution.
- It is skipped for HTTP **POST** requests.
- If nobody visits the site after the scheduled time, the automatic synchronization waits until the next normal page request.

There is also a **Run synchronization now** button in the plugin settings for a manual run.

### Deletion synchronization runs later

The plugin separates normal synchronization from deletion synchronization.

That means:

- content synchronization runs first
- deletion synchronization is marked as pending
- the delete check runs in a **later follow-up request**

This keeps the main synchronization request shorter and more stable.

The option **Enable deletion synchronization** controls whether that follow-up delete pass is used at all.

## Admin page overview

In **Plugins → Mastodon** you will see these areas:

### 1. Basic settings

Here you set:

- **Username**
- **Mastodon URL**
- **Daily sync time**
- **Synchronization start date**

The **Synchronization start date** is useful when you do not want to import or export much older content.

### 2. More options

These switches are especially important:

- **Update existing local content from Mastodon**  
  Allows already imported FlatPress entries and comments to be refreshed from Mastodon later.

- **Import comments as entries as well**  
  Allows certain synchronized Mastodon replies to also appear as FlatPress entries. Leave this disabled if you want to avoid duplicate content and keep thread replies mainly in the comment area.

- **Enable deletion synchronization**  
  Enables the separate follow-up delete pass.

### 3. OAuth helper

This section is used only to connect FlatPress to your Mastodon account.

The usual order is:

1. save the basic settings
2. click **Register Mastodon app**
3. open the shown authorization URL
4. allow the app on Mastodon
5. copy the shown code back into FlatPress
6. click **Exchange code for token**

Without a valid access token, synchronization cannot work.

### 4. Mastodon instance information

This section lets you manually fetch information about your Mastodon server, for example:

- exact Mastodon version
- API compatibility version
- server limits relevant to synchronization

This is mainly a diagnostic aid. It is not required before every synchronization.

### 5. Status and counters

The lower part of the page shows:

- the last synchronization time
- the last deletion synchronization time
- the last error
- counters for imported, updated, exported and deleted entries and comments

The deletion counters belong to the separate follow-up delete pass and can therefore change later than the normal synchronization counters.

## Installation and first setup

### Step 1: Install the plugin

Copy this folder into your FlatPress installation:

- `fp-plugins/mastodon/`

Then activate the plugin in the FlatPress admin area.

### Step 2: Enter the Mastodon server URL

Use only the base URL of your Mastodon server, for example:

- `https://mastodon.social`
- `https://fosstodon.org`
- `https://example.social`

Do **not** enter a profile or status URL such as:

- `https://mastodon.social/@yourname`
- `https://mastodon.social/web/statuses/123456789`

### Step 3: Save the basic settings

Save at least:

- **Username**
- **Mastodon URL**
- **Daily sync time**

You can also set a **Synchronization start date** now.

### Step 4: Register the Mastodon app

Click **Register Mastodon app**.

The plugin then stores the technical app information it needs for OAuth.

### Step 5: Authorize the app

Open the shown authorization URL in your browser, approve the app and copy the displayed authorization code.

### Step 6: Exchange the code for a token

Paste the code into the plugin page and click **Exchange code for token**.

After that, FlatPress has the access token it needs for synchronization.

### Step 7: Optional: fetch Mastodon instance information

You can click **Fetch Mastodon instance information** to store a snapshot of your server information in the admin area.

This is helpful when you want to check:

- the exact Mastodon version
- media and text limits
- whether the server reports useful compatibility information

### Step 8: Run the first synchronization

Click **Run synchronization now**.

After the run, also check:

- **Last sync**
- **Last error**
- the counters in the admin area

For technical details you can additionally inspect:

- `fp-content/plugin_mastodon/state.json`
- `fp-content/plugin_mastodon/sync.log`

## How synchronization works in practice

## FlatPress → Mastodon

### Entry export

A FlatPress entry becomes one Mastodon top-level status.

The exported text is built from:

- the entry subject
- the entry content converted into Mastodon-friendly text
- a public entry link when the blog URL is publicly usable

Local media in the entry can be attached to the Mastodon status:

- `[img]` and `[gallery]` become Mastodon image uploads
- `[audioplayer]` becomes a Mastodon audio upload
- `[videoplayer]` becomes a Mastodon video upload
- a local `poster` attribute on `[videoplayer]` is sent as the Mastodon media thumbnail when usable

Mastodon instances can take longer to process audio and video than images. The plugin therefore polls the Mastodon media endpoint until the uploaded attachment reports its final media URL or a bounded timeout is reached.

### Comment export

A FlatPress comment becomes one Mastodon reply to the synchronized entry.

If the comment is itself a reply to another FlatPress comment, it becomes a Mastodon reply to that parent reply.

This keeps reply chains intact on Mastodon.

### What happens on imported Mastodon threads

If a Mastodon top-level status was already imported as a FlatPress entry, and you then write a new FlatPress comment under that entry, the plugin exports that new FlatPress comment as a Mastodon reply in the existing Mastodon thread.

The same applies to replies to local FlatPress comments inside that thread.

## Mastodon → FlatPress

### Top-level status import

A Mastodon top-level status from the connected account can become a FlatPress entry.

Media attachments are imported with FlatPress-native markup:

- one image becomes an `[img]` tag
- multiple images become a `[gallery]` tag
- audio becomes an `[audioplayer]` tag
- video and GIFV become a `[videoplayer]` tag

If a video preview image is available, it is stored and referenced as the video poster.

### Reply import inside known threads

Replies that belong to a known imported Mastodon thread are imported as FlatPress comments under the matching FlatPress entry.

If a reply belongs under a previously synchronized FlatPress comment, it is imported as a reply to that local comment.

This also applies when another Mastodon member replies inside that known thread.

### About “Import comments as entries as well”

When this option is disabled, thread replies should stay in the comment area instead of also appearing as separate FlatPress entries.

When the option is enabled, the plugin may additionally import certain synchronized replies as entries as well.

If you mainly want a blog-like discussion structure, leaving the option disabled is usually the safer choice.

## Where plugin data is stored

The plugin stores its working data in:

- `fp-content/plugin_mastodon/state.json`
- `fp-content/plugin_mastodon/sync.log`

Imported Mastodon images are stored under:

- `fp-content/images/mastodon/`

Imported Mastodon audio and video files are stored under:

- `fp-content/attachs/mastodon/`

Imported video poster images are stored under:

- `fp-content/images/mastodon/`

OAuth and plugin settings are stored in the FlatPress configuration.

## Troubleshooting

### “No access token saved”

The OAuth setup is not finished yet.

Do this:

1. save the Mastodon URL
2. click **Register Mastodon app**
3. open the authorization URL
4. approve the app on Mastodon
5. paste the shown code back into FlatPress
6. click **Exchange code for token**

### “The synchronization finished successfully” but nothing new appears

Check these points:

- Is the content older than the configured **Synchronization start date**?
- Is the Mastodon post private or direct?
- Is the local FlatPress entry still a draft?
- Is the content already synchronized and therefore only counted as an update or not changed at all?
- Is the automatic run simply not due yet?

Also check:

- the counters in the plugin page
- `fp-content/plugin_mastodon/sync.log`

### Comments are not where I expected them

Remember these rules:

- top-level Mastodon posts become FlatPress entries
- replies inside a known imported thread become FlatPress comments
- if **Import comments as entries as well** is disabled, replies should normally stay in the comment area only

### Media is missing on Mastodon

Check these points:

- Is the media in a FlatPress entry and not only in comments?
- Are images referenced with `[img]` or `[gallery]`?
- Are audio files referenced with `[audioplayer]`?
- Are video files referenced with `[videoplayer]`?
- Are the local files stored below `fp-content/attachs/` or `fp-content/images/`?
- Does the Mastodon server allow the number, MIME type and size of the uploaded media?
- For large audio or video files, check `fp-content/plugin_mastodon/sync.log` for media processing messages.

### Imported Mastodon media is missing in FlatPress

Check these points:

- Are the attachments public and downloadable by the web server?
- Can FlatPress write to `fp-content/images/mastodon/`?
- Can FlatPress write to `fp-content/attachs/mastodon/`?
- Can the web server download files from the Mastodon media host?
- Is the FlatPress **AudioVideo** plugin active when you want imported audio/video to render as players?

### Audio or video upload reports `media_processing_timeout`

Mastodon may accept an audio or video upload first and then process it asynchronously.

The plugin waits longer for audio/video than for images, but processing can still fail when:

- the file is larger than the Mastodon instance allows
- the Mastodon server rejects the MIME type
- the server-side transcoder is overloaded
- the web host stops long-running PHP requests too early

Check:

- the Mastodon instance media limits in the plugin admin page
- `fp-content/plugin_mastodon/sync.log`
- the real file type and file size of the referenced media

## Security notes

- The plugin stores connection secrets in FlatPress configuration.
- When OpenSSL is available, those secrets are protected more strongly.
- Protect your FlatPress admin access and your backups carefully.

## FAQ

  **Q:** Why do Mastodon texts not look exactly like the original FlatPress formatting?  
  **A:** The plugin converts FlatPress formatting into Mastodon-friendly text. Mastodon itself stores and renders statuses differently from a blog system, so the result is intentionally adapted instead of copied 1:1.

  **Q:** Can videos and audio be synchronized?  
  **A:** Yes. FlatPress entries can export local `[audioplayer]` and `[videoplayer]` media to Mastodon, and Mastodon audio/video attachments can be imported into FlatPress when the AudioVideo plugin is available. FlatPress comments do not synchronize media attachments.

  **Q:** Why can the deletion counters change later than the synchronization counters?  
  **A:** Because the deletion pass runs in a later follow-up request, not in the main synchronization request.

## Resources for Developers:
- [Functional Organization Chart](Function-Organigram.md)

## For advanced testing

The package also contains:

- `simulate_mastodon_plugin.php`

This script is meant for regression testing. Normal users do not need it for everyday use.
