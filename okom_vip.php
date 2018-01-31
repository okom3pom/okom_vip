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
        $this->version = '1.0.4';
        $this->secure_key = Tools::encrypt($this->name);
        $this->bootstrap = true;
        $this->table_name = 'vip';

        parent::__construct();

        $this->displayName = $this->l('Add customer to the VIP Group');
        $this->description = $this->l('Automatisation pour les cartes VIP selon un id_produit.');
    }

    private function _installTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.$this->table_name.'` (
                `id_vip` INT(12) NOT NULL AUTO_INCREMENT,
                `id_customer` INT (12) NOT NULL,
                `vip_add` DATETIME NOT NULL,
                `vip_end` DATETIME NOT NULL,
                PRIMARY KEY (`id_vip`)
                ) ENGINE ='._MYSQL_ENGINE_ .' DEFAULT CHARSET=utf8';
        if (!Db::getInstance()->Execute($sql)) {
            return false;
        } else {
            return true;
        }
    }

    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }
        
        if (!parent::install()
            || !$this->_installTable()
            || !$this->registerHook('displayAdminOrderLeft')
            || !$this->registerHook('actionOrderStatusUpdate')
            || !$this->registerHook('customerAccount')
            || !$this->registerHook('adminCustomers')
            || !Configuration::updateValue('OKOM_VIP_IDGROUP', '')
            || !Configuration::updateValue('OKOM_VIP_IDORDERSTATE', '')
            || !Configuration::updateValue('OKOM_VIP_CLEAN', date('Y-m-d'))
            || !Configuration::updateValue('OKOM_VIP_NB_DAY', 365)
            || !Configuration::updateValue('OKOM_VIP_IDPRODUCT', '') ) {
            return false;
        }
        return true;
    }

    public function uninstall()
    {
        //$sql = !Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.$this->table_name.'`');
        if (!Db::getInstance()->delete('customer_group', 'id_group = '.(int)Configuration::get('OKOM_VIP_IDGROUP'))
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
            Configuration::updateValue('OKOM_VIP_NB_DAY', (int)Tools::getValue('OKOM_VIP_NB_DAY'));
        }
        // Clean Old Vip Card
        if (Tools::isSubmit('clean')) {
            $sql = 'SELECT * FROM '._DB_PREFIX_.$this->table_name.' WHERE vip_end != \'0000-00-00 00:00:00\' AND NOW() >= vip_end';
            
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
                        'label' => $this->l('Id product'),
                        'name' => 'OKOM_VIP_IDPRODUCT',
                        'size' => 20,
                        'desc' => $this->l('Choose an id_product of the VIP CARD'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Id group'),
                        'name' => 'OKOM_VIP_IDGROUP',
                        'size' => 20,
                        'desc' => $this->l('Choose id_group of the VIP Card Group'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Id order state'),
                        'name' => 'OKOM_VIP_IDORDERSTATE',
                        'size' => 20,
                        'desc' => $this->l('Choose an id order state to set yout customer in the VIP Group'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Days'),
                        'name' => 'OKOM_VIP_NB_DAY',
                        'size' => 20,
                        'desc' => $this->l('How many days customer will be VIP'),
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
            array('OKOM_VIP_IDPRODUCT','OKOM_VIP_IDGROUP','OKOM_VIP_IDORDERSTATE','OKOM_VIP_CLEAN','OKOM_VIP_NB_DAY')
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
                    if ($this->isVIP((int)$customer->id) == false) {
                        $values[] = array(
                            'id_customer' => (int)$customer->id,
                            'vip_add' => date('Y-m-d'),
                            'vip_end' => date('Y-m-d', strtotime(date('Y-m-d H:i:00').' + '.Configuration::get('OKOM_VIP_NB_DAY').' DAY'))
                        );
                        Db::getInstance()->insert($this->table_name, $values);
                        $customer->addGroups($id_group_vip);
                    } else {
                        $values = array(
                            'vip_add' => date('Y-m-d'),
                            'vip_end' => date('Y-m-d', strtotime(date('Y-m-d H:i:00').' + '.Configuration::get('OKOM_VIP_NB_DAY').' DAY'))
                        );
                        Db::getInstance()->update($this->table_name, $values, 'id_customer = '.(int)$customer->id);
                        $customer->addGroups($id_group_vip);
                    }
                }
            }
        }
        return true;
    }
      
    public function hookdisplayAdminOrder()
    {
        echo '
        <!-- VIP Customer -->
		<div class="panel">
            <div class="panel-heading"><i class="icon-money"></i>'.$this->l('Customer VIP !').'</div>
		        <div class="table-responsive">
                    '.$this->l('This customer is VIP !').'
                </div>
        </div>';
    }
    
    public function hookdisplayAdminOrderLeft()
    {
        return $this->hookdisplayAdminOrder();
    }
    
    public function hookCustomerAccount($params)
    {
        return $this->display(__FILE__, 'my-account.tpl');
    }

    /* Hook display in tab AdminCustomers on BO */
    public function hookAdminCustomers($params)
    {
        $customer = new Customer((int)$params['id_customer']);
        if ($customer && !Validate::isLoadedObject($customer)) {
            die($this->l('Incorrect Customer object.'));
        }

        $customer_vip = $this->isVIP((int)$params['id_customer']);

        if ($customer_vip == false) {
            return false;
        } else {
            $html = '';
            $html .= '<div class="col-lg-12">
		              <div class="panel">
			          <div class="panel-heading">'.$this->l('VIP Customer').'</div>
		              <div class="panel-body">';
            $html .= $this->l('Vip card start : ').$customer_vip['vip_add'].' '.$this->l('Vip Card End : ').$customer_vip['vip_end'];
            $html .= '</div></div></div>';

            return $html;
        }
    }
    
    public function isVIP($id_customer)
    {
        $is_vip = false;
        $sql = 'SELECT * FROM '._DB_PREFIX_.$this->table_name.' WHERE id_customer = '.(int)$id_customer.' ';
        $result = Db::getInstance()->executeS($sql);
        if ($result) {
            $is_vip = $result[0];
        }
        return $is_vip;
    }
}
