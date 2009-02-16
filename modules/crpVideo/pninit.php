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

function crpVideo_init()
{
	// create table
	if (!DBUtil :: createTable('crpvideos'))
	{
		return false;
	}

	if (!DBUtil :: createTable('crpvideo_covers'))
	{
		return false;
	}

	// Create the index
	if (!DBUtil :: createIndex('video_image', 'crpvideo_covers', array (
			'videoid',
			'document_type'
		), array (
			'UNIQUE' => '1'
		)))
		return false;

	// create our default category
	if (!_crpVideo_createdefaultcategory())
	{
		return LogUtil :: registerError(_CREATEFAILED);
	}

	// Set default pages per page
	pnModSetVar('crpVideo', 'itemsperpage', 25);
	pnModSetVar('crpVideo', 'enablecategorization', true);
	pnModSetVar('crpVideo', 'cover_dimension', 35000);
	pnModSetVar('crpVideo', 'image_width', 100);
	pnModSetVar('crpVideo', 'playerwidth', 400);
	pnModSetVar('crpVideo', 'playerheight', 340);
	pnModSetVar('crpVideo', 'displayheight', 300);
	pnModSetVar('crpVideo', 'file_dimension', 5000000);
	pnModSetVar('crpVideo', 'upload_path', 'modules/crpVideo/pnmedia/videos');
	pnModSetVar('crpVideo', 'crpvideo_use_gd', false);
	pnModSetVar('crpVideo', 'crpvideo_userlist_image', false);
	pnModSetVar('crpVideo', 'userlist_width', '64');
	pnModSetVar('crpVideo', 'display_embed', false);
	pnModSetVar('crpVideo', 'mandatory_cover', false);
	pnModSetVar('crpVideo', 'main_items', 3);
	pnModSetVar('crpVideo', 'crpvideo_notification', null);
	pnModSetVar('crpVideo', 'crpvideo_enable_rss', true);
	pnModSetVar('crpVideo', 'crpvideo_show_rss', true);
	pnModSetVar('crpVideo', 'crpvideo_rss', 'rss2');
	pnModSetVar('crpVideo', 'crpvideo_enable_podcast', false);
	pnModSetVar('crpVideo', 'crpvideo_podcast_category', null);
	pnModSetVar('crpVideo', 'crpvideo_podcast_description', null);
	pnModSetVar('crpVideo', 'crpvideo_podcast_editor', null);
	pnModSetVar('crpVideo', 'crpvideo_podcast_icategory', null);
	pnModSetVar('crpVideo', 'crpvideo_enable_playlist', false);
	pnModSetVar('crpVideo', 'crpvideo_playlist_type', null);
	pnModSetVar('crpVideo', 'crpvideo_playlist_position', null);
	pnModSetVar('crpVideo', 'crpvideo_playlist_size', 180);
	pnModSetVar('crpVideo', 'crpvideo_playlist_items', 5);

	// Initialisation successful
	return true;
}

function crpVideo_upgrade($oldversion)
{
	$tables= pnDBGetTables();
	switch ($oldversion)
	{
		case "0.1.0" :
			pnModSetVar('crpVideo', 'cover_dimension', '35000');
			pnModSetVar('crpVideo', 'image_width', '100');
			pnModSetVar('crpVideo', 'playerwidth', 400);
			pnModSetVar('crpVideo', 'playerheight', 340);
			pnModSetVar('crpVideo', 'displayheight', 300);

			if (!DBUtil :: createTable('crpvideo_covers'))
			{
				return LogUtil :: registerError(_UPDATETABLEFAILED);
			}

			if (!DBUtil :: createIndex('video_image', 'crpvideo_covers', array (
					'videoid',
					'document_type'
				), array (
					'UNIQUE' => '1'
				)))
				LogUtil :: registerError(_UPDATETABLEFAILED);
			return crpVideo_upgrade("0.1.1");
		case "0.1.1" :
			pnModSetVar('crpVideo', 'upload_path', 'modules/crpVideo/pnmedia/videos');
			pnModSetVar('crpVideo', 'file_dimension', 5000000);
			pnModSetVar('crpVideo', 'crpvideo_use_gd', false);
			pnModSetVar('crpVideo', 'crpvideo_userlist_image', false);
			pnModSetVar('crpVideo', 'userlist_width', '64');

			$sql= "ALTER TABLE $tables[crpvideos] ADD pn_pathvideo VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER pn_urlvideo";
			if (!DBUtil :: executeSQL($sql))
				return LogUtil :: registerError(_UPDATETABLEFAILED);

			return crpVideo_upgrade("0.1.2");
			break;
		case "0.1.2" :
			$sql= "ALTER TABLE $tables[crpvideos] ADD pn_tags VARCHAR( 48 ) NOT NULL DEFAULT '' AFTER pn_author";
			if (!DBUtil :: executeSQL($sql))
				return LogUtil :: registerError(_UPDATETABLEFAILED);

			pnModSetVar('crpVideo', 'display_embed', false);
			return crpVideo_upgrade("0.1.3");
			break;
		case "0.1.3" :
			$sql= "ALTER TABLE $tables[crpvideos] ADD pn_source VARCHAR( 32 ) NOT NULL DEFAULT '' AFTER pn_urltitle";
			if (!DBUtil :: executeSQL($sql))
				return LogUtil :: registerError(_UPDATETABLEFAILED);

			$sql= "UPDATE $tables[crpvideos] SET pn_source='video' WHERE pn_urlvideo!=''";
			if (!DBUtil :: executeSQL($sql))
				return LogUtil :: registerError(_UPDATETABLEFAILED);

			$sql= "ALTER TABLE $tables[crpvideos] ADD pn_external TEXT NOT NULL DEFAULT '' AFTER pn_urlvideo";
			if (!DBUtil :: executeSQL($sql))
				return LogUtil :: registerError(_UPDATETABLEFAILED);

			pnModSetVar('crpVideo', 'mandatory_cover', false);

			return crpVideo_upgrade("0.1.4");
			break;
		case "0.1.4" :
			pnModSetVar('crpVideo', 'main_items', 3);

			return crpVideo_upgrade("0.1.5");
			break;
		case "0.1.5" :
			pnModSetVar('crpVideo', 'crpvideo_notification', null);

			return crpVideo_upgrade("0.1.6");
			break;
		case "0.1.6" :
			pnModSetVar('crpVideo', 'crpvideo_enable_rss', true);
			pnModSetVar('crpVideo', 'crpvideo_show_rss', true);
			pnModSetVar('crpVideo', 'crpvideo_rss', 'rss2');
			return crpVideo_upgrade("0.1.7");
			break;
		case "0.1.7" :
			pnModSetVar('crpVideo', 'crpvideo_enable_podcast', false);
			pnModSetVar('crpVideo', 'crpvideo_podcast_category', null);
			pnModSetVar('crpVideo', 'crpvideo_podcast_description', null);
			return crpVideo_upgrade("0.1.8");
			break;
		case "0.1.8" :
			pnModSetVar('crpVideo', 'crpvideo_podcast_editor', null);
			pnModSetVar('crpVideo', 'crpvideo_podcast_icategory', null);
			return crpVideo_upgrade("0.1.9");
			break;
		case "0.1.9" :
			pnModSetVar('crpVideo', 'crpvideo_enable_playlist', false);
			pnModSetVar('crpVideo', 'crpvideo_playlist_type', null);
			pnModSetVar('crpVideo', 'crpvideo_playlist_position', null);
			pnModSetVar('crpVideo', 'crpvideo_playlist_size', 180);
			pnModSetVar('crpVideo', 'crpvideo_playlist_items', 5);
			return crpVideo_upgrade("0.2.0");
			break;
		case "0.2.0" :
			pnModDelVar('crpVideo', 'addcategorytitletopermalink');
			return crpVideo_upgrade("0.2.1");
			break;
		case "0.2.1" :
			break;
	}
	// Update successful
	return true;
}

function crpVideo_delete()
{
	// drop table
	if (!DBUtil :: dropTable('crpvideos'))
	{
		return false;
	}

	if (!DBUtil :: dropTable('crpvideo_covers'))
	{
		return false;
	}

	// Delete any module variables
	pnModDelVar('crpVideo');

	// Deletion successful
	return true;
}

function _crpVideo_createdefaultcategory()
{
	// load necessary classes
	Loader :: loadClass('CategoryUtil');
	Loader :: loadClassFromModule('Categories', 'Category');
	Loader :: loadClassFromModule('Categories', 'CategoryRegistry');

	// get the language file
	$lang= pnUserGetLang();

	// get the category path for which we're going to insert our place holder category
	$rootcat= CategoryUtil :: getCategoryByPath('/__SYSTEM__/Modules');

	// create placeholder for all our migrated categories
	$cat= new PNCategory();
	$cat->setDataField('parent_id', $rootcat['id']);
	$cat->setDataField('name', 'crpVideo');
	$cat->setDataField('value', '-1');

	$cat->setDataField('display_name', array (
		$lang => _CRPVIDEO_NAME
	));
	$cat->setDataField('display_desc', array (
		$lang => _CRPVIDEO_CATEGORY_DESCRIPTION
	));
	$cat->setDataField('security_domain', $rootcat['security_domain']);

	if (!$cat->validate('admin'))
	{
		return false;
	}

	$cat->insert();
	$cat->update();

	// get the category path for which we're going to insert our upgraded categories
	$rootcat= CategoryUtil :: getCategoryByPath('/__SYSTEM__/Modules/crpVideo');

	// create an entry in the categories registry
	$registry= new PNCategoryRegistry();
	$registry->setDataField('modname', 'crpVideo');
	$registry->setDataField('table', 'crpvideos');
	$registry->setDataField('property', 'Main');
	$registry->setDataField('category_id', $rootcat['id']);
	$registry->insert();

	return true;
}