<?php

/**
 * @service
 */

class Hello4 {
    /**
     * @reference
     * @binding.local ../hello3.php
     */
    public $hello3_service;
    
    public function hello()
    {
        return $this->hello3_service->hello();
    }
}