<?php

/**
 * class with no service annotation
 */
class NoServiceAnnotation 
{
}

/**
 * class with no binding annotation
 * @service
 */
class NoBindingAnnotation 
{
}

/**
 * class with rubbish binding annotation
 * @service
 * @binding.rubbish
 */
class RubbishBindingAnnotation 
{
}

/**
 * class with one valid binding annotation
 * @service
 * @binding.soap
 */
class OneValidBindingAnnotation 
{
}

/**
 * class with two valid binding annotations
 * @service
 * @binding.soap
 * @binding.jsonrpc
 */
class TwoValidBindingAnnotations 
{
}

/*******************************************************************************
 * METHODS
 ********************************************************************************/

/**
 * class with no methods
 * @service
 * @binding.soap
 */
class NoMethods 
{
}

/**
 * class with no public methods
 * @service
 * @binding.soap
 */
class NoPublicMethods {
    private function CannotSeeMe() {
    }
}

/**
 * class with one public method and no annotations
 * @service
 * @binding.soap
 */
class MethodHasNoAnnotations {
    public function myPublicMethod() {

    }
}

/*******************************************************************************
 * PARAM
 ********************************************************************************/

/**
 * param is empty
 * @service
 * @binding.soap
 */
class ParamWithNoTypeOrName {
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
 * @binding.soap
 */
class ParamWithValidTypeButNoName {
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
 * @binding.soap
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
 * param has an invalid type
 * @service
 * @binding.soap
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
 * param has a valid type but an invalid name
 * @service
 * @binding.soap
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
 * param has type and name
 * @service
 * @binding.soap
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
 * param has type and name
 * @service
 * @binding.soap
 */
class ParamWithChoiceOfTwoValidTypesAndName {
    /**
     * @param string|float $a
     *
     */
    public function myPublicMethod($a) {

    }
}

/**
 * param has type and name
 * @service
 * @binding.soap
 */
class ParamWithChoiceOfValidTypeOrNullAndName {
    /**
     * @param string|null $a
     *
     */
    public function myPublicMethod($a) {

    }
}

/**
 * param has the four valid scalar types
 * @service
 * @binding.soap
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

/*******************************************************************************
 * RETURN
 ********************************************************************************/

/**
 * return is empty
 * @service
 * @binding.soap
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
 * @binding.soap
 */
class ReturnWithInvalidType {
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
 * @binding.soap
 */
class ReturnWithValidType {
    /**
     * @return string
     *
     */
    public function myPublicMethod() {
    }
}

/**
 * return has string|float
 * @service
 * @binding.soap
 */
class ReturnWithChoiceOfTwoValidTypes{
    /**
     * @return string|float
     *
     */
    public function myPublicMethod($a) {

    }
}

/**
 * return has string|null
 * @service
 * @binding.soap
 */
class ReturnWithChoiceOfValidTypeOrNull {
    /**
     * @return string|null
     *
     */
    public function myPublicMethod($a) {

    }
}


/*******************************************************************************
 * TYPES, NAMESPACE
 ********************************************************************************/

/**
 * empty types
 * @service
 * @binding.soap
 * @types
 */
class EmptyTypes 
{
    
}

/**
 * types with only namespace
 * @service
 * @binding.soap
 * @types http://Namespace
 */
class TypesWithOnlyNamespace 
{
    
}

/**
 * types with valid namespace and xsd
 * @service
 * @binding.soap
 * @types http://Namespace Anything.xsd
 */
class TypesWithValidNamespaceAndXsd 
{
    
}

/**
 * Two types with same namespace and different xsds
 * @service
 * @binding.soap
 * @types http://Namespace Anything.xsd
 * @types http://Namespace More.xsd
 */
class TwoTypesWithSameNamespaceAndDifferentXsds
{
    
}

/**
 * param has type and namespace but wrong
 * @service
 * @binding.soap
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
 * @binding.soap
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
     * @binding.soap 
     */
    public $service;
}

/*******************************************************************************
 * INTERFACES
 ********************************************************************************/

interface InterfaceWithTwoMethods {
    
    public function Method1($in1);
    public function Method2($in1, $in2);
}


interface InterfaceWithOneMethod {
    
    public function Method3();
    
}

/**
 * MyService with a nonServiceMethod
 * @service InterfaceWithTwoMethods
 * @binding.soap
 *
 */
class ServiceInterfaceWithTwoMethods implements InterfaceWithTwoMethods, InterfaceWithOneMethod {
    
    /**
     * A private method that should not appear
     *
     */
    private function privateMethod() {}
    
    /**
     * My first service method
     *
     * @param string $in1
     * @return float
     */
    public function Method1($in1) {}
    
    /**
     * My second service method
     *
     * @param string $in1
     * @param float $in2
     * @return integer
     */
    public function Method2($in1, $in2) {}

    /**
     * A method that I don't want to appear
     *
     */
    public function Method3() {}

    /**
     * A method that I don't want to appear
     *
     */
    public function Method4() {}

}


/**
 * MyService with a nonServiceMethod
 * @service InterfaceWithOneMethod
 * @binding.soap
 *
 */
class ServiceInterfaceWithOneMethod implements InterfaceWithTwoMethods, InterfaceWithOneMethod {
    
    /**
     * A private method that should not appear
     *
     */
    private function privateMethod() {}
    
    /**
     * We don't want this one
     *
     * @param string $in1
     * @return float
     */
    public function Method1($in1) {}
    
    /**
     * We don't want this one
     *
     * @param string $in1
     * @param float $in2
     * @return integer
     */
    public function Method2($in1, $in2) {}

    /**
     * We do want this one
     *
     */
    public function Method3() {}

    /**
     * We don't want this one
     *
     */
    public function Method4() {}

}


/**
 * MyService with a nonServiceMethod
 * @service
 * @binding.soap
 *
 */
class ServiceInterfaceWithFourMethods implements InterfaceWithTwoMethods, InterfaceWithOneMethod {
    
    /**
     * A private method that should not appear
     *
     */
    private function privateMethod() {}
    
    /**
     * We want this one
     *
     * @param string $in1
     * @return float
     */
    public function Method1($in1) {}
    
    /**
     * We want this one
     *
     * @param string $in1
     * @param float $in2
     * @return integer
     */
    public function Method2($in1, $in2) {}

    /**
     * We want this one
     *
     */
    public function Method3() {}

    /**
     * We want this one
     *
     */
    public function Method4() {}

}


/**
 * MyService with a nonServiceMethod
 * @service SomeNonExistentInterface
 * @binding.soap
 *
 */
class ServiceWithInvalidInterface implements InterfaceWithTwoMethods, InterfaceWithOneMethod {
    
    /**
     * A private method that should not appear
     *
     */
    private function privateMethod() {}
    
    /**
     * We want this one
     *
     * @param string $in1
     * @return float
     */
    public function Method1($in1) {}
    
    /**
     * We want this one
     *
     * @param string $in1
     * @param float $in2
     * @return integer
     */
    public function Method2($in1, $in2) {}

    /**
     * We want this one
     *
     */
    public function Method3() {}

    /**
     * We want this one
     *
     */
    public function Method4() {}

}

?>