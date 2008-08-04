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

function crpVideo_ajax_getCategorizedVideo()
{
	if (!SecurityUtil::checkPermission('crpVideo::Video', '::', ACCESS_READ))
	{
		AjaxUtil :: error(pnVarPrepHTMLDisplay(_MODULENOAUTH));
	}

	pnModLangLoad('crpVideo', 'admin');
	
	// get all module vars
	$modvars = pnModGetVar('crpVideo');
	
	// load the category registry util
	if (!($class = Loader :: loadClass('CategoryRegistryUtil')))
		pn_exit('Unable to load class [CategoryRegistryUtil] ...');
	if (!($class = Loader :: loadClass('CategoryUtil')))
		pn_exit('Unable to load class [CategoryUtil] ...');

	$category = DataUtil::convertFromUTF8(FormUtil::getPassedValue('category', null, 'GET'));
	$startnum = '1';
	$mainCat = CategoryRegistryUtil :: getRegisteredModuleCategory('crpVideo', 'crpvideos', 'Main', '/__SYSTEM__/Modules/crpVideo');
	$ignoreml = true;
	$sortOrder = 'DESC';
	$data = compact('startnum', 'category', 'clear', 'ignoreml', 'mainCat', 'cats', 'modvars', 'sortOrder');
	
	$videos = pnModAPIFunc('crpVideo', 'user', 'getall_formlist', $data);
	
	$resultlist = DataUtil::convertFromUTF8($videos);
	return $resultlist;
}

function crpVideo_ajax_toggleStatus()
{
	if (!SecurityUtil::checkPermission('crpVideo::', '::', ACCESS_ADD))
	{
		AjaxUtil :: error(pnVarPrepHTMLDisplay(_MODULENOAUTH));
	}

	$videoid = FormUtil::getPassedValue('videoid', null, 'GET');
	$status = FormUtil::getPassedValue('status', -null, 'GET');
	
	pnModAPIFunc('crpVideo', 'admin', 'change_status', array('videoid' => $videoid, 'status' => $status));
	//($status=='A')?$status='P':$status='A';
	
	return array('videoid' => $videoid, 'status' => $status);
}


?>
