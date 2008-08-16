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
function crpVideo_topuploadersblock_init()
{
	// Security
	pnSecAddSchema('Topuploadersblock::', 'Block title::');
}

/**
 * get information on block
 *
 */
function crpVideo_topuploadersblock_info()
{
	return array (
		'text_type' => 'Top uploaders',
		'module' => 'crpVideo',
		'text_type_long' => 'Top uploaders',
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
function crpVideo_topuploadersblock_display($blockinfo)
{
	// security check
	if (!SecurityUtil :: checkPermission('topuploadersblock::', "$blockinfo[title]::", ACCESS_READ))
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
	$apiargs['orderBy'] = 'counter';
	$apiargs['sortOrder'] = 'DESC';
	$apiargs['interval'] = $vars['interval'];
	$apiargs['uploaders'] = true;

	// call the api
	$items = pnModAPIFunc('crpVideo', 'user', 'get_uploaders', $apiargs);

	// check for an empty return
	if (empty ($items))
		return;

	// create the output object
	$pnRender = pnRender :: getInstance('crpVideo', false);

	$pnRender->assign('uploaders', $items);
	$pnRender->assign($modvars);

	$blockinfo['content'] = $pnRender->fetch('blocks/crpvideo_block_topuploaders.htm');

	return pnBlockThemeBlock($blockinfo);
}

/**
 * modify block settings
 *
 * @param        array       $blockinfo     a blockinfo structure
 * @return       output      the bock form
 */
function crpVideo_topuploadersblock_modify($blockinfo)
{
	// Break out options from our content field
	$vars = pnBlockVarsFromContent($blockinfo['content']);

	// Defaults
	if (!isset ($vars['numitems']))
		$vars['numitems'] = 5;
	if (!isset ($vars['interval']))
		$vars['interval'] = null;

	// Create output object
	$pnRender = pnRender :: getInstance('crpVideo', false);

	// assign the block vars
	$pnRender->assign($vars);

	// Return the output that has been generated by this function
	return $pnRender->fetch('blocks/crpvideo_block_topuploaders_modify.htm');

}

/**
 * update block settings
 *
 * @param        array       $blockinfo     a blockinfo structure
 * @return       $blockinfo  the modified blockinfo structure
 */
function crpVideo_topuploadersblock_update($blockinfo)
{
	// Get current content
	$vars = pnBlockVarsFromContent($blockinfo['content']);

	// alter the corresponding variable
	$vars['numitems'] = (int) FormUtil :: getPassedValue('numitems', null, 'POST');
	$vars['interval'] = (int) FormUtil :: getPassedValue('interval', null, 'POST');

	// write back the new contents
	$blockinfo['content'] = pnBlockVarsToContent($vars);

	// clear the block cache
	$pnRender = pnRender :: getInstance('crpVideo');
	$pnRender->clear_cache('blocks/crpvideo_block_topuploaders.htm');

	return $blockinfo;
}
?>