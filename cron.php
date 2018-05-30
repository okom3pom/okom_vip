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
 * @author    Okom3pom <contact@okom3pom.com>
 * @copyright 2008-2018 Okom3pom
 * @license   Free
 */

include_once('../../config/config.inc.php');
include_once('okom_vip.php');

if (Tools::getValue('token') != Tools::encrypt('okom_vip') || !Module::isInstalled('okom_vip')) {
    echo 'OUPS !';
    die();
} else {
    //Remove old vip cards
    echo 'Start clean old VIP Card<br/><br/>';
    $module = new okom_vip();
    $sql = 'SELECT * FROM '._DB_PREFIX_.'vip'.' WHERE NOW() >= vip_end AND expired = 0';
    $old_vip_cards = Db::getInstance()->ExecuteS($sql);

    foreach ($old_vip_cards as $old_vip_card) {
        Db::getInstance()->delete('customer_group', 'id_customer = '.(int)$old_vip_card['id_customer'].' AND id_group = '.(int)Configuration::get('OKOM_VIP_IDGROUP'));
        $module->setExpired((int)$old_vip_card['id_vip']);
    }

    echo date('Y-m-d H:i:00').'<br/>';
    Configuration::updateValue('OKOM_VIP_CLEAN', date('Y-m-d H:i:00'));
    echo 'Done<hr/>';

    // Check if a VIP customer is not in the GROUP

    // Send Recall for vip card
    $first_recall_date = date('Y-m-d H:i:00', strtotime(date('Y-m-d H:i:00').' + 15 DAY'));
    $second_recall_date = date('Y-m-d H:i:00', strtotime(date('Y-m-d H:i:00').' + 7 DAY'));

    echo 'First Recall date : '.$first_recall_date.'<br/>';
    echo 'Second Recall date : '.$second_recall_date.'<br/>';

    $id_lang = (int)Configuration::get('PS_LANG_DEFAULT');
    $iso = Language::getIsoById($id_lang);

    // First Recall
    $sql = 'SELECT * FROM '._DB_PREFIX_.'vip'.' WHERE \''.$first_recall_date.'\' >= vip_end AND expired = 0 AND recall = 0';
    $recalls = Db::getInstance()->ExecuteS($sql);


    foreach ($recalls as $recall) {
        $customer = new Customer((int)$recall['id_customer']);
        $templateVars = array(
            '{firstname}' => $customer->firstname,
            '{lastname}' => $customer->lastname,
            '{expired_date}' => $recall['vip_end']
        );
        
        if (file_exists(_PS_MODULE_DIR_.$module->name.'/mails/'.$iso.'/recall.txt') && file_exists(_PS_MODULE_DIR_.$module->name.'/mails/'.$iso.'/recall.html')) {
            if (!Mail::Send(
                (int)Configuration::get('PS_LANG_DEFAULT'),
                'recall',
                Mail::l('Your VIP cards expires soon 1', $id_lang),
                $templateVars,
                $customer->email,
                $customer->firstname .' '.$customer->lastname,
                Configuration::get('PS_SHOP_EMAIL'),
                Configuration::get('PS_SHOP_NAME'),
                null,
                null,
                _PS_MODULE_DIR_.$module->name.'/mails/'
            ) ) {
                echo 'Mail not sent to the Customer ID : '.$recall['id_customer'].'<br/>';
            } else {
                echo 'Mail sent to the Customer ID : '.$recall['id_customer'].'<br/>';
                $module->setRecalled((int)$recall['id_vip'], 1);
            }
        } else {
            echo '<strong>No template email found</strong>';
        }
    }

    $sql = 'SELECT * FROM '._DB_PREFIX_.'vip'.' WHERE \''.$second_recall_date.'\' >= vip_end AND expired = 0 AND recall = 1';
    $recalls = Db::getInstance()->ExecuteS($sql);


    foreach ($recalls as $recall) {
        $customer = new Customer((int)$recall['id_customer']);
        $templateVars = array(
            '{firstname}' => $customer->firstname,
            '{lastname}' => $customer->lastname,
            '{expired_date}' => $recall['vip_end']
        );
        
        if (file_exists(_PS_MODULE_DIR_.$module->name.'/mails/'.$iso.'/recall.txt') && file_exists(_PS_MODULE_DIR_.$module->name.'/mails/'.$iso.'/recall.html')) {
            if (!Mail::Send(
                (int)Configuration::get('PS_LANG_DEFAULT'),
                'recall',
                Mail::l('Your VIP cards expires soon 2', $id_lang),
                $templateVars,
                $customer->email,
                $customer->firstname .' '.$customer->lastname,
                Configuration::get('PS_SHOP_EMAIL'),
                Configuration::get('PS_SHOP_NAME'),
                null,
                null,
                _PS_MODULE_DIR_.$module->name.'/mails/'
            ) ) {
                echo 'Mail not sent to the Customer ID : '.$recall['id_customer'].'<br/>';
            } else {
                echo 'Mail sent to the Customer ID : '.$recall['id_customer'].'<br/>';
                $module->setRecalled((int)$recall['id_vip'], 2);
            }
        } else {
            echo '<strong>No template email found</strong>';
        }
    }
}
