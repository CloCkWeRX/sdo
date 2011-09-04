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
|         Simon Laws,                                                         |
|         Rajini Sivaram                                                      |
+-----------------------------------------------------------------------------+
$Id: ServiceDescriptionGenerator.php 234945 2007-05-04 15:05:53Z mfp $
*/

require "SCA/Bindings/xmlrpc/Das.php";

if ( ! class_exists('SCA_Bindings_Xmlrpc_ServiceDescriptionGenerator', false) ) {

    class SCA_Bindings_Xmlrpc_ServiceDescriptionGenerator
    {

        public function generate($service_description)
        {
            SCA::$logger->log( "Entering");

            try
            {
                $xmlrpc_server   = xmlrpc_server_create();
                $aliases = array();
                $this->addIntrospectionData($xmlrpc_server, $service_description, $aliases);

                $describeMethods = <<< END
<?xml version="1.0" ?>
<methodCall>
<methodName>system.describeMethods</methodName>
<params/>
</methodCall>
END;
 

                $desc = xmlrpc_server_call_method($xmlrpc_server, $describeMethods, null);

                xmlrpc_server_destroy($xmlrpc_server);

                header('Content-type: text/xml');
                echo $desc;

            } catch (Exception $se ) {
                echo $se->exceptionString() . "\n" ;
            }

        }

        /**
         * Generate type information to add introspection data.
         *
         * @param string $namespace
         * @param string $type_name
         * @param array  $type_list (Reference parameter containing type list)
         * @param object $xmlrpc_das
         */
        private function generateType($namespace, $type_name, &$type_list, $xmlrpc_das)
        {

            if ($type_name == null || strlen($type_name) == 0)
                return;

            $composite_type_name = $namespace . ":" . $type_name;

            // ensure that types are only written out once
            if ( !array_key_exists($composite_type_name, $type_list) ) {

                $type                             = new stdClass;
                $type_list[$composite_type_name] = $type;

                $type->name                       = $type_name;
                $type->typedef                    = new stdClass;
                $type->typedef->properties        = array();

                // create a data object of the required type
                $do = $xmlrpc_das->getXmlDas()->createDataObject($namespace, $type_name);

                // get the type information for the data object
                $reflection = new SDO_Model_ReflectionDataObject($do);
                $do_type    = $reflection->getType();

                // test to make sure this is not a primitive type
                if ($do_type->isDataType()) {
                    return;
                }

                // iterate over the properties of this type
                foreach ($do_type->getProperties() as $property) {
                    $property_name           = $property->getName();
                    $property_type           = $property->getType();
                    $property_type_name      = $property_type->getName();
                    $property_type_namespace = $property_type->getNamespaceURI();

                    // create the entry for the type
                    $property_object         = new stdClass;
                    $property_object->name   = $property_name;

                    // convert from SDO property type names to XmlRpc property type names
                    $property_object->type   = $this->sdoTypeToXmlRpcType($property_type_name);

                    // work out if this is an array or not
                    if ( $property->isMany() ) {
                        $property_object->type .= " []";
                    }

                    // store away the property in the typedef
                    $type->typedef->properties[] = $property_object;

                    // If this type is a complex type then recurse.
                    if (!$property_type->isDataType()) {
                        $this->generateType($property_type_namespace, $property_type_name, $type_list, $xmlrpc_das);
                    }
                }

                // TODO - what to do about non-containment references
            }
        }

        /**
         * Generate method and type information to add introspection data.
         * The information required is generated from annotations.
         * All methods are registered with the server, and introspection data
         * is added to the server.
         *
         * @param resource $xmlrpc_server
         * @param array $service_description
         * @param array $method_aliases
         * @param object $xmlrpc_das
         * @return string  Method description
         */
        public function addIntrospectionData($xmlrpc_server, $service_description, &$method_aliases, $xmlrpc_das=null) {

            if ($xmlrpc_das == null) {
                $xsds     = SCA_Helper::getAllXsds($service_description->class_name);
                $xmlrpc_das = new SCA_Bindings_Xmlrpc_DAS();
                foreach ($xsds as $index => $xsds) {                   
                    list($namespace, $xsdfile) = $xsds;
                    if (SCA_Helper::isARelativePath($xsdfile)) {
                        $xsd = SCA_Helper::constructAbsolutePath($xsdfile, $service_description->class_name);
                        $xmlrpc_das->addTypesXsdFile($xsd);
                    }
                }
            }

            $type_list = array();


            $methodDesc = <<< END
<?xml version='1.0'?>

<introspection version='1.0'>

<methodList>
END;


            foreach($service_description->operations as $methodName => $methodInfo) {



                $methodParams = "";
                $methodReturn = "";
                
                if (array_key_exists("name", $methodInfo) && array_key_exists("name", $methodInfo["name"]) &&
                    $methodInfo["name"]["name"] != null && strlen($methodInfo["name"]["name"]) > 0) {
                    
                    $xmlrpcMethodName = $methodInfo["name"]["name"];
                    $method_aliases[$xmlrpcMethodName] = $methodName;
                } else {
                    $xmlrpcMethodName = $methodName;
                }


                xmlrpc_server_register_method($xmlrpc_server, $xmlrpcMethodName, $methodName);

                if (array_key_exists("parameters", $methodInfo) && $methodInfo["parameters"] != null) {
                    foreach ($methodInfo["parameters"] as $param) {

                        $paramName = $param["name"];

                        if (array_key_exists('objectType', $param)) {
                            $paramType = $param["objectType"];
                            $this->generateType($param["namespace"], $param["objectType"], $type_list, $xmlrpc_das);
                        }
                        else {
                            $paramType = $this->sdoTypeToXmlRpcType($param["type"]);
                        }

                        $methodParams = $methodParams.<<<END

                            <value type='$paramType' desc='$paramName'>
                            </value>
END;

}
                }

                if (array_key_exists("return", $methodInfo) && $methodInfo["return"] != null) {
                    foreach ($methodInfo["return"] as $ret) {

                        if (array_key_exists('objectType', $ret)) {
                            $retType = $ret["objectType"];
                            $this->generateType($ret["namespace"], $ret["objectType"], $type_list, $xmlrpc_das);
                        }
                        else {
                            $retType = $this->sdoTypeToXmlRpcType($ret["type"]);
                        }

                        $methodReturn = $methodReturn.<<<END

                            <value type='$retType' desc='return'>
                            </value>
END;
}
                }

                $methodDesc = $methodDesc.<<< END

    <methodDescription name='$xmlrpcMethodName'>
        <author></author>
        <purpose></purpose>
        <version></version>
        <signatures>
            <signature>
                <params>
                    $methodParams
                </params>
                <returns>
                    $methodReturn
                </returns>
            </signature>
        </signatures>

    </methodDescription>

END;
}



$methodDesc = $methodDesc."</methodList>\n";

if (count($type_list) > 0) {
    $methodDesc = $methodDesc."<typeList>\n";


    foreach($type_list as $type) {
        $methodDesc = $methodDesc.<<< END

    <typeDescription name='$type->name' basetype='struct' desc='$type->name'>
END;
        foreach($type->typedef->properties as $prop) {
            $methodDesc = $methodDesc.<<< END

        <value type='$prop->type' name='$prop->name'></value>

END;
}

$methodDesc = $methodDesc.<<< END

    </typeDescription>
END;

}



$methodDesc = $methodDesc."</typeList>\n";
}

$methodDesc = $methodDesc."</introspection>\n";


            $descArray = xmlrpc_parse_method_descriptions($methodDesc);
            xmlrpc_server_add_introspection_data($xmlrpc_server, $descArray);               

        }


        /**
         * Convert SDO type to XMLRPC type
         *
         * @param string $sdoTypeToXmlRpcType
         * @return string XMLRPC type
         */
        private function sdoTypeToXmlRpcType($sdo_type_name) {

            $xmlrpc_type_name = $sdo_type_name;

            switch ($sdo_type_name) {
                case "Boolean":
                    $xmlrpc_type_name = "boolean";
                    break;
                case "Byte":
                case "Bytes":
                case "Character":
                case "Date":
                case "String":
                case "URI":
                    $xmlrpc_type_name = "string";
                    break;
                case "BigDecimal":
                case "BigInteger":
                case "Double":
                case "Float":
                case "Long":
                    $xmlrpc_type_name = "double";
                    break;
                case "Integer":
                case "Short":
                    $xmlrpc_type_name = "int";
                    break;
            }

            return $xmlrpc_type_name;
        }

    }
}

?>
