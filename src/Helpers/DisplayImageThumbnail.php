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

class DisplayImageThumbnail
{
    public static function displayImageThumbnail($id_product, $id_image, $image, $size = 'small_default')
    {
        $image = new \Image($id_image);
        $imagePath = $image->getPathForCreation();
        $imageSize = \Image::getSize($size);
        $imageThumbPath = \ImageManager::thumbnail($imagePath, $size . '_' . $id_image . '.' . $image->image_format, $imageSize['width'], $imageSize['height']);
        return $imageThumbPath;
    }

    public static function displayImageAttribute($id_product_attribute, $id_lang = null)
    {
        if (!$id_lang) {
            $id_lang = (int) \Context::getContext()->language->id;
        }

        $comb = new \Combination($id_product_attribute, $id_lang);
        if (!\Validate::isLoadedObject($comb)) {
            return self::displayImage404();
        }

        /** @var array */
        $images = \Image::getImages($id_lang, $comb->id_product, $comb->id);
        if ($images) {
            $image = new \Image((int) $images[0]['id_image']);
            if (!\Validate::isLoadedObject($image)) {
                return self::displayImage($comb->id_product);
            }
            $path = _PS_IMG_DIR_ . 'p/' . $image->getImgPath() . '.' . $image->image_format;
            if (file_exists($path)) {
                return '<img src="' . \Context::getContext()->link->getImageLink($image->getImgPath(), $image->id_image, 'small_default') . '" class="img-thumbnail" style="max-width: 50px; max-height: 50px; object-fit: contain;">';
            }
        }
        return self::displayImage404();
    }
    public static function displayImage($id_product, $type = 'small_default', $width = 50, $height = 50, $size = '3x')
    {
        /** @var array */
        $cover = \Image::getCover((int) $id_product);
        if ($cover) {
            $image = new \Image((int) $cover['id_image']);
            $path = _PS_IMG_DIR_ . 'p/' . $image->getImgPath() . '.' . $image->image_format;
            if (file_exists($path)) {
                return sprintf(
                    '<img src="%s" class="img-thumbnail" style="max-width: %dpx; max-height: %dpx; object-fit: contain;">',
                    \Context::getContext()->link->getImageLink($image->getImgPath(), $image->id_image, $type),
                    $width,
                    $height
                );
            }
        }
        return self::displayImage404($size);
    }

    public static function displayImage404($size = '3x')
    {
        return '<i class="icon icon-' . $size . ' icon-picture"></i>';
    }
}