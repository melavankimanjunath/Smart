<?php
namespace Vendor\CustomOrderProcessing\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Sales\Model\Order\Config;

class OrderStatus implements OptionSourceInterface
{
    /**
     * @var Config
     */
    protected $orderConfig;

    /**
     * @param Config $orderConfig
     */
    public function __construct(Config $orderConfig)
    {
        $this->orderConfig = $orderConfig;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $statuses = $this->orderConfig->getStatuses();
        $options = [];
        
        foreach ($statuses as $code => $label) {
            $options[] = ['value' => $code, 'label' => $label];
        }
        
        return $options;
    }
}