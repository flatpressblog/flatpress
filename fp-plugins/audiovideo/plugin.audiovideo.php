<?php
/**
 * Plugin Name: Audio and video player
 * Version: 1.1.0
 * Plugin URI: https://flatpress.org
 * Description: A simple audio and video player.<br>Usage:<ul><li>Audio: <code>[audioplayer="attachs/file.mp3"]</code></li><li>Video: <code>[videoplayer="attachs/file.mp4"]</code></li></ul><a href="./fp-plugins/audiovideo/doc_audiovideo.txt" title="Instructions" target="_blank">[Instructions]</a>
 * Author: FlatPress
 * Author URI: https://flatpress.org
 */

/**
 * --------
 * About
 * --------
 *
 * This plugin provides simple players for audio an video files. It relies on HTML5 standard
 * elements and needs no Flash or other external browser plugins.
 *
 *
 * --------
 * Usage
 * --------
 *
 * Simple audio player:
 * [audioplayer="attachs/file.mp3"]
 * Audio player with additional parameters (each optional):
 * [audioplayer="attachs/file.mp3" controls="0" autoplay="1" loop="1"]
 * Audio player with optional description:
 * [audioplayer="attachs/file.mp3" controls="1"]A short audio description[/audioplayer]
 *
 * Simple video player:
 * [videoplayer="attachs/file.mp4"]
 * Video player with additional parameters (each optional):
 * [videoplayer="attachs/file.mp4" controls="0" autoplay="1" loop="1" width="640" height="480" poster="images/posterimage.jpg"]
 * Video player with optional description:
 * [videoplayer="attachs/file.mp4" controls="1" poster="images/posterimage.jpg"]A short video or poster description[/videoplayer]
 *
 *
 * --------
 * Parameters
 * --------
 *
 * Each of the following parameters is optional. If not set, the default value will be used.
 * For boolean parameters, the following values count as "true": true, 1, yes, ja, si.
 *
 * Both audio and video
 * controls: Show control elements of the player (default is yes)
 * autoplay: Start playing immediately (default is no)
 * loop: Play in endless loop (default is no)
 *
 * Video only
 * width: The width of the video's display area (default is the width of the video itself)
 * height: The height of the video's display area (default is the height of the video itself)
 * poster: The poster frame image to show until the user plays or seeks (default is the video's first frame). Must be an uploaded file.
 *
 * Optional description
 * Text between an opening and closing audioplayer/videoplayer tag is used as the media description.
 * Videos with a poster image use the same description for the video element while the poster is displayed.
 *
 *
 * --------
 * HTML+CSS
 * --------
 *
 * The player will be displayed as <audio> / <video> element with the CSS class "audiovideo".
 * See the documentation of these standard HTML5 elements for further details.
 * The given parameters (see above) will be set to the player element accordingly.
 */

// include the plugin's PHP files
include_once dirname(__FILE__) . '/audiovideoplugin.class.php';

// intialize the BBCode tags of the plugin
add_filter('init', 'AudioVideoPlugin::initializePluginTags');
?>
