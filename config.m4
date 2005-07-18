PHP_ARG_WITH(sdolibs, sdo dependent libraries,
[  --with-sdolibs[=DIR] Path to sdo dependent libraries])

if test "$PHP_SDOLIBS" != "no"; then
    if test -r $PHP_SDOLIBS/lib/libsdo.so -a -r $PHP_SDOLIBS/lib/libxmldas.so; then
        SDOLIBS_DIR=$PHP_SDOLIBS/lib
        AC_MSG_RESULT(SDO libraries are found at $PHP_SDOLIBS)
    else 
        for i in $HOME/lib /usr/lib /usr/local/lib; do
            if test -r $i/libsdo.so -a -r $i/libxmldas.so; then
                SDOLIBS_DIR=$i
                AC_MSG_RESULT(SDO Libraries are found at $i)
                break
            fi
        done
    fi

if test -z "$SDOLIBS_DIR"; then
    AC_MSG_RESULT(SDO libraries were not found)
    AC_MSG_ERROR(Please install the sdo dependent libraries)
fi

PHP_CHECK_LIBRARY(sdo, SDOCheck, 
[
  PHP_ADD_INCLUDE(sdolib/src/sdo)
  PHP_ADD_LIBRARY_WITH_PATH(sdo, $SDOLIBS_DIR, SDO_SHARED_LIBADD)
  AC_MSG_CHECKING(Is it a required version of sdo library)
  AC_EGREP_CPP(yes,[
#include "sdolib/src/sdo/export.h"
#if SDO4CPP_VERSION >= 20050715
  yes
#endif
  ],[
    AC_MSG_RESULT(yes)
  ],[
    AC_MSG_ERROR(libsdo version 20050715 or greater required.)
  ])
  ], [
    AC_MSG_ERROR(sdo module requires libsdo.so and libxmldas.so)
   ], [
    -L$SDOLIBS_DIR
   ])

PHP_CHECK_LIBRARY(xmldas, XMLDASCheck,
[
  PHP_ADD_INCLUDE(sdolib/src/xmldas)
  PHP_ADD_LIBRARY_WITH_PATH(xmldas, $SDOLIBS_DIR, SDO_DAS_XML_SHARED_LIBADD)
  PHP_ADD_LIBRARY_WITH_PATH(xmldas, $SDOLIBS_DIR, SDO_SHARED_LIBADD)
  AC_MSG_CHECKING(is it a required version of xmldas?)
  AC_EGREP_CPP(yes,[
#include "sdolib/src/xmldas/XMLDASExport.h"
#if XMLDAS4CPP_VERSION >= 20050715
  yes
#endif
  ],[
    AC_MSG_RESULT(yes)
  ],[
    AC_MSG_ERROR(libxmldas version level 20050715 or greater required.)
  ])
  ], [
    AC_MSG_ERROR(sdo_das_xml module requires libxmldas4cpp.so )
  ], [
    -L$SDOLIBS_DIR
  ])

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
PHP_SUBST(SDO_SHARED_LIBADD)
PHP_SUBST(SDO_DAS_XML_SHARED_LIBADD)
PHP_NEW_EXTENSION(sdo, sdo.cpp  SDO_DAS_ChangeSummary.cpp  SDO_DAS_DataFactory.cpp  SDO_DAS_Setting.cpp  SDO_DataObject.cpp  SDO_List.cpp  SDO_Sequence.cpp  sdo_utils.cpp, $ext_shared)
PHP_NEW_EXTENSION(sdo_das_xml, xmldas.cpp xmldas_utils.cpp SDO_DAS_XML.cpp SDO_DAS_XML_Document.cpp, $ext_shared)
fi
