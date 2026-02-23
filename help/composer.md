# composer

> How to install composer in staging and production environments.

### Install composer

Open terminal and navigate to the root directory `[landstre@cp16]`  
type `php composer-setup.php`

### Install the Xero Dependencies

Navigate to the root directory `[landstre@cp16]`  
`php composer.phar require xeroapi/xero-php-oauth2`  
Copy the contents of `composer.json` into the `leasemanager.co.nz/composer.json` file

### Run composer update

Navigate to `[landstre@cp16]`  
Run `composer update`  
`php composer.phar update --working-dir=public_html/leasemanager.co.nz`

### Update the Xero dependencies

Run `php composer.phar update xeroapi/xero-php-oauth2`