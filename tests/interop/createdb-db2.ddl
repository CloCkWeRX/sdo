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
CONNECT TO INTEROP;

------------------------------------------------
-- DDL Statements for table "INTEROP"."ALLTYPES"
------------------------------------------------

-- in the following type list some are not valid DB2 types
 
CREATE TABLE ALLTYPE (
--		  "ABIT" BIT,
--		  "ATINYINT" TINYINT,
--		  "ABOOLEAN" BOOLEAN,
		  "ASMALLINT" SMALLINT NOT NULL ,
--		  "AMEDIUMINT" MEDIUMINT,  
		  "AINTEGER" INTEGER,
		  "ABIGINT" BIGINT,
		  "AFLOAT" FLOAT,
		  "ADOUBLE" DOUBLE,
		  "ADOUBLEPRECISION" DOUBLE PRECISION,
		  "AREAL" REAL,
		  "ADECIMAL" DECIMAL,
		  "ADATE" DATE,
--		  "ADATETIME" DATETIME,
		  "ATIMESTAMP" TIMESTAMP,
		  "ATIME" TIME,
--		  "AYEAR" YEAR,
		  "ACHAR" CHAR,
		  "AVARCHAR" VARCHAR(14),
		  "PARENTID" SMALLINT ) 
		 IN "USERSPACE1" ; 
		 
ALTER TABLE ALLTYPE 
	ADD PRIMARY KEY
		("ASMALLINT");

-----------------------------------------------------
-- DDL Statements for table "INTEROP"."ALLTYPEPARENT"
-----------------------------------------------------
 
CREATE TABLE ALLTYPEPARENT  (
		  "PARENTID" SMALLINT NOT NULL , 
		  "DESCRIPTION" VARCHAR(14))   
		 IN "USERSPACE1" ; 

ALTER TABLE ALLTYPEPARENT 
	ADD PRIMARY KEY
		("PARENTID");

------------------------------------------------
-- Referential Integrity
------------------------------------------------

ALTER TABLE ALLTYPE 
	ADD CONSTRAINT "PARENTFK" FOREIGN KEY ("PARENTID")
	REFERENCES ALLTYPEPARENT ("PARENTID")
	ON DELETE SET NULL
	ON UPDATE NO ACTION
	ENFORCED
	ENABLE QUERY OPTIMIZATION;

COMMIT WORK;
CONNECT RESET;
TERMINATE;
