<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductOfferGui\Communication\Controller;

use Generated\Shared\Transfer\ProductOfferCriteriaTransfer;
use Generated\Shared\Transfer\ProductOfferTransfer;
use Spryker\Zed\Kernel\Communication\Controller\AbstractController;
use Spryker\Zed\ProductOfferGui\ProductOfferGuiConfig;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \Spryker\Zed\ProductOfferGui\Communication\ProductOfferGuiCommunicationFactory getFactory()
 * @method \Spryker\Zed\ProductOfferGui\Persistence\ProductOfferGuiRepositoryInterface getRepository()
 */
class ViewController extends AbstractController
{
    /**
     * @var string
     */
    protected const PARAM_ID_PRODUCT_OFFER = 'id-product-offer';

    /**
     * @var string
     */
    protected const MESSAGE_PRODUCT_OFFER_NOT_FOUND = 'Product offer not found';

    /**
     * @phpstan-return \Symfony\Component\HttpFoundation\RedirectResponse|array<string, mixed>
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|array
     */
    public function indexAction(Request $request)
    {
        $idProductOffer = $this->castId($request->get(
            static::PARAM_ID_PRODUCT_OFFER,
        ));

        $productOfferCriteria = (new ProductOfferCriteriaTransfer())
            ->setIdProductOffer($idProductOffer);

        $productOfferTransfer = $this->getFactory()
            ->getProductOfferFacade()
            ->findOne($productOfferCriteria);

        if (!$productOfferTransfer) {
            $this->addErrorMessage(static::MESSAGE_PRODUCT_OFFER_NOT_FOUND);

            return $this->redirectResponse(ProductOfferGuiConfig::URL_PRODUCT_OFFER_LIST);
        }

        $applicableProductOfferStatuses = $this->getFactory()
            ->getProductOfferFacade()
            ->getApplicableApprovalStatuses($productOfferTransfer->getApprovalStatus());

        $productConcreteTransfer = $this->getFactory()
            ->getProductFacade()
            ->getProductConcrete($productOfferTransfer->getConcreteSku());

        return $this->viewResponse([
            'applicableProductOfferStatuses' => $applicableProductOfferStatuses,
            'productConcrete' => $productConcreteTransfer,
            'productOffer' => $productOfferTransfer,
            'localeCollection' => $this->getFactory()->getLocaleFacade()->getLocaleCollection(),
            'relatedStores' => $productOfferTransfer->getStores(),
            'viewSections' => $this->getViewSections($productOfferTransfer),
        ]);
    }

    /**
     * @phpstan-return array<string, mixed>
     *
     * @param \Generated\Shared\Transfer\ProductOfferTransfer $productOfferTransfer
     *
     * @return array
     */
    protected function getViewSections(ProductOfferTransfer $productOfferTransfer): array
    {
        $viewPlugins = [];

        foreach ($this->getFactory()->getProductOfferViewSectionPlugins() as $productOfferViewSectionPlugin) {
            $viewPlugins[$productOfferViewSectionPlugin->getTemplate()] = $productOfferViewSectionPlugin->getData($productOfferTransfer);
        }

        return $viewPlugins;
    }
}
