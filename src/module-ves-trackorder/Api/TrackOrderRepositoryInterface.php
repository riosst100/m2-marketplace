<?php
/**
 * Copyright © landofcoder.com All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ves\Trackorder\Api;

interface TrackOrderRepositoryInterface
{

    /**
     * Track Order Info
     * @param string $order_id
     * @param string $email_address
     * @param string $code
     * @return \Magento\Sales\Api\Data\OrderInterface Order interface.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function track(
        string $order_id = "",
        string $email_address = "",
        $code = ""
    );
    /**
     * Track Order Info for Logged In Customer
     * @param int $customerId
     * @param string $order_id
     * @param string $code
     * @return \Magento\Sales\Api\Data\OrderInterface Order interface.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function trackMyOrder(
        int $customerId,
        string $order_id = "",
        $code = ""
    );
    /**
     * Send Order Info to email address
     * @param string $order_id
     * @param string $email_address
     * @param string $invoiceId
     * @param string $email_recipient
     * @param string $name
     * @param string $code
     * @return string|boolean $msg
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function send(
        string $invoiceId,
        string $email_recipient,
        string $name = "",
        string $order_id = "",
        string $email_address = "",
        $code = ""
    );
}

