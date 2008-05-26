<?php
/**
 * crpVideo
 *
 * @copyright (c) 2007, Daniele Conca
 * @link http://noc.postnuke.com/projects/crpvideo Support and documentation
 * @author Daniele Conca <jami at cremonapalloza dot org>
 * @license GNU/GPL - v.2
 * @package crpVideo
 */

/**
 * Return an array of items to show in the your account panel
 *
 * @return   array   array of items, or false on failure
 */
function crpVideo_accountapi_getall($args)
{
    if (!isset($args['uname'])) {
        if (!pnUserloggedIn()) {
            $uname = null;
        } else {
            $uname = pnUserGetVar('uname');
        }
    }		
		
	// Security check
	if (!SecurityUtil::checkPermission('crpVideo::', '::', ACCESS_COMMENT))
		$uname = null;
	  
  // Create an array of links to return
  if ($uname != null) 
  {
  	pnModLangLoad('crpVideo');
    $items = array(array('url'     => pnModURL('crpVideo', 'user', 'new'),
                         'module'  => 'crpVideo',
                         'set'     => 'pnimages',
                         'title'   => _CRPVIDEO_SUBMIT,
                         'icon'    => 'admin.gif'));
  } 
  else 
  	$items = null;

  // Return the items
  return $items;
}
