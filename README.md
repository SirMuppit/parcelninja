# Parcelninja

Parcelninja specialises in outsourced e-commerce warehousing and order fulfilment services.

## Installation Production

```bash
composer require fontera/parcelninja

php bin/magento module:enable --clear-static-content Fontera_Parcelninja
php bin/magento setup:upgrade
```

## Module events

See [Example](example.php)