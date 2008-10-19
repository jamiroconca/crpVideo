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

$modversion['name'] = _CRPVIDEO_NAME;
$modversion['displayname'] = _CRPVIDEO_DISPLAYNAME;
$modversion['description'] = _CRPVIDEO_DESCRIPTION;
$modversion['version'] = '0.1.9';
$modversion['credits'] = 'pndocs/credits.txt';
$modversion['help'] = 'pndocs/install.txt';
$modversion['changelog'] = 'pndocs/changelog.txt';
$modversion['license'] = 'pndocs/license.txt';
$modversion['official'] = 1;
$modversion['author'] = 'Daniele Conca - jami';
$modversion['contact'] = 'conca.daniele@gmail.com';
$modversion['securityschema'] = array ('crpVideo::Video' => 'AuthorID:VideoTitle:VideoID');

?>