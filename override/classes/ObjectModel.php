<?php
/**
 * 2007-2016 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2016 PrestaShop SA
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

abstract class ObjectModel extends ObjectModelCore
{
    /**
     * Adds current object to the database
     *
     * @param bool $auto_date
     * @param bool $null_values
     *
     * @return bool Insertion result
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function add($auto_date = true, $null_values = false)
    {
        if (isset($this->id) && !$this->force_id) {
            unset($this->id);
        }
        if ($this->force_id) {
            $this->forced_id = $this->id;
        }
        // @hook actionObject*AddBefore
        Hook::exec('actionObjectAddBefore', array('object' => $this));
        Hook::exec('actionObject'.get_class($this).'AddBefore', array('object' => $this));

        // Automatically fill dates
        if ($auto_date && property_exists($this, 'date_add')) {
            $this->date_add = date('Y-m-d H:i:s');
        }
        if ($auto_date && property_exists($this, 'date_upd')) {
            $this->date_upd = date('Y-m-d H:i:s');
        }

        if (Shop::isTableAssociated($this->def['table'])) {
            $id_shop_list = Shop::getContextListShopID();
            if (count($this->id_shop_list) > 0) {
                $id_shop_list = $this->id_shop_list;
            }
        }

        // Database insertion
        if (Shop::checkIdShopDefault($this->def['table'])) {
            $this->id_shop_default = (in_array(Configuration::get('PS_SHOP_DEFAULT'), $id_shop_list) == true) ? Configuration::get('PS_SHOP_DEFAULT') : min($id_shop_list);
        }
        if (!$result = Db::getInstance()->insert($this->def['table'], $this->getFields(), $null_values)) {
            return false;
        }

        // Get object id in database
        $this->id = Db::getInstance()->Insert_ID();
        
        // Database insertion for multishop fields related to the object
        if (Shop::isTableAssociated($this->def['table'])) {
            $fields = $this->getFieldsShop();
            $fields[$this->def['primary']] = (int)$this->id;

            foreach ($id_shop_list as $id_shop) {
                $fields['id_shop'] = (int)$id_shop;
                $result &= Db::getInstance()->insert($this->def['table'].'_shop', $fields, $null_values);
            }
        }

        if (!$result) {
            return false;
        }

        // Database insertion for multilingual fields related to the object
        if (!empty($this->def['multilang'])) {
            $fields = $this->getFieldsLang();
            if ($fields && is_array($fields)) {
                $shops = Shop::getCompleteListOfShopsID();
                $asso = Shop::getAssoTable($this->def['table'].'_lang');
                foreach ($fields as $field) {
                    foreach (array_keys($field) as $key) {
                        if (!Validate::isTableOrIdentifier($key)) {
                            throw new PrestaShopException('key '.$key.' is not table or identifier');
                        }
                    }
                    if ($this->force_id) {
                        $field[$this->def['primary']] = (int)$this->forced_id;    
                    } else {
                        $field[$this->def['primary']] = (int)$this->id;
                    }
                    
                    if ($asso !== false && $asso['type'] == 'fk_shop') {
                        foreach ($shops as $id_shop) {
                            $field['id_shop'] = (int)$id_shop;
                            $result &= Db::getInstance()->insert($this->def['table'].'_lang', $field);
                        }
                    } else {
                        $result &= Db::getInstance()->insert($this->def['table'].'_lang', $field);
                    }
                }
            }
        }

        // @hook actionObject*AddAfter
        Hook::exec('actionObjectAddAfter', array('object' => $this));
        Hook::exec('actionObject'.get_class($this).'AddAfter', array('object' => $this));

        return $result;
    }
}
