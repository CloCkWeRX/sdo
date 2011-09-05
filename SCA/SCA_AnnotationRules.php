<?php
/**
 * +-----------------------------------------------------------------------------+
 * | (c) Copyright IBM Corporation 2006.                                         |
 * | All Rights Reserved.                                                        |
 * +-----------------------------------------------------------------------------+
 * | Licensed under the Apache License, Version 2.0 (the "License"); you may not |
 * | use this file except in compliance with the License. You may obtain a copy  |
 * | of the License at -                                                         |
 * |                                                                             |
 * |                   http://www.apache.org/licenses/LICENSE-2.0                |
 * |                                                                             |
 * | Unless required by applicable law or agreed to in writing, software         |
 * | distributed under the License is distributed on an "AS IS" BASIS, WITHOUT   |
 * | WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.            |
 * | See the License for the specific language governing  permissions and        |
 * | limitations under the License.                                              |
 * +-----------------------------------------------------------------------------+
 * | Author: Graham Charters,                                                    |
 * |         Matthew Peters,                                                     |
 * |         Megan Beynon,                                                       |
 * |         Chris Miller,                                                       |
 * |         Caroline Maynard,                                                   |
 * |         Simon Laws                                                          |
 * +-----------------------------------------------------------------------------+
 * $Id: SCA_AnnotationRules.php 234945 2007-05-04 15:05:53Z mfp $
 *
 * PHP Version 5
 *
 * @category SCA_SDO
 * @package  SCA_SDO
 * @author   Graham Charters <gcc@php.net>
 * @license  Apache http://www.apache.org/licenses/LICENSE-2.0
 * @link     http://www.osoa.org/display/PHP/
 */

/**
 * The following script contains some simple methods that can be combined to
 * check that the format rules for the annotation of a parameter line, and return
 * line are obeyed, (or at least are sensible).
 *
 * Note : There are a few useful constants defined that can be accessed by other
 *        classes that include this file.
 *
 * @category SCA_SDO
 * @package  SCA_SDO
 * @author   Graham Charters <gcc@php.net>
 * @license  Apache http://www.apache.org/licenses/LICENSE-2.0
 * @link     http://www.osoa.org/display/PHP/
*/
class SCA_AnnotationRules
{
    const PARAM            = "@param";
    const RETRN            = "@return";
    const NAME             = "@name";
    const TYPES            = "@types";
    const AT               = "@";
    const DOLLAR           = "\$";
    const BACKSLASH        = "\\";
    const FORESLASH        = "/";
    const DOT              = ".";
    const SPACE            = " ";
    const LEFTBRACKET      = "(";
    const RIGHTBRACKET     = ")";
    const BRACKETS         = "()";
    const STAR             = "*";

    const   PARAM_ANNOTATION        = 'parameters';
    const   RETRN_ANNOTATION        = 'return';

    protected $token         = self::DOLLAR;

    // TODO make this the right list
    protected $dataTypeArray = array(
        'boolean', 'bool', 'string', 'integer',
        'array', 'float', 'double', 'real'
    );

    /**
     * Is there any method annotations at the beginning of the line
     *
     * @param string $line Line to check
     *
     * @return boolean
     */
    public function isMethodAnnotation($line)
    {
        return ($this->isParameter($line) || $this->isReturn($line) ||
                 $this->isName($line));
    }

    /**
     * Is the parameter annotation at the beginning of the line
     *
     * @param string $line Line to check
     *
     * @return boolean
     */
    public function isParameter($line)
    {
        return (strpos($line, self::PARAM) != false);
    }

    /**
     * Is the return annotation at the beginning of the line
     *
     * @param string $line Line to check
     *
     * @return boolean
     */
    public function isReturn($line)
    {
        return (strpos($line, self::RETRN) != false);
    }

    /**
     * Is the name annotation at the beginning of the line
     *
     * @param string $line Line to check
     *
     * @return boolean
     */
    public function isName($line)
    {
        return (strpos($line, self::NAME) != false);
    }

    /**
     * Is the data type defined as an object.
     *
     * @param string $word Word to check
     *
     * @return boolean
     */
    public function isDataObject($word)
    {
        return (!$this->isSupportedPrimitiveType($word));
    }

    /**
     * Does the word start with an '$' sign denoting a variable definition
     *
     * @param string $word Word to check
     *
     * @return boolean
     */
    public function isVariable($word)
    {
        return (strpos($word, self::DOLLAR) === 0);

    }

    /**
     * Check that the name given for the xsd definition is the same as the
     * variable name.
     *
     * @param string $name         Name
     * @param string $variableName Variable
     *
     * @return boolean
     */
    public function matchXsdName($name, $variableName)
    {
        $namepart = trim($variableName, $this->token);

        return (strcmp($name, $namepart) === 0);

    }

    /**
     * Does the word resemble a namespace definition (does it have some 'slash
     * characters somewhere inside the word).
     *
     * @param string $word Word to check
     *
     * @return boolean
     */
    public function looksLikeNamespace($word)
    {
        return ((strpos($word, self::BACKSLASH) >= 0)
        || (strpos($word, self::FORESLASH) >= 0));

    }

    /**
     * Check that the type of data is a supported data type.
     *
     * @param string $type Type to check
     *
     * @return boolean
     */
    public function isSupportedPrimitiveType($type)
    {
        $return = false;

        /* Is there a value worth testing?                                     */
        if (strlen($type) > 0) {
            foreach ($this->dataTypeArray as $dataType) {
                if (strpos($dataType, $type) !== false) {
                    $return = true;
                    break;
                }
            }
        }

        return $return;

    }

    /**
     * An annotation may be 'formatted' with extra spaces and/or tab chars. These
     * characters are removed, placing the words in a line into an array.
     * The function also makes sure that any comment (delimited by brackets)
     * remains as a string, and that extranious characters, and php variable
     * symbols are removed.
     *
     * @param string $line The line to be parsed
     *
     * @return array Containing elements of the parsed line
     */
    public static function parseAnnotation($line)
    {
        $thesePieces  = null;
        $i            = 0;
        $comment      = false;
        $commentArray = null;
        $j            = 0;
        $line         = preg_replace("{[\t]+}", " ", $line);
        $arrayOfLine  = explode(' ', (trim($line)));

        /**
         * Make up an array containing only words reserved words, and if there is
         * a comment filter it out into a separate array.
         */
        foreach ($arrayOfLine as $element) {
            /* When the the array captured a 'space'                           */
            if (strlen($element) !== 0) {
                /* .. and the contents are not the comment-star                */
                if ($element !== self::STAR) {
                    /* Alter the flow when the word is not a reserved word     */
                    if ((strpos($element, self::LEFTBRACKET)) !== false) {
                        $comment = true;
                    }

                    /* Put the 'word' into either the comment or the definitions array  */
                    if ($comment === true) {
                        $commentArray[$j++] = $element;
                    } else {
                        $thesePieces[$i++] = trim($element);
                    }

                }

            }

        }

        /* Putting back the comment into a single element                      */
        if ($commentArray !== null) {
            $thesePieces[$i] = trim((implode(' ', $commentArray)), self::BRACKETS);
        }


        return $thesePieces;
    }

    /**
     * Make sure that there are enough definitions to make up a wsdl entry
     *
     * @param array $inThisArray Arrray to check
     *
     * @return boolean
     */
    public static function enoughPieces($inThisArray)
    {
        $entries = count($inThisArray);

        /* Enough entries for something                                        */
        // TODO looks like we missed something here
        // This is only ever going to return true, I think :-)
        // I think the person who wrote this meant ||
        // but it's only a crude check of the parameter and return
        // lines anyway and not right
        return !($entries < 2 && $entries > 4);

    }

    /**
     * Make the empty set of annotation conversions
     *
     * @return array
     */
    public static function createEmptyAnnotationArray()
    {
        $emptySet                           = array();
        $emptySet[self::PARAM_ANNOTATION] = array();
        $emptySet[self::RETRN_ANNOTATION] = null;

        return $emptySet;
    }/*End create empty annotation array                                       */

}

