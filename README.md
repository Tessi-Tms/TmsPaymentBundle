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

Add parameters:
```yml
# app/config/parameters.yml.dist

payments.logs_dir: /tmp
payments.sogenactif_pathfile: null
payments.scellius_pathfile: null
payments.paybox_keyspath: null
payments.paybox_web_servers: [preprod-tpeweb.paybox.com]
```

To check if every thing seem to be ok, you can execute this command:
```sh
$ php app/console container:debug
```

You'll get this result:
```sh
...
tms_payment.backend_registry  container Tms\Bundle\PaymentBundle\Backend\BackendRegistry
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
