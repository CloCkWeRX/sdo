<?php
/*
+-----------------------------------------------------------------------------+
| Copyright IBM Corporation 2006,2007.                                        |
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
|         Simon Laws                                                          |
+-----------------------------------------------------------------------------+
$Id: SCA_Exceptions.php 234864 2007-05-03 18:23:57Z mfp $
*/


class SCA_RuntimeException extends Exception
{
    public function __toString()
    {
        // NOTE we use get_class() and not __CLASS__ because
        // __CLASS__ will always give us SCA_RuntimeExcpetion even for
        // derived classes, whereas get_class($this) will give us the
        // class name of the derived class
        return get_class($this) . ": " . $this->getMessage();
    }
}

class SCA_AuthenticationException      extends SCA_RuntimeException {}
class SCA_BadRequestException          extends SCA_RuntimeException {}
class SCA_ConflictException            extends SCA_RuntimeException {}
class SCA_InternalServerErrorException extends SCA_RuntimeException {}
class SCA_MethodNotAllowedException    extends SCA_RuntimeException {}
class SCA_NotFoundException            extends SCA_RuntimeException {}
class SCA_ServiceUnavailableException  extends SCA_RuntimeException {}
class SCA_UnauthorizedException        extends SCA_RuntimeException {}
