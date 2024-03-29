<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductOfferGui\Communication\Controller;

use Generated\Shared\Transfer\ProductOfferTransfer;
use Spryker\Zed\Kernel\Communication\Controller\AbstractController;
use Spryker\Zed\ProductOfferGui\ProductOfferGuiConfig;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \Spryker\Zed\ProductOfferGui\Communication\ProductOfferGuiCommunicationFactory getFactory()
 * @method \Spryker\Zed\ProductOfferGui\Persistence\ProductOfferGuiRepositoryInterface getRepository()
 */
class EditController extends AbstractController
{
    /**
     * @var string
     */
    protected const MESSAGE_SUCCESS_APPROVAL_STATUS_UPDATE = 'The approval status was updated';

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateApprovalStatusAction(Request $request): RedirectResponse
    {
        $approvalStatus = $request->get(ProductOfferGuiConfig::REQUEST_PARAM_APPROVAL_STATUS);
        $idProductOffer = $this->castId($request->get(ProductOfferGuiConfig::REQUEST_PARAM_ID_PRODUCT_OFFER));
        $idProductConcrete = $this->castId($request->get(ProductOfferGuiConfig::REQUEST_PARAM_ID_PRODUCT_CONCRETE));

        $productOfferResponseTransfer = $this->getFactory()
            ->getProductOfferFacade()
            ->update(
                (new ProductOfferTransfer())
                    ->setIdProductOffer($idProductOffer)
                    ->setApprovalStatus($approvalStatus)
                    ->setIdProductConcrete($idProductConcrete),
            );

        if ($productOfferResponseTransfer->getIsSuccessful()) {
            $this->addSuccessMessage(static::MESSAGE_SUCCESS_APPROVAL_STATUS_UPDATE);

            return $this->redirectResponse($request->headers->get('referer'));
        }

        foreach ($productOfferResponseTransfer->getErrors() as $productOfferErrorTransfer) {
            $this->addErrorMessage($productOfferErrorTransfer->getMessage());
        }

        return $this->redirectResponse($request->headers->get('referer'));
    }
}
