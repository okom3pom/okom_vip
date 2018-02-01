# Module VIP Card for Prestashop 1.6.X.X

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

Move html and js to a tpl file