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



/* Note: To ensure files are included they must be added BEFORE the SCA class   */
/*       This is because in the event that this script is activated through a   */
/*       xmlrpc request, control is passed to SCA and not returned to the script*/
/*       so the include files will not be loaded.                               */
include_once ( "BadTickerException.php" );
include_once ( "BadExchangeException.php" );

include_once ( "SCA/SCA.php" );

/**
 * @service
 * @binding.xmlrpc
 * @binding.jsonrpc
 * @binding.php
 */
class StockQuoteService {

    /**
     * @reference
     * @binding.php ./StockQuote.php
     */
    public $stock_quote;

    /**
     * @reference
     * @binding.xmlrpc http://localhost/examples/SCA/XmlRpc/ExchangeRate.php
     */
    public $exchange_rate;

    /**
     * @param string $ticker (the ticker symbol)
     * @return float (the converted stock quote)
     */
    function getQuote($ticker)
    {
        $rate     = $this->exchange_rate->getRate('USD');
        $quote     = $this->stock_quote->getQuote($ticker);
        return     $rate * $quote;
    }

    /**
     * @param string $ticker (the ticker symbol)
     * @param string $exchange (which exchange e.g. NYSE, London, ...)
     * @return float (the converted stock quote)
     */
    function getQuoteFromExchange($ticker,$exchange)
    {
        if ($exchange == 'RUBBISH') throw new BadExchangeException('Exchange RUBBISH not found');
        return 11144.85;
    }

    /**
     * @param string $ticker (the ticker symbol)
     * @return float (the converted stock quote)
     */
    function getPreciseQuote($ticker)
    {
        return 44.8591919191;
    }

    /**
     * @param string $ticker (the ticker symbol)
     * @return float (the converted stock quote)
     */
    function getIBMQuote($ticker)
    {
        // testing exceptions - throw BadTicker if anything but IBM
        if ($ticker == "IBM") {
            return 76.8;
        } else {
            throw new BadTickerException(  "Invalid Parameter = " . $ticker );
        }
    }

    /**
     * @param string $ticker (the ticker symbol)
     * @return float (the converted stock quote)
     */
    function getCrashingQuote($ticker)
    {
        // there is no rubbish method - will generate a fault that we do not trap and hence should flow back
        $rate     = $this->exchange_rate->rubbish('USD');
    }

}


?>
