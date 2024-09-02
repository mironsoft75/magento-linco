# Lyracons - Magento 2 - CustomerIdentification Module #

This README would normally document whatever steps are necessary to get your application up and running.

### What is this repository for? ###

* Installation using composer
* Installation copying files
* Version

## Installation using composer:

1. Add repository to your Magento installation composer.json file

```
{
    "repositories": [
        {"type": "composer", "url": "https://repo.packagist.com/lyracons/magento-modules/"},
        {"packagist.org": false}
    ]
}
```

2. Execute composer command to download plugin package

```sh
$ composer require lyracons/module-customer-identification:{tag version}
```

## Installation copying files:

1. Copy the content to the Magento installation in directory app/code/Lyracons/CustomerIdentification

2. Enable module from console.

	  - bin/magento module:enable Lyracons_CustomerIdentification

    Then update magento with new module:

      - bin/magento setup:upgrade
      - bin/magento setup:di:compile
