<?php
namespace Vendor\CustomOrderProcessing\Model;

use Vendor\CustomOrderProcessing\Api\StatusLogRepositoryInterface;
use Vendor\CustomOrderProcessing\Api\Data\StatusLogInterface;
use Vendor\CustomOrderProcessing\Model\ResourceModel\StatusLog as StatusLogResource;
use Vendor\CustomOrderProcessing\Model\StatusLogFactory;
use Vendor\CustomOrderProcessing\Model\ResourceModel\StatusLog\CollectionFactory;
use Vendor\CustomOrderProcessing\Model\Cache\StatusLogCache;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;

class StatusLogRepository implements StatusLogRepositoryInterface
{
    /**
     * @var StatusLogResource
     */
    private $resource;

    /**
     * @var StatusLogFactory
     */
    private $statusLogFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;
    
    /**
     * @var StatusLogCache
     */
    private $cache;
    
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param StatusLogResource $resource
     * @param StatusLogFactory $statusLogFactory
     * @param CollectionFactory $collectionFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param StatusLogCache $cache
     * @param SerializerInterface $serializer
     */
    public function __construct(
        StatusLogResource $resource,
        StatusLogFactory $statusLogFactory,
        CollectionFactory $collectionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor,
        StatusLogCache $cache,
        SerializerInterface $serializer
    ) {
        $this->resource = $resource;
        $this->statusLogFactory = $statusLogFactory;
        $this->collectionFactory = $collectionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->cache = $cache;
        $this->serializer = $serializer;
    }

    /**
     * @inheritDoc
     */
    public function save(StatusLogInterface $statusLog)
    {
        try {
            $this->resource->save($statusLog);
            // Invalidate cache for this order
            $this->cache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, [
                'order_id_' . $statusLog->getOrderId(),
                'increment_id_' . $statusLog->getIncrementId()
            ]);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the status log: %1',
                $exception->getMessage()
            ));
        }
        return $statusLog;
    }

    /**
     * @inheritDoc
     */
    public function getById($id)
    {
        $cacheKey = 'status_log_id_' . $id;
        $cachedData = $this->cache->load($cacheKey);
        
        if ($cachedData) {
            $statusLogData = $this->serializer->unserialize($cachedData);
            $statusLog = $this->statusLogFactory->create();
            $statusLog->setData($statusLogData);
            return $statusLog;
        }
        
        $statusLog = $this->statusLogFactory->create();
        $this->resource->load($statusLog, $id);
        
        if (!$statusLog->getId()) {
            throw new NoSuchEntityException(__('Status log with id "%1" does not exist.', $id));
        }
        
        // Cache the result
        $this->cache->save(
            $this->serializer->serialize($statusLog->getData()),
            $cacheKey,
            [
                StatusLogCache::CACHE_TAG,
                'order_id_' . $statusLog->getOrderId(),
                'increment_id_' . $statusLog->getIncrementId()
            ],
            3600 // Cache for 1 hour
        );
        
        return $statusLog;
    }

    /**
     * @inheritDoc
     */
    public function getByOrderId($orderId)
    {
        $cacheKey = 'status_log_order_id_' . $orderId;
        $cachedData = $this->cache->load($cacheKey);
        
        if ($cachedData) {
            return $this->serializer->unserialize($cachedData);
        }
        
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('order_id', $orderId)
            ->create();
        
        $result = $this->getList($searchCriteria);
        
        // Cache the result
        $this->cache->save(
            $this->serializer->serialize($result),
            $cacheKey,
            [StatusLogCache::CACHE_TAG, 'order_id_' . $orderId],
            3600 // Cache for 1 hour
        );
        
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getByIncrementId($incrementId)
    {
        $cacheKey = 'status_log_increment_id_' . $incrementId;
        $cachedData = $this->cache->load($cacheKey);
        
        if ($cachedData) {
            return $this->serializer->unserialize($cachedData);
        }
        
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', $incrementId)
            ->create();
        
        $result = $this->getList($searchCriteria);
        
        // Cache the result
        $this->cache->save(
            $this->serializer->serialize($result),
            $cacheKey,
            [StatusLogCache::CACHE_TAG, 'increment_id_' . $incrementId],
            3600 // Cache for 1 hour
        );
        
        return $result;
    }

    /**
     * Get list of status logs
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     */
    private function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->collectionFactory->create();
        
        $this->collectionProcessor->process($searchCriteria, $collection);
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        
        return $searchResults;
    }
}