# *Zeek Project*
### Open Source Back-Office : Simply administrate websites!

#### Introduction :

This is my 1st steps on internet technologies (php, jQuery) thanks to
this project.

Please correct & optimise as you wish this project.

You can see the actual state of art here :

http://zeekadmin.free.fr/

using following

 - Project Name : test
 - Login : test
 - Password : test

#### How to use it

The project is not yet finished.

#### Main Features :
 - handle multiple projects
 - handle multiple users
 - deployment tool (only on free.fr for the moment)
 - set all mysql tools as insible as possible
 - work with PDO & Mysql
 - lots of unit tests & error cases
 - keep it as simple as possible

#### TODO Features :
 - finish the configuration & get_and_set visual functionnalities
 - insert data beetween old ones
 - save&restore database functionnalities
 - setup help methods
 - deployment tool (for 1&1)
 - secure 1st form using http://www.jcryption.org/

#### Git Log :
16/09/2014 Leo
 * test with writing values

12/09/2014 Leo
 * handle multiple & dynamic project structure

11/09/2014 Leo
 * add datatable functionalities
 * 1st step on get&set
 * add lots of new application commands

08/09/2014 Leo
 * set user & project methods & test
 * optimisations on home.php
 * 1st link between view files & php
 * update the README file

12/08/2014 Leo
 * check sql input values

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
 * add unit tests
 * try to check database presence

08/07/2O14 Leo
 * setup welcome & home page
 * 1st step with JQuery
 * 1st step with PHP & MySQL
 * establish connection with database
