<?php

/**
 * crpVideo
 *
 * @copyright (c) 2007,2009 Daniele Conca
 * @link http://code.zikula.org/crpvideo Support and documentation
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

	$args['obj_status']= 'P';

	// Security check
	if (!SecurityUtil :: checkPermission('crpVideo::Video', "$args[title]::", ACCESS_COMMENT))
	{
		return LogUtil :: registerError(_MODULENOAUTH);
	}

	$object= DBUtil :: insertObject($args, 'crpvideos', 'videoid');
	if (!$object)
	{
		return LogUtil :: registerError(_CREATEFAILED);
	}

	// notify by mail if not an admin
	if (pnModGetVar('crpVideo', 'crpvideo_notification'))
		crpVideo :: notifyByMail($args, $object['videoid']);

	// Let any hooks know that we have created a new item.
	pnModCallHooks('item', 'create', $object['videoid'], array (
		'module' => 'crpVideo'
	));

	// Return the id of the newly created item to the calling process
	return $object['videoid'];
}

/**
 * get all videos
 * @return mixed array of items, or false on failure
 */
function crpVideo_userapi_getall($args)
{
	if (!isset ($args['startnum']) || empty ($args['startnum']))
		$args['startnum']= 0;
	if (!isset ($args['itemsperpage']) || empty ($args['itemsperpage']))
		$args['itemsperpage']= -1;
	if (isset ($args['modvars']['itemsperpage']) && !empty ($args['itemsperpage']))
		$args['itemsperpage']= $args['modvars']['itemsperpage'];
	if (!isset ($args['ignoreml']) || !is_bool($args['ignoreml']))
		$args['ignoreml']= false;
	if (!isset ($args['category']))
		$args['category']= null;
	if (!isset ($args['sortOrder']))
		$args['sortOrder']= 'ASC';
	if (!isset ($args['orderBy']))
		$args['orderBy']= 'title';
	if (!isset ($args['active']))
		$args['active']= 'A';
	if (!isset ($args['uid']))
		$args['uid']= false;
	if (!isset ($args['interval']))
		$args['interval']= null;
	if (!isset ($args['extension']))
		$args['extension']= null;

	if (!is_numeric($args['startnum']) || !is_numeric($args['itemsperpage']))
	{
		return LogUtil :: registerError(_MODARGSERROR);
	}

	$catFilter= array ();
	if (is_array($args['category']))
		$catFilter= $args['category'];
	else
		if ($args['category'])
		{
			$catFilter['Main']= $args['category'];
			$catFilter['__META__']['module']= 'crpVideo';
		}

	$items= array ();

	// Security check
	if (!SecurityUtil :: checkPermission('crpVideo::', '::', ACCESS_READ))
	{
		return $items;
	}

	// populate an array with each part of the where clause and then implode the array if there is a need.
	// credit to Jorg Napp for this technique - markwest
	$pntable= pnDBGetTables();
	$videoscolumn= $pntable['crpvideos_column'];
	$queryargs= array ();
	$nowDate= DateUtil :: getDatetime();

	if (pnConfigGetVar('multilingual') == 1 && !$args['ignoreml'])
	{
		$queryargs[]= "($videoscolumn[language]='" . DataUtil :: formatForStore(pnUserGetLang()) . "' OR $videoscolumn[language]='')";
	}
	if ($args['active'])
	{
		$queryargs[]= "($videoscolumn[obj_status]='" . DataUtil :: formatForStore($args['active']) . "')";
	}
	if ($args['uid'])
	{
		$queryargs[]= "($videoscolumn[cr_uid]='" . DataUtil :: formatForStore($args['uid']) . "')";
	}
	if ($args['interval'])
	{
		$intervaltime= time() - $args['interval'] * 86400;
		$intervalDate= DateUtil :: getDatetime($intervaltime);
		$queryargs[]= "($videoscolumn[cr_date] < '" . DataUtil :: formatForStore($nowDate) . "' " .
		"AND $videoscolumn[cr_date] > '" . DataUtil :: formatForStore($intervalDate) . "')";
	}
	if ($args['extension'])
	{
		$queryargs[]= "($videoscolumn[pathvideo] LIKE '%." . DataUtil :: formatForStore($args['extension']) . "')";
	}

	$where= null;
	if (count($queryargs) > 0)
	{
		$where= ' WHERE ' . implode(' AND ', $queryargs);
	}

	// define the permission filter to apply
	$permFilter= array (
		array (
			'realm' => 0,
			'component_left' => 'crpVideo',
			'component_right' => 'Video',
			'instance_left' => 'cr_uid',
			'instance_center' => 'title',
			'instance_right' => 'videoid',
			'level' => ACCESS_READ
		)
	);

	$orderColumn= $args['orderBy'];
	$orderby= "ORDER BY $videoscolumn[$orderColumn] $args[sortOrder]";

	// get the objects from the db
	$objArray= DBUtil :: selectObjectArray('crpvideos', $where, $orderby, $args['startnum'] - 1, $args['itemsperpage'], '', $permFilter, $catFilter);

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
 * Retrieve list of events, filtered as specified, for form use
 */
function crpVideo_userapi_getall_formlist($navigationValues)
{
	// Security check
	if (!SecurityUtil :: checkPermission('crpVideo::Video', '::', ACCESS_READ))
	{
		return LogUtil :: registerPermissionError();
	}

	$video= new crpVideo();

	return $video->dao->formList($navigationValues['startnum'], $navigationValues['category'], $navigationValues['clear'], $navigationValues['ignoreml'], $navigationValues['modvars'], $navigationValues['mainCat'], 'A', $navigationValues['interval'], $navigationValues['sortOrder']);
}

/**
 * get uploaders with conter
 * @return mixed array of items, or false on failure
 */
function crpVideo_userapi_get_uploaders($navigationValues)
{
	// Security check
	if (!SecurityUtil :: checkPermission('crpVideo::Video', '::', ACCESS_READ))
	{
		return LogUtil :: registerPermissionError();
	}

	$video= new crpVideo();

	return $video->dao->getUploaders($navigationValues['startnum'], $navigationValues['category'], $navigationValues['clear'], $navigationValues['ignoreml'], $navigationValues['modvars'], $navigationValues['mainCat'], 'A', $navigationValues['interval'], $navigationValues['sortOrder'], $navigationValues['orderBy'], $navigationValues['uid']);
}

/**
 * get a specific item
 * @param $args['videoid'] id of example item to get
 * @return mixed item array, or false on failure
 */
function crpVideo_userapi_get($args)
{
	// optional arguments
	if (isset ($args['objectid']))
	{
		$args['videoid']= $args['objectid'];
	}

	// Argument check
	if ((!isset ($args['videoid']) || !is_numeric($args['videoid'])) && !isset ($args['title']))
	{
		return LogUtil :: registerError(_MODARGSERROR);
	}

	$video= new crpVideo();
	return $video->dao->getData($args);
}

/**
 * utility function to count the number of items held by this module
 * @return integer number of items held by this module
 */
function crpVideo_userapi_countitems($args)
{
	$pntable= pnDBGetTables();
	$crpvideocolumn= $pntable['crpvideos_column'];

	$where= '';
	$nowDate= DateUtil :: getDatetime();

	$catFilter= array ();
	if (is_array($args['category']))
		$catFilter= $args['category'];
	else
		if ($args['category'])
		{
			$catFilter['Main']= $args['category'];
			$catFilter['__META__']['module']= 'crpVideo';
		}

	$queryargs= array ();
	if (pnConfigGetVar('multilingual') == 1 && !$args['ignoreml'])
	{
		$queryargs[]= "($crpvideocolumn[language]='" . DataUtil :: formatForStore(pnUserGetLang()) . "' OR $crpvideocolumn[language]='')";
	}
	if ($args['active'])
	{
		$queryargs[]= "($crpvideocolumn[obj_status]='" . DataUtil :: formatForStore($args['active']) . "')";
	}
	if ($args['uid'])
	{
		$queryargs[]= "($crpvideocolumn[cr_uid]='" . DataUtil :: formatForStore($args['uid']) . "')";
	}
	if ($args['interval'])
	{
		$intervaltime= time() - $args['interval'] * 86400;
		$intervalDate= DateUtil :: getDatetime($intervaltime);
		$queryargs[]= "($crpvideocolumn[cr_date] < '" . DataUtil :: formatForStore($nowDate) . "' " .
		"AND $crpvideocolumn[cr_date] > '" . DataUtil :: formatForStore($intervalDate) . "')";
	}

	$where= null;
	if (count($queryargs) > 0)
	{
		$where= ' WHERE ' . implode(' AND ', $queryargs);
	}

	// Return the number of items
	if ($args['uploaders'])
		return DBUtil :: selectObjectCount('crpvideos', $where, 'cr_uid', true, $catFilter);
	else
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

	$cat= $args['cat'];
	$module= $args['module'];
	$tablename= $args['tablename'];
	($args['module']) ? $property= $args['property'] : $property= 'Main';

	// process the relative paths of the categories
	if (pnModGetVar($module, 'enablecategorization') && !empty ($cat))
	{
		$registeredCats= array ();
		if (!($class= Loader :: loadClass('CategoryRegistryUtil')))
		{
			pn_exit(pnML('_UNABLETOLOADCLASS', array (
				's' => 'CategoryRegistryUtil'
			)));
		}
		$registeredCats= CategoryRegistryUtil :: getRegisteredModuleCategories($module, $tablename);

		if (!CategoryUtil :: hasCategoryAccess($cat, $module))
			return false;
	}

	$mainCat= $registeredCats[$property];

	$parents= array_diff_assoc(array_reverse(CategoryUtil :: getParentCategories($cat['id']), true), array_reverse(CategoryUtil :: getParentCategories($mainCat), true));

	return $parents;
}

/**
 * custom url string
 *
 * @return string custom url string
 */
function crpVideo_userapi_encodeurl($args)
{
	// check we have the required input
	if (!isset ($args['modname']) || !isset ($args['func']) || !isset ($args['args']))
		return LogUtil :: registerError(_MODARGSERROR);

	if (!isset ($args['type']))
		$args['type']= 'user';

	// create an empty string ready for population
	$vars= '';

	switch ($args['func'])
	{
		case "view" :
			// category
			if (isset ($args['args']['cat']))
			{
				//$displayCat = CategoryUtil :: getCategoryByID($args['args']['cat']);
				//$vars= 'cat/' . $displayCat['display_name'][pnUserGetLang()];
				$vars= 'cat/' . $args['args']['cat'];
			}
			// page
			if (isset ($args['args']['startnum']) && $args['args']['startnum'] != 1)
			{
				$args['args']['page']= ($args['args']['startnum'] - 1) / pnModGetVar('crpVideo', 'itemsperpage') + 1;
				$vars .= (empty ($vars) ? '' : '/') . 'page/' . $args['args']['page'];
			}

			break;

		case "view_uploaders" :
			// page
			if (isset ($args['args']['startnum']) && $args['args']['startnum'] != 1)
			{
				$args['args']['page']= ($args['args']['startnum'] - 1) / pnModGetVar('crpVideo', 'itemsperpage') + 1;
				$vars .= (empty ($vars) ? '' : '/') . 'page/' . $args['args']['page'];
			}

			break;
		case "view_uploads" :
			// uid
			if (isset ($args['args']['uid']))
			{
				$vars= 'uid/' . $args['args']['uid'];
			}
			// page
			if (isset ($args['args']['startnum']) && $args['args']['startnum'] != 1)
			{
				$args['args']['page']= ($args['args']['startnum'] - 1) / pnModGetVar('crpVideo', 'itemsperpage') + 1;
				$vars .= (empty ($vars) ? '' : '/') . 'page/' . $args['args']['page'];
			}

			break;

		case "display" :
			// check for the generic object id parameter
			if (isset ($args['args']['objectid']))
			{
				$args['args']['videoid']= $args['args']['objectid'];
			}
			// get the item (will be cached by DBUtil)
			if (isset ($args['args']['videoid']))
			{
				$item= pnModAPIFunc('crpVideo', 'user', 'get', array (
					'videoid' => $args['args']['videoid']
				));
			}
			else
			{
				$item= pnModAPIFunc('crpVideo', 'user', 'get', array (
					'title' => $args['args']['title']
				));
			}
			$vars= $item['urltitle'];
			// don't display the function name if either displaying a page or the normal overview
			$args['func']= '';

			break;

		case "watch" :
			// check for the generic object id parameter
			if (isset ($args['args']['objectid']))
			{
				$args['args']['videoid']= $args['args']['objectid'];
			}
			// get the item (will be cached by DBUtil)
			if (isset ($args['args']['videoid']))
			{
				$item= pnModAPIFunc('crpVideo', 'user', 'get', array (
					'videoid' => $args['args']['videoid']
				));
			}
			else
			{
				$item= pnModAPIFunc('crpVideo', 'user', 'get', array (
					'title' => $args['args']['title']
				));
			}
			$vars= $item['urltitle'];
			// don't display the function name if either displaying a page or the normal overview
			$args['func']= 'watch';

			break;

		case "main" :
			// don't display the function name if either displaying a page or the normal overview
			$args['func']= '';

			break;

		case "get_image" :
			// id
			if (isset ($args['args']['videoid']))
			{
				$vars= 'videoid/' . $args['args']['videoid'];
			}

			break;

		case "getplaylist" :
			// id
			if (isset ($args['args']['videoid']))
			{
				$vars= 'videoid/' . $args['args']['videoid'];
			}
			// category
			if (isset ($args['args']['id']))
			{
				$vars .= (empty ($vars) ? '' : '/') . 'id/' . $args['args']['id'];
			}
			break;

		case "get_thumbnail" :
			// id
			if (isset ($args['args']['videoid']))
			{
				$vars= 'videoid/' . $args['args']['videoid'];
			}
			// width
			if (isset ($args['args']['width']))
			{
				$vars .= (empty ($vars) ? '' : '/') . 'width/' . $args['args']['width'];
			}

			break;

		default :
			break;
	}

	// construct the custom url part
	if (empty ($args['func']) && empty ($vars))
		return $args['modname'] . '/';
	elseif (empty ($args['func'])) return $args['modname'] . '/' . $vars . '/';
	elseif (empty ($vars)) return $args['modname'] . '/' . $args['func'] . '/';
	else
		return $args['modname'] . '/' . $args['func'] . '/' . $vars . '/';
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
		return LogUtil :: registerError(_MODARGSERROR);

	// define the available user functions
	$funcs= array (
		'main',
		'view',
		'view_uploaders',
		'display',
		'watch',
		'new',
		'create',
		'get_thumbnail',
		'get_image',
		'getfeed',
		'getplaylist',
		'getpodcast',
		'view_uploads'
	);
	// set the correct function name based on our input
	if (empty ($args['vars'][2]))
	{
		pnQueryStringSetVar('func', 'main');
	}
	elseif (!in_array($args['vars'][2], $funcs))
	{
		pnQueryStringSetVar('func', 'display');
		$nextvar= 2;
	}
	else
	{
		pnQueryStringSetVar('func', $args['vars'][2]);
		$nextvar= 3;
	}

	// video list
	if (FormUtil :: getPassedValue('func') == 'view' && isset ($args['vars'][$nextvar]))
	{
		// get rid of unused vars
		$args['vars']= array_slice($args['vars'], $nextvar);
		$nextvar= 0;
		if ($args['vars'][$nextvar] == 'page')
		{
			pnQueryStringSetVar('startnum', (int) (($args['vars'][$nextvar +1] - 1) * pnModGetVar('crpVideo', 'itemsperpage') + 1));
			$nextvar= 2;
		}
		else
		{
			// add the category info
			pnQueryStringSetVar('cat', (string) $args['vars'][$nextvar]);

			if (isset ($args['vars'][$nextvar +1]))
			{
				// check if there's a page arg
				$varscount= count($args['vars']);
				($args['vars'][$varscount -2] == 'page') ? $pagersize= 2 : $pagersize= 0;
				// extract the category path
				$cat= implode('/', array_slice($args['vars'], 1, $varscount - $pagersize -1));
				pnQueryStringSetVar('cat', $cat);
				$nextvar= 2;
			}
		}
		if (isset ($args['vars'][$nextvar]) && $nextvar != 0 && $args['vars'][$nextvar] == 'page')
		{
			pnQueryStringSetVar('startnum', (int) (($args['vars'][$nextvar +1] - 1) * pnModGetVar('crpVideo', 'itemsperpage') + 1));
		}
	}

	// uploaders list
	if (FormUtil :: getPassedValue('func') == 'view_uploaders' && isset ($args['vars'][$nextvar]))
	{
		// get rid of unused vars
		$args['vars']= array_slice($args['vars'], $nextvar);
		$nextvar= 0;
		if ($args['vars'][$nextvar] == 'page')
		{
			pnQueryStringSetVar('startnum', (int) (($args['vars'][$nextvar +1] - 1) * pnModGetVar('crpVideo', 'itemsperpage') + 1));
		}
	}

	// uploads list
	if (FormUtil :: getPassedValue('func') == 'view_uploads' && isset ($args['vars'][$nextvar]))
	{
		// get rid of unused vars
		$args['vars']= array_slice($args['vars'], $nextvar);
		$nextvar= 0;
		if ($args['vars'][$nextvar] == 'uid')
		{
			pnQueryStringSetVar('uid', $args['vars'][$nextvar +1]);
			$nextvar= 2;
		}
		if ($args['vars'][$nextvar] == 'page')
		{
			pnQueryStringSetVar('startnum', (int) (($args['vars'][$nextvar +1] - 1) * pnModGetVar('crpVideo', 'itemsperpage') + 1));
		}
	}

	// video thumbnail
	if (FormUtil :: getPassedValue('func') == 'get_thumbnail' && isset ($args['vars'][$nextvar]))
	{
		// get rid of unused vars
		$args['vars']= array_slice($args['vars'], $nextvar);
		$nextvar= 0;
		if ($args['vars'][$nextvar] == 'videoid')
		{
			pnQueryStringSetVar('videoid', $args['vars'][$nextvar +1]);
			$nextvar= 2;
		}
		if ($args['vars'][$nextvar] == 'width')
		{
			pnQueryStringSetVar('width', $args['vars'][$nextvar +1]);
		}
	}

	// video image
	if (FormUtil :: getPassedValue('func') == 'get_image' && isset ($args['vars'][$nextvar]))
	{
		// get rid of unused vars
		$args['vars']= array_slice($args['vars'], $nextvar);
		$nextvar= 0;
		if ($args['vars'][$nextvar] == 'videoid')
		{
			pnQueryStringSetVar('videoid', $args['vars'][$nextvar +1]);
			$nextvar= 2;
		}
	}

	// video page
	if (FormUtil :: getPassedValue('func') == 'display' || FormUtil :: getPassedValue('func') == 'watch')
	{
		// get rid of unused vars
		$args['vars']= array_slice($args['vars'], $nextvar);

		$nextvar= 0;
		if (is_numeric($args['vars'][$nextvar]))
		{
			pnQueryStringSetVar('videoid', $args['vars'][$nextvar]);
		}
		else
		{
			pnQueryStringSetVar('title', $args['vars'][$nextvar]);
		}
	}

	// playlist
	if (FormUtil :: getPassedValue('func') == 'getplaylist' && isset ($args['vars'][$nextvar]))
	{
		// get rid of unused vars
		$args['vars']= array_slice($args['vars'], $nextvar);
		$nextvar= 0;
		if ($args['vars'][$nextvar] == 'videoid')
		{
			pnQueryStringSetVar('videoid', $args['vars'][$nextvar +1]);
			$nextvar= 2;
		}
		if ($args['vars'][$nextvar] == 'id')
		{
			pnQueryStringSetVar('id', $args['vars'][$nextvar +1]);
		}
	}

	return true;
}