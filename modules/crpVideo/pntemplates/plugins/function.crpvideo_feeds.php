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

/**
 * Smarty function to add rss link into header
 *
 * Example
 * <!--[crpvideo_feeds]-->
 *
 * @return void
 */
function smarty_function_crpvideo_feeds($params, & $smarty)
{
	// Security check
	if (!SecurityUtil :: checkPermission('crpVideo::', '::', ACCESS_READ))
	{
		return LogUtil :: registerPermissionError();
	}

	if (pnModGetVar('crpVideo', 'enablecategorization') && pnModGetVar('crpVideo', 'crpvideo_enable_rss'))
	{
		// load the category registry util
		if (!($class = Loader :: loadClass('CategoryRegistryUtil')))
			pn_exit('Unable to load class [CategoryRegistryUtil] ...');
		if (!($class = Loader :: loadClass('CategoryUtil')))
			pn_exit('Unable to load class [CategoryUtil] ...');

		$mainCat = CategoryRegistryUtil :: getRegisteredModuleCategory('crpVideo', 'crpvideos', 'Main', '/__SYSTEM__/Modules/crpVideo');
		$cats = CategoryUtil :: getCategoriesByParentID($mainCat);
		$userLang = pnUserGetLang();

		foreach ($cats as $cat)
		{
			PageUtil :: addVar('rawtext', '<link rel="alternate" type="application/rss+xml" href="' . DataUtil :: formatForDisplay(pnModUrl('crpVideo', 'user', 'getfeed', array (
				'videos_category' => $cat['id']
			))) . '" title="' . _CRPVIDEO_RSS . ' ' . $cat['display_name'][$userLang] . '" />');
		}
	}

	return;
}
?>
