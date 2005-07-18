AC_INIT(src/sdo/DataObject.h)
AM_INIT_AUTOMAKE(libsdo, 0.8.7)
AC_ARG_WITH(libxml2,[--with-libxml2-dir = XML: libxml2 install prefix])
AC_PROG_CXX
AM_PROG_LIBTOOL
if test "$with_libxml2_dir" != ""
then
    XML2CONFIG_PATH="$with_libxml2_dir/bin/xml2-config"
    if test -x "$XML2CONFIG_PATH"
    then
        AC_MSG_RESULT([$XML2CONFIG_PATH])
    fi
else
    AC_CHECK_PROGS(XML2CONFIG_PATH,xml2-config ,no)
    if test "$XML2CONFIG_PATH" = "no"
    then
        AC_MSG_ERROR([Unable to find xml2_config. Please ensure xml2-config is in path or use --with-libxml2-dir install prefix])
    fi
fi
AC_SUBST(XML2CONFIG_PATH)
AC_OUTPUT(Makefile src/Makefile src/sdo/Makefile src/xmldas/Makefile)
