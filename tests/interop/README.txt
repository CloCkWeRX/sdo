SDO Interoperability Testing
============================

The data (XSD, XML and database setupfiles) included here are duplicates of the
files held in the Apache Tuscany project. 

Current SDO Implementations
---------------------------
Java - M1     - Apache Tuscany
C++  - M1     - Apache Tuscany
PHP  - v1.0.1 - PECL

Features
--------
There are various approaches to getting data in and out of SDO and various levels of 
support for these approaches across the current SDO implementations:

Feature                     Java                     C++          PHP
.......                     ....                     ...          ...
XML (DAS)                     Y                       Y            Y    
RDB DAS                       Y                       N            Y 
Serialize to/from WS          Y (1)                   Y (1)        N 
Serialize to/from session     Via java serialize?     N            via serialize()      

(1) - SDO->XMLHelper->String->Axiom

Given these features there are a number of basic but useful tests to demonstrate that
SDO implementations can interoperate to a reasonable degree

Tests
-----

#  Test                                    Java                         C++                           PHP                        
.  ....                                    ....                         ...                           ...
1  XML->SDO-XML                              Y                           Y                             Y                             
2  XML->SDO->AddData->XML - Dynamic          Y                           Y                             Y                             
3  XML->SDO->AddData->XML - Generated        Y                           Not Supported                 Y (By Name/By Index)          
4  XSD->SDO-XSD                              Not Supported               Y                             Only supported via serialize     
5  RDB->SDO->AddData->RDB - Dynamic          Y                           Not Supported                 Y                                
6  RDB->SDO->AddData->RDB - Generated        Y                           Not Supported                 Y                               
7  XML->SDO->Axiom->SOAP->Axiom->SDO->XML    Y                           Y                             Not Supported
8  XML->SDO->Session->SDO->XML               Via java serializable?      N                             Stores SDO state (XML+XSD) in PHP session  

Test 1 
------

The test involved reading and XML file and writing it out again. The output should be compared with the input for change. The test convers all of the XML
schema features that SDO is expected to support. Each feature is described in a separate XSD as shown below. Input XML files include the test number (#) 
using the following pattern: interop#-in.xml.

Feature                                          XSD 
.......                                          ...                
xsd <include>                                    interop1.xsd         
xsd <import>                                     interop2.xsd 
With target namespace                            interop3.xsd 
Without target namespace                         interop4.xsd 
With sdoJava``:package                           interop5.xsd 
Global Element of simple type                    interop6.xsd 
Global Element of complex type                   interop7.xsd 
Element of simple type                           see interop10.xsd 
Element of complex type                          see interop19.xsd
Annotation                                       interop8.xsd 
Notation                                         interop9.xsd 
SimpleTypeWithName                               interop10.xsd
SimpleTypeAnonymous                              interop11.xsd
Element Ref                                      interop11.xsd 
SimpleTypeWithSDOName                            interop12.xsd 
SimpleTypeWithAbstract                           interop13.xsd 
SimpleTypeWithInstanceClass                      interop14.xsd 
SimpleTypeWithExtendedInstanceClass              interop15.xsd 
SimpleTypeWithList                               interop16.xsd 
SimpleTypeWithUnion                              interop17.xsd
ComplexTypeNoContent                             interop18.xsd 
ComplexTypeContent                               interop19.xsd
ComplexTypeAnonymous                             interop20.xsd 
ComplexTypeWithSDOName                           interop21.xsd 
ComplexTypeWithAbstract                          interop22.xsd 
ComplexTypeWithSDOAliasName                      interop23.xsd 
ComplexTypeExtendingComplexType                  interop24.xsd 
ComplexTypeExtendingSimpeType                    interop25.xsd 
ComplexTypeComplexContentRestrictingComplexType  interop26.xsd 
ComplexTypeSimpleContentRestrictingComplexType   interop27.xsd 
ComplexTypeWithMixed                             interop28.xsd 
ComplexTypeWithSDOSequence                       interop29.xsd 
ComplexTypeOpenContent                           interop30.xsd 
ComplexTypeOpenAttributes                        interop31.xsd 
ComplexTypeProperty                              interop32.xsd 
ComplexTypeOppositeProperty                      interop32.xsd 
Attribute                                        interop33.xsd 
AttributeWithSDOName                             interop33.xsd 
AttributeWithSDOAliasName                        interop33.xsd 
AttributeWithDefaultValue                        interop33.xsd 
AttributeWithFixedValue                          interop33.xsd 
AttributeReference                               interop33.xsd 
Global Attribute                                 interop33.xsd 
AttributeWithSDOString                           interop33.xsd 
AttributeWithSDOPropertyType                     interop33.xsd 
AttributeWithSDOPropertySDOOppositePropertyType  interop33.xsd 
AttributeWithSDODataType                         interop33.xsd 
ElementWithSDOName                               interop34.xsd 
ElementWithSDOAliasName                          interop35.xsd 
ElementWithMaxOccurs                             interop36.xsd 
Element in sequence                              see interop10.xsd
ElementInChoice                                  interop37.xsd 
ElementInAll                                     interop38.xsd 
ElementWithNillable                              interop39.xsd 
ElementSubstitutionGroupBase                     interop40.xsd 
ElementOfSimpleTypeWithDefault                   interop41.xsd 
ElementOfSimpleTypeWithFixed                     interop42.xsd 
ElementOfSimpleTypeWithSDOString                 interop43.xsd 
ElementOfSimpleTypeWithSDOPropertyType           interop44.xsd 
ElementOfSimpleTypeWithSDOOppositePropertyType   interop45.xsd 
ElementOfSimpleTypeWithSDODataType               interop46.xsd 
ElementOfSDOChangeSummaryType                    interop47.xsd 
anySimpleType                                    interop50.xsd 
anyType                                          interop50.xsd 
anyURI                                           interop50.xsd 
base64Binary                                     interop50.xsd 
boolean                                          interop50.xsd
byte                                             interop50.xsd
date                                             interop50.xsd
dateTime                                         interop50.xsd
decimal                                          interop50.xsd
double                                           interop50.xsd
duration                                         interop50.xsd
float                                            interop50.xsd
gDay                                             interop50.xsd
gMonth                                           interop50.xsd
gMonthDay                                        interop50.xsd
gYear                                            interop50.xsd
gYearMonth                                       interop50.xsd
hexBinary                                        interop50.xsd
ID                                               interop50.xsd
IDREF                                            interop50.xsd
IDREFS                                           interop50.xsd
int                                              interop50.xsd
integer                                          interop50.xsd
language                                         interop50.xsd
long                                             interop50.xsd
Name                                             interop50.xsd
NCName                                           interop50.xsd
negativeInteger                                  interop50.xsd
NMTOKEN                                          interop50.xsd
NMTOKENS                                         interop50.xsd
nonNegativeInteger                               interop50.xsd
nonPositiveInteger                               interop50.xsd
normalizedString                                 interop50.xsd
NOTATION                                         interop50.xsd
positiveInteger                                  interop50.xsd
QName                                            interop50.xsd
short                                            interop50.xsd
string                                           interop50.xsd
time                                             interop50.xsd
token                                            interop50.xsd
unsignedByte                                     interop50.xsd
unsignedInt                                      interop50.xsd
unsignedLong                                     interop50.xsd
unsignedShort                                    interop50.xsd
ENTITIES                                         TBD 
ENTITY                                           TBD


Test 2
------
TBD

Test 3
------
Generated interfaces are currently only supported in Java so no testing is defined.

Test 4
------
This is only supported by C++ currently so not testing is performed

Test 5
------
The test checks that the row added to the database matches the previous row in the alltype table

Loading schema and data into DB2:

From command line within the DB2 environment (you can set the DB2 environment on windows by 
starting the DB2 CLP and "quit"ing from the DB2 command prompt).

db2 -tvf create-db2.ddl 
db2 -tvf insertdata-db2.ddl 

Loading schema and data into MySQL

mysql < createdb-mysql.ddl
mysql < insertdata-mysql.ddl

Test 6
------
Generated interfaces are only supported in Java so no testing is defined.

Test 7
------
TBD

Test 8
------
There is no consistent format for serialization across the implementations currently so no testing is performed