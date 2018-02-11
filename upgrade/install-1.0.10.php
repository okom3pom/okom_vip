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

function upgrade_module_1_0_10($object)
{
    $table_name = 'vip';
    $success = true;
    if (!$object->isRegisteredInHook('Header')) {
        $object->registerHook('Header');
    }
    $sql = "SHOW COLUMNS FROM "._DB_PREFIX_.$table_name." LIKE 'recall'";
    $res = Db::getInstance()->executeS($sql);
    if (!isset($res[0]['Field'])) {
        $add = "ALTER TABLE `"._DB_PREFIX_.$table_name."`  ADD `recall` int(1) NOT NULL default '0';";
        if (!Db::getInstance()->Execute($add)) {
            $success = false;
            return $success;
        }
    }
    $sql = "SHOW COLUMNS FROM "._DB_PREFIX_.$table_name." LIKE 'expired'";
    $res = Db::getInstance()->executeS($sql);
    if (!isset($res[0]['Field'])) {
        $add = "ALTER TABLE `"._DB_PREFIX_.$table_name."` ADD `expired` int(1) NOT NULL default '0';";
        if (!Db::getInstance()->Execute($add)) {
            $success = false;
            return $success;
        }
    }
    return $success;
}
