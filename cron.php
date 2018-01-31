<?php
/**
 * Module Vip Card for Prestashop 1.6.x.x
 *
 * NOTICE OF LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction,
 * including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 *
 * @author    SARL Rouage communication <contact@okom3pom.com>
 * @copyright 2008-2018 Rouage Communication SARL
 * @version   1.0.4
 * @license   Free
 */

include_once('../../config/config.inc.php');
echo 'Start clean old VIP Card<br/><br/>';
if (Tools::getValue('token') != Tools::encrypt('okom_vip') || !Module::isInstalled('okom_vip')) {
    echo 'OUPS !';
    die();
} else {
    $sql = 'SELECT * FROM '._DB_PREFIX_.'vip'.' WHERE vip_end != \'0000-00-00 00:00:00\' AND NOW() >= vip_end';
            
    $old_vip_cards = Db::getInstance()->ExecuteS($sql);
            
    foreach ($old_vip_cards as $old_vip_card) {
        Db::getInstance()->delete('customer_group', 'id_customer = '.(int)$old_vip_card['id_customer'].' AND id_group = '.(int)Configuration::get('OKOM_VIP_IDGROUP'));
    }
    echo date('Y-m-d H:i:00').'<br/>';
    Configuration::updateValue('OKOM_VIP_CLEAN', date('Y-m-d H:i:00'));
    echo 'Done';
}
