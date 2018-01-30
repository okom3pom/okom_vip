# Module Okom3pom VIP !


## Créer un produit Carte VIP

## Créer un groupe VIP

## Règle de panier !

Ajouter une règle de panier avec comme condition l'id produit de la Carte VIP et comme action FDP offert ou autre

Ajouter une règle de panier avec comme condition l'id groupe de la Carte VIP et comme action FDP offert ou autre 

## Override

Vous devez ajouter un override à la Class Customer :

`
<?
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
?>
`

## Configuration du Module

Ajouter l'id product de l'article carte VIP
Ajouter l'id group pour la carte VIPokom_vip
