<?php
namespace Vendor\CustomOrderProcessing\Model;

use Vendor\CustomOrderProcessing\Api\OrderStatusUpdateInterface;
use Vendor\CustomOrderProcessing\Api\Data\StatusUpdateInterface;
use Vendor\CustomOrderProcessing\Model\RateLimiter;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;

class OrderStatusUpdate implements OrderStatusUpdateInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    
    /**
     * @var RateLimiter
     */
    private $rateLimiter;
    
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RateLimiter $rateLimiter
     * @param RequestInterface $request
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RateLimiter $rateLimiter,
        RequestInterface $request
    ) {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->rateLimiter = $rateLimiter;
        $this->request = $request;
    }

    /**
     * @inheritDoc
     */
    public function updateOrderStatus(StatusUpdateInterface $statusUpdate)
    {
        // Apply rate limiting based on client IP
        $clientIp = $this->request->getClientIp();
        $this->rateLimiter->isAllowed('order_status_update_' . $clientIp);
        
        $incrementId = $statusUpdate->getIncrementId();
        $newStatus = $statusUpdate->getNewStatus();

        if (empty($incrementId) || empty($newStatus)) {
            throw new LocalizedException(__('Increment ID and new status are required.'));
        }

        // Find order by increment ID
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', $incrementId)
            ->create();
        
        $orderList = $this->orderRepository->getList($searchCriteria);
        
        if ($orderList->getTotalCount() === 0) {
            throw new NoSuchEntityException(__('Order with increment ID "%1" does not exist.', $incrementId));
        }
        
        $order = $orderList->getItems()[array_key_first($orderList->getItems())];
        
        // Validate status transition
        $this->validateStatusTransition($order, $newStatus);
        
        // Update order status
        $order->setStatus($newStatus);
        
        // If status is complete, set state to complete as well
        if ($newStatus === Order::STATE_COMPLETE) {
            $order->setState(Order::STATE_COMPLETE);
        } elseif ($newStatus === Order::STATE_PROCESSING) {
            $order->setState(Order::STATE_PROCESSING);
        } elseif ($newStatus === Order::STATE_CLOSED) {
            $order->setState(Order::STATE_CLOSED);
        } elseif ($newStatus === Order::STATE_CANCELED) {
            $order->setState(Order::STATE_CANCELED);
        }
        
        // Save the order
        $this->orderRepository->save($order);
        
        return true;
    }
    
    /**
     * Validate if the status transition is allowed
     *
     * @param Order $order
     * @param string $newStatus
     * @return bool
     * @throws LocalizedException
     */
    private function validateStatusTransition(Order $order, $newStatus)
    {
        $currentStatus = $order->getStatus();
        
        // If status is not changing, no need to validate
        if ($currentStatus === $newStatus) {
            return true;
        }
        
        // Define allowed status transitions
        $allowedTransitions = [
            Order::STATE_NEW => [
                Order::STATE_PROCESSING,
                Order::STATE_CANCELED,
                Order::STATE_HOLDED
            ],
            Order::STATE_PROCESSING => [
                Order::STATE_COMPLETE,
                Order::STATE_CANCELED,
                Order::STATE_HOLDED,
                'shipped'
            ],
            Order::STATE_COMPLETE => [
                Order::STATE_CLOSED
            ],
            Order::STATE_HOLDED => [
                Order::STATE_PROCESSING,
                Order::STATE_CANCELED
            ],
            Order::STATE_CANCELED => [],
            Order::STATE_CLOSED => []
        ];
        
        // Check if transition is allowed
        if (isset($allowedTransitions[$currentStatus]) && 
            !in_array($newStatus, $allowedTransitions[$currentStatus])) {
            throw new LocalizedException(
                __('Status transition from "%1" to "%2" is not allowed.', $currentStatus, $newStatus)
            );
        }
        
        return true;
    }
}
