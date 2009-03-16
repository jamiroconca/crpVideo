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
 * crpVideoDAO
 */
class crpVideoDAO
{

	function crpVideoDAO()
	{
		// images allowed
		$this->ImageTypes[] = 'image/gif';
		$this->ImageTypes[] = 'image/jpeg';
		$this->ImageTypes[] = 'image/pjpeg';
		$this->ImageTypes[] = 'image/png';

		// video allowed
		$this->VideoTypes[] = 'video/x-flv';
		$this->VideoTypes[] = 'application/x-shockwave-flash';
		$this->VideoTypes[] = 'application/octet-stream';
		// audio allowed
		$this->VideoTypes[] = 'audio/mpeg';
	}

	/**
	 * Return administrative list of videos
	 *
	 * @param int $startnum pager offset
	 * @param int $category current category if specified
	 * @param bool $ignoreml ignore multilanguage
	 * @param array $modvars module's variables
	 * @param int $mainCat main module's category
	 *
	 * @return array element list
	 */
	function adminList($startnum = 1, $category = null, $clear = false, $ignoreml = true, $modvars = array (), $mainCat, $active = null, $interval = null, $sortOrder = 'DESC', $orderBy = 'title')
	{
		(empty ($startnum)) ? $startnum = 1 : '';
		(empty ($modvars['itemsperpage'])) ? $modvars['itemsperpage'] = pnModGetVar('crpVideo', 'itemsperpage') : '';

		if (!is_numeric($startnum) || !is_numeric($modvars['itemsperpage']))
		{
			return LogUtil :: registerError(_MODARGSERROR);
		}

		$catFilter = array ();
		if (is_array($category))
			$catFilter = $category;
		else
			if ($category)
			{
				$catFilter['Main'] = $category;
				$catFilter['__META__']['module'] = 'crpVideo';
			}

		$items = array ();

		// Security check
		if (!SecurityUtil :: checkPermission('crpVideo::', '::', ACCESS_READ))
		{
			return $items;
		}

		$pntable = pnDBGetTables();
		$crpvideocolumn = $pntable['crpvideos_column'];
		$queryargs = array ();
		if (pnConfigGetVar('multilingual') == 1 && !$ignoreml)
		{
			$queryargs[] = "($crpvideocolumn[language]='" . DataUtil :: formatForStore(pnUserGetLang()) . "'
															OR $crpvideocolumn[language]='')";
		}

		if ($active)
		{
			$queryargs[] = "($crpvideocolumn[obj_status]='" . DataUtil :: formatForStore($active) . "')";
		}

		$where = null;
		if (count($queryargs) > 0)
		{
			$where = ' WHERE ' . implode(' AND ', $queryargs);
		}

		// define the permission filter to apply
		$permFilter = array (
			array (
				'realm' => 0,
				'component_left' => 'crpVideo',
				'component_right' => 'Video',
				'instance_left' => 'cr_uid',
				'instance_center' => 'title',
				'instance_right' => 'videoid',
				'level' => ACCESS_READ
			)
		);

		$orderby = "ORDER BY $crpvideocolumn[$orderBy] $sortOrder";

		// get the objects from the db
		$objArray = DBUtil :: selectObjectArray('crpvideos', $where, $orderby, $startnum -1, $modvars['itemsperpage'], '', $permFilter, $catFilter);

		// Check for an error with the database code, and if so set an appropriate
		// error message and return
		if ($objArray === false)
		{
			return LogUtil :: registerError(_GETFAILED);
		}

		// need to do this here as the category expansion code can't know the
		// root category which we need to build the relative path component
		if ($objArray && isset ($mainCat) && $mainCat)
		{
			if (!Loader :: loadClass('CategoryUtil'))
			{
				pn_exit('Unable to load class [CategoryUtil]');
			}
			ObjectUtil :: postProcessExpandedObjectArrayCategories($objArray, $mainCat);
		}

		// Return the items
		return $objArray;
	}

	/**
	 * Return form list of events
	 *
	 * @param int $startnum pager offset
	 * @param int $category current category if specified
	 * @param bool $ignoreml ignore multilanguage
	 * @param array $modvars module's variables
	 * @param int $mainCat main module's category
	 *
	 * @return array element list
	 */
	function formList($startnum= 1, $category= null, $clear= false, $ignoreml= true, $modvars= array (), $mainCat, $active= null, $interval= null, $sortOrder= 'DESC')
	{
		if (!is_numeric($startnum) || !is_numeric($modvars['itemsperpage']))
		{
			return LogUtil :: registerError(_MODARGSERROR);
		}

		$catFilter= array ();
		if (is_array($category))
			$catFilter= $category;
		else
			if ($category)
			{
				$catFilter['Main']= $category;
				$catFilter['__META__']['module']= 'crpVideo';
			}

		$items= array ();
		$nowDate = DateUtil::getDatetime();

		// Security check
		if (!SecurityUtil :: checkPermission('crpVideo::', '::', ACCESS_READ))
		{
			return $items;
		}

		$pntable= pnDBGetTables();
		$crpvideocolumn= $pntable['crpvideos_column'];
		$queryargs= array ();
		if (pnConfigGetVar('multilingual') == 1 && !$ignoreml)
		{
			$queryargs[]= "($crpvideocolumn[language]='" . DataUtil :: formatForStore(pnUserGetLang()) . "' " .
			"OR $crpvideocolumn[language]='')";
		}

		if ($active)
		{
			$queryargs[]= "($crpvideocolumn[obj_status]='" . DataUtil :: formatForStore($active) . "')";
		}

		if ($interval)
		{
			$intervaltime = time() - $interval * 86400;
			$intervalDate = DateUtil :: getDatetime($intervaltime);
			$queryargs[]= "($crpvideocolumn[cr_date] < '" . DataUtil :: formatForStore($nowDate) . "' " .
			"AND $crpvideocolumn[cr_date] > '" . DataUtil :: formatForStore($intervalDate) . "')";
		}

		$where= null;
		if (count($queryargs) > 0)
		{
			$where= ' WHERE ' . implode(' AND ', $queryargs);
		}

		// define the permission filter to apply
		$permFilter= array (
			array (
				'realm' => 0,
				'component_left' => 'crpVideo',
				'component_right' => 'Video',
				'instance_left' => 'cr_uid',
				'instance_center' => 'title',
				'instance_right' => 'videoid',
				'level' => ACCESS_READ
			)
		);

		$orderby= "ORDER BY $crpvideocolumn[title] $sortOrder";

		$columnArray= array (
			'videoid',
			'title'
		);

		// get the objects from the db
		$objArray= DBUtil :: selectObjectArray('crpvideos', $where, $orderby, $startnum -1, '9999', '', $permFilter, $catFilter, $columnArray);

		// Check for an error with the database code, and if so set an appropriate
		// error message and return
		if ($objArray === false)
		{
			return LogUtil :: registerError(_GETFAILED);
		}

		foreach ($objArray as $kObj => $vObj)
		{
			$formArray[]= array (
				'id' => $vObj['videoid'],
				'name' => $vObj['title']
			);
		}

		// Return the items
		return $formArray;
	}


	function getData($args = array ())
	{
		// define the permission filter to apply
		$permFilter = array (
			array (
				'realm' => 0,
				'component_left' => 'crpVideo',
				'component_right' => 'Video',
				'instance_left' => 'cr_uid',
				'instance_center' => 'title',
				'instance_right' => 'videoid',
				'level' => ACCESS_READ
			)
		);

		if (isset ($args['videoid']) && is_numeric($args['videoid']))
		{
			$item = DBUtil :: selectObjectByID('crpvideos', $args['videoid'], 'videoid', '', $permFilter);
		}
		else
		{
			$item = DBUtil :: selectObjectByID('crpvideos', $args['title'], 'urltitle', '', $permFilter);
		}

		if (empty ($item))
			return false;

		// process the relative paths of the categories
		if (pnModGetVar('crpVideo', 'enablecategorization') && !empty ($item['__CATEGORIES__']))
		{
			static $registeredCats;
			if (!isset ($registeredCats))
			{
				if (!($class = Loader :: loadClass('CategoryRegistryUtil')))
				{
					pn_exit(pnML('_UNABLETOLOADCLASS', array (
						's' => 'CategoryRegistryUtil'
					)));
				}
				$registeredCats = CategoryRegistryUtil :: getRegisteredModuleCategories('crpVideo', 'crpvideos');
			}
			ObjectUtil :: postProcessExpandedObjectCategories($item['__CATEGORIES__'], $registeredCats);

			if (!CategoryUtil :: hasCategoryAccess($item['__CATEGORIES__'], 'crpVideo'))
				return false;
		}

		return $item;
	}

	/**
	 * get a specific event date
	 *
	 * @param int $videoid item identifier
	 * @param int $dateType date type
	 *
	 * @return string item value
	 */
	function getVideoDate($videoid = null, $dateType = null)
	{
		$pntable = pnDBGetTables();
		$crpvideocolumn = $pntable['crpvideos_column'];

		$queryargs[] = "($crpvideocolumn[videoid] = '" . DataUtil :: formatForStore($videoid) . "')";

		$columnArray = array (
			'videoid',
			'' . $dateType . ''
		);

		$where = null;
		if (count($queryargs) > 0)
		{
			$where = ' WHERE ' . implode(' AND ', $queryargs);
		}

		$item = DBUtil :: selectObject('crpvideos', $where, $columnArray);

		$dateValue = false;
		($item[$dateType]) ? $dateValue = $item[$dateType] : $author = false;

		return $dateValue;
	}

	/**
	 * Update video status
	 *
	 * @param int $videoid item identifier
	 * @param string $obj_status active or pending
	 *
	 * @return bool true on succes
	 */
	function updateStatus($videoid = null, $obj_status = null)
	{
		$obj = array (
			'videoid' => $videoid,
			'obj_status' => $obj_status
		);

		if (!DBUtil :: updateObject($obj, 'crpvideos', '', 'videoid'))
		{
			return false;
		}

		return true;
	}

	/**
	 * Save file into DB
	 */
	function setFile($data = array ())
	{
		$result = -1;

		if (!$data['error'])
		{
			$fd = fopen($data['tmp_name'], "r");
			$file_content = fread($fd, filesize($data['tmp_name']));
			fclose($fd);

			$item = $this->getFile($data['videoid'], $data['document_type']);

			// no empty spaces in filename
			$document['name'] = str_replace(" ", "_", $data['name']);
			$document['content_type'] = $data['type'];
			$document['size'] = $data['size'];
			$document['document_type'] = $data['document_type'];
			$document['videoid'] = $data['videoid'];
			// load binary
			$document['binary_data'] = $file_content;

			if ($item)
			{
				$document['id'] = $item['id'];
				if (!DBUtil :: updateObject($document, 'crpvideo_covers', '', 'id'))
				{
					LogUtil :: registerError(_UPDATEFAILED);
					return false;
				}
				$result = 0;
			}
			elseif (empty ($item))
			{
				if (!DBUtil :: insertObject($document, 'crpvideo_covers', 'id'))
				{
					LogUtil :: registerError(_CREATEFAILED);
					return false;
				}
				$result = DBUtil :: getInsertID('crpvideo_covers', 'id');
			}
			else
				return $result;
		}

		return $result;
	}

	/**
	 * Retrieve binary files
	 */
	function getFile($videoid = null, $file_type = 'image', $load_binary = false)
	{
		$pntable = pnDBGetTables();
		$crpvideocolumn = $pntable['crpvideo_covers_column'];

		$queryargs[] = "($crpvideocolumn[videoid] = '" . DataUtil :: formatForStore($videoid) . "'
												AND $crpvideocolumn[document_type] = '" . DataUtil :: formatForStore($file_type) . "')";

		$where = null;
		if (count($queryargs) > 0)
		{
			$where = ' WHERE ' . implode(' AND ', $queryargs);
		}

		$columnArray = array (
			'id',
			'videoid',
			'document_type',
			'name',
			'content_type',
			'size'
		);
		if ($load_binary)
			array_push($columnArray, "binary_data");

		$file = DBUtil :: selectObject('crpvideo_covers', $where, $columnArray);

		return $file;
	}

	/**
	 * Get image for an event
	 *
	 * @param int $eventid event identifier
	 *
	 */
	function getImage()
	{
		$videoid = pnVarCleanFromInput('videoid');

		$pntable = pnDBGetTables();
		$crpvideocolumn = $pntable['crpvideo_covers_column'];

		$queryargs[] = "($crpvideocolumn[videoid] = '" . DataUtil :: formatForStore($videoid) . "'
												AND $crpvideocolumn[document_type] = 'image')";

		$where = null;
		if (count($queryargs) > 0)
		{
			$where = ' WHERE ' . implode(' AND ', $queryargs);
		}

		$columnArray = array (
			'id',
			'videoid',
			'document_type',
			'name',
			'content_type',
			'size',
			'binary_data'
		);

		$file = DBUtil :: selectObject('crpvideo_covers', $where, $columnArray);
		$modifiedDate = $this->getVideoDate($videoid, 'lu_date');

		// we need a timestamp
		$server_etag = DateUtil :: makeTimestamp($modifiedDate);
		$server_date = gmdate('D, d M Y H:i:s', $server_etag). " GMT";

		// Check cached versus modified date
		$client_etag = $_SERVER['HTTP_IF_NONE_MATCH'];
		$client_date = $_SERVER['HTTP_IF_MODIFIED_SINCE'];

		if(($client_etag == $server_etag) && (!$client_date || ($client_date==$server_date)))
		{
			header("HTTP/1.1 304 Not Modified");
			header("ETag: $server_etag");
			pnShutDown();
		}
		else
		{
			header("Expires: ". gmdate('D, d M Y H:i:s', time() + 24 * 3600). " GMT");
			header('Pragma: cache');
			header('Cache-Control: public, must-revalidate');
			header("ETag: $server_etag");
			header("Last-Modified: ". gmdate('D, d M Y H:i:s', $server_etag). " GMT");
			header("Content-Type: {$file['content_type']}");
			header("Content-Disposition: inline; filename={$file['name']}");
		}

		echo $file['binary_data'];

		pnShutDown();
	}

	/**
	 * delete file
	 *
	 * @param int $file_type file identifier
	 * @param int $eventid event identifier
	 */
	function deleteFile($file_type = null, $videoid = null)
	{
		// Argument check
		if (!$videoid)
			return LogUtil :: registerError(_MODARGSERROR);

		$item = $this->getFile($videoid, $file_type);
		if (!$item)
			return LogUtil :: registerError(_NOSUCHITEM);

		if (!DBUtil :: deleteObjectByID('crpvideo_covers', $item['id'], 'id'))
			return LogUtil :: registerError(_DELETEFAILED);

		return true;
	}

	/**
	 * Verify binary existence
	 *
	 * @param int $eventid event identifier
	 * @param string $documentType tipe of file
	 *
	 * @return int count
	 */
	function existFile($videoid = null)
	{
		$count = DBUtil :: selectObjectCountByID('crpvideo_covers', $videoid, 'id', false);

		return $count > 0;
	}

	/**
	 * Validate submitted data
	 *
	 * @param array data submitted data
	 * @return boolean true if data are OK
	 */
	function validateData(& $data)
	{
		$validateOK = false;

		if (pnModGetVar('crpVideo', 'mandatory_cover') && $data['image']['error'] != UPLOAD_ERR_OK && empty ($data['videoid']))
		{
			LogUtil :: registerError(_CRPVIDEO_ERROR_IMAGE_NO_FILE);
		}
		elseif (($data['image']['error']) && $data['image']['error'] != UPLOAD_ERR_NO_FILE)
		{
			switch ($data['image']['error'])
			{
				case UPLOAD_ERR_INI_SIZE :
				case UPLOAD_ERR_FORM_SIZE :
					LogUtil :: registerError(_CRPVIDEO_ERROR_IMAGE_FILE_SIZE_TOO_BIG);
					break;
				case UPLOAD_ERR_PARTIAL :
				case UPLOAD_ERR_NO_TMP_DIR :
					LogUtil :: registerError(_CRPVIDEO_ERROR_IMAGE_NO_FILE);
					break;
			}
		}
		elseif (!in_array($data['image']['type'], $this->ImageTypes) && $data['image']['error'] != UPLOAD_ERR_NO_FILE)
		{
			LogUtil :: registerError(_CRPVIDEO_IMAGE_INVALID_TYPE);
		}
		elseif (($data['file']['error']) && empty ($data['videoid']))
		{
			switch ($data['file']['error'])
			{
				case UPLOAD_ERR_INI_SIZE :
				case UPLOAD_ERR_FORM_SIZE :
					LogUtil :: registerError(_CRPVIDEO_ERROR_VIDEO_FILE_SIZE_TOO_BIG);
					break;
				case UPLOAD_ERR_PARTIAL :
				case UPLOAD_ERR_NO_TMP_DIR :
				case UPLOAD_ERR_NO_FILE :
					LogUtil :: registerError(_CRPVIDEO_ERROR_VIDEO_NO_FILE);
					break;
			}
		}
		elseif (!in_array($data['file']['type'], $this->VideoTypes) && $data['file']['error'] != UPLOAD_ERR_NO_FILE && $data['source'] == 'video')
		{
			LogUtil :: registerError(_CRPVIDEO_VIDEO_INVALID_TYPE);
		}
		elseif ($data['image']['size'] > pnModGetVar('crpVideo', 'cover_dimension'))
		{
			LogUtil :: registerError(_CRPVIDEO_ERROR_IMAGE_FILE_SIZE_TOO_BIG);
		}
		elseif ($data['file']['size'] > pnModGetVar('crpVideo', 'file_dimension'))
		{
			LogUtil :: registerError(_CRPVIDEO_ERROR_VIDEO_FILE_SIZE_TOO_BIG);
		}
		elseif ($data['external'] && $data['source'] == 'external' && !pnVarValidate($data['external'], 'url'))
		{
			LogUtil :: registerError(_CRPVIDEO_INVALID_URL);
		}
		elseif (empty ($data['title']))
		{
			LogUtil :: registerError(_CRPVIDEO_ERROR_VIDEO_NO_TITLE);
		}
		elseif (empty ($data['author']))
		{
			LogUtil :: registerError(_CRPVIDEO_ERROR_VIDEO_NO_AUTHOR);
		}
		elseif (empty ($data['content']))
		{
			LogUtil :: registerError(_CRPVIDEO_ERROR_VIDEO_NO_CONTENT);
		}
		elseif (empty ($data['__CATEGORIES__']['Main']) && pnModGetVar('crpVideo','enablecategorization'))
		{
			LogUtil :: registerError(_CRPVIDEO_ERROR_VIDEO_NO_CATEGORY);
		}
		else
		{
			$validateOK = true;
		}

		return $validateOK;
	}

	function getUploaders($startnum=1, $category= null, $clear= false, $ignoreml= true, $modvars= array (), $mainCat,
												$active= 'A', $interval = null,	$sortOrder= 'ASC', $orderBy = 'title', $uid = false)
	{

		$pntable = pnDBGetTables();
		$videoscolumn = $pntable['crpvideos_column'];
		$nowDate = DateUtil::getDatetime();

		$queryargs = array ();
		if (pnConfigGetVar('multilingual') == 1 && !$ignoreml)
		{
			$queryargs[] = "($videoscolumn[language]='" . DataUtil :: formatForStore(pnUserGetLang()) . "' OR $videoscolumn[language]='')";
		}
		if ($active)
		{
			$queryargs[] = "($videoscolumn[obj_status]='" . DataUtil :: formatForStore($active) . "')";
		}
		if ($uid)
		{
			$queryargs[] = "($videoscolumn[cr_uid]='" . DataUtil :: formatForStore($uid) . "')";
		}
		if ($interval)
		{
			$intervaltime = time() - $interval * 86400;
			$intervalDate = DateUtil :: getDatetime($intervaltime);
			$queryargs[]= "($videoscolumn[cr_date] < '" . DataUtil :: formatForStore($nowDate) . "' " .
			"AND $videoscolumn[cr_date] > '" . DataUtil :: formatForStore($intervalDate) . "')";
		}

		$where = null;
		if (count($queryargs) > 0)
		{
			$where = ' WHERE ' . implode(' AND ', $queryargs);
		}

		($orderBy!='uploads')?$orderBy=$videoscolumn[$orderBy]:'';
		$orderby= "GROUP BY $videoscolumn[cr_uid] ORDER BY $orderBy $sortOrder";

		$sqlStatement= "SELECT $videoscolumn[cr_uid] as cr_uid, " .
			"COUNT(*) as uploads " .
			"FROM $pntable[crpvideos] " .
			"$where $orderby";

		// get the objects from the db
		$res= DBUtil :: executeSQL($sqlStatement, $startnum-1, $modvars['itemsperpage'], true, true);

		$objArray= DBUtil :: marshallObjects($res, array (
			'cr_uid', 'uploads'
		), true);

		// Return the items
		return $objArray;
	}
}
?>
