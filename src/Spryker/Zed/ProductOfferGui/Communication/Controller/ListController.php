<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductOfferGui\Communication\Controller;

use Spryker\Zed\Kernel\Communication\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @method \Spryker\Zed\ProductOfferGui\Communication\ProductOfferGuiCommunicationFactory getFactory()
 * @method \Spryker\Zed\ProductOfferGui\Persistence\ProductOfferGuiRepositoryInterface getRepository()
 */
class ListController extends AbstractController
{
    /**
     * @return array<string, mixed>
     */
    public function indexAction(): array
    {
        $productOfferTable = $this->getFactory()
            ->createProductOfferTable();

        $viewData = $this->executeProductOfferListActionViewDataExpanderPlugins([
            'productOfferTable' => $productOfferTable->render(),
        ]);

        return $this->viewResponse($viewData);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function tableAction(): JsonResponse
    {
        $productOfferTable = $this->getFactory()
            ->createProductOfferTable();

        return $this->jsonResponse($productOfferTable->fetchData());
    }

    /**
     * @param array<string, mixed> $viewData
     *
     * @return array<string, mixed>
     */
    protected function executeProductOfferListActionViewDataExpanderPlugins(array $viewData): array
    {
        foreach ($this->getFactory()->getProductOfferListActionViewDataExpanderPlugins() as $productOfferListActionViewDataExpanderPlugin) {
            $viewData = $productOfferListActionViewDataExpanderPlugin->expand($viewData);
        }

        return $viewData;
    }
}
