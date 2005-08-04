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

sdo_lib_srcs="sdolib/src/sdo/ChangeSummary.cpp sdolib/src/sdo/ChangeSummaryImpl.cpp sdolib/src/sdo/ChangedDataObjectList.cpp sdolib/src/sdo/ChangedDataObjectListImpl.cpp sdolib/src/sdo/CopyHelper.cpp sdolib/src/sdo/DASDataFactory.cpp sdolib/src/sdo/DASDataFactoryImpl.cpp sdolib/src/sdo/DASDataObject.cpp sdolib/src/sdo/DASProperty.cpp sdolib/src/sdo/DASType.cpp sdolib/src/sdo/DASValue.cpp sdolib/src/sdo/DASValues.cpp sdolib/src/sdo/DataFactory.cpp sdolib/src/sdo/DataObject.cpp sdolib/src/sdo/DataObjectImpl.cpp sdolib/src/sdo/DataObjectList.cpp sdolib/src/sdo/DataObjectListImpl.cpp sdolib/src/sdo/EqualityHelper.cpp sdolib/src/sdo/Logger.cpp sdolib/src/sdo/Property.cpp sdolib/src/sdo/PropertyImpl.cpp sdolib/src/sdo/PropertyList.cpp sdolib/src/sdo/RefCountingObject.cpp sdolib/src/sdo/RefCountingPointer.cpp sdolib/src/sdo/SDORuntimeException.cpp sdolib/src/sdo/SdoCheck.cpp sdolib/src/sdo/SdoRuntime.cpp sdolib/src/sdo/Sequence.cpp sdolib/src/sdo/SequenceImpl.cpp sdolib/src/sdo/Setting.cpp sdolib/src/sdo/SettingList.cpp sdolib/src/sdo/Type.cpp sdolib/src/sdo/TypeHelper.cpp sdolib/src/sdo/TypeImpl.cpp sdolib/src/sdo/TypeList.cpp sdolib/src/sdo/XMLDocument.cpp sdolib/src/sdo/XMLHelper.cpp sdolib/src/sdo/XSDHelper.cpp sdolib/src/sdo/XpathHelper.cpp sdolib/src/xmldas/HelperProvider.cpp sdolib/src/xmldas/PropertyDefinition.cpp sdolib/src/xmldas/PropertySetting.cpp sdolib/src/xmldas/SAX2Attribute.cpp sdolib/src/xmldas/SAX2Attributes.cpp sdolib/src/xmldas/SAX2Namespaces.cpp sdolib/src/xmldas/SAX2Parser.cpp sdolib/src/xmldas/SDOSAX2Parser.cpp sdolib/src/xmldas/SDOSchemaSAX2Parser.cpp sdolib/src/xmldas/SDOXMLBufferWriter.cpp sdolib/src/xmldas/SDOXMLFileWriter.cpp sdolib/src/xmldas/SDOXMLStreamWriter.cpp sdolib/src/xmldas/SDOXMLString.cpp sdolib/src/xmldas/SDOXMLWriter.cpp sdolib/src/xmldas/SDOXSDBufferWriter.cpp sdolib/src/xmldas/SDOXSDFileWriter.cpp sdolib/src/xmldas/SDOXSDStreamWriter.cpp sdolib/src/xmldas/SDOXSDWriter.cpp sdolib/src/xmldas/SchemaInfo.cpp sdolib/src/xmldas/TypeDefinition.cpp sdolib/src/xmldas/TypeDefinitions.cpp sdolib/src/xmldas/XMLDAS.cpp sdolib/src/xmldas/XMLDASCheck.cpp sdolib/src/xmldas/XMLDASImpl.cpp sdolib/src/xmldas/XMLDocumentImpl.cpp sdolib/src/xmldas/XMLHelperImpl.cpp sdolib/src/xmldas/XMLQName.cpp sdolib/src/xmldas/XSDHelperImpl.cpp sdolib/src/xmldas/XSDPropertyInfo.cpp sdolib/src/xmldas/XSDTypeInfo.cpp"


PHP_NEW_EXTENSION(sdo, sdo.cpp  SDO_DAS_ChangeSummary.cpp  SDO_DAS_DataFactory.cpp  SDO_DAS_Setting.cpp  SDO_DataObject.cpp  SDO_List.cpp  SDO_Sequence.cpp  sdo_utils.cpp $sdo_lib_srcs, $ext_shared,,-I@ext_srcdir@/sdolib/src/sdo -I@ext_srcdir@/sdolib/src/xmldas)
PHP_ADD_BUILD_DIR($ext_builddir/sdolib/src/sdo)
PHP_ADD_BUILD_DIR($ext_builddir/sdolib/src/xmldas)
PHP_NEW_EXTENSION(sdo_das_xml, das_xml.cpp xmldas_utils.cpp SDO_DAS_XML.cpp SDO_DAS_XML_Document.cpp, $ext_shared,,-I@ext_srcdir@/sdolib/src/sdo -I@ext_srcdir@/sdolib/src/xmldas)
PHP_ADD_EXTENSION_DEP(sdo_das_xml, sdo)
fi
