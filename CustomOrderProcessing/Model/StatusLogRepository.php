<?php
namespace Vendor\CustomOrderProcessing\Model;

use Vendor\CustomOrderProcessing\Api\StatusLogRepositoryInterface;
use Vendor\CustomOrderProcessing\Api\Data\StatusLogInterface;
use Vendor\CustomOrderProcessing\Model\ResourceModel\StatusLog as StatusLogResource;
use Vendor\CustomOrderProcessing\Model\StatusLogFactory;
use Vendor\CustomOrderProcessing\Model\ResourceModel\StatusLog\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

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
     * @param StatusLogResource $resource
     * @param StatusLogFactory $statusLogFactory
     * @param CollectionFactory $collectionFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        StatusLogResource $resource,
        StatusLogFactory $statusLogFactory,
        CollectionFactory $collectionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->statusLogFactory = $statusLogFactory;
        $this->collectionFactory = $collectionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(StatusLogInterface $statusLog)
    {
        try {
            $this->resource->save($statusLog);
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
        $statusLog = $this->statusLogFactory->create();
        $this->resource->load($statusLog, $id);
        if (!$statusLog->getId()) {
            throw new NoSuchEntityException(__('Status log with id "%1" does not exist.', $id));
        }
        return $statusLog;
    }

    /**
     * @inheritDoc
     */
    public function getByOrderId($orderId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('order_id', $orderId)
            ->create();
        
        return $this->getList($searchCriteria);
    }

    /**
     * @inheritDoc
     */
    public function getByIncrementId($incrementId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', $incrementId)
            ->create();
        
        return $this->getList($searchCriteria);
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
