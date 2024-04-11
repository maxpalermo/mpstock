<?php
/**
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
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
 *  @author    Massimiliano Palermo <info@mpsoft.it>
 *  @copyright 2007-2018 Digital Solutions®
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockAdminImportXML.php';
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockAdminImportCSV.php';

class MpStockAdminImport
{
    public $ext_file = array(
        'xml',
        'csv',
    );
    public static $definition = array(
        'table' => 'mp_stock',
        'primary' => 'id_mp_stock',
        'multilang' => false,
        'fields' => array(
            'movement_type' => array(
                'type' => ObjectModelCore::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => 'true',
            ),
            'movement_date' => array(
                'type' => ObjectModelCore::TYPE_DATE,
                'validate' => 'isDate',
                'required' => 'true',
            ),
            'ean13' => array(
                'type' => ObjectModelCore::TYPE_STRING,
                'validate' => 'isString',
                'required' => 'true',
            ),
            'qty' => array(
                'type' => ObjectModelCore::TYPE_INT,
                'validate' => 'isInt',
                'required' => 'true',
            ),
            'price' => array(
                'type' => ObjectModelCore::TYPE_FLOAT,
                'validate' => 'isFloat',
                'required' => 'true',
            ),
            'wholesale_price' => array(
                'type' => ObjectModelCore::TYPE_FLOAT,
                'validate' => 'isFloat',
                'required' => 'true',
            ),
        ),
    );

    public $notifications;
    public $filename;
    public $extension;
    public $content;
    public $filesize;
    public $module;
    public $status;

    public function __construct($attachment)
    {
        /** Set status **/
        $this->status = true;
        /** Get module **/
        $this->module = new MpStock();
        /** Create notifications structure **/
        $this->notifications = array(
            'confirmations' => array(),
            'warnings' => array(),
            'errors' => array(),
            'notices' => array(),
        );

        /** Manage attachment file **/
        $this->attachment = $attachment;
        if (empty($attachment) || $attachment['error'] != 0 || $attachment['size'] == 0) {
            if (isset($attachment) && isset($attachment['content'])) {
                unset($attachment['content']);
            }
            $this->notifications['errors'][] = $this->module->l('File not valid.', get_class($this));
            $this->notifications['notices'] = array(
                'attachment' => isset($attachment) ? print_r($attachment, 1) : $this->module->l('No attachment', get_class($this)),
            );
            return false;
        }
        /** @var Get variables **/
        $this->filename = $attachment['name'];
        $this->extension = Tools::strtolower(pathinfo($attachment['name'], PATHINFO_EXTENSION));
        $this->content = $attachment['content'];
        $this->filesize = $attachment['size'];
        $this->notifications['notices'][] = array(
            'get variables' => 'done',
            'filename' => $this->filename,
            'filesize' => $this->filesize
        );
        /** Parse extension **/
        if (!in_array($this->extension, $this->ext_file)) {
            $this->notifications['errors'][] = $this->module->l('File extension not valid. Please select a CSV or XML file.');
            $this->notifications['notices'][] = array(
                'parse extension' => 'fail',
                'filename' => $this->filename,
                'extension' => $this->extension,
                'filesize' => $this->filesize,
            );
        }
        /** Parse content **/
        switch ($this->extension) {
            case 'xml':
                $this->notifications['notices'][] = array(
                    'parsing xml' => 'parsing...'
                );
                $importer = new MpStockAdminImportXML($this->content, $this->filename, $this);
                break;
            case 'csv':
                $this->notifications['notices'][] = array(
                    'parsing csv' => 'parsing...'
                );
                $importer = new MpStockAdminImportCSV($this->content, $this->filename, $this);
                break;
            default:
                $this->notifications['errors'][] = $this->module->l('Bad switch choice.');
                $this->notifications['notices'][] = array(
                    'filename' => $this->filename,
                    'extension' => $this->extension,
                    'filesize' => $this->filesize,
                );
                $this->status;
                return false;
        }
        $this->notifications['notices'][] = array(
            'Init parser' => 'done',
        );
        $result = $importer->parseContent();
        $this->notifications['notices'][] = array(
            'parse result' => (int) $result,
        );
        if (!$result) {
            array_merge($this->notifications['errors'], $importer->errors);
            $this->status = false;
            return false;
        } else {
            $this->status = true;
            return true;
        }
    }

    public function getNotifications()
    {
        return print_r($this->notifications, 1);
    }

    public function getTotNotifications()
    {
        return count($this->notifications['errors']) +
            count($this->notifications['warnings']) +
            count($this->notifications['confirmations']) +
            count($this->notifications['notices']);
    }

    public function addNotification($notification)
    {
        $this->notifications['notices'][] = $notification;
    }

    public function addConfirmation($confirmation)
    {
        $this->notifications['confirmations'][] = $confirmation;
    }

    public function addWarning($warning)
    {
        $this->notifications['warnings'][] = $warning;
    }

    public function addError($error)
    {
        $this->notifications['errors'][] = $error;
    }
}