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

/* $Rev: 509991 $ $Date$ */

#include "commonj/sdo/SDOXMLWriter.h"
#include "commonj/sdo/SDOXMLString.h"
#include "commonj/sdo/SDOString.h"
#include "iostream"
using namespace::std;
#include "commonj/sdo/DASProperty.h"
#include "commonj/sdo/XSDPropertyInfo.h"
#include "commonj/sdo/XSDTypeInfo.h"
#include "commonj/sdo/ChangeSummary.h"
#include "commonj/sdo/Sequence.h"
#include "commonj/sdo/SDORuntimeException.h"
#include "commonj/sdo/XMLQName.h"
#include "commonj/sdo/DataObjectImpl.h"
#include "commonj/sdo/DataFactoryImpl.h"
#include "commonj/sdo/PropertySetting.h"

namespace commonj
{
    namespace sdo
    {
        
        const SDOXMLString SDOXMLWriter::s_xsi("xsi");
        const SDOXMLString SDOXMLWriter::s_type("type");
        const SDOXMLString SDOXMLWriter::s_nil("nil");
        const SDOXMLString SDOXMLWriter::s_true("true");
        const SDOXMLString SDOXMLWriter::s_xsiNS("http://www.w3.org/2001/XMLSchema-instance");
        const SDOXMLString SDOXMLWriter::s_xmlns("xmlns");
        const SDOXMLString SDOXMLWriter::s_commonjsdo("commonj.sdo");

        
        
        SDOXMLWriter::SDOXMLWriter(
            DataFactoryPtr dataFact)
            : dataFactory(dataFact)
        {
            
        }
        
        SDOXMLWriter::~SDOXMLWriter()
        {
            freeWriter();
        }
        
        void SDOXMLWriter::setWriter(xmlTextWriterPtr textWriter)
        {
            writer = textWriter;
        }
        
        void SDOXMLWriter::freeWriter()
        {
            if (writer != NULL)
            {
                xmlFreeTextWriter(writer);
                writer = NULL;
            }
        }
        
        int SDOXMLWriter::write(XMLDocumentPtr doc, int indent)
        {
            if (!doc)
            {
                return 0;
            }
            
            if (writer == NULL)
            {
                // Throw exception
                return -1;
            }
            
            int rc = 0;
            namespaceMap.empty();
            
            if (indent >= 0)
            {
                xmlTextWriterSetIndent(writer, 1);
                if (indent > 0)
                {
                    char * chars = new char[indent+1];
                    for (int i=0;i<indent;i++)chars[i] = ' ';
                    chars[indent] = 0;
                    xmlTextWriterSetIndentString(writer, SDOXMLString(chars));
                    delete[] chars;
                }
                else
                {
                    xmlTextWriterSetIndentString(writer, SDOXMLString(""));
                }
            }
            
            if (doc->getXMLDeclaration())
            {
                rc = xmlTextWriterStartDocument(writer, doc->getXMLVersion(), doc->getEncoding(), NULL);
                if (rc < 0) {
                    SDO_THROW_EXCEPTION("write", SDOXMLParserException, "xmlTextWriterStartDocument failed");
                }
            }
            
            DataObjectPtr root = doc->getRootDataObject();
            if (root)
            {
                const Type& rootType = root->getType();
                SDOXMLString rootTypeURI = rootType.getURI();
                SDOXMLString rootTypeName = rootType.getName();
                
                // For the root DataObject we need to determine the element name
                SDOXMLString elementURI = doc->getRootElementURI();
                if (elementURI.isNull() || elementURI.equals(""))
                {
                    elementURI = rootTypeURI;
                }
                SDOXMLString elementName = doc->getRootElementName();
                if (elementName.isNull() || elementName.equals(""))
                {
                    elementName = rootTypeName;
                    elementName = elementName.toLower(0,1);
                }
                
                // If the element name is defined as a global element then we
                // can supress the writing of xsi:type according to the spec
                bool writeXSIType = true;

                try
                {
                    // Locate the RootType
                    const Type& rootTy = dataFactory->getType(elementURI, "RootType");
                    // Does a property exist with the given element name?
                    const Property& rootProp = rootTy.getProperty((const char*)elementName);
                    // Is this property of the correct Type?
                    const Type& rootPropType = rootProp.getType();
                    if (rootTypeURI == (SDOXMLString)rootPropType.getURI()
                        && rootTypeName == (SDOXMLString)rootPropType.getName())
                    {
                        writeXSIType = false;
                    }
                }
                catch(SDORuntimeException&)
                {
                }
                
                // Supress the writing of xsi:type as well for DataObjects of type
                // commonj.sdo#OpenDataObject
                if (writeXSIType &&
                    rootTypeURI.equals("commonj.sdo") && rootTypeName.equals("OpenDataObject"))
                {
                    writeXSIType = false;
                }

                writeDO(root, elementURI, elementName, writeXSIType, true);
            }
            rc = xmlTextWriterEndDocument(writer);
            if (rc < 0) {
                    SDO_THROW_EXCEPTION("write", SDOXMLParserException, "xmlTextWriterEndDocument failed");
                return rc;
            }
            
            xmlTextWriterFlush(writer);
            freeWriter();
            
            return rc;
        }

        //////////////////////////////////////////////////////////////////////////
        // Write Change Summary attributes
        //////////////////////////////////////////////////////////////////////////

        void SDOXMLWriter::handleChangeSummaryAttributes(
            ChangeSummaryPtr cs, 
            DataObjectPtr dol)
        {
            int rc;

            SettingList& sl = cs->getOldValues(dol);
            if (sl.size() == 0) 
            {
                // no attributes
                return;
            }
            for (int j=0;j< sl.size(); j++)
            {
                try {

                    if (sl.get(j)->getProperty().isMany()) 
                    {
                        // manys are elements
                        continue;
                    }
        
                    if (sl.get(j)->getProperty().getType().isDataType())
                    {
                        // data types are OK
                        rc = xmlTextWriterWriteAttribute(writer, 
                            SDOXMLString(sl.get(j)->getProperty().getName()),
                            SDOXMLString(sl.get(j)->getCStringValue()));
                    }
                    else 
                    {
                        DataObjectPtr dob = sl.get(j)->getDataObjectValue();
                        if (dob) 
                        {
                            if (cs->isDeleted(dob))
                            {
                            rc = xmlTextWriterWriteAttribute(writer, 
                                SDOXMLString(sl.get(j)->getProperty().getName()),
                                SDOXMLString(cs->getOldXpath(dob)));
                            }
                            else 
                            {
                            rc = xmlTextWriterWriteAttribute(writer, 
                                SDOXMLString(sl.get(j)->getProperty().getName()),
                                SDOXMLString(dob->objectToXPath()));
                            }
                        }
                        else
                        {
                            rc = xmlTextWriterWriteAttribute(writer, 
                                SDOXMLString(sl.get(j)->getProperty().getName()),
                                SDOXMLString(""));
                        }
                    }
                }
                catch (SDORuntimeException e)
                {
                    // ignore this attribute
                }
            }
        }

        //////////////////////////////////////////////////////////////////////////
        // Write  Change Summary elements
        //////////////////////////////////////////////////////////////////////////
        
        void SDOXMLWriter::handleChangeSummaryElements(
            ChangeSummaryPtr cs, 
            DataObjectPtr dob)
        {
            int rc;

            SettingList& sl = cs->getOldValues(dob);
        
            if (sl.size() == 0) 
            {
            // there are no setting for this element.
            return;
            }
            
            for (int j=0;j< sl.size(); j++)
            {
                try 
                {

                    // single values will have been covered by the attributes.
                    if (!sl.get(j)->getProperty().isMany()) continue;
        
                    if (sl.get(j)->getProperty().getType().isDataType())
                    {

                        rc = xmlTextWriterWriteElement(
                            writer,
                            SDOXMLString(sl.get(j)->getProperty().getName()),
                            SDOXMLString(sl.get(j)->getCStringValue()));
                            
                    } // if datatype
                    else
                    {
                        DataObjectPtr dob2 = sl.get(j)->getDataObjectValue();
                        if (!dob2) 
                        {
                            continue;
                        }
                        if (cs->isDeleted(dob2))
                        {
                            handleChangeSummaryDeletedObject(sl.get(j)->getProperty().getName(), cs,dob2);
                        }
                        else
                        {
                            rc = xmlTextWriterStartElement(
                                writer,
                                SDOXMLString(sl.get(j)->getProperty().getName()));
                            rc = xmlTextWriterWriteAttribute(
                                writer,
                                SDOXMLString("sdo:ref"),
                                SDOXMLString(dob2->objectToXPath()));
                            rc = xmlTextWriterEndElement(
                                writer);
                        }
                    } 
                }
                catch (SDORuntimeException e)
                {
                    // ignore this element
                }
            } // for
        } 


        //////////////////////////////////////////////////////////////////////////
        // Write a deleted object and all its props
        //////////////////////////////////////////////////////////////////////////

        void SDOXMLWriter::handleChangeSummaryDeletedObject(
            const char* name, 
            ChangeSummaryPtr cs, 
            DataObjectPtr dob)
        {
        
            int rc, k; // TODO error handling
        
            SettingList& sl = cs->getOldValues(dob);
        
            rc = xmlTextWriterStartElement(
                writer,
                SDOXMLString(name));

            if (sl.size() == 0) 
            {
                rc = xmlTextWriterWriteAttribute(writer, 
                    SDOXMLString("sdo:ref"),
                    SDOXMLString(cs->getOldXpath(dob)));
                rc = xmlTextWriterEndElement(writer);
                return;
            }

        
            try 
            {
                // print single valued datatypes as attributes
        
                for (int j=0;j< sl.size(); j++)
                {
                    //if (!sl.get(j)->isSet()) 
                    //{
                    //    // unset properties dont need recording - ah but they do!
                    //
                    //    continue;
                    //}
                    if ( sl.get(j)->getProperty().isMany()) 
                    {
                        // manys are elements
                        continue;
                    }
                    if (!sl.get(j)->getProperty().getType().isDataType())
                    {
                        // data objects are element in a deleted data object.
                        continue;
                    }

                    rc = xmlTextWriterWriteAttribute(writer, 
                        SDOXMLString(sl.get(j)->getProperty().getName()),
                        SDOXMLString(sl.get(j)->getCStringValue()));

                } // for attributes
        
    
                // now we are onto the many-values, 
                // and dataobject single values.
                // 
                // handle deletions within deletions in reverse order, so they match the
                // deletion records above.

                for (k=sl.size()-1;k>=0; k--)
                {

                     if ( !sl.get(k)->getProperty().getType().isDataType() &&
                          sl.get(k)->getProperty().isMany()) 
                    {
                        // its a dataobject type
                        DataObjectPtr dob2 = sl.get(k)->getDataObjectValue();
                        if (!dob2) continue;
                        if (!cs->isDeleted(dob2)) continue;
                        handleChangeSummaryDeletedObject(sl.get(k)->
                                   getProperty().getName(),cs,dob2);
                    }
                } // for attributes

                for (k=0;k< sl.size(); k++)
                {

                     if ( !sl.get(k)->getProperty().getType().isDataType())
                    {
                        if (sl.get(k)->getProperty().isMany()) continue; 
                        // its a single valued dataobject type

                        DataObjectPtr dob2 = sl.get(k)->getDataObjectValue();
                        if (!dob2) continue;
                        if (!cs->isDeleted(dob2)) continue;
                        handleChangeSummaryDeletedObject(sl.get(k)->
                                   getProperty().getName(),cs,dob2);

                    }
                    else 
                    {
                        if ( !sl.get(k)->getProperty().isMany()) continue; 
                        
                        // could only be many valued data type
        
                        rc = xmlTextWriterWriteElement(writer, 
                            SDOXMLString(sl.get(k)->getProperty().getName()),
                            SDOXMLString(sl.get(k)->getCStringValue()));
                    }
                } // for attributes
            }
            catch (SDORuntimeException e)
            {
                 // ignore - and write the end-element
            }

            rc = xmlTextWriterEndElement(writer);
        } 


        //////////////////////////////////////////////////////////////////////////
        // Write the list of elements of a change summary
        //////////////////////////////////////////////////////////////////////////

        void SDOXMLWriter::handleSummaryChange(
            const SDOXMLString& elementName, 
            ChangeSummaryPtr cs, 
            DataObjectPtr dob)
        {
            int rc; 
            DataObject* temp = dob;
            const char* name;
            try 
            {
                name = temp->getContainmentProperty().getName();
            }
            catch (SDORuntimeException e)
            {
                // This could be a root, and have no name.
                name = 0;
            }            

            if (name == 0) 
            {
            rc = xmlTextWriterStartElement(
                writer,
                elementName);
            }
            else
            {
            rc = xmlTextWriterStartElement(
                writer,
                SDOXMLString(name));
            }

            if (rc != 0)
            {
                // failed to write an element
                return;
            }

            try 
            {
                name =  temp->objectToXPath();
            }
            catch (SDORuntimeException e)
            {
                name = 0;
            }

            rc = xmlTextWriterWriteAttribute(writer, 
                SDOXMLString("sdo:ref"),
                SDOXMLString(name));

            handleChangeSummaryAttributes(cs, temp);

            handleChangeSummaryElements(cs, temp);

            rc = xmlTextWriterEndElement(writer);

        }

        //////////////////////////////////////////////////////////////////////////
        // Write a Change Summary
        //////////////////////////////////////////////////////////////////////////

        void SDOXMLWriter::handleChangeSummary(
            const SDOXMLString& elementName,
            ChangeSummaryPtr cs)
        {
            unsigned int i;
            int rc; 

            ChangedDataObjectList& changedDOs =  cs->getChangedDataObjects();
            rc = xmlTextWriterStartElementNS(writer,
                    SDOXMLString("sdo"), SDOXMLString("changeSummary"), SDOXMLString(Type::SDOTypeNamespaceURI.c_str()));
            if (rc != 0) return;
            if (cs->isLogging())
            {
                rc = xmlTextWriterWriteAttribute(writer, 
                    SDOXMLString("logging"),
                    SDOXMLString("true"));
            }

            if (changedDOs.size() > 0)
            {

                // write the creates/deletes in the order they
                // happened, as elements.

                for (i=0;i< changedDOs.size();i++)
                {
                    if  (cs->isCreated(changedDOs[i])
                        && changedDOs.getType(i) == ChangedDataObjectList::Create) 
                    {
                        // TODO - should work out if theres a IDREF here
                        // TODO - can we have more than one create like this?
                        try
                        {
                            rc = xmlTextWriterWriteElement(writer, 
                            SDOXMLString("create"),
                            SDOXMLString(changedDOs[i]->objectToXPath()));
                        }
                        catch (SDORuntimeException e)
                        {
                                // The object was not in our tree - we ignore it.
                        }
                    }
                    if  (cs->isDeleted(changedDOs[i])
                        && changedDOs.getType(i) == ChangedDataObjectList::Delete) 
                    {
                        // TODO - should work out if theres a IDREF here
                        try 
                        {
                            rc = xmlTextWriterWriteElement(writer, 
                            SDOXMLString("delete"),
                            SDOXMLString(cs->getOldXpath(changedDOs[i])));
                        }
                        catch (SDORuntimeException e)
                        {
                            // The object was not in the deleted list - we ignore it.
                        }
                    }
                }

            
                for (i=0;i< changedDOs.size();i++)
                {
                    if (cs->isModified(changedDOs[i]))
                    {
                        handleSummaryChange(elementName, cs, changedDOs[i]);
                    }
                }
                        
            }
            rc = xmlTextWriterEndElement(writer);
        }
        
        //////////////////////////////////////////////////////////////////////////
        // Add to namespaces
        //////////////////////////////////////////////////////////////////////////
        
        void  SDOXMLWriter::addToNamespaces(DataObjectImpl* dob)
        {
            std::map<SDOXMLString,SDOXMLString>::iterator it;
            SDOXMLString uri = dob->getType().getURI();
            SDOXMLString typeName = dob->getType().getName();
            if (!(uri.equals("commonj.sdo") && typeName.equals("OpenDataObject")))
            {
                it = namespaceMap.find(uri);
                if (it == namespaceMap.end())
                {
                    char buf[20];
                    sprintf(buf,"%d",++spacescount);
                    SDOXMLString s = SDOXMLString("tns") + buf;
                    namespaceMap.insert(make_pair(uri,s));
                }
            }

            PropertyList pl = dob->getInstanceProperties();
            for (unsigned int i = 0; i < pl.size(); i++)
            {
                if (!dob->isSet(pl[i]))continue;

                if  (pl[i].isMany())
                {
                    if (!pl[i].getType().isDataType())
                    {
                        DataObjectList& dl = dob->getList(pl[i]);
                        for (unsigned int k=0;k< dl.size() ;k++)
                        {
                            DataObjectImpl* d = (DataObjectImpl*)(DataObject*)dl[k];
                            if (d != 0)addToNamespaces(d);
                        }
                    }
                }
                else
                {
                    if (!pl[i].getType().isDataType())
                    {
                        DataObjectImpl* d = (DataObjectImpl*)(DataObject*)dob->getDataObject(pl[i]);
                        if (d != 0)addToNamespaces(d);
                    }
                    else
                    {                    
                        XSDPropertyInfo* pi = getPropertyInfo(pl[i]);
                        if (pi)
                        {
                            PropertyDefinitionImpl propdef;
                            propdef = pi->getPropertyDefinition();
                            if (propdef.isElement)continue;
                            if (!propdef.isQName)continue;
                 
                            SDOXMLString propertyValue = (dob->getCString(pl[i]));
                            XMLQName qname(propertyValue);
                            
                            SDOXMLString qnameuri = qname.getURI(); 
                            if (qnameuri.equals("") || qnameuri.isNull() )
                            {
                                continue;
                            }

                            it = namespaceMap.find(qnameuri);
                            if (it == namespaceMap.end())
                            {
                                char buf[20];
                                sprintf(buf,"%d",++spacescount);
                                SDOXMLString s = SDOXMLString("tns") + buf;
                                namespaceMap.insert(make_pair(qnameuri,s));
                            }
                        }
                    }
                }
            }
        }
            
 
        
        /**
         *  WriteDO - write a DataObject tree
         *  
         */
        
        int SDOXMLWriter::writeDO(
            DataObjectPtr dataObject,
            const SDOXMLString& elementURI,
            const SDOXMLString& elementName,
            bool writeXSIType,
            bool isRoot)
        {

            int rc;

            if (dataObject == 0)
                return 0;          

            const Type& dataObjectType = dataObject->getType();
            SDOXMLString typeURI = dataObjectType.getURI();
            SDOXMLString typeName = dataObjectType.getName();
            bool isOpen = dataObjectType.isOpenType();
            DataObjectImpl* dataObjectImpl = (DataObjectImpl*)(DataObject*)dataObject;
            const TypeImpl& typeImpl = dataObjectImpl->getTypeImpl();


            // ---------------------------------------
            // First we need to write the startElement                      
            if (isRoot)
            {
                if (elementURI.equals(s_commonjsdo))
                {
                    tnsURI = "";
                }
                else
                {
                    tnsURI = elementURI;
                }

                if (tnsURI.equals("")) {
                    rc = xmlTextWriterStartElementNS(writer, NULL, elementName, NULL);
                }
                else
                {
                    rc = xmlTextWriterStartElementNS(writer, NULL, elementName, elementURI);
                }
                if (rc < 0) {
                    SDO_THROW_EXCEPTION("writeDO", SDOXMLParserException, "xmlTextWriterStartElementNS failed");
                }

                // For the root element we will now gather all the namespace information
                namespaceMap[elementURI] = SDOXMLString("tns");

                // We always add the xsi namespace. TODO we should omit if we can
                namespaceMap[s_xsiNS] = s_xsi;

                DataObjectImpl* d = (DataObjectImpl*)(DataObject*)dataObject;
                spacescount = 1;
                addToNamespaces(d);
            }
            else
            {
                // Write the startElement for non-root object
                SDOXMLString theName=elementName;

                if (!elementURI.isNull() 
                    && !elementURI.equals("")
                    && !elementURI.equals(s_commonjsdo)
                    && !elementURI.equals(tnsURI))
                {
                    // Locate the namespace prefix
                    std::map<SDOXMLString,SDOXMLString>::iterator it = namespaceMap.find(elementURI);
                    if (it != namespaceMap.end())
                    {
                        theName = (*it).second;
                        theName += ":";
                        theName += elementName;
                    }
                }

                rc = xmlTextWriterStartElement(writer, theName);
                if (rc < 0) {
                    SDO_THROW_EXCEPTION("writeDO", SDOXMLParserException, "xmlTextWriterStartElement failed");
                }   
            }
            // End - startElement is written
            // -----------------------------


            // -------------------------------------------
            // For a primitive type - just write the value
            if (dataObjectType.isDataType())
            {
                if (dataObject->isNull(""))
                {
                    rc = xmlTextWriterWriteAttributeNS(writer, s_xsi, s_nil, NULL, s_true);
                }
                else
                {
                    /* Use our wrapper function just in case the element has CDATA in it */
                    writeXMLElement(writer,
                        elementName,
                        dataObject->getCString(""));
                }

                // Write the end element and return
                rc = xmlTextWriterEndElement(writer);
                return 0;
            }
            // End - primitive value is written
            // --------------------------------
             

            //-------------------------------------------
            // Write the xsi:type= attribute if necessary
            if (writeXSIType)
            {
                // Supress the writing of xsi:type as well for DataObjects of type
                // commonj.sdo#OpenDataObject
                if (typeURI.equals("commonj.sdo") && typeName.equals("OpenDataObject"))
                {
                }
                else
                {
                    SDOXMLString theName=typeName;

                    if (!typeURI.isNull() && !typeURI.equals(tnsURI) && !typeURI.equals(""))
                    {
                        std::map<SDOXMLString,SDOXMLString>::iterator it = namespaceMap.find(typeURI);
                        if (it != namespaceMap.end())
                        {
                            theName = (*it).second;
                            theName += ":";
                            theName += typeName;
                        }
                    }

                    rc = xmlTextWriterWriteAttributeNS(writer, 
                        s_xsi, s_type, 
                        NULL,
                        theName);
                }
            }
            // End - xsi:type= attribute is written
            // ------------------------------------


            // -------------------------------
            // Write the namespace information
            if (isRoot)
            {
                // Now write all the namespace information
                for (std::map<SDOXMLString,SDOXMLString>::iterator it = namespaceMap.begin();
                     it != namespaceMap.end(); ++it)
                {
                    if ((*it).first.equals("")) continue;
                    rc = xmlTextWriterWriteAttributeNS(writer, s_xmlns, (*it).second, NULL, (*it).first);
                }
            }
            // End - namespave information is written
            // --------------------------------------


            // ---------------------
            // write nil if required
            if (dataObject->isNull(""))
            {
                rc = xmlTextWriterWriteAttributeNS(writer, s_xsi, s_nil, NULL, s_true);
            }
            // xsi:nil is written
            // ------------------


            // --------------------------------------------------
            // Iterate over all the properties to find attributes
            unsigned int i;
            unsigned int j = 1;
            PropertyList pl = dataObject->getInstanceProperties();
            for (i = 0; i < pl.size(); i++)
            {
                if (dataObject->isSet(pl[i]))
                {                    
                    SDOXMLString propertyName(pl[i].getName());
                    XSDPropertyInfo* pi = getPropertyInfo(pl[i]);
                    PropertyDefinitionImpl propdef;
                    if (pi)
                    {
                        propdef = pi->getPropertyDefinition();
                        propertyName = propdef.localname;
                    }
                    
                    // Elements are written as <element>
                    if (propdef.isElement)
                        continue;
                    
                    // Many-valued properties are written as <element>
                    if (pl[i].isMany())
                        continue;

                    // Non contained properties become attributes
                    const Type& propertyType = pl[i].getType();
                    
                    if (propertyType.isDataType())
                    {
                        SDOXMLString propertyValue = (dataObject->getCString(pl[i]));
                        if (pi && pi->getPropertyDefinition().isQName)
                        {
                            XMLQName qname(propertyValue);
                             
                            // TODO:  this logic seems bad. We should already have the namespace in the map
                            std::map<SDOXMLString,SDOXMLString>::iterator it = namespaceMap.find(qname.getURI());
                            if (it != namespaceMap.end())
                            {
                              propertyValue = (*it).second + ":" + qname.getLocalName();
                            }
                            else 
                            {
                                char buffer[20];
                                SDOXMLString pref = "tnss";
                                sprintf(buffer, "%d", j++);
                                pref += buffer;
                                rc = xmlTextWriterWriteAttributeNS(writer, s_xmlns, pref, NULL, qname.getURI());
                                propertyValue = pref + ":" + qname.getLocalName();
                            }
                            
                        }
                        rc = xmlTextWriterWriteAttribute(writer, 
                            propertyName, propertyValue);
                    }
                    else
                    {
                        // Handle non-containment reference to DataObject
                        if (pl[i].isReference())
                        {
                            writeReference(propertyName, dataObject, pl[i], false);
                        }
                    }
                }
            }
            // End - attributes are written
            // ----------------------------

            
            // --------------------
            // Handle ChangeSummary
            if (dataObject->getType().isChangeSummaryType())
            {
                ChangeSummaryPtr changeSummary = dataObject->getChangeSummary();
                if (changeSummary)
                {
                    handleChangeSummary(elementName, changeSummary);
                }
            }
            // End - ChangeSummary is written
            // ------------------------------
            

            // --------------------
            // Write Sequenced Data
            if (dataObjectType.isSequencedType())
            {
                SequencePtr sequence  = dataObject->getSequence();
                if (sequence)
                {
                    for (i=0; i<sequence->size(); i++)
                    {                       
                        if (sequence->isText(i))
                        {
                            // This is a raw write rather than xmlTextWriterWriteString
                            // just in case the text has a CDATA section in it 
                            rc = xmlTextWriterWriteRaw(
                                writer,
                                SDOXMLString(sequence->getCStringValue(i)));
                            continue;
                        } // end TextType

                        const Property& seqProp = sequence->getProperty(i);
                        const Type& seqPropType = seqProp.getType();
                        SDOXMLString seqPropName;
                        SDOXMLString seqPropURI;

                        // This call sets the property name and type URI and returns if xsi:type= is required
                        bool xsiTypeNeeded = determineNamespace(dataObject, seqProp, seqPropURI, seqPropName);

                        // Do not write attributes as members of the sequence
						XSDPropertyInfo* pi = getPropertyInfo(seqProp);
                        PropertyDefinitionImpl propdef;
                        if (pi)
                        {
                            propdef = pi->getPropertyDefinition();
                            if (!(propdef.isElement))
                            {
                                continue;
                            }
                        }

	
                        if (seqPropType.isDataObjectType())
                        {                                
                            DataObjectPtr doValue = sequence->getDataObjectValue(i);

                            if (doValue)
                            {
                                // Handle non-containment reference to DataObject
                                if (seqProp.isReference())
                                {
                                    writeReference(seqPropName, dataObject, seqProp, true, doValue);
                                }
                                else
                                {
                                    // If property is an undeclared propery of an open type
                                    // we write xsi:type
                                    bool xsiTypeNeeded = false;
                                    if (isOpen)
                                    {
                                        if (typeImpl.getPropertyImpl(seqPropName) == 0)
                                        {
                                            xsiTypeNeeded = true;
                                        }
                                    }
	
                                    writeDO(doValue, seqPropURI, seqPropName, xsiTypeNeeded);
                                }
                            }
                        } // end DataObject

                        else
                        {
                            // Sequence member is a primitive
							// Only write a primitive as an element if defined by the schema or if it's
							// many-valued.
							if (!pi && !seqProp.isMany()) continue;

                            /* Use our wrapper function just in case the element has CDATA in it */
                            xmlTextWriterStartElement(writer, seqPropName);
                            writeXMLElement(writer,
                                    seqPropName,
                                    sequence->getCStringValue(i));
                            xmlTextWriterEndElement(writer);
                            
                        } // end DataType
                    } // end - iterate over sequence

                }
            }
            // End = sequenced data is written
            // -------------------------------


            // ------------------------
            // Non-sequenced DataObject
            else
            {
                // ------------------------------------------------
                // Iterate over all the properties to find elements
                for (i = 0; i < pl.size(); i++)
                {
                    if (dataObject->isSet(pl[i]))
                    {
                        SDOXMLString propertyName;
                        SDOXMLString propertyTypeURI;

                        // This call sets the property name and type URI and returns if xsi:type= is required
                        bool xsiTypeNeeded = determineNamespace(dataObject, pl[i], propertyTypeURI, propertyName);
                       
                        const Type& propertyType = pl[i].getType();
						XSDPropertyInfo* pi = getPropertyInfo(pl[i]);
                        PropertyDefinitionImpl propdef;
                        if (pi)
                        {
                            propdef = pi->getPropertyDefinition();
                            if (!(propdef.isElement))
                            {
                                continue;
                            }
                        }

                        // -------------------------------------------------
                        // For a many-valued property get the list of values
                        if (pl[i].isMany())
                        {
                            DataObjectList& dol = dataObject->getList(pl[i]);
                            for (unsigned int j = 0; j <dol.size(); j++)
                            {
                                // Handle non-containment reference to DataObject
                                if (pl[i].isReference() )
                                {
                                    writeReference(propertyName, dataObject, pl[i], true, dol[j]);
                                }
                                else
                                {    
                                    writeDO(dol[j], propertyTypeURI, propertyName, xsiTypeNeeded);
                                }
                            }
                        } 
                        // End - write many valued property
                        // --------------------------------

                        
                        // -----------------------------
                        // For a dataobject write the do
                        else if (!propertyType.isDataType())
                        {
                            // Handle non-containment reference to DataObject
                            if (pl[i].isReference())
                            {
                                if (pi)
                                    writeReference(propertyName, dataObject, pl[i], true);
                            }
                            else
                            {
                                DataObjectPtr propDO = dataObject->getDataObject(pl[i]);                
                                writeDO(propDO, propertyTypeURI, propertyName, xsiTypeNeeded);
                            }
                        }
                        // End - write DataObject
                        // ----------------------

                        
                        // ---------------
                        // For a primitive
                        else
                        {
                            // Only write a primitive as an element if defined by the XSD
                            if (pi)
                            {
                                const Type& tp = dataObject->getType();
                                XSDTypeInfo* typeInfo = (XSDTypeInfo*)
                                    ((DASType*)&tp)->getDASValue("XMLDAS::TypeInfo");
                                if (typeInfo && typeInfo->getTypeDefinition().isExtendedPrimitive)
                                {
                                    xmlTextWriterWriteRaw(
                                    writer,
                                    SDOXMLString(dataObject->getCString(pl[i])));
                                }
                                else
                                {
                                    rc = xmlTextWriterStartElementNS(writer, NULL, propertyName, NULL);
                                    if (dataObject->isNull(pl[i]))
                                    {
                                        rc = xmlTextWriterWriteAttributeNS(writer, s_xsi, s_nil, NULL, s_true);
                                    }
                                    else
                                    {
                                        writeXMLElement(writer,
                                            propertyName,
                                            dataObject->getCString(pl[i]));
                                    }
                                    rc = xmlTextWriterEndElement(writer);
                                }
                            }
                        }
                        // End - handle primitive
                        // ----------------------

                    } // end isSet
                }
                // End - elements are written
                // --------------------------

            }
            // End - non-sequenced DO
            // ----------------------

            rc = xmlTextWriterEndElement(writer);
            return rc;

        } // End - writeDO


        
        XSDPropertyInfo* SDOXMLWriter::getPropertyInfo(const Property& property)
        {
            return (XSDPropertyInfo*)((DASProperty*)&property)->getDASValue("XMLDAS::PropertyInfo");       
        }


        void SDOXMLWriter::writeReference(
            const SDOXMLString& propertyName,
            DataObjectPtr dataObject, 
            const Property& property,
            bool isElement,
            DataObjectPtr refferedToObject)
        {
            DataObjectPtr reffedObject = refferedToObject;
            if (reffedObject == 0)
            {
                reffedObject = dataObject->getDataObject(property);
            }

            // Get ID from referred to DataObject or use XPath
            SDOXMLString refValue;
            XSDTypeInfo* ti = (XSDTypeInfo*)((DASType*)&reffedObject->getType())->
                getDASValue("XMLDAS::TypeInfo");
            if (ti)
            {
                TypeDefinitionImpl typeDef = ti->getTypeDefinition();
                if (!typeDef.IDPropertyName.isNull())
                {
                    refValue = reffedObject->getCString((const char*)typeDef.IDPropertyName);
                }
            }
            
            if (refValue.isNull())
            {
                // need to get XPATH
                refValue = ((DataObjectImpl*)(DataObject*)reffedObject)->objectToXPath();
            }
            
            if (!refValue.isNull())
            {
                if (isElement)
                {
                    // Set the IDREF value
                    xmlTextWriterWriteElement(writer, 
                        propertyName, refValue);
                }
                else
                {
                    // Set the IDREF value
                    xmlTextWriterWriteAttribute(writer, 
                        propertyName, refValue);
                }
            }
        }    



      /**
       * A wrapper for the libxml2 function xmlTextWriterWriteElement
       * it detects CDATA sections before writing out element contents
       */
      int SDOXMLWriter::writeXMLElement(xmlTextWriterPtr writer, 
                                        const SDOXMLString& name, 
                                        const SDOXMLString& content)
      {
        int rc = 0;
        rc = xmlTextWriterWriteRaw(writer, content);

        /* A more complex version that doesn't work!
         * I've left it here just in case we need to go back and separate out
         * CDATA from text. This might provide a starting point
           SDOString contentString(content);

           // write the start of the element. we could write a mixture of
           // text and CDATA before writing the end element
           rc = xmlTextWriterStartElement(writer, name);

           // Iterate along the string writing out text and CDATA sections 
           // separately using the appropriate libxml2 calls
           std::string::size_type start  = 0;
           std::string::size_type end    = contentString.find(PropertySetting::XMLCDataStartMarker, 0);
           std::string::size_type length = 0;
                        
           // loop while we still find a CDATA section that needs writing
           while ( end != std::string::npos )
           {
           // write out text from current pos to start of CDATA section
           length = end - start;
           rc = xmlTextWriterWriteString(writer,
           SDOXMLString(contentString.substr(start, length).c_str()));

           // find the end of the CDATA section
           start = end;
           end   = contentString.find(PropertySetting::XMLCDataEndMarker, start);
           
           if ( end != std::string::npos )
           {
           // we only nudge the start marker on to the end of the CDATA marker here
           // so that if we fail to find the end CDATA marker the whole string gets 
           // printed out by virtue of the line that follows the while loop
           start = start + strlen(PropertySetting::XMLCDataStartMarker);
           
           // write our the text from the CDATA section
           length = end - start;
           rc = xmlTextWriterWriteCDATA(writer, 
           SDOXMLString(contentString.substr(start, length).c_str()));

           // set current pos to end of CDATA section and 
           // start looking for the start marker again
           start = end + strlen(PropertySetting::XMLCDataEndMarker);
           end   = contentString.find(PropertySetting::XMLCDataStartMarker, start);
           }
           } 

           // write out text following the last CDATA section
           rc = xmlTextWriterWriteString(writer,
           SDOXMLString(contentString.substr(start).c_str()));

           // close off the element
           rc = xmlTextWriterEndElement(writer);
        */
        return rc;
      }

      bool SDOXMLWriter::determineNamespace(DataObjectPtr dataObject, const Property& prop,
          SDOXMLString& elementURI, SDOXMLString& elementName)
      {
          bool xsiTypeNeeded = false;

          // If this is a defined property with property information
          // we use the uri and name from the definition
          XSDPropertyInfo* pi = getPropertyInfo(prop);
          PropertyDefinitionImpl propdef;
          if (pi)
          {
              propdef = pi->getPropertyDefinition();
              elementName = propdef.localname;
              elementURI = propdef.namespaceURI;
          }
          else
          {
              elementName = prop.getName();

              const Type& propertyType = prop.getType();
              SDOXMLString propTypeName = propertyType.getName();
              SDOXMLString propTypeURI = propertyType.getURI();
              DataObjectImpl* dataObjectImpl = (DataObjectImpl*)(DataObject*)dataObject;
              const TypeImpl& typeImpl = dataObjectImpl->getTypeImpl();
              

              // If property is an undeclared propery of an open type
              if (typeImpl.getPropertyImpl(prop.getName()) == 0)
              {
                  // we need to write xsi:type information
                  xsiTypeNeeded = true;

                  // Determine the namespace of the property
                  // First see if there is a matching property in the namespace
                  // of the Type of this property. 
                  DataFactoryImpl* df = (DataFactoryImpl*)dataObject->getDataFactory();
                  const TypeImpl* ti = df->findTypeImpl(propertyType.getURI(), "RootType");
                  if (ti)
                  {
                      PropertyImpl* propi = ti->getPropertyImpl(elementName);
                      if (propi)
                      {
                          SDOXMLString propiTypeName = propi->getType().getName();
                          SDOXMLString propiTypeURI = propi->getType().getURI();
                          if (propiTypeName.equals(propTypeName)
                              && propiTypeURI.equals(propTypeURI) )
                          {
                              // We have a match
                              XSDPropertyInfo* ppi = getPropertyInfo(*propi);
                              PropertyDefinitionImpl propdef;
                              if (ppi)
                              {
                                  propdef = ppi->getPropertyDefinition();
                                  elementName = propdef.localname;
                                  elementURI = propdef.namespaceURI;
                              }
                          }
                      }
                  }
                  else
                  {
                      // For now we will just set the elementURI to ""
                      // We need to check further here for the element defined in
                      // the namespace of the parent object etc. etc.
                      elementURI = "";
                  }
              }
              else
              {
                  // The property has been defined programatically so we will
                  // assume it is the namespace fo the parent DataObject
                  elementURI = typeImpl.getURI();
              }

          }

          return xsiTypeNeeded;

      }
        
    } // End - namespace sdo
} // End - namespace commonj


