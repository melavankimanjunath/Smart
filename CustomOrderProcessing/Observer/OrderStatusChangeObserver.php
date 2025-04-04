<?php
namespace Vendor\CustomOrderProcessing\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Vendor\CustomOrderProcessing\Api\StatusLogRepositoryInterface;
use Vendor\CustomOrderProcessing\Model\StatusLogFactory;
use Magento\Sales\Model\Order;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Psr\Log\LoggerInterface;

class OrderStatusChangeObserver implements ObserverInterface
{
    /**
     * @var StatusLogRepositoryInterface
     */
    private $statusLogRepository;

    /**
     * @var StatusLogFactory
     */
    private $statusLogFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var StateInterface
     */
    private $inlineTranslation;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param StatusLogRepositoryInterface $statusLogRepository
     * @param StatusLogFactory $statusLogFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param LoggerInterface $logger
     */
    public function __construct(
        StatusLogRepositoryInterface $statusLogRepository,
        StatusLogFactory $statusLogFactory,
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        LoggerInterface $logger
    ) {
        $this->statusLogRepository = $statusLogRepository;
        $this->statusLogFactory = $statusLogFactory;
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->logger = $logger;
    }

    /**
     * Execute observer
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();
        
        // Check if the status has changed
        $oldStatus = $order->getOrigData('status');
        $newStatus = $order->getStatus();
        
        if ($oldStatus !== $newStatus) {
            // Log the status change
            $this->logStatusChange($order, $oldStatus, $newStatus);
            
            // If the order is marked as shipped, send notification
            if ($newStatus === 'shipped' || $newStatus === 'complete') {
                $this->sendShippedNotification($order);
            }
        }
    }

    /**
     * Log status change to custom table
     *
     * @param Order $order
     * @param string $oldStatus
     * @param string $newStatus
     * @return void
     */
    private function logStatusChange(Order $order, $oldStatus, $newStatus)
    {
        try {
            $statusLog = $this->statusLogFactory->create();
            $statusLog->setOrderId($order->getId());
            $statusLog->setIncrementId($order->getIncrementId());
            $statusLog->setOldStatus($oldStatus);
            $statusLog->setNewStatus($newStatus);
            
            $this->statusLogRepository->save($statusLog);
        } catch (\Exception $e) {
            $this->logger->error('Error logging order status change: ' . $e->getMessage());
        }
    }

    /**
     * Send shipped notification to customer
     *
     * @param Order $order
     * @return void
     */
    private function sendShippedNotification(Order $order)
    {
        try {
            $storeId = $order->getStoreId();
            $customerEmail = $order->getCustomerEmail();
            $customerName = $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname();
            
            $this->inlineTranslation->suspend();
            
            $transport = $this->transportBuilder
                ->setTemplateIdentifier('custom_order_shipped_email_template')
                ->setTemplateOptions([
                    'area' => 'frontend',
                    'store' => $storeId
                ])
                ->setTemplateVars([
                    'order' => $order,
                    'customer_name' => $customerName,
                    'increment_id' => $order->getIncrementId()
                ])
                ->setFrom([
                    'email' => $this->scopeConfig->getValue('trans_email/ident_general/email', ScopeInterface::SCOPE_STORE),
                    'name' => $this->scopeConfig->getValue('trans_email/ident_general/name', ScopeInterface::SCOPE_STORE)
                ])
                ->addTo($customerEmail, $customerName)
                ->getTransport();
            
            $transport->sendMessage();
            
            $this->inlineTranslation->resume();
            
            $this->logger->info('Shipped notification sent to customer for order #' . $order->getIncrementId());
        } catch (\Exception $e) {
            $this->logger->error('Error sending shipped notification: ' . $e->getMessage());
        }
    }
}
