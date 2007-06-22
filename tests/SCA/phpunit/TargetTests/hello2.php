<?php

/**
 * @service
 */

class Hello2 {
    /**
     * @reference
     * @binding.local ./hello.php
     */
    public $hello_service;
    
    public function hello()
    {
        return $this->hello_service->hello();
    }
}