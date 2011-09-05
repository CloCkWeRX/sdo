<?php
/*
+-----------------------------------------------------------------------------+
| Copyright IBM Corporation 2006.                                             |
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
| Authors: Graham Charters, Megan Beynon                                      |
|                                                                             |
+-----------------------------------------------------------------------------+
$Id$
*/

require_once 'SCA/SCA.php';

/**
 * A services which takes a list of names and return the list
 * with "Hello" prepended to each.
 *
 * @service
 * @binding.soap
 * @types http://example.org/names ./names.xsd
 *
 */
class BatchService
{

    /**
     * Say "Hello" to a batch of names.
     * Note: this demonstrates how to declare data structures in
     * the @param and @return annotations.
     *
     * @param people $names http://example.org/names
     * @return people http://example.org/names
     */
    function sayHello($names)
    {

        /*********************************************************************/
        /* Creating an SDO for the replies.  This is not strictly necessary  */
        /* but is done to demonstate how a service gets an SDO for a type it */
        /* declares in the @types annotation.                                */
        /*********************************************************************/
        $replies = SCA::createDataObject('http://example.org/names', 'people');

        // Iterate through each names to build up the replies
        foreach ($names->name as $name) {
            $replies->name[] = "Hello $name";
        }

        return $replies;
    }
}
