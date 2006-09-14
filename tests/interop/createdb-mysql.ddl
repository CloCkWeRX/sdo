--
--  Copyright (c) 2006 The Apache Software Foundation or its licensors, as applicable.
--
--  Licensed under the Apache License, Version 2.0 (the "License");
--  you may not use this file except in compliance with the License.
--  You may obtain a copy of the License at
--
--     http://www.apache.org/licenses/LICENSE-2.0
--
--  Unless required by applicable law or agreed to in writing, software
--  distributed under the License is distributed on an "AS IS" BASIS,
--  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
--  See the License for the specific language governing permissions and
--  limitations under the License.
--

CREATE DATABASE INTEROP;
USE INTEROP;

------------------------------------------------
-- DDL Statements for table "INTEROP"."ALLTYPES"
------------------------------------------------
 
CREATE TABLE ALLTYPE (
		  ABIT BIT,
		  ATINYINT TINYINT,
		  ABOOLEAN BOOLEAN,
		  ASMALLINT SMALLINT NOT NULL ,
		  AMEDIUMINT MEDIUMINT,  
		  AINTEGER INTEGER,
		  ABIGINT BIGINT,
		  AFLOAT FLOAT,
		  ADOUBLE DOUBLE,
		  ADOUBLEPRECISION DOUBLE PRECISION,
		  AREAL REAL,
		  ADECIMAL DECIMAL,
		  ADATE DATE,
		  ADATETIME DATETIME,
		  ATIMESTAMP TIMESTAMP,
		  ATIME TIME,
		  AYEAR YEAR,
		  ACHAR CHAR,
		  AVARCHAR VARCHAR(14),
		  PARENTID SMALLINT ) ;

-----------------------------------------------------
-- DDL Statements for table "INTEROP"."ALLTYPEPARENT"
-----------------------------------------------------
 
CREATE TABLE ALLTYPEPARENT  (
		  PARENTID SMALLINT NOT NULL , 
		  DESCRIPTION VARCHAR(14)); 


