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

/**
 * initialise rss feed
 *
 */
function crpVideo_videos_rss_init()
{

}

/**
 * get information on rss
 * 
 */
function crpVideo_videos_rss_info()
{
	return array (
		'name' => 'Videos',
		'module' => 'crpVideo',
		'long_descr' => 'Videos Titles'
	);
}

/**
 * display rss
 *
 * @param        array       $rssinfo     a rssinfo structure
 * @return       output      the rendered rss
 */
function crpVideo_videos_rss_feed($rssinfo)
{
	if (!pnModAvailable('crpVideo'))
	{
		return;
	}

	// Security check
	if (!SecurityUtil :: checkPermission('crpVideo::', '::', ACCESS_READ))
	{
		return LogUtil :: registerPermissionError();
	}

	// get the current language
	$currentlang = pnUserGetLang();

	// Break out options from our content field
	$apiargs['startnum'] = 1;
	$apiargs['active'] = 'A';
	$apiargs['numitems'] = '10';
	$apiargs['orderBy'] = 'cr_date';
	$apiargs['sortOrder'] = 'DESC';

	if (pnModGetVar('crpVideo','enablecategorization'))
	{
		// load the category registry util
		if (!($class= Loader :: loadClass('CategoryRegistryUtil')))
			pn_exit('Unable to load class [CategoryRegistryUtil] ...');
		if (!($class= Loader :: loadClass('CategoryUtil')))
			pn_exit('Unable to load class [CategoryUtil] ...');
		
		$mainCat= CategoryRegistryUtil :: getRegisteredModuleCategory('crpVideo', 'crpvideos', 'Main', '/__SYSTEM__/Modules/crpVideo');
		$category= (int) FormUtil :: getPassedValue('videos_category', null, 'GET');
		if ($category)
		{
			$apiargs['mainCat'] = $mainCat;
			$apiargs['category'] = $category;
		}
	}
	
	// call the api
	$items = pnModAPIFunc('crpVideo', 'user', 'getall', $apiargs);

	//
	$list = array ();
	foreach ($items as $item)
	{
		$list[] = array (
			'title' => $item['title'],
			'link' => pnModURL('crpVideo',
			'user',
			'display',
			array (
				'videoid' => $item['videoid']
			)
		), 'descr' => $item['content'], 'author' => $item['author'], 'hits' => $item['hits'], 'publ_date' => $item['cr_date'], 'author_uid' => $item['cr_uid']);
	}

	//
	return $list;
}
?>