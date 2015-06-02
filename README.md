TmsPaymentBundle
================

Symfony2 bundle that provides payment backend


Installation
------------

Add dependencies in your `composer.json` file:
```json
"repositories": [
    ...,
    {
        "type": "vcs",
        "url": "https://github.com/Tessi-Tms/TmsPaymentBundle.git"
    }
],
"require": {
    ...,
    "tms/payment-bundle":       "dev-master"
}
```

Install these new dependencies of your application:
```sh
$ php composer.phar update
```

Enable the bundle in your application kernel:
```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Tms\Bundle\PaymentBundle\TmsPaymentBundle(),
    );
}
```

Import the bundle configuration:
```yml
# app/config/config.yml

imports:
    - { resource: @TmsPaymentBundle/Resources/config/config.yml }
```

To check if every thing seem to be ok, you can execute this command:
```sh
$ php app/console container:debug
```

You'll get this result:
```sh
...
TODO
...
```


Documentation
-------------

[Read the Documentation](Resources/doc/index.md)


Tests
-----

Install bundle dependencies:
```sh
$ php composer.phar update
```

To execute unit tests:
```sh
$ phpunit --coverage-text
```
