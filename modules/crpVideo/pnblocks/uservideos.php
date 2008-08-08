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
 * initialise block
 *
 */
function crpVideo_uservideosblock_init()
{
	// Security
	pnSecAddSchema('userVideosblock::', 'Block title::');
}

/**
 * get information on block
 * 
 */
function crpVideo_uservideosblock_info()
{
	return array (
		'text_type' => 'user crpVideos',
		'module' => 'crpVideo',
		'text_type_long' => 'Videos Titles from a user',
		'allow_multiple' => true,
		'form_content' => false,
		'form_refresh' => false,
		'show_preview' => true
	);
}

/**
 * display block
 *
 * @param        array       $blockinfo     a blockinfo structure
 * @return       output      the rendered bock
 */
function crpVideo_uservideosblock_display($blockinfo)
{
	// security check
	if (!SecurityUtil :: checkPermission('userVideosblock::', "$blockinfo[title]::", ACCESS_READ))
		return;

	if (!pnModAvailable('crpVideo'))
		return;

	// get the current language
	$currentlang = pnUserGetLang();

	// Break out options from our content field
	$vars = pnBlockVarsFromContent($blockinfo['content']);
	// get all module vars for later use
	$modvars = pnModGetVar('crpVideo');

	if (!isset ($vars['numitems']))
		$vars['numitems'] = 5;

	$apiargs['startnum'] = 1;
	$apiargs['active'] = 'A';
	$apiargs['numitems'] = $vars['numitems'];
	$apiargs['orderBy'] = 'cr_date';
	$apiargs['sortOrder'] = 'DESC';
	$apiargs['uid'] = $vars['from_uid'];

	// call the api
	$items = pnModAPIFunc('crpVideo', 'user', 'getall', $apiargs);

	// check for an empty return
	if (empty ($items))
		return;

	// create the output object
	$pnRender = pnRender :: getInstance('crpVideo', false);

	$pnRender->assign('videos', $items);
	$pnRender->assign($modvars);

	if ($vars['ajaxblock']=='carousel')
	{
		$pnRender->assign('direction', $vars['carousel_direction']);
		$blockinfo['content'] = $pnRender->fetch('blocks/crpvideo_block_videos_carousel.htm');
	}
	elseif ($vars['ajaxblock']=='protoflow')
	{
		$blockinfo['content'] = $pnRender->fetch('blocks/crpvideo_block_videos_protoflow.htm');
	}
	else
		$blockinfo['content'] = $pnRender->fetch('blocks/crpvideo_block_videos.htm');
		
	return pnBlockThemeBlock($blockinfo);
}

/**
 * modify block settings
 *
 * @param        array       $blockinfo     a blockinfo structure
 * @return       output      the bock form
 */
function crpVideo_uservideosblock_modify($blockinfo)
{
	// Break out options from our content field
	$vars = pnBlockVarsFromContent($blockinfo['content']);

	// Defaults
	if (!isset ($vars['numitems']))
		$vars['numitems'] = 5;
	if (!isset ($vars['ajaxblock']))
	{
		$carousel = false;
		$protoflow = false;
	}
	elseif ($vars['ajaxblock']=='carousel') $carousel = true;
	elseif ($vars['ajaxblock']=='protoflow') $protoflow = true;
	
	if (isset ($carousel) && !isset ($vars['carousel_direction']))
		$vars['carousel_direction'] = 'horizontal';
	elseif (!isset ($vars['carousel_direction']))
		$vars['carousel_direction'] = null;
			
	// Create output object
	$pnRender = pnRender :: getInstance('crpVideo', false);

	// assign the block vars
	$pnRender->assign($vars);

	// Return the output that has been generated by this function
	return $pnRender->fetch('blocks/crpvideo_block_uservideos_modify.htm');

}

/**
 * update block settings
 *
 * @param        array       $blockinfo     a blockinfo structure
 * @return       $blockinfo  the modified blockinfo structure
 */
function crpVideo_uservideosblock_update($blockinfo)
{
	// Get current content
	$vars = pnBlockVarsFromContent($blockinfo['content']);

	// alter the corresponding variable
	$vars['numitems'] = (int) FormUtil :: getPassedValue('numitems', null, 'POST');
	$vars['from_uid'] = (int) FormUtil :: getPassedValue('from_uid', null, 'POST');
	$vars['ajaxblock'] = FormUtil :: getPassedValue('ajaxblock', false, 'POST');
	$vars['carousel_direction'] = FormUtil :: getPassedValue('carousel_direction', null, 'POST');
	($vars['ajaxblock']=='carousel' && !$vars['carousel_direction'])?$vars['carousel_direction']='horizontal':'';
	
	// write back the new contents
	$blockinfo['content'] = pnBlockVarsToContent($vars);

	// clear the block cache
	$pnRender = pnRender :: getInstance('crpVideo');
	$pnRender->clear_cache('blocks/crpvideo_block_videos.htm');
	$pnRender->clear_cache('blocks/crpvideo_block_videos_carousel.htm');
	$pnRender->clear_cache('blocks/crpvideo_block_videos_protoflow.htm');

	return $blockinfo;
}
?>