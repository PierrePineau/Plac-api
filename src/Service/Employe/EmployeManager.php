<?php

namespace App\Service\Employe;

use App\Entity\Employe;
use App\Core\Service\AbstractCoreService;
use Symfony\Bundle\SecurityBundle\Security;

class EmployeManager extends AbstractCoreService
{
    public function __construct($container, $entityManager, Security $security)
    {
        parent::__construct($container, $entityManager, [
            'identifier' => 'uuid',
            'code' => 'Employe',
            'entity' => Employe::class,
            'security' => $security,
        ]);
    }

    public function _create(array $data)
    {
        $element = new Employe();
        $this->setData(
            $element,
            [
                'firstname' => [
                    'nullable' => false,
                ],
                'lastname' => [
                    'nullable' => false,
                ],
            ],
            $data
        );

        $element->setUsername(uniqid(). '-' . $this->tools->generateCode($element->getFirstname()));

        $this->em->persist($element);
        $this->isValid($element);

        return $element;
    }

    public function _update($id, array $data)
    {
        $element = $this->_get($id);

        $this->setData(
            $element,
            [
                'firstname' => [
                ],
                'lastname' => [
                ],
            ],
            $data
        );

        $this->em->persist($element);
        $this->isValid($element);

        return $element;
    }
}