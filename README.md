[zeek_logo]: http://www.ohohleo.fr/img/complain.png "www.ohohleo.fr"

# *Zeek Project*

![zeek_logo]

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
