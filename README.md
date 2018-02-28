This module includes common extensions, functions etc used by christopherbolt.com developed websites.

It's not really intended for public use, so support and documentation is limited, but you are more than welcome to use it and contribute to it.

Requires SS 3, see 1.1 branch for SS 4

Install silverstripe (update to version required):
```
composer create-project silverstripe/installer . 3.3.1
```

Install BoltTools:
```
composer require christopherbolt/silverstripe-bolttools
```

Copy accross base mysite and themes.

Run the silverstripe web installer if you use it.

Run post install commands as required:
```
cat bolttools/install/htaccess.txt >> .htaccess && rm install.php && chmod 777 themes/mytheme/combined && chmod 777 assets && chmod 777 assets/Uploads
```