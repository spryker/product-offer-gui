<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductOfferGui\Dependency\Facade;

interface ProductOfferGuiToStoreFacadeInterface
{
    /**
     * @return list<\Generated\Shared\Transfer\StoreTransfer>
     */
    public function getAllStores(): array;
}
