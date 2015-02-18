# Import products from Bepado

With the Mayflower OXID-Bepado-module it is possible for you to import products from the *Bepado* network and offer 
them in your shop as if they were your own. Bepado will take care of relaying the order of an imported product to its 
original shop, which will then deliver it to your customer. There is no inconvenience to you and no additional workload 
after the product is activated in your shop.

This document will show you, how easy it's done!


## Choose products to import

After you registered your OXID shop to *Bepado* you can log in the *Bepado* network. Here you can search for products you 
would like to offer in your shop and choose them for import.

For detailed information on how this is done go to the *Bepado* help and support site.


## Get imported products into your shop

If you have activated the module according to the [setup](setup.md) 
instructions on this page, all products you chose for import will be loaded into your shop automatically. 

Now you have to activate the products in your shop so they can be seen by your customers. Go to the article list in your 
shops admin panel and find the articles marked with this little icon: 
![import-icon](img/bepado_in.png?raw=true) You can also spot imported products by sorting your articles by article number. 
*Bepados* articles will have an article number starting with 'BEP-' followed by 4 digit numbers.

*Note:* As you will see you are able to edit the imported products. It is not wise to do that though, because the module will 
trigger updates with Bepado to keep your imported products up tp date.


That's all there is to importing products from Bepado!


### Further information

For your convenience we have implemented some markers to show you which products in your orders are imported. 
If you check the articles in your orders, Bepado products are marked with this icon: 
![bepado-pic](img/bepado.png?raw=true) 

Even in your packing lists Bepado products won't have a square to tick off but this icon: 
![bepado-icon](img/bepado_b.png?raw=true) 
to show everyone to not search for this article, because it will be delivered by a Bepado store.


## Bepado categories

*Bepado* categories follow the [Google product taxonomy](https://support.google.com/merchants/answer/1705911?hl=en). As *OXID*
categories don't, you need to map your *OXID* categories to *Bepados*. 

For this go to 'Administer Products -> Categories' and click on the category that needs mapping. Go to the main tab:

![bepado-category](img/categories.png?raw=true) 

Here you can select the *Bepado* category that matches your *OXID* category. Assign the imported products to the category 
and save your changes.