<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\ProductOfferGui\Communication\Table;

use Generated\Shared\Transfer\QueryCriteriaTransfer;
use Generated\Shared\Transfer\QueryJoinTransfer;
use Orm\Zed\ProductOffer\Persistence\Map\SpyProductOfferStoreTableMap;
use Orm\Zed\ProductOffer\Persistence\Map\SpyProductOfferTableMap;
use Propel\Runtime\ActiveQuery\Criteria;
use Spryker\Zed\Gui\Communication\Table\TableConfiguration;
use Spryker\Zed\ProductOfferGuiExtension\Dependency\Plugin\ProductOfferTableExpanderPluginInterface;

class ProductOfferTableStoreJoinExpanderPluginStub implements ProductOfferTableExpanderPluginInterface
{
    public const string COL_JOINED_ID_STORE = 'joined_id_store';

    public function __construct(protected int $idStore)
    {
    }

    public function expandQueryCriteria(QueryCriteriaTransfer $queryCriteriaTransfer): QueryCriteriaTransfer
    {
        $queryJoinTransfer = (new QueryJoinTransfer())
            ->setJoinType(Criteria::INNER_JOIN)
            ->setLeft([SpyProductOfferTableMap::COL_ID_PRODUCT_OFFER, $this->idStore])
            ->setRight([SpyProductOfferStoreTableMap::COL_FK_PRODUCT_OFFER, SpyProductOfferStoreTableMap::COL_FK_STORE]);

        return $queryCriteriaTransfer
            ->addJoin($queryJoinTransfer)
            ->setWithColumns([SpyProductOfferStoreTableMap::COL_FK_STORE => static::COL_JOINED_ID_STORE]);
    }

    public function expandTableConfiguration(TableConfiguration $config): TableConfiguration
    {
        return $config;
    }

    /**
     * @param array<string, mixed> $rowData
     * @param array<string, mixed> $productOfferData
     *
     * @return array<string, mixed>
     */
    public function expandData(array $rowData, array $productOfferData): array
    {
        $rowData[static::COL_JOINED_ID_STORE] = $productOfferData[static::COL_JOINED_ID_STORE];

        return $rowData;
    }
}
