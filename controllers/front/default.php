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
 * @version   1.0.3
 * @license   Free
 */

include_once(dirname(__FILE__).'/../../okom_vip.php');

class okom_vipDefaultModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
		
		$module = new okom_vip();

		$customer_vip = $module->isVIP((int)$this->context->customer->id);
		
		if( $customer_vip == false ) {
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
		
        $this->context->smarty->assign(array(
            'customer_vip' => $customer_vip,
            'is_vip' => $is_vip,
			'exprired' => $exprired
        ));
 
        $this->setTemplate('vip.tpl');
    }
}
