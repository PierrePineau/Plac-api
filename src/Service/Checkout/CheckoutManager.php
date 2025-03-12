<?php

namespace App\Service\Checkout;

use App\Core\Service\AbstractCoreService;
use App\Entity\Plan;
use App\Entity\Subscription;
use App\Service\Checkout\Gateway\StripeGateway;
use App\Service\Organisation\OrganisationManager;
use App\Service\Subscription\SubscriptionManager;
use Symfony\Bundle\SecurityBundle\Security;

class CheckoutManager extends AbstractCoreService
{
    const GATEWAYS = [
        'STRIPE' => StripeGateway::class,
        // 'paypal' => 'App\Service\Checkout\Providers\PaypalGateway',
    ];
    public function __construct($container, $entityManager, Security $security)
    {
        parent::__construct($container, $entityManager, [
            'security' => $security,
            'code' => 'subscription',
            'entity' => Subscription::class,
        ]);
    }

    public function create(array $data): ?array
    {
        try {
            $return = $this->_create($data);

            $this->em->flush();

            return $this->messenger->newResponse([
                'success' => true,
                'message' => $this->ELEMENT_CREATED,
                'code' => 201,
                'data' => $return
            ]);
        } catch (\Throwable $th) {
            return $this->messenger->errorResponse($th);
        }
    }

    public function webhook(array $data): ?array
    {
        try {
            $return = $this->_webhook($data);

            return $this->messenger->newResponse([
                'success' => true,
                'message' => $this->ELEMENT_UPDATED,
                'code' => 200,
                // 'data' => $return
            ]);
        } catch (\Throwable $th) {
            return $this->messenger->errorResponse($th);
        }
    }

    // On créer une nouvelle session
    public function _create(array $data)
    {
        $idPlan = $data['idPlan'];
        $idOrganisation = $data['idOrganisation'];

        throw new \Exception('Webhook stripe not configured');

        // On récupère l'organisation via l'id (uuid)
        $organisationManager = $this->container->get(OrganisationManager::class);
        $organisation = $organisationManager->findOneBy(['uuid' => $idOrganisation]);
        if (!$organisation) {
            throw new \Exception($organisationManager->ELEMENT_NOT_FOUND);
        }

        // On récupère le plan via l'id
        $plan = $this->em->getRepository(Plan::class)->find($idPlan);
        if (!$plan) {
            throw new \Exception('Plan not found');
        }

        // On check si l'organisation a déjà un abonnement
        $subscription = $organisation->getCurrentSubscription();
        if ($subscription && $subscription->isActive()) {
            throw new \Exception("L'organisation a déjà un abonnement en cours");
            // TODO : Gérer le cas où l'organisation a déjà un abonnement en cours -> upgrade/downgrade
        }

        $dataSubscription = [
            'plan' => $plan,
            'organisation' => $organisation
        ];

        if ($subscription) {
            $dataSubscription['subscription'] = $subscription;
        }
        
        $subscriptionManager = $this->container->get(SubscriptionManager::class);
        $subscription = $subscriptionManager->_create($dataSubscription);

        $gateway = $this->container->get(self::GATEWAYS['STRIPE']);
        return $gateway->create([
            'plan' => $plan,
            'subscription' => $subscription,
            'success_url' => $data['success_url'],
            'cancel_url' => $data['cancel_url'],
        ]);
    }

    // Webhook lorsqu'un paiement est effectué
    public function _webhook(array $data)
    {
        $gateway = $this->container->get(self::GATEWAYS['STRIPE']);
        return $gateway->webhook($data);
    }
}