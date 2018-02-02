# Module VIP Card for Prestashop 1.6.X.X AND Thirty Bees 1.0.3

Simple module to manage VIP cards based on two cart rules

One on a product (the VIP card)

One on a VIP Group

When a customer buy a VIP card and the order change to a desired status, the customer automatically enters in the VIP Group for XX days. 


## Create a VIP card product

## Create a VIP group

## Cart Rule

Add a cart rule with condition : Thr Vip card Product and add a free shipping action or other action.

Add a cart rule with condition : The VIP Card Group and add a free shipping action or other action.


## Module Configuration / Use Module

Add the id_product of vip card

Add the id_group of vip groupe

Add the id_order_state that will pass your customers into the VIP Group.

Add the number of days of validity of the card.

## Cron Task

You can add a cron job to automatically delete expired vip cards.

The url is available in the module configuration.


## /!\ Remove module /!\

When you delete the module the vip table is not deleted, you must do it manually if you want to clear the table. 

All members of the VIP group will be removed from the group

## Screenshot

![alt text](https://www.okom3pom.com/dev-modules/image/okom_vip/okom_vip_mon_compte.png)


![alt text](https://www.okom3pom.com/dev-modules/image/okom_vip/okom_vip_front_controller.png)


![alt text](https://www.okom3pom.com/dev-modules/image/okom_vip/okom_vip_config_module.png)


![alt text](https://www.okom3pom.com/dev-modules/image/okom_vip/okom_vip_admin_order-controller.png)



## TODO 

Move html and js to a tpl file
