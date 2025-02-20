<?php

namespace App\Core\Interface;

use App\Model\Mail;

interface MailGatewayInterface
{
    /**
     * Send one mail.
     *
     * @param array $data
     * @return mixed
     */
    public function send(Mail $mail, array $data): array;
}