<?php

/*******************************************************************************
 * SERVICE and BINDING.WS
 ********************************************************************************/



/**
 * class with no service annotation
 */
class NoServiceAnnotation {
}

/**
 * class with no binding annotation
 * @service
 */
class NoBindingAnnotation {
}

/**
 * class with rubbish binding annotation
 * @service
 * @binding.rubbish
 */
class RubbishBindingAnnotation {
}

/*******************************************************************************
 * METHODS
 ********************************************************************************/

/**
 * class with no methods
 * @service
 * @binding.ws
 */
class NoMethods {
}

/**
 * class with no public methods
 * @service
 * @binding.ws
 */
class NoPublicMethods {
    private function CannotSeeMe() {
    }
}

/**
 * class with one public method and no annotations
 * @service
 * @binding.ws
 */
class MethodHasNoAnnotations {
    public function myPublicMethod() {

    }
}

/*******************************************************************************
 * PARAM AND RETURN
 ********************************************************************************/

/**
 * param is empty
 * @service
 * @binding.ws
 */
class EmptyParam {
    /**
     * @param
     *
     */
    public function myPublicMethod() {

    }
}

/**
 * param has no name
 * @service
 * @binding.ws
 */
class ParamWithOnlyType {
    /**
     * @param string
     *
     */
    public function myPublicMethod() {

    }
}

/**
 * param has invalid type and no name
 * @service
 * @binding.ws
 */
class ParamWithOnlyInvalidType {
    /**
     * @param rubbish
     *
     */
    public function myPublicMethod() {

    }
}

/**
 * param has type and name
 * @service
 * @binding.ws
 */
class ParamWithValidTypeAndName {
    /**
     * @param string $a
     *
     */
    public function myPublicMethod($a) {

    }
}

/**
 * param has the four valid scalar types
 * @service
 * @binding.ws
 */
class ParamWithFourValidScalarTypes {
    /**
     * @param string $a
     * @param real $b
     * @param boolean $c
     * @param integer $d
     *
     */
    public function myPublicMethod($a,$b,$c,$d) {

    }
}


/**
 * param has type and name
 * @service
 * @binding.ws
 */
class ParamWithInvalidTypeAndValidName {
    /**
     * @param rubbish $a
     *
     */
    public function myPublicMethod($a) {

    }
}
/**
 * param has type and name
 * @service
 * @binding.ws
 */
class ParamWithValidTypeAndInvalidName {
    /**
     * @param string rubbish
     *
     */
    public function myPublicMethod($a) {

    }
}

/**
 * return is empty
 * @service
 * @binding.ws
 */
class EmptyReturn {
    /**
     * @return
     *
     */
    public function myPublicMethod() {
    }
}

/**
 * return has invalid type
 * @service
 * @binding.ws
 */
class returnWithInvalidType {
    /**
     * @return rubbish
     *
     */
    public function myPublicMethod() {
    }
}

/**
 * return has valid type
 * @service
 * @binding.ws
 */
class returnWithValidType {
    /**
     * @return string
     *
     */
    public function myPublicMethod() {
    }
}

/*******************************************************************************
 * TYPES, NAMESPACE
 ********************************************************************************/

/**
 * empty types
 * @service
 * @binding.ws
 * @types
 */
class EmptyTypes 
{
    
}

/**
 * types with only namespace
 * @service
 * @binding.ws
 * @types http://Namespace
 */
class TypesWithOnlyNamespace 
{
    
}

/**
 * types with valid namespace and xsd
 * @service
 * @binding.ws
 * @types http://Namespace Anything.xsd
 */
class TypesWithValidNamespaceAndXsd 
{
    
}

/**
 * Two types with same namespace and different xsds
 * @service
 * @binding.ws
 * @types http://Namespace Anything.xsd
 * @types http://Namespace More.xsd
 */
class TwoTypesWithSameNamespaceAndDifferentXsds
{
    
}

/**
 * param has type and namespace but wrong
 * @service
 * @binding.ws
 * @types http://Namespace Anything.xsd
 */
class ParamWithInvalidNamespace {
    /**
     * @param aTypename $a rubbish_namespace
     *
     */
    public function myPublicMethod($a) {

    }
}

/**
 * return has type and namespace but wrong
 * @service
 * @binding.ws
 * @types http://Namespace Anything.xsd
 */
class ReturnWithInvalidNamespace {
    /**
     * @return aTypename rubbish_namespace
     *
     */
    public function myPublicMethod($a) {

    }
}

/*******************************************************************************
 * REFERENCES
 ********************************************************************************/


/**
 * empty reference
 * @service
 */
class ReferenceWithNoBinding
{
    /**
     * @reference
     */
    public $service;
}

/**
 * empty reference
 * @service
 */
class BindingWithNoReference
{
    /**
     * @binding.php anything.php
     */
    public $service;
}

/** 
 * reference with an invalid binding
 * @service
 */
class ReferenceWithAnInvalidBinding
{
    /**
     * @reference
     * @binding.rubbish anything.php
     */
    public $service;
}

/** 
 * reference with an empty php binding
 * @service
 */
class ReferenceWithAnEmptyPhpBinding
{
    /**
     * @reference
     * @binding.php 
     */
    public $service;
}

/** 
 * reference with an empty ws binding
 * @service
 */
class ReferenceWithAnEmptyWsBinding
{
    /**
     * @reference
     * @binding.ws 
     */
    public $service;
}


?>