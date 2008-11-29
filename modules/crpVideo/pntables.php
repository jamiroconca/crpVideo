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

function crpVideo_pntables()
{
    // Initialise table array
    $pntable = array();

    // Full table definition
    $pntable['crpvideos'] = DBUtil::getLimitedTablename('crpvideos');

    $pntable['crpvideos_column'] = array ('videoid'         => 'pn_videoid',
                                      'title'          => 'pn_title',
                                      'urltitle'       => 'pn_urltitle',
                                      'source'       	 => 'pn_source',
                                      'urlvideo'       => 'pn_urlvideo',
                                      'external'       => 'pn_external',
                                      'pathvideo'      => 'pn_pathvideo',
                                      'author'       	 => 'pn_author',
                                      'tags'       	 	 => 'pn_tags',
                                      'content'        => 'pn_content',
                                      'counter'        => 'pn_counter',
                                      'displaywrapper' => 'pn_displaywrapper',
                                      'language'       => 'pn_language');

    $pntable['crpvideos_column_def'] = array('videoid'         => 'I AUTOINCREMENT PRIMARY',
                                         'title'          => "X NOTNULL DEFAULT ''",
                                         'urltitle'       => "X NOTNULL DEFAULT ''",
                                         'source'       	=> "C(32) NOTNULL DEFAULT ''",
                                         'urlvideo'       => "C(255) NOTNULL DEFAULT ''",
                                         'external'       => "X NOTNULL DEFAULT ''",
                                         'pathvideo'      => "C(255) NOTNULL DEFAULT ''",
                                         'author'       	=> "C(255) NOTNULL DEFAULT ''",
                                         'tags'           => "C(48) NOTNULL DEFAULT ''",
                                         'content'        => "X NOTNULL DEFAULT ''",
                                         'counter'        => "I NOTNULL DEFAULT 0",
                                         'displaywrapper' => "I1 NOTNULL DEFAULT '1'",
                                         'language'       => "C(30) NOTNULL DEFAULT ''");

		$pntable['crpvideo_covers'] = DBUtil :: getLimitedTablename('crpvideo_covers');
		$pntable['crpvideo_covers_column'] = array (
				'id'						=> 'pn_id',
				'videoid'				=> 'pn_videoid',
				'document_type'	=> 'pn_document_type',
				'name'					=> 'pn_name',
				'content_type'	=> 'pn_content_type',
				'size'					=> 'pn_size',
				'binary_data'		=> 'pn_binary_data');
		$pntable['crpvideo_covers_column_def'] = array (
			'id' 						=> 'I(11) AUTOINCREMENT PRIMARY',
			'videoid' 			=> "I(11) NOTNULL DEFAULT 0",
			'document_type' => "C(255) NOTNULL DEFAULT ''",
			'name' 					=> "C(255) NOTNULL DEFAULT ''",
			'content_type'	=> "C(255) NOTNULL DEFAULT ''",
			'size' 					=> "I NOTNULL DEFAULT 0",
			'binary_data' 	=> "B NOTNULL DEFAULT ''"
		);

    // Enable categorization services
    $pntable['crpvideos_db_extra_enable_categorization'] = pnModGetVar('crpVideo', 'enablecategorization');
    $pntable['crpvideos_primary_key_column'] = 'videoid';

    // add standard data fields
    ObjectUtil::addStandardFieldsToTableDefinition ($pntable['crpvideos_column'], 'pn_');
    ObjectUtil::addStandardFieldsToTableDataDefinition($pntable['crpvideos_column_def']);

    return $pntable;
}

?>