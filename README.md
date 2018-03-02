This module includes common extensions, functions etc used by christopherbolt.com developed websites.

It's not really intended for public use, so support and documentation is limited, but you are more than welcome to use it and contribute to it.

Requires SS 4.x see 1.0 branch for SS 3

Install silverstripe (update to version required):
```
composer create-project silverstripe/installer . 4.0.3
```

Install BoltTools:
```
composer require christopherbolt/silverstripe-bolttools ^2
```

Copy accross base mysite and themes.

Run the silverstripe web installer if you use it.

Run post install commands as required:
```
cat vendor/christopherbolt/bolttools/install/htaccess.txt >> .htaccess && rm install.php
```
