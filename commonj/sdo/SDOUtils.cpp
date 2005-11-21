/* 
+----------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2005.                                  | 
| All Rights Reserved.                                                 |
+----------------------------------------------------------------------+ 
|                                                                      | 
| Licensed under the Apache License, Version 2.0 (the "License"); you  | 
| may not use this file except in compliance with the License. You may | 
| obtain a copy of the License at                                      | 
|  http://www.apache.org/licenses/LICENSE-2.0                          |
|                                                                      | 
| Unless required by applicable law or agreed to in writing, software  | 
| distributed under the License is distributed on an "AS IS" BASIS,    | 
| WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      | 
| implied. See the License for the specific language governing         | 
| permissions and limitations under the License.                       | 
+----------------------------------------------------------------------+ 
| Author:  Merle Sterling                                                            | 
+----------------------------------------------------------------------+ 
*/

#include "commonj/sdo/SDOUtils.h"

using namespace std;

//
// Utility methods to print a DataObject tree
//

namespace commonj {
	namespace sdo {

		int SDOUtils::increment = 0;


//////////////////////////////////////////////////////////////////////////
// Print Tabs
//////////////////////////////////////////////////////////////////////////

		void SDOUtils::printTabs()
		{
			for (int ind=0; ind < increment; ind++)
			{
				cout << "\t";
			}
		}

//////////////////////////////////////////////////////////////////////////
// Print a DatObject tree
//////////////////////////////////////////////////////////////////////////

		void SDOUtils::printDataObject(DataObjectPtr dataObject)
		{
			increment = 0;
	
			cout << ">>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> start of DO" 
				 << endl;
	
			if (!dataObject)return;

			const Type& dataObjectType = dataObject->getType();
			printTabs();
			cout << "DataObject type: " 
				 << dataObjectType.getURI() 
				 << "#" << dataObjectType.getName() << endl;
	
			increment++;
	
			//////////////////////////////////////////////////////////////
			// Iterate over all the properties
			//////////////////////////////////////////////////////////////
			PropertyList pl = dataObject->getInstanceProperties();
			for (int i = 0; i < pl.size(); i++)
			{
				printTabs();
				cout << "Property: " << pl[i].getName() << endl;
		
				const Type& propertyType = pl[i].getType();
		
				printTabs();

				cout << "Property Type: " 
					 << propertyType.getURI() 
					 << "#" << propertyType.getName() << endl;
		
				if (dataObject->isSet(pl[i]))
				{
			
					///////////////////////////////////////////////////////////
					// For a many-valued property get the list of values
					///////////////////////////////////////////////////////////
					if (pl[i].isMany())
					{
						increment++;
						DataObjectList& dol = dataObject->getList(pl[i]);
						for (int j = 0; j <dol.size(); j++)
						{
							printTabs();
							cout << "Value " << j <<endl;
							increment++;
							printDataObject(dol[j]);
							increment--;
							cout << endl;
						}
						increment--;
					} // end IsMany
		
					//////////////////////////////////////////////////////////////////////
					// For a primitive data type print the value
					//////////////////////////////////////////////////////////////////////
					else if (propertyType.isDataType())
					{
						printTabs();
						cout<< "Property Value: " 
							<< dataObject->getCString(pl[i]) <<endl ; 
					}
			
					//////////////////////////////////////////////////////////////////////
					// For a dataobject print the do
					//////////////////////////////////////////////////////////////////////
					else
					{
						increment++;
						printDataObject(dataObject->getDataObject(pl[i]));
						increment--;
					}
				}
				else
				{
					printTabs();
					cout << "Property Value: not set" <<endl ; 
				}
		
			}
			increment--;
			cout << "<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< end of do" << endl;
		}
	};
};
