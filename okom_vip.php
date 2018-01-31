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
 * @version   1.0.1
 * @license   Free
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class okom_vip extends Module
{
    private $_html = '';
    private $_postErrors = array();

    public function __construct()
    {
        $this->name = 'okom_vip';
        $this->tab = 'other';
        $this->author = 'Okom3pom';
        $this->version = '1.0.1';
        $this->secure_key = Tools::encrypt($this->name);
        $this->generic_name = 'okom_vip';
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Add customer to the VIP Group');
        $this->description = $this->l('Automatisation pour les cartes VIP selon un id_produit.');
    }

    public function install()
    {
        $sql = 'ALTER TABLE `ps_customer` ADD `vip_add` DATETIME NOT NULL AFTER `date_upd`, ADD `vip_end` DATETIME NOT NULL AFTER `vip_add`;';
        if (!parent::install()
            || !$this->registerHook('displayAdminOrderLeft')
            || !$this->registerHook('actionOrderStatusUpdate')
            || !$this->registerHook('customerAccount')
            || !Db::getInstance()->Execute($sql)
            || !Configuration::updateValue('OKOM_VIP_IDGROUP', '')
            || !Configuration::updateValue('OKOM_VIP_IDORDERSTATE', '')
            || !Configuration::updateValue('OKOM_VIP_CLEAN', date('Y-m-d'))
            || !Configuration::updateValue('OKOM_VIP_IDPRODUCT', '') ) {
            return false;
        }
        return true;
    }

    public function uninstall()
    {
        $sql = 'ALTER TABLE `ps_customer` DROP `vip_add` , DROP `vip_end` ';
        if (!Db::getInstance()->delete('customer_group', 'id_group = '.(int)Configuration::get('OKOM_VIP_IDGROUP'))
            || !Db::getInstance()->Execute($sql)
            || !Configuration::deleteByName('OKOM_VIP_IDGROUP')
            || !Configuration::deleteByName('OKOM_VIP_IDORDERSTATE')
            || !Configuration::deleteByName('OKOM_VIP_CLEAN')
            || !Configuration::deleteByName('OKOM_VIP_IDPRODUCT')
            || !parent::uninstall()
            ) {
            return false;
        }
        return true;
    }
    
    private function _postValidation()
    {
        if (Tools::isSubmit('btnSubmit')) {
            if (!Tools::getValue('OKOM_VIP_IDPRODUCT')) {
                $this->_postErrors[] = $this->l('You don\'t choose an id_product for the VIP Card');
            }
            if (!Tools::getValue('OKOM_VIP_IDGROUP')) {
                $this->_postErrors[] = $this->l('You don\'t choose an id_group for the VIP Card');
            }
            if (!Tools::getValue('OKOM_VIP_IDORDERSTATE')) {
                $this->_postErrors[] = $this->l('You don\'t choose an id_order_state to set customer in the VIP Group');
            }
        }
    }
    
    private function _postProcess()
    {
        if (Tools::isSubmit('btnSubmit')) {
            Configuration::updateValue('OKOM_VIP_IDPRODUCT', (int)Tools::getValue('OKOM_VIP_IDPRODUCT'));
            Configuration::updateValue('OKOM_VIP_IDGROUP', (int)Tools::getValue('OKOM_VIP_IDGROUP'));
            Configuration::updateValue('OKOM_VIP_IDORDERSTATE', (int)Tools::getValue('OKOM_VIP_IDORDERSTATE'));
        }
        // Clean Old Vip Card
        if (Tools::isSubmit('clean')) {
            $sql = 'SELECT * FROM ps_customer WHERE vip_end != \'0000-00-00 00:00:00\' AND NOW() >= vip_end';
            
            $old_vip_cards = Db::getInstance()->ExecuteS($sql);
            
            foreach ($old_vip_cards as $old_vip_card) {
                Db::getInstance()->delete('customer_group', 'id_customer = '.(int)$old_vip_card['id_customer'].' AND id_group = '.(int)Configuration::get('OKOM_VIP_IDGROUP'));
            }
            Configuration::updateValue('OKOM_VIP_CLEAN', date('Y-m-d H:i:00'));
        }
        $this->_html .= $this->displayConfirmation($this->l('Settings updated'));
    }
          
    public function renderForm()
    {
        $fields_form[0] = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Configuration du module'),
                    'icon' => 'icon-AdminAdmin'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Id product VIP Card'),
                        'name' => 'OKOM_VIP_IDPRODUCT',
                        'size' => 20,
                        'desc' => $this->l('Choose an id_product for the VIP CARD'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Id group VIP Card'),
                        'name' => 'OKOM_VIP_IDGROUP',
                        'size' => 20,
                        'desc' => $this->l('Choose an id_product for the VIP CARD'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Id order state to set customer in VIP Group'),
                        'name' => 'OKOM_VIP_IDORDERSTATE',
                        'size' => 20,
                        'desc' => $this->l('Choose an id order state to set yout customer in the VIP Group'),
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );
        
        $fields_form[1] = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Clean Old Vip Card'),
                    'icon' => 'icon-AdminAdmin'
                ),
                'input' => array(
                    array(
                    
                        'type' => 'hidden',
                        'name' => 'OKOM_VIP_CLEAN',

                    )
                ),
                'submit' => array(
                    'title' => $this->l('Clean Old Vip Card'),
                    'name' => 'clean'
                )
            ),
        );
        
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm($fields_form);
    }

    public function getConfigFieldsValues()
    {
        $conf = Configuration::getMultiple(
            array('OKOM_VIP_IDPRODUCT','OKOM_VIP_IDGROUP','OKOM_VIP_IDORDERSTATE','OKOM_VIP_CLEAN')
        );
        
        return $conf;
    }

    public function getcontent()
    {
        $this->_html .= '<h2>'.$this->displayName.'</h2>';
              
        if (Tools::isSubmit('btnSubmit')) {
            $this->_postValidation();
            if (!count($this->_postErrors)) {
                $this->_postProcess();
            } else {
                foreach ($this->_postErrors as $err) {
                    $this->_html .= $this->displayError($err);
                }
            }
        } else {
            $this->_html .= '<br />';
        }


        $this->_html .= '<div class="row">
                            <div class="col-lg-12">
                                <div class="panel" id="news">
                                    <div class="panel-heading"><i class="icon-cogs"></i> '.$this->l('Last Clean old VIP card').'</div>
                                        <div class="row">
                                            '.$this->l('Last time you removed old VIP cards is : ').Configuration::get('OKOM_VIP_CLEAN').'<br/><br/>
                                            '.$this->l('Url for cron task : ')._PS_BASE_URL_SSL_. _MODULE_DIR_ .'okom_vip/cron.php?token='.$this->secure_key.' 
                                        </div>    
                                    </div>          
                            </div>                          
                        </div>';


        $this->_html .= $this->renderForm();

        return $this->_html;
    }

    public function hookactionOrderStatusUpdate($params)
    {
        if ((int)$params['newOrderStatus']->id == (int)Configuration::get('OKOM_VIP_IDORDERSTATE')) {
            $id_product_vip = false;
            $id_group_vip = array();
            
            $order = new Order((int)$params[id_order]);
            $customer = new Customer($order->id_customer);
            
            // Check if customer is VIP
            $groups = $customer->getGroups();
            foreach ($groups as $group) {
                if ($group == (int)Configuration::get('OKOM_VIP_IDGROUP')) {
                    return false;
                }
            }
            
            $id_product_vip = (int)Configuration::get('OKOM_VIP_IDPRODUCT');
            $id_group_vip = array((int)Configuration::get('OKOM_VIP_IDGROUP'));
        
            $products = $order->getCartProducts();
            
            foreach ($products as $product) {
                //Fucking table with product_id not id_product
                if ($product['product_id'] == $id_product_vip) {
                    $customer->addGroups($id_group_vip);
                    $customer->vip_add = date('Y-m-d');
                    $customer->vip_end = date('Y-m-d', strtotime(date('Y-m-d H:i:00').' + 365 DAY'));
                    $customer->update();
                }
            }
        }
        return true;
    }
      
    public function hookdisplayAdminOrder()
    {
        echo '<!-- VIP Customer -->
		<div class="panel">
        <div class="panel-heading"><i class="icon-money"></i>'.$this->l('Customer VIP !').'</div>
		<div class="table-responsive">';
        $this->l('This customer is VIP !');
        echo '</div></div>';
    }
    
    public function hookdisplayAdminOrderLeft()
    {
        return $this->hookdisplayAdminOrder();
    }
    
    public function hookCustomerAccount($params)
    {
        return $this->display(__FILE__, 'my-account.tpl');
    }
}
