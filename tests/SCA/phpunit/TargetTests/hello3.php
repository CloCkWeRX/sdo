<?php

/**
 * @service
 */

class Hello3 {
    /**
     * @reference
     * @binding.local ./hello2.php
     */
    public $hello2_service;
    
    public function hello()
    {
        return $this->hello2_service->hello();
    }
}