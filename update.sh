sudo php8.1 bin/magento maintenance:enable
#sudo php7.3 composer.phar update
sudo php8.1 bin/magento cache:clean
sudo php8.1 bin/magento cache:flush
sudo rm -rf var/cache/*
sudo rm -rf var/page_cache/*
sudo rm -rf pub/static/*
sudo rm -rf generated/code/*
sudo php8.1 bin/magento setup:upgrade
sudo php8.1 bin/magento setup:di:compile
sudo php8.1 bin/magento setup:static-content:deploy en_US -f
sudo php8.1 bin/magento setup:static-content:deploy es_AR -f
#sudo chown www-data:www-data * -R
#solo para produccion
#sudo php8.1 bin/magento deploy:mode:set developer
sudo php8.1 bin/magento deploy:mode:set production
sudo php8.1 bin/magento maintenance:disable

sudo chown www-data:www-data * -R

