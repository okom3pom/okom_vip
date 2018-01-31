# Module VIP Card for Prestashop 1.6.X.X

Simple module pour gérer des cartes VIP, il est basé sur deux règles de paniers

Une sur un produit ( la carte VIP )
L'autre sur un Groupe VIP

Quand le client achète une carte VIP et que la commande passe dans un statut souhaité, le client passe automatiquement dans le Groupe VIP pour 365 jours. 


## Créer un produit Carte VIP

## Créer un groupe VIP

## Règle de panier !

Ajouter une règle de panier avec comme condition l'id produit de la Carte VIP et comme action FDP offert ou autre

Ajouter une règle de panier avec comme condition l'id groupe de la Carte VIP et comme action FDP offert ou autre 

## Override

Vous devez ajouter un override à la Class Customer :

```
class Customer extends CustomerCore
{
   
    public $vip_add;
    public $vip_end;

    public function __construct($id = null)
    {        
        Customer::$definition['fields']['vip_add'] =  array('type' => self::TYPE_DATE,'copy_post' => false);
        Customer::$definition['fields']['vip_end'] =  array('type' => self::TYPE_DATE,'copy_post' => false);	
        parent::__construct($id);
    }	
	


}
```

## Configuration du Module

Ajouter l'id product de l'article : carte VIP

Ajouter l'id group Client VIP

Ajouter l'id_order_state qui passera vos clients dans le Groupe VIP

## TODO

Use own table not override
Hook in admin customer controller to change date