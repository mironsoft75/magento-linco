# Lyracons Module: Lyracons_Notifications

## Descripcion
	
	El módulo cambia automaticamente las notificaciones nativas de Magento reemplazandolas por contenedores con posición fija, 
    agregándole colores que se configuran por backend dentro de Stores -> Configuration -> Notifications.

## Instalacion

Editar el archivo composer.json del proyecto y agregar como origen de paquetes Packagist

```
{
    "repositories": [
        {"type": "composer", "url": "https://repo.packagist.com/lyracons/magento-modules/"},
        {"packagist.org": false}
    ]
}
```

y ejecutar:

```sh
$ composer require lyracons/module-notifications:{tag version}
```