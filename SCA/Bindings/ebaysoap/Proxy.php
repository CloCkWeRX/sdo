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
$Id: Proxy.php 254122 2008-03-03 17:56:38Z mfp $
*/

require_once 'SCA/SCA_Exceptions.php';
require_once 'SCA/SCA_Helper.php';
require_once 'SCA/Bindings/soap/Proxy.php';
require_once 'SCA/Bindings/ebaysoap/Mapper.php';

if (!class_exists('SCA_Bindings_soap_Proxy', false)) {
	trigger_error("Cannot use SCA ebay soap binding as the SCA soap binding is not loaded",E_USER_WARNING);
	return;
}


class SCA_Bindings_ebaysoap_Proxy extends SCA_Bindings_soap_Proxy {
    protected $sdo_type_handler_class_name = "SCA_Bindings_ebaysoap_Mapper";

    public static function dependenciesLoaded() {
        $dependenciesLoaded = false;

        if (extension_loaded('openssl')) {
            $dependenciesLoaded = true;
        }

        return $dependenciesLoaded;
    }

    public function __construct($target, $base_path_for_relative_paths,
                                $binding_config) {
        SCA::$logger->log('Entering');
        if (!SCA_Bindings_ebaysoap_Proxy::dependenciesLoaded()) {
            SCA::$logger->log('eBay soap binding requires the openssl extension, but it is not loaded.');
            throw new SCA_RuntimeException('eBay soap binding requires the openssl extension, but it is not loaded.');
        }

        $binding_config =
            SCA_Helper::mergeBindingIniAndConfig($binding_config,
                                                 $base_path_for_relative_paths);

        // Check that all the required configuration has been provided
        if (!key_exists('siteid', $binding_config) || empty($binding_config['siteid'])) {
            SCA::$logger->log('eBay soap binding configuration missing: siteid.');
            throw new SCA_RuntimeException('eBay soap binding configuration missing: siteid.');
        }
        if (!key_exists('version', $binding_config) || empty($binding_config['version'])) {
            SCA::$logger->log('eBay soap binding configuration missing: version.');
            throw new SCA_RuntimeException('eBay soap binding configuration missing: version.');
        }
        if (!key_exists('authtoken', $binding_config) || empty($binding_config['authtoken'])) {
            SCA::$logger->log('eBay soap binding configuration missing: authtoken.');
            throw new SCA_RuntimeException('eBay soap binding configuration missing: authtoken.');
        }
        if (!key_exists('routing', $binding_config) || empty($binding_config['routing'])) {
            SCA::$logger->log('eBay soap binding configuration missing: routing.');
            throw new SCA_RuntimeException('eBay soap binding configuration missing: routing.');
        }
        if (!key_exists('appid', $binding_config) || empty($binding_config['appid'])) {
            SCA::$logger->log('eBay soap binding configuration missing: appid.');
            throw new SCA_RuntimeException('eBay soap binding configuration missing: appid.');
        }
        if (!key_exists('devid', $binding_config) || empty($binding_config['devid'])) {
            SCA::$logger->log('eBay soap binding configuration missing: devid.');
            throw new SCA_RuntimeException('eBay soap binding configuration missing: devid.');
        }
        if (!key_exists('authcert', $binding_config) || empty($binding_config['authcert'])) {
            SCA::$logger->log('eBay soap binding configuration missing: authcert.');
            throw new SCA_RuntimeException('eBay soap binding configuration missing: authcert.');
        }
        if (!key_exists('location', $binding_config) || empty($binding_config['location'])) {
            SCA::$logger->log('eBay soap binding configuration missing: location.');
            throw new SCA_RuntimeException('eBay soap binding configuration missing: location.');
        }
        parent::__construct($target, $base_path_for_relative_paths, $binding_config);
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

