<?php


require 'SCA/SCA.php';

/**
 * @service
 * @binding.message
 * 
 * @protocol stomp
 * @host localhost
 * @port 61613
 * @userid xxx
 * @password 123
 * @correlationScheme RequestCorrelIDToCorrelID
 * @JMSCorrelationID sca-correl-target2334524
 * @JMSCorrelationID
 * @response queue://reqponsequeue
 * @response.protocol wmq
 * @response.host l33h84m
 * @response.broker QM_Test
 * @wsdl disabled
 */
class MS_TestService
{

    /**
     * @param string $text (some test)
     */
    function hello($text)
    {
        echo "hello ". $text;
    }

    /**
     * @param string $text (some test)
     */
    function onMessage($text)
    {
        echo "onMessage ". $text ;
    }


}?>
