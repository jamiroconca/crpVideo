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

/**
 * create a new video
 * @param $args['title'] name of the item
 * @param $args['content'] content of the item
 * @param $args['language'] language of the item
 * @return mixed video ID on success, false on failure
 */
function crpVideo_userapi_create($args)
{
	// defaults
	if (!isset ($args['language']))
	{
		$args['language'] = '';
	}
	if (!isset ($args['displaywrapper']))
	{
		$args['displaywrapper'] = false;
	}

	// define the permalink title if not present
	if (!isset ($args['urltitle']) || empty ($args['urltitle']))
	{
		$args['urltitle'] = DataUtil :: formatPermalink($args['title']);
	}

	$args['obj_status'] = 'P';

	// Security check
	if (!SecurityUtil :: checkPermission('crpVideo::Video', "$args[title]::", ACCESS_COMMENT))
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
 * get all videos
 * @return mixed array of items, or false on failure
 */
function crpVideo_userapi_getall($args)
{
	if (!isset ($args['startnum']) || empty ($args['startnum']))
		$args['startnum'] = 0;
	if (!isset ($args['numitems']) || empty ($args['numitems']))
		$args['numitems'] = -1;
	if (!isset ($args['ignoreml']) || !is_bool($args['ignoreml']))
		$args['ignoreml'] = false;
	if (!isset ($args['category']))
		$args['category'] = null;
	if (!isset ($args['sortOrder']))
		$args['sortOrder'] = 'ASC';
	if (!isset ($args['orderBy']))
		$args['orderBy'] = 'title';
	if (!isset ($args['active']))
		$args['active'] = 'A';
	if (!isset ($args['uid']))
		$args['uid'] = false;

	if (!is_numeric($args['startnum']) || !is_numeric($args['numitems']))
	{
		return LogUtil :: registerError(_MODARGSERROR);
	}

	$catFilter = array ();
	if (is_array($args['category']))
		$catFilter = $args['category'];
	else
		if ($args['category'])
		{
			$catFilter['Main'] = $args['category'];
			$catFilter['__META__']['module'] = 'crpVideo';
		}

	$items = array ();

	// Security check
	if (!SecurityUtil :: checkPermission('crpVideo::', '::', ACCESS_READ))
	{
		return $items;
	}

	// populate an array with each part of the where clause and then implode the array if there is a need.
	// credit to Jorg Napp for this technique - markwest
	$pntable = pnDBGetTables();
	$videoscolumn = $pntable['crpvideos_column'];
	$queryargs = array ();
	if (pnConfigGetVar('multilingual') == 1 && !$args['ignoreml'])
	{
		$queryargs[] = "($videoscolumn[language]='" . DataUtil :: formatForStore(pnUserGetLang()) . "' OR $videoscolumn[language]='')";
	}
	if ($args['active'])
	{
		$queryargs[] = "($videoscolumn[obj_status]='" . DataUtil :: formatForStore($args['active']) . "')";
	}
	if ($args['uid'])
	{
		$queryargs[] = "($videoscolumn[cr_uid]='" . DataUtil :: formatForStore($args['uid']) . "')";
	}

	$where = null;
	if (count($queryargs) > 0)
	{
		$where = ' WHERE ' . implode(' AND ', $queryargs);
	}

	// define the permission filter to apply
	$permFilter = array (
		array (
			'realm' => 0,
			'component_left' => 'crpVideo',
			'component_right' => 'Video',
			'instance_left' => 'title',
			'instance_right' => 'videoid',
			'level' => ACCESS_READ
		)
	);

	$orderColumn = $args['orderBy'];
	$orderby = "ORDER BY $videoscolumn[$orderColumn] $args[sortOrder]";

	// get the objects from the db
	$objArray = DBUtil :: selectObjectArray('crpvideos', $where, $orderby, $args['startnum'] - 1, $args['numitems'], '', $permFilter, $catFilter);

	// Check for an error with the database code, and if so set an appropriate
	// error message and return
	if ($objArray === false)
	{
		return LogUtil :: registerError(_GETFAILED);
	}

	// need to do this here as the category expansion code can't know the
	// root category which we need to build the relative path component
	if ($objArray && isset ($args['mainCat']) && $args['mainCat'])
	{
		if (!Loader :: loadClass('CategoryUtil'))
			pn_exit('Unable to load class [CategoryUtil]');
		ObjectUtil :: postProcessExpandedObjectArrayCategories($objArray, $args['mainCat']);
	}

	// Return the items
	return $objArray;
}

/**
 * get a specific item
 * @param $args['videoid'] id of example item to get
 * @return mixed item array, or false on failure
 */
function crpVideo_userapi_get($args)
{
	// Argument check
	if ((!isset ($args['videoid']) || !is_numeric($args['videoid'])) && !isset ($args['title']))
	{
		return LogUtil :: registerError(_MODARGSERROR);
	}

	$video = new crpVideo();
	return $video->dao->getData($args);
}

/**
 * utility function to count the number of items held by this module
 * @return integer number of items held by this module
 */
function crpVideo_userapi_countitems($args)
{
	$pntable = pnDBGetTables();
	$crpvideocolumn = $pntable['crpvideos_column'];

	$where = '';

	$catFilter = array ();
	if (is_array($args['category']))
		$catFilter = $args['category'];
	else
		if ($args['category'])
		{
			$catFilter['Main'] = $args['category'];
			$catFilter['__META__']['module'] = 'crpVideo';
		}

	if ($args['active'])
		$where = " WHERE $crpvideocolumn[obj_status]='" . DataUtil :: formatForStore($args['active']) . "'";

	// Return the number of items
	return DBUtil :: selectObjectCount('crpvideos', $where, 'videoid', false, $catFilter);
}

/**
 * increment the item read count
 * @return bool true on success, false on failiure
 */
function crpVideo_userapi_incrementreadcount($args)
{
	if ((!isset ($args['videoid']) || !is_numeric($args['videoid'])) && !isset ($args['title']))
	{
		return LogUtil :: registerError(_MODARGSERROR);
	}

	if (isset ($args['videoid']))
	{
		return DBUtil :: incrementObjectFieldByID('crpvideos', 'counter', $args['videoid'], 'videoid');
	}
	else
	{
		return DBUtil :: incrementObjectFieldByID('crpvideos', 'counter', $args['title'], 'urltitle');
	}
}

/**
 * form custom url string *
 * @return string custom url string
 */
function crpVideo_userapi_encodeurl($args)
{
	// check we have the required input
	if (!isset ($args['modname']) || !isset ($args['func']) || !isset ($args['args']))
	{
		return LogUtil :: registerError(_MODARGSERROR);
	}

	// create an empty string ready for population
	$vars = '';

	// view function
	if ($args['func'] == 'view' && isset ($args['args']['cat']))
	{
		$vars = substr($args['args']['cat'], 1);
	}

	// for the display function use either the title (if present) or the video id
	if ($args['func'] == 'display')
	{
		// check for the generic object id parameter
		if (isset ($args['args']['objectid']))
		{
			$args['args']['videoid'] = $args['args']['videoid'];
		}
		// get the item (will be cached by DBUtil)
		if (isset ($args['args']['videoid']))
		{
			$item = pnModAPIFunc('crpVideo', 'user', 'get', array (
				'videoid' => $args['args']['videoid']
			));
		}
		else
		{
			$item = pnModAPIFunc('crpVideo', 'user', 'get', array (
				'title' => $args['args']['title']
			));
		}
		if (pnModGetVar('crpVideo', 'addcategorytitletopermalink') && isset ($args['args']['cat']))
		{
			$vars = $args['args']['cat'] . '/' . $item['urltitle'];
		}
		else
		{
			$vars = $item['urltitle'];
		}
		if (isset ($args['args']['video']) && $args['args']['video'] != 1)
		{
			$vars .= '/video/' . $args['args']['video'];
		}
	}

	// don't display the function name if either displaying a video or the normal overview
	if ($args['func'] == 'main' || $args['func'] == 'display')
	{
		$args['func'] = '';
	}

	// construct the custom url part
	if (empty ($args['func']) && empty ($vars))
	{
		return $args['modname'] . '/';
	}
	elseif (empty ($args['func']))
	{
		return $args['modname'] . '/' . $vars . '/';
	}
	elseif (empty ($vars))
	{
		return $args['modname'] . '/' . $args['func'] . '/';
	}
	else
	{
		return $args['modname'] . '/' . $args['func'] . '/' . $vars . '/';
	}
}

/**
 * decode the custom url string
 *
 * @return bool true if successful, false otherwise
 */
function crpVideo_userapi_decodeurl($args)
{
	// check we actually have some vars to work with...
	if (!isset ($args['vars']))
	{
		return LogUtil :: registerError(_MODARGSERROR);
	}

	// define the available user functions
	$funcs = array (
		'main',
		'view',
		'display'
	);
	// set the correct function name based on our input
	if (empty ($args['vars'][2]))
	{
		pnQueryStringSetVar('func', 'main');
	}
	elseif (!in_array($args['vars'][2], $funcs))
	{
		pnQueryStringSetVar('func', 'display');
		$nextvar = 2;
	}
	else
	{
		pnQueryStringSetVar('func', $args['vars'][2]);
		$nextvar = 3;
	}

	// add the category info
	if (FormUtil :: getPassedValue('func') == 'view')
	{
		pnQueryStringSetVar('cat', (string) $args['vars'][$nextvar]);
	}

	// identify the correct parameter to identify the video
	if (FormUtil :: getPassedValue('func') == 'display')
	{
		if (pnModGetVar('crpVideo', 'addcategorytitletopermalink') && !empty ($args['vars'][$nextvar +1]))
		{
			$nextvar++;
		}
		if (is_numeric($args['vars'][$nextvar]))
		{
			pnQueryStringSetVar('videoid', $args['vars'][$nextvar]);
		}
		else
		{
			pnQueryStringSetVar('title', $args['vars'][$nextvar]);
		}
		$nextvar++;
		if ($args['vars'][$nextvar] == 'video')
		{
			pnQueryStringSetVar('video', (int) $args['vars'][$nextvar +1]);
		}
	}

	return true;
}

/**
 * get meta data for the module
 *
 */
function crpVideo_userapi_getmodulemeta()
{
	return array (
		'viewfunc' => 'view',
		'displayfunc' => 'display',
		'newfunc' => 'new',
		'createfunc' => 'create',
		'modifyfunc' => 'modify',
		'updatefunc' => 'update',
		'deletefunc' => 'delete',
		'titlefield' => 'title',
		'itemid' => 'videoid'
	);
}

function crpVideo_userapi_breadcrumblinks($args)
{
	if (!$args['tablename'])
		return false;

	$cat = $args['cat'];
	$module = $args['module'];
	$tablename = $args['tablename'];
	($args['module']) ? $property = $args['property'] : $property = 'Main';

	// process the relative paths of the categories
	if (pnModGetVar($module, 'enablecategorization') && !empty ($cat))
	{
		$registeredCats = array ();
		if (!($class = Loader :: loadClass('CategoryRegistryUtil')))
		{
			pn_exit(pnML('_UNABLETOLOADCLASS', array (
				's' => 'CategoryRegistryUtil'
			)));
		}
		$registeredCats = CategoryRegistryUtil :: getRegisteredModuleCategories($module, $tablename);

		if (!CategoryUtil :: hasCategoryAccess($cat, $module))
			return false;
	}

	$mainCat = $registeredCats[$property];

	$parents = array_diff_assoc(array_reverse(CategoryUtil :: getParentCategories($cat['id']), true), array_reverse(CategoryUtil :: getParentCategories($mainCat), true));

	return $parents;
}