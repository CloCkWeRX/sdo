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
if ( ! class_exists('SCA_Bindings_message_SAMClient', false) ) {
    class SCA_Bindings_message_SAMClient {

        // set to turn-on test mode, then $test_queueborker will be used as the message provider.
        public static $test_mode = false;
        // array holds messages for unit tests
        public static $test_queueborker = null;

        protected $wrapper            = null; 
        private $request_conn         = null;
        private $response_conn        = null;
        private $request_queue        = null;  //the destination queue
        private $response_queue       = null;
        private $isFixedResponseQueue = false;
        private $headers              = null; // JMS headers for all options
        private $correlationScheme    = 'RequestMsgIDToCorrelID';

        public static $samOptions = array('host'        => SAM_HOST,
                                          'port'        => SAM_PORT,
                                          'broker'      => SAM_BROKER,
                                          'endpoints'   => SAM_ENDPOINTS,
                                          'targetchain' => SAM_TARGETCHAIN,
                                          'bus'         => SAM_BUS,
                                          'userid'      => SAM_USERID,
                                          'password'    => SAM_PASSWORD,
                                          'JMSCorrelationID' => SAM_CORRELID,
                                          'JMSDeliveryMode'  => SAM_DELIVERYMODE
//                                          ,
//                                          'JMSTimeToLive'    => SAM_TIMETOLIVE,
//                                          'JMSPriority'      => SAM_PRIORITY
                                          ) ;

        /**
         * Constructor - 
         * @param $wrapper instance of the wrapper
         *               which should implement an onMessage method
         */
        public function __construct($wrapper)
        {
             $this->wrapper = $wrapper;
        }


       /**
        * function that listens to a specified queue
        * and calls the wrapper's onMessage method if message received 
        */
        public function start()
        {
            echo "Listener for $this->request_queue has been started. To exit, press <Ctrl> + <C> ...\n";

            // to avoid the 30 seconds PHP maximun execution time error
            set_time_limit(0);

            while(1){
                $msg = $this->getRequest();
                if($msg){
                    /*call the wrapper*/
                    $response = $this->wrapper->onMessage($msg) ;
                    if (isset($response)){
                        $this->sendResponse($msg,$response);
                    }
                }
                if(self::$test_mode) break; //exit loop
            }
            return;
        }

        /**
         * function that sends request messages
         * @param $tragetOperation string name of the remote operation
         * @param $msgbody string the message payload
         * @return string correlid of the response message
         *         or return false if send failed
         */
        public function sendRequest($tragetOperation,$msgbody)
        {
            $msg = new SAMMessage($msgbody);
           @$msg->header->SAM_TYPE = SAM_TEXT; // put @ on the front as else get "Creating default object from empty value" strict message
            $msg->header->scaOperationName = $tragetOperation;

            if ($this->response_queue) {
                //$msg->header->scaCallbackQueue = $this->response_queue;
                $msg->header->SAM_REPLY_TO = $this->response_queue;
            }

            $options = ($this->headers !== null) ? $this->headers : array();

            if ($this->correlationScheme == 'RequestCorrelIDToCorrelID' && 
                !isset($options[SAM_CORRELID]))
            {
                /*generate a random correlation id
                this id is used for message selection at reply_queue*/
                $options[SAM_CORRELID] = sprintf ( "%s-%07d" ,"sca_correlid", mt_rand(0,9999999));
            }

            if(self::$test_mode){
                $rc = $this->_testmsgput($this->request_queue,$msg,
                                         isset($options->SAM_CORRELID)?$options->SAM_CORRELID:null);
            }else{
                /*sending message to a real broker*/
                SCA::$logger->log(" sending request msg to queue $this->request_queue");
                if(!$rc = $this->request_conn->send($this->request_queue, $msg, $options)){
                    SCA::$logger->log('SAM: send message failed:'.$this->getLastError());
                }else{
                    if ($this->correlationScheme == 'RequestCorrelIDToCorrelID') {
                        $rc = $options->SAM_CORRELID;
                    }/*else $rc = msgid as now
                       stomp will not return msgid, just true or false*/
                }
            }
            return $rc;
        }/* end of sendRequest function*/

        /**
         * function that sends response message
         * @param $request_msg SAMMessage the received request message
         * @param $response_msgbody string the response message payload
         * @return bool
         */
        public function sendResponse($request_msg, $response_msgbody)
        {
            /*check the target queue for callbacks using the 'scaCallbackQueue' user property 
                      or the JMS replyTo header*/
            $callback_queue = false;
            if (isset($request_msg->header->scaCallbackQueue)) {
                $callback_queue = trim( $request_msg->header->scaCallbackQueue );
            }else if (isset($request_msg->header->SAM_REPLY_TO)) {
                $callback_queue = trim( $request_msg->header->SAM_REPLY_TO );
            }

            $response_queue = ($this->isFixedResponseQueue) ? $this->response_queue : $callback_queue;
            /*if response_queue is defined send response to the queue*/
            if($response_queue){ 
                $response_msg = new SAMMessage($response_msgbody);
                @$response_msg->header->SAM_TYPE = SAM_TEXT;

                /*apply correlation Scheme*/
                $options = ($this->headers !== null) ? $this->headers : array();
                if($this->correlationScheme == 'RequestCorrelIDToCorrelID'){
                    $options[SAM_CORRELID] = $request_msg->header->SAM_CORRELID ;
                }
                if($this->correlationScheme == 'RequestMsgIDToCorrelID'){
                    $options[SAM_CORRELID] = $request_msg->header->SAM_MESSAGEID;
                }

                SCA::$logger->log("sending response $response_msgbody to queue $response_queue");
                if(self::$test_mode){
                    $rc = $this->_testmsgput($response_queue,$response_msg);
                }else{
                    /*sending message to a real broker*/
                    if(!$rc = $this->response_conn->send($response_queue, $response_msg, $options)){
                        SCA::$logger->log('SAM: send message failed:'.$this->getLastError(0));
                    }
                }
                return $rc;
            }
        }

        /**
         * function that receive request messages from the request queue
         * @param $timeout int wait request timeout in microseconds
         * @return SAMMessage the request message
         */
        public function getRequest($timeout = 0){
            if(self::$test_mode){
                /*in test mode, use $this::test_queueborker */
                $msg = self::$test_queueborker[$this->request_queue];
            }else{
                /*receiving message from a real broker*/
                $msg = $this->request_conn->receive($this->request_queue, array(SAM_WAIT=>$timeout));
            }
            return $msg;
        }

        /**
         * function that receive response messages from the response queue
         * @param $correlid the correlid of response messages
         * @param $timeout int  wait-response timeout in microseconds 
         * @return SAMMessage the response message
         */
        public function getResponse($correlid = null, $timeout = 0){
            if(is_null($this->response_queue)){
                throw new SCA_RuntimeException("Response queue is not specified");
            }
            if(self::$test_mode){
                /*in test mode, use $this::test_queueborker */
                $msg = self::$test_queueborker[$this->response_queue];
            }else{
                /*receiving message from a real broker*/
                $options[SAM_WAIT] =  $timeout;
                if (!is_null($correlid) && $this->correlationScheme != 'None') {
                    $options[SAM_CORRELID] = $correlid;
                }
                $msg = $this->response_conn->receive($this->response_queue, $options);
            }
            return $msg;
            
        }

        //
        public function setResponseQueue($queue){
            if (!$this->isFixedResponseQueue) {
                $this->response_queue = $queue;
            }else{
                //throws exception ??
                SCA::$logger->log("response queue cannot be changed as it is fixed at the service end.");
            }
        }

        /**
         * Function that apply binding configuration
         * and creates SAMConnection
         * Also checks that all the required configuration has been provided
         * 
         * @param $config SDO Data object 
         */
        public function config($config){
            SCA::$logger->log("entering");

            /*config request queue*/
            if (!isset($config->destination) || empty($config->destination)) {
                throw new SCA_RuntimeException('message binding configuration missing: destination.');
            }else{
                $this->request_queue = $config->destination;
            }

            /*config connection factory for request queue and establish a connection */
            if(!isset($config->connectionFactory) || count((array)$config->connectionFactory) == 0){
                throw new SCA_RuntimeException('message binding configuration missing: connectionFactory.');
            }else{
                $this->request_conn = $this->_configConnection($config->connectionFactory);
            }

            /*config response queue and connection factory
            */
            if(isset($config->response) && isset($config->response->destination)){
                $this->response_queue = $config->response->destination;
                $this->isFixedResponseQueue = true;
                if (isset($config->response->connectionFactory) && 
                    count((array)$config->response->connectionFactory) > 0) 
                {
                    $this->response_conn = $this->_configConnection($config->response->connectionFactory);
                }else{
                    /*Assume request queue and response queue is on the same broker, i.e. share the connection*/
                    $this->response_conn =  $this->request_conn;
                }
            }else{
                /*by omitting the response element, the response queue can be left to 
                the runtime to provide one, but has to be on the same broker as the request queue*/
                $this->response_conn =  $this->request_conn;
            }

            /*config headers*/
            if( isset($config->headers) ){
                $this->headers = $this->_configHeaders($config->headers);
            }

            if (isset($config->correlationScheme)) {
                $this->correlationScheme = $config->correlationScheme;
            }

            SCA::$logger->log("exiting");
        }

        public function getLastError($isRequest = true){
            $conn = $isRequest ? $this->request_conn : $this->response_conn;

            if ($conn->errno) {
                return ' ('.$conn->errno.') '.$conn->error;
            }else{
                return 'There was no error occured.';
            }
        }


        public function disconnect(){
            $this->request_conn->disconnect();
        }



/**********************
    private:
**********************/
        /**
         * creates a connection using connection factory
         * @param $connFactory SDO Data object type = msd:ConnectionFactory
         * @return SAMConnection or null
         */
        private function _configConnection($connFactory)
        {
            $optionsarray = array();
            foreach ($connFactory as $key => $value){
                switch ($key) {
                case "protocol":
                    $protocol = $value ;
                    break;
                default:
                    $optionsarray[self::$samOptions[$key]] = $value ;
                    break;
                }
            }

            /*check mandatory fields */
            if (!isset($protocol)) {
                throw new SCA_RuntimeException('message binding configuration connectionFactory missing: protocol. ');
            }

            if(self::$test_mode){
                /*in test mode, use $this::test_queueborker */
                if (is_null(self::$test_queueborker)){
                    self::$test_queueborker = array();
                }
                $connection = null;
            }else{
                /*connecting to a real broker*/
                $connection = new SAMConnection();
                $connection->connect($protocol, $optionsarray);
                if (!$connection) {
                    throw new SCA_RuntimeException("SAM connection failed");
                }
            }
            return $connection;

        }

        /**
         * construct a sam options array, to apply JMS headers
         * @param $headers SDO Data object type = msd:Headers
         * @return array or null
         */
        private function _configHeaders($headers)
        {
            if(count((array)$headers) == 0) return null;
            $options = array();
            foreach ($headers as $key => $value) {
                $options[self::$samOptions[$key]] = $value;
            }
            return $options;
        }

        /*helper function for sending request/response messages in test mode*/
        private function _testmsgput( $queue, $msg, $corrid = null){
            /*in test mode, use $this::test_queueborker */
            if(is_null(self::$test_queueborker)){
                return false;
            }else {
                if (!is_null($corrid)){
                    $msg->header->SAM_CORRELID = $corrid;
                }
                self::$test_queueborker[$queue] = $msg;
                return true; 
            }
         
        }

        
    }/* End SCA_Bindings_message_SAMClient class*/
}/* End instance check*/
?>
