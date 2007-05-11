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
| Author: Graham Charters, Caroline Maynard                                   |
+-----------------------------------------------------------------------------+
$Id$
*/

require 'SCA/SCA_Exceptions.php';
require 'SCA/SCA_Helper.php';
require 'SCA/Bindings/soap/Proxy.php';

if ( ! class_exists('SCA_Bindings_ebaysoap_Proxy', false) ) {

    class SCA_Bindings_ebaysoap_Proxy extends SCA_Bindings_soap_Proxy {

        public function __construct($absolute_path_to_target_wsdl,
                                    $immediate_caller_directory, 
                                    $binding_config) {
            SCA::$logger->log('Entering');

            if (!extension_loaded('openssl')) {
                SCA::$logger->log('eBay soap binding requires the openssl extension, but it is not loaded.');
                throw new SCA_RuntimeException('eBay soap binding requires the openssl extension, but it is not loaded.');
            }

            // Merge values from a config file and annotations
            if (key_exists('config', $binding_config)) {

                if (SCA_Helper::isARelativePath($binding_config['config'])) {
                    $msg = $binding_config['config'];
                    if (!empty($immediate_caller_directory)) {
                        $msg = $immediate_caller_directory . '/' . $msg;
                    }
                    $absolute_path = realpath($msg);
                    if ($absolute_path === false) {
                        throw new SCA_RuntimeException("File '$msg' could not be found");
                    }
                    SCA::$logger->log('Loading external configuration from: ' . $absolute_path);
                    $config = parse_ini_file($absolute_path, true);
                    $binding_config = array_merge($config, $binding_config);
                }
            }
            
            // Check that all the required configuration has been provided
            if (!key_exists('siteid', $binding_config)) {
               SCA::$logger->log('eBay soap binding configuration missing: siteid.');
               throw new SCA_RuntimeException('eBay soap binding configuration missing: siteid.');
            }
            if (!key_exists('version', $binding_config)) {
               SCA::$logger->log('eBay soap binding configuration missing: version.');
               throw new SCA_RuntimeException('eBay soap binding configuration missing: version.');
            }
            if (!key_exists('authtoken', $binding_config)) {
               SCA::$logger->log('eBay soap binding configuration missing: authtoken.');
               throw new SCA_RuntimeException('eBay soap binding configuration missing: authtoken.');
            }
            if (!key_exists('routing', $binding_config)) {
               SCA::$logger->log('eBay soap binding configuration missing: routing.');
               throw new SCA_RuntimeException('eBay soap binding configuration missing: routing.');
            }
            if (!key_exists('appid', $binding_config)) {
               SCA::$logger->log('eBay soap binding configuration missing: appid.');
               throw new SCA_RuntimeException('eBay soap binding configuration missing: appid.');
            }
            if (!key_exists('devid', $binding_config)) {
               SCA::$logger->log('eBay soap binding configuration missing: devid.');
               throw new SCA_RuntimeException('eBay soap binding configuration missing: devid.');
            }
            if (!key_exists('authcert', $binding_config)) {
               SCA::$logger->log('eBay soap binding configuration missing: authcert.');
               throw new SCA_RuntimeException('eBay soap binding configuration missing: authcert.');
            }
            if (!key_exists('location', $binding_config)) {
               SCA::$logger->log('eBay soap binding configuration missing: location.');
               throw new SCA_RuntimeException('eBay soap binding configuration missing: location.');
            }
            
            parent::__construct($absolute_path_to_target_wsdl,
                                $immediate_caller_directory, $binding_config);
            SCA::$logger->log('Leaving');
        }

        public function __call($method_name, $arguments) {
            SCA::$logger->log('Entering');

            // Build up the Url Query String Paramters
            $query_params = array('callname' => $method_name,
            'siteid'   => $this->config['siteid'],
            'version'  => $this->config['version'],
            'appid'    => $this->config['appid'],
            'Routing'  => $this->config['routing']);
            parent::__setQueryParams($query_params);

            // Build up the security header
            $requester_credentials =
            $this->createDataObject('urn:ebay:apis:eBLBaseComponents',
            'CustomSecurityHeaderType');
            $requester_credentials->eBayAuthToken = $this->config['authtoken'];
            $credentials = $requester_credentials->createDataObject('Credentials');
            $credentials->AppId    = $this->config['appid'];
            $credentials->DevId    = $this->config['devid'];
            $credentials->AuthCert = $this->config['authcert'];
            parent::__setSoapHeader($requester_credentials,
            'urn:ebay:apis:eBLBaseComponents',
            'RequesterCredentials');

            $return = parent::__call($method_name, $arguments);
            return $return;
        }

    }

}

?>