<?php


require 'SCA/SCA.php';

/**
 * @service
 * @binding.message
 * @protocol stomp
 * @response queue://reqponsequeue
 * @correlationScheme None
 * 
 * @types http://example.org/names ./names.xsd
 */
class MS_TestserviceSDO
{
    /**
     * generats a greeting for a batch of names.
     *
     * @param string $text custom text
     * @param people $names http://example.org/names
     * @return people http://example.org/names
     */
    function greetEveryone($text,$names)
    {

        $replies = SCA::createDataObject('http://example.org/names', 'people');

        // Iterate through each names to build up the replies
        foreach ($names->name as $name) {
            $replies->name[] =  $text." $name";
        }

        return $replies;
    }

}

