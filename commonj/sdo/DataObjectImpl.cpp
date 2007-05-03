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

/* $Rev: 527087 $ $Date$ */

#include "commonj/sdo/disable_warn.h"
#include "commonj/sdo/DataObjectImpl.h"

#include "commonj/sdo/SDORuntimeException.h"

#include "commonj/sdo/Property.h"
#include "commonj/sdo/Type.h"
#include "commonj/sdo/TypeList.h"
#include "commonj/sdo/Sequence.h"
#include "commonj/sdo/SequenceImpl.h"

#include "commonj/sdo/PropertyList.h"

#include "commonj/sdo/Logging.h"

#include "commonj/sdo/TypeImpl.h"

#include "commonj/sdo/ChangeSummaryImpl.h"
#include "commonj/sdo/DataFactoryImpl.h"
#include "commonj/sdo/SDOUtils.h"

#include <string>
#include <stdio.h>
#include <stdlib.h>
using std::string;




namespace commonj{
namespace sdo {

    /**
     * RDO is an internal class holding a property value
     */

    rdo::rdo(unsigned int infirst, DataObjectImpl* insecond)
        : first(infirst), second(insecond)
    {
    }

    rdo::rdo()
    {
        first = 0;
        second = 0;
    }

    rdo::rdo (const rdo& inrdo)
    {
        first = inrdo.first;
        second = inrdo.second;
    }

    rdo::~rdo()
    {
    }

#define ASSERT_SETTABLE(property,primval) ASSERT_WRITABLE(*property, set##primval)

 /**  DataObject
  *  DataObjects are the non-primitive members of a Data graph.
  *
  * A data object is a representation of some structured data. 
  * it is the fundamental component in the SDO (Service Data Objects) package.
  * Data objects support reflection, path-based accesss, convenience creation 
  * and deletion methods,and the ability to be part of a data graph.
  * Each data object holds its data as a series of properties. 
  * Properties can be accessed by name, property index, or using the property 
  * meta object itself. 
  * A data object can also contain references to other data objects, through 
  * reference-type properties.
  * A data object has a series of convenience accessors for its properties. 
  * These methods either use a path (String), a property index, 
  * or the property's meta object itself, to identify the property.
  * Some examples of the path-based accessors are as follows:
  * DataObjectPtr company = ...;
  * company->getString("name");                   
  * company->setString("name", "acme");
  * company->getString("department.0/name")       
  *                                        .n  indexes from 0.
  * company->getString("department[1]/name")      [] indexes from 1.
  * company->getDataObject("department[number=123]")  returns the department where number=123
  * company->getDataObject("..")                      returns the containing data object
  * company->getDataObject("/")                       returns the root containing data object
  * There are specific accessors for the primitive types and commonly used 
  * data types like String.
  */

  unsigned int DataObjectImpl::getBytes(const char* path, char* valptr , unsigned int max)
  {
    const SDOString pathObject(path);
    unsigned int result = getBytes(pathObject, valptr, max);
    return result;
  }

  unsigned int DataObjectImpl::getString(const char* path, wchar_t* buf, unsigned int max)
  {
    return getString(SDOString(path), buf, max);
  }

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
        return getLength(SDOString(path));
    }

    unsigned int DataObjectImpl::getLength(const SDOString& path)
    {
        DataObjectImpl* d;
        SDOString spath;
        DataObjectImpl::stripPath(path, spath);
        SDOString prop = findPropertyContainer(spath, &d);
        if (d != 0) {
            if (prop.length() == 0) {
                return 0;
            }
            else 
            {
                const Property& p  = d->getProperty(prop);
                return getLength(p);
            }
        }
        else 
        {
            if (prop.length())
            {
                const Property& p  = getProperty(prop);
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
      return getLength(getProperty(index));
   }

   // +++
   // Provide implementations for the getXXX(const char* path) methods.
   // These are implemented by delegating the call
   // to the corresponding getXXX(const SDOString& path) method.

   // getPrimitiveFromPath(Boolean,bool,false);
   bool DataObjectImpl::getBoolean(const char* path)
   {
      return getBoolean(SDOString(path));
   }

   // getPrimitiveFromPath(Short,short,0);
   short DataObjectImpl::getShort(const char* path)
   {
      return getShort(SDOString(path));
   }

   // getPrimitiveFromPath(Byte,char,0);
   char DataObjectImpl::getByte(const char* path)
   {
      return getByte(SDOString(path));
   }

   // getPrimitiveFromPath(Character,wchar_t,0);
   wchar_t DataObjectImpl::getCharacter(const char* path)
   {
      return getCharacter(SDOString(path));
   }

   // getPrimitiveFromPath(Date,const SDODate,0);
   const SDODate DataObjectImpl::getDate(const char* path)
   {
      return getDate(SDOString(path));
   }

   // getPrimitiveFromPath(Double,long double,0.0);
   long double DataObjectImpl::getDouble(const char* path)
   {
      return getDouble(SDOString(path));
   }

   // getPrimitiveFromPath(Float,float,0.0);
   float DataObjectImpl::getFloat(const char* path)
   {
      return getFloat(SDOString(path));
   }

   // getPrimitiveFromPath(Integer,long,0);
   long DataObjectImpl::getInteger(const char* path)
   {
      return getInteger(SDOString(path));
   }

   // getPrimitiveFromPath(Long,int64_t,0L);
   int64_t DataObjectImpl::getLong(const char* path)
   {
      return getLong(SDOString(path));
   }

   // End of implementations for the getXXX(const char* path) methods.
   // ---


   // +++
   // Provide implementations for the setXXX(const char* path, XXX) methods.
   // These are implemented by delegating the call
   // to the corresponding setXXX(const SDOString& path, XXX) method.

   void DataObjectImpl::setBoolean(const char* path, bool b)
   {
      setBoolean(SDOString(path), b);
   }

   void DataObjectImpl::setShort(const char* path, short s)
   {
      setShort(SDOString(path), s);
   }

   void DataObjectImpl::setByte(const char* path, char c)
   {
      setByte(SDOString(path), c);
   }

   void DataObjectImpl::setCharacter(const char* path, wchar_t c)
   {
      setCharacter(SDOString(path), c);
   }

   void DataObjectImpl::setDate(const char* path, const SDODate d)
   {
      setDate(SDOString(path), d);
   }

   void DataObjectImpl::setDouble(const char* path, long double d)
   {
      setDouble(SDOString(path), d);
   }

   void DataObjectImpl::setFloat(const char* path, float f)
   {
      setFloat(SDOString(path), f);
   }

   void DataObjectImpl::setInteger(const char* path, long i)
   {
      setInteger(SDOString(path), i);
   }

   // setPrimitiveFromPath(Long,int64_t, int64_t);
   // setPrimitiveFromPath(Integer,long, long);
   // Depends on wordsize, see SDOString& variant below.
   void DataObjectImpl::setLong(const char* path, /*long long*/ int64_t l)
   {
      setLong(SDOString(path), l);
   }

   // End of implementations for the setXXX(const char* path, XXX) methods.
   // ---


    // open type support

   const PropertyImpl* DataObjectImpl::defineProperty(const SDOString& propname, 
                                                      const Type& t)
   {
      openProperties.insert(openProperties.end(),
                            PropertyImpl(getType(),
                                         propname,
                                         (TypeImpl&)t,
                                         false,
                                         false,
                                         true));
      DataFactory* df = factory;
      ((DataFactoryImpl*)df)->addOpenProperty(PropertyImpl(getType(),
                                                           propname,
                                                           (TypeImpl&)t,
                                                           false,
                                                           false,
                                                           true));

      return getPropertyImpl(propname);
   }

    void DataObjectImpl::undefineProperty(unsigned int index)
    {
        if (index < openBase) return;
        unsigned int point = index - openBase;
        if (point >= openProperties.size()) return;

        // downgrade all the property settings above this one

        PropertyValueMap::iterator pit;
        for (pit = PropertyValues.begin(); pit != PropertyValues.end();++pit)
        {
            if ((*pit).first > index)
            {
                if (getPropertyImpl((*pit).first)->isMany())
                {
                    DataObjectListImpl* dl = (*pit).second->getListImpl();
                    if (dl != 0) dl->decrementPindex();
                }
                (*pit).first-=1;
             }
        }

        // then remove this property from the list 

        std::list<PropertyImpl>::iterator it = 
            openProperties.begin();
        for (unsigned int i=0;i<point;i++)++it; /* there must be a better way */

        DataFactory* df = factory;
        ((DataFactoryImpl*)df)->removeOpenProperty((*it).getName());
        
        openProperties.erase(it);
        
        return;
    }

    const PropertyImpl* DataObjectImpl::defineList(const char* propname)
    {
        const Type& t = factory->getType(Type::SDOTypeNamespaceURI, "OpenDataObject");
        openProperties.insert(
            openProperties.end(), PropertyImpl(getType(),propname,
            (TypeImpl&)t, true, false, true));

        DataFactory* df = factory;
        ((DataFactoryImpl*)df)->addOpenProperty(PropertyImpl(getType(),propname,
            (TypeImpl&)t, true, false, true));

        return getPropertyImpl(propname);
    }

    const PropertyImpl* DataObjectImpl::defineSDOValue(const SDOString& propname,
                                                       const SDOValue& sval)
    {
        const Type& t = factory->getType(Type::SDOTypeNamespaceURI,
                                         sval.convertTypeEnumToString());
        return defineProperty(propname, t);
    }
    
    const PropertyImpl* DataObjectImpl::defineBoolean(const SDOString& propname)
    {
        const Type& t = factory->getType(Type::SDOTypeNamespaceURI, "Boolean");
        return defineProperty(propname,t);
    }
    
    const PropertyImpl* DataObjectImpl::defineByte(const SDOString& propname)
    {
        const Type& t = factory->getType(Type::SDOTypeNamespaceURI, "Byte");
        return defineProperty(propname,t);
    }

    const PropertyImpl* DataObjectImpl::defineCharacter(const SDOString& propname)
    {
        const Type& t = factory->getType(Type::SDOTypeNamespaceURI, "Character");
        return defineProperty(propname,t);
    }
    
    const PropertyImpl* DataObjectImpl::defineString(const SDOString& propname)
    {
        const Type& t = factory->getType(Type::SDOTypeNamespaceURI, "String");
        return defineProperty(propname,t);
    }
    
    const PropertyImpl* DataObjectImpl::defineBytes(const SDOString& propname)
    {
        const Type& t = factory->getType(Type::SDOTypeNamespaceURI, "Bytes");
        return defineProperty(propname,t);
    }
    
    const PropertyImpl* DataObjectImpl::defineShort(const SDOString& propname)
    {
        const Type& t = factory->getType(Type::SDOTypeNamespaceURI, "Short");
        return defineProperty(propname,t);
    }
    
    const PropertyImpl* DataObjectImpl::defineInteger(const SDOString& propname)
    {
        const Type& t = factory->getType(Type::SDOTypeNamespaceURI, "Integer");
        return defineProperty(propname,t);
    }
    
    const PropertyImpl* DataObjectImpl::defineLong(const SDOString& propname)
    {
        const Type& t = factory->getType(Type::SDOTypeNamespaceURI, "Long");
        return defineProperty(propname,t);
    }
    
    const PropertyImpl* DataObjectImpl::defineFloat(const SDOString& propname)
    {
        const Type& t = factory->getType(Type::SDOTypeNamespaceURI, "Float");
        return defineProperty(propname,t);
    }
    
    const PropertyImpl* DataObjectImpl::defineDouble(const SDOString& propname)
    {
        const Type& t = factory->getType(Type::SDOTypeNamespaceURI, "Double");
        return defineProperty(propname,t);
    }
    
    const PropertyImpl* DataObjectImpl::defineDate(const SDOString& propname)
    {
        const Type& t = factory->getType(Type::SDOTypeNamespaceURI, "Date");
        return defineProperty(propname,t);
    }
    
    const PropertyImpl* DataObjectImpl::defineCString(const SDOString& propname)
    {
        const Type& t = factory->getType(Type::SDOTypeNamespaceURI, "String");
        return defineProperty(propname,t);
    }
    
    const PropertyImpl* DataObjectImpl::defineDataObject(const SDOString& propname,
        const Type& t)
    {
        return defineProperty(propname,t);
    }
    
    const PropertyImpl* DataObjectImpl::defineDataObject(const char* propname,
        const char* typeURI, const char* typeName)
    {
        const Type& t = factory->getType(typeURI, typeName);
        return defineProperty(propname,t);
    }

    void DataObjectImpl::setCString (unsigned int propertyIndex, const char* value)
    {
        setCString(propertyIndex, SDOString(value));
    }

    const char* DataObjectImpl::getCString (const char* path)
    {
       return getCString(SDOString(path));
    }

    void DataObjectImpl::setCString(const char* path, const char* value)
    {
        setCString(SDOString(path), SDOString(value));
    }

    void DataObjectImpl::setCString(const Property& property, const char* value)
    {
        setCString(property, SDOString(value));
    }

    // null support

    bool DataObjectImpl::isNull(const unsigned int propertyIndex)
    {
        validateIndex(propertyIndex);
        if ((getProperty(propertyIndex).isMany()))
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
        return isNull(SDOString(path));
    }

   bool DataObjectImpl::isNull(const SDOString& path)
   {
      DataObjectImpl *d = 0;
      SDOString spath;
      SDOString prop;
      // char* spath = 0;
      // char* prop = 0;
      try {
         DataObjectImpl::stripPath(path, spath);
         prop = findPropertyContainer(spath, &d);
         if (d != 0) {
            if (prop.length() == 0) {
               return d->isNull();
            }
            else {
               const Property& p = d->getProperty(prop);
               return d->isNull(p);
            }
         }
         return false;
      }
      catch (SDORuntimeException e) {
         SDO_RETHROW_EXCEPTION("isNull",e);
      }
   }

   void DataObjectImpl::setNull(const unsigned int propertyIndex)
   {
      validateIndex(propertyIndex);
      const Property& property = getProperty(propertyIndex);
      if (property.isMany())
      {
         string msg("Setting a list to null is not supported:");
         msg += property.getName();
         SDO_THROW_EXCEPTION("setNull",
                             SDOUnsupportedOperationException,
                             msg.c_str());
      }

      ASSERT_WRITABLE(property, setNull);

      PropertyValueMap::iterator i;
      for (i = PropertyValues.begin(); i != PropertyValues.end(); ++i)
      {
         if ((*i).first == propertyIndex)
         {
            logChange(propertyIndex);
            (*i).second->setNull();
            return;
         }
      }
      // The property was not set yet...
      logChange(propertyIndex);
      DataFactory* df = getDataFactory();
      DataObjectImpl* b =
         new DataObjectImpl(df, getProperty(propertyIndex).getType());
      b->setContainer(this);
      PropertyValues.push_back(rdo(propertyIndex,b));
      b->setNull();
   }

    void DataObjectImpl::setNull(const Property& property)
    {
        setNull(getPropertyIndexInternal(property));
    }

    void DataObjectImpl::setNull(const char* path)
    {
        setNull(SDOString(path));
    }

   void DataObjectImpl::setNull(const SDOString& path)
   {
      DataObjectImpl *d = 0;
      SDOString spath;
      SDOString prop;
      size_t pc;

      try {
         DataObjectImpl::stripPath(path, spath);
         prop = findPropertyContainer(spath, &d);
         if (d != 0) {
            if (prop.length() == 0) {
               try {
                  DataObjectImpl* cont = d->getContainerImpl();
                  if (cont != 0)
                  {
                     pc = path.rfind('/'); // Find the last '/' in the path
                     if (pc != string::npos)
                        pc++;   // pc is the index of the first character following the /
                  }
                  const Property& pcont = cont->getProperty(path.substr(pc));
                  ASSERT_WRITABLE(pcont, setNull)
                  cont->logChange(pcont);
               }
               catch (SDORuntimeException&)
               {
               }
               d->setNull();
            }
            else {
               const PropertyImpl* p = d->getPropertyImpl(prop);
               if (p == 0)
               {
                  if(d->getType().isOpenType())
                  {
                     string msg("Cannot set unassigned open property to null:");
                     msg += prop;
                     SDO_THROW_EXCEPTION("setNull", SDOUnsupportedOperationException,
                                         msg.c_str());
                  }
                  else
                  {
                     string msg("Property Not Found:");
                     msg += prop;
                     SDO_THROW_EXCEPTION("setNull", SDOPropertyNotFoundException,
                                         msg.c_str());
                  }
               }
               ASSERT_SETTABLE(p, Null)
               d->setNull((Property&)*p);
               return;
            }
         }
         return;
      }
      catch (SDORuntimeException e) {
         SDO_RETHROW_EXCEPTION("setNull",e);
      }

   }

    // getters and setters for a List data object 

    DataObjectList& DataObjectImpl::getList(const char* path)
    {
       // Can path really be a null pointer?
       if (path == 0)
       {
          return(getList(SDOString()));
       }
       else
       {
          return(getList(SDOString(path)));
       }
    }

   DataObjectList& DataObjectImpl::getList(const SDOString& path)
   {
      DataObjectImpl *d;
      SDOString spath;

      DataObjectImpl::stripPath(path, spath);
      SDOString prop = findPropertyContainer(spath, &d);

      if (d != 0) {
         if (prop.length() == 0) {
            return d->getList();
         }
         else {
            const PropertyImpl* p = d->getPropertyImpl(prop);
            if (p == 0 && d->getType().isOpenType())
            {
               p = d->defineList(prop.c_str());
            }
            if (p != 0)
            {
               return d->getList((Property&)*p);
            }
         }
      }

      string msg("Invalid path:");
      msg += path;
      SDO_THROW_EXCEPTION("getList",SDOPathNotFoundException, msg.c_str());
   }


    DataObjectList& DataObjectImpl::getList(unsigned int propIndex)
    {
        return getList(getProperty(propIndex));
    }

    DataObjectList& DataObjectImpl::getList(const Property& p)
    {
        if (!p.isMany())
        {
            PropertyImpl* pi = (PropertyImpl*)&p;
            if (!pi->getTypeImpl()->isFromList())
            {
                string msg("Get list not available on single valued property:");
                msg += p.getName();
                SDO_THROW_EXCEPTION("getList", SDOUnsupportedOperationException,
                msg.c_str());
            }
        }

        int propIndex = getPropertyIndexInternal(p);
        DataObjectImpl* d = getDataObjectImpl(propIndex);
        if (d == 0) {
            // There is no list yet, so we need to create an 
            // empty data object to hold the list
            DataFactory* df = getDataFactory();
            d = new DataObjectImpl(df, df->getType(Type::SDOTypeNamespaceURI,"DataObject"));
            PropertyValues.push_back(rdo(propIndex, d));
            d->setContainer(this);
            
            DataObjectListImpl* list = new DataObjectListImpl(df,this,
                propIndex,p.getType().getURI(),p.getType().getName());
            d->setList(list); 

        }
        return d->getList();
    }    



    DataObjectList& DataObjectImpl::getList()
    {
        if (getTypeImpl().isFromList())
        {
            return getList("values");
        }
        return *listValue;
    }

    DataObjectListImpl* DataObjectImpl::getListImpl()
    {
        if (getTypeImpl().isFromList())
        {
            DataObjectList& dl = getList("values");
            return (DataObjectListImpl*)&dl;
        }
        return listValue;
    }



  /////////////////////////////////////////////////////////////////////////////
  // Utilities 
  /////////////////////////////////////////////////////////////////////////////
  

    // get an index, or throw if the prop is not part of this DO 

    unsigned int DataObjectImpl::getPropertyIndex(const Property& p)
    {
       const std::list<PropertyImpl*> props = getType().getPropertyListReference();

       unsigned int i = 0;
       for (std::list<PropertyImpl*>::const_iterator j = props.begin();
            j != props.end();
            j++, i++)
        {
            if (!strcmp((*j)->getName(), p.getName()))
            {
                return i;
            }
        }
        if (getType().isOpenType())
        {
            std::list<PropertyImpl>::iterator j;
            int count = 0;
            for (j = openProperties.begin() ; 
                 j != openProperties.end() ; ++j)
             {
                if (!strcmp((*j).getName(),p.getName()))
                {
                    return count+openBase;
                }
                count++;
            }
        }
        string msg("Cannot find property:");
        msg += p.getName();
        SDO_THROW_EXCEPTION("getPropertyIndex", SDOPropertyNotFoundException,
            msg.c_str());
    }

   /**
     * This method is used internally to find the index of a 
     * property. If differs from the public getPropertyIndex method
     * in that if the type of the containing object is open a new
     * index is created. In the public version and error is thrown
     */
    unsigned int DataObjectImpl::getPropertyIndexInternal(const Property& p)
    {
        unsigned int index;

        try 
        {
            index = getPropertyIndex(p);
        }
        catch ( SDOPropertyNotFoundException e )
        {
            // this could mean that this data object has an open 
            // type. getPropertyIndex fails in this case because it
            // tries to access the index of the property 
            // and it doesn't exist because it hasn't been created yet. 
            // This new method is used where properties are being set
            // based on existing property objects. This is likely to 
            // occur when a data object is being copied. In this case
            // we want properties that are open to be copied also 
            // so we need to create the property and provide the index
            if ( this->getType().isOpenType() )
            {
                const Property *prop = NULL;
                
                // need to treat many valued properties specially
                // because the property is a list rather than 
                // a single value
                if ( p.isMany() )
                {
                    prop = defineList(p.getName());                   
                }
                else
                {
                    prop = defineProperty(p.getName(), p.getType());
                }
                
                index = getPropertyIndex(p);
            }
            else
            {
                throw e;
            }
        }

        return index;
    }


    const Property& DataObjectImpl::getProperty(unsigned int index)
    {
        PropertyImpl* pi = getPropertyImpl(index);
        if (pi == 0)
        {
            string msg("Index out of range");
            SDO_THROW_EXCEPTION("getProperty", SDOIndexOutOfRangeException,
            msg.c_str());
        }
        return (Property&)*pi;
    }

    /**
     * See if the property currently exists
     */

    bool DataObjectImpl::hasProperty(const char* name)
    {
        return hasProperty(SDOString(name));
    }

    bool DataObjectImpl::hasProperty(const SDOString& name)
    {
        PropertyImpl* pi = getPropertyImpl(name);
        if (pi == 0) return false;
        return true;
    }


    PropertyImpl* DataObjectImpl::getPropertyImpl(unsigned int index)
    {
       // Cannot use getPropertyListReference because we will return a
       // writeable PropertyImpl.
        PropertyList props = getType().getProperties();  
        if (index < props.size())
        {
            return (PropertyImpl*)&props[index];
        }

        if (getType().isOpenType())
        {
            if (index >= openBase && index - openBase  < openProperties.size())
            {
                std::list<PropertyImpl>::iterator j;
                unsigned int val = 0;
                j = openProperties.begin();
                while (val < index-openBase && j != openProperties.end())
                {
                    val++;
                    j++;
                }
                if (j != openProperties.end()) return &(*j);
            }
        }
        return 0;
    }


    //////////////////////////////////////////////////////////////////////
    // TODO - this is rubbish, but gets us by until XPATH is done
    // trip the path down to characters which I am going to
    // recognise later (a-z, A-Z _ [ ] .)
    //////////////////////////////////////////////////////////////////////

    const char* DataObjectImpl::templateString = 
    " /abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890=[]._#";

    char* DataObjectImpl::stripPath(const char* path)
    {
        int pos = 0;
        char* s = 0;
        if (path == 0) return s;

        s = new char[strlen(path)+1];

        for (unsigned int i=0;i < strlen(path); i++) 
        {
            if (strchr(templateString,path[i]) != 0) {
                s[pos++] = path[i];
            }
        }
        s[pos++] = 0;
        return s;
    }

    void DataObjectImpl::stripPath(const SDOString& path, SDOString& result)
    {
       result.erase();
       result.reserve(path.length());
       
       size_t start = 0;
       size_t position = path.find_first_not_of(templateString);

       while (position != string::npos)
       {
          result.append(path, start, (position - start));
          start = ++position;
          position = path.find_first_not_of(templateString, position);
       }

       result.append(path, start, string::npos);

       return;
    }

    //////////////////////////////////////////////////////////////////////
    // Find a data object or return 0 if not found
    //////////////////////////////////////////////////////////////////////    
    DataObjectImpl* DataObjectImpl::findDataObject(const SDOString& token, long* index)
    {
        // name , name[int], name[x=y] name.int 
        size_t beginbrace = token.find('[');
        size_t dot = token.find('.');
        size_t breaker = 0;
                
        if (dot != string::npos)
        {
            if (beginbrace != string::npos)
            {
                breaker = (beginbrace < dot) ? beginbrace : dot;
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
        
        if (breaker == string::npos)
        {
            // its this object, and a property thereof
            *index = -1;
            const Property& p = getProperty(token);
            return getDataObjectImpl(p);
        }
        
        // We did find a breaker character.
        const Property& p = getProperty(SDOString(token, 0, breaker));
        
        breaker++;
        
        size_t endbrace = token.find(']');
        SDOString breakerStr = token.substr(breaker, (endbrace - breaker));
        // Search for the first occurence of an = sign starting at the previously
        // identified "breaker" character and ending at the endbrace just found. We
        // need to make a new SDOString to contain that substring.
        
        size_t eq = breakerStr.find('=');
        
        if (eq == string::npos)
        {
            // There is no "=" sign
            unsigned int val = atoi(breakerStr.c_str());
            DataObjectList& list = getList(p);
            
            // The spec says that depts[1] is the first element, as is depts.0
            if (beginbrace != string::npos) val--;
            
            if (val >=0 && val < list.size())
            {
                DataObject* dob = list[val];
                *index = val;
                return (DataObjectImpl*)dob;
            }
            *index = -1;
            return 0;
        }
        
        // We did find an "=" sign.
        SDOString PropertyName = breakerStr.substr(0, eq);
        // breaker is now propname
        eq++;
        SDOString PropertyValue = breakerStr.substr(eq);
        // eq is now propval
        
        DataObjectList& list = getList(p);
        for (unsigned int li = 0 ; li < list.size() ; ++li)
        {
            // TODO  comparison for double not ok
            
            const Type & t = list[li]->getType();
            const Property& p  = list[li]->getProperty(PropertyName);
            int ok = 0;
            
            switch (p.getTypeEnum())
            {
            case Type::BooleanType:
                {
                    // getCString will return "true" or "false"
                    if (!strcmp(PropertyValue.c_str(), list[li]->getCString(p))) ok = 1;
                }
                break;
                
            case  Type::ByteType:
                {
                    char cc = PropertyValue[0];
                    // getByte return a char
                    if (cc == list[li]->getByte(p)) ok = 1;
                }
                break;
                
            case  Type::CharacterType:
                {
                    wchar_t wc = *((wchar_t*) PropertyValue.c_str());
                    // wchar_t wc =  (wchar_t)((wchar_t*)eq)[0];
                    // TODO - this is not a very accesible way of storing a wchar
                    if (wc == list[li]->getCharacter(p)) ok = 1;
                }
                break;
                
            case  Type::IntegerType:
                {
                    long  ic =  atol(PropertyValue.c_str());
                    if (ic == list[li]->getInteger(p)) ok = 1;
                }
                break;
                
            case  Type::DateType: 
                {
                    long  dc =  atol(PropertyValue.c_str());
                    if (dc == (long)(list[li]->getDate(p).getTime())) ok = 1;
                }
                break;
                
            case  Type::DoubleType:
                {
                    // TODO - double needs a bigger size than float
                    long double  ldc =  (long double)atof(PropertyValue.c_str());
                    if (ldc == list[li]->getDouble(p)) ok = 1;
                }
                break;
                
            case  Type::FloatType:
                {
                    float  fc =  atof(PropertyValue.c_str());
                    if (fc == list[li]->getFloat(p)) ok = 1;
                }
                break;
                
            case  Type::LongType:
                {
#if defined(WIN32)  || defined (_WINDOWS)
                    int64_t lic = (int64_t)_atoi64(PropertyValue.c_str());
#else
                    int64_t lic = (int64_t)strtoll(PropertyValue.c_str(), NULL, 0);
#endif
                    
                    if (lic == list[li]->getLong(p)) ok = 1;
                }
                break;
                
            case  Type::ShortType:
                {
                    short sic = atoi(PropertyValue.c_str());
                    if (sic == list[li]->getShort(p)) ok = 1;
                }
                break;
                
            case  Type::BytesType:
            case  Type::BigDecimalType:
            case  Type::BigIntegerType:
            case  Type::StringType:
            case  Type::UriType:
                {
                    
                    if (!strcmp(PropertyValue.c_str(), list[li]->getCString(p))) ok = 1;
                    // try with quotes too
                    size_t firstquote = PropertyValue.find('"');
                    size_t firstsingle = PropertyValue.find('\'');
                    
                    char searchchar = 0;
                    
                    if (firstsingle == string::npos)
                    {
                        if (firstquote != string::npos)
                        {
                            searchchar = '"';
                        }
                    }
                    else
                    {
                        if (firstquote != string::npos && firstquote < firstsingle)
                        {
                            searchchar = '"';
                        }
                        else
                        {
                            searchchar = '\'';
                            firstquote = firstsingle;
                        }
                    }
                    
                    if (searchchar != 0)
                    {
                        size_t ender = PropertyValue.find(searchchar, firstquote + 1);
                        if (ender != string::npos)
                        {
                            if (!strcmp(PropertyValue.substr(firstquote + 1, ender - (firstquote+1)).c_str(), list[li]->getCString(p)))
                                ok = 1;
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
                DataObject* dob = list[li];
                *index = li;
                return (DataObjectImpl*)dob;
            }
            
        }
        return 0;
    }


    //////////////////////////////////////////////////////////////////////
    // Find a data object and a property name within it.
    //////////////////////////////////////////////////////////////////////
    SDOString DataObjectImpl::findPropertyContainer(const SDOString& path, DataObjectImpl** din)
    {
        // initially check for "#/" which indicates that we need to find the root object first 
        
        if (path.length() <= 2)
        {
            if (path[0] == '#')
            {
                DataObjectImpl* root = this;
                while (root->getContainerImpl() != 0)
                {
                    root = root->getContainerImpl();
                }
                *din = root;
                return SDOString();
            }
        }
        
        if (path[0] == '#' && path[1] == '/')
        {
            DataObjectImpl* root = this;
            while (root->getContainerImpl() != 0)
            {
                root = root->getContainerImpl();
            }
            return root->findPropertyContainer(SDOString(path, 2, string::npos), din);
        }
        
        DataObjectImpl* d;
        size_t slashPos = path.find('/');  // i is the subscript of the found character
        SDOString remaining;
        SDOString token;
        
        if (slashPos != string::npos)      // If we found a slash character
        {
            if (slashPos > 0)              // If there is something before the slash
            {
                token.assign(path, 0, slashPos);
            }
            if ((path.length() - slashPos) > 1) // If there is something after the slash
            {
                remaining.assign(path, slashPos + 1, string::npos);
            }
        }
        else
        {
            remaining = path;
        }
        
        if (token.empty()) 
        {
            if (remaining == "..") 
            {
                /* Its the container itself */
                *din = getContainerImpl();
                return SDOString();
            }
            
            /* Its this data object - property could be empty or
            valid or invalid - user must check */
            *din = this;
            return remaining;
        }
        
        if (token == "..") {
            /* Its derived from the container */
            d = getContainerImpl();
            /* carry on trying to find a property */
            if (d != 0) {
                return d->findPropertyContainer(remaining, din);
            }
            /* Give up - no container */
            *din = 0;
            return SDOString();
        }
        
        /* Try to find a property ....*/
        long l;
        d = findDataObject(token, &l);
        if (d != 0) {
            return d->findPropertyContainer(remaining, din);
        }
        
        /* Give up its not in the tree */
        *din = 0;
        return SDOString();
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
        const std::list<PropertyImpl*> propList = getType().getPropertyListReference();

        for (std::list<PropertyImpl*>::const_iterator i = propList.begin();
             i != propList.end();
             i++)
        {
            theVec.push_back((*i));
        }
        std::list<PropertyImpl>::iterator j;
        for (j = openProperties.begin() ;
             j != openProperties.end() ; ++j)
        {
            theVec.push_back(&(*j));
        }
        return PropertyList(theVec);
    }
  
    void DataObjectImpl::setInstancePropertyType(unsigned int index,
        const Type* t)
    {
        if (index >= openBase && index - openBase  < openProperties.size())
        {
            std::list<PropertyImpl>::iterator j;
            unsigned int count = openBase;
            for (j = openProperties.begin() ;
                 j != openProperties.end() ; ++j)
            {
                if (count == index)
                {
                    openProperties.insert(j,
                        PropertyImpl(getType(),
                        (*j).getName(),
                        (TypeImpl&)*t,
                        (*j).isMany(),
                        (*j).isReadOnly(),
                        (*j).isContainment()));

                    DataFactory* df = factory;
                    ((DataFactoryImpl*)df)->addOpenProperty(
                        PropertyImpl(getType(),
                        (*j).getName(),
                        (TypeImpl&)*t,
                        (*j).isMany(),
                        (*j).isReadOnly(),
                        (*j).isContainment()));

                    openProperties.erase(j);
                    
                    return;
                }
                count++;
            }
        }
        return;
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
        return getSequence(SDOString(path));
    }

    SequencePtr DataObjectImpl::getSequence(const SDOString& path)
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
       // Can path really be a null pointer?
       if (path == 0)
       {
          return(getChangeSummary(SDOString()));
       }
       else
       {
          return(getChangeSummary(SDOString(path)));
       }
    }

    ChangeSummaryPtr DataObjectImpl::getChangeSummary(const SDOString& path)
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
		
        DataObjectImpl* dob = getContainerImpl();
        while (dob != 0) 
		{
            if (dob->getType().isChangeSummaryType())
            {
				return (ChangeSummaryPtr)dob->getSummary();
            }
            dob = dob->getContainerImpl();
        }
        return 0;
    }


    ChangeSummaryImpl* DataObjectImpl::getChangeSummaryImpl()
    {
        if (getType().isChangeSummaryType())
        {
            return localCS;
        }

        DataObjectImpl* dob = getContainerImpl();
        while (dob != 0) 
		{
            if (dob->getType().isChangeSummaryType())
            {
				return dob->getSummary();
            }
            dob = dob->getContainerImpl();
        }
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

    void DataObjectImpl::setDataObject(const char* path, DataObjectPtr value)
    {
        setDataObject(SDOString(path), value, true);
    }
    
    void DataObjectImpl::setDataObject(const char* path, DataObjectPtr value, bool updateSequence)
    {
        setDataObject(SDOString(path), value, updateSequence);
    }

   void DataObjectImpl::setDataObject(const SDOString& path,
                                      DataObjectPtr value)
   {
      setDataObject(path, value, false);
   }
   
   void DataObjectImpl::setDataObject(const SDOString& path,
                                      DataObjectPtr value,
                                      bool updateSequence)
   {
      DataObjectImpl* d;

      SDOString prop = findPropertyContainer(path, &d);
      if (d != 0)
      {
         if (!prop.empty()) {
            const PropertyImpl* p = d->getPropertyImpl(prop);
            if ((p == 0) && (d->getType().isOpenType()))
            {
               if (value != 0)
               {
                  p = d->defineDataObject(prop, value->getType());
               }
            }
            if (p != 0)
            {
               ASSERT_SETTABLE(p, DataObject);
               if (p->isMany())
               {
                  DataObjectList& dol = d->getList((Property&)*p);
                  long idx;
                  DataObjectImpl* dx = d->findDataObject(prop,&idx);
                  // fix this. This is the only place the 2nd parm to findDataObject
                  // is used. Need a better way to do this
                  unsigned int index = (unsigned int) idx;
                  if (index >= 0)
                  {
                     if(index < dol.size())
                     {
                        dol.setDataObject(index, value);
                     }
                     else 
                     {
                        dol.append(value);
                     }
                     return;
                  }
                  string msg("Set of data object on many valued item");
                  msg += path;
                  SDO_THROW_EXCEPTION("setDataObject",
                                      SDOUnsupportedOperationException,
                                      msg.c_str());
               }
               else 
               {
                  d->setDataObject((Property&) *p, value, updateSequence);
                  return;
               }
            }
         }
      }
        
      string msg("Path not valid:");
      msg += path;
      SDO_THROW_EXCEPTION("setDataObject", SDOPathNotFoundException,
                          msg.c_str());
   }

    void DataObjectImpl::validateIndex(unsigned int index)
    {
        const std::list<PropertyImpl*> pl = getType().getPropertyListReference();

        if (index >= pl.size()) {

            // open type support
            if (getType().isOpenType())
            {
                if (index < openBase + openProperties.size())
                {
                    return;
                }
            }

            string msg("Index of property out of range:");
            msg += index;
            SDO_THROW_EXCEPTION("Index Validation", SDOIndexOutOfRangeException,
                msg.c_str());
        }
    }


    void DataObjectImpl::checkFactory(DataObjectPtr dob,
        unsigned int propertyIndex)
    {
        
        DataObjectImpl* d = (DataObjectImpl*)(DataObject*)dob;

        if (d->getDataFactory() == getDataFactory()) return;

        if (d->getContainer() != 0)
        {
            string msg("Insertion of object from another factory is only allowed if the parent is null: ");
            const Type& t = d->getType();
            msg += t.getURI();
            msg += "#";
            msg += t.getName();
            msg += " into property ";
            msg += getProperty(propertyIndex).getName();
            msg += " of ";
            msg += getType().getURI();
            msg += "#";
            msg += getType().getName();
            SDO_THROW_EXCEPTION("checkFactory", SDOInvalidConversionException,
                msg.c_str());
        }

    }


    void DataObjectImpl::checkType(    const Property& prop,
                                    const Type& objectType)
    {
        const Type& propType = prop.getType();
        if (propType.equals(objectType)) return;

        DataFactory* df = (DataFactory*)factory;

        const TypeImpl* ti = ((DataFactoryImpl*)df)->findTypeImpl
            (objectType.getURI(),objectType.getName());
        if (ti != 0)
        {
            do 
            {
                ti = (const TypeImpl*)ti->getBaseType();
                if (ti == 0) break;
                if (propType.equals(*ti)) return;
            } while (ti != 0);

            // allow types of any substitutes
            const PropertyImpl* pi = 
                getPropertyImpl(getPropertyIndex(prop));
            if (pi != 0) 
            {
                unsigned int subcount = pi->getSubstitutionCount();
                for (unsigned int i=0;i<subcount;i++)
                {
                    const Type* tsub = pi->getSubstitutionType(i);
                    if (tsub != 0 && tsub->equals(objectType)) return;
                }
            }
        }

        // no match..
        string msg("Insertion of object of incompatible type ");
        msg += objectType.getURI();
        msg += "#";
        msg += objectType.getName();
        msg += " into property of type ";
        msg += propType.getURI();
        msg += "#";
        msg += propType.getName();
        SDO_THROW_EXCEPTION("TypeCheck", SDOInvalidConversionException,
            msg.c_str());
    }

    void DataObjectImpl::setDataObject(unsigned int propertyIndex, DataObjectPtr value)
    {
        setDataObject(getProperty(propertyIndex), value, true);
    }

    void DataObjectImpl::setDataObject(unsigned int propertyIndex, DataObjectPtr value, bool updateSequence)
    {
        setDataObject(getProperty(propertyIndex), value, updateSequence);
    }

   void DataObjectImpl::setDataObject(const Property& prop, DataObjectPtr value)
   {
      setDataObject(prop, value, false);
   }

void DataObjectImpl::setDataObject(const Property& prop,
                                   DataObjectPtr value,
                                   bool updateSequence)
{
   unsigned int propertyIndex = getPropertyIndexInternal(prop);

   if (value != 0)
   {
      checkFactory(value, propertyIndex);
      checkType(prop, value->getType());
   }

   validateIndex(propertyIndex);

   if (prop.isReference() && (value != 0))
   {
      // just need to make sure that the object is already part of our tree.
      DataObjectImpl* r1 = this;
      while (r1->getContainerImpl() != 0)
      {
         r1 = r1->getContainerImpl();
      }
      DataObjectImpl* r2 = (DataObjectImpl*) (DataObject*) value;
      while (r2->getContainerImpl() != 0)
      {
         r2 = r2->getContainerImpl();
      }
      if (r1 != r2)
      {
         string msg("Set of a reference to an object outside the graph");
         SDO_THROW_EXCEPTION("setDataObject",
                             SDOUnsupportedOperationException,
                             msg.c_str());
      }
   }

   if ((prop.isMany()))
   {
      string msg("Set operation on a many valued property:");
      msg += prop.getName();
      SDO_THROW_EXCEPTION("setDataObject",
                          SDOUnsupportedOperationException,
                          msg.c_str());
   }

   ASSERT_WRITABLE(prop, setDataObject);

   if (value == 0) 
   {
      // The new data object value is actually a null pointer.
      PropertyValueMap::iterator j;
      // Scan the property value map looking for this property.
      for (j = PropertyValues.begin(); j != PropertyValues.end(); ++j)
      {
         if ((*j).first == propertyIndex)
         {
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
            // We have just changed the value of this property, therefore
            // if this is a sequenced data object, then we must update the
            // sequence so that the new setting appears at the end (and
            // the existing entry is removed).
            if ((getType().isSequencedType()) && updateSequence)
            {
               SequenceImpl* mySequence = getSequenceImpl();
               mySequence->removeAll(prop);
               mySequence->push(prop, 0);
            }

            return;
         }
      }
      // The property does not currently have a value.
      logChange(prop);
      PropertyValues.push_back(rdo(propertyIndex, (DataObjectImpl*) 0));
      // If this is a sequenced data object then update the
      // sequence. We already know that a) the property was not previously
      // set so it can't be in the sequence currently and b) it is not a
      // multi-valued property.
      if ((getType().isSequencedType()) && updateSequence)
      {
         getSequenceImpl()->push(prop, 0);
      }
      return;
   }

   DataObject* dob = value;
   PropertyValueMap::iterator i;
   for (i = PropertyValues.begin(); i != PropertyValues.end(); ++i)
   {
      if ((*i).first == propertyIndex)
      {
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

         (*i).second = RefCountingPointer<DataObjectImpl>((DataObjectImpl*) dob);

         if (prop.isReference())
         {
            ((DataObjectImpl*) dob)->setReference(this, prop);
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
      // log creation before putting into property values.
      // also log change - not done by logCreation
      logCreation((DataObjectImpl*)dob, this, prop);
   }

   logChange(prop);

   PropertyValues.push_back(rdo(propertyIndex, (DataObjectImpl*) dob));
   // If this is a sequenced data object then update the
   // sequence. We already know that a) the property is not
   // in the sequence currently and b) it is not a
   // multi-valued property.
   if ((getType().isSequencedType()) && updateSequence)
   {
      getSequenceImpl()->push(prop, 0);
   }

   return;
}

    bool DataObjectImpl::isValid(const char* path)
    {
       // Can path really be a null pointer?
       if (path == 0)
       {
          return(isValid(SDOString()));
       }
       else
       {
          return(isValid(SDOString(path)));
       }

    }

    bool DataObjectImpl::isValid(const SDOString& path)
    {
        DataObjectImpl* d;
        SDOString prop = findPropertyContainer(path, &d);
        if (d != 0) {
            if (!prop.empty()) {
                const Property& p = d->getProperty(prop);
                return d->isValid(p);
            }
        }
        string msg("Invalid path:");
        msg += path;
        SDO_THROW_EXCEPTION("isSet" ,SDOPathNotFoundException,
            msg.c_str());
    }
   
    // Returns whether a property of either this object or an object reachable 
    // from it, as identified by the specified path,
    // is considered to be set.
    // @param path the path to a valid Object* and property.

    bool DataObjectImpl::isSet(const char* path)
    {
       // Can path really be a null pointer?
       if (path == 0)
       {
          return(isSet(SDOString()));
       }
       else
       {
          return(isSet(SDOString(path)));
       }
    }
    
    bool DataObjectImpl::isSet(const SDOString& path)
    {
        DataObjectImpl* d;
        SDOString prop = findPropertyContainer(path, &d);
        if (d != 0) {
            if (!prop.empty()) {
                const Property& p = d->getProperty(prop);
                return d->isSet(p);
            }
        }
        string msg("Invalid path:");
        msg += path;
        SDO_THROW_EXCEPTION("isSet" ,SDOPathNotFoundException,
            msg.c_str());
    }

    bool DataObjectImpl::isValid(unsigned int propertyIndex)
    {
        return isValid(getProperty(propertyIndex));
    }

    bool DataObjectImpl::isValid(const Property& p)
    {
        if (p.isDefaulted()) return true;
        if (isSet(p))return true;
        return false;
    }

    bool DataObjectImpl::isSet(unsigned int propertyIndex)
    {
        return isSet(getProperty(propertyIndex), propertyIndex);
    }

    bool DataObjectImpl::isSet(const Property& property)
    {
        return isSet(property, getPropertyIndex(property));
    }

    bool DataObjectImpl::isSet(const Property& prop, unsigned int propertyIndex)
    {
        PropertyValueMap::iterator i;
        for (i = PropertyValues.begin(); i != PropertyValues.end(); ++i)
        {
            if ((*i).first == propertyIndex) {
                if (prop.isMany())
                {
                    DataObjectImpl* dol = (*i).second;
                    if (dol != 0 && dol->getList().size() == 0)
                    {
                        return false;
                    }
                }
                return true;
            }
        }
        return false;
    }


    // unSets a property of either this Object or an Object reachable from it, 
    // as identified by the specified path.
    // @param path the path to a valid Object and property.
    // @see #unSet(Property)

    void DataObjectImpl::unset(const char* path)
    {
       // Can path really be a null pointer?
       if (path == 0)
       {
          unset(SDOString());
       }
       else
       {
          unset(SDOString(path));
       }
    }
    
   void DataObjectImpl::unset(const SDOString& path)
   {
      DataObjectImpl* d;
      SDOString prop = findPropertyContainer(path, &d);
      if (d != 0)
      {
         if (!prop.empty())
         {
            const Property& p = d->getProperty(prop);
            ASSERT_WRITABLE(p, unset);
            if (p.isMany())
            {
               SDOString subscript;
               size_t beginbrace = prop.find('[');
               if (beginbrace != string::npos)
               {
                  size_t endbrace = prop.find(']', ++beginbrace);
                  if (endbrace != string::npos) {
                     subscript =
                        prop.substr(beginbrace, (endbrace - beginbrace));
                  }
                  unsigned int i = atoi(subscript.c_str());
                  if (i > 0) {
                     i--;
                     DataObjectList& li = d->getList(p);
                     li.remove(i);
                  }
                  return;
               }
               size_t firstdot = prop.find('.');
               if (firstdot != string::npos) {
                  subscript = prop.substr(++firstdot);
                  unsigned int i = atoi(subscript.c_str());
                  DataObjectList& li = d->getList(p);
                  li.remove(i);
                  return;
               }
            }
            d->unset(p);
            return;
         }
      }

      string msg("Invalid path:");
      msg += path;
      SDO_THROW_EXCEPTION("unset",
                          SDOPathNotFoundException,
                          msg.c_str());
   }

    void DataObjectImpl::unset(unsigned int propertyIndex)
    {
        unset(getProperty(propertyIndex));
    }

    void DataObjectImpl::unset(const Property& p)
    {
        ASSERT_WRITABLE(p, unset)

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
                    else
                    {
                        PropertyValues.erase(i);
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
                                PropertyValues.erase(i);
                                dol->logDeletion();
                                logChange(index);
								dol->setContainer(0);
                            }
                        }
                        else
                        {
                        logChange(index);
                        PropertyValues.erase(i);
                        }
                    }
                    else {
                        if (dol) 
                        {
                            dol->unsetReference(this, p);
                        }
                        logChange(index);
                        PropertyValues.erase(i);
                    }
                }
                if (getType().isOpenType() && index >= openBase)
                {
                    if (p.isMany())
                    {
                        PropertyValues.erase(i);
                    }
                    undefineProperty(index);
                }
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
        return getDataObject(SDOString(path));
    }
    
    RefCountingPointer<DataObject> DataObjectImpl::getDataObject(const SDOString& path)
    {
        DataObjectImpl* ptr = getDataObjectImpl(path);
        return RefCountingPointer<DataObject> ((DataObject*)ptr);
    }

    DataObjectImpl* DataObjectImpl::getDataObjectImpl(const SDOString& path)
    {

       DataObjectImpl* d = 0;
       SDOString prop = findPropertyContainer(path, &d);
       if (d != 0)
       {
          if (!prop.empty())
          {
             if (prop.find_first_of("[.") != string::npos)
             {
                /* Its a multi-valued property */
                long l;
                DataObjectImpl* theob = d->findDataObject(prop, &l);
                if (theob == 0)
                {
                   string msg("Get DataObject - index out of range:");
                   msg += path;
                   SDO_THROW_EXCEPTION("getDataObject",
                                       SDOIndexOutOfRangeException,
                                       msg.c_str());
                }
                return theob;
             }
             else 
             {
                if (prop.length() == 0) 
                {
                   return d;
                }
                const Property& p = d->getProperty(prop);
                return d->getDataObjectImpl(p);
             }
          }
          else
          {
             return d;
          }
       }

       string msg("Invalid path:");
       msg += path;
       SDO_THROW_EXCEPTION("getDataObject",
                           SDOPathNotFoundException,
                           msg.c_str());
    }

   RefCountingPointer<DataObject> DataObjectImpl::getDataObject(unsigned int propertyIndex)
   {
      if ((getProperty(propertyIndex).isMany()))
      {
         string msg("get operation on a many valued property:");
         msg += getProperty(propertyIndex).getName();
         SDO_THROW_EXCEPTION("getDataObject",
                             SDOUnsupportedOperationException,
                             msg.c_str());
      }
      DataObjectImpl* ptr = getDataObjectImpl(propertyIndex);

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
            if ((dob == 0) || ((DataObjectImpl*) dob)->isNull())
            {
               return 0;
            }
            return (DataObjectImpl*) dob;
         }
      }
      return 0;
   }


   RefCountingPointer<DataObject> DataObjectImpl::getDataObject(const Property& property)
   {
      DataObjectImpl* ptr = getDataObjectImpl(property);
      return RefCountingPointer<DataObject>((DataObject*) ptr);
   }

   DataObjectImpl* DataObjectImpl::getDataObjectImpl(const Property& property)
   {
      return getDataObjectImpl(getPropertyIndex(property));
   }



   // Returns a new DataObject contained by this Object using the specified property,
   // which must be a containment property.
   // The type of the created Object is the declared type of the specified property.

    RefCountingPointer<DataObject> DataObjectImpl::createDataObject(const SDOString& propertyName)
    {
        // Throws runtime exception for type or property not found 

        const Property& p  = getProperty(propertyName);
        return createDataObject(p);
    }

   // Returns a new DataObject contained by this Object using the specified property,
   // which must be a containment property.
   // The type of the created Object is the declared type of the specified property.

    RefCountingPointer<DataObject> DataObjectImpl::createDataObject(const char* propertyName)
    {
       // Can propertyName really be a null pointer?
       if (propertyName == 0)
       {
          return(createDataObject(SDOString()));
       }
       else
       {
          return(createDataObject(SDOString(propertyName)));
       }
    }

    // Returns a new DataObject contained by this Object using the specified property,
    // which must be a containment property.
    // The type of the created Object is the declared type of the specified property.

    RefCountingPointer<DataObject> DataObjectImpl::createDataObject(unsigned int propertyIndex)
    {
        const Property& p  = getProperty(propertyIndex);
         return createDataObject(p);
    }

    // Returns a new DataObject contained by this Object using the specified property,
    // which must be a containment property.
    // The type of the created Object is the declared type of the specified property.
    
    RefCountingPointer<DataObject> DataObjectImpl::createDataObject(const Property& property)
    {
        const Type& tp = property.getType();
        return createDataObject(property,tp.getURI(), tp.getName());
    }


    // Returns a new DataObject contained by this Object using the specified property,
    // which must be a containment property.
    // The type of the created Object is the declared type of the specified property.

    RefCountingPointer<DataObject> DataObjectImpl::createDataObject(const Property& property, const char* namespaceURI, 
                                       const char* typeName)
    {
        if (!property.isContainment())
        {
            string msg("Create data object on non-containment property:");
            msg += property.getName();
            SDO_THROW_EXCEPTION("createDataObject", SDOUnsupportedOperationException,
            msg.c_str());
        }

        DataFactory* df = getDataFactory();
        if (property.isMany()) {
            /* add to the list */
            RefCountingPointer<DataObject> ptr = df->create(namespaceURI, typeName);
            DataObject* dob = ptr;
            ((DataObjectImpl*)dob)->setContainer(this);
            
            // log creation before adding to list - the change must record the old state 
            // of the list
            logCreation(((DataObjectImpl*)dob), this, property);
            //logChange(property);

            DataObjectImpl* theDO = getDataObjectImpl(property);
            if ( theDO == 0) { /* No value set yet */
                unsigned int ind = getPropertyIndex(property);
                RefCountingPointer<DataObject> listptr = 
                    df->create(Type::SDOTypeNamespaceURI,"DataObject");

                DataObject* doptr = listptr;

                PropertyValues.push_back(rdo(ind, (DataObjectImpl*) doptr));

                ((DataObjectImpl*)doptr)->setContainer(this);

                DataObjectListImpl* list = new DataObjectListImpl(df,
                    this, ind, namespaceURI,typeName);

                ((DataObjectImpl*)doptr)->setList(list);
                // the append will log a change to the property.
                list->append(ptr);

                // now done by list append
                //if (getType().isSequencedType())
                //{
                //    SequenceImpl* sq = getSequenceImpl();
                //    sq->push(property,0);
                //}
            }
            else 
            {
                DataObjectList& list =    theDO->getList();
                // the append will log a change to the property, and update the 
                // sequence
                list.append(ptr);
                //if (getType().isSequencedType())
                //{
                //    SequenceImpl* sq = getSequenceImpl();
                //    sq->push(property,list.size()-1);
                //}

            }
            return ptr;

        }
        else {
            unset(property);
            DataObjectImpl* ditem = 
              new DataObjectImpl(df, df->getType(namespaceURI, typeName));
            ditem->setContainer(this);

            // log both creation and change - creations no longer log
            // changes automatically.

            logCreation(ditem, this, property);
            logChange(property);

            PropertyValues.push_back(rdo(getPropertyIndex(property), ditem));

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
            const Property& prop = getProperty((*i).first);
            if (prop.isMany())
            {
                DataObjectList& dol = ((*i).second)->getList();
                for (unsigned int j=0;j< dol.size(); j++)
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
    // or 0 if there is no container.

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
            if (getProperty((*i).first).isReference()) continue;
            if (getProperty((*i).first).isMany())
            {
                DataObjectList& dl = ((*i).second)->getList();
                for (unsigned int j = 0 ; j < dl.size(); j++)
                {
                    if (dl[j] == ob)
                    {
                        return &(getProperty((*i).first));
                    }
                }
            }
            else 
            {
                if ((*i).second == ob) 
                {
                    return &(getProperty((*i).first));
                }
            }
        }
        return 0; // this can happen if the object has been detached 

        //string msg("Object cannot find its containing property");
        //SDO_THROW_EXCEPTION("FindInProperties" ,SDOPropertyNotFoundException,
        //    msg.c_str());
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
          return (const Type&)(*ObjectType);
    }

    const Type::Types DataObjectImpl::getTypeEnum()
    {
           return ObjectType->getTypeEnum();
    }

    const TypeImpl& DataObjectImpl::getTypeImpl()
    {
          return (const TypeImpl&)*ObjectType;
    }


    // open type support

    const Property& DataObjectImpl::getProperty(const char* prop)
    {
        return getProperty(SDOString(prop));
    }

    const Property& DataObjectImpl::getProperty(const SDOString& prop)
    {
        PropertyImpl* pi = getPropertyImpl(prop);
        if (pi == 0)
        {
            string msg("Cannot find property:");
            msg += prop;
            SDO_THROW_EXCEPTION("getProperty", SDOPropertyNotFoundException,
                msg.c_str());
            
        }
        return (Property&)*pi;
    }

    PropertyImpl* DataObjectImpl::getPropertyImpl(const SDOString& prop)
    {
        PropertyImpl* pi = getTypeImpl().getPropertyImpl(prop);
        if (pi != 0) return pi;
        
        if (getType().isOpenType())
        {
            std::list<PropertyImpl>::iterator j;
            for (j=openProperties.begin(); 
            j != openProperties.end(); ++j)
            {
                if (!strcmp((*j).getName(), prop.c_str()))
                {
                    return (PropertyImpl*)&(*j);
                }
            }
        }
        return 0;
    }

    DataFactory* DataObjectImpl::getDataFactory()
    {
        return factory;
    }

    void DataObjectImpl::setDataFactory(DataFactory* df)
    {
        ObjectType = (TypeImpl*)&(df->getType(ObjectType->getURI(),
                        ObjectType->getName()));
        factory = df;
    }

    ///////////////////////////////////////////////////////////////////////////
    // These finally are the setters/getters for primitives given
    // that the data object is a primitive type.
    ///////////////////////////////////////////////////////////////////////////
    

   bool DataObjectImpl::getBoolean()
   {
      return getTypeImpl().convertToBoolean(sdoValue);
   }

   char DataObjectImpl::getByte()
   {
      return getTypeImpl().convertToByte(sdoValue);
   }

    wchar_t DataObjectImpl::getCharacter()
    {
       return getTypeImpl().convertToCharacter(sdoValue);
    }

    long DataObjectImpl::getInteger() 
    {
       return getTypeImpl().convertToInteger(sdoValue);
    }

    long double DataObjectImpl::getDouble()
    {
       return getTypeImpl().convertToDouble(sdoValue);
    }

    float DataObjectImpl::getFloat()
    {
       return getTypeImpl().convertToFloat(sdoValue);
    }

    int64_t DataObjectImpl::getLong()
    {
       return getTypeImpl().convertToLong(sdoValue);
    }

    short DataObjectImpl::getShort()
    {
       return getTypeImpl().convertToShort(sdoValue);
    }

   unsigned int DataObjectImpl::getString(wchar_t* outptr, unsigned int max)
   {
      return getTypeImpl().convertToString(sdoValue, outptr, max);
   }

    unsigned int DataObjectImpl::getBytes( char* outptr, unsigned int max)
    {
       return getTypeImpl().convertToBytes(sdoValue, outptr, max);
    }

    const char* DataObjectImpl::getCString()
    {
       return getTypeImpl().convertToCString(sdoValue);
    }

    const SDODate DataObjectImpl::getDate()
    {
       return getTypeImpl().convertToDate(sdoValue);
    }

    DataObjectImpl* DataObjectImpl::getDataObject()
    {
       // If the sdoValue is unset, then there is no primitive value.
       // If doValue is non-null then that is the data object value.
       switch (getTypeImpl().getTypeEnum())
       {
          case Type::OtherTypes:
          case Type::DataObjectType:
             return doValue;

          case Type::BooleanType:
          case Type::ByteType:
          case Type::CharacterType:
          case Type::IntegerType: 
          case Type::ShortType:
          case Type::DoubleType:
          case Type::FloatType:
          case Type::LongType:
          case Type::DateType:
          case Type::BigDecimalType: 
          case Type::BigIntegerType: 
          case Type::StringType:    
          case Type::BytesType:
          case Type::UriType:
          default:
          {
             string msg("Cannot get Data Object from object of type:");
             msg += getTypeImpl().getName();
             SDO_THROW_EXCEPTION("DataObjectImpl::getDataObject",
                                 SDOInvalidConversionException,
                                 msg.c_str());
             break;
          }   
       }
       return 0;
    }

   void DataObjectImpl::setBoolean(bool invalue)
   {
      switch(getTypeEnum())
      {

         case Type::BooleanType: 
         case Type::ByteType:
         case Type::CharacterType:
         case Type::IntegerType: 
         case Type::ShortType:
         case Type::LongType:
         case Type::BigDecimalType: 
         case Type::BigIntegerType: 
         case Type::StringType:    
         case Type::UriType:
         case Type::BytesType:
            sdoValue = SDOValue(invalue);
            break;

         case Type::DoubleType:    
         case Type::FloatType:    
         case Type::DateType:
         case Type::OtherTypes:
         case Type::DataObjectType: 
         case Type::ChangeSummaryType:
         default:
         {
            string msg("Cannot set Boolean on object of type:");
            msg += getTypeImpl().getName();
            SDO_THROW_EXCEPTION("setBoolean" ,
                                SDOInvalidConversionException,
                                msg.c_str());
            break;
         }
      }
      return;
   }


   void DataObjectImpl::setByte(char invalue)
   {
      switch (getTypeEnum())
      {
         case Type::BooleanType:    
         case Type::ByteType:
         case Type::CharacterType: 
         case Type::IntegerType: 
         case Type::ShortType:
         case Type::DoubleType:
         case Type::FloatType:    
         case Type::LongType:      
         case Type::DateType:
         case Type::BigDecimalType: 
         case Type::BigIntegerType: 
         case Type::StringType:    
         case Type::UriType:
         case Type::BytesType:
            sdoValue = SDOValue(invalue);
            break;

         case Type::OtherTypes:
         case Type::DataObjectType: 
         case Type::ChangeSummaryType:
         default:
         {
            string msg("Cannot set Byte on object of type:");
            msg += getTypeImpl().getName();
            SDO_THROW_EXCEPTION("DataObjectImpl::setByte" ,
                                SDOInvalidConversionException,
                                msg.c_str());
            break;
         }
      }
      return;
   }


   void DataObjectImpl::setCharacter(wchar_t invalue)
   {
      switch (getTypeEnum())
      {
         case Type::BooleanType:    
         case Type::ByteType:
         case Type::CharacterType: 
         case Type::IntegerType: 
         case Type::ShortType:
         case Type::DoubleType:
         case Type::FloatType:    
         case Type::LongType:      
         case Type::DateType:
         case Type::BigDecimalType: 
         case Type::BigIntegerType: 
         case Type::StringType:    
         case Type::UriType:
         case Type::BytesType:
            sdoValue = SDOValue(invalue);
            break;

         case Type::OtherTypes:
         case Type::DataObjectType: 
         case Type::ChangeSummaryType:
         default:
         {
            string msg("Cannot set Character on object of type:");
            msg += getTypeImpl().getName();
            SDO_THROW_EXCEPTION("DataObjectImpl::setCharacter" ,
                                SDOInvalidConversionException,
                                msg.c_str());
            break;
         }
      }
      return;
   }

    void DataObjectImpl::setString(const wchar_t* invalue, unsigned int len)
    {
       switch (getTypeEnum())
       {
          case Type::BigDecimalType:
          case Type::BigIntegerType:
          case Type::UriType:
          case Type::StringType:
          case Type::BytesType:
          case Type::BooleanType:    
          case Type::CharacterType: 
          case Type::ByteType:
          case Type::ShortType:
          case Type::IntegerType:
          case Type::LongType:
             sdoValue = SDOValue(invalue, len);
             break;

          case Type::DoubleType:    
          case Type::FloatType:    
          case Type::DateType:
          case Type::OtherTypes:
          case Type::DataObjectType: 
          case Type::ChangeSummaryType:
          default:
          {
             string msg("Cannot set String on object of type:");
             msg += getTypeImpl().getName();
             SDO_THROW_EXCEPTION("DataObjectImpl::setString" ,
                                 SDOInvalidConversionException,
                                 msg.c_str());
             break;
          }
       }
       return;
    }

   void DataObjectImpl::setBytes(const char* invalue, unsigned int len)
   {
      switch (getTypeEnum())
      {
          case Type::BytesType:
          case Type::BigDecimalType: 
          case Type::BigIntegerType: 
          case Type::UriType:
          case Type::StringType:
          case Type::BooleanType:    
          case Type::ByteType:
          case Type::CharacterType: 
          case Type::IntegerType: 
          case Type::ShortType:
          case Type::LongType:
             sdoValue = SDOValue(invalue, len);
             break;

          case Type::DoubleType:
          case Type::FloatType:    
          case Type::DateType:
          case Type::OtherTypes:
          case Type::DataObjectType: 
          case Type::ChangeSummaryType:
          default:
          {
             string msg("Cannot set Bytes on object of type:");
             msg += getTypeImpl().getName();
             SDO_THROW_EXCEPTION("DataObjectImpl::setBytes" ,
                                 SDOInvalidConversionException,
                                 msg.c_str());
             return;
          }
       }
       return;
    }

   void DataObjectImpl::setInteger(long invalue) 
   {
      switch (getTypeEnum())
      {
         case Type::BooleanType:    
         case Type::ByteType:
         case Type::CharacterType: 
         case Type::IntegerType: 
         case Type::ShortType:
         case Type::DoubleType:
         case Type::FloatType:    
         case Type::LongType:      
         case Type::DateType:
         case Type::BigDecimalType: 
         case Type::BigIntegerType: 
         case Type::StringType:    
         case Type::UriType:
         case Type::BytesType:
            sdoValue = SDOValue(invalue);
            break;
        
         case Type::OtherTypes:
         case Type::DataObjectType: 
         case Type::ChangeSummaryType:
         default:
         {
            string msg("Cannot set LongLong on object of type:");
            msg += getTypeImpl().getName();
            SDO_THROW_EXCEPTION("DataObjectImpl::setInteger" ,
                                SDOInvalidConversionException,
                                msg.c_str());
            break;
         }
      }
      return;
   }


   void DataObjectImpl::setDouble(long double invalue)
   {
      switch (getTypeEnum()) 
      {
         case Type::BooleanType:    
         case Type::ByteType:
         case Type::CharacterType: 
         case Type::IntegerType: 
         case Type::ShortType:
         case Type::DoubleType:
         case Type::FloatType:    
         case Type::LongType:      
         case Type::DateType:
            sdoValue = SDOValue(invalue);
            break;

         case Type::BigDecimalType: 
         case Type::BigIntegerType: 
         case Type::StringType:    
         case Type::UriType:
         case Type::BytesType:
         case Type::OtherTypes:
         case Type::DataObjectType: 
         case Type::ChangeSummaryType:
         default:
         {
            string msg("Cannot set Long Double on object of type:");
            msg += getTypeImpl().getName();
            SDO_THROW_EXCEPTION("setDouble" ,
                                SDOInvalidConversionException,
                                msg.c_str());
            break;
         }
      }
      return;
   }

   void DataObjectImpl::setFloat(float invalue)
   {
      switch (getTypeEnum())
      {
         case Type::BooleanType:    
         case Type::ByteType:
         case Type::CharacterType: 
         case Type::IntegerType: 
         case Type::ShortType:
         case Type::DoubleType:
         case Type::FloatType:    
         case Type::LongType:      
         case Type::DateType:
            sdoValue = SDOValue(invalue);
            break;

         case Type::BigDecimalType: 
         case Type::BigIntegerType: 
         case Type::StringType:    
         case Type::UriType:
         case Type::BytesType:
         case Type::OtherTypes:
         case Type::DataObjectType: 
         case Type::ChangeSummaryType:
         default:
         {
            string msg("Cannot set Float on object of type:");
            msg += getTypeImpl().getName();
            SDO_THROW_EXCEPTION("setFloat" ,
                                SDOInvalidConversionException,
                                msg.c_str());
            break;
         }
         break;
      }
      return;
   }


   void DataObjectImpl::setLong(int64_t invalue)
   {
      switch (getTypeEnum())
      {
         case Type::BooleanType:    
         case Type::ByteType:
         case Type::CharacterType: 
         case Type::IntegerType: 
         case Type::ShortType:
         case Type::DoubleType:
         case Type::FloatType:    
         case Type::LongType:      
         case Type::DateType:
         case Type::BigDecimalType: 
         case Type::BigIntegerType: 
         case Type::StringType:    
         case Type::UriType:
         case Type::BytesType:
            sdoValue = SDOValue(invalue);
            break;

         case Type::OtherTypes:
         case Type::DataObjectType: 
         case Type::ChangeSummaryType:
         default:
         {
            string msg("Cannot set Long on object of type:");
            msg += getTypeImpl().getName();
            SDO_THROW_EXCEPTION("DataObjectImpl::setLong" ,
                                SDOInvalidConversionException,
                                msg.c_str());
            break;
         }
      }
      return;
   }


   void DataObjectImpl::setShort(short invalue)
   {
      switch (getTypeEnum())
      {
         case Type::BooleanType:    
         case Type::ByteType:
         case Type::CharacterType: 
         case Type::IntegerType: 
         case Type::ShortType:
         case Type::DoubleType:
         case Type::FloatType:    
         case Type::LongType:      
         case Type::DateType:
         case Type::BigDecimalType: 
         case Type::BigIntegerType: 
         case Type::StringType:    
         case Type::UriType:
         case Type::BytesType:
            sdoValue = SDOValue(invalue);
            break;

         case Type::OtherTypes:
         case Type::DataObjectType: 
         case Type::ChangeSummaryType:
         default:
         {
            string msg("Cannot set short on object of type:");
            msg += getTypeImpl().getName();
            SDO_THROW_EXCEPTION("DataObjectImpl::setShort" ,
                                SDOInvalidConversionException,
                                msg.c_str());
            break;
         }
      }
      return;
   }

    void DataObjectImpl::setCString(const char* invalue)
    {
        setCString(SDOString(invalue));
    }

   void DataObjectImpl::setCString(const SDOString& invalue)
   {
      switch (getTypeEnum())
      {
         case Type::BooleanType:
         case Type::ByteType:
         case Type::CharacterType:
         case Type::IntegerType:
         case Type::ShortType:
         case Type::DoubleType:
         case Type::FloatType:    
         case Type::LongType:   
         case Type::DateType:
         case Type::BigDecimalType: 
         case Type::BigIntegerType: 
         case Type::StringType:    
         case Type::UriType:
         case Type::BytesType:
            sdoValue = SDOValue(invalue);
            break;
       
         case Type::OtherTypes:
         case Type::DataObjectType: 
         case Type::ChangeSummaryType:
         default:
         {
            string msg("Cannot set CString on object of type:");
            msg += getTypeImpl().getName();
            SDO_THROW_EXCEPTION("DataObjectImpl::setCString" ,
                                SDOInvalidConversionException,
                                msg.c_str());
            break;
         }
      }
      return;
   }

    void DataObjectImpl::setDate(const SDODate invalue)
   {
      switch (getTypeEnum())
      {
         case Type::ByteType:
         case Type::CharacterType: 
         case Type::IntegerType: 
         case Type::ShortType:
         case Type::DoubleType:
         case Type::FloatType:    
         case Type::LongType:      
         case Type::DateType:
         case Type::BigDecimalType: 
         case Type::BigIntegerType: 
         case Type::StringType:    
         case Type::UriType:
         case Type::BytesType:
            sdoValue = SDOValue(invalue);
            break;

         case Type::OtherTypes:
         case Type::BooleanType:    
         case Type::DataObjectType: 
         case Type::ChangeSummaryType:
         default:
         {
            string msg("Cannot set LongLong on object of type:");
            msg += getTypeImpl().getName();
            SDO_THROW_EXCEPTION("DataObjectImpl::setDate" ,
                                SDOInvalidConversionException,
                                msg.c_str());
            break;
         }
      }
      return;
   }

   void DataObjectImpl::setDataObject(DataObject* inValue)
   {
      // If the sdoValue is unset, then there is no primitive value.
      // If doValue is non-null then that is the data object value.
      switch (getTypeImpl().getTypeEnum())
      {
         case Type::OtherTypes:
         case Type::DataObjectType:
            doValue = (DataObjectImpl*) inValue;
            break;

         case Type::BooleanType:
         case Type::ByteType:
         case Type::CharacterType:
         case Type::IntegerType: 
         case Type::ShortType:
         case Type::DoubleType:
         case Type::FloatType:
         case Type::LongType:
         case Type::DateType:
         case Type::BigDecimalType: 
         case Type::BigIntegerType: 
         case Type::StringType:    
         case Type::BytesType:
         case Type::UriType:
         default:
         {
            string msg("Cannot set Data Object for object of type:");
            msg += getTypeImpl().getName();
            SDO_THROW_EXCEPTION("DataObjectImpl::setDataObject",
                                SDOInvalidConversionException,
                                msg.c_str());
            break;
         }
      }
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


   DataObjectImpl::DataObjectImpl(const TypeImpl& t) :
      ObjectType((TypeImpl*) &t),
      container(0),
      doValue(0),
      isnull(false),
      userdata((void*) 0xFFFFFFFF)
   {
      // open type support
      openBase = t.getPropertiesSize() ;

      if (t.isChangeSummaryType())
      {
         localCS = new ChangeSummaryImpl();
      }
      else 
      {
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


   DataObjectImpl::DataObjectImpl(DataFactory* df, const Type& t) :
      ObjectType((TypeImpl*) &t),
      factory(df),
      container(0),
      isnull(false),
      userdata((void*) 0xFFFFFFFF)
   {
      // open type support
      openBase = ObjectType->getPropertiesSize() ;


      if (ObjectType->isChangeSummaryType())
      {
         localCS = new ChangeSummaryImpl();
      }
      else 
      {
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


        clearReferences();
         PropertyValueMap::iterator i = PropertyValues.begin();
        while (i != PropertyValues.end()) 
        {
            unsigned int pindx = (*i).first;
            DataObjectImplPtr dol = (*i).second;

            unset(pindx);
            i = PropertyValues.begin();
            if (i != PropertyValues.end() && (*i).first == pindx && (*i).second == dol)
            {
                // unset has not removed the item from the list - do it 
                // here instead
                PropertyValues.erase(i);
                i = PropertyValues.begin();
            }
        }

        // Theory: A DO cant get here if its still attached to anything,
        //so we dont need to detach....
        //detach();

        if (sdoValue.isSet())
        {
           sdoValue = SDOValue::unsetSDOValue;
        }
        
        if (getType().isSequencedType()) 
        {
            if (sequence != 0) delete sequence;
        }


        if (getTypeImpl().isChangeSummaryType()    )
        {
            if (getChangeSummary() != 0) 
            {
                delete localCS; 
                localCS = 0;
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
        // Only log if ChangeSummary is inherited from container

        if (getChangeSummaryImpl() != 0 && getChangeSummaryImpl()->isLogging() && !getType().isChangeSummaryType())
        {
            DataObjectImpl* cont = getContainerImpl();
            if (cont != 0)    // log if there is a container. If there is not, then
                            // this can only be the object with the CS, so logging
                            // would not make sense.
            {
                const Property* p = cont->findInProperties(this);
                if ( p != 0)    // if the object is not in the properties, then its been
                                // detached, and has already been logged as deleted
                {
                    getChangeSummaryImpl()->logDeletion(this,cont,*p,
                        objectToXPath(), true);
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
            getChangeSummaryImpl()->logChange(this,getProperty(propIndex));
        }
    }
    // reference support

    void DataObjectImpl::setReference(DataObject* dol, const Property& prop)
    {
        LOGINFO_1(INFO,"ChangeSummary:Setting a reference to %s",prop.getName());

        refs.push_back(new Reference(dol,prop));
    }
    void DataObjectImpl::unsetReference(DataObject* dol, const Property& prop)
    {
        LOGINFO_1(INFO,"ChangeSummary:Unsetting a reference to %s",prop.getName());

        for (unsigned int i=0;i< refs.size();i++)
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
        for (unsigned int i=0;i<refs.size();i++)
        {
            // Note - no loop as the referer must be of type reference
            refs[i]->getDataObject()->unset(refs[i]->getProperty());
        }
		// separate loop because the unsets may modify the refs
		for (unsigned int j=0;j<refs.size();j++) 
		{
			delete refs[j];
		}
        refs.clear();
    }

    const char* DataObjectImpl::objectToXPath()
    {
        asXPathBuffer = "";

        DataObjectImpl* dob = getContainerImpl();
        DataObject*thisob = this;
        while (dob != 0){
            const Property& p = thisob->getContainmentProperty();
            if (asXPathBuffer != "")
            {
                asXPathBuffer = "/" + asXPathBuffer;
            }

            if (p.isMany()) {
                DataObjectList& dol = dob->getList(p);
                for (unsigned int i=0;i<dol.size();i++)
                {
                    if (dol[i] == thisob)
                    {
                        char index[64];
                        sprintf(index,"%d",i);
                        asXPathBuffer = index + asXPathBuffer;
                        asXPathBuffer = "." + asXPathBuffer;
                        break;
                    }
                }
            }
            asXPathBuffer = p.getName() + asXPathBuffer;

            thisob = dob;
            dob = dob->getContainerImpl();
        }

        asXPathBuffer = "#/" + asXPathBuffer;

        return asXPathBuffer.c_str();
/*
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
        return asXPathBuffer; */
    }

    // user data support...
    void* DataObjectImpl::getUserData(const char* path)
    {
       // Can path really be a null pointer?
       if (path == 0)
       {
          return(getUserData(SDOString()));
       }
       else
       {
          return(getUserData(SDOString(path)));
       }
    }
    
    void* DataObjectImpl::getUserData(const SDOString& path)
    {
        DataObjectImpl *d;
        void* v = 0;
        SDOString spath;
        SDOString prop;
        try {
            DataObjectImpl::stripPath(path, spath);
            prop = findPropertyContainer(spath, &d);
            if (d != 0) 
            {
                if (!prop.empty())
                {
                    const Property& p = d->getProperty(prop);
                    if (p.getType().isDataType()) return 0;
                    if (p.isMany())
                    {
                        DataObjectImpl* d2 = d->getDataObjectImpl(prop);
                        if (d2) v = d2->getUserData();
                        return v;
                    }
                    v = d->getUserData(p);
                    return v;
                }
                return d->getUserData();
            }
            return 0;
        }
        catch (SDORuntimeException e)
        {
            return 0;
        }                
    }

    void* DataObjectImpl::getUserData(unsigned int propertyIndex)
    {
        if ((getProperty(propertyIndex).isMany()))
        {
            return 0;
        }
        if ((getProperty(propertyIndex).getType().isDataType()))
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
       // Can path really be a null pointer?
       if (path == 0)
       {
          setUserData(SDOString(), value);
       }
       else
       {
          setUserData(SDOString(path), value);
       }
    }
    
    void DataObjectImpl::setUserData(const SDOString& path, void* value)
    {
        SDOString spath;
        SDOString prop;
        DataObjectImpl *d;
        try {
            DataObjectImpl::stripPath(path, spath);
            prop = findPropertyContainer(spath, &d);
            if (d != 0) 
            {
                if (!prop.empty())
                {
                    const Property& p = d->getProperty(prop);
                    if (p.getType().isDataType())
                        return;
                    if (p.isMany())
                    {
                        DataObjectImpl* d2 = d->getDataObjectImpl(prop);
                        if (d2) d2->setUserData(value);
                        return;
                    }
                    d->setUserData(p, value);
                    return;
                }
                d->setUserData(value);
                return;
            }
        }
        catch (SDORuntimeException e)
        {
            return;
        }
        
    }

    void DataObjectImpl::setUserData(unsigned int propertyIndex, void* value)
    {
        if ((getProperty(propertyIndex).isMany()))
        {
            return;
        }
        if ((getProperty(propertyIndex).getType().isDataType()))
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
    
    std::ostream& DataObjectImpl::printSelf(std::ostream &os)
    {
        SDOUtils::printDataObject(os, this);
        return os;
    }

   // +++
   // Extra methods to support SDOValue as an internal mechanism that
   // simplifies dealing with the many interchangeable primitive data types.

   // set methods.

   void DataObjectImpl::setSDOValue(const SDOString& path,
                                    const SDOValue& sval,
                                    const SDOString& dataType)
   {
      DataObjectImpl *d = 0;

      SDOString spath;
      SDOString prop;
      try
      {
         DataObjectImpl::stripPath(path, spath);
         prop = findPropertyContainer(spath, &d);

         if (d != 0)
         {
            if (prop.length() == 0)
            {
               d->setSDOValue(sval);
            }
            else
            {
               const PropertyImpl* p = d->getPropertyImpl(prop);
               if ((p == 0) && (d->getType().isOpenType()))
               {
                  // p = d->defineBytes(prop);
                  p = d->defineSDOValue(prop, sval);
               }

               if (p == 0)
               {
                  string msg("DataObjectImpl::setSDOValue - path not found: ");
                  SDO_THROW_EXCEPTION("setter",
                                      SDOPathNotFoundException,
                                      msg.c_str());
               }

               if (p->isReadOnly())
               {
                  SDOString stringBuffer = p->getName();
                  stringBuffer += " is read-only.";
                  SDO_THROW_EXCEPTION("DataObjectImpl::setSDOValue",
                                      SDOUnsupportedOperationException,
                                      stringBuffer.c_str());
               }

               if ((p->isMany()) || (p->getTypeImpl()->isFromList()))
               {
                  long l;
                  DataObjectList& dol = d->getList((Property&) *p);
                  DataObjectImpl* doi = d->findDataObject(prop, &l);
                  if (doi != 0)
                  {
                     doi->setSDOValue(sval);
                  }
                  else 
                  {
                     dol.append(sval);
                  }
               }
               else
               {
                  d->setSDOValue((Property&)*p, sval, dataType);
               }
            }
         }
      }
      catch (SDORuntimeException e)
      {
         SDO_RETHROW_EXCEPTION("setSDOValue", e);
      }
   }

   void DataObjectImpl::setSDOValue(unsigned int propertyIndex,
                                    const SDOValue& sval,
                                    const SDOString& dataType)
   {
      setSDOValue(propertyIndex, sval, dataType, false);
   }
   
   void DataObjectImpl::setSDOValue(unsigned int propertyIndex,
                                    const SDOValue& sval,
                                    const SDOString& dataType,
                                    bool updateSequence)
   {
      validateIndex(propertyIndex);

      PropertyImpl *const p = getPropertyImpl(propertyIndex);

      if ((p->isMany()) || (p->getTypeImpl()->isFromList()))
      {
         string msg("Set value not available on many valued property: ");
         msg += p->getName();
         SDO_THROW_EXCEPTION("DataObjectImpl::setSDOValue",
                             SDOUnsupportedOperationException,
                             msg.c_str());
      }

      if (p->isReadOnly())
      {
          SDOString stringBuffer = p->getName();
          stringBuffer += "is read-only.";
          SDO_THROW_EXCEPTION("DataObjectImpl::setSDOValue",
                              SDOUnsupportedOperationException,
                              stringBuffer.c_str());
      }

      // PropertyValues is a std::list of rdo objects.
      PropertyValueMap::iterator i;
      for (i = PropertyValues.begin(); i != PropertyValues.end(); ++i)
      {
         if ((*i).first == propertyIndex)
         {
            logChange(propertyIndex);
            (*i).second->unsetNull();
            (*i).second->setSDOValue(sval);

            // If this is a sequenced data object then update the sequence. We
            // already know that a) the property is already set and b) it
            // is not a multi-valued property.
            if ((getType().isSequencedType()) && updateSequence)
            {
                   SequenceImpl* mySequence = getSequenceImpl();
                   mySequence->removeAll(getProperty(propertyIndex));
                   mySequence->push(getProperty(propertyIndex), 0);
            }
            return;
         }
      }

      // No existing property has the given index.
      DataFactory* df = getDataFactory();
      // It is tempting to use the raw data type from the SDOValue object to
      // set the type of the created DataObjectImpl but we can't because the
      // SDOValue specifies a C++ type while we need an SDO type.
      DataObjectImpl* b =
         new DataObjectImpl(df, df->getType(Type::SDOTypeNamespaceURI, dataType.c_str()));
      b->setContainer(this);
      logChange(propertyIndex);
      PropertyValues.push_back(rdo(propertyIndex, b));
      b->setSDOValue(sval);

      // If this is a sequenced data object then update the sequence. We
      // already know that a) the property is not already set and b) it
      // is not a multi-valued property.
      if ((getType().isSequencedType()) && updateSequence)
      {
         SequenceImpl* mySequence = getSequenceImpl();
         mySequence->removeAll(getProperty(propertyIndex));
         mySequence->push(getProperty(propertyIndex), 0);
      }

      return;
   }

   void DataObjectImpl::setSDOValue(const Property& property,
                                    const SDOValue& sval,
                                    const SDOString& dataType)
   {
      setSDOValue(getPropertyIndexInternal(property), sval, dataType);
   }

   void DataObjectImpl::setSDOValue(const Property& property,
                                    const SDOValue& sval,
                                    const SDOString& dataType,
                                    bool updateSequence)
   {
      setSDOValue(getPropertyIndexInternal(property), sval, dataType, updateSequence);
   }

   void DataObjectImpl::setSDOValue(const SDOValue& invalue)
   {
      sdoValue = invalue;
      return;
   }

   // get methods

   const SDOValue& DataObjectImpl::getSDOValue(const SDOString& path,
                                               PropertyImpl** propertyForDefault)
   {
      *propertyForDefault = 0;
      
      DataObjectImpl* d = 0;
      SDOString spath;
      SDOString prop;
      try
      {
         DataObjectImpl::stripPath(path, spath);
         // It is possible for findPropertyContainer to return a 0 which caues an accvio.
         prop = findPropertyContainer(spath, &d);
         if (d != 0)
         {
            if (prop.length() == 0)
            {
               return d->getSDOValue(propertyForDefault);
            }
            else
            {
               PropertyImpl* p  = d->getPropertyImpl(prop);
               if (p != 0)
               {
                  if ((p->isMany()) || p->getTypeImpl()->isFromList())
                  {
                     long l;
                     DataObjectImpl* doi = d->findDataObject(prop, &l);
                     if (doi != 0)
                     {
                        return doi->getSDOValue(propertyForDefault);
                     }
                     string msg("DataObjectImpl::getSDOValue - index out of range");
                     msg += path;
                     SDO_THROW_EXCEPTION("getter",
                                         SDOIndexOutOfRangeException,
                                         msg.c_str());
                  }
                  else
                  {
                     if (!d->isSet(*p))
                     {
                        *propertyForDefault = p;
                        return SDOValue::unsetSDOValue;
                     }
                     return d->getSDOValue(*p, propertyForDefault);
                  }
               }
            }
         }
         string msg("Object not found");
         SDO_THROW_EXCEPTION("DataObjectImpl::getSDOValue",
                             SDOPathNotFoundException,
                             msg.c_str());
      }
      catch (SDORuntimeException e) {
         SDO_RETHROW_EXCEPTION("getSDOValue", e);
      }
   }

   const SDOValue& DataObjectImpl::getSDOValue(const unsigned int propertyIndex,
                                               PropertyImpl** propertyForDefault)
   {
      *propertyForDefault = 0;

      validateIndex(propertyIndex);

      // Since validateIndex didn't throw an exception, the following call
      // will not return a null pointer.
      PropertyImpl* targetProperty = getPropertyImpl(propertyIndex);
      if ((targetProperty->isMany()) ||
          targetProperty->getTypeImpl()->isFromList())
      {
         string msg("Get value not available on many valued property:");
         msg += targetProperty->getName();
         SDO_THROW_EXCEPTION("DataObjectImpl::getSDOValue",
                             SDOUnsupportedOperationException,
                             msg.c_str());
      }

      DataObjectImpl* d = getDataObjectImpl(propertyIndex);
      if (d != 0)
      {
         if (!d->isNull())
         {
            return d->getSDOValue(propertyForDefault);
         }
         else
         {
            return SDOValue::nullSDOValue;
         }
      }

      // To get here, the property does not have a value, but there are still 2
      // cases to consider:
      // 1. The property has never had a value. In this case, we return
      // "unset" for the value of the property.
      // 2. The property did have a value at one time but since then has
      // been explicitly set to null, causing the value to be discarded. In
      // that case return an explicit null.

      if (isSet(propertyIndex))
      {
         return SDOValue::nullSDOValue;
      }

      *propertyForDefault = targetProperty;
      return SDOValue::unsetSDOValue;

   }

   const SDOValue& DataObjectImpl::getSDOValue(const Property& property,
                                               PropertyImpl** propertyForDefault)
   {
      return getSDOValue(getPropertyIndex(property), propertyForDefault);
   }

   const SDOValue& DataObjectImpl::getSDOValue(PropertyImpl** propertyForDefault)
   {
      if (sdoValue.isSet())
      {
         *propertyForDefault = 0;
      }
      else
      {
         *propertyForDefault = (PropertyImpl*) &(getContainmentProperty());
      }
      return sdoValue;
   }

   // End of SDOValue methods
   // ---

   // +++
   // setBoolean using SDOValue methods

   void DataObjectImpl::setBoolean(unsigned int propertyIndex,
                                   bool value)
   {
      setSDOValue(propertyIndex, SDOValue(value), "Boolean");
   }

   void DataObjectImpl::setBoolean(const Property& property, bool value)
   {
      setBoolean(getPropertyIndexInternal(property), value);
   }

   void DataObjectImpl::setBoolean(const SDOString& path,
                                   bool value)
   {
      setSDOValue(path, SDOValue(value), "Boolean");
   }

   // End of setBoolean using SDOValue methods
   // ---

   // +++
   // getBoolean using SDOValue methods

   bool DataObjectImpl::getBoolean(const Property& property)
   {
      return getBoolean(getPropertyIndex(property));
   }

   bool DataObjectImpl::getBoolean(unsigned int propertyIndex)
   {

      PropertyImpl* propertyForDefault = 0;
      const SDOValue& result = getSDOValue(propertyIndex, &propertyForDefault);
      
      if (!result.isSet())
      {
         return propertyForDefault->getBooleanDefault();
      }
      else
      {
         if (result.isNull())
         {
            return false;
         }
         else
         {
            return result.getBoolean();
         }
      }
   }

   bool DataObjectImpl::getBoolean(const SDOString& path)
   {
      PropertyImpl* propertyForDefault = 0;
      const SDOValue& result = getSDOValue(path, &propertyForDefault);

      if (!result.isSet())
      {
         return propertyForDefault->getBooleanDefault();         
      }
      else
      {
         if (result.isNull())
         {
            return false;
         }
         else
         {
            return result.getBoolean();
         }
      }
   }

   // End of getBoolean using SDOValue methods
   // ---

   // +++
   // setFloat using SDOValue methods

   void DataObjectImpl::setFloat(unsigned int propertyIndex,
                                 float value)
   {
      setSDOValue(propertyIndex, SDOValue(value), "Float");
   }

   void DataObjectImpl::setFloat(const Property& property, float value)
   {
      setFloat(getPropertyIndexInternal(property), value);
   }

   void DataObjectImpl::setFloat(const SDOString& path,
                                 float value)
   {
      setSDOValue(path, SDOValue(value), "Float");
   }

   // End of setFloat using SDOValue methods
   // ---

   // +++
   // getFloat using SDOValue methods

   float DataObjectImpl::getFloat(const Property& property)
   {
      return getFloat(getPropertyIndex(property));
   }

   float DataObjectImpl::getFloat(unsigned int propertyIndex)
   {

      PropertyImpl* propertyForDefault = 0;
      const SDOValue& result = getSDOValue(propertyIndex, &propertyForDefault);
      
      if (!result.isSet())
      {
         return propertyForDefault->getFloatDefault();
      }
      else
      {
         if (result.isNull())
         {
            return 0.0F;       // Default is 0 cast to return type
         }
         else
         {
            return result.getFloat();
         }
      }
   }

   float DataObjectImpl::getFloat(const SDOString& path)
   {
      PropertyImpl* propertyForDefault = 0;
      const SDOValue& result = getSDOValue(path, &propertyForDefault);

      if (!result.isSet())
      {
         return propertyForDefault->getFloatDefault();         
      }
      else
      {
         if (result.isNull())
         {
            return 0.0F;       // Default is 0 cast to return type
         }
         else
         {
            return result.getFloat();
         }
      }
   }

   // End of getFloat using SDOValue methods
   // ---

   // +++
   // setDouble using SDOValue methods

   void DataObjectImpl::setDouble(unsigned int propertyIndex,
                                  long double value)
   {
      setSDOValue(propertyIndex, SDOValue(value), "Double");
   }

   void DataObjectImpl::setDouble(const Property& property, long double value)
   {
      setDouble(getPropertyIndexInternal(property), value);
   }

   void DataObjectImpl::setDouble(const SDOString& path,
                                  long double value)
   {
      setSDOValue(path, SDOValue(value), "Double");
   }

   // End of setDouble using SDOValue methods
   // ---

   // +++
   // getDouble using SDOValue methods

   long double DataObjectImpl::getDouble(const Property& property)
   {
      return getDouble(getPropertyIndex(property));
   }

   long double DataObjectImpl::getDouble(unsigned int propertyIndex)
   {

      PropertyImpl* propertyForDefault = 0;
      const SDOValue& result = getSDOValue(propertyIndex, &propertyForDefault);
      
      if (!result.isSet())
      {
         return propertyForDefault->getDoubleDefault();
      }
      else
      {
         if (result.isNull())
         {
            return 0.0;         // Default is 0 cast to return type
         }
         else
         {
            return result.getDouble();
         }
      }
   }

   long double DataObjectImpl::getDouble(const SDOString& path)
   {
      PropertyImpl* propertyForDefault = 0;
      const SDOValue& result = getSDOValue(path, &propertyForDefault);

      if (!result.isSet())
      {
         return propertyForDefault->getDoubleDefault();         
      }
      else
      {
         if (result.isNull())
         {
            return 0.0;         // Default is 0 cast to return type
         }
         else
         {
            return result.getDouble();
         }
      }
   }

   // End of getDouble using SDOValue methods
   // ---

   // +++
   // setShort using SDOValue methods

   void DataObjectImpl::setShort(unsigned int propertyIndex,
                                 short value)
   {
      setSDOValue(propertyIndex, SDOValue(value), "Short");
   }

   void DataObjectImpl::setShort(const Property& property, short value)
   {
      setShort(getPropertyIndexInternal(property), value);
   }

   void DataObjectImpl::setShort(const SDOString& path,
                                 short value)
   {
      setSDOValue(path, SDOValue(value), "Short");
   }

   // End of setShort using SDOValue methods
   // ---

   // +++
   // getShort using SDOValue methods

   short DataObjectImpl::getShort(const Property& property)
   {
      return getShort(getPropertyIndex(property));
   }

   short DataObjectImpl::getShort(unsigned int propertyIndex)
   {

      PropertyImpl* propertyForDefault = 0;
      const SDOValue& result = getSDOValue(propertyIndex, &propertyForDefault);
      
      if (!result.isSet())
      {
         return propertyForDefault->getShortDefault();
      }
      else
      {
         if (result.isNull())
         {
            return 0;           // Default is 0 cast to return type
         }
         else
         {
            return result.getShort();
         }
      }
   }

   short DataObjectImpl::getShort(const SDOString& path)
   {
      PropertyImpl* propertyForDefault = 0;
      const SDOValue& result = getSDOValue(path, &propertyForDefault);

      if (!result.isSet())
      {
         return propertyForDefault->getShortDefault();         
      }
      else
      {
         if (result.isNull())
         {
            return 0;        // Default is 0 cast to return type
         }
         else
         {
            return result.getShort();
         }
      }
   }

   // End of getShort using SDOValue methods
   // ---

   // +++
   // setByte using SDOValue methods

   void DataObjectImpl::setByte(unsigned int propertyIndex,
                                char value)
   {
      setSDOValue(propertyIndex, SDOValue(value), "Byte");
   }

   void DataObjectImpl::setByte(const Property& property, char value)
   {
      setByte(getPropertyIndexInternal(property), value);
   }

   void DataObjectImpl::setByte(const SDOString& path,
                                 char value)
   {
      setSDOValue(path, SDOValue(value), "Byte");
   }

   // End of setByte using SDOValue methods
   // ---

   // +++
   // getByte using SDOValue methods

   char DataObjectImpl::getByte(const Property& property)
   {
      return getByte(getPropertyIndex(property));
   }

   char DataObjectImpl::getByte(unsigned int propertyIndex)
   {

      PropertyImpl* propertyForDefault = 0;
      const SDOValue& result = getSDOValue(propertyIndex, &propertyForDefault);
      
      if (!result.isSet())
      {
         return propertyForDefault->getByteDefault();
      }
      else
      {
         if (result.isNull())
         {
            return 0;           // Default is 0 cast to return type
         }
         else
         {
            return result.getByte();
         }
      }
   }

   char DataObjectImpl::getByte(const SDOString& path)
   {
      PropertyImpl* propertyForDefault = 0;
      const SDOValue& result = getSDOValue(path, &propertyForDefault);

      if (!result.isSet())
      {
         return propertyForDefault->getByteDefault();         
      }
      else
      {
         if (result.isNull())
         {
            return 0;        // Default is 0 cast to return type
         }
         else
         {
            return result.getByte();
         }
      }
   }

   // End of getByte using SDOValue methods
   // ---

   // +++
   // setDate using SDOValue methods

   void DataObjectImpl::setDate(unsigned int propertyIndex,
                                const SDODate value)
   {
      setSDOValue(propertyIndex, SDOValue(value), "Date");
   }

   void DataObjectImpl::setDate(const Property& property, const SDODate value)
   {
      setDate(getPropertyIndexInternal(property), value);
   }

   void DataObjectImpl::setDate(const SDOString& path,
                                const SDODate value)
   {
      setSDOValue(path, SDOValue(value), "Date");
   }

   // End of setDouble using SDOValue methods
   // ---

   // +++
   // getDate using SDOValue methods

   const SDODate DataObjectImpl::getDate(const Property& property)
   {
      return getDate(getPropertyIndex(property));
   }

   const SDODate DataObjectImpl::getDate(unsigned int propertyIndex)
   {

      PropertyImpl* propertyForDefault = 0;
      const SDOValue& result = getSDOValue(propertyIndex, &propertyForDefault);
      
      if (!result.isSet())
      {
         return propertyForDefault->getDateDefault();
      }
      else
      {
         if (result.isNull())
         {
            return SDODate(0);         // Default is 0 cast to return type
         }
         else
         {
            return result.getDate();
         }
      }
   }

   const SDODate DataObjectImpl::getDate(const SDOString& path)
   {
      PropertyImpl* propertyForDefault = 0;
      const SDOValue& result = getSDOValue(path, &propertyForDefault);

      if (!result.isSet())
      {
         return propertyForDefault->getDateDefault();         
      }
      else
      {
         if (result.isNull())
         {
            return SDODate(0);         // Default is 0 cast to return type
         }
         else
         {
            return result.getDate();
         }
      }
   }

   // End of getDouble using SDOValue methods
   // ---

   // +++
   // setInteger using SDOValue methods

   void DataObjectImpl::setInteger(unsigned int propertyIndex,
                                 long value)
   {
      setSDOValue(propertyIndex, SDOValue(value), "Integer");
   }

   void DataObjectImpl::setInteger(const Property& property, long value)
   {
      setInteger(getPropertyIndexInternal(property), value);
   }

   void DataObjectImpl::setInteger(const SDOString& path,
                                 long value)
   {
      setSDOValue(path, SDOValue(value), "Integer");
   }

   // End of setInteger using SDOValue methods
   // ---

   // +++
   // getInteger using SDOValue methods

   long DataObjectImpl::getInteger(const Property& property)
   {
      return getInteger(getPropertyIndex(property));
   }

   long DataObjectImpl::getInteger(unsigned int propertyIndex)
   {

      PropertyImpl* propertyForDefault = 0;
      const SDOValue& result = getSDOValue(propertyIndex, &propertyForDefault);
      
      if (!result.isSet())
      {
         return propertyForDefault->getIntegerDefault();
      }
      else
      {
         if (result.isNull())
         {
            return 0;           // Default is 0 cast to return type
         }
         else
         {
            return result.getInteger();
         }
      }
   }

   long DataObjectImpl::getInteger(const SDOString& path)
   {
      PropertyImpl* propertyForDefault = 0;
      const SDOValue& result = getSDOValue(path, &propertyForDefault);

      if (!result.isSet())
      {
         return propertyForDefault->getIntegerDefault();         
      }
      else
      {
         if (result.isNull())
         {
            return 0;        // Default is 0 cast to return type
         }
         else
         {
            return result.getInteger();
         }
      }
   }

   // End of getInteger using SDOValue methods
   // ---

   // +++
   // setCString using SDOValue methods

   void DataObjectImpl::setCString(unsigned int propertyIndex,
                                   const SDOString& value)
   {
      setSDOValue(propertyIndex, SDOValue(value), "String");
   }

   void DataObjectImpl::setCString(const Property& property, const SDOString& value)
   {
      setCString(getPropertyIndexInternal(property), value);
   }

   void DataObjectImpl::setCString(const SDOString& path,
                                   const SDOString& value)
   {
      setSDOValue(path, SDOValue(value), "String");
   }

   // End of setCString using SDOValue methods
   // ---

   // +++
   // getCString using SDOValue methods

   const char* DataObjectImpl::getCString(const Property& property)
   {
      return getCString(getPropertyIndex(property));
   }

   const char* DataObjectImpl::getCString(unsigned int propertyIndex)
   {

      PropertyImpl* propertyForDefault = 0;
      const SDOValue& result = getSDOValue(propertyIndex, &propertyForDefault);
      
      if (!result.isSet())
      {
         return propertyForDefault->getCStringDefault();
      }
      else
      {
         if (result.isNull())
         {
            return 0;           // Default is 0 cast to return type
         }
         else
         {
            return result.getCString();
         }
      }
   }

   const char* DataObjectImpl::getCString(const SDOString& path)
   {
      PropertyImpl* propertyForDefault = 0;
      const SDOValue& result = getSDOValue(path, &propertyForDefault);

      if (!result.isSet())
      {
         return propertyForDefault->getCStringDefault();         
      }
      else
      {
         if (result.isNull())
         {
            return 0;        // Default is 0 cast to return type
         }
         else
         {
            return result.getCString();
         }
      }
   }

   // End of getCString using SDOValue methods
   // ---

   // +++
   // setCharacter using SDOValue methods

   void DataObjectImpl::setCharacter(unsigned int propertyIndex,
                                     wchar_t value)
   {
      setSDOValue(propertyIndex, SDOValue(value), "Character");
   }

   void DataObjectImpl::setCharacter(const Property& property, wchar_t value)
   {
      setCharacter(getPropertyIndexInternal(property), value);
   }

   void DataObjectImpl::setCharacter(const SDOString& path,
                                     wchar_t value)
   {
      setSDOValue(path, SDOValue(value), "Character");
   }

   // End of setByte using SDOValue methods
   // ---

   // +++
   // getByte using SDOValue methods

   wchar_t DataObjectImpl::getCharacter(const Property& property)
   {
      return getCharacter(getPropertyIndex(property));
   }

   wchar_t DataObjectImpl::getCharacter(unsigned int propertyIndex)
   {

      PropertyImpl* propertyForDefault = 0;
      const SDOValue& result = getSDOValue(propertyIndex, &propertyForDefault);
      
      if (!result.isSet())
      {
         return propertyForDefault->getCharacterDefault();
      }
      else
      {
         if (result.isNull())
         {
            return (wchar_t) 0;           // Default is 0 cast to return type
         }
         else
         {
            return result.getCharacter();
         }
      }
   }

   wchar_t DataObjectImpl::getCharacter(const SDOString& path)
   {
      PropertyImpl* propertyForDefault = 0;
      const SDOValue& result = getSDOValue(path, &propertyForDefault);

      if (!result.isSet())
      {
         return propertyForDefault->getCharacterDefault();         
      }
      else
      {
         if (result.isNull())
         {
            return (wchar_t) 0;        // Default is 0 cast to return type
         }
         else
         {
            return result.getCharacter();
         }
      }
   }

   // End of getCharacter using SDOValue methods
   // ---

   // +++
   // setLong using SDOValue methods

   void DataObjectImpl::setLong(unsigned int propertyIndex,
                                int64_t value)
   {
      setSDOValue(propertyIndex, SDOValue(value), "Long");
   }

   void DataObjectImpl::setLong(const Property& property, int64_t value)
   {
      setLong(getPropertyIndexInternal(property), value);
   }

   void DataObjectImpl::setLong(const SDOString& path,
                                int64_t value)
   {
      setSDOValue(path, SDOValue(value), "Long");
   }

   // End of setLong using SDOValue methods
   // ---

   // +++
   // getLong using SDOValue methods

   int64_t DataObjectImpl::getLong(const Property& property)
   {
      return getLong(getPropertyIndex(property));
   }

   int64_t DataObjectImpl::getLong(unsigned int propertyIndex)
   {

      PropertyImpl* propertyForDefault = 0;
      const SDOValue& result = getSDOValue(propertyIndex, &propertyForDefault);
      
      if (!result.isSet())
      {
         return propertyForDefault->getLongDefault();
      }
      else
      {
         if (result.isNull())
         {
            return 0L;           // Default is 0 cast to return type
         }
         else
         {
            return result.getLong();
         }
      }
   }

   int64_t DataObjectImpl::getLong(const SDOString& path)
   {
      PropertyImpl* propertyForDefault = 0;
      const SDOValue& result = getSDOValue(path, &propertyForDefault);

      if (!result.isSet())
      {
         return propertyForDefault->getLongDefault();         
      }
      else
      {
         if (result.isNull())
         {
            return 0;        // Default is 0 cast to return type
         }
         else
         {
            return result.getLong();
         }
      }
   }

   // End of getLong using SDOValue methods
   // ---

   // The input value is a non-null terminated sequence of bytes.
   void DataObjectImpl::setBytes(unsigned int propertyIndex, const char* value, unsigned int len)
   {
      setSDOValue(propertyIndex, SDOValue(value, len), "Bytes");      
   }

   void DataObjectImpl::setString(unsigned int propertyIndex, const wchar_t* value, unsigned int len)
   {
      setSDOValue(propertyIndex, SDOValue(value, len), "String");
   }

   unsigned int DataObjectImpl::getBytes(unsigned int propertyIndex, char* valptr , unsigned int max)
   {

      PropertyImpl* propertyForDefault = 0;
      const SDOValue& result = getSDOValue(propertyIndex, &propertyForDefault);
      
      if (!result.isSet())
      {
         return propertyForDefault->getBytesDefault(valptr, max);
      }
      else
      {
         if (result.isNull())
         {
            return 0;
         }
         else
         {
            return result.getBytes(valptr, max);
         }
      }
   }

   unsigned int DataObjectImpl::getString(unsigned int propertyIndex, wchar_t* valptr , unsigned int max)
   {

      PropertyImpl* propertyForDefault = 0;
      const SDOValue& result = getSDOValue(propertyIndex, &propertyForDefault);
      
      if (!result.isSet())
      {
         return propertyForDefault->getStringDefault(valptr, max);
      }
      else
      {
         if (result.isNull())
         {
            return 0;
         }
         else
         {
            return result.getString(valptr, max);
         }
      }
   }

    unsigned int DataObjectImpl::getString(const SDOString& path, wchar_t* valptr , unsigned int max)
    {

      PropertyImpl* propertyForDefault = 0;
      const SDOValue& result = getSDOValue(path, &propertyForDefault);

      if (!result.isSet())
      {
         return propertyForDefault->getStringDefault(valptr, max);
      }
      else
      {
         if (result.isNull())
         {
            return 0;
         }
         else
         {
            return result.getString(valptr, max);
         }
      }
    }

    unsigned int DataObjectImpl::getBytes(const SDOString& path, char* valptr , unsigned int max)
    {
      PropertyImpl* propertyForDefault = 0;
      const SDOValue& result = getSDOValue(path, &propertyForDefault);

      if (!result.isSet())
      {
         return propertyForDefault->getBytesDefault(valptr, max);         
      }
      else
      {
         if (result.isNull())
         {
            return 0;        // Default is 0 cast to return type
         }
         else
         {
            return result.getBytes(valptr, max);
         }
      }
    }

   void DataObjectImpl::setString(const char* path, const wchar_t* value, unsigned int len)
   {
      setString(SDOString(path), value, len);
   }

    void DataObjectImpl::setBytes(const char* path, const char* value, unsigned int len)
    {
        setBytes(SDOString(path), value, len);
    }

    void DataObjectImpl::setString(const SDOString& path, const wchar_t* value, unsigned int len)
    {
      setSDOValue(path, SDOValue(value, len), "String");
    }


    void DataObjectImpl::setBytes(const SDOString& path, const char* value, unsigned int len)
    {
      setSDOValue(path, SDOValue(value, len), "Bytes");
    }

    unsigned int DataObjectImpl::getString(const Property& property, wchar_t* val, unsigned int max)
    {
       return getString(getPropertyIndex(property), val, max);
    }

    unsigned int DataObjectImpl::getBytes(const Property& property, char* val, unsigned int max)
    {
       return getBytes(getPropertyIndex(property), val, max);
    }

  void DataObjectImpl::setString(const Property& property, const wchar_t* value, unsigned int len)
  {
      setString(getPropertyIndexInternal(property),value, len);
  }

  void DataObjectImpl::setBytes(const Property& property, const char* value, unsigned int len)
  {
      setBytes(getPropertyIndexInternal(property),value, len);
  }

};
};
