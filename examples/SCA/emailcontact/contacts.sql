connect to email;

DROP TABLE contact;

CREATE TABLE contact ( shortname VARCHAR( 40 ) NOT NULL,
                       fullname  VARCHAR( 100 ),
                       email     VARCHAR ( 60 ),
                       PRIMARY KEY ( shortname ) 
                     ); 
                       
INSERT INTO contact (shortname, fullname, email) VALUES ('fred','Fred Bloggs','fred.bloggs@somewhere.co.uk');                       
INSERT INTO contact (shortname, fullname, email) VALUES ('simon','Simon Laws','simonslaws@googlemail.com'); 
                                            