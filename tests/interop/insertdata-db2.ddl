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

CONNECT TO INTEROP;

DELETE FROM ALLTYPE;
DELETE FROM ALLTYPEPARENT;

------------------------------------------------
-- Add data to table "INTEROP"."ALLTYPEPARENT"
------------------------------------------------
INSERT INTO ALLTYPEPARENT VALUES ( 
                                  1, 
                                  'THE PARENT' );

------------------------------------------------
-- Add data to table "INTEROP"."ALLTYPE"
------------------------------------------------
-- The following types are considered but some are not valid DB2 types
--		  ABIT BIT,
--		  ATINYINT TINYINT,
--		  ABOOLEAN BOOLEAN,
--		  ASMALLINT SMALLINT NOT NULL ,
--		  AMEDIUMINT MEDIUMINT,  
--		  AINTEGER INTEGER,
--		  ABIGINT BIGINT,
--		  AFLOAT FLOAT,
--		  ADOUBLE DOUBLE,
--		  ADOUBLEPRECISION DOUBLE,
--		  AREAL REAL,
--		  ADECIMAL DECIMAL,
--		  ADATE DATE,
--		  ADATETIME DATETIME,
--		  ATIMESTAMP TIMESTAMP,
--		  ATIME TIME,
--		  AYEAR YEAR,
--		  ACHAR CHAR,
--		  AVARCHAR VARCHAR(14) 
--        FK reference to parent

INSERT INTO ALLTYPE VALUES ( 
--                           1, 
--                           2, 
--                           1,
                             1,
--                           4,
                             5,
                             6,
                             1.23,
                             4.56,
                             7.89,
                             0.12,
                             123,
                             '2006-06-11',
--                           '2006-06-11 12:47:00',
                             '2006-06-11 12:47:00',
                             '12:47:00',
--                           2006,
                             'A',
                             'INIT DATA',
                             1 );




 
COMMIT WORK;
CONNECT RESET;
TERMINATE;
