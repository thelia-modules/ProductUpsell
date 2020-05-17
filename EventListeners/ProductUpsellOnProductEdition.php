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
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Thelia\Action\BaseAction;
use Thelia\Core\Event\Product\ProductUpdateEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\Event\TheliaFormEvent;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Core\Translation\Translator;

class ProductUpsellOnProductEdition extends BaseAction implements EventSubscriberInterface
{

    /** @var \Thelia\Core\HttpFoundation\Request */
    protected $request;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param ProductUpdateEvent $event
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function saveUpsellProduct(ProductUpdateEvent $event)
    {
        $productId = $event->getProductId();

        if (! ProductUpsell::isProductInUpsellCategory($productId)) {
            return;
        }

        if (null === $productUpsell = ProductUpsellQuery::create()->findOneByProductId($productId)) {
            $productUpsell = (new \ProductUpsell\Model\ProductUpsell())->setProductId($productId);
        }

        $productUpsell
            ->setMinimumcart($event->minimum_cart_amount)
            ->save();
    }

    /**
     * @param TheliaFormEvent $event
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function addMinimumCartField(TheliaFormEvent $event)
    {
        $productId = $this->request->get("product_id");

        if (! ProductUpsell::isProductInUpsellCategory($productId)) {
            return;
        }

        $event->getForm()->getFormBuilder()
            ->add(
                'minimum_cart_amount',
                NumberType::class,
                [
                    'constraints' => [],
                    'required' => false,
                    'label'      => Translator::getInstance()->trans('Product is added to cart if cart amout is greater than', [], ProductUpsell::DOMAIN_NAME),
                    'label_attr' => [
                        'for' => 'minimum_cart_amount',
                        'help' => Translator::getInstance()->trans(
                            'Minimum cart amout to automatically add this product to the customer cart.',
                            [],
                            ProductUpsell::DOMAIN_NAME
                        )
                    ],
                    'attr' => [
                        'step' => 0.01
                    ]
                ]
            );
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            TheliaEvents::FORM_AFTER_BUILD.'.thelia_product_modification'  => ['addMinimumCartField', 128],
            TheliaEvents::PRODUCT_UPDATE => ['saveUpsellProduct', 96],
        ];
    }
}
