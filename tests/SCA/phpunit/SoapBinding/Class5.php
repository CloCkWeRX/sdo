<?php
/**
 * @service
 * @binding.soap
 */
class Class5 {
    /**
     * @param string $a
     * @param float $b
     * @param integer $c
     * @param boolean $d
     * @return string
     */
    public function fourargs() {}

    /**
     * PHP Magic methods - WSDL generation and proxy processing should
     * ignore these
     */
    public function __construct(){
    }
    public function __destruct(){
    }
    public function __call($methodname, $args){
    }
    public function __get($name){
    }
    public function __set($name, $value){
    }
    public function __isset($name){
    }
    public function __unset($name){
    }
    public function __sleep(){
    }
    public function __wakeup(){
    }
    public function __toString(){
    }
    public function __set_state(){
    }
    public function __clone(){
    }
    public function __autoload(){  
    }    
}
?>