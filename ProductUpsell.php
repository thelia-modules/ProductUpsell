<?php
/*************************************************************************************/
/*      This file is part of the Thelia package.                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : dev@thelia.net                                                       */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace ProductUpsell;

use Propel\Runtime\Connection\ConnectionInterface;
use Thelia\Install\Database;
use Thelia\Model\Map\ProductCategoryTableMap;
use Thelia\Model\ProductCategoryQuery;
use Thelia\Module\BaseModule;

class ProductUpsell extends BaseModule
{
    /** @var string */
    const DOMAIN_NAME = 'productupsell';
    const PRODUCT_UPSELL_CATEGORY_CONF_NAME = 'productupsell_category';

    /**
     * @param ConnectionInterface $con
     */
    public function postActivation(ConnectionInterface $con = null)
    {
        if (! self::getConfigValue('is_initialized', false)) {
            $database = new Database($con);
            $database->insertSql(null, [__DIR__ . "/Config/thelia.sql"]);
            self::setConfigValue('is_initialized', true);
        }
    }

    /**
     * @param int $productId
     * @return bool
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public static function isProductInUpsellCategory($productId)
    {
        static $cache = [];

        if (empty($cache[$productId])) {
            $productCategories = ProductCategoryQuery::create()
                ->filterByProductId($productId)
                ->select([ProductCategoryTableMap::CATEGORY_ID])
                ->find()
                ->getData();

            $cache[$productId] = in_array(ProductUpsell::getUpsellCategoryId(), $productCategories);
        }

        return $cache[$productId];
    }

    /*
     * @return int Category id
     * 0 mean no category selected
     */
    public static function getUpsellCategoryId()
    {
        return (int) self::getConfigValue(self::PRODUCT_UPSELL_CATEGORY_CONF_NAME, 0);
    }

    public static function setUpsellCategoryId($categoryId)
    {
        self::setConfigValue(self::PRODUCT_UPSELL_CATEGORY_CONF_NAME, $categoryId);
    }
}
