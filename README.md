# *Zeek Project*
### Open Source Back-Office : Simply administrate websites!

This project is dedicated to simple website administration.

It will offer the possibilities to
- modify, test & deploy a static website
- handle some mySQL abstraction to store data that will be displayed
- can handle multiple projects & can be administrated by multiple users

Actual state of art could be seen at this address : http://zeekadmin.free.fr/

using following

 - Project Name : test
 - Login : test
 - Password : test

#### How to use it

#### TODO Features :
 - finish the configuration & get_and_set visual functionnalities
 - insert data beetween old ones
 - save & restore database functionnalities
 - setup help methods
 - deployment tool (for 1&1)
 - secure 1st form using http://www.jcryption.org/

#### Git Log :
10/04/2015 Leo
 * display methods to get list of files and get & save file modification

23/03/2015 Leo
 * setup methods to create directory & files associated to project
 * debug the application on free.fr server & update

19/03/2015 Leo
 * start working on html, css & js files
 * removing old commands & view directory (obsolete way of thinking!)

15/03/2015 Leo
 * display data on the UI
 * add dialog JQuery UI to modify & delete data

12/02/2014 Leo
 * modification on the way to store & get data from mySQL

12/12/2014 Leo
 * add mustache.js for easy displaying html element
 * php sends now json structure to javascript client
 * correct format change when creating new tables

9/12/2014 Leo
 * projects structure handled with json format

1/12/2014 Leo
 * set up new design for index.php
 * create output_json & simplify code
 * add some error cases
 * add js/ace functionalities

30/11/2014 Leo
 * clean on css/js/html for index.php & home.php

28/11/2014 Leo
 * rework on index.php : cleaning & separate html/css/js
 * accept all connections using database login & password

26/11/2014 Leo
 * ui is going to become independant from controler side
 * simplify controler
 * remove bootstrap heavy machine

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
