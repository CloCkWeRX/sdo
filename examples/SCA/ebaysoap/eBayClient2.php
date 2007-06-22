<?php
/*
+----------------------------------------------------------------------+
| Copyright IBM Corporation 2007.                                      |
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
| Author: Graham Charters                                              |
+----------------------------------------------------------------------+
$Id$
*/

include 'SCA/SCA.php';

function getResults($query) {

    $ebay_consumer = SCA::getService('eBaySvc.wsdl', 'ebaysoap', 
                                     array('config' => './config/ebay.ini'));

    // Create the body
    $request = $ebay->createDataObject('urn:ebay:apis:eBLBaseComponents', 'GetSearchResultsRequestType');
    $request->Version = 495;
    $request->Query = 'ipod';
    $request->createDataObject('Pagination');
    $request->Pagination->EntriesPerPage = 10;

    try {
        $results = $ebay->GetSearchResults($request);
        $total_results = $results->PaginationResult->TotalNumberOfEntries;
        echo "<b>{$total_results} results</b><br/><br/>";
        if ($total_results) {
            foreach ($results->SearchResultItemArray as $search_result_items) {
                foreach ($search_result_items as $search_result_item) {
                    echo '<table border="1">';
                    foreach ($search_result_item->Item as $name => $value) {
                        echo "<tr><td>{$name}</td><td>";
                        print_r($value);
                        echo "</td></tr>";
                    }
                    echo '</table></br>';
                }
            }
        }
    } catch (Exception $e) {
        echo '<b>Exception: </b>' . $e->getMessage();
    }

}

?>

<form action="eBayClient.php" method="POST">
    <b>Query: </b>
    <input name="query"/>
    <input type="submit" name="submitquery" value="Submit Query">
</form>

<?php
if (array_key_exists('query', $_POST)) {
    getResults($_POST['query']);
}
?>
