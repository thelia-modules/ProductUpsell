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

namespace ProductUpsell\EventListeners;

use ProductUpsell\Model\ProductUpsellQuery;
use ProductUpsell\ProductUpsell;
use Propel\Runtime\ActiveQuery\Criteria;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Core\Event\Cart\CartEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Model\CartItem;
use Thelia\Model\CartItemQuery;
use Thelia\Model\Map\ProductCategoryTableMap;
use Thelia\Model\ProductCategoryQuery;
use Thelia\TaxEngine\TaxEngine;

/**
 * Class ProductUpsellOnCartChange
 * @package ProductUpsell\EventListeners
 */
class ProductUpsellOnCartChange implements EventSubscriberInterface
{
    /**
     * @var \Thelia\Core\HttpFoundation\Request
     */
    protected $request;

    /**
     * @var TaxEngine
     */
    protected $taxEngine;

    public function __construct(
        Request $request,
        TaxEngine $taxEngine
    ) {
        $this->request = $request;
        $this->taxEngine = $taxEngine;
    }

    /**
     * Add, remove or change upsell product in the current cart, according to cart total
     *
     * @param CartEvent $event
     * @param $eventName
     * @param EventDispatcherInterface $dispatcher
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function updateCart(CartEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        // If no upsell category is defined, do nothing.
        if (0 === ProductUpsell::getUpsellCategoryId()) {
            return;
        }

        if (null === $cartItem = $event->getCartItem()) {
            $cartItem = CartItemQuery::create()->findPk((int) $event->getCartItemId());

            if (null === $cartItem) {
                return;
            }
        }

        // Do not process a product which belongs to upsell category
        if (ProductUpsell::isProductInUpsellCategory($cartItem->getProductId())) {
            return;
        }

        $taxCountry = $this->taxEngine->getDeliveryCountry();
        $taxState = $this->taxEngine->getDeliveryState();
        $totalCart = $event->getCart()->getTaxedAmount($taxCountry, true, $taxState);

        // Find upsell item, if any
        $applicableUpsellItem = ProductUpsellQuery::create()
            ->filterByMinimumcart($totalCart, Criteria::LESS_EQUAL)
            ->orderByMinimumcart(Criteria::DESC)
            ->findOne()
        ;

        $applicableItemAreadyInCart = false;

        // Add or remove products from cart, if required.
        foreach ($event->getCart()->getCartItems() as $cartItem) {
            if (ProductUpsell::isProductInUpsellCategory($cartItem->getProductId())) {
                $upsellItemInCart = ProductUpsellQuery::create()->findOneByProductId($cartItem->getProductId());

                // Delete cart item if not applicable
                if (null !== $upsellItemInCart) {
                    $applicableItemAreadyInCart = (null !== $applicableUpsellItem) && $upsellItemInCart->getId() === $applicableUpsellItem->getId();

                    if (!$applicableItemAreadyInCart) {
                        $dispatcher->dispatch(
                            TheliaEvents::CART_DELETEITEM,
                            (new CartEvent($event->getCart()))->setCartItemId($cartItem->getId())
                        );
                    }
                }
            }
        }

        // Add upsell product if required
        if (null !== $applicableUpsellItem && ! $applicableItemAreadyInCart) {
            $product = $applicableUpsellItem->getProduct();

            $tmpEvent = (new CartEvent($event->getCart()))
                ->setNewness(true)
                ->setAppend(false)
                ->setQuantity(1)
                ->setProductSaleElementsId($product->getDefaultSaleElements()->getId())
                ->setProduct($product->getId())
            ;

            $tmpEvent->upsell_add_allowed = true;

            $dispatcher->dispatch(TheliaEvents::CART_ADDITEM, $tmpEvent);
        }
    }

    /**
     * Prevent upsell product change or delete
     *
     * @param CartEvent $event
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function disableProductUpsellAddCartItem(CartEvent $event)
    {
        if (ProductUpsell::isProductInUpsellCategory($event->getProduct())) {
            if ($event->upsell_add_allowed !== true) {
                $event->stopPropagation();
            }
        }
    }

    /**
     * Prevent upsell product change or delete
     *
     * @param CartEvent $event
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function disableProductUpsellCartItemEdition(CartEvent $event)
    {
        $cartItem = CartItemQuery::create()->findPk($event->getCartItemId());

        if (null !== $cartItem && ProductUpsell::isProductInUpsellCategory($cartItem->getProductId())) {
            $event->stopPropagation();
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            TheliaEvents::CART_ADDITEM => [
                [ 'disableProductUpsellAddCartItem', 512 ],
                [ 'updateCart', 10 ]
            ],
            TheliaEvents::CART_UPDATEITEM => [
                ['disableProductUpsellCartItemEdition', 512],
                ['updateCart', 10]
            ],
            TheliaEvents::CART_DELETEITEM => ['updateCart', 10],
            TheliaEvents::COUPON_CONSUME  => ['updateCart', 10],
        ];
    }
}
