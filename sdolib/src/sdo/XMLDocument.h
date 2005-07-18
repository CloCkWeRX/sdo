#ifndef _XMLDOCUMENT_H_
#define _XMLDOCUMENT_H_

#include "export.h"

#include "DataObject.h"

namespace commonj
{
	namespace sdo
	{
		
		class XMLDocument : public RefCountingObject
		{
			
		public:
			
			SDO_API virtual ~XMLDocument();
			
			SDO_API virtual DataObjectPtr getRootDataObject() const = 0;
			SDO_API virtual const char* getRootElementURI() const = 0;
			SDO_API virtual const char* getRootElementName() const = 0;
			SDO_API virtual const char* getEncoding() const = 0;
			SDO_API virtual void setEncoding(const char* encoding) = 0;
			SDO_API virtual bool getXMLDeclaration() const = 0;
			SDO_API virtual void setXMLDeclaration(bool xmlDeclaration) = 0;
			SDO_API virtual const char* getXMLVersion() const = 0;
			SDO_API virtual void setXMLVersion(const char* xmlVersion) = 0;
			SDO_API virtual const char* getSchemaLocation() const = 0;
			SDO_API virtual void setSchemaLocation(const char* schemaLocation) = 0;
			SDO_API virtual const char* getNoNamespaceSchemaLocation() const = 0;
			SDO_API virtual void setNoNamespaceSchemaLocation(const char* noNamespaceSchemaLocation) = 0;		
			
			SDO_API friend std::istream& operator>>(std::istream& input, XMLDocument& doc);
			
		};
	} // End - namespace sdo
} // End - namespace commonj


#endif //_XMLDOCUMENT_H_
