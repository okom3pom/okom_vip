<?php
/**
 * Okom3pom
 *
 * NOTICE OF LICENSE
 *
 * Module Vip Card for Prestashop 1.6.x.x
 *
 * @author    SARL Rouage communication <contact@okom3pom.com>
 * @copyright 2008-2018 Rouage Communication SARL
 * @version   1.0.0
 * @license   Free
 */

include_once('../../config/config.inc.php');
echo 'Start clean old VIP Card<br/><br/>';
if (Tools::getValue('token') != Tools::encrypt('okom_vip') || !Module::isInstalled('okom_vip')) {
    echo 'OUPS !';
    die();
} else {
    $sql = 'SELECT * FROM ps_customer WHERE vip_end != \'0000-00-00 00:00:00\' AND NOW() >= vip_end';
            
    $old_vip_cards = Db::getInstance()->ExecuteS($sql);
            
    foreach ($old_vip_cards as $old_vip_card) {
        Db::getInstance()->delete('customer_group', 'id_customer = '.(int)$old_vip_card['id_customer'].' AND id_group = '.(int)Configuration::get('OKOM_VIP_IDGROUP'));
    }
    Configuration::updateValue('OKOM_VIP_CLEAN', date('Y-m-d H:i:00'));
    echo 'Done';
}
