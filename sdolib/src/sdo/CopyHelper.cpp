/* 
+----------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2005.                                  | 
| All Rights Reserved.                                                 |
+----------------------------------------------------------------------+ 
|                                                                      | 
| Licensed under the Apache License, Version 2.0 (the "License"); you  | 
| may not use this file except in compliance with the License. You may | 
| obtain a copy of the License at                                      | 
|http://www.apache.org/licenses/LICENSE-2.0                            |
|                                                                      | 
| Unless required by applicable law or agreed to in writing, software  | 
| distributed under the License is distributed on an "AS IS" BASIS,    | 
| WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      | 
| implied. See the License for the specific language governing         | 
| permissions and limitations under the License.                       | 
+----------------------------------------------------------------------+ 
| Author: Ed Slattery                                                  | 
+----------------------------------------------------------------------+ 

*/
/* $Id$ */

#include "Property.h"
#include "Type.h"
#include "TypeList.h"
#include "Sequence.h"
#include "RefCountingPointer.h"
#include "DataObjectImpl.h"


#include "CopyHelper.h"

namespace commonj{
namespace sdo{

	void CopyHelper::transfer(DataObjectPtr to, DataObjectPtr from, Type::Types t)
	{
// For next driver...
//		DataObject* tob = to;
//		DataObject* fromb = from;
//
//		switch (t)
//		{
//			case Type::BooleanType:
//				((DataObjectImpl*)tob  )->setBoolean(
//				((DataObjectImpl*)fromb)->getBoolean());
//				break;
//			case Type::ByteType:
//				((DataObjectImpl*)tob  )->setByte(
//				((DataObjectImpl*)fromb)->getByte());
//				break;
//			case Type::CharacterType:
//				((DataObjectImpl*)tob  )->setCharacter(
//				((DataObjectImpl*)fromb)->getCharacter());
//				break;
//			case Type::IntegerType: 
//				((DataObjectImpl*)tob  )->setInteger(
//				((DataObjectImpl*)fromb)->getInteger());
//				break;
//			case Type::ShortType:
//				((DataObjectImpl*)tob  )->setShort(
//				((DataObjectImpl*)fromb)->getShort());
//				break;
//			case Type::DoubleType:
//				((DataObjectImpl*)tob  )->setDouble(
//				((DataObjectImpl*)fromb)->getDouble());
//				break;
//			case Type::FloatType:
//				((DataObjectImpl*)tob  )->setFloat(
//				((DataObjectImpl*)fromb)->getFloat());
//				break;
//			case Type::LongType:
//				((DataObjectImpl*)tob  )->setLong(
//				((DataObjectImpl*)fromb)->getLong());
//				break;
//			case Type::DateType:
//				((DataObjectImpl*)tob  )->setDate(
//				((DataObjectImpl*)fromb)->getDate());
//				break;
//			case Type::BigDecimalType: 
//			case Type::BigIntegerType: 
//			case Type::UriType:
//			case Type::StringType:
//				{
//					unsigned int siz = 
//						((DataObjectImpl*)fromb)->getLength();
//					if (siz > 0)
//					{
//						wchar_t * buf = new wchar_t[siz];
//						((DataObjectImpl*)fromb)->getString(buf, siz);
//						((DataObjectImpl*)tob)  ->setString(buf, siz);
//						delete buf;
//					}
//				}
//				break;
//
//			case Type::BytesType:
//				{
//					unsigned int siz = 
//					((DataObjectImpl*)fromb)->getLength();
//					if (siz > 0)
//					{
//						char * buf = new char[siz];
//						((DataObjectImpl*)fromb)->getBytes(buf, siz);
//						((DataObjectImpl*)tob)->setBytes(buf, siz);
//						delete buf;
//					}
//				}
//				break;
//
//			default:
//				break;
//		} // case
	} // method

    DataObjectPtr CopyHelper::copyShallow(DataObjectPtr dataObject)
    {
		
// For next driver...
//		DataObject* theob = dataObject;
//		DASDataFactoryPtr fac = ((DataObjectImpl*)theob)->getDataFactory();
//		if (!fac) return 0;
//		const Type& t = dataObject->getType();
//		DataObjectPtr newob = fac->create(t);
//		if (!newob) return 0;
//
//		PropertyList& pl = dataObject->getProperties();
//		for (int i=0;i < pl.size(); i++)
//		{
//			if (dataObject->isSet(pl[i]))
//			{
//				if (pl[i].getType().isDataObjectType()) continue;
//				if (pl[i].isMany())
//				{
//					DataObjectList& dol = dataObject->getList(pl[i]);
//					for (int i=0;i<dol.size(); i++)
//					{
//						DataObjectPtr dob = fac->create(pl[i].getType());
//						transfer(dob,dol[i],pl[i].getTypeEnum());
//					}
//				}
//				else 
//				{
//					switch (pl[i].getTypeEnum())
//					{
//					case Type::BooleanType:
//						newob->setBoolean(
//							pl[i],
//						    dataObject->getBoolean(pl[i]));
//						break;
//					case Type::ByteType:
//						newob->setByte(
//							pl[i],
//						    dataObject->getByte(pl[i]));
//						break;
//					case Type::CharacterType:
//						newob->setCharacter(
//							pl[i],
//						    dataObject->getCharacter(pl[i]));
//						break;
//					case Type::IntegerType: 
//						newob->setInteger(
//							pl[i],
//						    dataObject->getInteger(pl[i]));
//						break;
//					case Type::ShortType:
//						newob->setShort(
//							pl[i],
//						    dataObject->getShort(pl[i]));
//						break;
//					case Type::DoubleType:
//						newob->setDouble(
//							pl[i],
//						    dataObject->getDouble(pl[i]));
//						break;
//					case Type::FloatType:
//						newob->setFloat(
//							pl[i],
//						    dataObject->getFloat(pl[i]));
//						break;
//					case Type::LongType:
//						newob->setLong(
//							pl[i],
//						    dataObject->getLong(pl[i]));
//						break;
//					case Type::DateType:
//						newob->setDate(
//							pl[i],
//						    dataObject->getDate(pl[i]));
//						break;
//					case Type::BigDecimalType: 
//					case Type::BigIntegerType: 
//					case Type::UriType:
//					case Type::StringType:
//						{
//							unsigned int siz = 
//								dataObject->getLength(pl[i]);
//							if (siz > 0)
//							{
//								wchar_t * buf = new wchar_t[siz];
//								dataObject->getString(pl[i],
//									buf, siz);
//								newob->setString(pl[i], buf, siz);
//								delete buf;
//							}
//						}
//						break;
//
//					case Type::BytesType:
//						{
//							unsigned int siz = 
//								dataObject->getLength(pl[i]);
//							if (siz > 0)
//							{
//								char * buf = new char[siz];
//								dataObject->getBytes(pl[i],
//									buf, siz);
//								newob->setBytes(pl[i], buf, siz);
//								delete buf;
//							}
//						}
//						break;
//
//					default:
//						break;
//					}  // switch
//				} // else
//			} 
//		} 
    	return 0;
	}
    
    DataObjectPtr CopyHelper::copy(DataObjectPtr dataObject)
    {
    	return 0;
    }

}
};

