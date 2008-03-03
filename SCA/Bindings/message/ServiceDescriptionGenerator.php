<?php
/*
+-----------------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2006, 2007.                                   |
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
| Author: Wangkai Zai                                                         |
|                                                                             |
+-----------------------------------------------------------------------------+
*/
require "SCA/Bindings/soap/ServiceDescriptionGenerator.php";

if ( ! class_exists('SCA_Bindings_message_ServiceDescriptionGenerator', false) ) {

    class SCA_Bindings_message_ServiceDescriptionGenerator
    {
        public function generate($service_description)
        {
            SCA::$logger->log( "Entering");

            try {
                if ( isset($_GET['wsdl']) ) {
                    /*if request url end with ?wsdl, generate a wsdl file
                    using the ServiceDescriptionGenerator from soap binding*/
                    $str = SCA_Bindings_soap_ServiceDescriptionGenerator::
                        generateDocumentLiteralWrappedWsdl($service_description);
                }else{
                    $str =  $this->generateMSD($service_description);
                }

                /* causes a php warning 'Cannot modify header information'
                    when executing a phpunit testcase*/ 
                //header('Content-type: text/xml'); 
                echo $str;
                SCA::$logger->log('Exiting having generated wsdl');
            }
            catch (SCA_RuntimeException $se )
            {
                echo $se . "\n" ;
            } catch( SDO_DAS_XML_FileException $e) {
                echo "{$e->getMessage()} in {$e->getFile()}";
            }
            return;
        }

         public static function generateMSD($service_desc)
         {

            /*Get a DAS*/
            $xmldas = SDO_DAS_XML::create(dirname(__FILE__)."/MessageServiceDescription.xsd");
            $msd_doc = $xmldas->createDocument();
            $msdDataObject = $msd_doc->getRootDataObject() ;

             // Guess a queue name
            if(!isset($service_desc->binding_config['destination'])){
                $service_desc->binding_config['destination'] 
                    = 'queue://' . $service_desc->class_name;
                SCA::$logger->log("Target queue not specified, SCA will use class name as default queue name");
            }

            /*construct a sdo */
            self::parseBindingConfig($service_desc->binding_config, $msdDataObject);

            /*userid and password will not output to the description file*/
            if (isset($msdDataObject->connectionFactory->userid)) {
                unset($msdDataObject->connectionFactory->userid);
            }
            if (isset($msdDataObject->connectionFactory->password)) {
                unset($msdDataObject->connectionFactory->password);
            }

            $str = $xmldas->saveString($msd_doc, 2);
            return $str;

         }

         /**
          * constructs a sdo data object which contains binding config infomation
          * 
          * @param array $binding_config   list of binding config parameters
          * @param object $msdDataObject   existing msd, will be overridden by new $binding_config
          * @return object                 modified msd
          */
         public static function parseBindingConfig($binding_config, $msdDataObject = null)
         {
             
             if ( is_null($msdDataObject) ) {
                 if ( array_key_exists('msd',$binding_config) ) {
                     /*load binding config information from the MPD file*/
                     $xmldas = SDO_DAS_XML::create(dirname(__FILE__)."/MessageServiceDescription.xsd");
                     /*Assumed the value is an Absolute path to a MSD file*/
                     $xdoc = $xmldas->loadFile($binding_config['msd']);
                     $msdDataObject = $xdoc->getRootDataObject();
                 }else{
                     /*create an empty document*/
                     $xmldas = SDO_DAS_XML::create(dirname(__FILE__)."/MessageServiceDescription.xsd");
                     $msd_doc = $xmldas->createDocument();
                     $msdDataObject = $msd_doc->getRootDataObject() ;
                 }
             }

             foreach ($binding_config as $key => $value){
                switch($key){
                /*creating connection factory elements*/
                case 'protocol':
                case 'host':
                case 'port':
                case 'broker':
                case 'endpoints':
                case 'targetchain':
                case 'bus':
                case 'userid':
                case 'password':
                    if (!isset($connFactory)) {
                        $connFactory = $msdDataObject->createDataObject('connectionFactory');
                    }
                    $connFactory->$key = $value;
                    break;

                /*Correlation Scheme*/
                case 'correlationScheme':
                    $msdDataObject->correlationScheme = $value;
                    break;

                /*response queue url*/
                case 'response':
                    if (!isset($response)) {
                        $response = $msdDataObject ->createDataObject('response');
                    }
                    $response->destination = $value;
                    break;

                /*response queue connection factory*/
                case (strncmp($key, 'response.', 9) == 0):
                    if(!isset($resp_ConnFactory)){
                        if (!isset($response)) {
                            $response = $msdDataObject->createDataObject('response');
                        }
                        $resp_ConnFactory = $response->createDataObject('connectionFactory');
                    }
                    /* delete prefix 'response.' */
                    $new_key = substr_replace($key, '', 0, 9);
                    $resp_ConnFactory->$new_key = $value ;
                    break;

                /*JMS heahers for all operations*/
                case 'JMSType':
                case 'JMSCorrelationID':
                case 'JMSDeliveryMode':
                case 'JMSTimeToLive':
                case 'JMSPriority':
                    if (!isset($headers)) {
                        $headers = $msdDataObject ->createDataObject('headers');
                    }
                    $headers->$key = $value;
                    break;
                default:
                    $msdDataObject->$key = $value ;
                }
             }
             return $msdDataObject;
         }

    }
}

?>