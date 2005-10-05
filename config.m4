dnl $Id$

PHP_ARG_ENABLE(sdo, sdo ,
[  --enable-sdo Enable sdo support])

if test "$PHP_SDO" != "no"; then

if test -z "$PHP_LIBXML_DIR"; then
  PHP_ARG_WITH(libxml-dir, libxml2 install dir,
  [  --with-libxml-dir=DIR     SimpleXML: libxml2 install prefix], no, no)
fi

PHP_SETUP_LIBXML(SIMPLEXML_SHARED_LIBADD, [
  AC_DEFINE(HAVE_SIMPLEXML,1,[ ])
  PHP_SUBST(SIMPLEXML_SHARED_LIBADD)
], [
  AC_MSG_ERROR([xml2-config not found. Please check your libxml2 installation.])
])

PHP_REQUIRE_CXX()

dnl This step should not be necessary, but the PHP_REQUIRE_CXX does
dnl not ensure the use of g++ as the linker, only as the compiler
CC=g++
PHP_SUBST(CC)

sdo_lib_srcs="cpp/sdo/ChangedDataObjectListImpl.cpp cpp/sdo/ChangeSummary.cpp cpp/sdo/ChangeSummaryImpl.cpp cpp/sdo/CopyHelper.cpp cpp/sdo/DASProperty.cpp cpp/sdo/DASType.cpp cpp/sdo/DASValue.cpp cpp/sdo/DASValues.cpp cpp/sdo/DataFactory.cpp cpp/sdo/DataFactoryImpl.cpp cpp/sdo/DataGraph.cpp cpp/sdo/DataGraphImpl.cpp cpp/sdo/DataObject.cpp cpp/sdo/DataObjectImpl.cpp cpp/sdo/DataObjectList.cpp cpp/sdo/DataObjectListImpl.cpp cpp/sdo/EqualityHelper.cpp cpp/sdo/HelperProvider.cpp cpp/sdo/Logger.cpp cpp/sdo/Property.cpp cpp/sdo/PropertyDefinition.cpp cpp/sdo/PropertyImpl.cpp cpp/sdo/PropertyList.cpp cpp/sdo/PropertySetting.cpp cpp/sdo/RefCountingObject.cpp cpp/sdo/RefCountingPointer.cpp cpp/sdo/SAX2Attribute.cpp cpp/sdo/SAX2Attributes.cpp cpp/sdo/SAX2Namespaces.cpp cpp/sdo/SAX2Parser.cpp cpp/sdo/SchemaInfo.cpp cpp/sdo/SdoCheck.cpp cpp/sdo/SDODate.cpp cpp/sdo/SdoRuntime.cpp cpp/sdo/SDORuntimeException.cpp cpp/sdo/SDOSAX2Parser.cpp cpp/sdo/SDOSchemaSAX2Parser.cpp cpp/sdo/SDOXMLBufferWriter.cpp cpp/sdo/SDOXMLFileWriter.cpp cpp/sdo/SDOXMLStreamWriter.cpp cpp/sdo/SDOXMLString.cpp cpp/sdo/SDOXMLWriter.cpp cpp/sdo/SDOXSDBufferWriter.cpp cpp/sdo/SDOXSDFileWriter.cpp cpp/sdo/SDOXSDStreamWriter.cpp cpp/sdo/SDOXSDWriter.cpp cpp/sdo/Sequence.cpp cpp/sdo/SequenceImpl.cpp cpp/sdo/Setting.cpp cpp/sdo/SettingList.cpp cpp/sdo/Type.cpp cpp/sdo/TypeDefinition.cpp cpp/sdo/TypeDefinitions.cpp cpp/sdo/TypeImpl.cpp cpp/sdo/TypeList.cpp cpp/sdo/XMLDocument.cpp cpp/sdo/XMLDocumentImpl.cpp cpp/sdo/XMLHelper.cpp cpp/sdo/XMLHelperImpl.cpp cpp/sdo/XMLQName.cpp cpp/sdo/XpathHelper.cpp cpp/sdo/XSDHelper.cpp cpp/sdo/XSDHelperImpl.cpp cpp/sdo/XSDPropertyInfo.cpp cpp/sdo/XSDTypeInfo.cpp cpp/xmldas/XMLDASCheck.cpp cpp/xmldas/XMLDAS.cpp cpp/xmldas/XMLDASImpl.cpp"


PHP_NEW_EXTENSION(sdo, sdo.cpp  SDO_DAS_ChangeSummary.cpp  SDO_DAS_DataFactory.cpp  SDO_DAS_Setting.cpp  SDO_DataObject.cpp  SDO_List.cpp  SDO_Sequence.cpp  sdo_utils.cpp $sdo_lib_srcs, $ext_shared,,-I@ext_srcdir@/cpp/sdo -I@ext_srcdir@/cpp/xmldas)
PHP_ADD_BUILD_DIR($ext_builddir/cpp/sdo)
PHP_ADD_BUILD_DIR($ext_builddir/cpp/xmldas)
PHP_NEW_EXTENSION(sdo_das_xml, das_xml.cpp xmldas_utils.cpp SDO_DAS_XML.cpp SDO_DAS_XML_Document.cpp, $ext_shared,,-I@ext_srcdir@/cpp/sdo -I@ext_srcdir@/cpp/xmldas)
PHP_ADD_EXTENSION_DEP(sdo_das_xml, sdo)
fi
