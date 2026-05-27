This module includes common extensions, functions etc used by christopherbolt.com developed websites.

It's not really intended for public use, so support and documentation is limited, but you are more than welcome to use it and contribute to it.

Requires SS 6.x see older branches for other versions

Install silverstripe (update to version required):
```
composer create-project silverstripe/installer ./
```

Install BoltTools:
```
composer require christopherbolt/silverstripe-bolttools ^2
```

Run post install commands as required:
```
cat vendor/christopherbolt/silverstripe-bolttools/install/htaccess.txt public/.htaccess > temp && mv temp public/.htaccess && cat vendor/christopherbolt/silverstripe-bolttools/install/gitignore.txt >> .gitignore && cp vendor/christopherbolt/silverstripe-bolttools/install/robots.txt public/robots.txt
```

Merge files from install/files into project as required

Merge values from install/composer.txt into composer.json and then run composer vendor-expose

Run npm install