<?php
/*
+-----------------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2006.                                         |
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
$Id: SCA_TuscanyWrapper.php 234945 2007-05-04 15:05:53Z mfp $
*/

/**
* This class is called when a PHP service is invoked from the CPP SCA
* runtime. It decides how the service is implemented
*   php file
*   php function
*   php class function
*   php sca service
* It then marshalls the parameters correctly in order to make the call
* and return the response.
*/



class SCA_TuscanyWrapper {

    private $mediator        = null;
    private $service         = null;
    private $component_name  = null;
    private $class_name      = null;
    private $method_name     = null;

    /**
     * Create the service wrapper for a SCA Component.
     *
     * @param string $component_name
     * @param string $wsdl_filename
     */
    public function __construct($mediator, $component_name, $class_name, $method_name, $arg_array)
    {
        SCA::$logger->log('Entering');
        SCA::$logger->log("component_name = $component_name, class name = $class_name");

        SCA::setIsEmbedded(true);

        $this->mediator       = $mediator;
        $this->component_name = $component_name;
        $this->method_name    = $method_name;
        $this->class_name     = $class_name;

        // if a class name has not been provided
        // then this may just be a script.
        if ( $class_name == null ){
         //   $arg_array = $this->mediator->getArgArray();

            // get the arguments and put them into the request
            $index = 0;
            foreach ( $arg_array as $arg_val ){
                $_REQUEST[$index] = $arg_val;
                $index = $index + 1;
            }

            // its just a script so get ready to
            // capture its output. We have to do this here
            // as the script is included between construction
            // and invoke of this object
            ob_start();
        }

        SCA::$logger->log('Exiting');
    }/* End service wrapper constructor  */


    public function invoke()
    {
        SCA::$logger->log('Entering');

//component_name, string reference_name, string method_name, array arguments
//           Reflection::export(new ReflectionObject($this->mediator));

        try {
            // get the arguments from the mediator
            $arg_array = $this->mediator->getArgArray();

//var_dump($arg_array);

            if ( $this->class_name != null ){
                SCA::$logger->log('Attempt to create a service');
                $this->service = new $this->class_name();
                $reflection    = new ReflectionObject($this->service);
                $reader        = new SCA_CommentReader($reflection->getDocComment());

                // if it's an SCA service recreate the instance
                // with all the references filled in
                if ( $reader->isService() ){
                    SCA::$logger->log('Create SCA service');
                    $this->service = SCA::createInstanceAndFillInReferences($this->class_name);
                } else {
                    SCA::$logger->log('Create PHP object');
                }
            }

            //invoke the function
            if ( $this->service != null ){
                SCA::$logger->log('Invoke service as PHP object');
                // it's a class with a member function
                // so call it. Even if it's an SCA service
                // we are not relying of SCA service bindings
                // at this point. We just want the references
                // that will have been set up on initialization
                // above.
                $return = call_user_func_array(array($this->service,
                                                     $this->method_name),
                                               $arg_array);

            } else if ( function_exists($this->method_name) ) {
                SCA::$logger->log('Invoke service as PHP function');
                // it's not just a script so turn
                // off output buffering and then call
                // the function
                ob_end_clean();
                $return = call_user_func_array($this->method_name,
                                               $arg_array);
            } else {
                SCA::$logger->log('Invoke service as PHP script');
                // the script will have already executed
                // because it was included so all we have to
                // do here is return the contents of the
                // output buffer.
                $return = ob_get_contents();
                ob_end_clean();
            }

        } catch ( Exception $e ) {
            SCA::$logger->log('Caught '.$e->getMessage());
            throw $e;
        }

        SCA::$logger->log('Exiting');

        return $return;

    }
}
