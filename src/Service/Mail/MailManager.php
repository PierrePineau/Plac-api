<?php

namespace App\Service\Mail;

use App\Core\Utils\Messenger;
use App\Model\Mail;
use App\Service\Mail\Gateway\BrevoGateway;
use Twig\Environment;

class MailManager
{
    private $container;
    private $em;
    private $messenger;
    private $twig;

    public const ELEMENT = 'Mail';
    public const ELEMENT_NOT_FOUND = 'Mail.not_found';

    // CODE TEMPLATE
    public const TEMPLATE_USER_ACTIVATION = 'user/activation';
    public const SUBJECT_USER_ACTIVATION = 'mail.subject.user_activation';
    
    public const TEMPLATE_USER_PASSWORD_RESET = 'user/password_reset';
    public const SUBJECT_USER_PASSWORD_RESET = 'mail.subject.user_password_reset';

    public const TEMPLATE_USER_PASSWORD_RESET_SUCCESS = 'user/password_reset_success';
    public const SUBJECT_USER_PASSWORD_RESET_SUCCESS = 'mail.subject.user_password_reset_success';

    public const TEMPLATE_USER_WELCOME = 'user/welcome';
    public const TEMPLATE_USER_WELCOME_SUBJECT = 'mail.subject.user_welcome';
    
    public const TEMPLATE_USER_DELETED = 'user/deleted';
    public const SUBJECT_USER_DELETED = 'mail.subject.user_deleted';

    public const TEMPLATES = [
        'USER_ACTIVATION' => self::TEMPLATE_USER_ACTIVATION,
        'USER_PASSWORD_RESET' => self::TEMPLATE_USER_PASSWORD_RESET,
        'USER_PASSWORD_RESET_SUCCESS' => self::TEMPLATE_USER_PASSWORD_RESET_SUCCESS,
        'USER_WELCOME' => self::TEMPLATE_USER_WELCOME,
        'USER_DELETED' => self::TEMPLATE_USER_DELETED,
    ];

    const GATEWAYS = [
        'BREVO' => BrevoGateway::class,
    ];

    public function __construct($container, $entityManager, Environment $twig)
    {
        $this->container = $container;
        $this->em = $entityManager;
        $this->messenger = $this->container->get(Messenger::class);
        $this->twig = $twig;
    }

    public function send(Mail $mail, array $options = []): mixed
    {
        $gateway = $options['gateway'] ?? 'BREVO';
        if (!in_array($gateway, self::GATEWAYS)) {
            $gateway = 'BREVO'; // Si non définie on utilise le gateway par défaut
        }

        // html
        if (($mail->getHtml() == null || $mail->getHtml() == '') && isset($options['template'])) {
            // On fait un render de du html avec le template et les données
            $mail->setHtml($this->renderTemplate($options['template'], $options['data']));
        }
        
        $gatewayManager = $this->container->get(self::GATEWAYS[$gateway]);
        return $gatewayManager->send($mail, $options);
        // return $this->sendMultiple([$mail], $options);
    }

    // public function sendMultiple(array $data, array $options = []): mixed
    // {
    //     $gateway = $options['gateway'] ?? 'BREVO';

    //     if (!in_array($gateway, self::GATEWAYS)) {
    //         $gateway = 'BREVO'; // Si non définie on utilise le gateway par défaut
    //     }
        
    //     $gatewayManager = $this->container->get(self::GATEWAYS[$gateway]);
    //     return $gatewayManager->send($data);
    // }

    public function renderTemplate(string $path, array $data = []): string
    {
        // On vérifie si path contient le dossier /mail
        if (strpos($path, 'mail/') !== false) {
            $path = str_replace('mail/', '', $path);
        }
        // Si la chaine commence par un / on le retire
        if (substr($path, 0, 1) == '/') {
            $path = substr($path, 1);
        }
        // On vérifie que le path contient bien l'extension .html.twig
        if (strpos($path, '.html.twig') === false) {
            $path .= '.html.twig';
        }
        return $this->twig->render("mail/".$path, $data);
    }
}