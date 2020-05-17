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


use ProductUpsell\ProductUpsell;
use Thelia\Core\Template\Element\ArraySearchLoopInterface;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;

/**
 * @method getCategoryId()
 */
class ProductUpsellCategoryLoop extends BaseLoop implements ArraySearchLoopInterface
{
    /***
     * @return ArgumentCollection
     */
    protected function getArgDefinitions()
    {
        return new ArgumentCollection(
            Argument::createIntTypeArgument('category_id')
        );
    }


    public function buildArray()
    {
        if ((0 !== $upsellCategoryId = ProductUpsell::getUpsellCategoryId()) && $upsellCategoryId === (int) $this->getCategoryId()) {
            return [ $upsellCategoryId ];
        }

        return [];
    }

    /**
     * @param LoopResult $loopResult
     *
     * @return LoopResult
     */
    public function parseResults(LoopResult $loopResult)
    {
        foreach ($loopResult->getResultDataCollection() as $upsellCategoryId) {
            $loopResultRow = new LoopResultRow();

            $loopResultRow->set("UPSELL_CATEGORY_ID", $upsellCategoryId);

            $loopResult->addRow($loopResultRow);
        }
        return $loopResult;
    }
}
