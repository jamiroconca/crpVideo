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
