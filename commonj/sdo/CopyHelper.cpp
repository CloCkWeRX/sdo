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

/* $Rev: 241789 $ $Date: 2007-08-25 00:50:26 +0930 (Sat, 25 Aug 2007) $ */

#include "commonj/sdo/Property.h"
#include "commonj/sdo/Type.h"
#include "commonj/sdo/TypeList.h"
#include "commonj/sdo/Sequence.h"
#include "commonj/sdo/RefCountingPointer.h"
#include "commonj/sdo/DataObjectImpl.h"


#include "commonj/sdo/CopyHelper.h"

#include <iostream>
using namespace std;
namespace commonj{
namespace sdo{

    /** CopyHelper provides static copying helper functions.
     *
     * CopyHelper provides shallow and deep copy of data objects.
     * copyShallow() copies the DataType members of the data object.
     * copy() copies all the members and recurses downwards though
     * the data graph
     */

    void CopyHelper::transferitem(DataObjectPtr to, DataObjectPtr from, const Property& p)
    {
		if (from->isNull(p)) {
			to->setNull(p);
			return;
		}

        switch (p.getTypeEnum())
        {
        case Type::BooleanType:
            to->setBoolean(    p, from->getBoolean(p));
            break;
        case Type::ByteType:
            to->setByte( p, from->getByte(p));
            break;
        case Type::CharacterType:
            to->setCharacter( p, from->getCharacter(p));
            break;
        case Type::IntegerType: 
            to->setInteger( p, from->getInteger(p));
            break;
        case Type::ShortType:
            to->setShort( p,from->getShort(p));
            break;
        case Type::DoubleType:
            to->setDouble( p, from->getDouble(p));
            break;
        case Type::FloatType:
            to->setFloat( p, from->getFloat(p));
            break;
        case Type::LongType:
            to->setLong( p, from->getLong(p));
            break;
        case Type::DateType:
            to->setDate( p, from->getDate(p));
            break;
        case Type::BigDecimalType: 
        case Type::BigIntegerType: 
        case Type::UriType:
        case Type::StringType:
            {
                unsigned int siz =     from->getLength(p);
                if (siz > 0)
                {
                    wchar_t * buf = new wchar_t[siz];
                    from->getString(p,buf, siz);
                    to->setString(p, buf, siz);
                    delete[] buf;
                }
                else
                {
                    // property is set to a NULL value
                    to->setString(p, (const wchar_t*)0, 0);
                }
            }
            break;
        case Type::BytesType:
            {
                unsigned int siz = from->getLength(p);
                if (siz > 0)
                {
                    char * buf = new char[siz];
                    from->getBytes(p,buf, siz);
                    to->setBytes(p, buf, siz);
                    delete buf;
                }
                else
                {
                    // property is set to a NULL value
                    to->setBytes(p, (const char*)0, 0);
                }
            }
            break;
        default:
            break;
        }  // switch
    }

    void CopyHelper::transferlist(DataObjectList& to, DataObjectList& from, Type::Types t)
    {
        for (unsigned int i=0;i< from.size(); i++)
        {
            switch (t)
            {
            case Type::BooleanType:
                to.append(from.getBoolean(i));
                break;
            case Type::ByteType:
                to.append(from.getByte(i));
                break;
            case Type::CharacterType:
                to.append(from.getCharacter(i));
                break;
#if __WORDSIZE ==64
            case Type::IntegerType: 
                to.append((int64_t)(from.getInteger(i)));
                break;
#else
            case Type::IntegerType: 
                to.append(from.getInteger(i));
                break;
#endif
            case Type::ShortType:
                to.append(from.getShort(i));
                break;
            case Type::DoubleType:
                to.append(from.getDouble(i));
                break;
            case Type::FloatType:
                to.append(from.getFloat(i));
                break;
            case Type::LongType:
                to.append(from.getLong(i));
                break;
            case Type::DateType:
                to.append(from.getDate(i));
                break;
            case Type::BigDecimalType: 
            case Type::BigIntegerType: 
            case Type::UriType:
            case Type::StringType:
                {
                    unsigned int siz = from.getLength(i);
                    if (siz > 0) 
                    {
                        wchar_t * buf = new wchar_t[siz];
                        from.getString(i,buf,siz);
                        to.append(buf,siz);
                        delete buf;
                    }
                    else
                    {
                        // Property is set to a NULL value
                        to.append((const wchar_t*)0, 0);
                    }
                }
                break;

            case Type::BytesType:
                {
                    unsigned int siz = from.getLength(i);
                    if (siz > 0) 
                    {
                        char * buf = new char[siz];
                        from.getBytes(i,buf,siz);
                        to.append(buf,siz);
                        delete buf;
                    }
                    else
                    {
                        // Property is set to a NULL value
                        to.append((const char*)0, 0);
                    }
                }
                break;

            default:
                break;
            } // case
        } // for
    } // method



    void CopyHelper::transfersequenceitem(Sequence *to, Sequence *from, const Property& p, int index)
    {
        switch (p.getTypeEnum())
        {
        case Type::BooleanType:
            to->addBoolean(    p, from->getBooleanValue(index));
            break;
        case Type::ByteType:
            to->addByte( p, from->getByteValue(index));
            break;
        case Type::CharacterType:
            to->addCharacter( p, from->getCharacterValue(index));
            break;
        case Type::IntegerType: 
            to->addInteger( p, from->getIntegerValue(index));
            break;
        case Type::ShortType:
            to->addShort( p,from->getShortValue(index));
            break;
        case Type::DoubleType:
            to->addDouble( p, from->getDoubleValue(index));
            break;
        case Type::FloatType:
            to->addFloat( p, from->getFloatValue(index));
            break;
        case Type::LongType:
            to->addLong( p, from->getLongValue(index));
            break;
        case Type::DateType:
            to->addDate( p, from->getDateValue(index));
            break;
        case Type::BigDecimalType: 
        case Type::BigIntegerType: 
        case Type::UriType:
        case Type::StringType:
            {
                unsigned int siz =     from->getLength(index);
                if (siz > 0)
                {
                    wchar_t * buf = new wchar_t[siz];
                    from->getStringValue(index, buf, siz);
                    to->addString(p, buf, siz);
                    delete[] buf;
                }
                else
                {
                    // property is set to a NULL value
                    to->addString(p, 0, 0);
                }
            }
            break;
        case Type::BytesType:
            {
                unsigned int siz = from->getLength(index);
                if (siz > 0)
                {
                    char * buf = new char[siz];
                    from->getBytesValue(index, buf, siz);
                    to->addBytes(p, buf, siz);
                    delete buf;
                }
                else
                {
                    // property is set to a NULL value
                    to->addBytes(p, 0, 0);
                }
            }
            break;
        default:
            break;
        }  // switch
    }

    /** CopyHelper provides static copying helper functions.
     *
     * copyShallow() copies the DataType members of the data object.
     * copy() copies all the members and recurses downwards though
     * the data graph
     */
    DataObjectPtr CopyHelper::copyShallow(DataObjectPtr dataObject)
    {
        return internalCopy(dataObject, false);
        
    }

    /** CopyHelper provides static copying helper functions.
     *
     * copyShallow() copies the DataType members of the data object.
     * copy() copies all the members and recurses downwards though
     * the data graph
     */
    DataObjectPtr CopyHelper::copy(DataObjectPtr dataObject)
    {
        DataObjectPtr newob = internalCopy(dataObject, true);
        resolveReferences(dataObject, newob);
        return newob;
    }

    DataObjectPtr CopyHelper::internalCopy(DataObjectPtr dataObject,
                                           bool fullCopy)
    {

        DataObject* theob = dataObject;
        if (!theob) return 0;

        DataFactoryPtr fac = ((DataObjectImpl*)theob)->getDataFactory();
        if (!fac) return 0;

        const Type& t = dataObject->getType();
        DataObjectPtr newob = fac->create(t);
        if (!newob) return 0;

        if ( dataObject->getType().isSequencedType() )
        {
            Sequence* fromSequence = dataObject->getSequence();
            int sequence_length = fromSequence->size();
            
            Sequence* toSequence = newob->getSequence();
            
            for (int i=0;i < sequence_length; i++)
            {
                if ( fromSequence->isText(i) )
                {
                    const char *text = fromSequence->getCStringValue(i);
                    toSequence->addText(i, text);
                } 
                else 
                {
                    const Property& seqProperty = fromSequence->getProperty(i); 
                    SDOXMLString seqPropertyName = seqProperty.getName();
                    const Type& seqPropertyType = seqProperty.getType();

                    if (seqPropertyType.isDataObjectType())
                    {                                
                        if (!fullCopy) 
                        {
                            continue;
                        }
                        else
                        {
                            DataObjectPtr dob;

                            // retrieve the data object to be copied
                            if (seqProperty.isMany())
                            {
                                int index = fromSequence->getListIndex(i);
                                dob = dataObject->getList(seqProperty)[index];
                            }
                            else
                            {
                                dob = dataObject->getDataObject(seqProperty);
                            }
                              
                            // do the copying of referencing
                            if (dob)
                            {
                                // Handle non-containment reference to DataObject
                                if (seqProperty.isReference())
                                {
                                    // add just the reference into the sequence
                                    // This will be resolved to a new reference later
                                    // This is really bad but we need to add something to the
                                    // sequence here to maintain the ordering
                                    toSequence->addDataObject(seqProperty, 0);
                                }
                                else
                                {
                                    // make a copy of the data object itself
                                    // and add it to the sequence
                                    toSequence->addDataObject(seqProperty,
                                                              internalCopy(dob,
                                                                           true));
                                }
                            }
                        }
                    } 
                    else
                    {
                        // Sequence member is a primitive
                        transfersequenceitem(toSequence,
                                             fromSequence,
                                             seqProperty,
                                             i);
                                                
                    } 
                } // is it a text element
            } // for all elements in sequence
        }
        else
        {
            PropertyList pl = dataObject->getInstanceProperties();
            for (unsigned int i=0;i < pl.size(); i++)
            {
                if (dataObject->isSet(pl[i]))
                {
                    // data objects are only copied in the deep copy case
                    if (pl[i].getType().isDataObjectType()) 
                    {
                        if (!fullCopy) 
                        {
                            continue;
                        }
                        else
                        {
                            if (pl[i].isMany())
                            {
                                DataObjectList& dolold = dataObject->getList(pl[i]);
                                DataObjectList& dolnew = newob->getList(pl[i]);
                                for (unsigned int li=0;li< dolold.size(); li++)
                                {    
                                    // references are maintained to the old object if it
                                    // is outside of the copy tree
                                    if (pl[i].isReference()) 
                                    {
                                        // have to resolve references in a 2nd pass
                                    }
                                    else
                                    {
                                        dolnew.append(internalCopy(dolold[li],true));
                                    }
                                }
                            }
                            else 
                            {
								if (dataObject->isNull(pl[i])) {
									newob->setNull(pl[i]);
									continue;
								}
								
                                DataObjectPtr dob = dataObject->getDataObject(pl[i]);
                                if (pl[i].isReference()) 
                                {
                                    // have to resolve references in a 2nd pass
                                }
                                else
                                {
                                    newob->setDataObject(pl[i],internalCopy(dob,true));
                                }
                            }
                        }
                    }
                    else 
                    {
                        if (pl[i].isMany())
                        {
                            DataObjectList& dolold = dataObject->getList(pl[i]);
                            DataObjectList& dolnew = newob->getList(pl[i]);
                            transferlist(dolnew,dolold, pl[i].getTypeEnum());
                        }
                        else 
                        {
                            transferitem(newob,dataObject, pl[i]);
                        }
                    } // else
                } 
            } 
        }

        return newob;
    }

    void CopyHelper::resolveReferences(DataObjectPtr oldDO, DataObjectPtr newDO)
    {
        // Iterate through the properties to find references.
        // If the reference is to a DataObject with the copied tree then we can
        // set it to reference the DO in the new tree, otherwise it is left unset.

        findReferences(oldDO, newDO, oldDO, newDO);

    }

    void CopyHelper::findReferences(DataObjectPtr oldDO, DataObjectPtr newDO,
        DataObjectPtr obj, DataObjectPtr newObj)
    {
		if (!obj) return;

        if ( obj->getType().isSequencedType() )
        {
            Sequence* fromSequence = obj->getSequence();
            int sequence_length = fromSequence->size();
            
            Sequence* toSequence = newObj->getSequence();
            
            for (int i=0;i < sequence_length; i++)
            {
                if (!fromSequence->isText(i) )
                {
                    const Property& seqProperty = fromSequence->getProperty(i); 
                    SDOXMLString seqPropertyName = seqProperty.getName();
                    const Type& seqPropertyType = seqProperty.getType();

                    if (seqProperty.isReference())
                    {  
                        DataObjectPtr ref = findReference(oldDO, newDO, fromSequence->getDataObjectValue(i));
                        if (ref)
                        {
                            if (seqProperty.isMany())
                            {
                                int index = fromSequence->getListIndex(i);
                                newObj->getList(seqProperty).setDataObject(index, ref);
                            }
                            else
                            {
                                toSequence->setDataObjectValue(i, ref);
                            }

                        }
                    }
                    else if (seqPropertyType.isDataObjectType())
                    {
                        findReferences(oldDO, newDO, fromSequence->getDataObjectValue(i), toSequence->getDataObjectValue(i));
                    }
                }
 
             } // for all elements in sequence
 
        }       
        else
        {
            PropertyList pl = obj->getInstanceProperties();
            for (unsigned int i=0;i < pl.size(); i++)
            {
                if (!obj->isSet(pl[i]))
                    continue;

                if (!pl[i].getType().isDataObjectType())
                    continue;

                if (pl[i].isMany())
                {
                    DataObjectList& dolold = obj->getList(pl[i]);
                    DataObjectList& dolnew = newObj->getList(pl[i]);
                    for (unsigned int li=0;li< dolold.size(); li++)
                    {
                        if (pl[i].isReference())
                        {
                            DataObjectPtr ref = findReference(oldDO, newDO, dolold[li]);
                            if (ref)
                            {
                                dolnew.setDataObject(li, ref);
                            }
                        }
                        else
                        {
                            findReferences(oldDO, newDO, dolold[li], dolnew[li]);
                        }
                    }
                }
                else 
                {
                    if (pl[i].isReference())
                    {
                        DataObjectPtr ref = findReference(oldDO, newDO,  obj->getDataObject(pl[i]));
                        if (ref)
                        {
                            newObj->setDataObject(pl[i], ref);
                        }
                    }
                    else
                    {
                        findReferences(oldDO, newDO, obj->getDataObject(pl[i]), newObj->getDataObject(pl[i]));
                    }
                }
            }
        }
    }

    DataObjectPtr CopyHelper::findReference(DataObjectPtr oldDO, DataObjectPtr newDO, DataObjectPtr ref)
    {
        SDOString rootXPath = oldDO->objectToXPath();
        SDOString refXPath = ref->objectToXPath();

        DataObjectPtr newRef;
        if (refXPath.find(refXPath) == 0)
        {
            SDOString relXPath = refXPath.substr(rootXPath.length());
            if (relXPath == "")
                newRef = newDO;
            if (relXPath.find("/") == 0)
                relXPath = relXPath.substr(1);
            newRef = newDO->getDataObject(relXPath);
        }

        return newRef;
    }


}
};

