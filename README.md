# WebTech Webshop

This ZIP file contains all files and images that are needed to run our webshop 'Cute Cloths by An'.
Created by Mees Lindeman, Jop Sutmuller, Lieuwe van Weert and Sam Wetzels.

## Structure

Within this ZIP file are several directories and files, including:
- index.php
- postcode.php 
- Images.php
- App.php
- styles.css

Directories:
- pages: this directory contains all possible pages you are able to visit on our webshop.
- layouts: this directory contains the main lay out for our website, and two other files needed for design.
- images: this directory contains all images needed to run our website. This is also the directory where we store product uploaded images

## Install

Put the contents of this Zip file in a directory servered by your webserver. Load the `webshop.sql` in your MySQL database, 
browse to the directory on your webserver, you should now see our webshop!
A default Admin user is created for you (change it!): username _admin@web.shop_, password _admin_

### Prerequisites

- Webserver with Php support (> Php 7)
- Required Php extensions
  - (GD)[https://www.php.net/manual/en/book.image.php] 
  - (PDO)[https://www.php.net/manual/en/book.pdo.php] with MySQL support
- MySQL or MariaDB server
- Load MySQL create script `webshop.sql`, it creates some default products and a admin user:
  

## License 

GNU AFFERO GENERAL PUBLIC LICENSE, see LICENSE