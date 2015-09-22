# *Zeek Project*

<p align="center">
    <a href="http://www.ohohleo.fr">
       <img src="http://www.ohohleo.fr/img/complain.png">
    </a>
</p>

Url of the project : [www.ohohleo.fr]( http://www.ohohleo.fr)

### Open Source Back-Office : Simply websites!

This project is dedicated to simple website generation & administration.

It offers the possibilities to :
- modify, test & deploy static websites
- store data in database that will be displayed
- can handle multiple projects & can be administrated by multiple users

#### How to test it

Actual state of art could be seen at this address : http://zeekadmin.free.fr/

using following

 - Project Name : test
 - Login : test
 - Password : test

#### How to install it

1. Copy all the files from the build directory on your server

2. Configure 'config.ini' file as following using database login,
password, host & name specified :

```
db_login={database_login};
db_password={database_password};
db_host={database_host};
db_name={database_name};
```

3. Enter db_login, db_password and the name of the project you would
like to create.

4. As you are in master mode, I recommend to create your new own
administrator user, going in Configuration -> User, writing your email
address to create the new user. You should receive a mail with your
password that you can change, going to Configuration -> Password.

#### What is happening when using Test & Deploy

When creating a new project, all the projects file are stored in the directory :

    {zeek_path}/projects/{project_id}/

By clicking on "Test" : all the project files are actually copied
into a specific user directory :

    {zeek_path}/projects/{project_id}/TEST/{user_login}/

By clicking on "Deploy" : all the project files are finally copied
into a specific user directory :

    {zeek_path}/projects/{project_id}/DEPLOY/

Moreover, once you have modified a file, the modified file is normally
stored in the project directory. But if the user test directory exists,
the modified file will also be copied into the user test directory.

**So you don't have to click all the time on "Test" button !**

You click it once to create the user test directory, then you only have to
refresh the test web page! Practical, isn't it?

In test mode, if you develop on Chromium or Firefox : don't hesitate to
use Ctrl + Maj + c to enter in development tool box.

#### How to use Zeek

By defaut Zeek is disabled. Go to Configuration -> Zeek -> Enable.

Zeek allows you to declare your data structure.

To use the data in the HTML code, you simply have to declare this way :

```
<zeek name="structure_name" size="5" offset="1" sort_by="{attribute_name(++/--)}">
       some <p> stuff ... {{attribute_name}} ... some </p> stuff
</zeek>
```
Using following parameters :
 - structure_name : name of your structure for sure
 - size : number of elements to display
 - offset : where to begin
 - sort_by : which element use to sort (++ means in 'ASC' way, -- means in 'DEC' way)

#### Next Features :
 - protect against CSRF attack
 - set up update process
 - get & set data from client side
 - insert data beetween old ones & sort them
 - save & restore database functionnalities
 - secure 1st form using http://www.jcryption.org/
