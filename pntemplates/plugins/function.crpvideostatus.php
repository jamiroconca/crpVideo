<?php
/**
 * crpVideo
 *
 * @copyright (c) 2007, Daniele Conca
 * @link http://noc.postnuke.com/projects/crpcalendar Support and documentation
 * @author Daniele Conca <jami at cremonapalloza dot org>
 * @license GNU/GPL - v.2.1
 * @package crpVideo
 */
 
/**
 * Smarty function to display status of a video
 *
 * Example
 * <!--[crpvideostatus status="$status_flag" ]-->
 * 
 * @param array $params All attributes passed to this function from the template
 * @param object &$smarty Reference to the Smarty object
 * @param int $status status flag
 * @param int videoid item_identifier
 * 
 * @return string the results of the module function
 */
function smarty_function_crpvideostatus($params, &$smarty)
{
	// Security check
	if (!SecurityUtil::checkPermission('crpVideo::', '::', ACCESS_EDIT))
	{
		return LogUtil::registerPermissionError();
	}

  $statusimage = '';
	
	if (!$params['fake'])
	{
		if (SecurityUtil::checkPermission('crpVideo::', '::', ACCESS_ADD) && ($params['status']=='A' || $params['status']=='P'))
	  	$statusimage .= '<a href="'.pnModUrl('crpVideo','admin','change_status', array('videoid' => $params['videoid'], 'obj_status'=>$params['status'])).'" title="'._CRPVIDEO_CHANGE_STATUS.'">'."\n";
		else
			$statusimage .= '';
	
		if ($params['status']=='A')
	    $statusimage .= '<img id="videostatus_'.$params['videoid'].'" src="modules/crpVideo/pnimages/green_dot.gif" alt="'._ACTIVE.'" title="'._CRPVIDEO_CHANGE_STATUS.'"/>'."\n</a>\n";
	  elseif ($params['status']=='P')
	    $statusimage .= '<img id="videostatus_'.$params['videoid'].'" src="modules/crpVideo/pnimages/yellow_dot.gif" alt="'._CRPVIDEO_PENDING.'" title="'._CRPVIDEO_CHANGE_STATUS.'"/>'."\n</a>\n";
		else
	    $statusimage .= '<img id="videostatus_'.$params['videoid'].'" src="modules/crpVideo/pnimages/red_dot.gif" alt="'._CRPVIDEO_REJECTED.'" title="'._CRPVIDEO_CHANGE_STATUS_MODIFYING.'" />'."\n";
	}
	else
	{
		if (SecurityUtil::checkPermission('crpVideo::', '::', ACCESS_ADD) && ($params['status']=='A' || $params['status']=='P'))
		{
			$statusimage .= "<a href='javascript:void(0);'>";
	    $statusimage .= '<img id="videostatus_fake_A_'.$params['videoid'].'" ';
	    $statusimage .= ($params['status']=='P')?' style="display:none" ':'';
			$statusimage .= '" onclick="togglestatus(\''.$params['videoid'].'\',\'A\')" src="modules/crpVideo/pnimages/green_dot.gif" alt="'._ACTIVE.'" title="'._CRPVIDEO_CHANGE_STATUS.'"/>'."\n";
	    $statusimage .= '<img id="videostatus_fake_P_'.$params['videoid'].'"';
	    $statusimage .= ($params['status']=='A')?' style="display:none" ':'';
			$statusimage .= 'onclick="togglestatus(\''.$params['videoid'].'\',\'P\')" src="modules/crpVideo/pnimages/yellow_dot.gif" alt="'._CRPVIDEO_PENDING.'" title="'._CRPVIDEO_CHANGE_STATUS.'"/>'."\n";
			$statusimage .= "</a>";
		}
		elseif ($params['status']=='A')
	    $statusimage .= '<img id="videostatus_'.$params['videoid'].'" src="modules/crpVideo/pnimages/green_dot.gif" alt="'._ACTIVE.'" title="'._CRPCALENDAR_CHANGE_STATUS.'"/>'."\n";
	  elseif ($params['status']=='P')
	    $statusimage .= '<img id="videostatus_'.$params['videoid'].'" src="modules/crpVideo/pnimages/yellow_dot.gif" alt="'._CRPVIDEO_PENDING.'" title="'._CRPVIDEO_CHANGE_STATUS.'"/>'."\n";
		else
	    $statusimage .= '<img id="videostatus_'.$params['videoid'].'" src="modules/crpVideo/pnimages/red_dot.gif" alt="'._CRPVIDEO_REJECTED.'" title="'._CRPVIDEO_CHANGE_STATUS_MODIFYING.'" />'."\n";
	}
	  
  return $statusimage;
}
?>
