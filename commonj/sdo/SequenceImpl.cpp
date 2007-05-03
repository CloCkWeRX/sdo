/*
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 * 
 *   http://www.apache.org/licenses/LICENSE-2.0
 *   
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 */

/* $Rev: 506932 $ $Date$ */

#include "commonj/sdo/disable_warn.h"
#include "commonj/sdo/Property.h"
#include "commonj/sdo/Type.h"
#include "commonj/sdo/TypeList.h"

#include "commonj/sdo/SequenceImpl.h"

#include "commonj/sdo/DataObject.h"
#include "commonj/sdo/DataObjectImpl.h"
#include "commonj/sdo/SDORuntimeException.h"
using namespace std;


#define CHECK_RANGE\
        if (index >= the_list.size()){\
            std::string msg("Index out of range:");\
            msg += index;\
            SDO_THROW_EXCEPTION("Sequence API", SDOIndexOutOfRangeException,\
            msg.c_str());\
        }\
        unsigned int j = 0;\
        for (i = the_list.begin(); (j < index) && (i != the_list.end()); ++i)\
        {\
            j++;\
        }
    

namespace commonj
{
   namespace sdo
   {

      SequenceImpl::SequenceImpl()
      {
         SDO_THROW_EXCEPTION("Sequence constructor",
                             SDORuntimeException,
                             "SequenceImpl::construction without a data object");
      }

      SequenceImpl::SequenceImpl(DataObject* indo)
      {
         the_do = (DataObjectImpl*) indo;
      }

      SequenceImpl::SequenceImpl(SequenceImpl* inseq)
      {
         // take a copy of the_list
         the_list = inseq->the_list;
      }

      unsigned int SequenceImpl::size()
      {
         return the_list.size();
      }

      // Convert an index into the sequence into an iterator (pointer) to the
      // list element identified by the index. If the index is invalid then
      // throw an exception.
      void SequenceImpl::checkRange(unsigned int index, SEQUENCE_ITEM_LIST::iterator& i)
      {
         if (index >= the_list.size())
         {
            std::string msg("Index out of range:");
            msg += index;
            SDO_THROW_EXCEPTION("Sequence API",
                                SDOIndexOutOfRangeException,
                                msg.c_str());
         }
         unsigned int j = 0;
         for (i = the_list.begin(); (j < index) && (i != the_list.end()); ++i)
         {
            j++;
         }
         return;
      }
      
      // Return the data object associated with this sequence
      const DataObjectPtr SequenceImpl::getDataObject()
      {
         return the_do;
      }

      const Property& SequenceImpl::getProperty(unsigned int index)
      {
         SEQUENCE_ITEM_LIST::iterator i;

         if (isText(index))
         {
            std::string msg("Cannot get property of a text item");
            msg += index;
            SDO_THROW_EXCEPTION("getProperty",
                                SDOUnsupportedOperationException,
                                msg.c_str());
         }

         checkRange(index, i);

         if (i != the_list.end())
         {
            return *((*i).getProp()); 
         }
         std::string msg("Index out of range:");
         msg += index;
         SDO_THROW_EXCEPTION("getProperty",
                             SDOIndexOutOfRangeException,
                             msg.c_str());
      }

      Type::Types SequenceImpl::getTypeEnum(unsigned int index)
      {
         SEQUENCE_ITEM_LIST::iterator i;
        
         checkRange(index, i);

         if (i != the_list.end())
         {
            if ((*i).getProp() == 0)
            {
               // text type
               return Type::TextType;
            }
            return (*i).getProp()->getTypeEnum();
         }
         std::string msg("Index out of range:");
         msg += index;
         SDO_THROW_EXCEPTION("getTypeEnum",
                             SDOIndexOutOfRangeException,
                             msg.c_str());
      }

      unsigned int SequenceImpl::getListIndex(unsigned int index)
      {
         SEQUENCE_ITEM_LIST::iterator i;

         checkRange(index, i);

         if (i != the_list.end()) {
            if ((*i).getProp() == 0) 
            {
               std::string msg("Get list index on text property");
               SDO_THROW_EXCEPTION("getListIndex",
                                   SDOUnsupportedOperationException,
                                   msg.c_str());
            }
            if ((*i).getProp()->isMany())
            {
               return (*i).getIndex();
            }
            else
            {
               std::string msg("Get list index on text single valued property");
               SDO_THROW_EXCEPTION("getListIndex",
                                   SDOUnsupportedOperationException,
                                   msg.c_str());
            }
         }
         std::string msg("Index out of range:");
         msg += index;
         SDO_THROW_EXCEPTION("getListIndex",
                             SDOIndexOutOfRangeException,
                             msg.c_str());
      }

      unsigned int SequenceImpl::getIndex(const char* propName, unsigned int pindex)
      {
         SEQUENCE_ITEM_LIST::iterator i;
         unsigned int j = 0;
         for (i = the_list.begin(), j = 0; i != the_list.end(); ++i, ++j)
         {
            const Property* p = (*i).getProp();
            if (p == 0)
            {
               continue; // avoid text
            }

            if (!strcmp(p->getName(), propName))
            {
               if (p->isMany())
               {
                  if (pindex == (*i).getIndex())
                  {
                     return j;
                  }
               }
               else
               {
                  return j;
               }
            }
         }
         SDO_THROW_EXCEPTION("getIndex",
                             SDOIndexOutOfRangeException,
                             "SequenceImpl::getIndex - property Setting not in sequence");
      }

      unsigned int SequenceImpl::getIndex(const Property& p, unsigned int pindex)
      {
         return getIndex(p.getName(), pindex);
      }

      // specific getters for prims and data objects

      // It isn't obvious from the code here, however, this method also
      // retrieves free text elements (see the spec) ie text items that can be
      // added to the sequence even though they are not associated with a property.
      const char* SequenceImpl::getCStringValue(unsigned int index)
      {
         const SDOValue& result = getSDOValue(index);
   
         if (result.isSet())
         {
            return result.getCString();
         }
         else
         {
            return 0;
         }
      }

      unsigned int SequenceImpl::getLength(unsigned int index)
      {
         SEQUENCE_ITEM_LIST::iterator i;
         CHECK_RANGE;
         const Property* p = (*i).getProp();
         switch (p->getTypeEnum())
         {
            case Type::StringType:
               return getStringValue(index,0,0);
            case Type::BytesType:
               return getBytesValue(index,0,0);
            default:
               return 0;
         }
      }

   RefCountingPointer<DataObject> SequenceImpl::getDataObjectValue(unsigned int index)
   {
      SEQUENCE_ITEM_LIST::iterator i;
      CHECK_RANGE;
      const Property* p = (*i).getProp();
      if (p == 0) {
         return 0;
      }
      if (p->isMany())
      {
         DataObjectList& dol = the_do->getList(*p);
         DataObject* list_do = dol[(*i).getIndex()];
         if (list_do != 0)
         {
            return list_do;
         }
         return 0;
      }
      return the_do->getDataObject(*((*i).getProp()));
   }

    ///////////////////////////////////////////////////////////////////////////
    // generic getter for those types which support it
    ///////////////////////////////////////////////////////////////////////////
    
    void SequenceImpl::setCStringValue(    unsigned int index, const char* s )
    {
        SEQUENCE_ITEM_LIST::iterator i;
        CHECK_RANGE;
        if ((*i).getProp() == 0) {
            (*i).setText(s);
            return;
        }
        the_do->setCString(*((*i).getProp()),s);

    }
    void SequenceImpl::setBooleanValue(   unsigned int index, bool        b )
    {
        SEQUENCE_ITEM_LIST::iterator i;
        CHECK_RANGE;
        if ((*i).getProp() == 0) {
            return;
        }
        the_do->setBoolean(*((*i).getProp()),b);
    }

    void SequenceImpl::setByteValue(      unsigned int index, char        c )
    {
        SEQUENCE_ITEM_LIST::iterator i;
        CHECK_RANGE;
        if ((*i).getProp() == 0) {
            return;
        }
        the_do->setByte(*((*i).getProp()),c);
    }

    void SequenceImpl::setCharacterValue(     unsigned int index, wchar_t     c )
    {
        SEQUENCE_ITEM_LIST::iterator i;
        CHECK_RANGE;
        if ((*i).getProp() == 0) {
            return;
        }
        the_do->setCharacter(*((*i).getProp()),c);
    }
    void SequenceImpl::setStringValue(   unsigned int index, const wchar_t* s , unsigned int len)
    {
        SEQUENCE_ITEM_LIST::iterator i;
        CHECK_RANGE;
        if ((*i).getProp() == 0) {
            return;
        }
        the_do->setString(*((*i).getProp()),s, len);
    }
    void SequenceImpl::setBytesValue(   unsigned int index, const char* s , unsigned int len)
    {
        SEQUENCE_ITEM_LIST::iterator i;
        CHECK_RANGE;
        if ((*i).getProp() == 0) {
            return;
        }
        the_do->setBytes(*((*i).getProp()),s, len);
    }
    void SequenceImpl::setShortValue(     unsigned int index, short       s )
    {
        SEQUENCE_ITEM_LIST::iterator i;
        CHECK_RANGE;
        if ((*i).getProp() == 0) {
            return;
        }
        the_do->setShort(*((*i).getProp()),s);
    }

    void SequenceImpl::setIntegerValue(       unsigned int index, long         l)
    {
        SEQUENCE_ITEM_LIST::iterator i;
        CHECK_RANGE;
        if ((*i).getProp() == 0) {
            return;
        }
        the_do->setInteger(*((*i).getProp()),l);
    }


    void SequenceImpl::setLongValue(  unsigned int index, int64_t     l )
    {
        SEQUENCE_ITEM_LIST::iterator i;
        CHECK_RANGE;
        if ((*i).getProp() == 0) {
            return;
        }
        the_do->setLong(*((*i).getProp()),l);
    }

    void SequenceImpl::setFloatValue(     unsigned int index, float       f )
    {
        SEQUENCE_ITEM_LIST::iterator i;
        CHECK_RANGE;
        if ((*i).getProp() == 0) {
            return;
        }
        the_do->setFloat(*((*i).getProp()),f);
    }


    void SequenceImpl::setDoubleValue(unsigned int index, long double d )
    {
        SEQUENCE_ITEM_LIST::iterator i;
        CHECK_RANGE;
        if ((*i).getProp() == 0) {
            return;
        }
        the_do->setDouble(*((*i).getProp()),d);
    }

    void SequenceImpl::setDateValue(unsigned int index, const SDODate t )
    {
        SEQUENCE_ITEM_LIST::iterator i;
        CHECK_RANGE;
        if ((*i).getProp() == 0) {
            return;
        }
        the_do->setDate(*((*i).getProp()),t);
    }

    void SequenceImpl::setDataObjectValue(unsigned int index, DataObjectPtr d )
    {
        SEQUENCE_ITEM_LIST::iterator i;
        CHECK_RANGE;
        if ((*i).getProp() == 0) {
            return;
        }
        the_do->setDataObject(*((*i).getProp()),d);
    }


bool SequenceImpl::addDataObject(const char* propertyName, RefCountingPointer<DataObject> v)
{
   const PropertyImpl* p = the_do->getPropertyImpl(propertyName);
   if (p == 0)
   {
      if (the_do->getType().isOpenType())
      {
         p = the_do->defineDataObject(propertyName, v->getType());
      }
      if (p == 0)
      {
         std::string msg("Cannot find property:");
         msg += propertyName;
         SDO_THROW_EXCEPTION("getProperty", SDOPropertyNotFoundException,
                             msg.c_str());
      }
   }
   return addDataObject((const Property&)*p,v);
}
bool SequenceImpl::addDataObject(unsigned int propertyIndex, RefCountingPointer<DataObject> v)
{
   return addDataObject(the_do->getProperty(propertyIndex), v);
}
bool SequenceImpl::addDataObject(const Property& p, RefCountingPointer<DataObject> v)
{
   // If this is a many valued property.
   if (p.isMany())
   {
      // Append the incoming data object value to the end of the list of
      // values. The sequence is updated as part of the append operation.
      DataObjectList& dol = the_do->getList(p);
      dol.append((RefCountingPointer<DataObject>) v);
      /* the_list.push_back(seq_item(&p,dol.size()-1));*/
      return true;
   }

   SEQUENCE_ITEM_LIST::iterator i;

   // Scan the sequence to check that this property has not been set already.
   for (i= the_list.begin(); i != the_list.end(); ++i)
   {
      const Property* pp = (*i).getProp();
      if (pp == 0)
      {
         continue;              // This item is a free text entry.
      }
      if (!strcmp(pp->getName(), p.getName()))
      {
         SDO_THROW_EXCEPTION("add",
                             SDOUnsupportedOperationException,
                             "Sequence::add of property which already exists in sequence");
      }
   }

   the_do->setDataObject(p, v, true);
   // the_list.push_back(seq_item(&p, 0));
   return true;
}


bool SequenceImpl::addDataObject(unsigned int index, const char* propertyName, RefCountingPointer<DataObject> v)
{
   const PropertyImpl* p = the_do->getPropertyImpl(propertyName);
   if (p == 0)
   {
      if (the_do->getType().isOpenType())
      {
         p = the_do->defineDataObject(propertyName, v->getType());
      }
      if (p == 0)
      {
         std::string msg("Cannot find property:");
         msg += propertyName;
         SDO_THROW_EXCEPTION("getProperty", SDOPropertyNotFoundException,
                             msg.c_str());
      }
   }
   return addDataObject(index,(const Property&)*p,v);
}
bool SequenceImpl::addDataObject(unsigned int index, unsigned int propertyIndex, RefCountingPointer<DataObject> v)
{
   return addDataObject(index,the_do->getProperty(propertyIndex), v);
}
bool SequenceImpl::addDataObject(unsigned int index, const Property& p, RefCountingPointer<DataObject> v)
{
   SEQUENCE_ITEM_LIST::iterator i;
   SEQUENCE_ITEM_LIST::iterator i2 = the_list.end();
   unsigned int j = 0;

   if (index >= the_list.size())
   {
      return addDataObject(p, v);
   }
   if (p.isMany())
   {
      DataObjectList& dol = the_do->getList(p);
      dol.append((RefCountingPointer<DataObject>)v);

      checkRange(index, i);

      /*the_list.insert(i,seq_item(&p,dol.size()-1));*/
      return true;
   }

   for (i = the_list.begin(); i != the_list.end(); ++i)
   {
      const Property* pp = (*i).getProp();
      if (pp == 0)
      {
         continue;              // This item is a free text entry.
      }
      if (!strcmp(pp->getName(), p.getName()))
      {
         SDO_THROW_EXCEPTION("Insert",
                             SDOUnsupportedOperationException,
                             "Sequence::insert of property which already exists in sequence");
      }
      if (j == index)
      {
         i2 = i;
      }
      j++;
   }
   // setDataObject can update the sequence but does not do so by an append
   // so tell it to mind its own business and we will update the sequence here.
   the_do->setDataObject(p, v, false);
   the_list.insert(i2, seq_item(&p, 0));
   return true;
}

    void SequenceImpl::push(const Property& p, unsigned int index)
    {
        the_list.push_back(seq_item(&p,index));
    }

    void SequenceImpl::remove(unsigned int index)
    {
        if (index >= the_list.size()) {
            std::string msg("Index out of range:");
            msg += index;
            SDO_THROW_EXCEPTION("Sequence remove", SDOIndexOutOfRangeException,
            msg.c_str());
        }
        SEQUENCE_ITEM_LIST::iterator i;

        checkRange(index, i);

        the_list.erase(i);
        return;
    }

    void SequenceImpl::removeAll(const Property& p)
    {
        int j = 0;
        const Property* prop;
        SEQUENCE_ITEM_LIST::iterator i = the_list.begin();

        while (i != the_list.end())
        {
           prop = (*i).getProp();
           if (prop != 0)
           {
              if (!strcmp(prop->getName(), p.getName()))
              {
                 i = the_list.erase(i);
              }
              else
              {
                 ++i;
              }
           }
           else
           {
              ++i;
           }

        }
    
        return;
    }

    void SequenceImpl::move(unsigned int toIndex, unsigned int fromIndex)
    {
        if (fromIndex >= the_list.size()) {
            std::string msg("Index out of range:");
            msg += fromIndex;
            SDO_THROW_EXCEPTION("Sequence Move", SDOIndexOutOfRangeException,
            msg.c_str());
        }

        if (toIndex == fromIndex) return;

        SEQUENCE_ITEM_LIST::iterator i1,
                            i2 = the_list.end(), 
                            i3 = the_list.end();
        unsigned int j = 0;
        for (i3 = the_list.begin(); 
             j < toIndex && j < fromIndex && 
                 i3 != the_list.end() ; ++i3);
        {
            if (j == toIndex)   i1 = i3;
            if (j == fromIndex) i2 = i3;
            j++;
        }

        if (toIndex < fromIndex) 
        {
            the_list.insert( i1, *i2);
            the_list.erase(i2);
        }
        else 
        {
            if (toIndex + 1 == the_list.size()) 
            {
                the_list.push_back(*i2);
            }
            else
            {
                the_list.insert(++i1,*i2);
            }
            the_list.erase(i2);
        }
        return;
    }

    bool SequenceImpl::addText(const char* text)
    {
        the_list.push_back(seq_item(text));
        return true;
    }

    bool SequenceImpl::isText(unsigned int index)
    {
        if (index >= the_list.size()) {
            return false;
        }
        SEQUENCE_ITEM_LIST::iterator i;

        checkRange(index, i);

        if ((*i).getProp() == 0)
        {
            return true;
        }

        return false;
    }

    bool SequenceImpl::addText(unsigned int index, const char* text)
    {
        if (index >= the_list.size()) {
            return addText(text);
        }

        SEQUENCE_ITEM_LIST::iterator i;

        checkRange(index, i);

        the_list.insert(i,seq_item(text));
        return true;
    }

    bool SequenceImpl::setText(unsigned int index, const char* text)
    {
        if (index >= the_list.size()) {
            return false;
        }

        if (!isText(index))
        {
            return false;
        }

        SEQUENCE_ITEM_LIST::iterator i;

        checkRange(index, i);

        (*i).setText(text);
        return true;
    }

const SDOValue& SequenceImpl::getSDOValue(unsigned int index)
{
   SEQUENCE_ITEM_LIST::iterator i;

   checkRange(index, i);

   const Property* p = (*i).getProp();
   if (p == 0)
   {
      // There is no property. Either this is a free text element or we have a
      // problem.
      const SDOValue* freeText = (*i).getFreeText();
      if (freeText != 0)
      {
         return *freeText;
      }
      else
      {
         return SDOValue::unsetSDOValue;
      }
   }
   PropertyImpl* pProp = 0;     // Not used. Just a place for getSDOValue to
                                // write the return value that we don't need.
   if (p->isMany())
   {
      DataObjectList& dol = the_do->getList(*p);
      DataObject* list_do = dol[(*i).getIndex()];
      if (list_do != 0)
      {
         return ((DataObjectImpl*) list_do)->getSDOValue(&pProp);
      }
      return SDOValue::unsetSDOValue;
   }
   return the_do->getSDOValue(*((*i).getProp()), &pProp);
}

bool SequenceImpl::getBooleanValue(unsigned int index)
{
   const SDOValue& result = getSDOValue(index);
   
   if (result.isSet())
   {
      return result.getBoolean();
   }
   else
   {
      return (bool) 0;
   }
}

char SequenceImpl::getByteValue(unsigned int index)
{
   const SDOValue& result = getSDOValue(index);
   
   if (result.isSet())
   {
      return result.getByte();
   }
   else
   {
      return (char) 0;
   }
}

wchar_t SequenceImpl::getCharacterValue(unsigned int index)
{
   const SDOValue& result = getSDOValue(index);
   
   if (result.isSet())
   {
      return result.getCharacter();
   }
   else
   {
      return (wchar_t) 0;
   }
}

short SequenceImpl::getShortValue(unsigned int index)
{
   const SDOValue& result = getSDOValue(index);
   
   if (result.isSet())
   {
      return result.getShort();
   }
   else
   {
      return (short) 0;
   }
}

long SequenceImpl::getIntegerValue(unsigned int index)
{
   const SDOValue& result = getSDOValue(index);
   
   if (result.isSet())
   {
      return result.getInteger();
   }
   else
   {
      return (long) 0;
   }
}

int64_t SequenceImpl::getLongValue(unsigned int index)
{
   const SDOValue& result = getSDOValue(index);
   
   if (result.isSet())
   {
      return result.getLong();
   }
   else
   {
      return (int64_t) 0;
   }
}

float SequenceImpl::getFloatValue(unsigned int index)
{
   const SDOValue& result = getSDOValue(index);
   
   if (result.isSet())
   {
      return result.getFloat();
   }
   else
   {
      return (float) 0;
   }
}

long double SequenceImpl::getDoubleValue(unsigned int index)
{
   const SDOValue& result = getSDOValue(index);
   
   if (result.isSet())
   {
      return result.getDouble();
   }
   else
   {
      return (long double) 0;
   }
}

const SDODate SequenceImpl::getDateValue(unsigned int index)
{
   const SDOValue& result = getSDOValue(index);
   
   if (result.isSet())
   {
      return result.getDate();
   }
   else
   {
      return (SDODate) 0;
   }
}

bool SequenceImpl::addCString(const char* propertyName, const char* v)
{
   return addSDOValue(propertyName, SDOValue(SDOString(v)));
}
bool SequenceImpl::addCString(const Property& p, const char* v)
{
   return addSDOValue(p, SDOValue(SDOString(v)));
}
bool SequenceImpl::addCString(unsigned int propertyIndex, const char* v)
{
   return addCString(the_do->getProperty(propertyIndex), v);
}

bool SequenceImpl::addByte(const char* propertyName, char v)
{
   return addSDOValue(propertyName, SDOValue(v));
}
bool SequenceImpl::addByte(const Property& p, char v)
{
   return addSDOValue(p, SDOValue(v));
}
bool SequenceImpl::addByte(unsigned int propertyIndex, char v)
{
   return addByte(the_do->getProperty(propertyIndex), v);
}

bool SequenceImpl::addCharacter(const char* propertyName, wchar_t v)
{
   return addSDOValue(propertyName, SDOValue(v));
}
bool SequenceImpl::addCharacter(const Property& p, wchar_t v)
{
   return addSDOValue(p, SDOValue(v));
}
bool SequenceImpl::addCharacter(unsigned int propertyIndex, wchar_t v)
{
   return addCharacter(the_do->getProperty(propertyIndex), v);
}

bool SequenceImpl::addShort(const char* propertyName, short v)
{
   return addSDOValue(propertyName, SDOValue(v));
}
bool SequenceImpl::addShort(const Property& p, short v)
{
   return addSDOValue(p, SDOValue(v));
}
bool SequenceImpl::addShort(unsigned int propertyIndex, short v)
{
   return addShort(the_do->getProperty(propertyIndex), v);
}

bool SequenceImpl::addFloat(const char* propertyName, float v)
{
   return addSDOValue(propertyName, SDOValue(v));
}
bool SequenceImpl::addFloat(const Property& p, float v)
{
   return addSDOValue(p, SDOValue(v));
}
bool SequenceImpl::addFloat(unsigned int propertyIndex, float v)
{
   return addFloat(the_do->getProperty(propertyIndex), v);
}

bool SequenceImpl::addDouble(const char* propertyName, long double v)
{
   return addSDOValue(propertyName, SDOValue(v));
}
bool SequenceImpl::addDouble(const Property& p, long double v)
{
   return addSDOValue(p, SDOValue(v));
}
bool SequenceImpl::addDouble(unsigned int propertyIndex, long double v)
{
   return addDouble(the_do->getProperty(propertyIndex), v);
}

bool SequenceImpl::addDate(const char* propertyName, const SDODate v)
{
   return addSDOValue(propertyName, SDOValue(v));
}
bool SequenceImpl::addDate(const Property& p, const SDODate v)
{
   return addSDOValue(p, SDOValue(v));
}
bool SequenceImpl::addDate(unsigned int propertyIndex, const SDODate v)
{
   return addDate(the_do->getProperty(propertyIndex), v);
}

bool SequenceImpl::addLong(const char* propertyName, int64_t v)
{
   return addSDOValue(propertyName, SDOValue(v));
}
bool SequenceImpl::addLong(const Property& p, int64_t v)
{
   return addSDOValue(p, SDOValue(v));
}
bool SequenceImpl::addLong(unsigned int propertyIndex, int64_t v)
{
   return addLong(the_do->getProperty(propertyIndex), v);
}

bool SequenceImpl::addInteger(const char* propertyName, long v)
{
   return addSDOValue(propertyName, SDOValue(v));
}
bool SequenceImpl::addInteger(const Property& p, long v)
{
   return addSDOValue(p, SDOValue(v));
}
bool SequenceImpl::addInteger(unsigned int propertyIndex, long v)
{
   return addInteger(the_do->getProperty(propertyIndex), v);
}

bool SequenceImpl::addBoolean(const char* propertyName, bool v)
{
   return addSDOValue(propertyName, SDOValue(v));
}
bool SequenceImpl::addBoolean(const Property& p, bool v)
{
   return addSDOValue(p, SDOValue(v));
}

bool SequenceImpl::addBoolean(unsigned int propertyIndex, bool v)
{
   return addBoolean(the_do->getProperty(propertyIndex), v);
}

// The return value is not spec compliant (which calls for void) it is a
// yes/no as to whether the call succeeded.
bool SequenceImpl::addSDOValue(const char* propertyName, const SDOValue& sval)
{
   const PropertyImpl* p = the_do->getPropertyImpl(propertyName);
   if (p == 0)
   {
      if (the_do->getType().isOpenType())
      {
         p = the_do->defineSDOValue(propertyName, sval);
      }
      if (p == 0)
      {
         std::string msg("Cannot find property:");
         msg += propertyName;
         SDO_THROW_EXCEPTION("SequenceImpl::addSDOValue",
                             SDOPropertyNotFoundException,
                             msg.c_str());
      }
   }
   return addSDOValue((const Property&) *p, sval);
}
bool SequenceImpl::addSDOValue(const Property& p, const SDOValue& sval)
{
   if (p.isMany())
   {
      DataObjectList& dol = the_do->getList(p);
      dol.append(sval);
      /* the_list.push_back(seq_item(&p,dol.size()-1));*/
      return true;
   }
//   std::cout << "Incoming property: " << p.getName() << std::endl << std::endl;
   SEQUENCE_ITEM_LIST::iterator i;
   for (i = the_list.begin(); i != the_list.end(); ++i)
   {
      const Property* pp = (*i).getProp();
      if (pp == 0) continue;
//      std::cout << pp->getName() << std::endl;
      if (!strcmp(pp->getName(), p.getName()))
      {
         SDO_THROW_EXCEPTION("add",
                             SDOUnsupportedOperationException,
                             "Sequence::add of property which already exists in sequence");
      }
   }
//   std::cout << std::endl;
   the_do->setSDOValue(p, sval, sval.convertTypeEnumToString(), true);
   // the_list.push_back(seq_item(&p, 0));
   return true;
}
bool SequenceImpl::addSDOValue(unsigned int propertyIndex, const SDOValue& sval)
{
   return addSDOValue(the_do->getProperty(propertyIndex), sval);
}

bool SequenceImpl::addString(const char* propertyName, const wchar_t* v, unsigned int len)
{
   return addSDOValue(propertyName, SDOValue(v, len));
}
bool SequenceImpl::addString(unsigned int propertyIndex, const wchar_t* v, unsigned int len)
{
   return addString(the_do->getProperty(propertyIndex), v, len);
}
bool SequenceImpl::addString(const Property& p, const wchar_t* v, unsigned int len)
{
   return addSDOValue(p, SDOValue(v, len));
}

bool SequenceImpl::addBytes(const char* propertyName, const char* v, unsigned int len)
{
   return addSDOValue(propertyName, SDOValue(v, len));
}
bool SequenceImpl::addBytes(unsigned int propertyIndex, const char* v, unsigned int len)
{
   return addBytes(the_do->getProperty(propertyIndex), v, len);
}
bool SequenceImpl::addBytes(const Property& p, const char* v, unsigned int len)
{
   return addSDOValue(p, SDOValue(v, len));
}

    bool SequenceImpl::addByte(unsigned int index, const char* propertyName, char v)
    {
       return addSDOValue(index, propertyName, SDOValue(v));
    }
    bool SequenceImpl::addByte(unsigned int index, unsigned int propertyIndex, char v)
    {
        return addByte(index, the_do->getProperty(propertyIndex), v);
    }
    bool SequenceImpl::addByte(unsigned int index, const Property& p, char v)
    {
       return addSDOValue(index, p, SDOValue(v));
    }

    bool SequenceImpl::addCharacter(unsigned int index, const char* propertyName, wchar_t v)
    {
       return addSDOValue(index, propertyName, SDOValue(v));
    }
    bool SequenceImpl::addCharacter(unsigned int index, unsigned int propertyIndex, wchar_t v)
    {
        return addCharacter(index, the_do->getProperty(propertyIndex), v);
    }
    bool SequenceImpl::addCharacter(unsigned int index, const Property& p, wchar_t v)
    {
       return addSDOValue(index, p, SDOValue(v));
    }

    bool SequenceImpl::addShort(unsigned int index, const char* propertyName, short v)
    {
       return addSDOValue(index, propertyName, SDOValue(v));
    }
    bool SequenceImpl::addShort(unsigned int index, unsigned int propertyIndex, short v)
    {
        return addShort(index, the_do->getProperty(propertyIndex), v);
    }
    bool SequenceImpl::addShort(unsigned int index, const Property& p, short v)
    {
       return addSDOValue(index, p, SDOValue(v));
    }

    bool SequenceImpl::addLong(unsigned int index, const char* propertyName, int64_t v)
    {
       return addSDOValue(index, propertyName, SDOValue(v));
    }
    bool SequenceImpl::addLong(unsigned int index, unsigned int propertyIndex, int64_t v)
    {
        return addLong(index, the_do->getProperty(propertyIndex), v);
    }
    bool SequenceImpl::addLong(unsigned int index, const Property& p, int64_t v)
    {
       return addSDOValue(index, p, SDOValue(v));
    }

    bool SequenceImpl::addFloat(unsigned int index, const char* propertyName, float v)
    {
       return addSDOValue(index, propertyName, SDOValue(v));
    }
    bool SequenceImpl::addFloat(unsigned int index, unsigned int propertyIndex, float v)
    {
        return addFloat(index, the_do->getProperty(propertyIndex), v);
    }
    bool SequenceImpl::addFloat(unsigned int index, const Property& p, float v)
    {
       return addSDOValue(index, p, SDOValue(v));
    }

    bool SequenceImpl::addDouble(unsigned int index, const char* propertyName, long double v)
    {
       return addSDOValue(index, propertyName, SDOValue(v));
    }
    bool SequenceImpl::addDouble(unsigned int index, unsigned int propertyIndex, long double v)
    {
        return addDouble(index, the_do->getProperty(propertyIndex), v);
    }
    bool SequenceImpl::addDouble(unsigned int index, const Property& p, long double v)
    {
       return addSDOValue(index, p, SDOValue(v));
    }

    bool SequenceImpl::addDate(unsigned int index, const char* propertyName, const SDODate v)
    {
       return addSDOValue(index, propertyName, SDOValue(v));
    }
    bool SequenceImpl::addDate(unsigned int index, unsigned int propertyIndex, const SDODate v)
    {
        return addDate(index, the_do->getProperty(propertyIndex), v);
    }
    bool SequenceImpl::addDate(unsigned int index, const Property& p, const SDODate v)
    {
       return addSDOValue(index, p, SDOValue(v));
    }

    bool SequenceImpl::addInteger(unsigned int index, const char* propertyName, long v)
    {
       return addSDOValue(index, propertyName, SDOValue(v));
    }
    bool SequenceImpl::addInteger(unsigned int index, unsigned int propertyIndex, long v)
    {
        return addInteger(index, the_do->getProperty(propertyIndex), v);
    }
    bool SequenceImpl::addInteger(unsigned int index, const Property& p, long v)
    {
       return addSDOValue(index, p, SDOValue(v));
    }

    bool SequenceImpl::addCString(unsigned int index, const char* propertyName, const char* v)
    {
       return addSDOValue(index, propertyName, SDOValue(SDOString(v)));
    }
    bool SequenceImpl::addCString(unsigned int index, unsigned int propertyIndex, const char* v)
    {
        return addCString(index, the_do->getProperty(propertyIndex), v);
    }
    bool SequenceImpl::addCString(unsigned int index, const Property& p, const char* v)
    {
       return addSDOValue(index, p, SDOValue(SDOString(v)));
    }

    bool SequenceImpl::addBoolean(unsigned int index, const char* propertyName, bool v)
    {
       return addSDOValue(index, propertyName, SDOValue(v));
    }
    bool SequenceImpl::addBoolean(unsigned int index, unsigned int propertyIndex, bool v)
    {
        return addBoolean(index, the_do->getProperty(propertyIndex), v);
    }
    bool SequenceImpl::addBoolean(unsigned int index, const Property& p, bool v)
    {
       return addSDOValue(index, p, SDOValue(v));
    }

    bool SequenceImpl::addSDOValue(unsigned int index, const char* propertyName, const SDOValue& sval)
    {
        const PropertyImpl* p = the_do->getPropertyImpl(propertyName);
        if (p == 0)
        {
            if (the_do->getType().isOpenType())
            {
                p = the_do->defineSDOValue(propertyName, sval);
            }
            if (p == 0)
            {
                std::string msg("Cannot find property:");
                msg += propertyName;
                SDO_THROW_EXCEPTION("getProperty",
                                    SDOPropertyNotFoundException,
                                    msg.c_str());
            }
        }
        return addSDOValue(index, (const Property&) *p, sval);
    }
    bool SequenceImpl::addSDOValue(unsigned int index, unsigned int propertyIndex, const SDOValue& sval)
    {
        return addSDOValue(index,the_do->getProperty(propertyIndex), sval);
    }
    bool SequenceImpl::addSDOValue(unsigned int index, const Property& p, const SDOValue& sval)
    {
        SEQUENCE_ITEM_LIST::iterator i;
        SEQUENCE_ITEM_LIST::iterator i2 = the_list.end();
        unsigned int j = 0;
        if (index >= the_list.size()) {
            return addSDOValue(p, sval);
        }
        if (p.isMany())
        {
            DataObjectList& dol = the_do->getList(p);
            dol.append(sval);

            checkRange(index, i);

            /*the_list.insert(i,seq_item(&p,dol.size()-1));*/
            return true;
        }

        for (i = the_list.begin(); i != the_list.end(); ++i)
        {
            const Property* pp = (*i).getProp();
            if (pp == 0) continue;
            if (!strcmp(pp->getName(), p.getName()))
            {
               SDO_THROW_EXCEPTION("Insert",
                                   SDOUnsupportedOperationException,
                                   "Sequence::insert of property which already exists in sequence");
            }
            if (j == index) {
                i2 = i;
            }
            j++;
        }

        // setSDOValue can update the sequence but does not do so by an append so
        // tell it to mind its own business and we will update the sequence here.
        the_do->setSDOValue(p, sval, sval.convertTypeEnumToString(), false);
        the_list.insert(i2, seq_item(&p, 0));
        return true;
    }

bool SequenceImpl::addString(unsigned int index,
                            const char* propertyName,
                            const wchar_t* v,
                            unsigned int len)
{
   return addSDOValue(index, propertyName, SDOValue(v, len));
}

bool SequenceImpl::addString(unsigned int index, unsigned int propertyIndex, const wchar_t* v, unsigned int len)
{
   return addString(index,the_do->getProperty(propertyIndex), v, len);
}
bool SequenceImpl::addString(unsigned int index, const Property& p, const wchar_t* v, unsigned int len)
{
   return addSDOValue(index, p, SDOValue(v, len));
}

bool SequenceImpl::addBytes(unsigned int index,
                            const char* propertyName,
                            const char* v,
                            unsigned int len)
{
   return addSDOValue(index, propertyName, SDOValue(v, len));
}

bool SequenceImpl::addBytes(unsigned int index, unsigned int propertyIndex, const char* v, unsigned int len)
{
   return addBytes(index,the_do->getProperty(propertyIndex), v, len);
}
bool SequenceImpl::addBytes(unsigned int index, const Property& p, const char* v, unsigned int len)
{
   return addSDOValue(index, p, SDOValue(v, len));
}

unsigned int SequenceImpl::getBytesValue(unsigned int index, char* ptr, unsigned int max)
{
   
   const SDOValue& result = getSDOValue(index);
   
   if (result.isSet())
   {
      return result.getBytes(ptr, max);
   }
   else
   {
      return 0;
   }
}
unsigned int SequenceImpl::getStringValue(unsigned int index, wchar_t* ptr, unsigned int max)
{
   
   const SDOValue& result = getSDOValue(index);
   
   if (result.isSet())
   {
      return result.getString(ptr, max);
   }
   else
   {
      return 0;
   }
}

};
};
