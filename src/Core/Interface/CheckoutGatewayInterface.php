<?php

namespace App\Core\Interface;

interface CheckoutGatewayInterface
{
    /**
     * Create a new session.
     *
     * @param array $data
     * @return mixed
     */
    public function create(array $data): mixed;

    /**
     * Webhook when a payment is made.
     *
     * @param array $data
     * @return mixed
     */
    public function webhook(array $data): mixed;
}