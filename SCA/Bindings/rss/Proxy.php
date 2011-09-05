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
$Id: Proxy.php 238265 2007-06-22 14:32:40Z mfp $
*/

require_once "SCA/SCA_Exceptions.php";
require_once "SCA/Bindings/rss/RssDas.php";




class SCA_Bindings_rss_Proxy
{

    /**
     * Holds the target url of the RSS feed
     *
     * @var string
     */
    private $target;

    /**
     * Hold headers from curl
     */
    private $received_headers;

    public function __construct($target,
                                $base_path_for_relative_paths,
                                $binding_config)
    {
        SCA::$logger->log("Entering constructor");
        $this->target = $target;
        SCA::$logger->log("Exiting constructor");
    }

    /*
    * Callback set by CURL_HEADERFUNCTION
    * Receives header lines from the response.
    */
    private function _headerCallback ($handle, $header)
    {
        SCA::$logger->log("Entering");
        SCA::$logger->log("header = $header");
        /* split the header on the first : */
        $split_header = preg_split('/\s*:\s*/', trim($header), 2);
        if (count($split_header) > 1) {
            $this->received_headers[strtoupper($split_header[0])] =
            $split_header[1];
        } else if (!empty($split_header[0])) {
            $this->received_headers[] = $split_header[0];
        }

        return strlen($header);
    }

    /**
     * The XmlDas we use for Rss will automatically load the rss2.0.xsd.
     * If we want additional types to be loaded, we will need to provide
     * an implementation here.
     * Note, should SCA be managing our RssDas?
     */
    public function addReferenceType($reference_type)
    {
        SCA::$logger->log("TODO: implement addReferenceType");
        /* This is the old implementation
        SCA::$logger->log("Entering");
        $this->reference_type = $reference_type;

        // Add type descriptions to the XML DAS. We use XSDs if they
        // are prvoided.
        if ( count($reference_type->getTypes()) > 0 ) {
        // Some XSD types are specified with the reference
        // annotation so use these XSDs to build the XMLDAS
        $this->xmldas = $reference_type->getXmlDas();

        // get the list of types that have been loaded into
        // the XMLDAS in this case
        $this->type_list =
        SCA_Helper::getAllXmlDasTypes($this->xmldas);
        } else {

        //TODO: This is where we end up if we don't specify @types on a client side RSS component with an @reference!

        //TODO: refactor this and check routes to this 'else' result in proper response
        $this->xmldas = SDO_DAS_XML::create(dirname(__FILE__) . '/rss2.0.xsd');
        }
        */
    }

    /**
     * Rss has fixed methods to retrieve a channel or items __call
     * is implemented to catch any problem cases.
     */
    public function __call($method_name, $arguments)
    {
        SCA::$logger->log("Call to invalid method $method_name.  RSS only supports retrieve() or enumerate()");
        throw SCA_MethodNotAllowedException("Call to invalid method $method_name.  RSS only supports retrieve() or enumerate()");
    }


    public function retrieve($id=null)
    {

        SCA::$logger->log("Entering");
        //TODO these should all be building a setopts array not sending a lot.

        if ($id !== null) {
            //$target is the target of the RSS binding. If there is no slash on the end of the target provided, one is added.
            $slash_if_needed =
            ('/' === $this->target[strlen($this->target)-1])?'':'/';

            $handle = curl_init($this->target.$slash_if_needed."$id");
        }
        else{
            $handle = curl_init($this->target);
        }
        curl_setopt($handle, CURLOPT_HEADER, false);
        curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_ENCODING, '');
        curl_setopt($handle, CURLOPT_HTTPGET, true);

        $result = curl_exec($handle);

        $response_http_code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

        curl_close($handle);

        //convert the result into an sdo.
        $sdo = SCA_Bindings_rss_RssDas::fromXml($result);

        //TODO: confirm the correct response code for retrieve.
        if ($response_http_code != 200) {{

            switch($response_http_code) {
                // Temporary redirects
                case 302:
                case 307:
                    // FOLLOWLOCATION is on, so we should not get here
                    throw new SCA_RuntimeException('enumerate() status code '. $response_http_code . ' when 200 expected');
                case 503:
                    //TODO: pick out the message from the response if there is one
                    //for now just pass back a one liner.
                    throw new SCA_ServiceUnavailableException("Service Unavailable");
                case 409:
                    throw new SCA_ConflictException("Conflict");
                case 407:
                    throw new SCA_AuthenticationException("Authentication Required");
                case 400:
                    throw new SCA_BadRequestException("Bad Request");
                case 500:
                    throw new SCA_InternalServerErrorException("Internal Server Error");
                case 405:
                    throw new SCA_MethodNotAllowedException("Method Not Allowed");
                case 401:
                    throw new SCA_UnauthorizedException("Unauthorized");
                default:
                    throw new SCA_RuntimeException('retrieve() status code '. $response_http_code . ' when 200 expected');
            }

        }
        } else {

            return $sdo;
        }

    }



    public function enumerate()
    {

        SCA::$logger->log("Entering enumerate()");
        //TODO these should all be building a setopts array not sending a lot.


        $handle = curl_init($this->target);

        curl_setopt($handle, CURLOPT_HEADER, false);
        curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_HTTPGET, true);
        curl_setopt($handle, CURLOPT_ENCODING, '');
        curl_setopt($handle, CURLOPT_HEADERFUNCTION, array($this, '_headerCallback'));

        $result = curl_exec($handle);

        $response_http_code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

        SCA::$logger->log("Http response code: $response_http_code");

        curl_close($handle);

        //convert the result into an sdo.
        $sdo = SCA_Bindings_rss_RssDas::fromXml($result);

        //TODO: confirm the correct response code for retrieve.
        if ($response_http_code != 200) {{

            switch($response_http_code) {
                // Temporary redirects
                case 302:
                case 307:
                    // FOLLOWLOCATION is on, so we should not get here
                    throw new SCA_RuntimeException('enumerate() status code '. $response_http_code . ' when 200 expected');
                case 503:
                    //TODO: pick out the message from the response if there is one
                    //for now just pass back a one liner.
                    throw new SCA_ServiceUnavailableException("Service Unavailable");
                case 409:
                    throw new SCA_ConflictException("Conflict");
                case 407:
                    throw new SCA_AuthenticationException("Authentication Required");
                case 400:
                    throw new SCA_BadRequestException("Bad Request");
                case 500:
                    throw new SCA_InternalServerErrorException("Internal Server Error");
                case 405:
                    throw new SCA_MethodNotAllowedException("Method Not Allowed");
                case 401:
                    throw new SCA_UnauthorizedException("Unauthorized");
                default:
                    throw new SCA_RuntimeException('enumerate() status code '. $response_http_code . ' when 200 expected');
            }

        }
        } else {

            return $sdo;
        }

    }

}
