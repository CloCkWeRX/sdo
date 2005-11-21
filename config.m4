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

sdo_lib_srcs="commonj/sdo/ChangedDataObjectListImpl.cpp commonj/sdo/ChangeSummary.cpp commonj/sdo/ChangeSummaryImpl.cpp commonj/sdo/CopyHelper.cpp commonj/sdo/DASProperty.cpp commonj/sdo/DASType.cpp commonj/sdo/DASValue.cpp commonj/sdo/DASValues.cpp commonj/sdo/DataFactory.cpp commonj/sdo/DataFactoryImpl.cpp commonj/sdo/DataGraph.cpp commonj/sdo/DataGraphImpl.cpp commonj/sdo/DataObject.cpp commonj/sdo/DataObjectImpl.cpp commonj/sdo/DataObjectList.cpp commonj/sdo/DataObjectListImpl.cpp commonj/sdo/DefaultLogWriter.cpp commonj/sdo/EqualityHelper.cpp commonj/sdo/HelperProvider.cpp commonj/sdo/Logger.cpp commonj/sdo/LogWriter.cpp commonj/sdo/ParserErrorSetter.cpp commonj/sdo/Property.cpp commonj/sdo/PropertyDefinition.cpp commonj/sdo/PropertyImpl.cpp commonj/sdo/PropertyList.cpp commonj/sdo/PropertySetting.cpp commonj/sdo/RefCountingObject.cpp commonj/sdo/RefCountingPointer.cpp commonj/sdo/SAX2Attribute.cpp commonj/sdo/SAX2Attributes.cpp commonj/sdo/SAX2Namespaces.cpp commonj/sdo/SAX2Parser.cpp commonj/sdo/SchemaInfo.cpp commonj/sdo/SdoCheck.cpp commonj/sdo/SDODate.cpp commonj/sdo/SdoRuntime.cpp commonj/sdo/SDORuntimeException.cpp commonj/sdo/SDOSAX2Parser.cpp commonj/sdo/SDOSchemaSAX2Parser.cpp commonj/sdo/SDOXMLBufferWriter.cpp commonj/sdo/SDOXMLFileWriter.cpp commonj/sdo/SDOXMLStreamWriter.cpp commonj/sdo/SDOXMLString.cpp commonj/sdo/SDOXMLWriter.cpp commonj/sdo/SDOXSDBufferWriter.cpp commonj/sdo/SDOXSDFileWriter.cpp commonj/sdo/SDOXSDStreamWriter.cpp commonj/sdo/SDOXSDWriter.cpp commonj/sdo/Sequence.cpp commonj/sdo/SequenceImpl.cpp commonj/sdo/Setting.cpp commonj/sdo/SettingList.cpp commonj/sdo/Type.cpp commonj/sdo/TypeDefinition.cpp commonj/sdo/TypeDefinitions.cpp commonj/sdo/TypeImpl.cpp commonj/sdo/TypeList.cpp commonj/sdo/XMLDocument.cpp commonj/sdo/XMLDocumentImpl.cpp commonj/sdo/XMLHelper.cpp commonj/sdo/XMLHelperImpl.cpp commonj/sdo/XMLQName.cpp commonj/sdo/XpathHelper.cpp commonj/sdo/XSDHelper.cpp commonj/sdo/XSDHelperImpl.cpp commonj/sdo/XSDPropertyInfo.cpp commonj/sdo/XSDTypeInfo.cpp commonj/sdo/xmldas/XMLDASCheck.cpp commonj/sdo/xmldas/XMLDAS.cpp commonj/sdo/xmldas/XMLDASImpl.cpp"


PHP_NEW_EXTENSION(sdo, sdo.cpp  SDO_DAS_ChangeSummary.cpp  SDO_DAS_DataFactory.cpp  SDO_DAS_Setting.cpp  SDO_DataObject.cpp  SDO_List.cpp  SDO_Sequence.cpp  sdo_utils.cpp $sdo_lib_srcs, $ext_shared,,-I@ext_srcdir@)
PHP_ADD_BUILD_DIR($ext_builddir/commonj/sdo)
PHP_ADD_BUILD_DIR($ext_builddir/commonj/sdo/xmldas)
PHP_NEW_EXTENSION(sdo_das_xml, das_xml.cpp xmldas_utils.cpp SDO_DAS_XML.cpp SDO_DAS_XML_Document.cpp, $ext_shared,,-I@ext_srcdir@)
PHP_ADD_EXTENSION_DEP(sdo_das_xml, sdo)
fi
