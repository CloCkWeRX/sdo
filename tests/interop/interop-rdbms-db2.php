<?php
/* 
+----------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2006.                                  |
| All Rights Reserved.                                                 |
+----------------------------------------------------------------------+
|                                                                      |
| Licensed under the Apache License, Version 2.0 (the "License"); you  |
| may not use this file except in compliance with the License. You may |
| obtain a copy of the License at                                      |
| http://www.apache.org/licenses/LICENSE-2.0                           |
|                                                                      |
| Unless required by applicable law or agreed to in writing, software  |
| distributed under the License is distributed on an "AS IS" BASIS,    |
| WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
| implied. See the License for the specific language governing         |
| permissions and limitations under the License.                       |
+----------------------------------------------------------------------+
| Author: Simon Laws                                                   |
+----------------------------------------------------------------------+
$Id: interop-rdbms-db2.php 219957 2006-09-14 12:16:19Z slaws $
*/
   
    require_once 'SDO/DAS/Relational.php';
  
    // Describe the structure of the alltypeparent table
    $alltypeparent_table = array('name' => 'alltypeparent',
                                 'columns' => array( 'parentid', 
                                                     'description'),
                                 'PK' => 'parentid');

    // Describe the structure of the alltype table
    $alltype_table = array('name' => 'alltype',
                           'columns' => array( 'asmallint', 
                                               'ainteger', 
                                               'abigint', 
                                               'afloat', 
                                               'adouble', 
                                               'adoubleprecision', 
                                               'areal', 
                                               'adecimal', 
                                               'adate', 
                                               'atimestamp', 
                                               'atime', 
                                               'achar', 
                                               'avarchar',
                                               'parentid' ),
                           'PK' => 'asmallint',
	                       'FK' => array (
		                           'from' => 'parentid',
		                           'to' => 'alltypeparent'
		                            )
                          );
                       

    // create the meta data structure for the single table
    $table_metadata = array($alltypeparent_table, $alltype_table);

    // describe the cross table reference
    $parent_reference = array( 'parent' => 'alltypeparent', 'child' => 'alltype');
    $reference_metadata = array($parent_reference);

    // Create the Relational Data Access Service telling it the database
    // schema, that table should be considered the root of the graph,
    // and finally the additional information for the object model.
    $das = new SDO_DAS_Relational($table_metadata, 'alltypeparent',$reference_metadata );

    // no security on my local database so access control strings are empty
    $user = "";
    $password = "";
    
    try 
    {
      // connect to the DB2 database.  This connection will be released when the
      // $dbh variable is cleaned up. 
      $dbh = new PDO("odbc:interop", $user, $password, array(
                            PDO::ATTR_PERSISTENT => TRUE, 
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));                                      
      
      // construct the SQL query for contact retrieval
      $stmt = "select p.parentid, p.description, a.asmallint, a.ainteger, a.abigint, a.afloat, a.adouble, a.adoubleprecision, a.areal, a.adecimal, a.adate, a.atimestamp, a.atime, a.achar, a.avarchar from alltypeparent p, alltype a where p.parentid = a.parentid ";    
       
      // execute the query to retrieve the departments
      $root = $das->executeQuery($dbh, $stmt, array('alltypeparent.parentid', 
                                                    'alltypeparent.description', 
                                                    'alltype.asmallint', 
                                                    'alltype.ainteger', 
                                                    'alltype.abigint', 
                                                    'alltype.afloat',
                                                    'alltype.adouble',
                                                    'alltype.adoubleprecision',
                                                    'alltype.areal',
                                                    'alltype.adecimal', 
                                                    'alltype.adate', 
                                                    'alltype.atimestamp', 
                                                    'alltype.atime', 
                                                    'alltype.achar', 
                                                    'alltype.avarchar') );
          
      echo "\nprint_r root \n";
      print_r($root);
      echo "\n";
      
      // get each alltype object and print location
      echo "print out data for each alltype \n";
      $alltypeparent = $root ['alltypeparent'];
      $alltype = $alltypeparent[0]['alltype'];
      $count = 1;
      foreach ($alltype as $row) 
      {
         echo "Alltype obtained from the database has id = " . $row['asmallint'] . "\n";
         $count = $count + 1;
      }  
     
      //create a new row in the table
      $newrow = $alltypeparent[0] -> createDataObject('alltype');
      
      // set the properties from the first row (the one that was loaded when the table was created)
      //$newrow->abit             = $alltype[0]->abit; 
      //$newrow->atinyint         = $alltype[0]->atinyint;
      //$newrow->aboolean         = $alltype[0]->aboolean;
      $newrow->asmallint        = $count;
      //$newrow->amediumint       = $alltype[0]->amediumint;
      $newrow->ainteger         = $alltype[0]->ainteger;
      $newrow->abigint          = $alltype[0]->abigint;
      $newrow->afloat           = $alltype[0]->afloat;
      $newrow->adouble          = $alltype[0]->adouble;
      $newrow->adoubleprecision = $alltype[0]->adoubleprecision;
      $newrow->areal            = $alltype[0]->areal;
      $newrow->adecimal         = $alltype[0]->adecimal;
      $newrow->adate            = $alltype[0]->adate;
      //$newrow->adatetime        = $alltype[0]->adatetime;
      $newrow->atimestamp       = $alltype[0]->atimestamp; 
      $newrow->atime            = $alltype[0]->atime;
      //$newrow->ayear            = $alltype[0]->ayear; 
      $newrow->achar            = $alltype[0]->achar; 
      $newrow->avarchar         = "PHP XP DB2";
         
      echo "\nprint_r root \n";
      print_r($root);
           
      // update the data base with the new row
      $das->applyChanges($dbh, $root);
       
    } 
    catch (Exception $e) 
    {
      print "Error: " . $e->getMessage() . "<br/><br/>";
    }
?>
