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
| Author: Ed Slattery                                                  | 
+----------------------------------------------------------------------+ 

*/

#include "DataObjectImpl.h"


 
#include "SDORuntimeException.h"

#include "Property.h"
#include "Type.h"
#include "TypeList.h"
#include "Sequence.h"
#include "SequenceImpl.h"

#include "PropertyList.h"

#include "Logger.h"

#include "TypeImpl.h"

#include "ChangeSummaryImpl.h"

///////////////////////////////////////////////////////////////////////////////
// Macro versions  of the getter/setter interfaces 
///////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////
// gets a non-char primitive from a property index
////////////////////////////////////////////////////
namespace commonj{
namespace sdo {

#define getPrimitive(primval,retval,defval)\
	retval DataObjectImpl::get ##primval (unsigned int propertyIndex)\
	{\
		validateIndex(propertyIndex);\
		if ((getType().getProperty(propertyIndex).isMany()))\
		{\
			string msg("Get value not available on many valued property:");\
			msg += getType().getProperty(propertyIndex).getName();\
			SDO_THROW_EXCEPTION("get value", SDOUnsupportedOperationException,\
				msg.c_str());\
		}\
  		DataObjectImpl* d = getDataObjectImpl(propertyIndex);\
		if (d != 0) {\
			if (!d->isNull())return d->get ##primval ();\
		}\
  		PropertyImpl& p = getPropertyImplFromIndex(propertyIndex);\
		return p.get ##primval ##Default();\
	}

////////////////////////////////////////////////////
// gets a character primitive from a property index
////////////////////////////////////////////////////

#define getCharsBasic(primval,retval,defval)\
	unsigned int DataObjectImpl::get ##primval (unsigned int propertyIndex, retval valptr , unsigned int max)\
	{\
		validateIndex(propertyIndex);\
		if ((getType().getProperty(propertyIndex).isMany()))\
		{\
			string msg("Get value not available on many valued property:");\
			msg += getType().getProperty(propertyIndex).getName();\
			SDO_THROW_EXCEPTION("character getter", SDOUnsupportedOperationException,\
				msg.c_str());\
		}\
 		DataObjectImpl* d = getDataObjectImpl(propertyIndex);\
		if (d != 0) { \
			if (!d->isNull()) return d->get ##primval ( valptr , max);\
		}\
  		PropertyImpl& p = getPropertyImplFromIndex(propertyIndex);\
		return p.get ##primval ##Default( valptr , max);\
	}


/////////////////////////////////////////////////
// set non-char primitive by index
// May throw SDOPropertyNotFoundException  or SDOTypeNotFoundException 
/////////////////////////////////////////////////

#define setPrimitive(primval,primtype,primnam)\
	void DataObjectImpl::set ##primval (unsigned int propertyIndex, primtype value)\
	{\
	validateIndex(propertyIndex);\
	if ((getType().getProperty(propertyIndex).isMany()))\
	{\
		string msg("Set value not available on many valued property:");\
		msg += getType().getProperty(propertyIndex).getName();\
		SDO_THROW_EXCEPTION("set value",SDOUnsupportedOperationException,\
			msg.c_str());\
	}\
	PropertyValueMap::iterator i;\
	for (i = PropertyValues.begin(); i != PropertyValues.end();++i)\
		{\
		if ((*i).first == propertyIndex)\
			{\
			logChange(propertyIndex);\
			(*i).second->unsetNull();\
			(*i).second->set ##primval (value);\
			return;\
			}\
		}\
		DASDataFactory* df = getDataFactory();\
		DataObjectImpl* b = new DataObjectImpl(df, df->getType(Type::SDOTypeNamespaceURI, primnam));\
		b->setContainer(this);\
		b->setApplicableChangeSummary();\
		PropertyValues.insert(std::make_pair(propertyIndex,	b));\
		logChange(propertyIndex);\
 		b->set ##primval (value);\
 		return;\
	}


/////////////////////////////////////////////////////
// set a character primitive by index
////////////////////////////////////////////////////

#define setCharsBasic(primval,primtype,primnam)\
	void DataObjectImpl::set ##primval (unsigned int propertyIndex, primtype value, unsigned int len)\
	{\
	validateIndex(propertyIndex);\
	if ((getType().getProperty(propertyIndex).isMany()))\
	{\
		string msg("Set value not available on many valued property:");\
		msg += getType().getProperty(propertyIndex).getName();\
		SDO_THROW_EXCEPTION("setter",SDOUnsupportedOperationException,\
			msg.c_str());\
	}\
	PropertyValueMap::iterator i;\
	for (i = PropertyValues.begin(); i != PropertyValues.end();++i)\
		{\
		if ((*i).first == propertyIndex)\
			{\
			logChange(propertyIndex);\
			(*i).second->unsetNull();\
			(*i).second->set ##primval (value, len);\
			return;\
			}\
		}\
		DASDataFactory* df = getDataFactory();\
		DataObjectImpl* b = new DataObjectImpl(df, df->getType(Type::SDOTypeNamespaceURI, primnam));\
		b->setContainer(this);\
		b->setApplicableChangeSummary();\
		PropertyValues.insert(std::make_pair(propertyIndex,b));\
		logChange(propertyIndex);\
 		b->set ##primval (value, len);\
 		return;\
	}


////////////////////////////////////////////////////////////
// get a non-char primitive from a path
////////////////////////////////////////////////////////////

#define getPrimitiveFromPath(primval, retval, defval)\
	retval DataObjectImpl::get ##primval (const char* path)\
	{\
		DataObjectImpl* d;\
		char *spath = 0;\
		char* prop = 0;\
         try {\
			spath = DataObjectImpl::stripPath(path);\
  			prop = findPropertyContainer(spath,&d);\
			if (spath){\
			    delete spath;\
				spath = 0;\
			}\
			if (d != 0) {\
				if (prop == 0 || (strlen(prop) == 0)) {\
					return d->get ##primval ();\
				}\
				else {\
					PropertyImpl& p = d->getTypeImpl().getPropertyImpl(prop);\
					if (p.isMany())\
					{\
						long l;\
						DataObjectImpl* doi = d->findDataObject(prop,&l);\
						delete prop;\
						prop = 0;\
						if (doi != 0)	{\
							return doi->get ## primval();\
						}\
						string msg("Get value - index out of range:");\
						msg += path;\
						SDO_THROW_EXCEPTION("getter", SDOIndexOutOfRangeException,\
						msg.c_str());\
					}\
					else\
					{\
						delete prop;\
						prop = 0;\
						if (!isSet(p)) {\
							return p.get ## primval ##Default();\
						}\
						return d->get ##primval (p);\
					}\
				}\
				if (prop) {\
                    delete prop;\
                    prop = 0;\
				}\
			}\
			string msg("Get value  - path not found");\
			SDO_THROW_EXCEPTION("getter", SDOPathNotFoundException,\
			msg.c_str());\
		}\
		catch (SDORuntimeException e) {\
		   if (spath)delete spath;\
		   if (prop) delete prop;\
		   SDO_RETHROW_EXCEPTION("getter", e);\
		}\
	}


////////////////////////////////////////////////////////////
// get a char primitive from a path
////////////////////////////////////////////////////////////

#define getCharsFromPath(primval, retval, defval)\
	unsigned int DataObjectImpl::get ##primval (const char* path, retval valptr , unsigned int max)\
	{\
		DataObjectImpl* d;\
		char *spath = 0;\
		char* prop = 0;\
         try {\
			spath = DataObjectImpl::stripPath(path);\
  			prop = findPropertyContainer(spath,&d);\
			if (spath){\
			    delete spath;\
				spath = 0;\
			}\
			if (d != 0) {\
				if (prop == 0 || (strlen(prop) == 0)) {\
					return d->get ##primval ( valptr , max);\
				}\
				else {\
					PropertyImpl& p = d->getTypeImpl().getPropertyImpl(prop);\
					if (p.isMany())\
					{\
						long l;\
						DataObjectImpl* doi = d->findDataObject(prop,&l);\
						delete prop;\
						prop = 0;\
						if (doi != 0)	{\
							return doi->get ## primval (valptr, max);\
						}\
						string msg("Get value - index out of range");\
						msg += path;\
						SDO_THROW_EXCEPTION("getChars", SDOIndexOutOfRangeException,\
						msg.c_str());\
					}\
					else { \
						delete prop;\
						prop = 0;\
						if (!isSet(p)) {\
							return p.get ## primval ##Default( valptr , max);\
						}\
						return d->get ##primval (p, valptr , max);\
					}\
				}\
				if (prop) {\
                    delete prop;\
                    prop = 0;\
				}\
			}\
			string msg("Get value  - path not found");\
			SDO_THROW_EXCEPTION("getCString", SDOPathNotFoundException,\
			msg.c_str());\
		}\
		catch (SDORuntimeException e) {\
		   if (spath)delete spath;\
		   if (prop) delete prop;\
		   SDO_RETHROW_EXCEPTION("character getter", e);\
		}\
	}



/////////////////////////////////////////////////////////////
// set a non-char primitive at a path
////////////////////////////////////////////////////////////

#define setPrimitiveFromPath(primval,setval)\
	void DataObjectImpl::set ##primval (const char* path, setval value)\
	{\
		DataObjectImpl *d;\
		char* spath = 0;\
		char* prop = 0;\
		try {\
			spath = DataObjectImpl::stripPath(path);\
  			prop = findPropertyContainer(spath,&d);\
			if (spath) {\
			    delete spath;\
				spath = 0;\
			}\
			if (d != 0)\
			{\
				if (prop == 0 || (strlen(prop) == 0)) {\
					d->set ##primval (value);\
				}\
				else {\
					const Property& p = d->getType().getProperty(prop);\
					if (p.isMany())\
					{\
						long l;\
						DataObjectList& dol = d->getList(p);\
						DataObjectImpl* doi = d->findDataObject(prop,&l);\
						delete prop;\
						prop = 0;\
						if (doi != 0)	{\
							doi->set ## primval (value);\
						}\
						else {\
							dol.append(value);\
						}\
					}\
					else {\
						delete prop;\
						prop = 0;\
						d->set ##primval (p,value);\
					}\
				}\
			}\
			if (prop){\
			    delete prop;\
				prop = 0;\
			}\
		}\
		catch (SDORuntimeException e) {\
		    if (spath) delete spath;\
            if (prop) delete prop;\
			SDO_RETHROW_EXCEPTION("setter",e);\
		}\
	}

/////////////////////////////////////////////////////////////
// set a char primitive at a path
////////////////////////////////////////////////////////////

#define setCharsFromPath(primval,setval)\
	void DataObjectImpl::set ##primval (const char* path, setval value, unsigned int len)\
	{\
		DataObjectImpl *d;\
		char* spath = 0;\
		char* prop = 0;\
		try {\
			spath = DataObjectImpl::stripPath(path);\
  			prop = findPropertyContainer(spath,&d);\
			if (spath) {\
			    delete spath;\
				spath = 0;\
			}\
			if (d != 0)\
			{\
				if (prop == 0 || (strlen(prop) == 0)) {\
					d->set ##primval (value, len);\
				}\
				else {\
					const Property& p = d->getType().getProperty(prop);\
					if (p.isMany())\
					{\
						long l;\
						DataObjectList& dol = d->getList(p);\
						DataObjectImpl* doi = d->findDataObject(prop,&l);\
						delete prop;\
						prop = 0;\
						if (doi != 0)	{\
							doi->set ## primval (value, len);\
						}\
						else {\
							dol.append(value,len);\
						}\
					}\
					else { \
						delete prop;\
						prop = 0;\
						d->set ##primval (p,value, len);\
					}\
				}\
			}\
			if (prop){\
			    delete prop;\
				prop = 0;\
			}\
		}\
		catch (SDORuntimeException e) {\
		    if (spath) delete spath;\
            if (prop) delete prop;\
			SDO_RETHROW_EXCEPTION("setter",e);\
		}\
	}




/////////////////////////////////////////////////////////////
// get a non-char primitive from a property
////////////////////////////////////////////////////////////

#define getPrimitiveFromProperty(primval,retval)\
	retval DataObjectImpl::get ##primval (const Property& property)\
	{\
		return get ##primval (getPropertyIndex(property));\
	}


/////////////////////////////////////////////////////////////
// get a char primitive from a property
////////////////////////////////////////////////////////////

#define getCharsFromProperty(primval,retval)\
	unsigned int DataObjectImpl::get ##primval (const Property& property, retval val, unsigned int max)\
	{\
		return get ##primval (getPropertyIndex(property), val, max);\
	}


/////////////////////////////////////////////////////////////
// set a non-char primitive from a property
////////////////////////////////////////////////////////////

#define setPrimitiveFromProperty(primval,primtype)\
  void DataObjectImpl::set ##primval (const Property& property, primtype value)\
  {\
	  set ##primval (getPropertyIndex(property),value);\
  }

/////////////////////////////////////////////////////////////
// set a char primitive from a property
////////////////////////////////////////////////////////////

#define setCharsFromProperty(primval,primtype)\
  void DataObjectImpl::set ##primval (const Property& property, primtype value, unsigned int len)\
  {\
	  set ##primval (getPropertyIndex(property),value, len);\
  }


// end of macros

//
//  A data object is a representation of some structured data. 
//  It is the fundamental component in the SDO (Service Data Objects) package.
//  Data objects support reflection, path-based accesss, convenience creation and deletion methods, 
//  and the ability to be part of a data graph
//  
//  Each data object holds its data as a series of properties. 
//  Properties can be accessed by name, property index, or using the property meta object itself. 
//  A data object can also contain references to other data objects, through reference-type properties.
//  
//  A data object has a series of convenience accessors for its properties. 
//  These methods either use a path (char*), 
//  a property index, 
//  or the property's meta object itself, to identify the property.
//  Some examples of the path-based accessors are as follows:
// <pre>
//  DataObject company = ...;
//  company.get("name");                   is the same as company.get(company.getType().getProperty("name"))
//  company.set("name", "acme");
//  company.get("department.0/name")       is the same as ((DataObject)((List)company.get("department")).get(0)).get("name")
//                                         .n  indexes from 0 ... implies the name property of the first department
//  company.get("department[1]/name")      [] indexes from 1 ... implies the name property of the first department
//  company.get("department[number=123]")  returns the first department where number=123
//  company.get("..")                      returns the containing data object
//  company.get("/")                       returns the root containing data object
// </pre> 
//  <p> There are general accessors for properties, i.e.,  get and set, 
// as well as specific accessors for the primitive types and commonly used data types like 
// char*, Date, List.
// 

	



	// setters and getters from a path specification 

	getCharsFromPath(String, wchar_t* , 0);
	getCharsFromPath(Bytes, char* , 0);
	setCharsFromPath(String, const wchar_t*);
	setCharsFromPath(Bytes, const char*);
	getCharsFromProperty(String,wchar_t*);
	getCharsFromProperty(Bytes,char*);
	setCharsFromProperty(String,const wchar_t*);
	setCharsFromProperty(Bytes,const char*);
	getCharsBasic(String,wchar_t*,0);
	getCharsBasic(Bytes,char*,0);
	setCharsBasic(String,const wchar_t*,"String");
	setCharsBasic(Bytes,const char*,"Bytes");


	// Convenience methods for string/bytes length

	unsigned int DataObjectImpl::getLength(const Property& p)
	{
		switch (p.getType().getTypeEnum()) {
		case Type::BooleanType:
			return BOOL_SIZE;
		case Type::CharacterType:
		case Type::ByteType:
			return BYTE_SIZE;
		case Type::ShortType:
		case Type::IntegerType:
		case Type::LongType:
			return MAX_LONG_SIZE;
		case Type::FloatType:
			return MAX_FLOAT_SIZE;
		case Type::DoubleType:
			return MAX_DOUBLE_SIZE;
		case Type::BigDecimalType:
		case Type::BigIntegerType:
		case Type::UriType:
		case Type::StringType:
			return getString(p,0,0);
		case Type::BytesType:
			return getBytes(p,0,0);
		default:
			return 0;
		}
	}

	unsigned int DataObjectImpl::getLength()
	{
		switch (getType().getTypeEnum()) {
		case Type::BooleanType:
			return BOOL_SIZE;
		case Type::CharacterType:
		case Type::ByteType:
			return BYTE_SIZE;
		case Type::ShortType:
		case Type::IntegerType:
		case Type::LongType:
			return MAX_LONG_SIZE;
		case Type::FloatType:
			return MAX_FLOAT_SIZE;
		case Type::DoubleType:
			return MAX_DOUBLE_SIZE;
		case Type::BigDecimalType:
		case Type::BigIntegerType:
		case Type::UriType:
		case Type::StringType:
			return getString(0,0);
		case Type::BytesType:
			return getBytes(0,0);
		default:
			return 0;
		}
	}

	unsigned int DataObjectImpl::getLength(const char* path)
	{
		DataObjectImpl* d;
		char * spath = DataObjectImpl::stripPath(path);
  		char * prop = findPropertyContainer(spath,&d);
		if (spath) 	delete spath;
		if (d != 0) {
			if (prop == 0 || (strlen(prop) == 0)) {
				return 0;
			}
			else 
			{
				const Property& p  = d->getType().getProperty(prop);
				delete prop;
				return getLength(p);
			}
		}
		else 
		{
			if (prop)
			{
				const Property& p  = getType().getProperty(prop);
				delete prop;
				
				return getLength(p);
			}
			else 
			{
				return 0;
			}
		}
	}

	unsigned int DataObjectImpl::getLength(unsigned int index)
	{
		return getLength(getPropertyFromIndex(index));
	}

	getPrimitiveFromPath(Boolean,bool,false);
	getPrimitiveFromPath(Byte,char,0);
	getPrimitiveFromPath(Character,wchar_t,0);
	getPrimitiveFromPath(Short,short,0);
	getPrimitiveFromPath(Integer,long,0);
	getPrimitiveFromPath(Long,int64_t,0L);
	getPrimitiveFromPath(Double,long double,0.0);
	getPrimitiveFromPath(Float,float,0.0);
	getPrimitiveFromPath(Date,time_t,0);


	setPrimitiveFromPath(Boolean,bool);
	setPrimitiveFromPath(Byte,char);
	setPrimitiveFromPath(Character,wchar_t);
	setPrimitiveFromPath(Short,short);
	setPrimitiveFromPath(Integer,long);
	setPrimitiveFromPath(Long,int64_t);
	setPrimitiveFromPath(Float,float);
	setPrimitiveFromPath(Double,long double);
	setPrimitiveFromPath(Date,time_t);


	getPrimitiveFromProperty(Boolean,bool);
	getPrimitiveFromProperty(Byte,char);
	getPrimitiveFromProperty(Character,wchar_t);
	getPrimitiveFromProperty(Short,short);
	getPrimitiveFromProperty(Integer,long);
	getPrimitiveFromProperty(Long,int64_t);
	getPrimitiveFromProperty(Double,long double);
	getPrimitiveFromProperty(Float,float);
	getPrimitiveFromProperty(Date,time_t);

	setPrimitiveFromProperty(Boolean,bool);
	setPrimitiveFromProperty(Byte,char);
	setPrimitiveFromProperty(Character,wchar_t);
	setPrimitiveFromProperty(Short,short);
	setPrimitiveFromProperty(Integer,long);
	setPrimitiveFromProperty(Long,int64_t);
	setPrimitiveFromProperty(Float,float);
	setPrimitiveFromProperty(Double,long double);
	setPrimitiveFromProperty(Date,time_t);

	getPrimitive(Boolean,bool,false);
	getPrimitive(Byte,char,0);
	getPrimitive(Character,wchar_t,0);
	getPrimitive(Short,short,0);
	getPrimitive(Integer,long,0);
	getPrimitive(Long,int64_t,0L);
	getPrimitive(Double,long double,0.0);
	getPrimitive(Float,float,0.0);
	getPrimitive(Date,time_t,0);

	setPrimitive(Boolean,bool,"Boolean");
	setPrimitive(Byte,char, "Byte");
	setPrimitive(Character,wchar_t,"Character");
	setPrimitive(Short,short,"Short");
	setPrimitive(Integer,long,"Integer");
	setPrimitive(Long,int64_t,"Long");
	setPrimitive(Float,float,"Float");
	setPrimitive(Double,long double,"Double");
	setPrimitive(Date,time_t,"Date");


	// Used to return empty values - remove when defaults are there.
	const char* DataObjectImpl::emptyString = "";

    // Useful for debug, so not included in the macros above - but
	// could be.
	// getters and setters for strings 

	const char* DataObjectImpl::getCString(unsigned int propertyIndex)
	{
		validateIndex(propertyIndex);
		if ((getType().getProperty(propertyIndex).isMany()))
		{
			string msg("Get value not available on many valued property:");
			msg += getType().getProperty(propertyIndex).getName();
			SDO_THROW_EXCEPTION("getCString", SDOUnsupportedOperationException,
				msg.c_str());
		}
  		DataObjectImpl* d = getDataObjectImpl(propertyIndex);
		if (d != 0) {
			if (!d->isNull()) return d->getCString ();
		}
		PropertyImpl& p = (PropertyImpl&)getPropertyImplFromIndex(propertyIndex);
		return p.getCStringDefault();
	}


	void DataObjectImpl::setCString (unsigned int propertyIndex, const char* value)
	{
		validateIndex(propertyIndex);
		PropertyValueMap::iterator i;
		if ((getType().getProperty(propertyIndex).isMany()))
		{
			string msg("Set value not available on many valued property:");
			msg += getType().getProperty(propertyIndex).getName();
			SDO_THROW_EXCEPTION("setString", SDOUnsupportedOperationException,
				msg.c_str());
		}
		for (i = PropertyValues.begin(); i != PropertyValues.end();++i)
			{
			if ((*i).first == propertyIndex)
			{
				logChange(propertyIndex);
				(*i).second->unsetNull();
				(*i).second->setCString(value);
				return;
			}
		}
		DASDataFactory* df = getDataFactory();
		DataObjectImpl* b = new DataObjectImpl(df, df->getType(Type::SDOTypeNamespaceURI,"String"));
		b->setContainer(this);
		b->setApplicableChangeSummary();
		PropertyValues.insert(std::make_pair(propertyIndex,b));
		logChange(propertyIndex);
		b->setCString(value);
  		return;
	}


	const char* DataObjectImpl::getCString (const char* path)
	{
		DataObjectImpl* d = 0;
		char* spath = 0;
		char *prop = 0;
		try {
			spath = DataObjectImpl::stripPath(path);
  			prop = findPropertyContainer(spath,&d);
			if (spath) {
				delete spath;
				spath = 0;
			}
			if (d != 0) {
				if (prop == 0 || (strlen(prop) == 0)) {
					return d->getCString();
				}
				else {
					PropertyImpl& p  = d->getTypeImpl().getPropertyImpl(prop);
					if (p.isMany())
					{
						long l;
						DataObjectImpl* doi = d->findDataObject(prop,&l);
						delete prop;
						prop = 0;
						if (doi != 0)	{
							return doi->getCString();
						}
						string msg("Get CString - index out of range");
						msg += path;
						SDO_THROW_EXCEPTION("getter", SDOIndexOutOfRangeException,
						msg.c_str());
					}
					else {
						delete prop;
						prop = 0;
						if (!d->isSet(p)) {
							return p.getCStringDefault();
						}
						return d->getCString(p);
					}
				}
			}
			if (prop){
				delete prop;
				prop = 0;
			}
			string msg("Get CString  - object not found");
			SDO_THROW_EXCEPTION("getCString", SDOPathNotFoundException,
			msg.c_str());
		}
		catch (SDORuntimeException e) {
			if (spath) delete spath;
			if (prop) delete prop;
			SDO_RETHROW_EXCEPTION("getCString",e);
		}
	}
    


	void DataObjectImpl::setCString(const char* path, const char* value)
	{
		DataObjectImpl *d = 0;
		char* spath = 0;
		char* prop = 0;
		try {
			spath = DataObjectImpl::stripPath(path);
 			prop = findPropertyContainer(spath,&d);
			if (spath) {
				delete spath;
				spath = 0;
			}
			if (d != 0) {
				if (prop == 0 || (strlen(prop) == 0)) {
					d->setCString(value);
				}
				else {
					const Property& p = d->getType().getProperty(prop);
					if (p.isMany()) {
						long l;
						DataObjectList& dol = d->getList(p);
						DataObjectImpl* doi = d->findDataObject(prop,&l);
						if (doi != 0)
						{
							doi->setCString(value);
						}
						else 
						{
							dol.append(value);
						}
					}
					else {
						d->setCString(p,value);
					}
					delete prop;
					prop = 0;
				}
			}
			if (prop) {
				delete prop;
				prop = 0;
			}
		}
		catch (SDORuntimeException e) {
			if (spath) delete spath;
			if (prop) delete prop;
			SDO_RETHROW_EXCEPTION("setCString",e);
		}
	}



	const char* DataObjectImpl::getCString (const Property& property)
	{
		return getCString(getPropertyIndex(property));
	}


	void DataObjectImpl::setCString(const Property& property, const char* value)
	{
		setCString(getPropertyIndex(property),value);
	}

	// null support

	bool DataObjectImpl::isNull(const unsigned int propertyIndex)
	{
		validateIndex(propertyIndex);
		if ((getType().getProperty(propertyIndex).isMany()))
		{
			return false;
		}

		PropertyValueMap::iterator i;
		for (i = PropertyValues.begin(); i != PropertyValues.end();++i)
			{
			if ((*i).first == propertyIndex)
			{
				return (*i).second->isNull();
			}
		}
		return false;
	}

	bool DataObjectImpl::isNull(const Property& property)
	{
		return isNull(getPropertyIndex(property));
	}

	bool DataObjectImpl::isNull(const char* path)
	{
		DataObjectImpl *d = 0;
		char* spath = 0;
		char* prop = 0;
		try {
			spath = DataObjectImpl::stripPath(path);
 			prop = findPropertyContainer(spath,&d);
			if (spath) {
				delete spath;
				spath = 0;
			}
			if (d != 0) {
				if (prop == 0 || (strlen(prop) == 0)) {
					return d->isNull();
				}
				else {
					const Property& p = d->getType().getProperty(prop);
					delete prop;
					return d->isNull(p);
				}
			}
			if (prop) {
				delete prop;
				prop = 0;
			}
			return false;
		}
		catch (SDORuntimeException e) {
			if (spath) delete spath;
			if (prop) delete prop;
			SDO_RETHROW_EXCEPTION("isNull",e);
		}

	}
	void DataObjectImpl::setNull(const unsigned int propertyIndex)
	{
		validateIndex(propertyIndex);
		if ((getType().getProperty(propertyIndex).isMany()))
		{
			string msg("Setting a list to null is not supported:");
			msg += getType().getProperty(propertyIndex).getName();
			SDO_THROW_EXCEPTION("setNull", SDOUnsupportedOperationException,
				msg.c_str());
		}

		PropertyValueMap::iterator i;
		for (i = PropertyValues.begin(); i != PropertyValues.end();++i)
			{
			if ((*i).first == propertyIndex)
			{
				(*i).second->setNull();
				return;
			}
		}
		// The property was not set yet...
		DASDataFactory* df = getDataFactory();
		DataObjectImpl* b = new DataObjectImpl(df, 
			getPropertyFromIndex(propertyIndex).getType());
		b->setContainer(this);
		b->setApplicableChangeSummary();
		PropertyValues.insert(std::make_pair(propertyIndex,b));
		logChange(propertyIndex);
		b->setNull();


	}
	void DataObjectImpl::setNull(const Property& property)
	{
		setNull(getPropertyIndex(property));
	}

	void DataObjectImpl::setNull(const char* path)
	{
		DataObjectImpl *d = 0;
		char* spath = 0;
		char* prop = 0;
		try {
			spath = DataObjectImpl::stripPath(path);
 			prop = findPropertyContainer(spath,&d);
			if (spath) {
				delete spath;
				spath = 0;
			}
			if (d != 0) {
				if (prop == 0 || (strlen(prop) == 0)) {
					d->setNull();
				}
				else {
					const Property& p = d->getType().getProperty(prop);
					delete prop;
					if (p.getType().isDataType()) 
					{
						d->setNull(p);
					}
					else 
					{
						d->setDataObject(0);
					}
					return;
				}
			}
			if (prop) {
				delete prop;
				prop = 0;
			}
			return;
		}
		catch (SDORuntimeException e) {
			if (spath) delete spath;
			if (prop) delete prop;
			SDO_RETHROW_EXCEPTION("setNull",e);
		}

	}



	// getters and setters for a List data object 

	DataObjectList& DataObjectImpl::getList(const char* path)
	{
		DataObjectImpl *d;
		char* spath = DataObjectImpl::stripPath(path);
 		char* prop = findPropertyContainer(spath,&d);
        if (spath) delete spath;  
		if (d != 0) {
			if (prop == 0 || (strlen(prop) == 0)) {
				return d->getList();
			}
			else {
	            const Property& p = d->getType().getProperty(prop);
				delete prop;
				return d->getList(p);
			}
		}
		if (prop) delete prop;

		string msg("Invalid path:");
		msg += path;
		SDO_THROW_EXCEPTION("getList",SDOPathNotFoundException, msg.c_str());

	}



	DataObjectList& DataObjectImpl::getList(unsigned int propIndex)
	{
		if (!(getType().getProperty(propIndex).isMany()))
		{
		string msg("Get list not available on single valued property:");
		msg += getType().getProperty(propIndex).getName();
		SDO_THROW_EXCEPTION("getList", SDOUnsupportedOperationException,
			msg.c_str());
		}
		DataObject* d = getDataObject(propIndex);
		return d->getList();
	}

	DataObjectList& DataObjectImpl::getList(const Property& p)
	{
		if (!p.isMany())
		{
			string msg("Get list not available on single valued property:");
			msg += p.getName();
			SDO_THROW_EXCEPTION("getList", SDOUnsupportedOperationException,
			msg.c_str());
		}

		int propIndex = getPropertyIndex(p);
		DataObjectImpl* d = getDataObjectImpl(propIndex);
		if (d == 0) {
			// There is no list yet, so we need to create an 
			// empty data object to hold the list
			DASDataFactory* df = getDataFactory();
			d = new DataObjectImpl(df, df->getType(Type::SDOTypeNamespaceURI,"DataObject"));
			PropertyValues.insert(std::make_pair(
					propIndex,d));
			d->setContainer(this);
			d->setApplicableChangeSummary();
			DataObjectListImpl* list = new DataObjectListImpl(df,this,
				propIndex,p.getType().getURI(),p.getType().getName());
			d->setList(list); 

		}
		return d->getList();
	}	



	DataObjectList& DataObjectImpl::getList()
	{
		return *listValue;
	}



  /////////////////////////////////////////////////////////////////////////////
  // Utilities 
  /////////////////////////////////////////////////////////////////////////////
  

    // get an index, or throw if the prop is not part of this DO 

	unsigned int DataObjectImpl::getPropertyIndex(const Property& p)
	{
		PropertyList props = getType().getProperties();  
		for (int i = 0; i < props.size() ; ++i)
		{
			if (props[i].getName()==p.getName() )
			{
				return i;
			}
		}
		string msg("Cannot find property:");
		msg += p.getName();
		SDO_THROW_EXCEPTION("getPropertyIndex", SDOPropertyNotFoundException,
			msg.c_str());
	}

	const Property& DataObjectImpl::getPropertyFromIndex(unsigned int index)
	{
		return (Property&)getPropertyImplFromIndex(index);
	}

	PropertyImpl& DataObjectImpl::getPropertyImplFromIndex(unsigned int index)
	{
		PropertyList props = getType().getProperties();  
		if (index < props.size())
		{
			return (PropertyImpl&)props[index];
		}

		string msg("Index out of range::");
		msg += index;
		SDO_THROW_EXCEPTION("getPropertyFromIndex", SDOIndexOutOfRangeException,
			msg.c_str());
	}


	//////////////////////////////////////////////////////////////////////
	// TODO - this is rubbish, but gets us by until XPATH is done
	// trip the path down to characters which I am going to
	// recognise later (a-z, A-Z _ [ ] .)
	//////////////////////////////////////////////////////////////////////

	const char* DataObjectImpl::templateString = 
	" /abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890=[]._";

	char* DataObjectImpl::stripPath(const char* path)
	{
	int pos = 0;
	char* s = new char[strlen(path)+1];

		for (int i=0;i < strlen(path); i++) 
		{
			if (strchr(templateString,path[i]) != 0) {
				s[pos++] = path[i];
			}
		}
		s[pos++] = 0;
		return s;
	}


	//////////////////////////////////////////////////////////////////////
	// Find a data object or return 0 if not found
	//////////////////////////////////////////////////////////////////////

    DataObjectImpl* DataObjectImpl::findDataObject(char* token, long* index)
	{
		// name , name[int], name[x=y] name.int 
		char c = 0;
       	char* beginbrace = strchr(token,'[');
		char* endbrace   = strchr(token,']');
		char* dot = strchr(token,'.');
		char* breaker = 0;


		if (dot != 0)
		{
			if (beginbrace != 0) 
			{
				breaker = (beginbrace < dot)? beginbrace:dot;
			}
			else 
			{
				breaker = dot;
			}
		}
		else 
		{
			breaker = beginbrace;
		}

		if (breaker == 0){
			// its this object, and a property thereof 
			*index = -1;
			const Property& p = getType().getProperty(token);
			return getDataObjectImpl(p);
		
		}

		c = *breaker;
		*breaker = 0;
		const Property& p = getType().getProperty(token);
		*breaker = c;

	
		breaker++;

		if (endbrace != 0)
		{
			*endbrace = 0;
		}

		char* eq = strchr(breaker,'=');

		if (eq == 0)
		{
			int val = atoi(breaker);
			DataObjectList& list = getList(p);

			// The spec says that depts[1] is the first element,
			//   as is depts.0 
			
      	    if (beginbrace != 0)val--;

			if (endbrace != 0)*endbrace = ']';

			if (val >=0 && val < list.size())
			{
				DataObject* dob =  list[val];
				*index = val;
				return (DataObjectImpl*)dob;
			}
            *index = -1;
			return 0;
		}
 		*eq = 0;
		// breaker is now propname
		eq++;
		// eq is now propval

		DataObjectList& list = getList(p);
		for (int li = 0 ; li < list.size() ; ++li)
		{
            // TODO  comparison for double not ok

			const Type & t = list[li]->getType();
			const Property& p  = t.getProperty(breaker);
			int ok = 0;

			switch (p.getTypeEnum())
			{
			case  Type::BooleanType:
				{
					// getCString will return "true" or "false"
					if (!strcmp(eq,list[li]->getCString(p))) ok = 1;
				}
				break;

			case  Type::ByteType:
				{
					char cc =  (char)eq[0];
					// getByte return a char
					if (cc == list[li]->getByte(p)) ok = 1;
				}
				break;

			case  Type::CharacterType:
				{
					wchar_t wc =  (wchar_t)((wchar_t*)eq)[0];
					// TODO - this is not a very accesible way of storing a wchar
					if (wc == list[li]->getCharacter(p)) ok = 1;
				}
				break;

			case  Type::IntegerType:
				{
					long  ic =  atol(eq);
					if (ic == list[li]->getInteger(p)) ok = 1;
				}
				break;

			case  Type::DateType: 
				{
					long  dc =  atol(eq);
					if (dc == list[li]->getDate(p)) ok = 1;
				}
				break;
				
			case  Type::DoubleType:
				{
					// TODO - double needs a bigger size than float
					long double  ldc =  (long double)atof(eq);
					if (ldc == list[li]->getDouble(p)) ok = 1;
				}
				break;

			case  Type::FloatType:
				{
					float  fc =  atof(eq);
					if (fc == list[li]->getFloat(p)) ok = 1;
				}
				break;

			case  Type::LongType:
				{
#if defined (__linux__)
                                        int64_t lic = (int64_t)strtoll(eq, NULL, 0);
#else
                                        int64_t lic = (int64_t)_atoi64(eq);
#endif

					if (lic == list[li]->getLong(p)) ok = 1;
				}
				break;

			case  Type::ShortType:
				{
					short  sic =  atoi(eq);
					if (sic == list[li]->getShort(p)) ok = 1;
				}
				break;


			case  Type::BytesType:
			case  Type::BigDecimalType:
			case  Type::BigIntegerType:
			case  Type::StringType:
			case  Type::UriType:
				{

					if (!strcmp(eq, list[li]->getCString(p))) ok = 1;
					// try with quotes too
					char *firstquote = strchr(eq,'"');
					if (firstquote != 0) {
						char* secondquote = strrchr(firstquote,'"');
						if (secondquote && secondquote != firstquote) {
							*secondquote = 0;
							if (!strcmp(firstquote+1, list[li]->getCString(p))) ok = 1;
							*secondquote = '"';
						}
					}
				}
				break;

			case Type::DataObjectType:
				break;

			default:
				break;
			}

			if (ok == 1)
			{
				*--eq='=';
			    if (endbrace != 0)*endbrace = ']';
				DataObject* dob = list[li];
				*index = li;
				return (DataObjectImpl*)dob;
			}
		}
		if (endbrace != 0)*endbrace = ']';
		*--eq='=';
		return 0;
	}


	//////////////////////////////////////////////////////////////////////
	// Find a data object and a property name within it.
	//////////////////////////////////////////////////////////////////////

	char* DataObjectImpl::findPropertyContainer(const char* path, DataObjectImpl** din)
	{

		DataObjectImpl* d;
		char*  i = strchr(path,'/');
		char* remaining = 0;
		char* token     = 0;

		if (i != 0) 
		{
			int j = strlen(path) - strlen(i);
			if (j > 0) 
			{
			    token = new char[j + 1];
			    strncpy(token,path, j);
			    token[j] = 0;
			}
			if (strlen(i) > 1) 
			{
			    remaining = new char[strlen(i)];
			    strcpy(remaining, i+1);
			}
		}
		else 
		{
            remaining = new char[strlen(path) + 1];
			strcpy(remaining,path);
		}
		
		if (token == 0) 
		{
			if (remaining != 0 && !strcmp(remaining,"..")) 
			{
				/* Its the container itself */
				*din = getContainerImpl();
				delete remaining;
				return 0;
			}

			/* Its this data object - property could be empty or
			   valid or invalid - user must check */
			*din = this;
			return remaining;
		}

		if (!strcmp(token,"..")) {
			/* Its derived from the container */
			d = getContainerImpl();
			/* carry on trying to find a property */
			if (d != 0) {
				char* ret = d->findPropertyContainer(remaining, din);
				delete token;
				if (remaining) delete remaining;
				return ret;
			}
			/* Give up - no container */
			delete token;
			if (remaining) delete remaining;
			*din = 0;
			return 0;
		}


		/* Try to find a property ....*/
		long l;
		d = findDataObject(token,&l);
		if (d != 0) {
			char* ret = d->findPropertyContainer(remaining,din);
			delete token;
			if (remaining) delete remaining;
			return ret;
		}

		/* Give up its not in the tree */

		delete token;
		if (remaining) delete remaining;
		*din = 0;
		return 0;
	}




   // Returns a read-only List of the Properties currently used in thIs DataObject.
   // ThIs list will contain all of the properties in getType().getProperties()
   // and any properties where isSet(property) is true.
   // For example, properties resulting from the use of
   // open or mixed XML content are present if allowed by the Type.
   // The list does not contain duplicates. 
   // The order of the properties in the list begins with getType().getProperties()
   // and the order of the remaining properties is determined by the implementation.
   // The same list will be returned unless the DataObject is updated so that 
   // the contents of the list change
   // @return the list of Properties currently used in thIs DataObject.
   
	PropertyList /* Property */ DataObjectImpl::getInstanceProperties()
	{
		std::vector<PropertyImpl*> theVec;
		PropertyList propList = getType().getProperties();
		for (int i = 0 ; i < propList.size() ; ++i)
		{
			Property& p = propList[i];
// TODO getInstanceProperties should show all the props - including any 
// properties of open types - which we dont have yet.
			theVec.insert(theVec.end(),(PropertyImpl*)&p);
		}
		return PropertyList(theVec);
	}
  
   // Returns the Sequence for thIs DataObject.
   // When Type.isSequencedType() == true,
   // the Sequence of a DataObject corresponds to the
   // XML elements representing the values of its properties.
   // Updates through DataObject and the Lists or Sequences returned
   // from DataObject operate on the same data.
   // When Type.isSequencedType() == false, null is returned.  
   // @return the <code>Sequence</code> or null.

	SequenceImpl* DataObjectImpl::getSequenceImpl()
	{

 		return sequence;
	}

	SequencePtr DataObjectImpl::getSequence()
	{
		return (SequencePtr)sequence;
	}

	SequencePtr DataObjectImpl::getSequence(const char* path)
	{
		DataObject* d = (DataObject*)getDataObject(path);
 		if (d) return d->getSequence();
		return 0;
	}

	SequencePtr DataObjectImpl::getSequence(unsigned int propertyIndex)
	{
		DataObject* d = (DataObject*)getDataObject(propertyIndex);
 		if (d) return d->getSequence();
		return 0;
	}

	SequencePtr DataObjectImpl::getSequence(const Property& property)
	{
		DataObject* d = (DataObject*)getDataObject(property);
 		if (d) return d->getSequence();
		return 0;
	}

  

	ChangeSummaryPtr DataObjectImpl::getChangeSummary(const char* path)
	{
		DataObjectImpl* d = getDataObjectImpl(path);
		return d->getChangeSummary();
	}

    ChangeSummaryPtr DataObjectImpl::getChangeSummary(unsigned int propIndex)
	{
		DataObjectImpl* d = getDataObjectImpl(propIndex);
		return d->getChangeSummary();
	}

    ChangeSummaryPtr DataObjectImpl::getChangeSummary(const Property& prop)
	{
		DataObjectImpl* d = getDataObjectImpl(prop);
		return d->getChangeSummary();

	}

	ChangeSummaryPtr DataObjectImpl::getChangeSummary()
	{
		if (getType().isChangeSummaryType())
		{
			return (ChangeSummaryPtr)localCS;
		}
		// The changesummaryobject MUST be a change summary type
		// but as an additional check against looping, I will use
		// a redundent getSummary() method.
		// TODO - remove this.
		if (changesummaryobject != 0) return 
			(ChangeSummaryPtr)(changesummaryobject->getSummary());
		return 0;
	}


	ChangeSummaryImpl* DataObjectImpl::getChangeSummaryImpl()
	{
		if (getType().isChangeSummaryType())
		{
			return localCS;
		}
		// The changesummaryobject MUST be a change summary type
		// but as an additional check against looping, I will use
		// a redundent getSummary() method.
		// TODO - remove this.
		if (changesummaryobject != 0) return changesummaryobject->getSummary();
		return 0;
	}

	ChangeSummaryImpl* DataObjectImpl::getSummary()
	{
		return localCS;
	}

	// sets a property of either this object or an object reachable from it, 
	// as identified by the specified path,
	// to the specified value.
	// @param path the path to a valid object and property.
	// @param value the new value for the property.

	void DataObjectImpl::setDataObject(const char* path, RefCountingPointer<DataObject> value)
	{
		DataObjectImpl* d;
		char* prop = findPropertyContainer(path, &d);
		if (d != 0)
		{
			if (prop != 0) {
				const Property& p = d->getType().getProperty(prop);
				if (p.isMany())
				{
					DataObjectList& dol = d->getList(p);
					long index;
					DataObjectImpl* dx = d->findDataObject(prop,&index);
					if (index >= 0)
					{
						if(index < dol.size())
						{
							dol.setDataObject((unsigned int)index,value);
						}
						else 
						{
							dol.append(value);
						}
						delete prop;
						return;
					}
					string msg("Set of data object on many valued item");
					msg += path;
					SDO_THROW_EXCEPTION("setDataObject", SDOUnsupportedOperationException,
					msg.c_str());
				}
				else 
				{
					d->setDataObject(p,value);
					delete(prop);
					return;
				}
			}
		}
		if (prop != 0)delete prop;

		string msg("Path not valid:");
		msg += path;
		SDO_THROW_EXCEPTION("setDataObject", SDOPathNotFoundException,
			msg.c_str());
	}

	void DataObjectImpl::validateIndex(unsigned int index)
	{
		PropertyList pl = getType().getProperties();

		if (index >= pl.size()) {

			string msg("Index of property out of range:");
			msg += index;
			SDO_THROW_EXCEPTION("Index Validation", SDOIndexOutOfRangeException,
				msg.c_str());
		}
	}


	void DataObjectImpl::setDataObject(unsigned int propertyIndex, RefCountingPointer<DataObject> value)
	{
		setDataObject(getType().getProperty(propertyIndex), value);
	}

	void DataObjectImpl::setDataObject(const Property& prop, RefCountingPointer<DataObject> value)
	{
		unsigned int propertyIndex = getPropertyIndex(prop);
		
		validateIndex(propertyIndex);

		if ((prop.isMany()))
		{
			string msg("Set operation on a many valued property:");
			msg += prop.getName();
			SDO_THROW_EXCEPTION("setDataObject", SDOUnsupportedOperationException,
				msg.c_str());
		}

		if (value == 0) 
		{
			PropertyValueMap::iterator j;
			for (j = PropertyValues.begin(); j != PropertyValues.end(); ++j)
			{
				if ((*j).first == propertyIndex) {
					if (prop.isReference())
					{
						((*j).second)->unsetReference(this, prop);
					}
					else
					{
						// log both deletion and change - change is not 
						// automatically recorded by deletion.
						((*j).second)->logDeletion();
					}
					logChange(prop);

					(*j).second = RefCountingPointer<DataObjectImpl>(0);
			
					return;
				}
			}
			logChange(prop);
			PropertyValues.insert(std::make_pair(propertyIndex,
			RefCountingPointer<DataObjectImpl>((DataObjectImpl*)0)));
			return;
		}

		const Type& t = prop.getType();
		const Type* objectType = &value->getType();
		bool typeMatch = false;
		while (objectType != 0)
		{
			if (strcmp(t.getURI(), objectType->getURI()) == 0
				&& strcmp(t.getName(),objectType->getName())==0)
			{
				typeMatch = true;
				break;
			}
			objectType = objectType->getBaseType();
		}

		if (!typeMatch)
		{
			string msg("Set data object incompatible types:");
			msg += value->getType().getURI();
			msg += " ";
			msg += value->getType().getName();
			msg += " and ";
			msg += t.getURI();
			msg += " ";
			msg += t.getName();
			SDO_THROW_EXCEPTION("setDataObject", SDOUnsupportedOperationException,
			msg.c_str());
		}

		DataObject* dob = value;
		PropertyValueMap::iterator i;
		for (i = PropertyValues.begin(); i != PropertyValues.end(); ++i)
		{
			if ((*i).first == propertyIndex) {

				if (prop.isReference())
				{
					((*i).second)->unsetReference(this, prop);
				}
				else
				{
					// log both deletion and change - change is not 
					// automatically recorded by deletion.
					((*i).second)->logDeletion();
				}
				logChange(prop);

				(*i).second = RefCountingPointer<DataObjectImpl>((DataObjectImpl*)dob);

			
				if (prop.isReference())
				{
					((DataObjectImpl*)dob)->setReference(this, prop);
				}
				else
				{
					logCreation((*i).second, this, prop);
				}
				return;
			}
		}
		if (prop.isReference())
		{
			((DataObjectImpl*)dob)->setReference(this, prop);
		}
		else
		{
			((DataObjectImpl*)dob)->setContainer(this);
			((DataObjectImpl*)dob)->setApplicableChangeSummary();
		}

		// log creation before putting into property values.
		// also log change - not done by logCreation
		logCreation((DataObjectImpl*)dob, this, prop);
		logChange(prop);

		PropertyValues.insert(std::make_pair(propertyIndex,
			RefCountingPointer<DataObjectImpl>((DataObjectImpl*)dob)));
		return;
	}


   
	// Returns whether a property of either this object or an object reachable 
	// from it, as identified by the specified path,
	// is considered to be set.
	// @param path the path to a valid Object* and property.
	
	bool DataObjectImpl::isSet(const char* path)
	{
		DataObjectImpl* d;
		char* prop = findPropertyContainer(path,&d);
		if (d != 0) {
			if (prop != 0) {
				const Property& p = d->getType().getProperty(prop);
				delete prop;
			    return d->isSet(p);
			}
		}
		if (prop != 0)delete prop;
		string msg("Invalid path:");
		msg += path;
		SDO_THROW_EXCEPTION("isSet" ,SDOPathNotFoundException,
			msg.c_str());
	}

	bool DataObjectImpl::isSet(unsigned int propertyIndex)
	{
		PropertyValueMap::iterator i;
		for (i = PropertyValues.begin(); i != PropertyValues.end(); ++i)
		{
			if ((*i).first == propertyIndex) {
				return true;
			}
		}
		return false;
	}

	bool DataObjectImpl::isSet(const Property& property)
	{
		return isSet(getPropertyIndex(property));
	}

	// unSets a property of either this Object or an Object reachable from it, 
	// as identified by the specified path.
	// @param path the path to a valid Object and property.
	// @see #unSet(Property)

	void DataObjectImpl::unset(const char* path)
	{
		
		DataObjectImpl* d;
		char* prop = findPropertyContainer(path,&d);
		if (d != 0)
		{
			if (prop != 0){
				const Property& p = d->getType().getProperty(prop);
				if (p.isMany())
				{
					if (strchr(prop,'[') || strchr(prop,'.'))
					{
						delete prop;
						string msg("Cannot unset a member of a list:");
						msg += path;
						SDO_THROW_EXCEPTION("unset", SDOUnsupportedOperationException,
						msg.c_str());
					}
				}
				delete prop;
			    d->unset(p);
				return;
			}
		}
		if (prop != 0) delete prop;

		string msg("Invalid path:");
		msg += path;
		SDO_THROW_EXCEPTION("unset", SDOPathNotFoundException,
			msg.c_str());
	}

	void DataObjectImpl::unset(unsigned int propertyIndex)
	{
		unset(getPropertyFromIndex(propertyIndex));
	}

	void DataObjectImpl::unset(const Property& p)
	{
		PropertyValueMap::iterator i;
		unsigned int index = getPropertyIndex(p);

		if (getType().isSequencedType())
		{
			Sequence* sq = getSequence();
			sq->removeAll(p);
		}

		for (i = PropertyValues.begin(); i != PropertyValues.end(); ++i)
		{
			if ((*i).first == index) {
				DataObjectImplPtr dol = (*i).second;
				if (p.getType().isDataType())
				{
					dol->clearReferences();
					logChange(index);
					if (p.isMany()) {
						DataObjectList& dl = dol->getList();
						while (dl.size() > 0) 
						{
							RefCountingPointer<DataObject> dli = dl.remove(0);
						}
					}
				}
				else {
					// if its a reference, we dont want to delete anything
					if (!p.isReference())
					{
						if (dol) { 
							dol->clearReferences();
							if (p.isMany()) {
								DataObjectList& dl = dol->getList();
								while (dl.size() > 0) 
								{
									if (p.getType().isDataObjectType())
									{
										DataObject* dob = dl[0];
										((DataObjectImpl*)dob)->logDeletion();
									}
									// the remove will record a change
									// remove will also clear the container.
									RefCountingPointer<DataObject> dli = dl.remove(0);
								}
							}
							else 
							{
								dol->logDeletion();
								logChange(index);
							}
						}
					}
					else {
						logChange(index);
					}
				}
				PropertyValues.erase(index);
				return;
			}
		}
		return;
	}

	

	// Returns the value of a DataObject property identified by 
	// the specified path.
	// @param path the path to a valid object and property.
	// @return the DataObject value of the specified property.

	RefCountingPointer<DataObject> DataObjectImpl::getDataObject(const char* path)
	{
		DataObjectImpl* ptr = getDataObjectImpl(path);;
		return RefCountingPointer<DataObject> ((DataObject*)ptr);
 	}

	DataObjectImpl* DataObjectImpl::getDataObjectImpl(const char* path)
	{
		
  		DataObjectImpl* d = 0;
		char* prop = findPropertyContainer(path,&d);
		if (d != 0) {
			if (prop != 0) {
				if (strchr(prop,'[') || strchr(prop,'.')) {
					/* Its a multlvalued property */
					long l;
					DataObjectImpl* theob = d->findDataObject(prop,&l);
                    delete prop;
					if (theob == 0) {
						string msg("Get DataObject - index out of range:");
						msg += path;
						SDO_THROW_EXCEPTION("getDataObject" ,SDOIndexOutOfRangeException,
						msg.c_str());
					}
					return theob;
				}
				else 
				{
					const Property& p = d->getType().getProperty(prop);
					delete prop;
					prop = 0;
					return d->getDataObjectImpl(p);
				}
			}
			else {
				return d;
			}
		}
		if (prop != 0)delete prop;

		string msg("Invalid path:");
		msg += path;
		SDO_THROW_EXCEPTION("getDataObject" ,SDOPathNotFoundException,
			msg.c_str());
	}

	RefCountingPointer<DataObject> DataObjectImpl::getDataObject(unsigned int propertyIndex)
	{
		if ((getType().getProperty(propertyIndex).isMany()))
		{
			string msg("get operation on a many valued property:");
			msg += getType().getProperty(propertyIndex).getName();
			SDO_THROW_EXCEPTION("getDataObject", SDOUnsupportedOperationException,
				msg.c_str());
		}
		DataObjectImpl* ptr = getDataObjectImpl(propertyIndex);;
		return RefCountingPointer<DataObject>((DataObject*)ptr);
	}

	DataObjectImpl* DataObjectImpl::getDataObjectImpl(unsigned int propertyIndex)
	{
		PropertyValueMap::iterator i;
		for (i = PropertyValues.begin(); i != PropertyValues.end(); ++i)
		{
			if ((*i).first == propertyIndex)
			{
				DataObject* dob = (*i).second;
				return (DataObjectImpl*)dob;
			}
		}
		return 0;
	}

  
	RefCountingPointer<DataObject> DataObjectImpl::getDataObject(const Property& property)
	{
		DataObjectImpl* ptr = getDataObjectImpl(property);
		return RefCountingPointer<DataObject>((DataObject*)ptr);
	}

	DataObjectImpl* DataObjectImpl::getDataObjectImpl(const Property& property)
	{
		return getDataObjectImpl(getPropertyIndex(property));
	}





   // Returns a new DataObject contained by this Object using the specified property,
   // which must be a containment property.
   // The type of the created Object is the declared type of the specified property.

	RefCountingPointer<DataObject> DataObjectImpl::createDataObject(const char* propertyName)
	{
		// Throws runtime exception for type or property not found 

		Logger::log("Creation of DataObject");
		const Type& t = getType();
		const Property& p  = t.getProperty(propertyName);
		return createDataObject(p);
	}

	// Returns a new DataObject contained by this Object using the specified property,
	// which must be a containment property.
	// The type of the created Object is the declared type of the specified property.

	RefCountingPointer<DataObject> DataObjectImpl::createDataObject(unsigned int propertyIndex)
	{
		Logger::log("Creation of DataObject");
		const Type& t = getType();
		const Property& p  = t.getProperty(propertyIndex);
 		return createDataObject(p);
	}

	// Returns a new DataObject contained by this Object using the specified property,
	// which must be a containment property.
	// The type of the created Object is the declared type of the specified property.
	
	RefCountingPointer<DataObject> DataObjectImpl::createDataObject(const Property& property)
	{
		Logger::log("Creation of DataObject");
		const Type& tp = property.getType();
		return createDataObject(property,tp.getURI(), tp.getName());
	}


	// Returns a new DataObject contained by this Object using the specified property,
	// which must be a containment property.
	// The type of the created Object is the declared type of the specified property.

	RefCountingPointer<DataObject> DataObjectImpl::createDataObject(const Property& property, const char* namespaceURI, 
		                               const char* typeName)
	{
		Logger::log("Creation of DataObject");
		if (!property.isContainment())
		{
			string msg("Create data object on non-containment property:");
			msg += property.getName();
			SDO_THROW_EXCEPTION("createDataObject", SDOUnsupportedOperationException,
			msg.c_str());
		}

		DASDataFactory* df = getDataFactory();
		if (property.isMany()) {
			/* add to the list */
			RefCountingPointer<DataObject> ptr = df->create(namespaceURI, typeName);
			DataObject* dob = ptr;
			((DataObjectImpl*)dob)->setContainer(this);
			((DataObjectImpl*)dob)->setApplicableChangeSummary();
			
			// log creation before adding to list - the change must record the old state 
			// of the list
			logCreation(((DataObjectImpl*)dob), this, property);
			logChange(property);

			DataObjectImpl* theDO = getDataObjectImpl(property);
			if ( theDO == 0) { /* No value set yet */
				unsigned int ind = getPropertyIndex(property);
				RefCountingPointer<DataObject> listptr = 
					df->create(Type::SDOTypeNamespaceURI,"DataObject");

				DataObject* doptr = listptr;
				PropertyValues.insert(std::make_pair(ind,(DataObjectImpl*)doptr));
				((DataObjectImpl*)doptr)->setContainer(this);
				((DataObjectImpl*)doptr)->setApplicableChangeSummary();

				DataObjectListImpl* list = new DataObjectListImpl(df,
					this, ind, namespaceURI,typeName);

				((DataObjectImpl*)doptr)->setList(list);
				// the append will log a change to the property.
				list->append(ptr);

				// now done by list append
				//if (getType().isSequencedType())
				//{
				//	SequenceImpl* sq = getSequenceImpl();
				//	sq->push(property,0);
				//}
			}
			else 
			{
				DataObjectList& list =	theDO->getList();
				// the append will log a change to the property, and update the 
				// sequence
				list.append(ptr);
				//if (getType().isSequencedType())
				//{
				//	SequenceImpl* sq = getSequenceImpl();
				//	sq->push(property,list.size()-1);
				//}

			}
			return ptr;

		}
		else {
			unset(property);
			DataObjectImpl* ditem = 
			  new DataObjectImpl(df, df->getType(namespaceURI, typeName));
			ditem->setContainer(this);
			ditem->setApplicableChangeSummary();

			// log both creation and change - creations no longer log
			// changes automatically.

			logCreation(ditem, this, property);
			logChange(property);

			PropertyValues.insert(std::make_pair(
					getPropertyIndex(property),
					RefCountingPointer<DataObjectImpl>(ditem)));
			if (getType().isSequencedType())
			{
				SequenceImpl* sq = getSequenceImpl();
				sq->push(property,0);
			}
			return RefCountingPointer<DataObject>((DataObject*)ditem);
		}
		return 0;
	}

	void DataObjectImpl::setList( DataObjectList* theList)
	{
		listValue = (DataObjectListImpl*)theList;
	}


	bool DataObjectImpl::remove(DataObjectImpl* indol)
	{
		PropertyValueMap::iterator i;
		for (i = PropertyValues.begin(); i != PropertyValues.end(); ++i)
		{
			const Property& prop = getPropertyFromIndex((*i).first);
			if (prop.isMany())
			{
				DataObjectList& dol = ((*i).second)->getList();
				for (int j=0;j< dol.size(); j++)
				{
					if (dol[j] == indol)
					{
						indol->logDeletion();
						logChange(prop);
						indol->setContainer(0);
						dol.remove(j);
						return true;
					}
				}
			}
			DataObjectImpl* tmp = (*i).second;
			if (tmp == indol) {
				indol->logDeletion();
				logChange(prop);
				indol->setContainer(0);
				PropertyValues.erase(i);
				return true;
			}
		}
		return false;
 	}

	// remove this Object from its container and dont unSet all its properties.

	void DataObjectImpl::detach()
	{
		// remove this data object from its tree
		clearReferences();
	// Change of spec - detach now doesnt clear the properties
//		PropertyValueMap::iterator i = PropertyValues.begin();
//
//		while (i != PropertyValues.end()) 
//		{
//			unSet((*i).first);
//			i = PropertyValues.begin();
//		}
//
        if (container == 0) return; 
		container->remove(this);
		return ;
	}

	void DataObjectImpl::clear()
	{
		// clear this objects state
		PropertyValueMap::iterator i = PropertyValues.begin();

		while (i != PropertyValues.end()) 
		{
			unset((*i).first);
			i = PropertyValues.begin();
		}
		return ;
	}

	// Returns the containing Object
	// of 0 if there is no container.

	RefCountingPointer<DataObject> DataObjectImpl::getContainer()
	{
		DataObject* dob = (DataObject*)container;
  		return RefCountingPointer<DataObject> (dob);
	}

	DataObjectImpl* DataObjectImpl::getContainerImpl()
	{
  		return container;
	}

	void DataObjectImpl::setContainer(DataObjectImpl* d)
	{
  		container = d;
	}

	const Property* DataObjectImpl::findInProperties(DataObject* ob)
	{
		PropertyValueMap::iterator i;
		for (i = PropertyValues.begin() ;i != PropertyValues.end() ; ++i)
		{
			if (getType().getProperty((*i).first).isReference()) continue;
			if (getType().getProperty((*i).first).isMany())
			{
				DataObjectList& dl = ((*i).second)->getList();
				for (int j = 0 ; j < dl.size(); j++)
				{
					if (dl[j] == ob)
					{
						return &(getType().getProperty((*i).first));
					}
				}
			}
			else 
			{
				if ((*i).second == ob) 
				{
					return &(getType().getProperty((*i).first));
				}
			}
		}
		return 0; // this can happen if the object has been detached 

		//string msg("Object cannot find its containing property");
		//SDO_THROW_EXCEPTION("FindInProperties" ,SDOPropertyNotFoundException,
		//	msg.c_str());
	}

	// Return the Property of the data Object containing this data Object
	// or 0 if there is no container.

	const Property& DataObjectImpl::getContainmentProperty()
	{
		if (container != 0) {
			const Property* p = container->findInProperties(this);
			if (p != 0)return *p;
		}
		SDO_THROW_EXCEPTION("getContainmentProperty" ,SDOPropertyNotFoundException,
			"Object cannot find its containment property");
	}


	// Returns the data Object's type.
	// The type defines the properties available for reflective access.

	const Type& DataObjectImpl::getType()
	{
  		return ObjectType;
	}

	const Type::Types DataObjectImpl::getTypeEnum()
	{
  	 	return ObjectType.getTypeEnum();
	}

	const TypeImpl& DataObjectImpl::getTypeImpl()
	{
  		return ObjectType;
	}


	PropertyList DataObjectImpl::getProperties()
	{
  		return getType().getProperties();
	}

	DASDataFactory* DataObjectImpl::getDataFactory()
	{
		return factory;
	}

	void DataObjectImpl::setDataFactory(DASDataFactory* df)
	{
		factory = df;
	}

	///////////////////////////////////////////////////////////////////////////
	// These finally are the setters/getters for primitives given
	// that the data object is a primitive type.
	///////////////////////////////////////////////////////////////////////////
	

	bool DataObjectImpl::getBoolean()
	{
		Logger::log("DataObject.getBoolean()\n");
		return getTypeImpl().convertToBoolean(value, valuelength);
	}


	char DataObjectImpl::getByte()
	{
		Logger::log("DataObject.getByte()\n");
		return getTypeImpl().convertToByte(value,valuelength);

	}


	wchar_t DataObjectImpl::getCharacter()
	{
		Logger::log("DataObject.getCharacter()\n");
		return getTypeImpl().convertToCharacter(value,valuelength);

	}

	long DataObjectImpl::getInteger() 
	{
		Logger::log("DataObject.getInteger()\n");
		return getTypeImpl().convertToInteger(value,valuelength);

	}


	long double DataObjectImpl::getDouble()
	{
		Logger::log("DataObject.getDouble()\n");
		return getTypeImpl().convertToDouble(value,valuelength);
	}


	float DataObjectImpl::getFloat()
	{
		Logger::log("DataObject.getFloat()\n");
		return getTypeImpl().convertToFloat(value,valuelength);

	}



	int64_t DataObjectImpl::getLong()
	{
		Logger::log("DataObject.getLong()\n");
		return getTypeImpl().convertToLong(value,valuelength);

	}


	short DataObjectImpl::getShort()
	{
		Logger::log("DataObject.getShort()\n");
		return getTypeImpl().convertToShort(value,valuelength);

	}

	unsigned int DataObjectImpl::getString( wchar_t* outptr, unsigned int max)
	{
		Logger::log("DataObject.getString()\n");
		if (outptr == 0 || max == 0) return valuelength;
		return getTypeImpl().convertToString(value, outptr, valuelength, max);

	}
	unsigned int DataObjectImpl::getBytes( char* outptr, unsigned int max)
	{
		Logger::log("DataObject.getBytes()\n");
		if (outptr == 0 || max == 0) return valuelength;
		return getTypeImpl().convertToBytes(value, outptr, valuelength, max);

	}

	const char* DataObjectImpl::getCString()
	{
		Logger::log("DataObject.getCString()\n");
		return getTypeImpl().convertToCString(value, &asStringBuffer, valuelength);

	}

	time_t DataObjectImpl::getDate()
	{
		Logger::log("DataObject.getDate()\n");
		return getTypeImpl().convertToDate(value, valuelength); /* time_t == long*/

	}

	DataObjectImpl* DataObjectImpl::getDataObject()
	{
		Logger::log("DataObject.getDataObject()\n");
		return (DataObjectImpl*)getTypeImpl().convertToDataObject(value, valuelength);

	}


	void DataObjectImpl::setBoolean(bool invalue)
	{
		Logger::log("DataObject.setBoolean()\n");
		valuelength = getTypeImpl().convert(&value,invalue);
		return;
	}


	void DataObjectImpl::setByte(char invalue)
	{
		Logger::log("DataObject.setByte()\n");
		valuelength = getTypeImpl().convert(&value,invalue);
		return;

	}


	void DataObjectImpl::setCharacter(wchar_t invalue)
	{
		Logger::log("DataObject.setCharacter()\n");
		valuelength = getTypeImpl().convert(&value,invalue);
		return;
	}

	void DataObjectImpl::setString(const wchar_t* invalue, unsigned int len)
	{
		Logger::log("DataObject.setString()\n");
		valuelength = getTypeImpl().convert(&value,invalue, len);
		return;
	}

	void DataObjectImpl::setBytes(const char* invalue, unsigned int len)
	{
		Logger::log("DataObject.setBytes()\n");
		valuelength = getTypeImpl().convert(&value,invalue, len);
		return;
	}

	void DataObjectImpl::setInteger(long invalue) 
	{
		Logger::log("DataObject.setInteger()\n");
		valuelength = getTypeImpl().convert(&value,invalue);
		return;
	}

	void DataObjectImpl::setDouble(long double invalue)
	{
		Logger::log("DataObject.setDouble()\n");
		valuelength = getTypeImpl().convert(&value,invalue);
		return;
	}

	void DataObjectImpl::setFloat(float invalue)
	{
		Logger::log("DataObject.setFloat()\n");
		valuelength = getTypeImpl().convert(&value,invalue);
		return;

	}


	void DataObjectImpl::setLong(int64_t invalue)
	{
		Logger::log("DataObject.setLong()\n");
		valuelength = getTypeImpl().convert(&value,invalue);
		return;
	}


	void DataObjectImpl::setShort(short invalue)
	{
		Logger::log("DataObject.setShort()\n");
		valuelength = getTypeImpl().convert(&value,invalue);
		return;

	}

	void DataObjectImpl::setCString(const char* invalue)
	{
		Logger::log("DataObject.setCString()\n");
		valuelength = getTypeImpl().convert(&value,invalue);
		return;
	}

	void DataObjectImpl::setDate(time_t invalue)
	{
		Logger::log("DataObject.setDate()\n");
		valuelength = getTypeImpl().convertDate(&value,invalue); /* time_t == long*/
		return;
	}

	void DataObjectImpl::setDataObject(DataObject* invalue)
	{
		Logger::log("DataObject.setDataObject()\n");
		valuelength = getTypeImpl().convert(&value,invalue);
		return;
	}

	void DataObjectImpl::setNull()
	{
		isnull = true;
	}

	bool DataObjectImpl::isNull()
	{
		return isnull;
	}

	void DataObjectImpl::unsetNull()
	{
		isnull = false;
	}


	DataObjectImpl::DataObjectImpl(const TypeImpl& t) : ObjectType(t)
	{
		container = 0;
		value = 0; /* Will be initialized when used */
		valuelength = 0;
		asStringBuffer = 0;
		asXPathBuffer = 0;
		isnull = false;
		userdata = (void*)0xFFFFFFFF;

		if (t.isChangeSummaryType())
		{
			changesummaryobject = 0;
			localCS = new ChangeSummaryImpl();
		}
		else 
		{
			changesummaryobject = 0;
			localCS = 0;
		}

		if (getType().isSequencedType()) sequence = new SequenceImpl(this);
		else sequence = 0;
	}



	DataObjectImpl::DataObjectImpl(DASDataFactory* df, const Type& t) : ObjectType((TypeImpl&)t),
		factory(df)
	{
		container = 0;
		value = 0;
		valuelength = 0;
		asStringBuffer = 0;
        asXPathBuffer = 0;
		isnull = false;
		userdata = (void*)0xFFFFFFFF;

		if (ObjectType.isChangeSummaryType())
		{
			changesummaryobject = 0;
			localCS = new ChangeSummaryImpl();
		}
		else 
		{
			changesummaryobject = 0;
			localCS = 0;
		}

		if (getType().isSequencedType()) 
		{
			sequence = new SequenceImpl(this);
		}
		else 
		{
			sequence = 0;
		}
	}

	void DataObjectImpl::deleteValue()
	{
		switch (getTypeEnum())
		{
		case  Type::BooleanType:
		case  Type::ByteType:
		case  Type::CharacterType:
		case  Type::IntegerType: 
		case  Type::DateType:   
		case  Type::DoubleType:
		case  Type::FloatType:
		case  Type::LongType:
		case  Type::ShortType:
		case  Type::BytesType:
			delete (char*)value;
			return;

		case  Type::BigDecimalType:
		case  Type::BigIntegerType:
		case  Type::StringType:
		case  Type::UriType:
			delete (wchar_t*)value;
			return;

		case Type::DataObjectType:
			return;

        default:
			return;
		}
	}

	DataObjectImpl::~DataObjectImpl()
	{
		// We do not want to log changes to our own deletion
		// if this DO owns the ChangeSummary. Do not delete
		// it here as contained DOs still have a reference to it.

		if (getTypeImpl().isChangeSummaryType())
		{
			ChangeSummaryPtr c = getChangeSummary();
			if (c) {
			    if (c->isLogging())
				{
					c->endLogging();
				}
			}
		}

		// Let my containees go.

		//PropertyValueMap::iterator i;
		//for (i = PropertyValues.begin(); i != PropertyValues.end();++i)
		//{
		//	(*i).second->setContainer(0);
		//}

		clearReferences();
 		PropertyValueMap::iterator i = PropertyValues.begin();
		while (i != PropertyValues.end()) 
		{
			unset((*i).first);
			i = PropertyValues.begin();
		}

		// Theory: A DO cant get here if its still attached to anything,
		//so we dont need to detach....
		//detach();

		
		if (asStringBuffer != 0) delete asStringBuffer;
		if (asXPathBuffer != 0) delete asXPathBuffer;

		if (value != 0) 
		{
			if (getType().isDataType())deleteValue();
		}
		
		

		if (getType().isSequencedType()) 
		{
			if (sequence != 0) delete sequence;
		}


		if (getTypeImpl().isChangeSummaryType()	)
		{
			if (getChangeSummary() != 0) 
			{
				delete localCS; 
				localCS = 0;
			}
		}
	}

	void DataObjectImpl::setApplicableChangeSummary()
	{
		changesummaryobject = 0;
		if (getType().isChangeSummaryType())
		{
			changesummaryobject = 0;
			return;
		}
		else {
			DataObjectImpl* dob = getContainerImpl();
			while (dob != 0) {
				if (dob->getType().isChangeSummaryType())
				{
					changesummaryobject = dob;
					return;
				}
				dob = dob->getContainerImpl();
			}
		}

	}

	void DataObjectImpl::logCreation(DataObjectImpl* dol, DataObjectImpl* cont,
		const Property& theprop)
	{
		if (getChangeSummaryImpl() != 0 && getChangeSummaryImpl()->isLogging())
		{
			getChangeSummaryImpl()->logCreation(dol,cont,theprop); 
		}
	}

	void DataObjectImpl::logDeletion()
	{
		// Only log if ChangeSummary is inherited form container
		if (getChangeSummaryImpl() != 0 && getChangeSummaryImpl()->isLogging() && !getType().isChangeSummaryType())
		{
			DataObjectImpl* cont = getContainerImpl();
			if (cont != 0)	// log if there is a container. If there is not, then
							// this can only be the object with the CS, so logging
							// would not make sense.
			{
				const Property* p = cont->findInProperties(this);
				if ( p != 0)	// if the object is not in the properties, then its been
								// detached, and has already been logged as deleted
				{
					getChangeSummaryImpl()->logDeletion(this,cont,*p);
				}
			}
		}
	}

	void DataObjectImpl::logChange(const Property& prop)
	{
		if (getChangeSummaryImpl() != 0 && getChangeSummaryImpl()->isLogging())
		{
			getChangeSummaryImpl()->logChange(this,prop);
		}
	}

	void DataObjectImpl::logChange(unsigned int propIndex)
	{
		if (getChangeSummaryImpl() != 0 && getChangeSummaryImpl()->isLogging())
		{
			getChangeSummaryImpl()->logChange(this,getPropertyFromIndex(propIndex));
		}
	}
	// reference support

	void DataObjectImpl::setReference(DataObject* dol, const Property& prop)
	{
		//cout << "Setting a reference to " << prop.getName() << endl;
		refs.push_back(new Reference(dol,prop));
	}
	void DataObjectImpl::unsetReference(DataObject* dol, const Property& prop)
	{
		//cout << "Unsetting a reference to " << prop.getName() << endl;
		for (int i=0;i< refs.size();i++)
		{
			if (refs[i]->getDataObject() == dol)
			{
				if (!strcmp(refs[i]->getProperty().getName(),
					prop.getName()))
				{
					delete refs[i];
					refs.erase(refs.begin() + i);
				}
			}
		}
	}


	void DataObjectImpl::clearReferences()
	{
		//cout << "Clearing all references: " << endl;
		for (int i=0;i<refs.size();i++)
		{
			// Note - no loop as the referer must be of type reference
			refs[i]->getDataObject()->unset(refs[i]->getProperty());
			//cout << " Cleared " << refs[i]->getProperty().getName() << endl;
			delete refs[i];
		}
		//cout << endl;
		refs.clear();
	}

	char* DataObjectImpl::objectToXPath()
	{
		char* temp1;
		char* temp2;

		if (asXPathBuffer == 0)
		{
			asXPathBuffer = new char[2];
			sprintf(asXPathBuffer,"#");
		}

		DataObjectImpl* dob = getContainerImpl();
		DataObject*thisob = this;
		while (dob != 0){
			const Property& p = thisob->getContainmentProperty();
			const char* name = p.getName();
			temp1 = new char[strlen(name) + 34];
			temp1[0] = 0;

			
			if (p.isMany()) {
				DataObjectList& dol = dob->getList(p);
				for (int i=0;i<dol.size();i++)
				{
					if (dol[i] == thisob)
					{
						sprintf(temp1,"#/%s.%d",name,i);
						break;
					}
				}
			}
			else {
				sprintf(temp1,"#/%s",name);
			}
			if (asXPathBuffer != 0) {
			    temp2 = new char[strlen(asXPathBuffer) + strlen(temp1)  + 1];
			    sprintf(temp2,"%s%s", temp1, asXPathBuffer+1 );
				delete asXPathBuffer;
			}
			else {
			    temp2 = new char[strlen(temp1)  + 1];
			    sprintf(temp2,"%s", temp1);
			}
			delete temp1;
			asXPathBuffer = temp2;
			thisob = dob;
			dob = dob->getContainerImpl();
		}
		return asXPathBuffer;
	}

	// user data support...
	void* DataObjectImpl::getUserData(const char* path)
	{
		DataObjectImpl *d;
		void* v = 0;
		char *spath = 0;
		char* prop = 0;
        try {
			spath = DataObjectImpl::stripPath(path);
  			prop = findPropertyContainer(spath,&d);
			if (spath)
			{
			    delete spath;
				spath = 0;
			}
			if (d != 0) 
			{
				if (prop != 0)
				{
					const Property& p = d->getType().getProperty(prop);
					if (p.getType().isDataType()) return 0;
					if (p.isMany())
					{
						DataObjectImpl* d2 = d->getDataObjectImpl(prop);
						if (d2) v = d2->getUserData();
						delete prop;
						prop = 0;
						return v;
					}
					v = d->getUserData(p);
					delete prop;
					prop = 0;
					return v;
				}
				return d->getUserData();
			}
			return 0;
		}
		catch (SDORuntimeException e)
		{
			if (prop)  delete prop;
			if (spath) delete spath;
			return 0;
		}
				
	}

	void* DataObjectImpl::getUserData(unsigned int propertyIndex)
	{
		if ((getType().getProperty(propertyIndex).isMany()))
		{
			return 0;
		}
		if ((getType().getProperty(propertyIndex).getType().isDataType()))
		{
			return 0;
		}
		DataObjectImpl* ptr = getDataObjectImpl(propertyIndex);
		if (ptr) return ptr->getUserData();
		return 0;
	}

	void* DataObjectImpl::getUserData(const Property& property)
	{
		if (property.isMany())
		{
			return 0;
		}
		if (property.getType().isDataType())
		{
			return 0;
		}
		DataObjectImpl* ptr = getDataObjectImpl(property);
		if (ptr) return ptr->getUserData();
		return 0;
	}

	void* DataObjectImpl::getUserData()
	{
		return userdata;
	}

	void DataObjectImpl::setUserData(const char* path, void* value)
	{
		char *spath = 0;
		char* prop = 0;
 		DataObjectImpl *d;
        try {
			spath = DataObjectImpl::stripPath(path);
  			prop = findPropertyContainer(spath,&d);
			if (spath)
			{
			    delete spath;
				spath = 0;
			}
			if (d != 0) 
			{
				if (prop != 0)
				{
					const Property& p = d->getType().getProperty(prop);
					if (p.getType().isDataType()) return;
					if (p.isMany())
					{
						DataObjectImpl* d2 = d->getDataObjectImpl(prop);
						if (d2) d2->setUserData(value);
						delete prop;
						prop = 0;
						return;
					}
					d->setUserData(p,value);
					delete prop;
					prop = 0;
					return;
				}
				d->setUserData(value);
				return;
			}
		}
		catch (SDORuntimeException e)
		{
			if (prop)  delete prop;
			if (spath) delete spath;
			return;
		}
				
	}

	void DataObjectImpl::setUserData(unsigned int propertyIndex, void* value)
	{
		if ((getType().getProperty(propertyIndex).isMany()))
		{
			return;
		}
		if ((getType().getProperty(propertyIndex).getType().isDataType()))
		{
			return;
		}
		DataObjectImpl* ptr = getDataObjectImpl(propertyIndex);
		if (ptr) ptr->setUserData(value);
		return;
	}

	void DataObjectImpl::setUserData(const Property& property, void* value)
	{
		if (property.isMany())
		{
			return;
		}
		if (property.getType().isDataType())
		{
			return;
		}
		DataObjectImpl* ptr = getDataObjectImpl(property);
		if (ptr) ptr->setUserData(value);
		return;
	}

	void DataObjectImpl::setUserData(void* value)
	{
		userdata = value;
	}

};
};
