<?php
namespace Vendor\CustomOrderProcessing\Api;

use Vendor\CustomOrderProcessing\Api\Data\StatusLogInterface;

interface StatusLogRepositoryInterface
{
    /**
     * Save status log
     *
     * @param StatusLogInterface $statusLog
     * @return StatusLogInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(StatusLogInterface $statusLog);

    /**
     * Get by id
     *
     * @param int $id
     * @return StatusLogInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($id);

    /**
     * Get by order id
     *
     * @param int $orderId
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByOrderId($orderId);

    /**
     * Get by increment id
     *
     * @param string $incrementId
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByIncrementId($incrementId);
}
