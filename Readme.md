# Setup

## 1 Anlegen des Modul-Ordners

Klonen Sie das Modul in den Ordner `/modules/bepado`, dh. Sie gehen in den Ordner `/modules` und führen
folgenden git Befehl aus:

```
    git clone https://github.com/Mayflower/oxid-bepado.git bepado 
```

Dieser Befehl legt den Ordner `bepado` für sie an. Wollen Sie dem Modul einen anderen 
Ordnernamen geben, beachten Sie, dass dieser der `id` in der `metadata.php` des Moduls entsprechen muss.

## 2 Bepado SDK installieren mit Composer

Wechseln Sie in den Modulordner und führen ein `composer install` aus. Für nähere Informationen
sei [Composer-Doku]("https://getcomposer.org/doc/00-intro.md") zu empfehlen.

## 3 Autoloader in Oxid  hinzufügen

Um den composer autoloader benutzen zu können muss man in der `functions.php` folgende Zeile hinzufügen:

``` php
    require_once __DIR__."/bepado/vendor/autoload.php";
```

## 4 Modul im Admin-Bereich aktivieren

Durch die Aktivierung des Moduls im Admin-Bereich werden gleichzeitig die Tabellen, die das SDK benötigt ebenso wie Änderungen an den Tabellen
des Oxid Shops vorgenommen. Darum ist es danach von Nöten die Views zu updaten. (Services->Tools)

## 5 Produktiv/DemoMode

Frisch installiert kommuniziert das SDK mit der Sandbox von Bepado. Um auf Live umzustellen, kann man dies in den allg. ModuleSettings tun.




# (TODO fix that)
## Alte Doku

2. $this->aUserComponents['mfcmp_bepado'] = 0
3. $this->iDebug = true

# Todos

1. PDO Config aus Oxid Konfiguration holen
