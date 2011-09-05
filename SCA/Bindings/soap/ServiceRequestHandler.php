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
| Author: Graham Charters,                                                    |
|         Matthew Peters,                                                     |
|         Megan Beynon,                                                       |
|         Chris Miller,                                                       |
|         Caroline Maynard,                                                   |
|         Simon Laws                                                          |
+-----------------------------------------------------------------------------+
$Id: ServiceRequestHandler.php 241789 2007-08-24 15:20:26Z mfp $
*/

include "SCA/Bindings/soap/Wrapper.php";
include "SCA/Bindings/soap/Mapper.php";
include "SCA/Bindings/soap/ServiceDescriptionGenerator.php";

if ( ! extension_loaded('soap')) {
trigger_error("Cannot use SCA soap binding as soap extension is not loaded",E_USER_WARNING);
return;
}



class SCA_Bindings_soap_ServiceRequestHandler
{

    public function handle($calling_component_filename, $service_description)
    {
        SCA::$logger->log('Entering');

        $class_name = SCA_Helper::guessClassName($calling_component_filename);

        $wsdl_filename = str_replace('.php', '.wsdl', $calling_component_filename);

        if (!file_exists($wsdl_filename)) {
            file_put_contents($wsdl_filename,
            SCA_Bindings_soap_ServiceDescriptionGenerator::generateDocumentLiteralWrappedWsdl($service_description));
        }

        $handler = new SCA_Bindings_soap_Mapper("SoapServer");
        try {
            SCA::$logger->log("Wsdl Type = {$wsdl_filename}");
            $handler->setWSDLTypes($wsdl_filename);
        } catch( SCA_RuntimeException $wsdlerror ) {
            echo $wsdlerror->exceptionString() . "\n";
        }

        if (SCA_Helper::wsdlWasGeneratedForAnScaComponent($wsdl_filename)) {
            $options = $service_description->binding_config;
            $options['typemap'] = $handler->getTypeMap();
            $server = new SoapServer($wsdl_filename, $options);
        } else {
            $server = new SoapServer($wsdl_filename, $service_description->binding_config);
        }

        $class_name    = SCA_Helper::guessClassName($calling_component_filename);
        $service_wrapper = new SCA_Bindings_soap_Wrapper($class_name, $handler);
        $server->setObject($service_wrapper);
        global $HTTP_RAW_POST_DATA;
        $server->handle($HTTP_RAW_POST_DATA);
    }

}
