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

namespace ProductUpsell\Form;

use ProductUpsell\ProductUpsell;
use Propel\Runtime\ActiveQuery\Criteria;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;
use Thelia\Model\Category;
use Thelia\Model\CategoryQuery;
use Thelia\Model\Lang;
use Thelia\Model\Tools\ModelCriteriaTools;

class ConfigurationForm extends BaseForm
{
    /**
     *
     * in this function you add all the fields you need for your Form.
     * Form this you have to call add method on $this->formBuilder attribute :
     *
     * @return null
     */
    protected function buildForm()
    {
        $locale = $this->getRequest()->getSession()->getLang()->getLocale();

        $categories = CategoryQuery::create()
            ->orderByPosition(Criteria::ASC)
            ->find();
        ;

        $choices = [
            0 => $this->translator->trans('Please select...', [], ProductUpsell::DOMAIN_NAME)
        ];

        /** @var Category $category */
        foreach ($categories as $category) {
            $choices[$category->getId()] = $category->setLocale($locale)->getTitle();
        }

        $this->formBuilder->add(
            ProductUpsell::PRODUCT_UPSELL_CATEGORY_CONF_NAME,
            ChoiceType::class,
            [
                'required' => true,
                'label' => $this->translator->trans('Upsell products category', [], ProductUpsell::DOMAIN_NAME),
                'choices' => $choices,
                'data' => ProductUpsell::getUpsellCategoryId(),
                'label_attr' => [
                    'help' => $this->translator->trans('Please select the category the upsell products belongs to.', [], ProductUpsell::DOMAIN_NAME),
                ]
            ]
        );
    }

    public function getName()
    {
        return "productupsell_config";
    }
}
