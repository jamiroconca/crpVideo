<?php

/**
 * crpVideo
 *
 * @copyright (c) 2007, Daniele Conca
 * @link http://noc.postnuke.com/projects/crpvideo Support and documentation
 * @version 0.1.1
 * @author Daniele Conca <jami at cremonapalloza dot org>
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package crpVideo
 */

/**
 * Search plugin info
 **/
function crpVideo_searchapi_info()
{
	return array ('title' => 'crpVideo',
								'functions' => array ('crpVideo' => 'search'));
}

/**
 * Search form component
 **/
function crpVideo_searchapi_options($args)
{
	if (SecurityUtil :: checkPermission('crpVideo::', '::', ACCESS_READ))
	{
		// Create output object - this object will store all of our output so that
		// we can return it easily when required
		$pnRender= pnRender :: getInstance('crpVideo');
		$pnRender->assign('active', (isset ($args['active']) && isset ($args['active']['crpVideo'])) || (!isset ($args['active'])));
		return $pnRender->fetch('crpvideo_search_options.htm');
	}
	return '';

}

/**
 * Search plugin main function
 **/
function crpVideo_searchapi_search($args)
{
	pnModDBInfoLoad('Search');
	$pntable= pnDBGetTables();
	$videostable= $pntable['crpvideos'];
	$videoscolumn= $pntable['crpvideos_column'];
	$searchTable= $pntable['search_result'];
	$searchColumn= $pntable['search_result_column'];

	$where= search_construct_where($args, array (
		$videoscolumn['title'],
		$videoscolumn['content'],
		$videoscolumn['tags']
	), null);

	$sessionId= session_id();

	$sql= "
	SELECT 
	   $videoscolumn[title] as title,
	   $videoscolumn[content] as text,
	   $videoscolumn[videoid] as id,
	   $videoscolumn[cr_date] as date
	FROM $videostable
	WHERE $where";

	$result= DBUtil :: executeSQL($sql);
	if (!$result)
	{
		return LogUtil :: registerError(_GETFAILED);
	}

	$insertSql= "INSERT INTO $searchTable
	  ($searchColumn[title],
	   $searchColumn[text],
	   $searchColumn[extra],
	   $searchColumn[created],
	   $searchColumn[module],
	   $searchColumn[session])
	VALUES ";

	// Process the result set and insert into search result table
	for (; !$result->EOF; $result->MoveNext())
	{
		$item= $result->GetRowAssoc(2);
		if (SecurityUtil :: checkPermission('crpVideo::Video', "$item[title]::$item[id]", ACCESS_READ))
		{
			$sql= $insertSql . '(' . '\'' . DataUtil :: formatForStore($item['title']) . '\', ' . '\'' . DataUtil :: formatForStore($item['text']) . '\', ' . '\'' . DataUtil :: formatForStore($item['id']) . '\', ' . '\'' . DataUtil :: formatForStore($item['date']) . '\', ' . '\'' . 'crpVideo' . '\', ' . '\'' . DataUtil :: formatForStore($sessionId) . '\')';
			$insertResult= DBUtil :: executeSQL($sql);
			if (!$insertResult)
			{
				return LogUtil :: registerError(_GETFAILED);
			}
		}
	}

	return true;
}

/**
 * Do last minute access checking and assign URL to items
 *
 * Access checking is ignored since access check has
 * already been done. But we do add a URL to the found item
 */
function crpVideo_searchapi_search_check(& $args)
{
	$datarow= & $args['datarow'];
	$videoId= $datarow['extra'];

	$datarow['url']= pnModUrl('crpVideo', 'user', 'display', array (
		'videoid' => $videoId
	));

	return true;
}