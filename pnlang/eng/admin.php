<?php
/**
 * crpVideo
 *
 * @copyright (c) 2007, Daniele Conca
 * @link http://noc.postnuke.com/projects/crpvideo Support and documentation
 * @author Daniele Conca <conca dot daniele at gmail dot com>
 * @license GNU/GPL - v.2
 * @package crpVideo
 */

define('_CRPVIDEO', 'Video');
define('_CRPVIDEO_VIDEO', 'Video');
define('_CRPVIDEO_VIDEOS', 'Videos');

define('_CRPVIDEO_DISPLAYWRAPPER', 'Display additional information with page');
define('_CRPVIDEO_GENERAL', 'General Settings');

// main list
define('_CRPVIDEO_CHANGE_STATUS','Change status');
define('_CRPVIDEO_CHANGE_STATUS_MODIFYING','Change status modifying the video');
define('_CRPVIDEO_NOT_SPECIFIED','Not specified');
define('_CRPVIDEO_PENDING','Pending');
define('_CRPVIDEO_REJECTED','Rejected');
define('_CRPVIDEO_STATUS','Status');

// creation form
define('_CRPVIDEO_AUTHOR','Video author');
define('_CRPVIDEO_CURRENT_FILE','Current file');
define('_CRPVIDEO_DELETE_FILE','Delete file');
define('_CRPVIDEO_FILE','Video file (.flv or .mp3) - Max');
define('_CRPVIDEO_FILEBLANK','(Must be the name for flv video in pnmedia/video)');
define('_CRPVIDEO_EXTERNAL','External url (http://)');
define('_CRPVIDEO_IMAGE','Video cover (.gif, .jpg, .png) - Max');
define('_CRPVIDEO_IMAGE_WIDTH','Max image width');
define('_CRPVIDEO_REQUIRED','(*)');
define('_CRPVIDEO_REQUIRED_EXT','Mandatory fields');
define('_CRPVIDEO_SHOW_FILE','Show file');
define('_CRPVIDEO_TAGS','Tags');

// config
define('_CRPVIDEO_BROWSER_PATH','Postnuke relative URL path (with trailing slash)');
define('_CRPVIDEO_COVER_DIMENSION','Max upload cover size (bytes)');
define('_CRPVIDEO_DISPLAY_EMBED','Display embed code');
define('_CRPVIDEO_DISPLAY_HEIGHT','Display height');
define('_CRPVIDEO_FILE_DIMENSION','Max upload file size (bytes)');
define('_CRPVIDEO_GD_AVAILABLE','GD Library');
define('_CRPVIDEO_IMAGES','Images');
define('_CRPVIDEO_IMAGE_RESIZE','Image would be resized to a width of');
define('_CRPVIDEO_MAIN_ITEMS','Main page elements');
define('_CRPVIDEO_MANDATORY_COVER','Mandatory cover');
define('_CRPVIDEO_NOTIFICATION_MAIL','Notification for user\'s creation (none if empty)');
define('_CRPVIDEO_PLAYER','Player');
define('_CRPVIDEO_PLAYER_HEIGHT','Player height');
define('_CRPVIDEO_PLAYER_WIDTH','Player width');
define('_CRPVIDEO_SHARE','Sharing');
define('_CRPVIDEO_UPLOAD','Upload');
define('_CRPVIDEO_UPLOAD_PATH','Upload path (root relative server filesystem path, no trail slash, must be 777 chmoded)');
define('_CRPVIDEO_USE_BROWSER','crpVideo use browser');
define('_CRPVIDEO_USE_GD','crpVideo use GD Library');
define('_CRPVIDEO_USERLIST_IMAGE','Show thumbnails in user list');
define('_CRPVIDEO_USERLIST_WIDTH','User list thumbnail witdh');

// error messages
define('_CRPVIDEO_ERROR_IMAGE_FILE_SIZE_TOO_BIG','Image dimension not allowed');
define('_CRPVIDEO_ERROR_IMAGE_NO_FILE','Image not uploaded or empty');
define('_CRPVIDEO_ERROR_VIDEO_FILE_SIZE_TOO_BIG','Video dimension not allowed');
define('_CRPVIDEO_ERROR_VIDEO_NO_AUTHOR','Author is required');
define('_CRPVIDEO_ERROR_VIDEO_NO_CATEGORY','Category is required');
define('_CRPVIDEO_ERROR_VIDEO_NO_CONTENT','Content is required');
define('_CRPVIDEO_ERROR_VIDEO_NO_FILE','Video not uploaded or empty');
define('_CRPVIDEO_ERROR_VIDEO_NO_TITLE','Title is required');
define('_CRPVIDEO_IMAGE_INVALID_TYPE','Image invalid type');
define('_CRPVIDEO_VIDEO_INVALID_TYPE','Video invalid type');
define('_CRPVIDEO_INVALID_URL','Invalid URL');
define('_CRPVIDEO_INVALID_NOTIFICATION','Invalid notification e-mail address');

// RSS define
define('_CRPVIDEO_ATOM','ATOM');
define('_CRPVIDEO_RSS','CRPVIDEO feed');
define('_CRPVIDEO_RSS1','RSS 1.0');
define('_CRPVIDEO_RSS2','RSS 2.0');
define('_CRPVIDEO_ENABLE_RSS','Enable RSS feed');
define('_CRPVIDEO_SHOW_RSS','Display link to RSS feed');
define('_CRPVIDEO_USE_RSS','Feed format');

// PodCast
define('_CRPVIDEO_PODCAST','crpVideo podcast');
define('_CRPVIDEO_ENABLE_PODCAST','Enable podcasting');
define('_CRPVIDEO_PODCAST_CATEGORY','Podcast category');
define('_CRPVIDEO_PODCAST_DESCRIPTION','Podcast description');
define('_CRPVIDEO_PODCAST_EDITOR','Managing editor');
define('_CRPVIDEO_PODCAST_ICATEGORY','Category description');

// Playlist
define('_CRPVIDEO_ENABLE_PLAYLIST','Enable playlist');
define('_CRPVIDEO_PLAYLIST_BY_CATEGORY','By category');
define('_CRPVIDEO_PLAYLIST_BY_DATE','By date');
define('_CRPVIDEO_PLAYLIST_BY_UPLOADER','By uploader');
define('_CRPVIDEO_PLAYLIST_BY_VIEWS','By views');
define('_CRPVIDEO_PLAYLIST_TYPE','Kind of playlist');
?>