<?php
/**
 * +-----------------------------------------------------------------------------+
 * | (c) Copyright IBM Corporation 2007.                                         |
 * | All Rights Reserved.                                                        |
 * +-----------------------------------------------------------------------------+
 * | Licensed under the Apache License, Version 2.0 (the "License"); you may not |
 * | use this file except in compliance with the License. You may obtain a copy  |
 * | of the License at -                                                         |
 * |                                                                             |
 * |                   http://www.apache.org/licenses/LICENSE-2.0                |
 * |                                                                             |
 * | Unless required by applicable law or agreed to in writing, software         |
 * | distributed under the License is distributed on an "AS IS" BASIS, WITHOUT   |
 * | WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.            |
 * | See the License for the specific language governing  permissions and        |
 * | limitations under the License.                                              |
 * +-----------------------------------------------------------------------------+
 * | Author: Graham Charters,                                                    |
 * |         Megan Beynon,                                                       |
 * |         Caroline Maynard                                                    |
 * +-----------------------------------------------------------------------------+
 * $Id: RssDas.php 238265 2007-06-22 14:32:40Z mfp $
 *
 * PHP Version 5
 *
 * @category SCA
 * @package  SCA_SDO
 * @author   Simon Laws <slaws@php.net>
 * @license  Apache http://www.apache.org/licenses/LICENSE-2.0
 * @link     http://www.osoa.org/display/PHP/
 */

/**
 * SCA_Bindings_Rss_RssDas
 *
 * @category SCA
 * @package  SCA_SDO
 * @author   Simon Laws <slaws@php.net>
 * @license  Apache http://www.apache.org/licenses/LICENSE-2.0
 * @link     http://www.osoa.org/display/PHP/
 */
class SCA_Bindings_Rss_RssDas
{

    protected static $xmldas = null;

    /**
     * Get DAS
     *
     * @return object
     */
    public static function getXmlDas()
    {
        if (is_null(self::$xmldas)) {
            self::$xmldas = SDO_DAS_XML::create(dirname(__FILE__) . '/rss2.0.xsd');
        }
        return self::$xmldas;
    }

    /**
     * To XML
     *
     * @param SDO $sdo SDO
     *
     * @return string
     */
    public static function toXml($sdo)
    {
        $type = $sdo->getTypeName();
        $xmldas = self::getXmlDas();
        $xdoc   = $xmldas->createDocument('', $type, $sdo);
        $xmlstr = $xmldas->saveString($xdoc);
        return $xmlstr;
    }

    /**
     * From XML
     *
     * @param string $xml XML
     *
     * @return object
     */
    public static function fromXml($xml)
    {
        $xmldas = self::getXmlDas();
        $doc = $xmldas->loadString($xml);
        $ret = $doc->getRootDataObject();
        return $ret;
    }
}
