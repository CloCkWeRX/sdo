#ifndef _XMLHELPER_H_
#define _XMLHELPER_H_
#include "export.h"
#include "XMLDocument.h"
#include "RefCountingObject.h"

namespace commonj
{
	namespace sdo
	{
		///////////////////////////////////////////////////////////////////////////
		// XMLHelper
		///////////////////////////////////////////////////////////////////////////
		
		class XMLHelper : public RefCountingObject
		{
		public:
			
			///////////////////////////////////////////////////////////////////////
			// load
			//
			// De-serializes the specified XML file building a graph of DataObjects.
			// Returns a pointer to the root data object
			///////////////////////////////////////////////////////////////////////
			SDO_API virtual XMLDocumentPtr loadFile(
				const char* xmlFile,
				const char* targetNamespaceURI=0) = 0;
			SDO_API virtual XMLDocumentPtr load(
				std::istream& inXml,
				const char* targetNamespaceURI=0) = 0;
			SDO_API virtual XMLDocumentPtr load(
				const char* inXml,
				const char* targetNamespaceURI=0) = 0;
			
			///////////////////////////////////////////////////////////////////////
			// save - Serializes the datagraph to the XML file
			///////////////////////////////////////////////////////////////////////
			SDO_API virtual void	save(XMLDocumentPtr doc, const char* xmlFile) = 0;				
			SDO_API virtual void save(
				DataObjectPtr dataObject,
				const char* rootElementURI,
				const char* rootElementName,
				const char* xmlFile) = 0;
			
			
			///////////////////////////////////////////////////////////////////////
			// save - Serializes the datagraph to a stream
			///////////////////////////////////////////////////////////////////////
			SDO_API virtual void save(XMLDocumentPtr doc, std::ostream& outXml) = 0;
			SDO_API virtual void save(
				DataObjectPtr dataObject,
				const char* rootElementURI,
				const char* rootElementName,
				std::ostream& outXml) = 0;
			
			///////////////////////////////////////////////////////////////////////
			// save - Serializes the datagraph to a string
			///////////////////////////////////////////////////////////////////////
			SDO_API virtual char* save(XMLDocumentPtr doc) = 0;
			SDO_API virtual char* save(
				DataObjectPtr dataObject,
				const char* rootElementURI,
				const char* rootElementName) = 0;
			
			///////////////////////////////////////////////////////////////////////
			// createDocument 
			///////////////////////////////////////////////////////////////////////
			SDO_API virtual XMLDocumentPtr createDocument(
				DataObjectPtr dataObject,
				const char* rootElementURI,
				const char* rootElementName) = 0;
			
			///////////////////////////////////////////////////////////////////////
			// Destructor
			///////////////////////////////////////////////////////////////////////
			SDO_API virtual ~XMLHelper();
			
		};
	} // End - namespace sdo
} // End - namespace commonj

#endif //_XMLHELPER_H_
