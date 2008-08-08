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
 * Smarty modifier calculate difference for a video date from now
 *
 * Example
 * 
 *   <!--[$myvar|videodatediff]-->
 */
function smarty_modifier_videodatediff($date)
{
    $diffArray = DateUtil :: getDatetimeDiff ($date, date('Y-m-d H:i:s'));

		if ($diffArray['d']<1)
			$datediff = "<div>"._CRPVIDEO_ADDED_DIFF." <strong>$diffArray[h] "._CRPVIDEO_HOURS." $diffArray[m] "._MINUTES."</strong>  "._CRPVIDEO_AGO."</div>";
		else if ($diffArray['d']==1)
			$datediff = "<div>"._CRPVIDEO_ADDED_DIFF." <strong>"._CRPVIDEO_YESTERDAY."</strong></div>";
		else
			$datediff = "";
			
    return $datediff;
}
