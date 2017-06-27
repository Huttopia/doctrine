[![version](https://img.shields.io/badge/version-master-red.svg)](https://github.com/huttopia/doctrine)
[![symfony](https://img.shields.io/badge/doctrine/orm-^2.5-blue.svg)](http://www.doctrine-project.org/)
![Lines](https://img.shields.io/badge/code%20lines-??-green.svg)
![Total Downloads](https://poser.pugx.org/huttopia/doctrine/downloads)

# doctrine

New features and fix for [Doctrine](https://github.com/doctrine/doctrine2)

# Installation

Add it to your composer.json :

```bash
composer require huttopia/doctrine dev-master
```

Enable [ComposerOverloadClass](https://github.com/steevanb/composer-overload-class), in your composer.json :

```json
{
    "autoload": {
        "psr-4": {
            "ComposerOverloadClass\\": "var/cache/ComposerOverloadClass"
        }
    }
}
```
