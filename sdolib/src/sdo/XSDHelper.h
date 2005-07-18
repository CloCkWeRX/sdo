#ifndef _XSDHELPER_H_
#define _XSDHELPER_H_

#include "export.h"
#include "RefCountingObject.h"
#include "DataFactory.h"

namespace commonj
{
	namespace sdo
	{
		
		///////////////////////////////////////////////////////////////////////////
		// XSDHelper
		///////////////////////////////////////////////////////////////////////////
		
		class XSDHelper : public RefCountingObject
		{
		public:
			
			///////////////////////////////////////////////////////////////////////
			// define/defineFile
			//
			// Populates the data factory with Types and Properties from the schema
			// Loads from file, stream or char* buffer.
			// The return value is the URI of the root Type
			///////////////////////////////////////////////////////////////////////
			SDO_API virtual const char* defineFile(const char* schemaFile) = 0;
			SDO_API virtual const char* define(std::istream& schema) = 0;
			SDO_API virtual const char* define(const char* schema) = 0;
			

			SDO_API virtual char* generate(
				const TypeList& types,
				const char* targetNamespaceURI = "") = 0;
			SDO_API virtual void generate(
				const TypeList& types,
				std::ostream& outXsd,
				const char* targetNamespaceURI = "") = 0;
			SDO_API virtual void generateFile(
				const TypeList& types,
				const char* fileName,
				const char* targetNamespaceURI = "") = 0;

			///////////////////////////////////////////////////////////////////////
			// Destructor
			///////////////////////////////////////////////////////////////////////
			SDO_API virtual ~XSDHelper();

			// Return the DataFactory
			SDO_API virtual DataFactoryPtr getDataFactory() = 0;

			// Return the URI for the root Type
			SDO_API virtual const char* getRootTypeURI() = 0;
			
		};
	} // End - namespace sdo
} // End - namespace commonj

#endif //_XSDHELPER_H_
