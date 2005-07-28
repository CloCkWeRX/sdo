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
| Author: Pete Robbins                                                 | 
+----------------------------------------------------------------------+ 

*/
/* $Id$ */

#include "SAX2Parser.h"
#include "libxml/SAX2.h"
#include "SDORuntimeException.h"
using namespace commonj::sdo::xmldas;

// Callbacks from libxml to these C methods are converted into calls
// to the C++ equivalent (with some parameter manipulation
// In the callback methods the void* ctx is a pointer to 'this' SAX2Parser


int sdo_isStandalone(void *ctx)
{
	return 0;
}


int sdo_hasInternalSubset(void *ctx)
{
    return(0);
}

int sdo_hasExternalSubset(void *ctx)
{
    return(0);
}

void sdo_internalSubset(void *ctx, const xmlChar *name,
						const xmlChar *ExternalID, const xmlChar *SystemID)
{
}


void sdo_externalSubset(void *ctx, const xmlChar *name,
						const xmlChar *ExternalID, const xmlChar *SystemID)
{
}

xmlParserInputPtr sdo_resolveEntity(void *ctx, const xmlChar *publicId, const xmlChar *systemId)
{
    return(NULL);
}


xmlEntityPtr sdo_getEntity(void *ctx, const xmlChar *name)
{
    return(NULL);
}


xmlEntityPtr sdo_getParameterEntity(void *ctx, const xmlChar *name)
{
    return(NULL);
}


void sdo_entityDecl(void *ctx, const xmlChar *name, int type,
					const xmlChar *publicId, const xmlChar *systemId, xmlChar *content)
{
}


void sdo_attributeDecl(void *ctx, const xmlChar * elem,
					   const xmlChar * name, int type, int def,
					   const xmlChar * defaultValue, xmlEnumerationPtr tree)
{
}

void sdo_elementDecl(void *ctx, const xmlChar *name, int type,
					 xmlElementContentPtr content)
{
}


void sdo_notationDecl(void *ctx, const xmlChar *name,
					  const xmlChar *publicId, const xmlChar *systemId)
{
}

void sdo_unparsedEntityDecl(void *ctx, const xmlChar *name,
							const xmlChar *publicId, const xmlChar *systemId,
							const xmlChar *notationName)
{
}


void sdo_setDocumentLocator(void *ctx, xmlSAXLocatorPtr loc)
{
}


void sdo_startDocument(void *ctx)
{	
	if (!((SAX2Parser*)ctx)->parserError)
		((SAX2Parser*)ctx)->startDocument();
}


void sdo_endDocument(void *ctx)
{
	if (!((SAX2Parser*)ctx)->parserError)
		((SAX2Parser*)ctx)->endDocument();
}


void sdo_startElement(void *ctx, const xmlChar *name, const xmlChar **atts)
{
	//	((SAX2Parser*)ctx)->startElement(name, atts);
}


void sdo_endElement(void *ctx, const xmlChar *name)
{
	//	((SAX2Parser*)ctx)->endElement(name);
}


void sdo_characters(void *ctx, const xmlChar *ch, int len)
{
	if (!((SAX2Parser*)ctx)->parserError)
		((SAX2Parser*)ctx)->characters(SDOXMLString(ch, 0, len));
}


void sdo_reference(void *ctx, const xmlChar *name)
{
}


void sdo_ignorableWhitespace(void *ctx, const xmlChar *ch, int len)
{
}


void sdo_processingInstruction(void *ctx, const xmlChar *target,
							   const xmlChar *data)
{
}


void sdo_cdataBlock(void *ctx, const xmlChar *value, int len)
{
}

void sdo_comment(void *ctx, const xmlChar *value)
{
}


void sdo_warning(void *ctx, const char *msg, ...)
{
}

void sdo_error(void *ctx, const char *msg, ...)
{
	va_list args;
    va_start(args, msg);
	((SAX2Parser*)ctx)->error(msg, args);
    va_end(args);
	
	
}

void sdo_fatalError(void *ctx, const char *msg, ...)
{
	va_list args;
    va_start(args, msg);
	((SAX2Parser*)ctx)->fatalError(msg, args);
    va_end(args);
}



// ===============
// SAX2 callbacks
// ===============
void sdo_startElementNs(void *ctx,
						const xmlChar *localname,
						const xmlChar *prefix,
						const xmlChar *URI,
						int nb_namespaces,
						const xmlChar **namespaces,
						int nb_attributes,
						int nb_defaulted,
						const xmlChar **attributes)
{
	if (!((SAX2Parser*)ctx)->parserError)
		((SAX2Parser*)ctx)->startElementNs(
		localname,
		prefix,
		URI,
		SAX2Namespaces(nb_namespaces, namespaces),
		SAX2Attributes(nb_attributes, nb_defaulted, attributes));
}


void sdo_endElementNs(void *ctx,
					  const xmlChar *localname,
					  const xmlChar *prefix,
					  const xmlChar *URI)
{
	if (!((SAX2Parser*)ctx)->parserError)
		((SAX2Parser*)ctx)->endElementNs(localname, prefix, URI);
}


// The callback method structure
xmlSAXHandler SDOSAX2HandlerStruct = {
    sdo_internalSubset,
		sdo_isStandalone,
		sdo_hasInternalSubset,
		sdo_hasExternalSubset,
		sdo_resolveEntity,
		sdo_getEntity,
		sdo_entityDecl,
		sdo_notationDecl,
		sdo_attributeDecl,
		sdo_elementDecl,
		sdo_unparsedEntityDecl,
		sdo_setDocumentLocator,
		sdo_startDocument,
		sdo_endDocument,
		sdo_startElement,
		sdo_endElement,
		sdo_reference,
		sdo_characters,
		sdo_ignorableWhitespace,
		sdo_processingInstruction,
		sdo_comment,
		sdo_warning,
		sdo_error,
		sdo_fatalError,
		sdo_getParameterEntity,
		sdo_cdataBlock,
		sdo_externalSubset,
		XML_SAX2_MAGIC,
		NULL,
		sdo_startElementNs,
		sdo_endElementNs,
		NULL
};


namespace commonj
{
	namespace sdo
	{
		
		namespace xmldas
		{
			
			
			
			SAX2Parser::SAX2Parser()
			{
				parserError = false;
			}
			
			SAX2Parser::~SAX2Parser()
			{
				xmlCleanupParser();
				
			}
			
			
			int SAX2Parser::parse(const char* filename)
			{
				
				parserError = false;
				xmlSAXHandlerPtr handler = &SDOSAX2HandlerStruct;
				xmlSAXUserParseFile(handler, this, filename);
				return 0;
			}

			void SAX2Parser::startDocument()
			{
			}

			void SAX2Parser::endDocument()
			{
			}
			
			void SAX2Parser::startElementNs(
				const SDOXMLString& localname,
				const SDOXMLString& prefix,
				const SDOXMLString& URI,
				const SAX2Namespaces& namespaces,
				const SAX2Attributes& attributes)
			{
			}
			
			void SAX2Parser::endElementNs(
				const SDOXMLString& localname,
				const SDOXMLString& prefix,
				const SDOXMLString& URI)
			{
			}
						
			void SAX2Parser::characters(const SDOXMLString& chars)
			{
			}
						
			
			void SAX2Parser::fatalError(const char* msg, va_list args)
			{
				char buff[1024];
				vsprintf(buff, msg, args);
				parserError = true;
				SDO_THROW_EXCEPTION("fatalError", SDOXMLParserException,buff);

			}
			
			void SAX2Parser::error(const char* msg, va_list args)
			{
				parserError = true;
				char buff[1024];
				vsprintf(buff, msg, args);
				parserError = true;
				SDO_THROW_EXCEPTION("error", SDOXMLParserException,buff);
			}

			void SAX2Parser::stream(std::istream& input)
			{
				parserError = false;
				char buffer[100];
				xmlSAXHandlerPtr handler = &SDOSAX2HandlerStruct;
				xmlParserCtxtPtr ctxt;
				
				input.read(buffer,4);
				ctxt = xmlCreatePushParserCtxt(handler, this,
					buffer, input.gcount(), NULL);
				
				while (input.read(buffer,100))
				{
					xmlParseChunk(ctxt, buffer, input.gcount(), 0);
					
				}
				
				xmlParseChunk(ctxt, buffer, input.gcount(), 1);
				xmlFreeParserCtxt(ctxt);
				
			}

			
			std::istream& operator>>(std::istream& input, SAX2Parser& parser)
			{
				parser.stream(input);							
				return input;
			}
			
			
		} // End - namespace xmldas
	} // End - namespace sdo
} // End - namespace commonj

