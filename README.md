
# OnlineShoppingPortal
Installation instructions:
You need xampp or something like php 5.5, mysql 5.5+, apache 2.1+


First, you need to import your db tables.

Enter the following commands, individually:
    mysql -u root -e "source ddl/cmpe226a6.sql"
    mysql -u root -e "source ddl/cmpe226operational.sql"

Now, the db is imported.

Next, place all the files in this directory into a dir that an apache webserver will serve.
This is probably "/var/www" or maybe "/opt/lampp/htdocs".

Our config assumes your mysql server has a username named "root" with no password. If this isn't the case, you will
need to edit our config file and enter your user/pass. Edit the config.xml file and change the "std" and "stdAnalytical" profiles.

Usage Instructions:

Once installed, you can goto:
    http://localhost/Login.html

If you want to log in using an existing user(recommended), use:
    username = jolsonj
    pw = jolsonj Morton's Salt
jolsonj is one of the fake customers/orders that we created.

You can also register yourself, if you want. Just click the link.
Once logged in, you can access other pages via the menu seen on:
    http://localhost/Index.html

The order Listing Page,
    http://localhost/p1/project/OrderSearchDB.php
simply lists all the order placed by the currently logged in user.

The product search page,
    http://localhost/p1/project/ProductSearch.html
lets you enter a category name, and it will list all products in that category. Notice that
each product in the result list has a readio button - if selected, you can click the "Submit a review"
button at the bottom of the page. You'll be taken to the review submission page, where you
enter a numeric rating and a text comment for the product you selected.

THe contact info update page,
    http://localhost/p1/project/CustomerInfoUpdate.html
lets you update parts of your profile info. Whatever fields you enter wil be updated, but those you
leave blank are left alone.


Additionally, some other pages:

A web page script we used to generate fake orders and customer product reviews. You can
 submit it if you like - it will generate some orders and show you the details(a lot of detail!).
    http://localhost/generate-orders.php
It lets you select how many orders to create, and also a date range. The orders generated will have
random dates between the range you input.

There's also some command line scripts you can run.

import-users.php
    This will import the fake user data that we got from Mockaroo.com. The data
    file is in "external-datasets/customers.csv" if you want to look.
    If you want to run this script, you will need to first delete the existing records,
    otherwise you will get duplicate constraint errors. The "ddl/delete-reset.sql" file can be executed
    via the command "mysql -u root cmpe226operational < ddl/delete-reset.sql" to accomplish this. Then, you can run the import script:
        php import-users.php
    At this point, the imported data will be in the simulateduserdata table.
    You might want to use the generate-orders.php page now, which will populate the customer, order, review and other tables.

import-products.php
    This will import product data, including the products, their brands, descriptions, prices etc...The data
    file is in "external-datasets/products-500k.tsv" if you want to look.
    The database is already populated, so if you want to run this script, you will need to first delete
    the existing records, otherwise you will get duplicate constraint errors. First, you need to delete the customer
    data and orders via the command "mysql -u root cmpe226operational < ddl/delete-reset.sql". Then, run
    via the command "mysql -u root cmpe226operational < ddl/delete-reset-product-data.sql". Then, you can run the import script:
        php import-products.php

move-operational-to-analytical.sql
    This script will move data from our operational tables into our analytical tables, merging,
    inserting, updating where appropriate. Youn can run it via the command
        mysql -u root -e "source ddl/move-operational-to-analytical.sql"
    After running that, you can look at the tables in the cmpe226a6 database and see the data has been moved over to it from
    cmpe226operational.

Analytical Queries: Tableau 
	Tableau desktop (14-day trial version) can be downloaded from www.tableau.com.
Once installed, you can use the tableau workbook “Project_analytical queries.twb” for analytical and OLAP queries. Open the workbook and connect to the local host using ‘MySQL’ under database connections and access the analytical tables created. Under CustomSQL you can see the analytical queries written. There are six worksheets in the workbook displaying the results of various analytical queries.




