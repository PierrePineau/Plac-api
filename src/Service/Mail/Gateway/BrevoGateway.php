<?php

namespace App\Service\Mail\Gateway;

use GuzzleHttp\Client;
use SendinBlue\Client\Api\TransactionalEmailsApi;
use SendinBlue\Client\Configuration;
use SendinBlue\Client\Model\SendSmtpEmail;
use App\Core\Interface\MailGatewayInterface;
use App\Core\Utils\Messenger;
use App\Model\Mail;
use Symfony\Bundle\SecurityBundle\Security;

class BrevoGateway implements MailGatewayInterface
{
    private TransactionalEmailsApi $apiInstance;
    public function __construct(
        private $container,
        private $entityManager,
        private Security $security
    ){
        $config = Configuration::getDefaultConfiguration()->setApiKey('api-key', $_ENV['BREVO_API_KEY']);
        $this->apiInstance = new TransactionalEmailsApi(
            new Client(),
            $config
        );
    }

    // Send mail
    public function send(Mail $mail, array $option): array
    {
        $expediteur = $mail->getSender();
        $sender = [
            'name' => $expediteur['nom'] ?? $_ENV['MAIL_SENDER_NAME'],
            'email' => $expediteur['email'] ?? $_ENV['MAIL_SENDER_EMAIL']
        ];
        // Les destinataires
        $emailData = [
            'sender' => $sender,
            'to' => $mail->getDestinataires(),
            'subject' => $mail->getSubject(),
            'htmlContent' => $mail->getHtml()
        ];

        dump($mail->getDestinataires());

        // Les destinataires en copie cachée
        if (!empty($mail->getBcc())) {
            $emailData['bcc'] = $mail->getBcc();
        }

        // Répondre à
        if (!empty($mail->getReplyTo()) && !$mail->getReplyTo()) {
            $emailData['replyTo'] = $mail->getReplyTo();
        }

        $sendSmtpEmail = new SendSmtpEmail($emailData);

        try {
            $result = $this->apiInstance->sendTransacEmail($sendSmtpEmail);
            return ['success' => true, 'message' => 'ok', 'result' => $result];
        } catch (\Exception $e) {
            $messenger = $this->container->get(Messenger::class);
            $messenger->log($e->getMessage());
            return [
                'success' => false,
                'message' => 'Mail.error',
                'data' => [
                    'e' => $e->getMessage()
                ]
            ];
        }
    }
}