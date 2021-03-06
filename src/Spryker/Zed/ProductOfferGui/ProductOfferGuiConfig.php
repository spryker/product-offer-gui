<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductOfferGui;

use Spryker\Zed\Kernel\AbstractBundleConfig;

class ProductOfferGuiConfig extends AbstractBundleConfig
{
    public const REQUEST_PARAM_ID_PRODUCT_OFFER = 'id-product-offer';
    public const REQUEST_PARAM_APPROVAL_STATUS = 'approval-status';

    /**
     * @uses \Spryker\Zed\ProductOfferGui\Communication\Controller\ProductOfferController::indexAction()
     */
    public const URL_PRODUCT_OFFER_LIST = '/product-offer-gui/list-product-offer';

    /**
     * @uses \Spryker\Zed\ProductOfferGui\Communication\Controller\EditController::updateApprovalStatusAction()
     */
    public const URL_UPDATE_APPROVAL_STATUS = '/product-offer-gui/edit/update-approval-status';

    /**
     * @uses \Spryker\Zed\ProductOfferGui\Communication\Controller\ViewController::indexAction()
     */
    public const URL_VIEW = '/product-offer-gui/view';
}
