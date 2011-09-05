<?php
/*
+----------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2005, 2006.                            |
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
| Author: Matthew Peters                                               |
+----------------------------------------------------------------------+
$Id: SettingListHelper.php 220738 2006-09-28 19:25:00Z cem $
*/

/**
* Helper routines for use with an SDO_DAS_SettingList. Ideally this is behaviour
* that belongs to the SettingList itself, but we do not own the implementation and
* so must add the behaviour with this helper routine
*/

class SDO_DAS_Relational_SettingListHelper
{

    public static function getSettingsAsArray(SDO_DAS_SettingList $setting_list)
    {
        $settings_as_array = array();

        foreach ($setting_list as $setting) {
            if ($setting->isSet()) {
                $property_name  = $setting->getPropertyName();
                $old_value      = $setting->getValue();
                $settings_as_array[$property_name] = $old_value;
            }
        }
        return $settings_as_array;
    }

}

?>
