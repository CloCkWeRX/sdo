<?php

if ( ! class_exists('SCA_RSSProxy', false)) {

    class SCA_Bindings_rss_RssDas
    {

        private static $xmldas = null;

        public static function getXmlDas() {
            if (is_null(self::$xmldas)) {
                self::$xmldas = SDO_DAS_XML::create(dirname(__FILE__) . '/rss2.0.xsd');
            }
            return self::$xmldas;
        }

        public static function toXml($sdo) {
            $type = $sdo->getTypeName();
            $xmldas = self::getXmlDas();
            $xdoc   = $xmldas->createDocument('', $type, $sdo);
            $xmlstr = $xmldas->saveString($xdoc);
            return $xmlstr;
        }

        public static function fromXml($xml) {
            $xmldas = self::getXmlDas();
            $doc = $xmldas->loadString($xml);
            $ret = $doc->getRootDataObject();
            return $ret;
        }


    }
}

?>