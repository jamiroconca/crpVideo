<?php
/**
 * crpVideo
 *
 * @copyright (c) 2007, Daniele Conca
 * @link http://noc.postnuke.com/projects/crpvideo Support and documentation
 * @author Daniele Conca <conca dot daniele at gmail dot com>
 * @license GNU/GPL - v.2.1
 * @package crpVideo
 */
 
/**
 * Draws category navigation 
 * 
 * Example
 * <!--[breadcrumbcategorymenu modname="crpVideo" tablename ="crpvideos" property="Main" func="view" category=$category lang=$lang separator="<img src='images/icons/extrasmall/next.gif'/>"]-->
 * 
 * @return string html output
 */
 
function smarty_function_breadcrumbcategorymenu($params, &$smarty)
{
	$modname	= isset($params['modname']) ? $params['modname'] : null;
	$type		= isset($params['type']) ? $params['type'] : 'user';
	$property	= isset($params['property']) ? $params['property'] : 'Main';
	$tablename	= isset($params['tablename']) ? $params['tablename'] : null;
	$func		= isset($params['func']) ? $params['func'] : 'view';
	$category	= isset($params['category']) ? $params['category'] : null;
	$lang		= isset($params['lang']) ? $params['lang'] : pnUserGetLang();
	$separator	= isset($params['separator']) ? $params['separator'] : '&raquo;';

	unset($params);
	
	if (!$tablename || !$modname || !is_array($category))
		return false;
		
	$parents = pnModAPIFunc($modname, 'user', 'breadcrumblinks', array('module'=>$modname,
																		'cat'=>$category,
																		'property'=>$property,
																		'tablename'=>$tablename));
	
	$categorylinks = '<ul class="pages-bread">';
	foreach ($parents as $kParent => $vParent)
	{
		if ($vParent['parent_id'])
			$categorylinks .= '<li>'.$separator.' <a href="'.DataUtil::formatForDisplayHTML(pnModURL($modname, $type, $func, array('cat' => $kParent))).'" title="'.$vParent['display_desc'][$lang].'">'. $vParent['display_name'][$lang].'</a></li>';
		
	}	
	$categorylinks .= '<li>'.$separator.' <a href="'.DataUtil::formatForDisplayHTML(pnModURL($modname, $type, $func, array('cat' => $category['id']))).'" title="'.$category['display_desc'][$lang].'">'. $category['display_name'][$lang].'</a></li>';
	$categorylinks .= '</ul>';
	   
	return $categorylinks;
}

?>