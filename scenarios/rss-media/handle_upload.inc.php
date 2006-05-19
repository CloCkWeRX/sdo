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
| Author: Matthew Peters, Graham Charters                              |
+----------------------------------------------------------------------+
$Id$
*/
function handle_upload($from) {

	if (is_uploaded_file($from)) {
		$to = './media/' . $_FILES['media']['name'];
		$po = pathinfo($to);
		/**
		 * Check we got a file with the right extension
		 */
		if (strtolower($po['extension']) == 'mov') {
			if (!stat('./media')) {
				mkdir('./media');
			}
			/**
			 * Copy the file to the desired location
			 */
			$rc = move_uploaded_file($from,$to);
			if ($rc) {
				return true;
			} else {
				echo "<strong>Error uploading: failed to move uploaded file</strong><br/>";
			}
		} else {
			echo "<strong>Uploaded file was not an MOV file and was ignored</strong><br/>";
			return false;
		}
	} else {
		/**
		 * Report any upload problems
		 */
		switch($_FILES['media']['error']) {
			case 0:
				echo "<strong>Great, smashin, super.</strong><br/>";
				break;
			case UPLOAD_ERR_INI_SIZE:
				echo "<strong>Error code 1 uploading: file size exceeds upload_max_filesize ini directive</strong><br/>";
				break;
			case UPLOAD_ERR_FORM_SIZE:
				echo "<strong>Error code 2 uploading: file size exceeds MAX_FILE_SIZE html directive</strong><br/>";
				break;
			case UPLOAD_ERR_PARTIAL:
				echo "<strong>Error code 3 uploading: file size was not completely uploaded</strong><br/>";
				break;
			case UPLOAD_ERR_NO_FILE:
				echo "<strong>Error code 4 uploading: no file was specified for upload</strong><br/>";
				break;
		}
		return false;
	}
}


?>