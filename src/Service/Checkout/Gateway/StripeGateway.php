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

        $successUrl = $data['success_url'];
        $cancelUrl = $data['cancel_url'];
        // $host = $data['host'];
        
        // On vérifie que les urls est bien un domaine du style "https://ton-site.com"
        if (!filter_var($successUrl, FILTER_VALIDATE_URL)) {
            throw new \Exception('Invalid success url');
        }
        if (!filter_var($cancelUrl, FILTER_VALIDATE_URL)) {
            throw new \Exception('Invalid cancel url');
        }
        // On check que mes urls ne termine pas par un slash
        if (substr($successUrl, -1) === '/') {
            $successUrl = substr($successUrl, 0, -1);
        }
        if (substr($cancelUrl, -1) === '/') {
            $cancelUrl = substr($cancelUrl, 0, -1);
        }

        $priceId = $plan->getStripeId();
        $successUrl = $successUrl . '?session_id={CHECKOUT_SESSION_ID}';
        // $cancelUrl = $cancelUrl . '/checkout/cancel';

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