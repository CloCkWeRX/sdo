CREATE DATABASE sdo_contacts;

USE sdo_contacts;

CREATE TABLE `contact` (
    `shortname` VARCHAR( 40 ) NOT NULL,
    `fullname` VARCHAR( 100 ) NULL,
    PRIMARY KEY ( `shortname` ) 
    ) COMMENT = 'Contact name details'; 

INSERT INTO `contact` VALUES (
    'shifty', 'The Right Hon. Elias Shifty, Esq');
    
CREATE TABLE `address` (
    `id` INTEGER auto_increment,
    `contact_id` VARCHAR( 40 ) NOT NULL,
    `addressline1` VARCHAR( 100 ) NULL,
    `addressline2` VARCHAR( 100 ) NULL,
    `city` VARCHAR( 50 ) NULL,
    `state` VARCHAR( 50 ) NULL,
    `zip` VARCHAR( 20 ) NULL,
    `telephone` VARCHAR( 50 ) NULL,
    PRIMARY KEY ( `id` )
    ) COMMENT = 'Contact address details';

INSERT INTO `address` VALUES (
    1, 'shifty', 'Left Luggage Office', 'Victoria Station', 'London', 
    'Metropolitan Borough of London', 'WC1A 1AA', 'Whitehall 1212');