<?php

class SCA_Bindings_rss_RequestTester
{
    public function isServiceDescriptionRequest($calling_component_filename)
    {
        // RSS doesn't have service descriptions
        return false;
    }

    public function isServiceRequest($calling_component_filename)
    {
        // RSS uses GET
        if (isset($_SERVER['HTTP_HOST']) && 
          ($_SERVER['REQUEST_METHOD'] == 'GET')) {
            return true;
        }
        return false;
    }

}

?>