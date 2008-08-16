<?php
/**
 * crpVideo
 *
 * @copyright (c) 2007, Daniele Conca
 * @link http://noc.postnuke.com/projects/crpvideo Support and documentation
 * @author Daniele Conca <jami at cremonapalloza dot org>
 * @license GNU/GPL - v.2.1
 * @package crpVideo
 */

Loader::includeOnce('modules/crpVideo/pnclass/crpVideo.php');

/**
 * Retrieve list of events, filtered if specified
 */
function crpVideo_adminapi_getall($navigationValues)
{
	// Security check
	if (!SecurityUtil::checkPermission('crpVideo::', '::', ACCESS_EDIT))
	{
		return LogUtil::registerPermissionError();
	}

	$video = new crpVideo();

	return $video->dao->adminList($navigationValues['startnum'], $navigationValues['category'],
																		$navigationValues['clear'], $navigationValues['ignoreml'],
																		$navigationValues['modvars'], $navigationValues['mainCat'],
																		$navigationValues['active'], $navigationValues['interval'],
																		$navigationValues['sortOrder']);
}

/**
 * create a new video
 * @param $args['title'] name of the item
 * @param $args['content'] content of the item
 * @param $args['language'] language of the item
 * @return mixed video ID on success, false on failure
 */
function crpVideo_adminapi_create($args)
{
	// defaults
	if (!isset ($args['language']))
	{
		$args['language']= '';
	}
	if (!isset ($args['displaywrapper']))
	{
		$args['displaywrapper']= false;
	}

	// define the permalink title if not present
	if (!isset ($args['urltitle']) || empty ($args['urltitle']))
	{
		$args['urltitle']= DataUtil :: formatPermalink($args['title']);
	}

	// Security check
	if (!SecurityUtil :: checkPermission('crpVideo::Video', "$args[title]::", ACCESS_EDIT))
	{
		return LogUtil :: registerError(_MODULENOAUTH);
	}

	if (!DBUtil :: insertObject($args, 'crpvideos', 'videoid'))
	{
		return LogUtil :: registerError(_CREATEFAILED);
	}

	// Let any hooks know that we have created a new item.
	pnModCallHooks('item', 'create', $args['videoid'], array (
		'module' => 'crpVideo'
	));

	// Return the id of the newly created item to the calling process
	return $args['videoid'];
}

/**
 * delete a video
 * @param $args['videoid'] ID of the video
 * @return bool true on success, false on failure
 */
function crpVideo_adminapi_delete($args)
{
	// Argument check
	if (!isset ($args['videoid']))
	{
		return LogUtil :: registerError(_MODARGSERROR);
	}

	// Check item exists before attempting deletion
	$videoObj = new crpVideo();
	$oldData = $videoObj->dao->getData($args);

	if ($oldData == false)
	{
		return LogUtil :: registerError(_NOSUCHITEM);
	}

	// Security check
	if (!SecurityUtil :: checkPermission('crpVideo::Video', "$item[title]::$videoid", ACCESS_DELETE))
	{
		return LogUtil :: registerError(_MODULENOAUTH);
	}

	if (!DBUtil :: deleteObjectByID('crpvideos', $args['videoid'], 'videoid'))
	{
		return LogUtil :: registerError(_DELETEFAILED);
	}

	// remove cover
	$item = $videoObj->dao->getFile($args['videoid'], 'image');
	if ($item)
	{
		if (!DBUtil::deleteObjectByID('crpvideo_covers', $item['id'], 'id'))
      	return LogUtil::registerError (_DELETEFAILED);
	}
  // remove file from filesystem
	unlink($oldData['pathvideo']);

	// Let any hooks know that we have deleted an item.
	pnModCallHooks('item', 'delete', $args['videoid'], array (
		'module' => 'crpVideo'
	));

	return true;
}

/**
 * update a video
 * @param $args['videoid'] the ID of the video
 * @param $args['title'] the new name of the item
 * @param $args['content'] the new content of the item
 */
function crpVideo_adminapi_update($args)
{
	if (!isset ($args['displaywrapper']))
	{
		$args['displaywrapper']= false;
	}

	// define the permalink title if not present
	if (!isset ($args['urltitle']) || empty ($args['urltitle']))
	{
		$args['urltitle']= DataUtil :: formatPermalink($args['title']);
	}

	// Check page to update exists, and get information for
	// security check
	$item= pnModAPIFunc('crpVideo', 'user', 'get', array (
		'videoid' => $args['videoid']
	));

	if ($item == false)
	{
		return LogUtil :: registerError(_NOSUCHITEM);
	}

	// Security check
	if (!SecurityUtil :: checkPermission('crpVideo::', "$item[title]::$item[videoid]", ACCESS_EDIT))
	{
		return LogUtil :: registerError(_MODULENOAUTH);
	}

	// set some defaults
	if (!isset ($args['language']))
	{
		$args['language']= '';
	}

	if (!DBUtil :: updateObject($args, 'crpvideos', '', 'videoid'))
	{
		return LogUtil :: registerError(_UPDATEFAILED);
	}

	// Let any other modules know we have updated an item
	pnModCallHooks('item', 'update', $args['videoid'], array (
		'module' => 'crpVideo'
	));

	// The item has been modified, so we clear all cached pages of this item.
	$pnRender= pnRender :: getInstance('crpVideo');
	$pnRender->clear_cache(null, $args['videoid']);

	return true;
}

/**
 * get available admin panel links
 *
 * @return array array of admin links
 */
function crpVideo_adminapi_getlinks()
{
	$links= array ();

	pnModLangLoad('crpVideo', 'admin');

	$itemname= _CRPVIDEO_VIDEO;
	$itemsname= _CRPVIDEO_VIDEOS;

	if (SecurityUtil :: checkPermission('crpVideo::', '::', ACCESS_READ))
	{
		$links[]= array (
			'url' => pnModURL('crpVideo',
			'admin',
			'view'
		), 'text' => pnML('_VIEWITEMS', array (
			'i' => $itemsname
		)));
	}
	if (SecurityUtil :: checkPermission('crpVideo::', '::', ACCESS_ADD))
	{
		$links[]= array (
			'url' => pnModURL('crpVideo',
			'admin',
			'new'
		), 'text' => pnML('_CREATEITEM', array (
			'i' => $itemname
		)));
	}
	if (SecurityUtil :: checkPermission('crpVideo::', '::', ACCESS_ADMIN))
	{
		$links[]= array (
			'url' => pnModURL('crpVideo',
			'admin',
			'modifyconfig'
		), 'text' => _MODIFYCONFIG);
	}

	return $links;
}

/**
 * modify item status
 *
 * @return string HTML output
 */
function crpVideo_adminapi_change_status($args=array())
{
	// Security check
	if (!SecurityUtil::checkPermission('crpVideo::', '::', ACCESS_ADD))
	{
		return LogUtil::registerPermissionError();
	}

	$video = new crpVideo();

	if ($args['status']=='P' || $args['status']=='A')
	{
		($args['status']=='A')?$args['status']='P':$args['status']='A';
		$video->dao->updateStatus($args['videoid'], $args['status']);
	}

	return;
}
?>