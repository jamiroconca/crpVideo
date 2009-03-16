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

Loader :: includeOnce('modules/crpVideo/pnclass/crpVideoUI.php');
Loader :: includeOnce('modules/crpVideo/pnclass/crpVideoDAO.php');

/**
 * crpVideo Object
 */
class crpVideo
{

	function crpVideo()
	{
		$this->ui= new crpVideoUI();
		$this->dao= new crpVideoDAO();

		(function_exists('gd_info')) ? $this->gd= gd_info() : $this->gd= array ();
	}

	function userDisplay()
	{
		$videoid= FormUtil :: getPassedValue('videoid', isset ($args['videoid']) ? $args['videoid'] : null, 'REQUEST');
		$title= FormUtil :: getPassedValue('title', isset ($args['title']) ? $args['title'] : null, 'REQUEST');
		$objectid= FormUtil :: getPassedValue('objectid', isset ($args['objectid']) ? $args['objectid'] : null, 'REQUEST');
		if (!empty ($objectid))
		{
			$videoid= $objectid;
		}

		// Get the page
		if (isset ($videoid) && is_numeric($videoid))
		{
			$item= pnModAPIFunc('crpVideo', 'user', 'get', array (
				'videoid' => $videoid
			));
		}
		else
		{
			$item= pnModAPIFunc('crpVideo', 'user', 'get', array (
				'title' => $title
			));
			pnQueryStringSetVar('videoid', $item['videoid']);
		}

		// The return value of the function is checked here
		if ($item == false || ($item['obj_status'] == 'P' && !SecurityUtil :: checkPermission('crpVideo::', '::', ACCESS_EDIT)))
		{
			return LogUtil :: registerError(_NOSUCHITEM, 404);
		}

		return $this->ui->userDisplay($videoid, $title, $item);
	}

	/**
	 * update a cover
	 *
	 * @param int $videoid item identifier
	 * @param array $inputValues array of updated values
	 *
	 * @return string html
	 */
	function createCover($video= array ())
	{
		// Argument check
		if (!$this->dao->validateData($video))
			return false;

		$video['image']['videoid']= $video['videoid'];
		$video['image']['document_type']= 'image';
		$id_image= $this->dao->setFile($video['image']);
		if ($id_image == '-1')
			return false;

		return true;
	}

	/**
	 * Change item status
	 *
	 * @param int $eventid item identifier
	 * @param string $obj_status active or pending
	 *
	 * @return string html
	 */
	function changeStatus()
	{
		$videoid= FormUtil :: getPassedValue('videoid', null);
		$obj_status= FormUtil :: getPassedValue('obj_status', null);

		if ($obj_status == 'P' || $obj_status == 'A')
		{
			($obj_status == 'A') ? $obj_status= 'P' : $obj_status= 'A';
			if (!$this->dao->updateStatus($videoid, $obj_status))
				LogUtil :: registerError(_UPDATEFAILED);
			else
				LogUtil :: registerStatus(_UPDATESUCCEDED);
		}
		else
			LogUtil :: registerError(_UPDATEFAILED);

		return pnRedirect(pnModURL('crpVideo', 'admin', 'view'));
	}

	/**
	 * Modify module's configuration
	 */
	function modifyConfig()
	{
		// get all module vars
		$modvars= pnModGetVar('crpVideo');

		// load the category registry util
		if (!($class= Loader :: loadClass('CategoryRegistryUtil')))
			pn_exit('Unable to load class [CategoryRegistryUtil] ...');

		$mainCat= CategoryRegistryUtil :: getRegisteredModuleCategory('crpVideo', 'crpvideos', 'Main', '/__SYSTEM__/Modules/crpVideo');

		return $this->ui->modifyConfig($modvars, $this->gd, $mainCat);
	}

	/**
	 * Update module's configuration
	 */
	function updateConfig()
	{
		// Confirm authorisation code
		if (!SecurityUtil :: confirmAuthKey())
		{
			return LogUtil :: registerAuthidError(pnModURL('crpVideo', 'admin', 'view'));
		}

		// Update module variables
		$itemsperpage= (int) FormUtil :: getPassedValue('itemsperpage', 25, 'POST');
		if ($itemsperpage < 1)
		{
			$itemsperpage= 25;
		}
		pnModSetVar('crpVideo', 'itemsperpage', $itemsperpage);
		$enablecategorization= (bool) FormUtil :: getPassedValue('enablecategorization', false, 'POST');
		pnModSetVar('crpVideo', 'enablecategorization', $enablecategorization);
		$cover_dimension= (int) FormUtil :: getPassedValue('cover_dimension', 35000, 'POST');
		pnModSetVar('crpVideo', 'cover_dimension', $cover_dimension);
		$image_width= (int) FormUtil :: getPassedValue('image_width', 300, 'POST');
		pnModSetVar('crpVideo', 'image_width', $image_width);
		$playerwidth= (int) FormUtil :: getPassedValue('playerwidth', 400, 'POST');
		pnModSetVar('crpVideo', 'playerwidth', $playerwidth);
		$playerheight= (int) FormUtil :: getPassedValue('playerheight', 340, 'POST');
		pnModSetVar('crpVideo', 'playerheight', $playerheight);
		$displayheight= (int) FormUtil :: getPassedValue('displayheight', 300, 'POST');
		pnModSetVar('crpVideo', 'displayheight', $displayheight);
		$file_dimension= (int) FormUtil :: getPassedValue('file_dimension', 1500000, 'POST');
		pnModSetVar('crpVideo', 'file_dimension', $file_dimension);
		$upload_path= FormUtil :: getPassedValue('upload_path', null, 'POST');
		pnModSetVar('crpVideo', 'upload_path', $upload_path);
		$browser_path= FormUtil :: getPassedValue('browser_path', null, 'POST');
		pnModSetVar('crpVideo', 'browser_path', $browser_path);
		$crpvideo_use_gd= (bool) FormUtil :: getPassedValue('crpvideo_use_gd', false, 'POST');
		pnModSetVar('crpVideo', 'crpvideo_use_gd', $crpvideo_use_gd);
		$crpvideo_userlist_image= (bool) FormUtil :: getPassedValue('crpvideo_userlist_image', false, 'POST');
		pnModSetVar('crpVideo', 'crpvideo_userlist_image', $crpvideo_userlist_image);
		$userlist_width= (int) FormUtil :: getPassedValue('userlist_width', 32, 'POST');
		pnModSetVar('crpVideo', 'userlist_width', $userlist_width);
		$display_embed= (bool) FormUtil :: getPassedValue('display_embed', false, 'POST');
		pnModSetVar('crpVideo', 'display_embed', $display_embed);
		$mandatory_cover= (bool) FormUtil :: getPassedValue('mandatory_cover', false, 'POST');
		pnModSetVar('crpVideo', 'mandatory_cover', $mandatory_cover);
		$main_items= (int) FormUtil :: getPassedValue('main_items', 3, 'POST');
		pnModSetVar('crpVideo', 'main_items', $main_items);
		$crpvideo_notification= FormUtil :: getPassedValue('crpvideo_notification', null, 'POST');
		if ($crpvideo_notification && !pnVarValidate($crpvideo_notification, 'email'))
		{
			LogUtil :: registerError(_CRPVIDEO_INVALID_NOTIFICATION);
			return pnRedirect(pnModUrl('crpVideo', 'admin', 'modifyconfig'));
		}
		pnModSetVar('crpVideo', 'crpvideo_notification', $crpvideo_notification);
		// RSS
		$crpvideo_enable_rss= (bool) FormUtil :: getPassedValue('crpvideo_enable_rss', false, 'POST');
		pnModSetVar('crpVideo', 'crpvideo_enable_rss', $crpvideo_enable_rss);
		$crpvideo_show_rss= (bool) FormUtil :: getPassedValue('crpvideo_show_rss', false, 'POST');
		pnModSetVar('crpVideo', 'crpvideo_show_rss', $crpvideo_show_rss);
		$crpvideo_rss= (string) FormUtil :: getPassedValue('crpvideo_rss', false, 'POST');
		pnModSetVar('crpVideo', 'crpvideo_rss', $crpvideo_rss);
		// PODCAST
		$crpvideo_enable_podcast= (bool) FormUtil :: getPassedValue('crpvideo_enable_podcast', false, 'POST');
		pnModSetVar('crpVideo', 'crpvideo_enable_podcast', $crpvideo_enable_podcast);
		$crpvideo_podcast_category= FormUtil :: getPassedValue('crpvideo_podcast_category', null, 'POST');
		pnModSetVar('crpVideo', 'crpvideo_podcast_category', $crpvideo_podcast_category);
		$crpvideo_podcast_description= FormUtil :: getPassedValue('crpvideo_podcast_description', null, 'POST');
		pnModSetVar('crpVideo', 'crpvideo_podcast_description', $crpvideo_podcast_description);
		$crpvideo_podcast_editor= FormUtil :: getPassedValue('crpvideo_podcast_editor', null, 'POST');
		pnModSetVar('crpVideo', 'crpvideo_podcast_editor', $crpvideo_podcast_editor);
		$crpvideo_podcast_icategory= FormUtil :: getPassedValue('crpvideo_podcast_icategory', null, 'POST');
		pnModSetVar('crpVideo', 'crpvideo_podcast_icategory', $crpvideo_podcast_icategory);
		// PLAYLIST
		$crpvideo_enable_playlist= (bool) FormUtil :: getPassedValue('crpvideo_enable_playlist', false, 'POST');
		pnModSetVar('crpVideo', 'crpvideo_enable_playlist', $crpvideo_enable_playlist);
		$crpvideo_playlist_type= ($crpvideo_enable_playlist) ? FormUtil :: getPassedValue('crpvideo_playlist_type', null, 'POST') : null;
		pnModSetVar('crpVideo', 'crpvideo_playlist_type', $crpvideo_playlist_type);
		$crpvideo_playlist_position= ($crpvideo_enable_playlist) ? FormUtil :: getPassedValue('crpvideo_playlist_position', null, 'POST') : null;
		pnModSetVar('crpVideo', 'crpvideo_playlist_position', $crpvideo_playlist_position);
		$crpvideo_playlist_size= ($crpvideo_enable_playlist) ? FormUtil :: getPassedValue('crpvideo_playlist_size', 180, 'POST') : null;
		pnModSetVar('crpVideo', 'crpvideo_playlist_size', $crpvideo_playlist_size);
		$crpvideo_playlist_items= ($crpvideo_enable_playlist) ? (int) FormUtil :: getPassedValue('crpvideo_playlist_items', 10, 'POST') : null;
		pnModSetVar('crpVideo', 'crpvideo_playlist_items', $crpvideo_playlist_items);

		// Let any other modules know that the modules configuration has been updated
		pnModCallHooks('module', 'updateconfig', 'crpVideo', array (
			'module' => 'crpVideo'
		));

		// the module configuration has been updated successfuly
		LogUtil :: registerStatus(_CONFIGUPDATED);

		return pnRedirect(pnModURL('crpVideo', 'admin', 'view'));
	}

	/**
	 * Generate thumbnail for image
	 *
	 * @param int id doc
	 * @param string width doc
	 * @return nothing
	 */
	function getThumbnail()
	{
		$videoid= FormUtil :: getPassedValue('videoid', isset ($args['videoid']) ? $args['videoid'] : null, 'REQUEST');
		$width= FormUtil :: getPassedValue('width', isset ($args['width']) ? $args['width'] : null, 'REQUEST');
		if (!SecurityUtil :: checkPermission('crpVideo::', '::', ACCESS_READ))
			pnShutDown();

		$file= $this->dao->getFile($videoid, 'image', true);
		$modifiedDate= $this->dao->getVideoDate($videoid, 'lu_date');

		if (!(is_numeric($width) && $width > 0))
			$width= pnModGetVar('crpVideo', 'image_width');
		$params['width']= $width; //	$params['append_ghosted']=1;
		$params['modifiedDate']= $modifiedDate;

		crpVideo :: imageGetThumbnail($file['binary_data'], $file['name'], $file['content_type'], $params);
	}

	function imageGetThumbnail(& $pSrcImage, $filename, $content_type, $params= array ())
	{
		// we need a timestamp
		$server_etag= DateUtil :: makeTimestamp($params['modifiedDate']);
		$server_date= gmdate('D, d M Y H:i:s', $server_etag) . " GMT";

		// Check cached versus modified date
		$client_etag= $_SERVER['HTTP_IF_NONE_MATCH'];
		$client_date= $_SERVER['HTTP_IF_MODIFIED_SINCE'];

		if (($client_etag == $server_etag) && (!$client_date || ($client_date == $server_date)))
		{
			header("HTTP/1.1 304 Not Modified");
			header("ETag: $server_etag");
			pnShutDown();
		}
		else
		{
			header("Expires: " . gmdate('D, d M Y H:i:s', time() + 24 * 3600) . " GMT");
			header('Pragma: cache');
			header('Cache-Control: public, must-revalidate');
			header("ETag: $server_etag");
			header("Last-Modified: " . gmdate('D, d M Y H:i:s', $server_etag) . " GMT");
			header("Content-Type: $content_type");
			header("Content-Disposition: inline; filename=thumb_{$filename}");
		}

		/***************************************************************************/

		$alphaThreshold= isset ($params['alpha_threshold']) ? $params['alpha_threshold'] : 64;
		$appendGhosted= $params['append_ghosted'];
		//
		$srcImage= imagecreatefromstring($pSrcImage);

		if ($srcImage)
		{
			//obtain the original image Height and Width
			$srcWidth= imagesx($srcImage);
			$srcHeight= imagesy($srcImage);

			$newWidth= isset ($params['width']) ? $params['width'] : $srcWidth;

			$destWidth= round($newWidth, '0');
			$destHeight= round(($srcHeight / $srcWidth) * $newWidth, '0');

			// creating the destination image with the new Width and Height
			if (!$appendGhosted)
				$destImage= imagecreatetruecolor($destWidth, $destHeight);
			else
				$destImage= imagecreatetruecolor($destWidth, 2 * $destHeight);

			//png transparency
			switch ($content_type)
			{
				case 'image/png' :
				case 'image/x-png' :
					imageantialias($destImage, true);
					imagealphablending($destImage, false);
					imagesavealpha($destImage, true);
					$transparent= imagecolorallocatealpha($destImage, 255, 255, 255, 80);
					imagefill($destImage, 0, 0, $transparent);
					break;

				case 'image/gif' :
					imageantialias($destImage, true);
					imagealphablending($destImage, false);
					break;
			}

			//copy the srcImage to the destImage
			imagecopyresampled($destImage, $srcImage, 0, 0, 0, 0, $destWidth, $destHeight, $srcWidth, $srcHeight);

			//
			if ($appendGhosted)
			{
				imagecopyresampled($destImage, $srcImage, 0, $destHeight, 0, 0, $destWidth, $destHeight, $srcWidth, $srcHeight);

				$ghostImage= imagecreatetruecolor($destWidth, $destHeight);
				imageantialias($ghostImage, true);
				imagealphablending($ghostImage, false);
				imagesavealpha($ghostImage, true);
				$whitetrasp= imagecolorallocatealpha($ghostImage, 255, 255, 255, 0);
				imagefill($ghostImage, 0, 0, $whitetrasp);
				imagecopymerge($destImage, $ghostImage, 0, $destHeight, 0, 0, $destWidth, $destHeight, 50);
				if ($content_type == 'image/png')
				{ //	problems mergins transparent png.. need to restore original pixel transparency
					for ($x= 0; $x < $destWidth; $x++)
						for ($y= 0; $y < $destHeight; $y++)
						{
							$srcPixel= imagecolorsforindex($destImage, imagecolorat($destImage, $x, $y));
							$destPixel= imagecolorsforindex($destImage, imagecolorat($destImage, $x, $y + $destHeight));
							imagesetpixel($destImage, $x, $y + $destHeight, imagecolorallocatealpha($destImage, $destPixel['red'], $destPixel['green'], $destPixel['blue'], $srcPixel['alpha']));
						}

				}
				imagedestroy($ghostImage);

			}

			//save output to a buffer
			ob_start();

			//create the image
			switch ($content_type)
			{
				case 'image/gif' :
					imagetruecolortopalette($destImage, true, 255);
					//
					if (imagecolortransparent($srcImage) >= 0)
					{
						$maskImage= imagecreatetruecolor($destWidth, $destHeight);
						imageantialias($maskImage, true);
						imagealphablending($maskImage, false);
						imagecopyresampled($maskImage, $srcImage, 0, 0, 0, 0, $destWidth, $destHeight, $srcWidth, $srcHeight);
						//
						$transp= imagecolorallocatealpha($destImage, 0, 0, 0, 127);
						imagecolortransparent($destImage, $transp);
						//
						for ($x= 0; $x < $destWidth; $x++)
							for ($y= 0; $y < $destHeight; $y++)
							{
								$c= imagecolorsforindex($maskImage, imagecolorat($maskImage, $x, $y));
								if ($c['alpha'] >= $alphaThreshold)
								{
									imagesetpixel($destImage, $x, $y, $transp);
									if ($appendGhosted)
										imagesetpixel($destImage, $x, $y + $destHeight, $transp);
								}
							}
						imagedestroy($maskImage);
					}
					//
					imagegif($destImage);
					break;

				case 'image/jpeg' :
				case 'image/pjpeg' :
					imagejpeg($destImage);
					break;

				case 'image/png' :
				case 'image/x-png' :
					imagepng($destImage);
					break;
			}

			//copy output buffer to string
			$resizedImage= ob_get_contents();

			//clear output buffer that was saved
			ob_end_clean();

			//fre the memory used for the images
			imagedestroy($srcImage);
			imagedestroy($destImage);

			echo $resizedImage;
			pnShutDown();
		}
	}

	/**
	 * Display RSS content
	 *
	 * */
	function getFeed()
	{
		$result= '';

		// Return if not enabled
		if (!pnModGetVar('crpVideo', 'crpvideo_enable_rss'))
			return $result;
		//	header("Content-Type: text/plain\n\n");	//debug

		$rssinfo= $this->loadRSS('crpVideo', 'videos', pnUserGetLang());

		$feedfunc= "crpVideo_videos_rss_feed";
		$list= array ();
		if (function_exists($feedfunc))
			$list= $feedfunc ();

		$data['xml_lang']= substr(pnUserGetLang(), 0, 2);
		$data['publ_date']= date('Y-m-d H:i:s', time());
		$selfurl= pnModUrl('crpVideo', 'user', 'getfeed');
		$data['selfurl']= $selfurl;
		$data['format']= pnModGetVar('crpVideo', 'crpvideo_rss');
		$sitename= pnConfigGetVar('sitename');

		Header("Content-Disposition: inline; filename=" . $sitename . "_videos.xml");
		if ($data['format'] == _CRPVIDEO_ATOM)
			header("Content-Type: application/atom+xml\n\n");
		else
			header("Content-Type: application/rss+xml\n\n");
		//	header("Content-Type: text/xml\n\n");

		$result= $this->ui->drawFeed($data, $list);
		echo $result;
		pnShutDown();
	}

	/**
	 * Display Playlist content
	 *
	 * */
	function getPlaylist()
	{
		$result= '';

		// Return if not enabled
		if (!pnModGetVar('crpVideo', 'crpvideo_enable_playlist'))
			return $result;
		//	header("Content-Type: text/plain\n\n");	//debug

		$videoid= FormUtil :: getPassedValue('videoid', isset ($args['videoid']) ? $args['videoid'] : null, 'GET');

		switch (pnModGetVar('crpVideo', 'crpvideo_playlist_type'))
		{
			case "category" :
				// by category
				$apiargs['category']= FormUtil :: getPassedValue('id', isset ($args['id']) ? $args['id'] : null, 'GET');
				$apiargs['orderBy']= 'cr_date';
				break;

			case "views" :
				// most viewed
				$apiargs['orderBy']= 'counter';
				$apiargs['interval']= '31';
				break;

			case "uploader" :
				// by uploader
				$apiargs['orderBy']= 'cr_date';
				$apiargs['uid']= FormUtil :: getPassedValue('id', isset ($args['id']) ? $args['id'] : null, 'GET');
				break;

			case "date" :
			default :
				// by date
				$apiargs['orderBy']= 'cr_date';
				break;
		}

		$apiargs['startnum']= 1;
		$apiargs['active']= 'A';
		$apiargs['itemsperpage']= pnModGetVar('crpVideo', 'crpvideo_playlist_items') + 1;
		$apiargs['sortOrder']= 'DESC';

		// call the api
		$list= pnModAPIFunc('crpVideo', 'user', 'getall', $apiargs);
		foreach ($list as $kvideo => $vvideo)
		{
			if (($vvideo['videoid'] == $videoid) || ($vvideo['source'] != 'video'))
				unset ($list[$kvideo]);
		}

		array_unshift($list, pnModAPIFunc('crpVideo', 'user', 'get', array (
			'videoid' => $videoid
		)));

		Header("Content-Disposition: inline; filename=playlist_videos.xml");
		Header("Content-Type: application/xml; charset={_CHARSET}\n\n");
		//Header("Content-Type: text/xml; charset={_CHARSET}\n\n");

		$result= $this->ui->drawPlaylist($list);
		echo $result;
		pnShutDown();
	}

	/**
	 * Retrieve info about a rss module plugin
	 *
	 * */
	function loadRSS($modname, $modrss, $id_lang= '')
	{
		$result= false;

		$modinfo= pnModGetInfo(pnModGetIdFromName($modname));
		$moddir= 'modules/' . pnVarPrepForOS($modinfo['directory']) . '/pnrss';
		$langdir= 'modules/' . pnVarPrepForOS($modinfo['directory']) . '/pnlang';
		$infofunc= "{$modname}_{$modrss}rss_info";

		if (!$id_lang)
			$id_lang= pnUserGetLang();

		// Load the rss
		$incfile= $modrss . '.php';
		$filepath= $moddir . '/' . pnVarPrepForOS($incfile);
		if (!file_exists($filepath))
			return false;

		include_once $filepath;

		// Load the RSS language files
		$currentlangfile= $langdir . '/' . pnVarPrepForOS($id_lang) . '/' . pnVarPrepForOS($incfile);
		$defaultlangfile= $langdir . '/' . pnVarPrepForOS(pnConfigGetVar('language')) . '/' . pnVarPrepForOS($incfile);
		if (file_exists($currentlangfile))
			include_once $currentlangfile;
		elseif (file_exists($defaultlangfile)) include_once $defaultlangfile;

		// get the rss info
		if (function_exists($infofunc) && ($info= $infofunc ()) && ($info !== false))
		{
			// set the module and keys for the new rss
			if (!isset ($info['module']))
				$info['module']= $modname;
			$info['mid']= pnModGetIDFromName($$modname);

			// Initialise rss if required (new-style)
			$initfunc= "{$modname}_{$modrss}rss_init";
			if (function_exists($initfunc))
			{
				pnModLangLoad($modname);
				$initfunc ();
			}
			$result= $info;
		}
		//
		return $result;
	}

	/**
	 * Display Podcast content
	 *
	 * */
	function getPodcast()
	{
		$result= '';

		// Return if not enabled
		if (!pnModGetVar('crpVideo', 'crpvideo_enable_podcast'))
			return $result;
		//	header("Content-Type: text/plain\n\n");	//debug

		$data['xml_lang']= str_replace('_', '-', strtolower(_LOCALE));
		$data['publ_date']= date('Y-m-d H:i:s', time());
		$data['selfurl']= pnModUrl('crpVideo', 'user', 'getpodcast');
		$data['timezone']= (pnConfigGetVar('timezone_offset') >= 0) ? '+' : '-';
		$data['timezone'] .= str_pad(abs(pnConfigGetVar('timezone_offset')) * 100, 4, '0', STR_PAD_LEFT);

		$sitename= pnConfigGetVar('sitename');

		Header("Content-Disposition: inline; filename=" . $sitename . "_podcasts.xml");
		header("Content-Type: application/rss+xml\n\n");
		//	header("Content-Type: text/xml\n\n");

		$modvars= pnModGetVar('crpVideo');

		$apiargs['startnum']= 1;
		$apiargs['category']= $modvars['crpvideo_podcast_category'];
		$apiargs['active']= 'A';
		$apiargs['orderBy']= 'cr_date';
		$apiargs['sortOrder']= 'DESC';
		//$apiargs['extension'] = 'mp3';

		// call the api
		$list= pnModAPIFunc('crpVideo', 'user', 'getall', $apiargs);

		// force time in en format
		setlocale(LC_TIME, 'en_US');

		$result= $this->ui->drawPodcast($data, $list, $modvars);
		echo $result;
		pnShutDown();
	}

	/**
	* send an email notification
	*/
	function notifyByMail($inputValues= array (), $videoid= null)
	{
		// send the email
		$pnRender= pnRender :: getInstance('crpVideo', false);
		$pnRender->assign('inputValues', $inputValues);
		$pnRender->assign('videoid', $videoid);
		$body= $pnRender->fetch('crpvideo_user_notify_newvideo.htm');

		$subject= _CRPVIDEO_VIDEO_NOTIFICATION;
		$to= pnModGetVar('crpVideo', 'crpvideo_notification');
		;

		$result= pnModAPIFunc('Mailer', 'user', 'sendmessage', array (
			'toaddress' => $to,
			'subject' => $subject,
			'body' => $body,
			'html' => true,
			'fromname' => pnConfigGetVar('sitename'),
			'fromaddress' => pnConfigGetVar('adminmail'),
			'replytoname' => pnConfigGetVar('sitename'),
			'replytoaddress' => pnConfigGetVar('adminmail')
		));
	}

	/**
	 * List overall uploaders
	 */
	function listUploaders()
	{
		$navigationValues= $this->collectNavigationFromInput();

		// sort by
		$navigationValues['orderBy']= 'uploads';
		$navigationValues['sortOrder']= 'DESC';
		$items= pnModAPIFunc('crpVideo', 'user', 'get_uploaders', $navigationValues);

		$rows= array ();
		$exports= array ();
		foreach ($items as $kevent => $item)
		{
			$options= array ();
			$options[]= crpVideo :: buildLinkArray("_CRPVIDEO_VIDEOS_UPLOADED", $item, 'user');

			$options[]= array (
				'url' => pnModURL('Profile', 'user', 'view', array (
					'uid' => $item['cr_uid']
				)),
				'image' => 'personal.gif',
				'title' => _VIEW
			);

			// Add the calculated menu options to the item array
			$item['options']= $options;
			$rows[]= $item;
		}

		return $this->ui->uploadersList($rows, $navigationValues['category'], $navigationValues['mainCat'], $navigationValues['rootCat'], $navigationValues['cats'], $navigationValues['modvars'], $navigationValues['active']);
	}

	/**
	 * List uploads by a user
	 */
	function listUploads()
	{
		$navigationValues= $this->collectNavigationFromInput();

		$items= pnModAPIFunc('crpVideo', 'user', 'getall', $navigationValues);

		// Create output object
		$pnRender= pnRender :: getInstance('crpVideo', false);
		$pnRender->assign($navigationValues['modvars']);
		// Loop through each item and display it.

		return $this->ui->uploadsList($items, $navigationValues['category'], $navigationValues['mainCat'], $navigationValues['rootCat'], $navigationValues['cats'], $navigationValues['modvars'], $navigationValues['uid'], $navigationValues['active']);
	}

	/**
	 * Collect navigation input value
	 *
	 * @param int $startnum pager offset
	 * @param int $category current category if specified
	 * @param bool clear clean category
	 * @param bool $ignoreml ignore multilanguage
	 *
	 * @return array input values
	 */
	function collectNavigationFromInput()
	{
		// Get parameters from whatever input we need.
		$startnum= (int) FormUtil :: getPassedValue('startnum', isset ($args['startnum']) ? $args['startnum'] : 0, 'GET');
		$cat= (string) FormUtil :: getPassedValue('cat', isset ($args['cat']) ? $args['cat'] : null, 'GET');
		$uid= (int) FormUtil :: getPassedValue('uid', null, 'GET');
		$active= FormUtil :: getPassedValue('active', 'A');
		$clear= FormUtil :: getPassedValue('clear');

		// defaults and input validation
		if (!is_numeric($startnum) || $startnum < 0)
		{
			$startnum= 1;
		}

		if ($clear)
		{
			$active= null;
			$cat= null;
		}

		$ignoreml= FormUtil :: getPassedValue('ignoreml', true);
		$sortOrder= FormUtil :: getPassedValue('sortOrder', 'ASC');
		$orderBy= FormUtil :: getPassedValue('orderBy', 'title');

		// load the category registry util
		if (!($class= Loader :: loadClass('CategoryRegistryUtil')))
			pn_exit('Unable to load class [CategoryRegistryUtil] ...');
		if (!($class= Loader :: loadClass('CategoryUtil')))
			pn_exit('Unable to load class [CategoryUtil] ...');

		$mainCat= CategoryRegistryUtil :: getRegisteredModuleCategory('crpVideo', 'crpvideos', 'Main', '/__SYSTEM__/Modules/crpVideo');
		$rootCat= CategoryUtil :: getCategoryByID($mainCat);
		$cats= CategoryUtil :: getCategoriesByParentID($mainCat);

		// get all module vars for later use
		$modvars= pnModGetVar('crpVideo');

		$data= compact('startnum', 'category', 'active', 'clear', 'ignoreml', 'mainCat', 'rootCat', 'cats', 'modvars', 'sortOrder', 'orderBy', 'uid');

		return $data;
	}

	/**
	 * build an array link by define
	 *
	 * @param string $mlname link define
	 * @param array $item values
	 * @param string $actiontype user level action
	 *
	 * @return array link
	 */
	function buildLinkArray($mlname= null, $item= array (), $actiontype= null)
	{

		switch ($mlname)
		{
			case "_CRPVIDEO_VIDEOS_UPLOADED" :
				$linkArray= array (
					'url' => pnModURL('crpVideo', 'user', 'view_uploads', array (
						'uid' => $item['cr_uid']
					)),
					'image' => 'folder_inbox.gif',
					'title' => pnML('_CRPVIDEO_VIDEOS_UPLOADED', array (
						'videos' => $item['counter']
					), true)
				);
				break;
			default :
				$linkArray= "";
				break;
		}

		return $linkArray;
	}

}
?>
