<?php

/*
 * Module Vip Card for Prestashop 1.6.x.x
 * NOTICE OF LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction,
 * including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 *
 * @author    Okom3pom <contact@okom3pom.com>
 * @copyright 2008-2018 Okom3pom
 * @version   1.0.11
 * @license   Free
 */

class okom_vip extends Module
{
    private $_html = '';
    private $_postErrors = array();
    
    public function __construct()
    {
        $this->name = 'okom_vip';
        $this->tab = 'other';
        $this->author = 'Okom3pom';
        $this->version = '1.0.12';
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
                `recall` int(1) NOT NULL DEFAULT "0",
                `expired` int(1) NOT NULL DEFAULT "0",
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
            || !$this->registerHook('displayPriceVIP')
            || !Configuration::updateValue('OKOM_VIP_IDGROUP', '')
            || !Configuration::updateValue('OKOM_VIP_IDORDERSTATE', '')
            || !Configuration::updateValue('OKOM_VIP_CLEAN', date('Y-m-d'))
            || !Configuration::updateValue('OKOM_VIP_NB_DAY', 365)
            || !Configuration::updateValue('OKOM_VIP_DISPLAY_PRICE', 1)
            || !Configuration::updateValue('OKOM_VIP_IDPRODUCT', '')) {
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
            || !Configuration::deleteByName('OKOM_VIP_DISPLAY_PRICE')
            || !Configuration::deleteByName('OKOM_VIP_NB_DAY')
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
            if (!Tools::getValue('OKOM_VIP_NB_DAY')) {
                365 == Tools::getValue('OKOM_VIP_NB_DAY');
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
            Configuration::updateValue('OKOM_VIP_DISPLAY_PRICE', (int)Tools::getValue('OKOM_VIP_DISPLAY_PRICE'));
        }
        // Clean Old Vip Card
        if (Tools::isSubmit('clean')) {
            $sql = 'SELECT * FROM '._DB_PREFIX_.$this->table_name.' WHERE NOW() >= vip_end AND expired = 0';
            
            $old_vip_cards = Db::getInstance()->executeS($sql);
            
            foreach ($old_vip_cards as $old_vip_card) {
                Db::getInstance()->delete('customer_group', 'id_customer = '.(int)$old_vip_card['id_customer'].' AND id_group = '.(int)Configuration::get('OKOM_VIP_IDGROUP'));
                $this->setExpired((int)$old_vip_card['id_vip']);
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
                    ),
                    array(
                        'name' => 'OKOM_VIP_DISPLAY_PRICE',
                        'type' => 'switch',
                        'label' => $this->l('Activate'),
                        'desc' => $this->l('Affiche les nouveaux prix pour les clients'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        )
                    ),
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
            array('OKOM_VIP_IDPRODUCT','OKOM_VIP_IDGROUP','OKOM_VIP_IDORDERSTATE','OKOM_VIP_CLEAN','OKOM_VIP_NB_DAY','OKOM_VIP_DISPLAY_PRICE')
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
                    if (false == $this->isVIP((int)$customer->id)) {
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
    
    public function hookdisplayAdminOrderLeft($params)
    {
        $order = new Order((int)Tools::getValue('id_order'));
        $customer = new Customer((int)$order->id_customer);


        // DELETE a VIP Cards
        if (Tools::getValue('id_vip') > 0 && Tools::isSubmit('submit_delete_vip')) {
            $this->deleteVipCard((int)Tools::getValue('id_vip'));
        }

        // ADD a VIP Cards
        if (Tools::getValue('vip_add') && Tools::getValue('vip_end') && Tools::isSubmit('submit_add_vip')) {
            // ADD to group only if vip_end > Now
            if (Tools::getValue('vip_end') > date('Y-m-d H:i:00')) {
                $id_group_vip = array((int)Configuration::get('OKOM_VIP_IDGROUP'));
                $customer->addGroups($id_group_vip);
            }
            $values[] = array(
                'id_customer' => (int)$order->id_customer,
                'vip_add' => Tools::getValue('vip_add'),
                'vip_end' => Tools::getValue('vip_end')
            );
            Db::getInstance()->insert($this->table_name, $values);
        }

        // UPDATE a VIP CARD
        if (Tools::getValue('vip_add') && Tools::getValue('vip_end') && Tools::getValue('id_vip')  && Tools::isSubmit('submit_edit_vip')) {
            $customer_vip = $this->isVIP((int)$order->id_customer);

            if (Tools::getValue('vip_end') > date('Y-m-d H:i:00')) {
                $expired = 0;
                $id_group_vip = array((int)Configuration::get('OKOM_VIP_IDGROUP'));
                $customer->addGroups($id_group_vip);
            } else {
                $expired = 1;
            }

            $values = array(
                'vip_add' => Tools::getValue('vip_add'),
                'vip_end' => Tools::getValue('vip_end'),
                'expired' => $expired,
                'recall' => 0
            );
            Db::getInstance()->update($this->table_name, $values, ' id_vip = '.(int)Tools::getValue('id_vip').' AND id_customer = '.(int)$order->id_customer);
            /*
            if ($customer_vip == false) {
                $values[] = array(
                    'id_customer' => (int)$order->id_customer,
                    'vip_add' => Tools::getValue('vip_add'),
                    'vip_end' => Tools::getValue('vip_end')
                );
                Db::getInstance()->insert($this->table_name, $values);
                if (Tools::getValue('vip_end') > date('Y-m-d H:i:00')) {
                    $id_group_vip = array((int)Configuration::get('OKOM_VIP_IDGROUP'));
                    $customer->addGroups($id_group_vip);
                } else {
                    Db::getInstance()->delete('customer_group', 'id_customer = '.(int)$order->id_customer.' AND id_group = '.(int)Configuration::get('OKOM_VIP_IDGROUP'));
                    $this->setExpired((int)$customer_vip['id_vip']);
                }
            /*} else {
                if (Tools::getValue('vip_end') > date('Y-m-d H:i:00')) {
                    $id_group_vip = array((int)Configuration::get('OKOM_VIP_IDGROUP'));
                    $customer->addGroups($id_group_vip);
                    $values[] = array(
                        'id_customer' => (int)$order->id_customer,
                        'vip_add' => Tools::getValue('vip_add'),
                        'vip_end' => Tools::getValue('vip_end')
                    );
                    Db::getInstance()->insert($this->table_name, $values);
                } else {
                    Db::getInstance()->delete('customer_group', 'id_customer = '.(int)$order->id_customer.' AND id_group = '.(int)Configuration::get('OKOM_VIP_IDGROUP'));
                    $this->setExpired((int)$customer_vip['id_vip']);
                }
            }*/
        }

        $customer_vip = $this->isVIP((int)$order->id_customer, true);

        if (false == $customer_vip) {
            $vip_add = '0000-00-00';
            $vip_end = '0000-00-00';
        } else {
            $vip_add = $customer_vip['vip_add'];
            $vip_end = $customer_vip['vip_end'];
        }

        $html = $this->printForm($vip_add, $vip_end, $this->getVipCards((int)$order->id_customer));
        return $html;
    }
    
    public function hookCustomerAccount($params)
    {
        return $this->display(__FILE__, 'my-account.tpl');
    }

    public function hookAdminCustomers($params)
    {
    	/*
        $customer = new Customer((int)$params['id_customer']);

        if ($customer && !Validate::isLoadedObject($customer)) {
            die($this->l('Incorrect Customer object.'));
        }

        $vip_add = '';
        $vip_end = '';

        if (Tools::getValue('vip_add') && Tools::getValue('vip_end')) {
            $customer_vip = $this->isVIP((int)$customer->id);
            if (false == $customer_vip) {
                $values[] = array(
                    'id_customer' => (int)$order->id_customer,
                    'vip_add' => Tools::getValue('vip_add'),
                    'vip_end' => Tools::getValue('vip_end')
                );
                Db::getInstance()->insert($this->table_name, $values);
                if (Tools::getValue('vip_end') > date('Y-m-d H:i:00')) {
                    $id_group_vip = array((int)Configuration::get('OKOM_VIP_IDGROUP'));
                    $customer->addGroups($id_group_vip);
                } else {
                    Db::getInstance()->delete('customer_group', 'id_customer = '.(int)$customer->id.' AND id_group = '.(int)Configuration::get('OKOM_VIP_IDGROUP'));
                    $this->setExpired((int)$customer_vip['id_vip']);
                }
            } else {
                if (Tools::getValue('vip_end') > date('Y-m-d H:i:00')) {
                    $id_group_vip = array((int)Configuration::get('OKOM_VIP_IDGROUP'));
                    $customer->addGroups($id_group_vip);
                    $values[] = array(
                        'id_customer' => (int)$order->id_customer,
                        'vip_add' => Tools::getValue('vip_add'),
                        'vip_end' => Tools::getValue('vip_end')
                    );
                    Db::getInstance()->insert($this->table_name, $values);
                } else {
                    Db::getInstance()->delete('customer_group', 'id_customer = '.(int)$customer->id.' AND id_group = '.(int)Configuration::get('OKOM_VIP_IDGROUP'));
                    $this->setExpired((int)$customer_vip['id_vip']);
                }
            }
        }
        $customer_vip = $this->isVIP((int)$order->id_customer, true);

        if (false == $customer_vip) {
            $vip_add = '0000-00-00';
            $vip_end = '0000-00-00';
        } else {
            $vip_add = $customer_vip['vip_add'];
            $vip_end = $customer_vip['vip_end'];
        }

        $html = $this->printForm($vip_add, $vip_end);
        return $html;
        */
    }

    public function hookHeader()
    {
        $this->context->controller->addCSS(_MODULE_DIR_.$this->name.'/views/css/okom_vip.css');
    }

    public function hookShoppingCart($params)
    {
        $customer_vip = $this->isVIP($this->context->customer->id, true);

        if (false == $customer_vip) {
            $is_vip = false;
            $exprired = true;
        } else {
            $is_vip = true;
            if (date('Y-m-d') < $customer_vip['vip_end']) {
                $exprired = false;
            } else {
                $exprired = true;
            }
        }

        $product = new Product((int)Configuration::get('OKOM_VIP_IDPRODUCT'), true, $this->context->language->id);
        $link = new Link();
        //@TODO Fix Bad Link
        $vip_product_url = $link->getProductLink($product);
        $this->context->smarty->assign(array(
            'customer_vip' => $customer_vip,
            'exprired' => $exprired,
            'is_vip' => $is_vip,
            'vip_product_url' => $vip_product_url
        ));
        return $this->display(__FILE__, 'shopping-cart.tpl');
    }

    public function hookdisplayPriceVIP($params)
    {
        
        if( Configuration::get('OKOM_VIP_DISPLAY_PRICE') == 0 ) {
            return;
        }

        $id_product = (int)Tools::getValue('id_product');

        $sql = 'SELECT id_group FROM  `'._DB_PREFIX_.'specific_price` WHERE id_product = '.$id_product.' AND id_group = '.(int)Configuration::get('OKOM_VIP_IDGROUP').' ';

        $id_group = Db::getInstance()->getValue($sql);

        if($id_group == (int)Configuration::get('OKOM_VIP_IDGROUP') ) {

            $price = okom_vip::getPriceStatic($id_product,true);

            $this->context->smarty->assign(array(
                'price' => $price ,
                'id_group' => $id_group
            ));

            return $this->display(__FILE__, 'product_price.tpl');
        }
    }

    public function printForm($vip_add, $vip_end, $vip_cards)
    {
        $option = '';
        
        if ($vip_cards) {
            foreach ($vip_cards as $vip_card) {
                $option .= '<option data-add="'.$vip_card['vip_add'].'" data-end="'.$vip_card['vip_end'].'" value="'.$vip_card['id_vip'].'">'.$vip_card['id_vip'].' : '.$vip_card['vip_add'].' to '.$vip_card['vip_end'].'</option>';
            }
        }

        $html = '';
        $html .= '
        <div class="col-lg-12">
        <div class="panel">
        <div class="panel-heading">'.$this->l('VIP Customer').'</div>
        <div class="panel-body">';
        $html .= '
        <form class="defaultForm form-horizontal" id="edit_vp" name="edit_vp" method="POST">
            <div class="form-group">                                                    
                <label class="control-label col-lg-3">'.$this->l('Vip Card: ').'</label>                         
                <div class="col-lg-9">                  
                    <div class="row">
                        <div class="input-group col-lg-6">
                            <select id="id_vip" class="form-control" name="id_vip">
                                <option value="0">'.$this->l('Create or Select VIP Card for update').'</option>
                                '.$option.'
                            </select>
                        </div>
                    </div>                          
                    <p class="help-block"></p>                                                                  
                </div>                          
            </div>
            <div class="form-group">                                                    
                <label class="control-label col-lg-3">'.$this->l('Vip Card Start : ').'</label>                         
                <div class="col-lg-9">                  
                    <div class="row">
                        <div class="input-group col-lg-6">
                            <input id="vip_add" type="text" data-hex="true" class="datetimepicker" name="vip_add" value="'.$vip_add.'">
                            <span class="input-group-addon">
                                <i class="icon-calendar-empty"></i>
                            </span>
                        </div>
                    </div>                          
                    <p class="help-block"></p>                                                                  
                </div>                          
            </div>
            <div class="form-group">                                                    
                <label class="control-label col-lg-3">'.$this->l('Vip Card End : ').'</label>
                <div class="col-lg-9">
                    <div class="row">
                        <div class="input-group col-lg-6">
                            <input id="vip_end" type="text" data-hex="true" class="datetimepicker" name="vip_end" value="'.$vip_end.'">
                            <span class="input-group-addon">
                                <i class="icon-calendar-empty"></i>
                            </span>
                            </div>
                        </div>
                        <p class="help-block"></p>
                    </div>                          
                </div>
            <div class="panel-footer">
                <button type="submit" value="1" id="submit_delete_vip" name="submit_delete_vip" class="btn btn-default pull-right">
                    <i class="process-icon-save"></i> '.$this->l('Delete').'
                </button>
                <button type="submit" value="1" id="submit_edit_vip" name="submit_edit_vip" class="btn btn-default pull-right">
                    <i class="process-icon-save"></i> '.$this->l('Update').'
                </button>
                <button type="submit" value="1" id="submit_add_vip" name="submit_add_vip" class="btn btn-default pull-right">
                    <i class="process-icon-save"></i> '.$this->l('Add').'
                </button>
            </div>
        </form>';
        $html .= '</div></div></div>';
        $html .= '
        <script type="text/javascript">
            $(document).ready(function() {

                $("#submit_edit_vip").hide();
                $("#submit_delete_vip").hide();
                $("#vip_add").val(""); 
                $("#vip_end").val(""); 

                $("#id_vip").change(function() {

                    if($("#id_vip").val() == 0) {
                        $("#submit_edit_vip").hide();
                        $("#submit_delete_vip").hide();
                        $("#submit_add_vip").show();

                    } else {
                        $("#submit_edit_vip").show();                        
                        $("#submit_delete_vip").show();
                        $("#submit_add_vip").hide();                    
                    }

                    var vip_add = $("option:selected", this).attr("data-add");
                    var vip_end = $("option:selected", this).attr("data-end");
                    $("#vip_add").val(vip_add); 
                    $("#vip_end").val(vip_end);                 
                });

                if ($(".datepicker").length > 0)
                    $(".datepicker").datepicker({
                        prevText: "",
                        nextText: "",
                        dateFormat: "yy-mm-dd"
                });
                if ($(".datetimepicker").length > 0)
                    $(".datetimepicker").datetimepicker({
                        prevText: "",
                        nextText: "",
                        dateFormat: "yy-mm-dd",
                        // Define a custom regional settings in order to use PrestaShop translation tools
                        currentText: "Maintenant",
                        closeText: "Valider",
                        ampm: false,
                        amNames: ["AM", "A"],
                        pmNames: ["PM", "P"],
                        timeFormat: "hh:mm:ss tt",
                        timeSuffix: "",
                        timeOnlyTitle: "Choisir l heure",
                        timeText: "Heure",
                        hourText: "Heure",
                        minuteText: "Minute",
                });
            });
        </script>';
        return $html;
    }
    
    public function isVIP($id_customer, $not_expired = false)
    {
        $is_vip = false;
        $sql = 'SELECT * FROM '._DB_PREFIX_.$this->table_name.' WHERE id_customer = '.(int)$id_customer.' ';

        if (true == $not_expired) {
            $sql .= 'AND expired = 0 ';
        }

        $sql .= 'ORDER BY id_vip DESC';
        $result = Db::getInstance()->executeS($sql);

        if ($result) {
            $is_vip = $result[0];
        }

        return $is_vip;
    }

    public function getVipCards($id_customer)
    {
        $vip_cards = false;
        $sql = 'SELECT * FROM '._DB_PREFIX_.$this->table_name.' WHERE id_customer = '.(int)$id_customer.' ORDER BY id_vip DESC ';
        $result = Db::getInstance()->executeS($sql);

        if ($result) {
            $vip_cards = $result;
        }

        return $vip_cards;
    }

    public function deleteVipCard($id_vip)
    {
        if (Db::getInstance()->execute('DELETE FROM  `'._DB_PREFIX_.$this->table_name.'` WHERE id_vip = '.(int)$id_vip.' ')) {
            return true;
        } else {
            return false;
        }
    }

    public function setExpired($id_vip)
    {
        if (Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.$this->table_name.'` SET expired = 1 WHERE id_vip = '.(int)$id_vip.' ')) {
            return true;
        } else {
            return false;
        }
    }

    public function setRecalled($id_vip, $recall = 1)
    {
        if (Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.$this->table_name.'` SET recall = '.(int)$recall.' WHERE id_vip = '.(int)$id_vip.' ')) {
            return true;
        } else {
            return false;
        }
    }
   /**
     * Returns product price
     *
     * @param int      $id_product            Product id
     * @param bool     $usetax                With taxes or not (optional)
     * @param int|null $id_product_attribute  Product attribute id (optional).
     *                                        If set to false, do not apply the combination price impact.
     *                                        NULL does apply the default combination price impact.
     * @param int      $decimals              Number of decimals (optional)
     * @param int|null $divisor               Useful when paying many time without fees (optional)
     * @param bool     $only_reduc            Returns only the reduction amount
     * @param bool     $usereduc              Set if the returned amount will include reduction
     * @param int      $quantity              Required for quantity discount application (default value: 1)
     * @param bool     $force_associated_tax  DEPRECATED - NOT USED Force to apply the associated tax.
     *                                        Only works when the parameter $usetax is true
     * @param int|null $id_customer           Customer ID (for customer group reduction)
     * @param int|null $id_cart               Cart ID. Required when the cookie is not accessible
     *                                        (e.g., inside a payment module, a cron task...)
     * @param int|null $id_address            Customer address ID. Required for price (tax included)
     *                                        calculation regarding the guest localization
     * @param null     $specific_price_output If a specific price applies regarding the previous parameters,
     *                                        this variable is filled with the corresponding SpecificPrice object
     * @param bool     $with_ecotax           Insert ecotax in price output.
     * @param bool     $use_group_reduction
     * @param Context  $context
     * @param bool     $use_customer_price
     * @return float                          Product price
     */
    public static function getPriceStatic($id_product, $usetax = true, $id_product_attribute = null, $decimals = 6, $divisor = null,
        $only_reduc = false, $usereduc = true, $quantity = 1, $force_associated_tax = false, $id_customer = null, $id_cart = null,
        $id_address = null, &$specific_price_output = null, $with_ecotax = true, $use_group_reduction = true, Context $context = null,
        $use_customer_price = true)
    {
        if (!$context) {
            $context = Context::getContext();
        }

        $cur_cart = $context->cart;

        if ($divisor !== null) {
            Tools::displayParameterAsDeprecated('divisor');
        }

        if (!Validate::isBool($usetax) || !Validate::isUnsignedId($id_product)) {
            die(Tools::displayError());
        }


        $id_group = (int)Configuration::get('OKOM_VIP_IDGROUP');

        // If there is cart in context or if the specified id_cart is different from the context cart id
        if (!is_object($cur_cart) || (Validate::isUnsignedInt($id_cart) && $id_cart && $cur_cart->id != $id_cart)) {
            /*
            * When a user (e.g., guest, customer, Google...) is on PrestaShop, he has already its cart as the global (see /init.php)
            * When a non-user calls directly this method (e.g., payment module...) is on PrestaShop, he does not have already it BUT knows the cart ID
            * When called from the back office, cart ID can be inexistant
            */
            if (!$id_cart && !isset($context->employee)) {
                die(Tools::displayError());
            }
            $cur_cart = new Cart($id_cart);
            // Store cart in context to avoid multiple instantiations in BO
            if (!Validate::isLoadedObject($context->cart)) {
                $context->cart = $cur_cart;
            }
        }

        $cart_quantity = 0;
        if ((int)$id_cart) {
            $cache_id = 'Product::getPriceStatic_'.(int)$id_product.'-'.(int)$id_cart;
            if (!Cache::isStored($cache_id) || ($cart_quantity = Cache::retrieve($cache_id) != (int)$quantity)) {
                $sql = 'SELECT SUM(`quantity`)
                FROM `'._DB_PREFIX_.'cart_product`
                WHERE `id_product` = '.(int)$id_product.'
                AND `id_cart` = '.(int)$id_cart;
                $cart_quantity = (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
                Cache::store($cache_id, $cart_quantity);
            } else {
                $cart_quantity = Cache::retrieve($cache_id);
            }
        }

        $id_currency = Validate::isLoadedObject($context->currency) ? (int)$context->currency->id : (int)Configuration::get('PS_CURRENCY_DEFAULT');

        // retrieve address informations
        $id_country = (int)$context->country->id;
        $id_state = 0;
        $zipcode = 0;

        if (!$id_address && Validate::isLoadedObject($cur_cart)) {
            $id_address = $cur_cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')};
        }

        if ($id_address) {
            $address_infos = Address::getCountryAndState($id_address);
            if ($address_infos['id_country']) {
                $id_country = (int)$address_infos['id_country'];
                $id_state = (int)$address_infos['id_state'];
                $zipcode = $address_infos['postcode'];
            }
        } elseif (isset($context->customer->geoloc_id_country)) {
            $id_country = (int)$context->customer->geoloc_id_country;
            $id_state = (int)$context->customer->id_state;
            $zipcode = $context->customer->postcode;
        }

        if (Tax::excludeTaxeOption()) {
            $usetax = false;
        }

        if ($usetax != false
            && !empty($address_infos['vat_number'])
            && $address_infos['id_country'] != Configuration::get('VATNUMBER_COUNTRY')
            && Configuration::get('VATNUMBER_MANAGEMENT')) {
            $usetax = false;
        }

        if (is_null($id_customer) && Validate::isLoadedObject($context->customer)) {
            $id_customer = $context->customer->id;
        }

        $return = Product::priceCalculation(
            $context->shop->id,
            $id_product,
            $id_product_attribute,
            $id_country,
            $id_state,
            $zipcode,
            $id_currency,
            $id_group,
            $quantity,
            $usetax,
            $decimals,
            $only_reduc,
            $usereduc,
            $with_ecotax,
            $specific_price_output,
            $use_group_reduction,
            $id_customer,
            $use_customer_price,
            $id_cart,
            $cart_quantity
        );

        return $return;
    }
}
