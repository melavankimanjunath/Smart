<?php
namespace Vendor\CustomOrderProcessing\Api\Data;

interface StatusLogInterface
{
    const LOG_ID = 'log_id';
    const ORDER_ID = 'order_id';
    const INCREMENT_ID = 'increment_id';
    const OLD_STATUS = 'old_status';
    const NEW_STATUS = 'new_status';
    const CREATED_AT = 'created_at';

    /**
     * Get log ID
     *
     * @return int|null
     */
    public function getLogId();

    /**
     * Set log ID
     *
     * @param int $logId
     * @return $this
     */
    public function setLogId($logId);

    /**
     * Get order ID
     *
     * @return int
     */
    public function getOrderId();

    /**
     * Set order ID
     *
     * @param int $orderId
     * @return $this
     */
    public function setOrderId($orderId);

    /**
     * Get increment ID
     *
     * @return string
     */
    public function getIncrementId();

    /**
     * Set increment ID
     *
     * @param string $incrementId
     * @return $this
     */
    public function setIncrementId($incrementId);

    /**
     * Get old status
     *
     * @return string
     */
    public function getOldStatus();

    /**
     * Set old status
     *
     * @param string $status
     * @return $this
     */
    public function setOldStatus($status);

    /**
     * Get new status
     *
     * @return string
     */
    public function getNewStatus();

    /**
     * Set new status
     *
     * @param string $status
     * @return $this
     */
    public function setNewStatus($status);

    /**
     * Get created at
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Set created at
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);
}
