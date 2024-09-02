# Lyracons Module: Lyracons_QtyIncrement

## Descripcion
	
	El modulo provee los controles +/- para incrementar y decrementar las cantidades a agregar al cartito en pagina de producto, cart y mini cart.
    Hace uso de la config qty increment de magento para incrementar y decrementar en n unidades

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
$ composer require lyracons/module-qty-increment:{tag version}
```