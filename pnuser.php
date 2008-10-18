<?php

/**
 * crpVideo
 *
 * @copyright (c) 2007-2008, Daniele Conca
 * @link http://code.zikula.org/projects/crpvideo Support and documentation
 * @author Daniele Conca <conca.daniele@gmail.com>
 * @license GNU/GPL - v.2.1
 * @package crpVideo
 */

Loader :: includeOnce('modules/crpVideo/pnclass/crpVideo.php');

function crpVideo_user_main()
{
	// Security check
	if (!SecurityUtil :: checkPermission('crpVideo::', '::', ACCESS_READ))
	{
		return LogUtil :: registerPermissionError();
	}

	// get all module vars
	$modvars = pnModGetVar('crpVideo');

	// Create output object
	$pnRender = pnRender :: getInstance('crpVideo');

	// load the categories system
	if (pnModGetVar('crpVideo', 'enablecategorization'))
	{
		Loader :: loadClass('CategoryUtil');
		Loader :: loadClass('CategoryRegistryUtil');
		$mainCat = CategoryRegistryUtil :: getRegisteredModuleCategory('crpVideo', 'crpvideo', 'Main', '/__SYSTEM__/Modules/crpVideo');
		$rootCat = CategoryUtil :: getCategoryByID($mainCat);
		$cats = CategoryUtil :: getCategoriesByParentID($mainCat);
		$parents = array_diff_assoc(array_reverse(CategoryUtil :: getParentCategories($mainCat), true), array_reverse(CategoryUtil :: getParentCategories($mainCat), true));
		$pnRender->assign('rootCat', $rootCat);
		$pnRender->assign('categories', $cats);
		$pnRender->assign('lang', pnUserGetLang());
		$pnRender->assign(pnModGetVar('crpVideo'));
	}

	$popvideos=array();
	$popvideos = pnModAPIFunc('crpVideo', 'user', 'getall',
		array('startnum' => 1,
				'active' => 'A',
				'itemsperpage'=> $modvars['main_items'],
				'orderBy' => 'counter',
				'sortOrder' => 'DESC'
				));

	$newvideos=array();
	$newvideos = pnModAPIFunc('crpVideo', 'user', 'getall',
		array('startnum' => 1,
				'active' => 'A',
				'itemsperpage'=> $modvars['main_items'],
				'orderBy' => 'cr_date',
				'sortOrder' => 'DESC'
				));

	// assign the item output to the template
	$pnRender->assign('parents', $parents);
	$pnRender->assign('category', $rootCat);
	$pnRender->assign($modvars);
	$pnRender->assign('mostviewedvideos',$popvideos);
	$pnRender->assign('recentvideos',$newvideos);

	// Return the output that has been generated by this function
	return $pnRender->fetch('crpvideo_user_main.htm');
}

/**
 * view items
 *
 * @return string html string
 */
function crpVideo_user_view($args)
{
	// Security check
	if (!SecurityUtil :: checkPermission('crpVideo::', '::', ACCESS_OVERVIEW))
	{
		return LogUtil :: registerPermissionError();
	}

	$startnum = (int) FormUtil :: getPassedValue('startnum', isset ($args['startnum']) ? $args['startnum'] : 0, 'GET');
	$cat = (string) FormUtil :: getPassedValue('cat', isset ($args['cat']) ? $args['cat'] : null, 'GET');
	$uid = (int) FormUtil :: getPassedValue('uid', null, 'GET');

	// defaults and input validation
	if (!is_numeric($startnum) || $startnum < 0)
	{
		$startnum = 1;
	}

	// get all module vars for later use
	$modvars = pnModGetVar('crpVideo');

	// Create output object
	$pnRender = pnRender :: getInstance('crpVideo', false);

	// check if categorisation is enabled
	if ($modvars['enablecategorization'])
	{
		Loader :: loadClass('CategoryUtil');
		Loader :: loadClass('CategoryRegistryUtil');
		if (isset ($cat) && !is_numeric($cat))
		{
			//$mainCat = CategoryUtil::getCategoryByID(CategoryRegistryUtil::getRegisteredModuleCategory ('Pages', 'pages', 'Main', '/__SYSTEM__/Modules/Pages'));
			$cat = CategoryUtil :: getCategoryByPath("{$mainCat['path']}/{$cat}");
		}
		elseif (is_numeric($cat))
		{
			$cat = CategoryUtil :: getCategoryByID($cat);
		}
		$mainCat = CategoryRegistryUtil :: getRegisteredModuleCategory('crpVideo', 'crpvideos', 'Main', '/__SYSTEM__/Modules/crpVideo');
		$rootCat = CategoryUtil :: getCategoryByID($mainCat);
		($cat['id']) ? '' : $cat = $rootCat;
		$cats = CategoryUtil :: getCategoriesByParentID($cat['id']);

		$pnRender->assign('rootCat', $rootCat);
		$pnRender->assign('categories', $cats);

	}

	// Get all matching pages
	$items = pnModAPIFunc('crpVideo', 'user', 'getall', array (
		'startnum' => $startnum,
		'itemsperpage' => $modvars['itemsperpage'],
		'uid' => $uid,
		'category' => (isset ($cat['id']
	)) ? $cat['id'] : null));

	// assign various useful template variables
	$pnRender->assign('startnum', $startnum);
	$pnRender->assign('category', $cat);
	$pnRender->assign('mainCat', $mainCat);
	$pnRender->assign('lang', pnUserGetLang());
	$pnRender->assign($modvars);

	// assign the item output to the template
	$pnRender->assign('videos', $items);

	// assign the values for the smarty plugin to produce a pager
	$category_id = (isset ($cat['id'])) ? $cat['id'] : null;
	$pnRender->assign('pager', array (
		'numitems' => pnModAPIFunc('crpVideo',
		'user',
		'countitems',
		array (
			'category' => $category_id,
			'active' => 'A'
		)
	), 'itemsperpage' => $modvars['itemsperpage']));

	// Return the output that has been generated by this function
	return $pnRender->fetch('crpvideo_user_view.htm');
}

/**
 * display item
 *
 * @return string html string
 */
function crpVideo_user_display($args)
{
	// Security check
	if (!SecurityUtil :: checkPermission('crpVideo::', '::', ACCESS_READ))
	{
		return LogUtil :: registerPermissionError();
	}

	$videoid = FormUtil :: getPassedValue('videoid', isset ($args['videoid']) ? $args['videoid'] : null, 'REQUEST');
	$title = FormUtil :: getPassedValue('title', isset ($args['title']) ? $args['title'] : null, 'REQUEST');
	$video = FormUtil :: getPassedValue('video', isset ($args['video']) ? $args['video'] : null, 'REQUEST');
	$objectid = FormUtil :: getPassedValue('objectid', isset ($args['objectid']) ? $args['objectid'] : null, 'REQUEST');
	if (!empty ($objectid))
	{
		$videoid = $objectid;
	}

	// Set the default page number
	if (empty ($video))
	{
		$video = 1;
	}

	// Get the page
	if (isset ($videoid) && is_numeric($videoid))
	{
		$item = pnModAPIFunc('crpVideo', 'user', 'get', array (
			'videoid' => $videoid
		));
	}
	else
	{
		$item = pnModAPIFunc('crpVideo', 'user', 'get', array (
			'title' => $title
		));
		pnQueryStringSetVar('videoid', $item['videoid']);
	}

	// The return value of the function is checked here
	if ($item == false || ($item['obj_status'] == 'P' && !SecurityUtil :: checkPermission('crpVideo::', '::', ACCESS_EDIT)))
	{
		return LogUtil :: registerError(_NOSUCHITEM, 404);
	}

	// Create output object
	$pnRender = pnRender :: getInstance('crpVideo');

	// Regardless of caching, we need to increment the read count and set the cache ID
	if (isset ($videoid) && is_numeric($videoid))
	{
		$pnRender->cache_id = $videoid . $video;
		$incrementresult = pnModAPIFunc('crpVideo', 'user', 'incrementreadcount', array (
			'videoid' => $videoid
		));
	}
	else
	{
		$pnRender->cache_id = $title . $video;
		$incrementresult = pnModAPIFunc('crpVideo', 'user', 'incrementreadcount', array (
			'title' => $title
		));
	}
	if ($incrementresult == false)
	{
		return LogUtil :: registerError(_NOSUCHITEM, 404);
	}

	// load the categories system
	if (pnModGetVar('crpVideo', 'enablecategorization'))
	{
		Loader :: loadClass('CategoryUtil');
		$cat = CategoryUtil :: getCategoryByID($item['__CATEGORIES__']['Main']['id']);
		$cats = CategoryUtil :: getCategoriesByParentID($cat['id']);
		$pnRender->assign('categories', $cats);
		$pnRender->assign('category', $cat);
	}

	$pnRender->assign('lang', pnUserGetLang());
	$pnRender->assign(pnModGetVar('crpVideo'));

	// determine which template to render this page with
	// A specific template may exist for this page (based on video id)
	if ($pnRender->template_exists("crpvideo_user_display_$videoid"))
	{
		$template = "crpvideo_user_display_$videoid";
	}
	else
	{
		$template = 'crpvideo_user_display.htm';
	}

	// check if the contents are cached.
	if ($pnRender->is_cached($template))
	{
		return $pnRender->fetch($template);
	}

	// Assign details of the item.
	$pnRender->assign($item);

	return $pnRender->fetch($template);
}

/**
 * add new item
 *
 * @return string HTML output
 */
function crpVideo_user_new()
{
	// Security check
	if (!SecurityUtil :: checkPermission('crpVideo::Video', '::', ACCESS_COMMENT))
	{
		return LogUtil :: registerPermissionError();
	}

	// Create output object
	$pnRender = pnRender :: getInstance('crpVideo', false);

	// load the categories system
	if (!($class = Loader :: loadClass('CategoryRegistryUtil')))
		pn_exit('Unable to load class [CategoryRegistryUtil] ...');
	$mainCat = CategoryRegistryUtil :: getRegisteredModuleCategory('crpVideo', 'crpvideos', 'Main', '/__SYSTEM__/Modules/crpVideo');
	$pnRender->assign('mainCategory', $mainCat);
	$pnRender->assign(pnModGetVar('crpVideo'));

	// Return the output that has been generated by this function
	return $pnRender->fetch('crpvideo_user_new.htm');
}

/**
 * create a video
 * @param 'title' the title of the video
 * @param 'content' the content of the video
 * @param 'language' the language of the video
 */
function crpVideo_user_create()
{
	// Security check
	if (!SecurityUtil :: checkPermission('crpVideo::Video', '::', ACCESS_COMMENT))
	{
		return LogUtil :: registerPermissionError();
	}
	// Confirm authorisation code
	if (!SecurityUtil :: confirmAuthKey())
	{
		return LogUtil :: registerAuthidError(pnModURL('crpVideo', 'user', 'view'));
	}

	$video = FormUtil :: getPassedValue('video', null, 'POST');
	$video_image = FormUtil :: getPassedValue('video_image', null, 'FILES');
	if ($video['source'] == 'video')
	{
		$video_file = FormUtil :: getPassedValue('video_file', null, 'FILES');
		$newVideoName = time() . '_' . str_replace(" ", "_", $video_file['name']);
		$video['urlvideo'] = pnGetBaseUrl() . pnModGetVar('crpVideo', 'upload_path') . '/' . $newVideoName;
		$video['pathvideo'] = pnModGetVar('crpVideo', 'upload_path') . '/' . $newVideoName;
		$video['file'] = $video_file;
	}

	$videoObj = new crpVideo();

	$video['image'] = $video_image;

	// Argument check
	if (!$videoObj->dao->validateData($video))
		return false;

	// Create the video
	$videoid = pnModAPIFunc('crpVideo', 'user', 'create', $video);

	if ($video_image['error'] == UPLOAD_ERR_OK && $videoid)
	{
		$video_image['videoid'] = $videoid;
		$video_image['document_type'] = 'image';
		$id_image = $videoObj->dao->setFile($video_image);
		if ($id_image == '-1')
			return false;
	}

	if ($video_file['error'] == UPLOAD_ERR_OK && $videoid && $video['source'] == 'video')
	{
		Loader :: loadClass('FileUtil');
		$msg = FileUtil :: uploadFile('video_file', pnModGetVar('crpVideo', 'upload_path'), $newVideoName);
		chmod(pnModGetVar('crpVideo', 'upload_path') . '/' . $newVideoName, 0755);
	}

	// The return value of the function is checked
	if ($videoid != false)
	{
		// Success
		LogUtil :: registerStatus(_CREATESUCCEDED . ' ' . _CRPVIDEO_WAITING);
	}

	return pnRedirect(pnModURL('crpVideo', 'user', 'view'));
}

/**
 * get video's image
 *
 * @return blob image
 */
function crpVideo_user_get_image()
{
	$imageObj = new crpVideo();
	return $imageObj->dao->getImage();
}

/**
 * get event's thumbnail thru gd library
 *
 * @return blob image
 */
function crpVideo_user_get_thumbnail()
{
	$imageObj = new crpVideo();
	return $imageObj->getThumbnail();
}

/**
 * feed items
 *
 * @return string HTML output
 */
function crpVideo_user_getfeed()
{
	// Security check
	if (!SecurityUtil::checkPermission('crpVideo::', '::', ACCESS_READ))
	{
		return LogUtil::registerPermissionError();
	}

	$videoObj = new crpVideo();
	return $videoObj->getFeed();
}

/**
 * feed items
 *
 * @return string HTML output
 */
function crpVideo_user_getpodcast()
{
	// Security check
	if (!SecurityUtil::checkPermission('crpVideo::', '::', ACCESS_READ))
	{
		return LogUtil::registerPermissionError();
	}

	$videoObj = new crpVideo();
	return $videoObj->getPodcast();
}

/**
 * get uploaders list
 *
 * @return html
 */
function crpVideo_user_view_uploaders()
{
	// Security check
	if (!SecurityUtil::checkPermission('crpVideo::', '::', ACCESS_READ))
	{
		return LogUtil::registerPermissionError();
	}

	$video = new crpVideo();
	return $video->listUploaders();
}

/**
 * get uploads details
 *
 * @return html
 */
function crpVideo_user_view_uploads()
{
	// Security check
	if (!SecurityUtil::checkPermission('crpVideo::', '::', ACCESS_READ))
	{
		return LogUtil::registerPermissionError();
	}

	$video = new crpVideo();
	return $video->listUploads();
}

?>