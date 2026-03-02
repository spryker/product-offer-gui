<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductOfferGui\Dependency\Facade;

use Generated\Shared\Transfer\ProductOfferCriteriaTransfer;
use Generated\Shared\Transfer\ProductOfferResponseTransfer;
use Generated\Shared\Transfer\ProductOfferTransfer;

interface ProductOfferGuiToProductOfferFacadeInterface
{
    public function update(ProductOfferTransfer $productOfferTransfer): ProductOfferResponseTransfer;

    public function findOne(ProductOfferCriteriaTransfer $productOfferCriteria): ?ProductOfferTransfer;

    /**
     * @param string $currentStatus
     *
     * @return array<string>
     */
    public function getApplicableApprovalStatuses(string $currentStatus): array;
}
