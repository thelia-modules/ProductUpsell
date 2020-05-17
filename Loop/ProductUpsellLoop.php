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

namespace ProductUpsell\Loop;


use ProductUpsell\ProductUpsell as ProductUpsellModule;
use ProductUpsell\Model\ProductUpsell;
use ProductUpsell\Model\ProductUpsellQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;
use Thelia\Model\ProductQuery;

class ProductUpsellLoop extends BaseLoop implements PropelSearchLoopInterface
{

    public $countable = true;
    public $timestampable = false;
    public $versionable = false;

    /***
     * @return ArgumentCollection
     */
    protected function getArgDefinitions()
    {
        return new ArgumentCollection(
            Argument::createIntListTypeArgument('id'),
            Argument::createIntListTypeArgument('product_id'),
            Argument::createIntListTypeArgument('cart_amount')
        );
    }


    public function buildModelCriteria()
    {
        $search = ProductUpsellQuery::create();

        /** @noinspection PhpUndefinedMethodInspection */
        if (null !== $id = $this->getId()) {
            $search->filterById($id, Criteria::EQUAL);
        }

        /** @noinspection PhpUndefinedMethodInspection */
        if (null !== $productId = $this->getProductId()) {
            $search->filterByProductId($productId, Criteria::IN);
        }

        /** @noinspection PhpUndefinedMethodInspection */
        if (null !== $cartAmount = $this->getCartAmount()) {
            $search->filterByMinimumcart($cartAmount, Criteria::LESS_EQUAL);
        }

        $search->orderByMinimumcart(Criteria::ASC);

        return $search;
    }

    /**
     * @param LoopResult $loopResult
     *
     * @return LoopResult
     */
    public function parseResults(LoopResult $loopResult)
    {
        $upsellCategoryId = ProductUpsellModule::getUpsellCategoryId();

        /** @var ProductUpsell $productUpsell */
        foreach ($loopResult->getResultDataCollection() as $productUpsell) {

            /** @noinspection PhpParamsInspection */
            $product = ProductQuery::create()->findOneById($productUpsell->getProductId());
            if ($product->getDefaultCategoryId() !== $upsellCategoryId) {
                continue;
            }
            $loopResultRow = new LoopResultRow($productUpsell);
            $loopResultRow
                ->set("PRODUCT_UPSELL_ID", $productUpsell->getId())
                ->set("PRODUCT_ID", $productUpsell->getProductId())
                ->set("MINIMUM_CART_AMOUNT", $productUpsell->getMinimumcart());
            $loopResult->addRow($loopResultRow);
        }
        return $loopResult;
    }
}
