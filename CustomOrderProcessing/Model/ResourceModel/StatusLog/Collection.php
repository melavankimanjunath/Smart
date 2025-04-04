<?php
namespace Vendor\CustomOrderProcessing\Model\ResourceModel\StatusLog;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Vendor\CustomOrderProcessing\Model\StatusLog;
use Vendor\CustomOrderProcessing\Model\ResourceModel\StatusLog as StatusLogResource;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'log_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(StatusLog::class, StatusLogResource::class);
    }
}
