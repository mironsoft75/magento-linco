# Lyracons Module: Lyracons_Core

## Descripcion
	
	El modulo provee componentes abstractos basicos para creacion de Controllers para ABM / Api Repository / Entities, Menu de backend para Modulos Lyracons. y otros componentes reutilizables.

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
$ composer require lyracons/module-core:{tag version}
```