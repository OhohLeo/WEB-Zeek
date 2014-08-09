WEB-Zeek
========

Simple web site backoffice

09/08/2014 Leo
 * handle test for both old_mysql & new pdo way

07/08/2014 Leo
 * readaptation to old way to establish connection & request for free.fr
 * add an intermediate zeek_library between visual methods & database methods
 * finally adapt the code to handle free.fr website
 * input.php has now internal & external version

04/08/2014 Leo
 * script to deploy website on free.fr services
 * some adaptation to accept old php version (json_encode)
 * new structuration of project
 * setup file configuration

31/07/2014 Leo
 * regenerate session id each 5 minutes

29/07/2014 Leo
 * add user table
 * setup the welcome page & handle simple authentification & disconnection
 * create project at startup if it doesn't exist yet

25/07/2014 Leo
 * setup display menus & disconnection
 * finished database get & set methods
 * setup project & database environment

13/07/2014 Leo
 * add creation, check_presence & deletion of tables, rows
 * add methods to view & select data easily

10/07/2014 Leo
 * add creation, check_presence & deletion of databases
 * begin to create tables

09/07/2014 Leo
 * add unit testsx
 * try to check database presence

08/07/2O14 Leo
 * setup welcome & home page
 * 1st step with JQuery
 * 1st step with PHP & MySQL
 * establish connection with database