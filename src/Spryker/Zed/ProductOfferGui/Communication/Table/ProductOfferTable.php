<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductOfferGui\Communication\Table;

use Generated\Shared\Transfer\ProductOfferTableCriteriaTransfer;
use Generated\Shared\Transfer\QueryCriteriaTransfer;
use Orm\Zed\Product\Persistence\Map\SpyProductLocalizedAttributesTableMap;
use Orm\Zed\Product\Persistence\Map\SpyProductTableMap;
use Orm\Zed\ProductOffer\Persistence\Map\SpyProductOfferStoreTableMap;
use Orm\Zed\ProductOffer\Persistence\Map\SpyProductOfferTableMap;
use Orm\Zed\ProductOffer\Persistence\SpyProductOfferQuery;
use Orm\Zed\Store\Persistence\Map\SpyStoreTableMap;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Propel;
use Spryker\Service\UtilText\Model\Url\Url;
use Spryker\Shared\ProductOfferGui\ProductOfferGuiConfig as SharedProductOfferGuiConfig;
use Spryker\Shared\ProductOfferGui\ProductOfferStatusEnum;
use Spryker\Zed\Gui\Communication\Table\AbstractTable;
use Spryker\Zed\Gui\Communication\Table\TableConfiguration;
use Spryker\Zed\ProductOfferGui\Communication\Form\ApprovalStatusForm;
use Spryker\Zed\ProductOfferGui\Dependency\Facade\ProductOfferGuiToLocaleFacadeInterface;
use Spryker\Zed\ProductOfferGui\Dependency\Facade\ProductOfferGuiToProductOfferFacadeInterface;
use Spryker\Zed\ProductOfferGui\Persistence\ProductOfferGuiRepositoryInterface;
use Spryker\Zed\ProductOfferGui\ProductOfferGuiConfig;
use Spryker\Zed\Propel\PropelConfig;

class ProductOfferTable extends AbstractTable
{
    protected const string COL_STORES = 'stores';

    protected const string COL_ACTIONS = 'actions';

    protected const string COL_PRODUCT_NAME = 'product_name';

    protected const string COL_ID_PRODUCT_CONCRETE = 'id_product_concrete';

    protected const string STORE_CLASS_LABEL = 'label-info';

    /**
     * @var array<string, string>
     */
    protected const array APPROVAL_STATUS_CLASS_LABEL_MAPPING = [
        SharedProductOfferGuiConfig::STATUS_WAITING_FOR_APPROVAL => 'label-warning',
        SharedProductOfferGuiConfig::STATUS_APPROVED => 'label-success',
        SharedProductOfferGuiConfig::STATUS_DENIED => 'label-danger',
    ];

    /**
     * @var array<string, string>
     */
    protected const array APPROVAL_STATUS_CLASS_BUTTON_MAPPING = [
        SharedProductOfferGuiConfig::STATUS_APPROVED => 'btn-create',
        SharedProductOfferGuiConfig::STATUS_DENIED => 'btn-remove',
    ];

    /**
     * @var \Orm\Zed\ProductOffer\Persistence\SpyProductOfferQuery<mixed>
     */
    protected $productOfferQuery;

    /**
     * @var \Spryker\Zed\ProductOfferGui\Dependency\Facade\ProductOfferGuiToLocaleFacadeInterface
     */
    protected $localeFacade;

    /**
     * @var \Spryker\Zed\ProductOfferGui\Dependency\Facade\ProductOfferGuiToProductOfferFacadeInterface
     */
    protected $productOfferFacade;

    /**
     * @var \Spryker\Zed\ProductOfferGui\Persistence\ProductOfferGuiRepositoryInterface
     */
    protected $repository;

    /**
     * @var array<\Spryker\Zed\ProductOfferGuiExtension\Dependency\Plugin\ProductOfferTableExpanderPluginInterface>
     */
    protected $productOfferTableExpanderPlugins;

    protected ?ProductOfferTableCriteriaTransfer $productOfferTableCriteriaTransfer = null;

    protected ?ModelCriteria $productOfferIdQuery = null;

    /**
     * @param \Orm\Zed\ProductOffer\Persistence\SpyProductOfferQuery<mixed> $productOfferQuery
     * @param array<\Spryker\Zed\ProductOfferGuiExtension\Dependency\Plugin\ProductOfferTableExpanderPluginInterface> $productOfferTableExpanderPlugins
     */
    public function __construct(
        SpyProductOfferQuery $productOfferQuery,
        ProductOfferGuiToLocaleFacadeInterface $localeFacade,
        ProductOfferGuiToProductOfferFacadeInterface $productOfferFacade,
        ProductOfferGuiRepositoryInterface $repository,
        array $productOfferTableExpanderPlugins,
    ) {
        $this->productOfferQuery = $productOfferQuery;
        $this->localeFacade = $localeFacade;
        $this->productOfferFacade = $productOfferFacade;
        $this->repository = $repository;
        $this->productOfferTableExpanderPlugins = $productOfferTableExpanderPlugins;
    }

    /**
     * @return $this
     */
    public function applyCriteria(ProductOfferTableCriteriaTransfer $productOfferTableCriteriaTransfer)
    {
        $this->productOfferTableCriteriaTransfer = $productOfferTableCriteriaTransfer;

        return $this;
    }

    protected function configure(TableConfiguration $config): TableConfiguration
    {
        $url = Url::generate(
            '/table',
            $this->getRequest()->query->all(),
        );
        $config->setUrl($url);

        $config = $this->setHeader($config);

        $config->setSortable([
            SpyProductOfferTableMap::COL_ID_PRODUCT_OFFER,
            SpyProductOfferTableMap::COL_PRODUCT_OFFER_REFERENCE,
            SpyProductOfferTableMap::COL_CONCRETE_SKU,
            static::COL_PRODUCT_NAME,
            SpyProductOfferTableMap::COL_APPROVAL_STATUS,
            SpyProductOfferTableMap::COL_IS_ACTIVE,
        ]);

        $config->setRawColumns([
            static::COL_ACTIONS,
            SpyProductOfferTableMap::COL_APPROVAL_STATUS,
            SpyProductOfferTableMap::COL_IS_ACTIVE,
            SpyProductOfferStoreTableMap::COL_FK_STORE,
        ]);
        $config->setDefaultSortField(SpyProductOfferTableMap::COL_ID_PRODUCT_OFFER, TableConfiguration::SORT_DESC);

        $config->setSearchable([
            SpyProductOfferTableMap::COL_ID_PRODUCT_OFFER,
            SpyProductOfferTableMap::COL_PRODUCT_OFFER_REFERENCE,
            SpyProductOfferTableMap::COL_CONCRETE_SKU,
            SpyProductLocalizedAttributesTableMap::COL_NAME,
            SpyProductOfferTableMap::COL_APPROVAL_STATUS,
            SpyProductOfferTableMap::COL_IS_ACTIVE,
        ]);

        foreach ($this->productOfferTableExpanderPlugins as $productOfferTableExpanderPlugin) {
            $config = $productOfferTableExpanderPlugin->expandTableConfiguration($config);
        }

        return $config;
    }

    protected function setHeader(TableConfiguration $config): TableConfiguration
    {
        $baseData = [
            SpyProductOfferTableMap::COL_ID_PRODUCT_OFFER => 'Offer ID',
            SpyProductOfferTableMap::COL_PRODUCT_OFFER_REFERENCE => 'Reference',
            SpyProductOfferTableMap::COL_CONCRETE_SKU => 'SKU',
            static::COL_PRODUCT_NAME => 'Name',
            SpyProductOfferTableMap::COL_APPROVAL_STATUS => 'Approval',
            SpyProductOfferTableMap::COL_IS_ACTIVE => 'Status',
            SpyProductOfferStoreTableMap::COL_FK_STORE => 'Stores',
            static::COL_ACTIONS => 'Actions',
        ];

        $config->setHeader($baseData);

        return $config;
    }

    /**
     * @return \Orm\Zed\ProductOffer\Persistence\SpyProductOfferQuery<mixed>
     */
    protected function prepareQuery(): SpyProductOfferQuery
    {
        if ($this->productOfferTableCriteriaTransfer) {
            $this->applyFiltersCriteria();
        }

        $this->productOfferQuery = $this->repository->mapQueryCriteriaTransferToModelCriteria(
            $this->productOfferQuery,
            $this->buildQueryCriteriaTransfer(),
        );

        $this->total = $this->productOfferQuery->count();

        $this->productOfferIdQuery = $this->createProductOfferIdQuery($this->productOfferQuery);

        $this->productOfferQuery
            ->addJoin(
                SpyProductOfferTableMap::COL_CONCRETE_SKU,
                SpyProductTableMap::COL_SKU,
                Criteria::LEFT_JOIN,
            )
            ->addJoin(
                [SpyProductTableMap::COL_ID_PRODUCT, (string)$this->localeFacade->getCurrentLocale()->getIdLocale()],
                [SpyProductLocalizedAttributesTableMap::COL_FK_PRODUCT, SpyProductLocalizedAttributesTableMap::COL_FK_LOCALE],
                Criteria::LEFT_JOIN,
            )
            ->withColumn(SpyProductLocalizedAttributesTableMap::COL_NAME, static::COL_PRODUCT_NAME)
            ->withColumn(SpyProductTableMap::COL_ID_PRODUCT, static::COL_ID_PRODUCT_CONCRETE)
            ->withColumn(
                $this->prepareStoresSubQuery(),
                static::COL_STORES,
            );

        return $this->productOfferQuery;
    }

    protected function applyFiltersCriteria(): void
    {
        $this->applyStatusFilters();
        $this->applyApprovalStatusFilters();
        $this->applyStoreFilters();
    }

    protected function applyStatusFilters(): void
    {
        $status = $this->productOfferTableCriteriaTransfer->getStatus();
        if (!$status) {
            return;
        }

        $booleanStatus = $status === ProductOfferStatusEnum::ACTIVE->value;

        $this->productOfferQuery->filterByIsActive($booleanStatus);
    }

    protected function applyApprovalStatusFilters(): void
    {
        $approvalStatuses = $this->productOfferTableCriteriaTransfer->getApprovalStatuses();
        if (!$approvalStatuses) {
            return;
        }

        $this->productOfferQuery->filterByApprovalStatus_In($approvalStatuses);
    }

    protected function applyStoreFilters(?SpyProductOfferQuery $offerQuery = null): void
    {
        $stores = $this->productOfferTableCriteriaTransfer->getStores();
        if (!$stores) {
            return;
        }

        /** @var literal-string $storeFilterSubQuery */
        $storeFilterSubQuery = sprintf(
            'EXISTS (SELECT 1 FROM %s WHERE %s = %s AND %s IN (%s))',
            SpyProductOfferStoreTableMap::TABLE_NAME,
            SpyProductOfferStoreTableMap::COL_FK_PRODUCT_OFFER,
            SpyProductOfferTableMap::COL_ID_PRODUCT_OFFER,
            SpyProductOfferStoreTableMap::COL_FK_STORE,
            implode(',', array_map('intval', $stores)),
        );

        ($offerQuery ?? $this->productOfferQuery)->where($storeFilterSubQuery);
    }

    /**
     * @return array<mixed>
     */
    protected function prepareData(TableConfiguration $config): array
    {
        $queryResults = $this->runQuery($this->prepareQuery(), $config);
        $results = [];

        foreach ($queryResults as $item) {
            $results[] = $this->formatRow($item);
        }

        return $results;
    }

    /**
     * @param bool $returnRawResults
     *
     * @return \Propel\Runtime\Collection\ObjectCollection|array<mixed>
     */
    protected function runQuery(ModelCriteria $query, TableConfiguration $config, $returnRawResults = false): ObjectCollection|array
    {
        $order = $this->getOrders($config);
        $orderColumn = $this->getOrderByColumn($query, $config, $order);
        $isOrderingByPrimaryKey = str_contains($orderColumn, 'id_product_offer');

        if ($isOrderingByPrimaryKey && $this->canUseOptimizedQuery()) {
            return $this->runOptimizedQuery($query, $order, $returnRawResults);
        }

        return parent::runQuery($query, $config, $returnRawResults);
    }

    protected function canUseOptimizedQuery(): bool
    {
        $searchTerm = $this->getSearchTerm();
        $searchValue = $searchTerm[static::PARAMETER_VALUE] ?? '';

        return mb_strlen($searchValue) === 0;
    }

    /**
     * @param \Propel\Runtime\ActiveQuery\ModelCriteria<mixed> $query
     * @param array<mixed> $order
     *
     * @return \Propel\Runtime\Collection\ObjectCollection|array<mixed>
     */
    protected function runOptimizedQuery(
        ModelCriteria $query,
        array $order,
        bool $returnRawResults,
    ): ObjectCollection|array {
        $this->total = $this->filtered = $this->countTotal($query);
        $orderDirection = $order[0][static::SORT_BY_DIRECTION] ?? 'ASC';

        /** @var \Propel\Runtime\Collection\ObjectCollection $result */
        $result = ($this->productOfferIdQuery ?? $this->createProductOfferIdQuery($query))
            ->orderBy(SpyProductOfferTableMap::COL_ID_PRODUCT_OFFER, $orderDirection)
            ->offset($this->getOffset())
            ->limit($this->getLimit())
            ->find();

        $ids = $result->toArray();

        if (!$ids) {
            return $returnRawResults ? new ObjectCollection() : [];
        }

        // Fetch full data for these IDs with JOINs (only for 10-20 rows)
        $dataQuery = $this->productOfferQuery
            ->filterByIdProductOffer_In($ids);

        if ($this->isMysql()) {
            /** @var literal-string $fieldExpression */
            $fieldExpression = sprintf(
                'FIELD(%s, %s)',
                SpyProductOfferTableMap::COL_ID_PRODUCT_OFFER,
                implode(',', $ids), //@phpstan-ignore-line - IDs are guaranteed to be integers from the previous query
            );

            $dataQuery->withColumn($fieldExpression, 'field_order');
            $dataQuery->addAscendingOrderByColumn('field_order');
        } else {
            $caseStatements = [];
            foreach ($ids as $index => $id) {
                $caseStatements[] = sprintf('WHEN %s = %d THEN %d', SpyProductOfferTableMap::COL_ID_PRODUCT_OFFER, (int)$id, (int)$index);
            }
            /** @var literal-string $caseExpression */
            $caseExpression = sprintf(
                'CASE %s END',
                implode(' ', $caseStatements),
            );

            $dataQuery->withColumn($caseExpression, 'order_position');
            $dataQuery->addAscendingOrderByColumn('order_position');
        }

        /** @var \Propel\Runtime\Collection\ObjectCollection $data */
        $data = $dataQuery->find();

        if ($returnRawResults === true) {
            return $data;
        }

        return $data->toArray(null, false, TableMap::TYPE_COLNAME);
    }

    protected function createProductOfferIdQuery(ModelCriteria $query): ModelCriteria
    {
        return (clone $query)
            ->clearSelectColumns()
            ->select([SpyProductOfferTableMap::COL_ID_PRODUCT_OFFER]);
    }

    /**
     * @param array<string, mixed> $item
     *
     * @return array<string, mixed>
     */
    protected function formatRow(array $item): array
    {
        $rowData = [
            SpyProductOfferTableMap::COL_ID_PRODUCT_OFFER => $item[SpyProductOfferTableMap::COL_ID_PRODUCT_OFFER],
            SpyProductOfferTableMap::COL_PRODUCT_OFFER_REFERENCE => $item[SpyProductOfferTableMap::COL_PRODUCT_OFFER_REFERENCE],
            SpyProductOfferTableMap::COL_CONCRETE_SKU => $item[SpyProductOfferTableMap::COL_CONCRETE_SKU],
            static::COL_PRODUCT_NAME => $item[static::COL_PRODUCT_NAME],
            SpyProductOfferTableMap::COL_APPROVAL_STATUS => $this->createStatusLabel($item),
            SpyProductOfferTableMap::COL_IS_ACTIVE => $this->getActiveLabel($item[SpyProductOfferTableMap::COL_IS_ACTIVE]),
            SpyProductOfferStoreTableMap::COL_FK_STORE => $this->createStoresLabel($item),
            static::COL_ACTIONS => $this->buildLinks($item),
        ];

        foreach ($this->productOfferTableExpanderPlugins as $productOfferTableExpanderPlugin) {
            $rowData = $productOfferTableExpanderPlugin->expandData($rowData, $item);
        }

        $rowData[SpyProductOfferTableMap::COL_ID_PRODUCT_OFFER] = $this->formatInt(
            $rowData[SpyProductOfferTableMap::COL_ID_PRODUCT_OFFER],
        );

        return $rowData;
    }

    /**
     * @param array<string, mixed> $item
     */
    protected function buildLinks(array $item): string
    {
        $buttons = [];
        $buttons[] = $this->generateViewButton(
            Url::generate(
                ProductOfferGuiConfig::URL_VIEW,
                [
                    ProductOfferGuiConfig::REQUEST_PARAM_ID_PRODUCT_OFFER => $item[SpyProductOfferTableMap::COL_ID_PRODUCT_OFFER],
                ],
            ),
            'View',
            ['icon' => 'fa fa fa-eye', 'class' => 'btn-info'],
        );

        $availableApprovalStatusButtonsMapping = static::APPROVAL_STATUS_CLASS_BUTTON_MAPPING;
        unset($availableApprovalStatusButtonsMapping[$item[SpyProductOfferTableMap::COL_APPROVAL_STATUS]]);

        $availableApprovalStatuses = $this->productOfferFacade
            ->getApplicableApprovalStatuses($item[SpyProductOfferTableMap::COL_APPROVAL_STATUS]);

        foreach ($availableApprovalStatuses as $availableApprovalStatus) {
            $iconClass = $availableApprovalStatus === 'approved' ? 'fa fa fa-caret-right' : 'fa fa fa-trash';

            $buttons[] = $this->generateFormButton(
                Url::generate(
                    ProductOfferGuiConfig::URL_UPDATE_APPROVAL_STATUS,
                    [
                        ProductOfferGuiConfig::REQUEST_PARAM_ID_PRODUCT_OFFER => $item[SpyProductOfferTableMap::COL_ID_PRODUCT_OFFER],
                        ProductOfferGuiConfig::REQUEST_PARAM_APPROVAL_STATUS => $availableApprovalStatus,
                        ProductOfferGuiConfig::REQUEST_PARAM_ID_PRODUCT_CONCRETE => $item[static::COL_ID_PRODUCT_CONCRETE],
                    ],
                ),
                $availableApprovalStatus . '_offer_button',
                ApprovalStatusForm::class,
                ['icon' => $iconClass, 'class' => $availableApprovalStatusButtonsMapping[$availableApprovalStatus]],
            );
        }

        return implode(' ', $buttons);
    }

    /**
     * @param array<string, mixed> $item
     */
    protected function createStatusLabel(array $item): string
    {
        $currentStatus = $item[SpyProductOfferTableMap::COL_APPROVAL_STATUS];

        if (!isset(static::APPROVAL_STATUS_CLASS_LABEL_MAPPING[$currentStatus])) {
            return $currentStatus;
        }

        return $this->generateLabel($currentStatus, static::APPROVAL_STATUS_CLASS_LABEL_MAPPING[$currentStatus]);
    }

    public function getActiveLabel(bool $isActive): string
    {
        return $isActive ? $this->generateLabel('Active', 'label-primary') : $this->generateLabel('Inactive', 'badge-outline-dark');
    }

    /**
     * @param array<string, mixed> $item
     */
    protected function createStoresLabel(array $item): string
    {
        $storeNames = explode(',', $item[static::COL_STORES]);

        $storeLabels = [];
        foreach ($storeNames as $storeName) {
            if (!$storeName) {
                continue;
            }

            $storeLabels[] = $this->generateLabel($storeName, static::STORE_CLASS_LABEL);
        }

        return implode(' ', $storeLabels);
    }

    protected function buildQueryCriteriaTransfer(): QueryCriteriaTransfer
    {
        $queryCriteriaTransfer = new QueryCriteriaTransfer();

        foreach ($this->productOfferTableExpanderPlugins as $productOfferTableExpanderPlugin) {
            $queryCriteriaTransfer = $productOfferTableExpanderPlugin->expandQueryCriteria($queryCriteriaTransfer);
        }

        return $queryCriteriaTransfer;
    }

    /**
     * @param \Propel\Runtime\ActiveQuery\ModelCriteria<mixed> $query
     */
    protected function countTotal(ModelCriteria $query): int
    {
        return $this->total;
    }

    protected function prepareStoresSubQuery(): string
    {
        $selectString = '(SELECT GROUP_CONCAT(%s) FROM %s INNER JOIN %s ON %s = %s WHERE %s = %s)';
        if (!$this->isMysql()) {
            $selectString = '(SELECT STRING_AGG(%s, \',\') FROM %s INNER JOIN %s ON %s = %s WHERE %s = %s)';
        }

        return sprintf(
            $selectString,
            SpyStoreTableMap::COL_NAME,
            SpyStoreTableMap::TABLE_NAME,
            SpyProductOfferStoreTableMap::TABLE_NAME,
            SpyProductOfferStoreTableMap::COL_FK_STORE,
            SpyStoreTableMap::COL_ID_STORE,
            SpyProductOfferStoreTableMap::COL_FK_PRODUCT_OFFER,
            SpyProductOfferTableMap::COL_ID_PRODUCT_OFFER,
        );
    }

    protected function isMysql(): bool
    {
        return Propel::getServiceContainer()->getAdapterClass() === PropelConfig::DB_ENGINE_MYSQL;
    }
}
