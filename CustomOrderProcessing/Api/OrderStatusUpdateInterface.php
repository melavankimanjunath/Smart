<?php
namespace Vendor\CustomOrderProcessing\Api;

interface OrderStatusUpdateInterface
{
    /**
     * Update order status
     *
     * @param \Vendor\CustomOrderProcessing\Api\Data\StatusUpdateInterface $statusUpdate
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function updateOrderStatus(\Vendor\CustomOrderProcessing\Api\Data\StatusUpdateInterface $statusUpdate);
}