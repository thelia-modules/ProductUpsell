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

namespace ProductUpsell\Controller\Admin;

use ProductUpsell\ProductUpsell;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Tools\URL;

class ConfigController extends BaseAdminController
{
    public function editConfigAction()
    {
        if (null !== $response = $this->checkAuth(AdminResources::MODULE, [ProductUpsell::DOMAIN_NAME], AccessManager::UPDATE)) {
            return $response;
        }

        $configForm = $this->createForm('productupsell.configuration.form');

        try {
            $configForm = $this->validateForm($configForm);

            ProductUpsell::setUpsellCategoryId((int) $configForm->get(ProductUpsell::PRODUCT_UPSELL_CATEGORY_CONF_NAME)->getData());
        } catch (\Exception $exception) {
            $error_message = $exception->getMessage();
            $configForm->setErrorMessage($error_message);
            $this->getParserContext()
                ->addForm($configForm)
                ->setGeneralError($error_message);
        }

        return $this->generateRedirect(URL::getInstance()->absoluteUrl('/admin/module/ProductUpsell'));
    }
}
