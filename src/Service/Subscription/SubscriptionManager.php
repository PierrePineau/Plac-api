<?php

namespace App\Service\Subscription;

use App\Core\Service\AbstractCoreService;
use App\Entity\Subscription;
use ErrorException;
use Symfony\Bundle\SecurityBundle\Security;

class SubscriptionManager extends AbstractCoreService
{
    public function __construct($container, $entityManager, Security $security)
    {
        parent::__construct($container, $entityManager, [
            'security' => $security,
            'code' => 'Subscription',
            'entity' => Subscription::class,
        ]);
    }

    public function _create(array $data) : Subscription
    {
        $subscription = (isset($data['subscription']) && $data['subscription'] instanceof Subscription )? $data['subscription'] : new Subscription();
        // $plan = $data['plan'] instanceof Plan ? $data['plan'] : $this->em->getRepository(Plan::class)->find($data['plan']);
        $plan = $data['plan'];
        $organisation = $data['organisation'];

        $subscription->setPlan($plan);
        $subscription->setOrganisation($organisation);
        $subscription->setCreatedAt(new \DateTime());
        $subscription->setUpdatedAt(new \DateTime());
        $subscription->setStartAt(new \DateTime());

        $renewalFrequency = $plan->getRenewalFrequency() ?? 'monthly';
        $subscription->setRenewalFrequency($renewalFrequency);
        $subscription->setAutoRenew(true);
        $subscription->setPrice($plan->getPrice());
        $subscription->setActive(false);
        $this->em->persist($subscription);

        /**
         * ABONNEMENT MENSUEL - ANNUEL
         */
        if ($subscription->getRenewalFrequency() === 'monthly') {
            $subscription->setEndAt((new \DateTime())->modify('+1 month'));
        } else {
            $subscription->setEndAt((new \DateTime())->modify('+1 year'));
        }

        $this->em->persist($subscription);
        $this->isValid($subscription);
        
        $organisation->setCurrentSubscription($subscription);
        $this->em->persist($organisation);

        $this->em->flush();

        return $subscription;
    }

    public function _delete($id, array $data = [])
    {
        throw new ErrorException('action.not_allowed', 400);
    }
}