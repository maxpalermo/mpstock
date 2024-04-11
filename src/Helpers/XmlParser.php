<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Massimiliano Palermo <maxx.palermo@gmail.com>
 * @copyright Since 2016 Massimiliano Palermo
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace MpSoft\MpStock\Helpers;
if (!defined('_PS_VERSION_')) {
    exit;
}

class XmlParser
{
    protected $filename;
    protected $module;
    protected $controller_name;
    protected $path;

    public function __construct($filename)
    {
        $this->filename = $filename;
        $this->module = \Module::getInstanceByName('mpstock');
        $this->controller_name = get_class($this);
        $this->path = _PS_UPLOAD_DIR_ . $this->module->name . '/';
        if (!file_exists($this->path)) {
            mkdir($this->path, 0775, true);
        }
    }

    public function parse()
    {
        if (\ModelMpStockImport::fileExists($this->filename)) {
            \Context::getContext()->controller->errors[] = sprintf(
                $this->module->l('Il file %s è già presente in archivio.', $this->controller_name),
                $this->filename
            );

            return false;
        }

        if (!file_exists($this->path . $this->filename)) {
            \Context::getContext()->controller->errors[] = sprintf(
                $this->module->l('Il file %s non esiste.', $this->controller_name),
                $this->filename
            );

            return false;
        }

        $data = file_get_contents($this->path . $this->filename);
        $movement_date = '';
        $movement_type = 0;
        $import_type = preg_match('/^s/i', $this->filename) ? 'unload' : 'load';

        $sxi = new \SimpleXmlIterator($data);
        foreach ($sxi as $key => $elements) {
            if ($key == 'rows') {
                $rows = $elements->row;
                foreach ($elements as $key => $row) {
                    $model = new \ModelMpStockImport();
                    $model->filename = $this->filename;
                    $model->movement_date = $movement_date;
                    $model->movement_type = $movement_type;
                    $model->import_type = $import_type;
                    $model->sign = (int) $row->sign;
                    $model->ean13 = (string) $row->ean13;
                    $model->reference = (string) $row->reference;
                    $model->quantity = (int) $row->qty;
                    $model->price = (float) $row->price;
                    $model->wholesale_price = (float) $row->wholesale_price;
                    $model->id_employee = \Context::getContext()->employee->id;
                    $model->date_add = date('Y-m-d H:i:s');
                    $model->date_upd = date('Y-m-d H:i:s');

                    try {
                        $model->add();
                    } catch (\Throwable $th) {
                        \Context::getContext()->controller->errors[] = sprintf(
                            $this->module->l('Errore durante l\'importazione del file %s. %s', $this->controller_name),
                            $this->filename,
                            $th->getMessage()
                        );

                        return false;
                    }
                }
            } else {
                if ($key == 'movement_date') {
                    $movement_date = (string) $elements;
                } elseif ($key == 'movement_type') {
                    $movement_type = (int) $elements;
                }
            }
        }

        unlink($this->path . $this->filename);

        return true;
    }
}
