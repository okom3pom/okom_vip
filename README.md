# Module VIP Card for Prestashop 1.6.X.X AND Thirty Bees 1.0.3

Simple module to manage VIP cards based on two cart rules

One on a product (the VIP card)

One on a VIP Group

When a customer buy a VIP card and the order change to a desired status, the customer automatically enters in the VIP Group for XX days. 

## Features

- Vip Cards history
- Add Vip Cards from back office
- All cart rule and specific price available from VIP 
- Count Down VIP Card
- ShoppingCart hook to propose VIP cards
- See TODO for next featured and bug fix

## Installation

#### Create a VIP card product

#### Create a VIP group

#### Cart Rule

Add a cart rule with condition : The Vip card Product and add a free shipping action or other action.

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

## TODO 

- Add option : Add or Update VIP Cards ... 
- Move html and js to a tpl file
- Check if it's a guest order
- Remove VIP card product from cart if customer is VIP
- Move Vipcard to a model
- Fix tanslation Hard coded

# Module Carte VIP pour Prestashop 1.6.X.X && Thirty Bees 1.0.3

Simple module pour gérer des cartes VIP, il est basé sur deux règles de paniers

Une sur un produit ( la carte VIP )

L'autre sur un Groupe VIP

Quand le client achète une carte VIP et que la commande passe dans un statut souhaité, le client passe automatiquement dans le Groupe VIP pour XX jours. 


## Créer un produit Carte VIP

## Créer un groupe VIP

## Règle de panier !

Ajouter une règle de panier avec comme condition l'id du produit de la Carte VIP et comme action FDP offert ou autre.

Ajouter une règle de panier avec comme condition l'id du groupe de la Carte VIP et comme action FDP offert ou autre. 


## Configuration / Utilsation du Module

Ajouter l'id product de l'article : carte VIP.

Ajouter l'id group Client VIP.

Ajouter l'id_order_state qui passera vos clients dans le Groupe VIP.

Ajouter le nombre de jours de la validité de la carte. 

Vous pouvez modifier les dates d'abonnement depuis une commande ou depuis la fiche client du BO.

## Tache Cron

Vous pouvez ajouter une tache cron pour supprimer automatiquement les cartes vip expirées.

L'url est disponible dans la configuration du module.


## /!\ Suppression du module /!\

A la suppression du module la table vip n'est pas supprimée, vous devez le faire manuellement si vous souhaitez effacer la table.
Tous les membres du groupe VIP seront retirés du groupe

## TODO 

Déplacer le html et je js dans un fichier .tpl

Vérifier si ce n'est pas une commande guest

Supprimer / Bloquer le produit carte vip si le client est VIP

Ajouter un rappel sur les cartes qui vont expirer

## Screenshot

![alt text](https://www.okom3pom.com/dev-modules/image/okom_vip/okom_vip_mon_compte.png)


![alt text](https://www.okom3pom.com/dev-modules/image/okom_vip/okom_vip_front_controller.png)


![alt text](https://www.okom3pom.com/dev-modules/image/okom_vip/okom_vip_config_module.png)


![alt text](https://www.okom3pom.com/dev-modules/image/okom_vip/okom_vip_admin_order-controller.png)