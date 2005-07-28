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
/* $Id$ */

#ifndef _SEQUENCE_H_
#define _SEQUENCE_H_

#include "export.h"
#include "RefCountingObject.h"
#include "RefCountingPointer.h"
#include "Type.h"


namespace commonj{
namespace sdo{

class Property; /* forward declaration */
class DataObject;

///////////////////////////////////////////////////////////////////////////
//  A sequence is a heterogeneous list of properties and corresponding values.
//  It represents an ordered arbitrary mixture of data values from more than one property of a {@link DataObject data object}.
///////////////////////////////////////////////////////////////////////////
class Sequence : public RefCountingObject
{
	public:
	///////////////////////////////////////////////////////////////////////////
    // Returns the number of entries in the sequence.
    // @return the number of entries.
	///////////////////////////////////////////////////////////////////////////
	SDO_API virtual unsigned int size() = 0;

	///////////////////////////////////////////////////////////////////////////
    // Returns the property for the given entry index.
	///////////////////////////////////////////////////////////////////////////
    SDO_API virtual const Property& getProperty(unsigned int index) = 0;

	///////////////////////////////////////////////////////////////////////////
    // Returns the list index for many valued types.
	///////////////////////////////////////////////////////////////////////////
    SDO_API virtual unsigned int getListIndex(unsigned int index) = 0;

	SDO_API virtual Type::Types getTypeEnum(unsigned int index) = 0;


    SDO_API virtual unsigned int getIndex(const Property& p, unsigned int pindex=0) = 0;
	SDO_API virtual unsigned int getIndex(const char* propName, unsigned int pindex=0) = 0;

	///////////////////////////////////////////////////////////////////////////
    // Returns the property value for the given entry index.
    // @param index the index of the entry.
    // @return the value for the given entry index..
	///////////////////////////////////////////////////////////////////////////
	SDO_API virtual const char* getCStringValue(unsigned int index) = 0;
	SDO_API virtual bool        getBooleanValue(unsigned int index) = 0;
	SDO_API virtual char        getByteValue(unsigned int index) = 0;
	SDO_API virtual wchar_t     getCharacterValue(unsigned int index) = 0;
	SDO_API virtual unsigned int getStringValue(unsigned int index, wchar_t* val, unsigned int max) = 0;
	SDO_API virtual unsigned int getBytesValue(unsigned int index, char* val, unsigned int max) = 0;
	SDO_API virtual short       getShortValue(unsigned int index) = 0;
	SDO_API virtual long         getIntegerValue(unsigned int index) = 0;	
	SDO_API virtual int64_t     getLongValue(unsigned int index) = 0;
	SDO_API virtual float       getFloatValue(unsigned int index) = 0;
	SDO_API virtual long double getDoubleValue(unsigned int index) = 0;
	SDO_API virtual time_t      getDateValue(unsigned int index) = 0;
	SDO_API virtual DataObjectPtr getDataObjectValue(unsigned int index) = 0;

    SDO_API virtual unsigned int getLength(unsigned int index) = 0;


	///////////////////////////////////////////////////////////////////////////
    // sets the entry at a specified index to the new value.
    // @param index the index of the entry.
    // @param value the new value for the entry.
	///////////////////////////////////////////////////////////////////////////

	SDO_API virtual void setCStringValue(   unsigned int index, const char* s ) = 0;
	SDO_API virtual void setBooleanValue(   unsigned int index, bool        b ) = 0;
	SDO_API virtual void setByteValue(      unsigned int index, char        c ) = 0;
	SDO_API virtual void setCharacterValue( unsigned int index, wchar_t     c ) = 0;
	SDO_API virtual void setStringValue(	unsigned int index, const wchar_t* s , unsigned int len) = 0;
	SDO_API virtual void setBytesValue(     unsigned int index, const char* s , unsigned int len) = 0;
	SDO_API virtual void setShortValue(     unsigned int index, short       s ) = 0;
	SDO_API virtual void setIntegerValue(       unsigned int index, long         i ) = 0;	
	SDO_API virtual void setLongValue(  unsigned int index, int64_t     l ) = 0;
	SDO_API virtual void setFloatValue(     unsigned int index, float       f ) = 0;
	SDO_API virtual void setDoubleValue(unsigned int index, long double d ) = 0;
	SDO_API virtual void setDateValue(unsigned int index, time_t t ) = 0;
	SDO_API virtual void setDataObjectValue(unsigned int index, DataObjectPtr d ) = 0;


	///////////////////////////////////////////////////////////////////////////
    // adds a new entry with the specified property name and value
    // to the end of the entries.
    // @param propertyName the name of the entry's property.
    // @param value the value for the entry.
	///////////////////////////////////////////////////////////////////////////
	SDO_API virtual bool addCString(   const char* propertyName,const char* s ) = 0;
	SDO_API virtual bool addBoolean(   const char* propertyName,bool        b ) = 0;
	SDO_API virtual bool addByte(      const char* propertyName,char        c ) = 0;
	SDO_API virtual bool addCharacter( const char* propertyName,wchar_t     c ) = 0;
	SDO_API virtual bool addString(    const char* propertyName,const wchar_t* s , unsigned int len) = 0;
	SDO_API virtual bool addBytes (    const char* propertyName,const char* s , unsigned int len) = 0;
	SDO_API virtual bool addShort(     const char* propertyName,short       s ) = 0;
	SDO_API virtual bool addInteger(       const char* propertyName,long         i ) = 0;	
	SDO_API virtual bool addLong(  const char* propertyName,int64_t     l ) = 0;
	SDO_API virtual bool addFloat(     const char* propertyName,float       f ) = 0;
	SDO_API virtual bool addDouble(const char* propertyName,long double d ) = 0;
	SDO_API virtual bool addDate(const char* propertyName,time_t t ) = 0;
	SDO_API virtual bool addDataObject(const char* propertyName,DataObjectPtr d ) = 0;

 
	///////////////////////////////////////////////////////////////////////////
    // adds a new entry with the specified property index and value
    // to the end of the entries.
    // @param propertyIndex the index of the entry's property.
    // @param value the value for the entry.
	///////////////////////////////////////////////////////////////////////////
	SDO_API virtual bool addCString(   unsigned int propertyIndex,const char* s ) = 0;
	SDO_API virtual bool addBoolean(   unsigned int propertyIndex,bool        b ) = 0;
	SDO_API virtual bool addByte(      unsigned int propertyIndex,char        c ) = 0;
	SDO_API virtual bool addCharacter( unsigned int propertyIndex,wchar_t     c ) = 0;
	SDO_API virtual bool addString(    unsigned int propertyIndex,const wchar_t* s , unsigned int len) = 0;
	SDO_API virtual bool addBytes(     unsigned int propertyIndex,const char* s , unsigned int len) = 0;
	SDO_API virtual bool addShort(     unsigned int propertyIndex,short       s ) = 0;
	SDO_API virtual bool addInteger(       unsigned int propertyIndex,long         i ) = 0;	
	SDO_API virtual bool addLong(  unsigned int propertyIndex,int64_t     l ) = 0;
	SDO_API virtual bool addFloat(     unsigned int propertyIndex,float       f ) = 0;
	SDO_API virtual bool addDouble(unsigned int propertyIndex,long double d ) = 0;
	SDO_API virtual bool addDate(unsigned int propertyIndex,time_t t ) = 0;
	SDO_API virtual bool addDataObject(unsigned int propertyIndex,DataObjectPtr d ) = 0;


	///////////////////////////////////////////////////////////////////////////
    // adds a new entry with the specified property and value
    // to the end of the entries.
    // @param property the property of the entry.
    // @param value the value for the entry.
	///////////////////////////////////////////////////////////////////////////

	SDO_API virtual bool addCString(   const Property& property,const char* s ) = 0;
	SDO_API virtual bool addBoolean(   const Property& property,bool        b ) = 0;
	SDO_API virtual bool addByte(      const Property& property,char        c ) = 0;
	SDO_API virtual bool addCharacter( const Property& property,wchar_t     c ) = 0;
	SDO_API virtual bool addString   ( const Property& property,const wchar_t* s , unsigned int len) = 0;
	SDO_API virtual bool addBytes(     const Property& property,const char* s , unsigned int len) = 0;
	SDO_API virtual bool addShort(     const Property& property,short       s ) = 0;
	SDO_API virtual bool addInteger(       const Property& property,long         i ) = 0;	
	SDO_API virtual bool addLong(  const Property& property,int64_t     l ) = 0;
	SDO_API virtual bool addFloat(     const Property& property,float       f ) = 0;
	SDO_API virtual bool addDouble(const Property& property,long double d ) = 0;
	SDO_API virtual bool addDate(const Property& property,time_t t ) = 0;
	SDO_API virtual bool addDataObject(const Property& property,DataObjectPtr d ) = 0;


	///////////////////////////////////////////////////////////////////////////
    // adds a new entry with the specified property name and value
    // at the specified entry index.
    // @param index the index at which to add the entry.
    // @param propertyName the name of the entry's property.
    // @param value the value for the entry.
	///////////////////////////////////////////////////////////////////////////
	SDO_API virtual bool addCString(   unsigned int index,const char* propertyName,const char* s ) = 0;
	SDO_API virtual bool addBoolean(   unsigned int index,const char* propertyName,bool        b ) = 0;
	SDO_API virtual bool addByte(      unsigned int index,const char* propertyName,char        c ) = 0;
	SDO_API virtual bool addCharacter( unsigned int index,const char* propertyName,wchar_t     c ) = 0;
	SDO_API virtual bool addString(    unsigned int index,const char* propertyName,const wchar_t* s, unsigned int len ) = 0;
	SDO_API virtual bool addBytes(     unsigned int index,const char* propertyName,const char* s , unsigned int len) = 0;
	SDO_API virtual bool addShort(     unsigned int index,const char* propertyName,short       s ) = 0;
	SDO_API virtual bool addInteger(       unsigned int index,const char* propertyName,long         i ) = 0;	
	SDO_API virtual bool addLong(  unsigned int index,const char* propertyName,int64_t     l ) = 0;
	SDO_API virtual bool addFloat(     unsigned int index,const char* propertyName,float       f ) = 0;
	SDO_API virtual bool addDouble(unsigned int index,const char* propertyName,long double d ) = 0;
	SDO_API virtual bool addDate(unsigned int index,const char* propertyName,time_t t ) = 0;
	SDO_API virtual bool addDataObject(unsigned int index,const char* propertyName,DataObjectPtr d ) = 0;


	///////////////////////////////////////////////////////////////////////////
    // adds a new entry with the specified property index and value
    // at the specified entry index.
    // @param index the index at which to add the entry.
    // @param propertyIndex the index of the entry's property.
    // @param value the value for the entry.
	///////////////////////////////////////////////////////////////////////////
	SDO_API virtual bool addCString(   unsigned int index,unsigned int propertyIndex,const char* s ) = 0;
	SDO_API virtual bool addBoolean(   unsigned int index,unsigned int propertyIndex,bool        b ) = 0;
	SDO_API virtual bool addByte(      unsigned int index,unsigned int propertyIndex,char        c ) = 0;
	SDO_API virtual bool addCharacter( unsigned int index,unsigned int propertyIndex,wchar_t     c ) = 0;
	SDO_API virtual bool addString(    unsigned int index,unsigned int propertyIndex,const wchar_t* s , unsigned int len) = 0;
	SDO_API virtual bool addBytes(     unsigned int index,unsigned int propertyIndex,const char* s , unsigned int len) = 0;
	SDO_API virtual bool addShort(     unsigned int index,unsigned int propertyIndex,short       s ) = 0;
	SDO_API virtual bool addInteger(       unsigned int index,unsigned int propertyIndex,long         i ) = 0;	
	SDO_API virtual bool addLong(  unsigned int index,unsigned int propertyIndex,int64_t     l ) = 0;
	SDO_API virtual bool addFloat(     unsigned int index,unsigned int propertyIndex,float       f ) = 0;
	SDO_API virtual bool addDouble(unsigned int index,unsigned int propertyIndex,long double d ) = 0;
	SDO_API virtual bool addDate(unsigned int index,unsigned int propertyIndex,time_t t ) = 0;
	SDO_API virtual bool addDataObject(unsigned int index,unsigned int propertyIndex,DataObjectPtr d ) = 0;


	///////////////////////////////////////////////////////////////////////////
    // adds a new entry with the specified property and value
    // at the specified entry index.
    // @param index the index at which to add the entry.
    // @param property the property of the entry.
    // @param value the value for the entry.
	///////////////////////////////////////////////////////////////////////////
	SDO_API virtual bool addCString(   unsigned int index,const Property& property,const char* s ) = 0;
	SDO_API virtual bool addBoolean(   unsigned int index,const Property& property,bool        b ) = 0;
	SDO_API virtual bool addByte(      unsigned int index,const Property& property,char        c ) = 0;
	SDO_API virtual bool addCharacter( unsigned int index,const Property& property,wchar_t     c ) = 0;
	SDO_API virtual bool addString(    unsigned int index,const Property& property,const wchar_t* s , unsigned int len) = 0;
	SDO_API virtual bool addBytes(     unsigned int index,const Property& property,const char* s , unsigned int len) = 0;
	SDO_API virtual bool addShort(     unsigned int index,const Property& property,short       s ) = 0;
	SDO_API virtual bool addInteger(       unsigned int index,const Property& property,long         i ) = 0;	
	SDO_API virtual bool addLong(  unsigned int index,const Property& property,int64_t     l ) = 0;
	SDO_API virtual bool addFloat(     unsigned int index,const Property& property,float       f ) = 0;
	SDO_API virtual bool addDouble(unsigned int index,const Property& property,long double d ) = 0;
	SDO_API virtual bool addDate(unsigned int index,const Property& property,time_t t ) = 0;
	SDO_API virtual bool addDataObject(unsigned int index,const Property& property,DataObjectPtr d ) = 0;

 
	///////////////////////////////////////////////////////////////////////////
    // removes the entry at the given entry index.
    // @param index the index of the entry
	///////////////////////////////////////////////////////////////////////////
	SDO_API virtual void remove(unsigned int index) = 0;
	virtual void removeAll(const Property& p) = 0;

	///////////////////////////////////////////////////////////////////////////
    // Moves the entry at <code>fromIndex</code> to <code>toIndex</code>.
    // @param toIndex the index of the entry destination.
    // @param fromIndex the index of the entry to move.
	///////////////////////////////////////////////////////////////////////////
	SDO_API virtual void move(unsigned int toIndex, unsigned int fromIndex) = 0;

	///////////////////////////////////////////////////////////////////////////
    // adds a new Setting with the SDO text Property
    // to the end of the Settings.
    // @param text value of the Setting.
	///////////////////////////////////////////////////////////////////////////
	SDO_API virtual bool addText(const char* text) = 0;

	///////////////////////////////////////////////////////////////////////////
    // adds a new Setting with the SDO text Property
    // to the Settings.
    // @param index the index at which to add the entry.
    // @param text value of the Setting.
	///////////////////////////////////////////////////////////////////////////
	SDO_API virtual bool addText(unsigned int index, const char* text) = 0;

	///////////////////////////////////////////////////////////////////////////
    // sets an SDO text Property
    // @param index the index at which to add the entry.
    // @param text value of the Setting.
	///////////////////////////////////////////////////////////////////////////
	SDO_API virtual bool setText(unsigned int index, const char* text) = 0;

	///////////////////////////////////////////////////////////////////////////
    // checks whether an entry is text or not
	///////////////////////////////////////////////////////////////////////////
	SDO_API virtual bool isText(unsigned int index) = 0;

};


};
};

#endif //_SEQUENCE_H_
