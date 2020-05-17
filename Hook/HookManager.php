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

namespace ProductUpsell\Hook;

use ProductUpsell\Model\ProductUpsellQuery;
use ProductUpsell\ProductUpsell;
use Thelia\Core\Event\Hook\HookRenderBlockEvent;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;
use Thelia\Model\ProductQuery;
use Thelia\Tools\URL;

/**
 * Class HookManager
 * @package ProductUpsell\Hook
 * @author  Franck Allimant <franck@cqfdev.fr>
 */
class HookManager extends BaseHook
{
    public function onProductModification(HookRenderEvent $event)
    {
        $productId = (int) $event->getArgument('product_id');

        $categoryId = ProductQuery::create()->findPk($productId)->getDefaultCategoryId();

        // Render
        if (null !== $productUpsell = ProductUpsellQuery::create()->findOneByProductId($productId)) {
            $amount = $productUpsell->getMinimumcart();
        } else {
            $amount = 0;
        }

        $event->add(
            $this->render(
                'hook/product-edit.html',
                [
                    'upsell_cart_amount' => $amount,
                    'product_id' => $productId,
                    'category_id' => $categoryId
                ]
            )
        );
    }

    public function onMainTopMenuTools(HookRenderBlockEvent $event)
    {
        $event->add(
            [
                'id' => 'tools_menu_product_upsell',
                'class' => '',
                'url' => URL::getInstance()->absoluteUrl('/admin/module/ProductUpsell'),
                'title' => $this->trans('Manage upsell products', [], ProductUpsell::DOMAIN_NAME)
            ]
        );
    }
}
