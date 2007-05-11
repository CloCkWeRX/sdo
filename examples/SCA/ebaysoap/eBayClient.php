<?php

include 'SCA/SCA.php';

function getResults($query) {

    $ebay_consumer = SCA::getService('eBayConsumer.php');

    try {
        $results = $ebay_consumer->GetSearchResults($query);
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
