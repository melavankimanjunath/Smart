<?php
namespace Vendor\CustomOrderProcessing\Model;

use Magento\Framework\Model\AbstractModel;
use Vendor\CustomOrderProcessing\Api\Data\StatusLogInterface;
use Vendor\CustomOrderProcessing\Model\ResourceModel\StatusLog as StatusLogResource;

class StatusLog extends AbstractModel implements StatusLogInterface
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(StatusLogResource::class);
    }

    /**
     * @inheritDoc
     */
    public function getLogId()
    {
        return $this->getData(self::LOG_ID);
    }

    /**
     * @inheritDoc
     */
    public function setLogId($logId)
    {
        return $this->setData(self::LOG_ID, $logId);
    }

    /**
     * @inheritDoc
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * @inheritDoc
     */
    public function getIncrementId()
    {
        return $this->getData(self::INCREMENT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setIncrementId($incrementId)
    {
        return $this->setData(self::INCREMENT_ID, $incrementId);
    }

    /**
     * @inheritDoc
     */
    public function getOldStatus()
    {
        return $this->getData(self::OLD_STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setOldStatus($status)
    {
        return $this->setData(self::OLD_STATUS, $status);
    }

    /**
     * @inheritDoc
     */
    public function getNewStatus()
    {
        return $this->getData(self::NEW_STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setNewStatus($status)
    {
        return $this->setData(self::NEW_STATUS, $status);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }
}
