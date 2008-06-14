<?php
/**
 * crpVideo
 *
 * @copyright (c) 2007, Daniele Conca
 * @link http://noc.postnuke.com/projects/crpvideo Support and documentation
 * @version $Id: $ 
 * @author Daniele Conca <jami at cremonapalloza dot org>
 * @license GNU/GPL - v.2
 * @package crpVideo
 */

Loader::includeOnce('modules/crpVideo/pnclass/crpVideoUI.php');
Loader::includeOnce('modules/crpVideo/pnclass/crpVideoDAO.php');

/**
 * crpVideo Object
 */
class crpVideo
{

	function crpVideo()
	{
		$this->ui = new crpVideoUI();
		$this->dao = new crpVideoDAO();
		
		(function_exists('gd_info'))?$this->gd = gd_info():$this->gd = array();
	}
	
	/**
	 * update a cover
	 * 
	 * @param int $videoid item identifier
	 * @param array $inputValues array of updated values
	 * 
	 * @return string html
	 */
	function createCover($video=array())
	{	
		// Argument check
		if(!$this->dao->validateData($video))
			return false;
		
		$video['image']['videoid'] = $video['videoid'];
		$video['image']['document_type'] = 'image';
		$id_image = $this->dao->setFile($video['image']);
		if($id_image == '-1')	return false;
		
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
		$videoid = FormUtil::getPassedValue('videoid', null);
		$obj_status = FormUtil :: getPassedValue('obj_status', null);

		if ($obj_status=='P' || $obj_status=='A')
		{
			($obj_status=='A')?$obj_status='P':$obj_status='A';
			if (!$this->dao->updateStatus($videoid, $obj_status)) 
	      LogUtil::registerError (_UPDATEFAILED);
	    else
				LogUtil::registerStatus (_UPDATESUCCEDED);
		}
		else
			LogUtil::registerError (_UPDATEFAILED);
		
    return pnRedirect(pnModURL('crpVideo', 'admin', 'view')); 
	}
	
	/**
	 * Modify module's configuration
	 */
	function modifyConfig()
	{
		// get all module vars
		$modvars = pnModGetVar('crpVideo');
		
		return $this->ui->modifyConfig($modvars, $this->gd);
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
		$addcategorytitletopermalink= (bool) FormUtil :: getPassedValue('addcategorytitletopermalink', false, 'POST');
		pnModSetVar('crpVideo', 'addcategorytitletopermalink', $addcategorytitletopermalink);
		$cover_dimension = (int)FormUtil::getPassedValue('cover_dimension', 35000, 'POST');
	  pnModSetVar('crpVideo', 'cover_dimension', $cover_dimension);
	  $image_width = (int)FormUtil::getPassedValue('image_width', 300, 'POST');
	  pnModSetVar('crpVideo', 'image_width', $image_width);
	  $playerwidth = (int)FormUtil::getPassedValue('playerwidth', 400, 'POST');
	  pnModSetVar('crpVideo', 'playerwidth', $playerwidth);
	  $playerheight = (int)FormUtil::getPassedValue('playerheight', 340, 'POST');
	  pnModSetVar('crpVideo', 'playerheight', $playerheight);
	 	$displayheight = (int)FormUtil::getPassedValue('displayheight', 300, 'POST');
	  pnModSetVar('crpVideo', 'displayheight', $displayheight);
	 	$file_dimension = (int)FormUtil::getPassedValue('file_dimension', 1500000, 'POST');
	  pnModSetVar('crpVideo', 'file_dimension', $file_dimension);
	  $upload_path = FormUtil::getPassedValue('upload_path', null, 'POST');
	  pnModSetVar('crpVideo', 'upload_path', $upload_path);
	  $browser_path = FormUtil::getPassedValue('browser_path', null, 'POST');
	  pnModSetVar('crpVideo', 'browser_path', $browser_path);
	  $crpvideo_use_gd = (bool)FormUtil::getPassedValue('crpvideo_use_gd', false, 'POST');
	  pnModSetVar('crpVideo', 'crpvideo_use_gd', $crpvideo_use_gd);
	  $crpvideo_userlist_image = (bool)FormUtil::getPassedValue('crpvideo_userlist_image', false, 'POST');
	  pnModSetVar('crpVideo', 'crpvideo_userlist_image', $crpvideo_userlist_image);
	  $userlist_width = (int)FormUtil::getPassedValue('userlist_width', 32, 'POST');
	  pnModSetVar('crpVideo', 'userlist_width', $userlist_width);
	  $display_embed = (bool)FormUtil::getPassedValue('display_embed', false, 'POST');
	  pnModSetVar('crpVideo', 'display_embed', $display_embed);
	  $mandatory_cover = (bool)FormUtil::getPassedValue('mandatory_cover', false, 'POST');
	  pnModSetVar('crpVideo', 'mandatory_cover', $mandatory_cover);
	  
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
		$videoid = FormUtil::getPassedValue('videoid', isset($args['videoid']) ? $args['videoid'] : null, 'REQUEST');
		$width = FormUtil::getPassedValue('width', isset($args['width']) ? $args['width'] : null, 'REQUEST');
		if(!SecurityUtil::checkPermission('crpVideo::', '::', ACCESS_READ)) pnShutDown();

		$file = $this->dao->getFile($videoid, 'image', true);
		$modifiedDate = $this->dao->getVideoDate($videoid,'lu_date');

		if(!(is_numeric($width) && $width>0))
			$width = pnModGetVar('crpVideo', 'image_width');
		$params['width'] = $width;//	$params['append_ghosted']=1;
		$params['modifiedDate'] = $modifiedDate;

		crpVideo::imageGetThumbnail($file['binary_data'], $file['filename'],  $file['content_type'], $params);
	}
	
	function imageGetThumbnail(& $pSrcImage, $filename, $content_type, $params = array())
	{
		$alphaThreshold = isset($params['alpha_threshold']) ? $params['alpha_threshold'] : 64;
		$newWidth = isset($params['width']) ? $params['width'] : 100;
		$appendGhosted = $params['append_ghosted'];
		//
		$srcImage = imagecreatefromstring($pSrcImage);
	
		if ($srcImage)
		{
			//obtain the original image Height and Width
			$srcWidth = imagesx($srcImage);
			$srcHeight = imagesy($srcImage);
	
			$destWidth = round($newWidth, '0');
			$destHeight = round(($srcHeight / $srcWidth) * $newWidth, '0');
	
			// creating the destination image with the new Width and Height
			if(!$appendGhosted)
				$destImage = imagecreatetruecolor($destWidth, $destHeight);
			else
				$destImage = imagecreatetruecolor($destWidth, 2*$destHeight);
	
			//png transparency
			switch ($content_type)
			{
				case 'image/png' :
					imageantialias($destImage, true);
					imagealphablending($destImage, false);
					imagesavealpha($destImage, true);
					$transparent = imagecolorallocatealpha($destImage, 255, 255, 255, 80);
					imagefill($destImage, 0,0,$transparent);
					break;
	
				case 'image/gif' :
					imageantialias($destImage, true);
					imagealphablending($destImage, false);
					break;
			}
	
			//copy the srcImage to the destImage
			imagecopyresampled($destImage, $srcImage, 0, 0, 0, 0, $destWidth, $destHeight, $srcWidth, $srcHeight);
	
			//
			if($appendGhosted)
			{
				imagecopyresampled($destImage, $srcImage, 0, $destHeight, 0, 0, $destWidth, $destHeight, $srcWidth, $srcHeight);
	
				$ghostImage = imagecreatetruecolor($destWidth, $destHeight);
				imageantialias($ghostImage, true);
				imagealphablending($ghostImage, false);
				imagesavealpha($ghostImage, true);
				$whitetrasp = imagecolorallocatealpha($ghostImage, 255, 255, 255, 0);
				imagefill($ghostImage, 0,0,$whitetrasp);
				imagecopymerge($destImage, $ghostImage, 0,$destHeight,0,0,$destWidth, $destHeight, 50);
				if($content_type == 'image/png')
				{	//	problems mergins transparent png.. need to restore original pixel transparency
					for ($x = 0; $x < $destWidth; $x ++)
						for ($y = 0; $y < $destHeight; $y ++)
						{
							$srcPixel = imagecolorsforindex($destImage, imagecolorat($destImage, $x, $y));
							$destPixel = imagecolorsforindex($destImage, imagecolorat($destImage, $x, $y+$destHeight));
							imagesetpixel($destImage, $x, $y+$destHeight, imagecolorallocatealpha($destImage,
								$destPixel['red'], $destPixel['green'], $destPixel['blue'], $srcPixel['alpha']));
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
					if(imagecolortransparent($srcImage)>=0)
					{
						$maskImage = imagecreatetruecolor( $destWidth, $destHeight);
						imageantialias($maskImage, true);
						imagealphablending($maskImage, false);
						imagecopyresampled( $maskImage, $srcImage, 0, 0, 0, 0, $destWidth, $destHeight, $srcWidth, $srcHeight );
						//
						$transp = imagecolorallocatealpha($destImage, 0,0,0,127);
						imagecolortransparent($destImage, $transp);
						//
						for ($x = 0; $x < $destWidth; $x ++)
							for ($y = 0; $y < $destHeight; $y ++)
							{
								$c = imagecolorsforindex($maskImage, imagecolorat($maskImage, $x, $y));
								if($c['alpha'] >= $alphaThreshold)
								{
									imagesetpixel($destImage, $x, $y, $transp);
									if($appendGhosted)
										imagesetpixel($destImage, $x, $y+$destHeight, $transp);
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
					imagepng($destImage);
					break;
			}
	
			//copy output buffer to string
			$resizedImage = ob_get_contents();
	
			//clear output buffer that was saved
			ob_end_clean();
	
			//fre the memory used for the images
			imagedestroy($srcImage);
			imagedestroy($destImage);
			
			// credits to Mediashare by Jorn Lind-Nielsen
			if (pnConfigGetVar('UseCompression') == 1)
  			header("Content-Encoding: identity");
  		
  		// we need a timestamp
  		$params['modifiedDate'] = DateUtil::parseUIDateTime($params['modifiedDate']);
  			
  		// Check cached versus modified date
		  $lastModifiedDate = date('D, d M Y H:i:s T', $params['modifiedDate']);
		  $currentETag = $params['modifiedDate'];
		
		  global $HTTP_SERVER_VARS;
		  $cachedDate = (isset($HTTP_SERVER_VARS['HTTP_IF_MODIFIED_SINCE']) ? $HTTP_SERVER_VARS['HTTP_IF_MODIFIED_SINCE'] : null);
		  $cachedETag = (isset($HTTP_SERVER_VARS['HTTP_IF_NONE_MATCH']) ? $HTTP_SERVER_VARS['HTTP_IF_NONE_MATCH'] : null);
		
		  // If magic quotes are on then all query/post variables are escaped - so strip slashes to make a compare possible
		  // - only cachedETag is expected to contain quotes
		  if (get_magic_quotes_gpc())
		    $cachedETag = stripslashes($cachedETag);
		
		  if (    (empty($cachedDate) || $lastModifiedDate == $cachedDate)
		      &&  '"'.$currentETag.'"' == $cachedETag)
		  {
		    header("HTTP/1.1 304 Not Modified");
		    header("Status: 304 Not Modified");
		    header("Expires: " . date('D, d M Y H:i:s T', time()+180*24*3600)); // My PHP insists on Expires in 1981 as default!
		  	header('Pragma: cache'); // My PHP insists on putting a pragma "no-cache", so this is an attempt to avoid that
		  	header('Cache-Control: public');
		    header("ETag: \"$params[modifiedDate]\"");
		    pnShutDown();
		  }
  			
  		header("Expires: " . date('D, d M Y H:i:s T', time()+180*24*3600));
  		header('Pragma: cache');
  		header('Cache-Control: public');
  		header("ETag: \"$params[modifiedDate]\"");
	
			header("Content-Disposition: inline; filename=thumb_{$filename}");
			header("Content-Type: $content_type");
			header("Last-Modified: $lastModifiedDate");
			//header("Content-Length: " . strlen($resizedImage));
			echo $resizedImage;
			pnShutDown();
		}
	}
	
}

?>
