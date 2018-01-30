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
 * @license   Private
 */

include_once(dirname(__FILE__).'/../../okom_vip.php');

class okom_vipDefaultModuleFrontController extends ModuleFrontController
{

    public function initContent()
    {
        parent::initContent();

        $is_vip = false;

        if (date('Y-m-d') < $this->context->customer->vip_end) {
            $is_vip = true;
        }

        $this->context->smarty->assign(array(
            'customer' => $this->context->customer,
            'is_vip' => $is_vip
        ));
 
        $this->setTemplate('vip.tpl');
    }
}
