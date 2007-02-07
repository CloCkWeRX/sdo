use companydb;

DROP TABLE IF EXISTS company, department, employee;

create table company (
  id integer auto_increment,
  name char(20),
  employee_of_the_month integer,
  primary key(id)
);

create table department (
  id integer auto_increment,
  name char(20),
  location char(10),
  number integer(3),
  co_id integer,
  primary key(id)
);

create table employee (
  id integer auto_increment,
  name char(20),
  SN char(4),
  manager tinyint(1),
  dept_id integer,
  primary key(id)
);
