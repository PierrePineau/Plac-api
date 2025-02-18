<?php

namespace App\Service\Checkout\Gateway;

use App\Core\Interface\CheckoutInterface;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Symfony\Bundle\SecurityBundle\Security;

class StripeGateway implements CheckoutInterface
{
    public function __construct(
        private $container,
        private $entityManager,
        private Security $security)
    {}

    public function create(array $data): mixed
    {
        $plan = $data['plan'];
        $subscription = $data['subscription'];

        $host = $data['host'];
        
        // On vérifie que host est bien un domaine du style "https://ton-site.com"
        if (!filter_var($host, FILTER_VALIDATE_URL)) {
            throw new \Exception('Invalid host');
        }
        // On check que host ne termine pas par un slash
        if (substr($host, -1) === '/') {
            $host = substr($host, 0, -1);
        }

        $priceId = $plan->getStripeId();
        $successUrl = $host . '/checkout/success?session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl = $host . '/checkout/cancel';

        // Create a new session
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
        $session = Session::create([
            'payment_method_types' => ['card'],
            'mode' => 'subscription',
            'line_items' => [[
                'price' => $priceId, // ID Stripe du prix
                'quantity' => 1,
            ]],
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'metadata' => ['subscription_uuid' => $subscription->getUuid()],
        ]);

        return [
            'url' => $session->url,
        ];
    }

    public function webhook(array $options): mixed
    {
        // Webhook when a payment is made

        return [];
    }

}