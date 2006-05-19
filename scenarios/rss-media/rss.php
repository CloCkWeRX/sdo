<?php
/*
+----------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2006.                                  |
| All Rights Reserved.                                                 |
+----------------------------------------------------------------------+
|                                                                      |
| Licensed under the Apache License, Version 2.0 (the "License"); you  |
| may not use this file except in compliance with the License. You may |
| obtain a copy of the License at                                      |
| http://www.apache.org/licenses/LICENSE-2.0                           |
|                                                                      |
| Unless required by applicable law or agreed to in writing, software  |
| distributed under the License is distributed on an "AS IS" BASIS,    |
| WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
| implied. See the License for the specific language governing         |
| permissions and limitations under the License.                       |
+----------------------------------------------------------------------+
| Author: Matthew Peters, Graham Charters                              |
+----------------------------------------------------------------------+
$Id$
*/
header('Content-type: application/xml');

/**
 * Load the RSS 'template'
 */
$rss_xmldas = SDO_DAS_XML::create('./rss.xsd');
$rss_document = $rss_xmldas->loadFile('./base.xml');
$rss_data_object = $rss_document->getRootDataObject();

/**
 * Set the RSS channel values
 */
$channel = $rss_data_object->channel;
$channel->lastBuildDate = date("D\, j M Y G:i:s T");
$channel->pubDate = date("D\, j M Y G:i:s T");
$channel->link = 'http://'
			. $_SERVER["HTTP_HOST"]
			. dirname($_SERVER["REQUEST_URI"])
			. '/newitem.php';
$channel->ttl = '1';

/**
 * Load the blog data
 */
$blog_xmldas      = SDO_DAS_XML::create('./blog.xsd');
$blog_document    = $blog_xmldas->loadFile('./blog.xml');
$blog_data_object = $blog_document->getRootDataObject();

/**
 * Iterate through the blog entries converting
 * them to RSS items.
 */
foreach ($blog_data_object->item as $item) {
	$new_channel_item 				= $channel->createDataObject('item');
	$new_channel_item->createDataObject('guid');
	$new_channel_item->createDataObject('enclosure');

	$new_channel_item->title 		= $item->title;
	$new_channel_item->description 	= $item->description;
	$new_channel_item->pubDate 		= $item->date;
	$new_channel_item->link 		= 'http://'
								. $_SERVER["HTTP_HOST"]
								. dirname($_SERVER["REQUEST_URI"])
								. '/showitem.php?id=' . $item->guid;
	$new_channel_item->guid->value 	= md5($new_channel_item->pubDate);
	$new_channel_item->guid->isPermaLink = false;

	/**
	 * Add a link for any mov attachments
	 */
	if (isset($item->enclosure)) {
		$new_channel_item->createDataObject('enclosure');
		$new_channel_item->enclosure->url = 'http://'
									. $_SERVER["HTTP_HOST"]
									. dirname($_SERVER["REQUEST_URI"])
									. "/media/"
									. $item->enclosure;
		$file_handle 		= fopen("./media/" . $item->enclosure,'r');
		$file_info 			= fstat($file_handle);
		$file_size 			= $file_info['size'];

		$new_channel_item->enclosure->length = $file_size;
		$new_channel_item->enclosure->type = 'video/mov';
	}

}

/**
 * Serve up the feed
 */
print $rss_xmldas->saveString($rss_document,2);
?> 