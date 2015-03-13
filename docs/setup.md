# Setup

The *Mayflower-OXID-Bepado*-module has been developed and tested with die OXID eShop Community Edition v4.9 without 
additional moduls.
If you are operating an older version of the OXID CE, other OXID shop software or have extended your shop, please be 
cautious of possible misbehaviours of the module and contact your tec support for trouble shooting. 


## 1 Create the module folder

Clone this module to the folder `/modules/bepado` in your shop directory. Do this by opening the `/modules` directory
in your terminal and execute the following command:

```
    $ git clone -b 1.0 https://github.com/Mayflower/oxid-bepado.git bepado
```

This creates the `bepado` directory for you and directly clones the current stable branch. If you want to name the module otherwise please note that the name of the 
directory has to be exactly the same as the `id` in the modules `metadata.php` (so change that as well).


## 2 Installing the Bepado SDK with Composer

Change to the `/modules/bepado` directory and execute `composer install`. 

(for further information on Composer see the [Composer documentation](https://getcomposer.org/doc/00-intro.md))


## 3 Add the autoloader to OXID

To use the Composer autoloader add the following line in the `/modules/functions.php`:

``` PHP
    require_once __DIR__."/bepado/vendor/autoload.php";
```
(if you named your modules directory otherwise than `bepado`, please change the line accordingly)


## 4 Activate the module in the admin panel

Go to the admin panel of your shop and find the menue item *Extensions -> Modules*. Click on the *Bepado* module and go to 
the settings tab. Here you find the general parameters where you have to enter your *ApiKey* and *ApiEndPointUrl*.
These were given to you when you registered your shop with *Bepado*.

![bepado-settings](img/settings.png?raw=true) 

Furthermore you can select which *OXID* price attribute should hold the purchase price of *Bepado* products.

(don't forget to save the changes)

Afterwards go to the overview tab and activate the module. You were successful if you see a green item left of the modules name.

Note that the structure of your database has been altered by activating the module. To use its functionality you have to 
update the OXID views. Go to *Services -> Tools -> Update views* to do that.


## 5 Test mode vs. active mode

In the settings of the *Bepado* module you can unset the sandbox mode (which is the default setting). 

If you are in the sandbox mode, the module will connect you to the *Bepado* sandbox where you can test the modules 
functionality. Changing into the active mode will connect you to the live *Bepado* network and allow you to 
start your *Bepado* experience.

