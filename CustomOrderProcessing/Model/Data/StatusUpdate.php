<?php
namespace Vendor\CustomOrderProcessing\Model\Data;

use Vendor\CustomOrderProcessing\Api\Data\StatusUpdateInterface;
use Magento\Framework\Api\AbstractSimpleObject;

class StatusUpdate extends AbstractSimpleObject implements StatusUpdateInterface
{
    /**
     * @inheritDoc
     */
    public function getIncrementId()
    {
        return $this->_get(self::INCREMENT_ID);
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
    public function getNewStatus()
    {
        return $this->_get(self::NEW_STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setNewStatus($status)
    {
        return $this->setData(self::NEW_STATUS, $status);
    }
}
