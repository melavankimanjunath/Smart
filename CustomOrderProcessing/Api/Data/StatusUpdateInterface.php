<?php
namespace Vendor\CustomOrderProcessing\Api\Data;

interface StatusUpdateInterface
{
    const INCREMENT_ID = 'increment_id';
    const NEW_STATUS = 'new_status';

    /**
     * Get order increment ID
     *
     * @return string
     */
    public function getIncrementId();

    /**
     * Set order increment ID
     *
     * @param string $incrementId
     * @return $this
     */
    public function setIncrementId($incrementId);

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
}
