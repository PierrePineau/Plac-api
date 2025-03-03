<?php

namespace App\Service\Address;

use App\Entity\Address;
use App\Core\Service\AbstractCoreService;
use Symfony\Bundle\SecurityBundle\Security;

class AddressManager extends AbstractCoreService
{
    public function __construct($container, $entityManager, Security $security)
    {
        parent::__construct($container, $entityManager, [
            'security' => $security,
            'identifier' => 'uuid',
            'code' => 'Address',
            'entity' => Address::class,
        ]);
    }

    private function _saveData(Address $address, array $data): Address
    {
        $data['country'] = $data['country'] ?? 'FR';
        $this->setData(
            $address,
            [
                'country' => [
                    'nullable' => true,
                ],
                'state' => [
                    'nullable' => true,
                ],
                'city' => [
                    'nullable' => true,
                ],
                'postcode' => [
                    'nullable' => true,
                ],
                'street' => [
                    'nullable' => true,
                ],
                'compl' => [
                    'nullable' => true,
                ],
            ],
            $data
        );

        $this->em->persist($address);

        return $address;
    }

    public function _create(array $data)
    {
        $element = new Address();

        $this->_saveData($element, $data);

        $this->em->persist($element);
        $this->isValid($element);

        return $element;
    }

    public function _update($id, array $data)
    {
        if ($id === null) {
            $element = new Address();
        } else {
            $element = $this->_get($id);
        }

        $this->_saveData($element, $data);

        $this->em->persist($element);
        $this->isValid($element);

        return $element;
    }
}