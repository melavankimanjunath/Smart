<?php
namespace Vendor\CustomOrderProcessing\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class StatusLog extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('vendor_order_status_log', 'log_id');
    }
}
