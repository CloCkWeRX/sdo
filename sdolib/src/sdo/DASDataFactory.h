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
| Author: Ed Slattery                                                  | 
+----------------------------------------------------------------------+ 

*/
/* $Id$ */

#ifndef _DASDataFactory_H_
#define _DASDataFactory_H_

#include "DataFactory.h"
#include "DASType.h"
#include "RefCountingPointer.h"

namespace commonj{
namespace sdo{

class  DASDataFactory : public DataFactory
	{
	public:
		static SDO_API DASDataFactoryPtr getDataFactory();
		
		virtual SDO_API ~DASDataFactory();
		
		virtual SDO_API void addType(const char* uri, const char* inTypeName,
			bool isSequenced = false, bool isOpen = false) = 0;
		
		
		virtual SDO_API void addPropertyToType(const char* uri, 
			const char* inTypeName,
			const char* propname,
			const char* propTypeUri, 
			const char* propTypeName,
			bool	 many  ,
			bool  rdonly ,
			bool cont ) = 0;
		
		virtual SDO_API void addPropertyToType(const char* uri, 
			const char* inTypeName,
			const char* propname,
			const Type& propType,
			bool  many   ,
			bool  rdonly ,
			bool cont ) = 0;
		
		virtual SDO_API void addPropertyToType(const Type& type, 
			const char* propname,
			const Type& propType,
			bool  many   ,
			bool  rdonly ,
			bool cont ) = 0;
		
		virtual SDO_API void addPropertyToType(const Type& type, 
			const char* propname,
			const char* propTypeUri, 
			const char* propTypeName,
			bool  many   ,
			bool  rdonly ,
			bool cont ) = 0;
		
		
		virtual SDO_API void addPropertyToType(const char* uri, 
			const char* inTypeName,
			const char* propname,
			const char* propTypeUri, 
			const char* propTypeName,
			bool	 many   = false) =0;
		
		virtual SDO_API void addPropertyToType(const char* uri, 
			const char* inTypeName,
			const char* propname,
			const Type& propType,
			bool  many   = false) =0;
		
		virtual SDO_API void addPropertyToType(const Type& type, 
			const char* propname,
			const Type& propType,
			bool  many   = false) =0;
		
		virtual SDO_API void addPropertyToType(const Type& type, 
			const char* propname,
			const char* propTypeUri, 
			const char* propTypeName,
			bool  many   = false) =0;
		
		virtual SDO_API void setBaseType( const Type& type,
			const Type& base) = 0;
		
		virtual SDO_API void setBaseType( const char* typeuri,
			const char* typenam,
			const char* baseuri,
			const char* basename) = 0;
		
		virtual SDO_API void setAlias(const char* typeuri,
			const char* typenam,
			const char* alias) = 0;
		
		virtual SDO_API void setAlias(const char* typeuri, 
			const char* typname, 
			const char* propname,
			const char* alias) = 0;

		///////////////////////////////////////////////////
		//Setting of default values
		///////////////////////////////////////////////////

	
		virtual SDO_API void setDefault(
			const Type& t, const char* propname, bool b ) = 0;
		virtual SDO_API void setDefault(
			const Type& t, const char* propname , char c) = 0;
		virtual SDO_API void setDefault(
			const Type& t, const char* propname , wchar_t c) = 0;
		virtual SDO_API void setDefault(
			const Type& t, const char* propname , char* c) = 0;
		virtual SDO_API void setDefault(
			const Type& t, const char* propname , short s) = 0;
		virtual SDO_API void setDefault(
			const Type& t, const char* propname , long l) = 0;
		virtual SDO_API void setDefault(
			const Type& t, const char* propname , int64_t i) = 0;
		virtual SDO_API void setDefault(
			const Type& t, const char* propname , float f) = 0;
		virtual SDO_API void setDefault(
			const Type& t, const char* propname , long double d) = 0;
		virtual SDO_API void setDefault(
			const Type& t, const char* propname , const wchar_t* c, unsigned int len) = 0;
		virtual SDO_API void setDefault(
			const Type& t, const char* propname , const char* c, unsigned int len) = 0;

		virtual SDO_API void setDefault(
			const char* typuri, const char* typnam, const char* propname, bool b ) = 0;
		virtual SDO_API void setDefault(
			const char* typuri, const char* typnam, const char* propname , char c) = 0;
		virtual SDO_API void setDefault(
			const char* typuri, const char* typnam, const char* propname , wchar_t c) = 0;
		virtual SDO_API void setDefault(
			const char* typuri, const char* typnam, const char* propname , char* c) = 0;
		virtual SDO_API void setDefault(
			const char* typuri, const char* typnam, const char* propname , short s) = 0;
		virtual SDO_API void setDefault(
			const char* typuri, const char* typnam, const char* propname , long l) = 0;
		virtual SDO_API void setDefault(
			const char* typuri, const char* typnam, const char* propname , int64_t i) = 0;
		virtual SDO_API void setDefault(
			const char* typuri, const char* typnam, const char* propname , float f) = 0;
		virtual SDO_API void setDefault(
			const char* typuri, const char* typnam, const char* propname , long double d) = 0;
		virtual SDO_API void setDefault(
			const char* typuri, const char* typnam, const char* propname , const wchar_t* c, unsigned int len) = 0;
		virtual SDO_API void setDefault(
			const char* typuri, const char* typnam, const char* propname , const char* c, unsigned int len) = 0;

		
		virtual SDO_API void setDASValue( 
			const Type& type,
			const char* name,
			DASValue* value) = 0;

		virtual SDO_API void setDASValue(
			const char* typeuri,
			const char* typenam,
			const char* name,
			DASValue* value) = 0;

		virtual SDO_API DASValue* getDASValue( 
			const Type& type,
			const char* name) const = 0;

		virtual SDO_API DASValue* getDASValue(
			const char* typeuri,
			const char* typenam, 
			const char* name) const = 0;

		virtual SDO_API void setDASValue( 
			const Type& type,
			const char* propertyName,
			const char* name,
			DASValue* value) = 0;

		virtual SDO_API void setDASValue( 
			const char* typeuri,
			const char* typenam,
			const char* propertyName,
			const char* name,
			DASValue* value) = 0;

		virtual SDO_API DASValue* getDASValue( 
			const Type& type,
			const char* propertyName,
			const char* name) const = 0;

		virtual SDO_API DASValue* getDASValue(
			const char* typeuri,
			const char* typenam,
			const char* propertyName, 
			const char* name) const = 0;
		};
	};
};
#endif // _DASDataFactory_H_
