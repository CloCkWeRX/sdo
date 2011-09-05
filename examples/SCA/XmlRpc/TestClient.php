<?php

/*
+-----------------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2006.                                         |
| All Rights Reserved.                                                        |
+-----------------------------------------------------------------------------+
| Licensed under the Apache License, Version 2.0 (the "License"); you may not |
| use this file except in compliance with the License. You may obtain a copy  |
| of the License at -                                                         |
|                                                                             |
|                   http://www.apache.org/licenses/LICENSE-2.0                |
|                                                                             |
| Unless required by applicable law or agreed to in writing, software         |
| distributed under the License is distributed on an "AS IS" BASIS, WITHOUT   |
| WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.            |
| See the License for the specific language governing  permissions and        |
| limitations under the License.                                              |
+-----------------------------------------------------------------------------+
| Author: Graham Charters,                                                    |
|         Matthew Peters,                                                     |
|         Megan Beynon,                                                       |
|         Chris Miller,                                                       |
|         Caroline Maynard,                                                   |
|         Simon Laws,                                                         |
|         Rajini Sivaram                                                      |
+-----------------------------------------------------------------------------+
$Id$
*/



include_once "SCA/SCA.php";


try
{
    $local_service      = SCA::getService('./StockQuoteService.php');
    $remote_service     = SCA::getService('http://localhost/examples/SCA/XmlRpc/StockQuoteService.php', "XmlRpc");

    /**
     * Call the component first locally then remotely, and compare
     * Normal paths first
     */

    echo    '<p>Calling StockQuoteService locally</p><p>';
    echo     $local_service->getQuote('IBM') . "\n";
    echo     $local_service->getPreciseQuote('IBM') . "\n";
    echo     $local_service->getQuoteFromExchange('IBM','NYSE') . "</p>\n";

    echo    '<p>Calling StockQuoteService remotely</p><p>';
    echo     $remote_service->getQuote('IBM') . "\n";
    echo     $remote_service->getPreciseQuote('IBM') . "\n";
    echo     $remote_service->getQuoteFromExchange('IBM','NYSE') . "</p>\n";

    /**
     * Call the component locally and remotely and compare results.
     * Ensure they are close (note that last call is 4.85 different)
     */
    assert (abs($local_service->getQuote('IBM') - $remote_service->getQuote('IBM')) < 1);
    assert (abs($local_service->getPreciseQuote('IBM') - $remote_service->getPreciseQuote('IBM')) < 1);
    assert (abs($local_service->getQuoteFromExchange('IBM','NYSE') - $remote_service->getQuoteFromExchange('IBM','NYSE')) < 10);
}
catch ( SCA_RuntimeException $se )
{
    print "<b>{$se->decodeCode($se->getCode() )}</b> :: {$se->__toString()} \n";

}

?>
