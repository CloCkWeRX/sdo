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
static char rcs_id[] = "$Id$";

//////////////////////////////////////////////////////////////////////
// DasDataFactoryImpl.cpp: implementation of the DataFactory class.
//
//////////////////////////////////////////////////////////////////////
#include "SDORuntimeException.h"

#include "DASDataFactoryImpl.h"
#include "DataObjectImpl.h"

#include "Logger.h"


#include "PropertyList.h"

#include <iostream>

//////////////////////////////////////////////////////////////////////
// Construction/Destruction
//////////////////////////////////////////////////////////////////////

using namespace std;
using namespace commonj::sdo;

namespace commonj{
namespace sdo {

// ===================================================================
// Constructor
// ===================================================================
DASDataFactoryImpl::DASDataFactoryImpl()
{
	Logger::log("DASDataFactoryImpl created\n");

	/* add the primitives to every mdg - */

	isResolved = false;
	addType(Type::SDOTypeNamespaceURI,"BigDecimal");
	addType(Type::SDOTypeNamespaceURI,"BigInteger");
    addType(Type::SDOTypeNamespaceURI,"Boolean");

	addType(Type::SDOTypeNamespaceURI,"Byte");
	addType(Type::SDOTypeNamespaceURI,"Bytes");
	addType(Type::SDOTypeNamespaceURI,"Character");

	addType(Type::SDOTypeNamespaceURI,"String");
	addType(Type::SDOTypeNamespaceURI,"DataObject");
	addType(Type::SDOTypeNamespaceURI,"Date");
	addType(Type::SDOTypeNamespaceURI,"Double");
	addType(Type::SDOTypeNamespaceURI,"Float");
	addType(Type::SDOTypeNamespaceURI,"Integer");
	addType(Type::SDOTypeNamespaceURI,"Long");
	addType(Type::SDOTypeNamespaceURI,"Short");
	addType(Type::SDOTypeNamespaceURI,"URI");
	addType(Type::SDOTypeNamespaceURI,"ChangeSummary");

}

// ===================================================================
// Destructor
// ===================================================================
DASDataFactoryImpl::~DASDataFactoryImpl()
{
	Logger::log("DASDataFactory destroyed\n");

	TYPES_MAP::iterator typeIter;
	for (typeIter = types.begin() ; typeIter != types.end() ; ++typeIter)
	{
		if (strncmp((typeIter->first).c_str(),"ALIAS::", 7)) 
		{
			delete typeIter->second;
		}
	}

}

// ===================================================================
// copy constructor
// ===================================================================
DASDataFactoryImpl::DASDataFactoryImpl(const DASDataFactoryImpl& inmdg)
{
	Logger::log("DASDataFactoryImpl copy constructor\n");
	isResolved = false;
	copyTypes(inmdg);
}

// ===================================================================
// Assignment operator
// ===================================================================
DASDataFactoryImpl& DASDataFactoryImpl::operator=(const DASDataFactoryImpl& inmdg)
{
	Logger::log("DASDataFactoryImpl assignment operator\n");
	if (this != &inmdg)
	{
		copyTypes(inmdg);
	}
	return *this;
}

// ===================================================================
// copy Types to this DASDataFactory
// ===================================================================
void DASDataFactoryImpl::copyTypes(const DASDataFactoryImpl& inmdg)
{

	if (isResolved)
	{
	SDO_THROW_EXCEPTION("copyTypes",
		SDOUnsupportedOperationException, "Copying Type after data graph completed");
	}

	TYPES_MAP::const_iterator typeIter;
	TYPES_MAP::iterator typeIter2;
	char* fullTypeName;

	for (typeIter = inmdg.types.begin() ; typeIter != inmdg.types.end() ; ++typeIter)
	{
		// add this type to this metadata
		addType((typeIter->second)->getURI(), (typeIter->second)->getName());

		// re-find the type we just added.
		fullTypeName = getFullTypeName(
				(typeIter->second)->getURI(), 
				(typeIter->second)->getName());
		typeIter2 = types.find(fullTypeName);
		if (fullTypeName)delete fullTypeName;

		// copy the aliases , if there are any.

		if ((typeIter->second)->getAliasCount() > 0)
		{
			for (int j=0;j<(typeIter->second)->getAliasCount();j++)
			{
				(typeIter2->second)->setAlias(
					(typeIter->second)->getAlias());
			}
		}

		
		// Now add all the properties
		PropertyList props = typeIter->second->getProperties();
		for (int i=0; i < props.size(); i++)
		{
			// Ensure the properties type is added
			const Type& propType = props[i].getType();
			addType(propType.getURI(), propType.getName());

			// Now add the property
			addPropertyToType((typeIter->second)->getURI(),
							  (typeIter->second)->getName(),
							  props[i].getName(),
							  propType.getURI(), 
							  propType.getName(),
							  props[i].isMany(),
							  props[i].isReadOnly(),
							  props[i].isContainment());

			// copy the aliases if there are any.
            if (props[i].getAliasCount() > 0) 
			{

				PropertyImpl& p = (typeIter2->second)->
					getPropertyImpl(props[i].getName());

				for (int j=0;j<p.getAliasCount();j++)
				{
					p.setAlias(props[i].getAlias(j));
				}

			}

		} // end - iterate over Properties
	} // end - iterate over Types
}

// ===================================================================
// addType - adds a new Type if it does not already exist
// ===================================================================
void DASDataFactoryImpl::addType(const char* uri, const char* inTypeName, bool isSeq,
								 bool isOp)
{
	if (isResolved)
	{
	SDO_THROW_EXCEPTION("DASDataFactory::addType",
		SDOUnsupportedOperationException, "Adding Type after data graph completed");
	}

	if (inTypeName == 0 || strlen(inTypeName) == 0)
	{
	SDO_THROW_EXCEPTION("DASDataFactory::addType",
		SDOIllegalArgumentException, " Type has empty name");
	}

	
	if (findType(uri, inTypeName) == 0) 
	{
		char* fullTypeName = getFullTypeName(uri, inTypeName);
		types[fullTypeName] = new TypeImpl(uri, inTypeName, isSeq, isOp);
		if (fullTypeName)delete fullTypeName;

	}
}

// ===================================================================
//  Check whether a change summary would clash.
// ===================================================================

bool DASDataFactoryImpl::recursiveCheck(TypeImpl* cs, TypeImpl* t)
{
	if (cs->isDataType()) return false;

	if (! strcmp(cs->getName(), t->getName()) &&
		! strcmp(cs->getURI() , t->getURI()) )
	{
		return true;
	}

	PropertyList pl = cs->getProperties();
	
	for (int i=0 ; i < pl.size() ; i++ )
	{
        if (recursiveCheck((TypeImpl*)&(pl[i].getType()), t)) return true;
	}
	return false;
}

// ===================================================================
//  Check whether a change summary would clash.
// ===================================================================
bool DASDataFactoryImpl::checkForValidChangeSummary(TypeImpl* t)
{
	// None of the containing types can have a cs already.
	// None of the properties of this type can hold a type
	// which has a change summary.
	if (isResolved)
	{
	SDO_THROW_EXCEPTION("DASDataFactory::addChangeSummary",
		SDOUnsupportedOperationException, "Adding Change Summary after data graph completed");
	}

	if (cstypes.size() > 0) {
		for (int i = 0 ;i < cstypes.size(); i++) 
		{
			if (recursiveCheck(cstypes[i], t)) 
			{
				Logger::log("Attempt to add a change summary within a change summary");
				return false;

			}
		}
	}
    cstypes.push_back(t);
	return true;
}

// ===================================================================
// addPropertyToType - adds a Property to an existing Type
// ===================================================================
void DASDataFactoryImpl::addPropertyToType(const char* uri, 
									  const char* inTypeName, 
									  const char* propname,
									  const char* propTypeUri,
									  const char* propTypeName,
									  bool	many)
{
	char* fullPropTypeName = getFullTypeName(propTypeUri, propTypeName);
	TYPES_MAP::iterator typeIter;
	typeIter = types.find(fullPropTypeName);
	if (fullPropTypeName)delete fullPropTypeName;
	if (typeIter != types.end())
	{
		addPropertyToType(uri,inTypeName, 
								  propname,
								  propTypeUri,
								  propTypeName,
								  many, 
								  false,
								  !(typeIter->second)->isDataType());
	}
}

void DASDataFactoryImpl::addPropertyToType(const char* uri, 
									  const char* inTypeName, 
									  const char* propname,
									  const char* propTypeUri,
									  const char* propTypeName,
									  bool	many,
									  bool	rdonly,
									  bool cont)
{
	if (isResolved)
	{
	SDO_THROW_EXCEPTION("DASDataFactory::addPropertyToType",
		SDOUnsupportedOperationException, "Adding Properties after data graph completed");
	}

	TYPES_MAP::iterator typeIter, typeIter2;

	char* fullTypeName = getFullTypeName(uri, inTypeName);
	typeIter = types.find(fullTypeName);
	if (fullTypeName)delete fullTypeName;

	if(typeIter == types.end())
	{
		string msg("Type not found: ");
		msg += uri;
		msg += " ";
		msg += inTypeName;
		SDO_THROW_EXCEPTION("addPropertyToType",
		SDOTypeNotFoundException, msg.c_str());

	}

	if ((typeIter->second)->isDataType())
	{
		string msg("Cannot add a properties to data types: ");
		msg += (typeIter->second)->getName();
		SDO_THROW_EXCEPTION("addPropertyToType",
		SDOIllegalArgumentException, msg.c_str());
	}

	fullTypeName = getFullTypeName(propTypeUri, propTypeName);
	typeIter2 = types.find(fullTypeName);
	if (fullTypeName)delete fullTypeName;
	
	if (typeIter2 == types.end())
	{
		string msg("Type not found: ");
		msg += propTypeUri;
		msg += " ";
		msg += propTypeName;
		SDO_THROW_EXCEPTION("addPropertyToType",
		SDOTypeNotFoundException, msg.c_str());
	}
	
	// Check if its a ChangeSummary
	if (!strcmp(propTypeUri,Type::SDOTypeNamespaceURI) &&
		!strcmp(propTypeName,"ChangeSummary") )
	{
		if (checkForValidChangeSummary(typeIter->second)) 
		{
			// The change summary is allowable if we got to here - force the right params.
			// we will not use this property - its just for compatibility.
			// we have to use getChangeSummary to get the change summary, 
			// and isChangeSummaryType to see if this is a type which may have
			// a change summary.
			(typeIter->second)->addChangeSummary();
			// dont even show the property - its not needed
			//((typeIter->second)->addProperty(propname, *(typeIter2->second),false,true, false));

		}
		return;
	}
	

	if ((typeIter->second)->isDataType())
	{
		string msg("Cannot add property to a data type : ");
		msg += (typeIter->second)->getName();
		SDO_THROW_EXCEPTION("addPropertyToType",
		SDOIllegalArgumentException, msg.c_str());
           // cannot add a property to a primitive
	}

	// @PGR@ containment should be ignored for DataType
/*	if ((typeIter2->second)->isDataType() && cont == true)
	{
		string msg("Data types may not be containment : ");
		msg += (typeIter2->second)->getName();
		SDO_THROW_EXCEPTION("addPropertyToType",
		SDOIllegalArgumentException, msg.c_str());
		// cannot try to make a property containment on a data type
	}
*/
	((typeIter->second)->addProperty(propname, *(typeIter2->second),many,rdonly, cont));
	return;
}

// ===================================================================
// addPropertyToType - adds a Property to an existing Type
// ===================================================================

void DASDataFactoryImpl::addPropertyToType(const char* uri, 
									  const char* inTypeName, 
									  const char* propname,
									  const Type& tprop,
									  bool	many)
{
	addPropertyToType(uri, 
					inTypeName, 
					propname,
					tprop,
					many,
					false,
					!tprop.isDataType());
}


void DASDataFactoryImpl::addPropertyToType(const char* uri, 
									  const char* inTypeName, 
									  const char* propname,
									  const Type& tprop,
									  bool	many,
									  bool	rdonly,
									  bool cont)
{
	addPropertyToType(uri, 
					  inTypeName,
					  propname,
					  tprop.getURI(),
		              tprop.getName(),
					  many,
					  rdonly, cont);
}

// ===================================================================
// addPropertyToType - adds a Property to an existing Type
// ===================================================================
void DASDataFactoryImpl::addPropertyToType(const Type& cont,
									  const char* propname,
									  const char* propTypeUri,
									  const char* propTypeName,
									  bool  many)
{
	addPropertyToType(cont.getURI(),
		              cont.getName(),
					  propname,
					  propTypeUri,
					  propTypeName,
					  many);
}

void DASDataFactoryImpl::addPropertyToType(const Type& cont,
									  const char* propname,
									  const char* propTypeUri,
									  const char* propTypeName,
									  bool  many,
									  bool  rdonly,
									  bool contain)
{
	addPropertyToType(cont.getURI(),
		              cont.getName(),
					  propname,
					  propTypeUri,
					  propTypeName,
					  many,
					  rdonly,
					  contain);
}

// ===================================================================
// addPropertyToType - adds a Property to an existing Type
// ===================================================================
void DASDataFactoryImpl::addPropertyToType(const Type& tp,
									  const char* propname,
									  const Type& tprop,
									  bool  many)
{
		addPropertyToType(tp.getURI(),
					  tp.getName(),
					  propname,
					  tprop.getURI(),
					  tprop.getName(),
					  many);
}

void DASDataFactoryImpl::addPropertyToType(const Type& tp,
									  const char* propname,
									  const Type& tprop,
									  bool  many,
									  bool  rdonly,
									  bool cont)
{
	addPropertyToType(tp.getURI(),
					  tp.getName(),
					  propname,
					  tprop.getURI(),
					  tprop.getName(),
					  many,
					  rdonly,
					  cont);
}

// ===================================================================
// getFullTypeName - return the name used as a key in the types map
// ===================================================================
char* DASDataFactoryImpl::getFullTypeName(const char* uri, const char* inTypeName) const
{
	char* c = new char[strlen(uri) + strlen(inTypeName) + 2];
	sprintf(c,"%s#%s",uri,inTypeName);
	return c;
}

// ===================================================================
// getFullTypeName - return the name used as a key in the types map
// ===================================================================
char* DASDataFactoryImpl::getAliasTypeName(const char* uri, const char* inTypeName) const
{
	char* c = new char[strlen(uri) + strlen(inTypeName) + 9];
	sprintf(c,"ALIAS::%s#%s",uri,inTypeName);
	return c;
}

// ===================================================================
// getType - return a pointer to the required Type
// ===================================================================
const Type& DASDataFactoryImpl::getType(const char* uri, const char* inTypeName) const
{

	const Type* type = findType(uri, inTypeName);

	if (type == 0)
	{
		string msg("Type not found :");
		msg += uri;
		msg += " ";
		msg += inTypeName;
		SDO_THROW_EXCEPTION("getType" ,
		SDOTypeNotFoundException, msg.c_str());
	}
	
	return *type;
}

// ===================================================================
// setBaseType - sets the type from which this type inherits properties
// ===================================================================

void DASDataFactoryImpl::setBaseType( const Type& type,
		          const Type& base) 
{
	setBaseType(type.getURI(),type.getName(),base.getURI(), base.getName());
}

// ===================================================================
// setBaseType - sets the type from which this type inherits properties
// ===================================================================

void DASDataFactoryImpl::setBaseType( const char* typeuri,
		          const char* typenam,
				  const char* baseuri,
   			      const char* basename)
{
	const TypeImpl* base = findTypeImpl(baseuri, basename);
	if (base == 0)
	{
		string msg("Type not found :");
		msg += baseuri;
		msg += " ";
		msg += basename;
		SDO_THROW_EXCEPTION("setBaseType" ,
		SDOTypeNotFoundException, msg.c_str());
	}

	TYPES_MAP::const_iterator typeIter;

	char* fullTypeName = getFullTypeName(typeuri, typenam);
	typeIter = types.find(fullTypeName);
    if (fullTypeName)delete fullTypeName;
	
	if(typeIter == types.end())
	{
		string msg("Type not found :");
		msg += typeuri;
		msg += " ";
		msg += typenam;
		SDO_THROW_EXCEPTION("setBaseType" ,
		SDOTypeNotFoundException, msg.c_str());
	}

	(typeIter->second)->setBaseType(base);
}

// ===================================================================
// setDefault - sets the default value for a property of a type
// ===================================================================

	void DASDataFactoryImpl::setDefault(
		const Type& t, const char* propname, bool b ) 
	{
		setDefault(t.getURI(), t.getName(), propname, b);
	}

	void DASDataFactoryImpl::setDefault(
		const Type& t, const char* propname , char c) 
		
	{
		setDefault(t.getURI(), t.getName(), propname, c);
	}

	void DASDataFactoryImpl::setDefault(
		const Type& t, const char* propname , wchar_t c) 
	{
		setDefault(t.getURI(), t.getName(), propname, c);
	}

	void DASDataFactoryImpl::setDefault(
		const Type& t, const char* propname , char* c) 
	{
		setDefault(t.getURI(), t.getName(), propname, c);
	}

	void DASDataFactoryImpl::setDefault(
		const Type& t, const char* propname , short s) 
	{
		setDefault(t.getURI(), t.getName(), propname, s);
	}

	void DASDataFactoryImpl::setDefault(
		const Type& t, const char* propname , long l) 
	{
		setDefault(t.getURI(), t.getName(), propname, l);
	}

	void DASDataFactoryImpl::setDefault(
		const Type& t, const char* propname , int64_t i) 
	{
		setDefault(t.getURI(), t.getName(), propname, i);
	}

	void DASDataFactoryImpl::setDefault(
		const Type& t, const char* propname , float f) 
	{
		setDefault(t.getURI(), t.getName(), propname, f);
	}

	void DASDataFactoryImpl::setDefault(
		const Type& t, const char* propname , long double d) 
	{
		setDefault(t.getURI(), t.getName(), propname, d);
	}

	void DASDataFactoryImpl::setDefault(
		const Type& t, const char* propname , const wchar_t* c, unsigned int len) 
	{
		setDefault(t.getURI(), t.getName(), propname, c, len);
	}

	void DASDataFactoryImpl::setDefault(
		const Type& t, const char* propname , const char* c, unsigned int len) 
	{
		setDefault(t.getURI(), t.getName(), propname, c, len);
	}


	void DASDataFactoryImpl::setDefault(
		const char* typuri, const char* typnam, 
		const char* propname, bool b ) 
	{
		const TypeImpl* ti = findTypeImpl(typuri,typnam);
		PropertyImpl& pi = ti->getPropertyImpl(propname);
		pi.setDefault(b);
	}

	void DASDataFactoryImpl::setDefault(
		const char* typuri, const char* typnam, 
		const char* propname , char c) 
	{
		const TypeImpl* ti = findTypeImpl(typuri,typnam);
		PropertyImpl& pi = ti->getPropertyImpl(propname);
		pi.setDefault(c);
	}

	void DASDataFactoryImpl::setDefault(
		const char* typuri, const char* typnam, 
		const char* propname , wchar_t c) 
	{
		const TypeImpl* ti = findTypeImpl(typuri,typnam);
		PropertyImpl& pi = ti->getPropertyImpl(propname);
		pi.setDefault(c);
	}

	void DASDataFactoryImpl::setDefault(
		const char* typuri, const char* typnam, 
		const char* propname , char* c) 
	{
		const TypeImpl* ti = findTypeImpl(typuri,typnam);
		PropertyImpl& pi = ti->getPropertyImpl(propname);
		pi.setDefault(c);
	}

	void DASDataFactoryImpl::setDefault(
		const char* typuri, const char* typnam, 
		const char* propname , short s) 
	{
		const TypeImpl* ti = findTypeImpl(typuri,typnam);
		PropertyImpl& pi = ti->getPropertyImpl(propname);
		pi.setDefault(s);
	}

	void DASDataFactoryImpl::setDefault(
		const char* typuri, const char* typnam, 
		const char* propname , long l) 
	{
		const TypeImpl* ti = findTypeImpl(typuri,typnam);
		PropertyImpl& pi = ti->getPropertyImpl(propname);
		pi.setDefault(l);
	}

	void DASDataFactoryImpl::setDefault(
		const char* typuri, const char* typnam, 
		const char* propname , int64_t i) 
	{
		const TypeImpl* ti = findTypeImpl(typuri,typnam);
		PropertyImpl& pi = ti->getPropertyImpl(propname);
		pi.setDefault(i);
	}

	void DASDataFactoryImpl::setDefault(
		const char* typuri, const char* typnam, 
		const char* propname , float f) 
	{
		const TypeImpl* ti = findTypeImpl(typuri,typnam);
		PropertyImpl& pi = ti->getPropertyImpl(propname);
		pi.setDefault(f);
	}

	void DASDataFactoryImpl::setDefault(
		const char* typuri, const char* typnam, 
		const char* propname , long double d) 
	{
		const TypeImpl* ti = findTypeImpl(typuri,typnam);
		PropertyImpl& pi = ti->getPropertyImpl(propname);
		pi.setDefault(d);
	}

	void DASDataFactoryImpl::setDefault(
		const char* typuri, const char* typnam, 
		const char* propname , const wchar_t* c, unsigned int len) 
	{
		const TypeImpl* ti = findTypeImpl(typuri,typnam);
		PropertyImpl& pi = ti->getPropertyImpl(propname);
		pi.setDefault(c,len);
	}

	void DASDataFactoryImpl::setDefault(
		const char* typuri, const char* typnam, 
		const char* propname , const char* c, unsigned int len) 
	{
		const TypeImpl* ti = findTypeImpl(typuri,typnam);
		PropertyImpl& pi = ti->getPropertyImpl(propname);
		pi.setDefault(c,len);
	}


// ===================================================================
// getTypeImpl - return a pointer to the required TypeImpl
// ===================================================================
const TypeImpl& DASDataFactoryImpl::getTypeImpl(const char* uri, const char* inTypeName) const
{
	const TypeImpl* type = findTypeImpl(uri, inTypeName);

	if (type == 0)
	{
		string msg("Type not found :");
		msg += uri;
		msg += " ";
		msg += inTypeName;
		SDO_THROW_EXCEPTION("getTypeImpl" ,
		SDOTypeNotFoundException, msg.c_str());
	}
	
	return *type;
}

// ===================================================================
// findType
// ===================================================================

const Type* DASDataFactoryImpl::findType(const char* uri, const char* inTypeName) const
{
	return (Type*)findTypeImpl(uri,inTypeName);
}

// ===================================================================
// findTypeImpl
// ===================================================================

const TypeImpl* DASDataFactoryImpl::findTypeImpl(const char* uri, const char* inTypeName) const
{
	char* fullTypeName = getFullTypeName(uri, inTypeName);
	TYPES_MAP::const_iterator typeIter;
	typeIter = types.find(fullTypeName);
    if (fullTypeName)delete fullTypeName;
	if(typeIter != types.end())
	{
		return typeIter->second;
	}
	else
	{
		// try alias names
		fullTypeName = getAliasTypeName(uri, inTypeName);
		typeIter = types.find(fullTypeName);
		if (fullTypeName)delete fullTypeName;
		if(typeIter != types.end())
		{
			return typeIter->second;
		}
	}
	return 0;
}

// ===================================================================
// setAlias - sets a new alias for this type
// ===================================================================

void DASDataFactoryImpl::setAlias(const char* typeuri,
	                          const char* typenam,
							  const char* alias)
{

	char* fullTypeName = getFullTypeName(typeuri, typenam);
	TYPES_MAP::iterator typeIter;
	typeIter = types.find(fullTypeName);
	if (fullTypeName)delete fullTypeName;
	if(typeIter != types.end())
	{
		(typeIter->second)->setAlias(alias);
		fullTypeName = getAliasTypeName(typeuri, alias);
		types[fullTypeName] = typeIter->second;
	}

}

// ===================================================================
// setAlias - sets a new alias for this type
// ===================================================================

void DASDataFactoryImpl::setAlias(const char* typeuri, 
	                          const char* typenam, 
							  const char* propname,
							  const char* alias)
{
	const TypeImpl&  t = getTypeImpl(typeuri, typenam);
	PropertyImpl& p  = t.getPropertyImpl(propname); 
	p.setAlias(alias);

}

// ===================================================================
//  getTypes - gets the full list of types for this factory
// ===================================================================

TypeList DASDataFactoryImpl::getTypes() const
{
	TYPES_MAP::const_iterator typeIter;
	
	Logger::log("DASDataFactory creates list of types\n");

	std::vector<const Type*> typeVector;

	for (typeIter = types.begin() ; typeIter != types.end();
	++typeIter) {
		if (strncmp((typeIter->first).c_str(),"ALIAS::", 7)) {
			typeVector.insert(typeVector.end(),typeIter->second);
		}
	}

	Logger::log("DASDataFactoryImpl returns the list of types\n");

	return typeVector;
}


// ===================================================================
//  resolve - resolves the type and includes all the properties from
// the supertype. After this has been called, no further changes
// to the type hierarchy are allowed.
// ===================================================================

void DASDataFactoryImpl::resolve()
{
	if (isResolved) return; 

	TYPES_MAP::iterator typeIter;
	for (typeIter = types.begin() ; typeIter != types.end();
	++typeIter) 
	{
		(typeIter->second)->initCompoundProperties();
		(typeIter->second)->validateChangeSummary();
	}

	isResolved = true;
}

// ===================================================================
//  create - creates a data object from the types available.
//  This first resolves the type hierarchy, and thus no further changes
//  to the type hierarchy are allowed.
// ===================================================================


RefCountingPointer<DataObject> DASDataFactoryImpl::create(const char* uri, const char* typeName) 
{
	if (!isResolved)resolve();
	DataObject* dob = (DataObject*)(new DataObjectImpl(this, getTypeImpl(uri, typeName)));
	return dob;
}


// ===================================================================
//  create - creates a data object from the types available.
//  This first resolves the type hierarchy, and thus no further changes
//  to the type hierarchy are allowed.
// ===================================================================

RefCountingPointer<DataObject>	DASDataFactoryImpl::create(const Type& type) 
{
	if (!isResolved)resolve();
	return create( type.getURI(), type.getName());
}


// ===================================================================
//  setDASValue - Set a value on a Type
// ===================================================================
void DASDataFactoryImpl::setDASValue(const Type& type,
										const char* name, 
										DASValue* value)
{
	setDASValue(type.getURI(), type.getName(), name, value);
}


void DASDataFactoryImpl::setDASValue(const char* typeuri,
										const char* typenam, 
										const char* name, 
										DASValue* value)
{
	TypeImpl* type = (TypeImpl*)findTypeImpl(typeuri, typenam);
	if (type != NULL)
	{
		type->setDASValue(name, value);
	}
}

// ===================================================================
//  getDASValue - retrieve a value from a Type
// ===================================================================

DASValue* DASDataFactoryImpl::getDASValue(const Type& type,
												const char* name) const
{
	return getDASValue(type.getURI(), type.getName(), name);
}

DASValue* DASDataFactoryImpl::getDASValue(const char* typeuri,
												const char* typenam,
												const char* name) const
{
	TypeImpl* type = (TypeImpl*)findTypeImpl(typeuri, typenam);
	if (type != NULL)
	{
		return type->getDASValue(name);
	}

	return NULL;	
}

// ===================================================================
//  setDASValue - Set a value on a Property
// ===================================================================
void DASDataFactoryImpl::setDASValue( 
				const Type& type,
				const char* propertyName,
				const char* name,
				DASValue* value)
{
	setDASValue(type.getURI(), type.getName(), propertyName, name, value);
}


void DASDataFactoryImpl::setDASValue( 
				const char* typeuri,
				const char* typenam,
				const char* propertyName,
				const char* name,
				DASValue* value)
{
	const TypeImpl* type = findTypeImpl(typeuri, typenam);
	if (type != NULL)
	{
		PropertyImpl& prop = type->getPropertyImpl(propertyName);
		prop.setDASValue(name, value);
	}
}

// ===================================================================
//  getDASValue - retrieve a value from a Property
// ===================================================================
DASValue* DASDataFactoryImpl::getDASValue( 
				const Type& type,
				const char* propertyName,
				const char* name) const
{
	return getDASValue(type.getURI(), type.getName(), propertyName, name);
}

DASValue* DASDataFactoryImpl::getDASValue(
				const char* typeuri,
				const char* typenam,
				const char* propertyName, 
				const char* name) const
{
	const TypeImpl* type = findTypeImpl(typeuri, typenam);
	if (type != NULL)
	{
		try
		{
			PropertyImpl& prop = type->getPropertyImpl(propertyName);
			return prop.getDASValue(name);
		}
		catch (const SDOPropertyNotFoundException&)
		{
			// Ignore - return null
		}
	}

	return NULL;	
}


};
};

