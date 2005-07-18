INCLUDES = -I$(top_srcdir)/src/sdo `$(XML2CONFIG_PATH) --cflags`
LIBS = `$(XML2CONFIG_PATH) --libs`
lib_LTLIBRARIES = libxmldas.la
libxmldas_la_SOURCES = HelperProvider.cpp SAX2Namespaces.cpp SDOXMLBufferWriter.cpp SDOXSDBufferWriter.cpp TypeDefinitions.cpp XMLHelperImpl.cpp \
PropertyDefinition.cpp SAX2Parser.cpp SDOXMLFileWriter.cpp SDOXSDFileWriter.cpp XMLDASCheck.cpp XMLQName.cpp \
PropertySetting.cpp SchemaInfo.cpp SDOXMLStreamWriter.cpp SDOXSDStreamWriter.cpp XMLDAS.cpp XSDHelperImpl.cpp \
SAX2Attribute.cpp SDOSAX2Parser.cpp SDOXMLString.cpp SDOXSDWriter.cpp XMLDASImpl.cpp XSDPropertyInfo.cpp \
SAX2Attributes.cpp SDOSchemaSAX2Parser.cpp SDOXMLWriter.cpp TypeDefinition.cpp XMLDocumentImpl.cpp XSDTypeInfo.cpp \
HelperProvider.h SAX2Attributes.h SDOSAX2Parser.h SDOXMLStreamWriter.h SDOXSDFileWriter.h TypeDefinitions.h XMLDASImpl.h XSDHelperImpl.h \
PropertyDefinition.h SAX2Namespaces.h SDOSchemaSAX2Parser.h SDOXMLString.h SDOXSDStreamWriter.h XMLDASCheck.h XMLDocumentImpl.h XSDPropertyInfo.h \
PropertySetting.h SAX2Parser.h SDOXMLBufferWriter.h SDOXMLWriter.h SDOXSDWriter.h XMLDASExport.h XMLHelperImpl.h XSDTypeInfo.h \
SAX2Attribute.h SchemaInfo.h SDOXMLFileWriter.h SDOXSDBufferWriter.h TypeDefinition.h XMLDAS.h XMLQName.h
libxmldas_la_LIBADD = ../sdo/libsdo.la
