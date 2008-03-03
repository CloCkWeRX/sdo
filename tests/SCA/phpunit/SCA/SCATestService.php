<?php
include_once "SCA/SCA.php";
/**
 * 
 * @service
 * @types PersonNamespace person.xsd
 */
class ScaTestService
{
    /**
     * Create and return a person SDO
     *
     * @return personType personNamespace
     */
	public function reply()
	{
        $person = SCA::createDataObject('PersonNamespace','personType');
        $person->name = 'William Shakespeare';
        $person->dob = 'April 1564, most likely 23rd';
        $person->pob = 'Stratford-upon-Avon, Warwickshire';
	    return $person;
	}
}
?>