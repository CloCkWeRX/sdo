<?php
/*
+-----------------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2007.                                         |
| All Rights Reserved.                                                        |
+-----------------------------------------------------------------------------+
| Licensed under the Apache License, Version 2.0 (the "License"); you may not |
| use this file except in compliance with the License. You may obtain a copy  |
| of the License at -                                                         |
|                                                                             |
|                   http://www.apache.org/licenses/LICENSE-2.0                |
|                                                                             |
| Unless required by applicable law or agreed to in writing, software         |
| distributed under the License is distributed on an "AS IS" BASIS, WITHOUT   |
| WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.            |
| See the License for the specific language governing  permissions and        |
| limitations under the License.                                              |
+-----------------------------------------------------------------------------+
| Author: Graham Charters,                                                    |
|         Megan Beynon,                                                       |
|         Caroline Maynard                                                    |
+-----------------------------------------------------------------------------+
$Id: RssTypes.php 238265 2007-06-22 14:32:40Z mfp $
*/

/**
 * To represent an image in RSS
 *    
 * <xs:element name="url" type="urlType" />
 * <xs:element name="title" type="xs:string" />
 * <xs:element name="link" type="urlType" />
 * <xs:element name="width" type="xs:positiveInteger" minOccurs="0" />
 * <xs:element name="height" type="xs:positiveInteger" minOccurs="0" />
 * <xs:element name="description" type="xs:string" minOccurs="0" />
 *
 */
class Image {
    
    /**
     * Enter description here...
     *
     * @var string
     */
    public $url;

    /**
     * Enter description here...
     *
     * @var string
     */
    public $title;

    /**
     * Enter description here...
     *
     * @var string
     */
    public $link;

    /**
     * Enter description here...
     *
     * @var integer
     */
    public $width;

    /**
     * Enter description here...
     *
     * @var integer
     */
    public $height;

    /**
     * Enter description here...
     *
     * @var string
     */
    public $description;
    
}

/**
 * To represent an enclosure in RSS
 * 
 * <xs:attribute name="url" type="xs:string"/>
 * <xs:attribute name="length" type="xs:positiveInteger"/>
 * <xs:attribute name="type" type="xs:string"/>
 */
class Enclosure {
    

    /**
     * Enter description here...
     *
     * @var string
     */
    public $url;

    /**
     * Enter description here...
     *
     * @var integer
     */
    public $length;

    /**
     * Enter description here...
     *
     * @var string
     */
    public $type;
    
}

/**
 * To represent a text input in RSS
 * 
 * <xs:element name="title" type="xs:string" />
 * <xs:element name="description" type="xs:string" />
 * <xs:element name="name" type="xs:string" />
 * <xs:element name="link" type="urlType" />
 */
class TextInput {
    

    /**
     * Enter description here...
     *
     * @var string
     */
    public $title;

    /**
     * Enter description here...
     *
     * @var string
     */
    public $description;

    /**
     * Enter description here...
     *
     * @var string
     */
    public $name;

    /**
     * Enter description here...
     *
     * @var string
     */
    public $link;
    
}


/**
 * To represent an item in RSS
 *
 * <xs:element name="title" type="xs:string" minOccurs="0" />
 * <xs:element name="link" type="urlType" minOccurs="0" />
 * <xs:element name="description" type="xs:string" minOccurs="0" />
 * <xs:element name="author" type="emailType" minOccurs="0" />
 * <xs:element name="category" type="categoryType" minOccurs="0" maxOccurs="unbounded" />
 * <xs:element name="comments" type="urlType" minOccurs="0" />
 * <xs:element name="enclosure" type="enclosureType" minOccurs="0" />
 * <xs:element name="guid" type="guidType" minOccurs="0" />
 * <xs:element name="pubDate" type="xs:string" minOccurs="0" />
 * <xs:element name="source" type="sourceType" />
 */
class Item {
    

    /**
     * Enter description here...
     *
     * @var string
     */
    public $title;

    /**
     * Enter description here...
     *
     * @var string
     */
    public $link;

    /**
     * Enter description here...
     *
     * @var string
     */
    public $description;

    /**
     * Enter description here...
     *
     * @var string
     */
    public $author;

    /**
     * Enter description here...
     *
     * @var array string
     */
    public $category;

    /**
     * Enter description here...
     *
     * @var string
     */
    public $comments;

    /**
     * Enter description here...
     *
     * @var Enclosure
     */
    public $enclosure;

    /**
     * Enter description here...
     *
     * @var string
     */
    public $guid;

    /**
     * Enter description here...
     *
     * @var string
     */
    public $pubDate;

    /**
     * Enter description here...
     *
     * @var Source
     */
    public $source;
    
}

/**
 * To represent a source item in RSS
 * 
 * <xs:extension base="xs:string">
 *   <xs:attribute name="url" type="urlType"/>
 * </xs:extension>
 *
 */
class Source {


    /**
     * Enter description here...
     *
     * @var string
     */
    public $value;

    /**
     * Enter description here...
     *
     * @var string
     */
    public $url;
}

/**
 * To represent a channel in RSS
 * 
 * <xs:element name="title" type="xs:string" />
 * <xs:element name="link" type="urlType" />
 * <xs:element name="description" type="xs:string" />
 * <xs:element name="language" type="xs:string"/>
 * <xs:element name="copyright" type="xs:string" minOccurs="0" />
 * <xs:element name="managingEditor" type="emailType" minOccurs="0" />
 * <xs:element name="webMaster" type="emailType" minOccurs="0" />
 * <xs:element name="pubDate" type="xs:string" minOccurs="0" /> <!-- TODO: date of format: Sat, 07 Sep 2002 09:42:31 GMT -->
 * <xs:element name="lastBuildDate" type="xs:string" minOccurs="0" /> <!-- TODO: date of format: Sat, 07 Sep 2002 09:42:31 GMT -->
 * <xs:element name="category" type="categoryType" minOccurs="0" maxOccurs="unbounded" />
 * <xs:element name="generator" type="xs:string" minOccurs="0" />
 * <xs:element name="docs" type="urlType" minOccurs="0" />
 * <xs:element name="cloud" type="cloudType" minOccurs="0" /> <!-- TODO: proper cloud support -->
 * <xs:element name="ttl" type="xs:positiveInteger" minOccurs="0" />
 * <xs:element name="image" type="imageType" minOccurs="0" />
 * <xs:element name="rating" type="picsType" minOccurs="0" />
 * <xs:element name="textInput" type="textInputType" minOccurs="0" />
 * <xs:element name="skipHours" type="skipHoursType" minOccurs="0" />
 * <xs:element name="skipDays" type="skipDaysType" minOccurs="0" />
 * <xs:element name="item" type="itemType" minOccurs="0" maxOccurs="unbounded" />

 */
class Channel {
   

    /**
     * Enter description here...
     *
     * @var string
     */
    public $title;

    /**
     * Enter description here...
     *
     * @var string
     */
    public $link;

    /**
     * Enter description here...
     *
     * @var string
     */
    public $description;

    /**
     * Enter description here...
     *
     * @var string
     */
    public $language;

    /**
     * Enter description here...
     *
     * @var string
     */
    public $copyright;

    /**
     * Enter description here...
     *
     * @var string
     */
    public $managingEditor;

    /**
     * Enter description here...
     *
     * @var string
     */
    public $webMaster;

    /**
     * Enter description here...
     *
     * @var string
     */
    public $pubDate;

    /**
     * Enter description here...
     *
     * @var string
     */
    public $lastBuildDate;

    /**
     * Enter description here...
     *
     * @var array string
     */
    public $category;

    /**
     * Enter description here...
     *
     * @var string
     */
    public $generator;

    /**
     * Enter description here...
     *
     * @var string
     */
    public $docs;

    /**
     * Enter description here...
     *
     * @var string
     */
    public $cloud;

    /**
     * Enter description here...
     *
     * @var string
     */
    public $ttl;

    /**
     * Enter description here...
     *
     * @var Image
     */
    public $image;

    /**
     * Enter description here...
     *
     * @var string
     */
    public $rating;

    /**
     * Enter description here...
     *
     * @var TextInput
     */
    public $textInput;

    /**
     * Enter description here...
     *
     * @var string
     */
    public $skipHours;

    /**
     * Enter description here...
     *
     * @var string
     */
    public $skipDays;

    /**
     * Enter description here...
     *
     * @var array Item
     */
    public $item;

}

?>