<?php

namespace App\Service\Status;

use App\Entity\Status;
use App\Core\Service\AbstractCoreService;
use Symfony\Bundle\SecurityBundle\Security;

class StatusManager extends AbstractCoreService
{
    public const TYPE_PROJECT = 'Project';
    public const TYPE_TASK = 'Task';

    public const TYPES = [
        self::TYPE_PROJECT,
        self::TYPE_TASK,
    ];

    public function __construct($container, $entityManager, Security $security)
    {
        parent::__construct($container, $entityManager, [
            'identifier' => 'uuid',
            'code' => 'Status',
            'entity' => Status::class,
        ]);
    }

    public function _create(array $data)
    {
        $element = new Status();
        $this->setData(
            $element,
            [
                'code' => [
                    'nullable' => false,
                ],
                'name' => [
                ],
                'color' => [
                ],
            ],
            $data
        );

        if (!isset($data['type']) || !in_array($data['type'], self::TYPES)) {
            $this->errorException($this->ELEMENT_INVALID. '.type');
        }

        $element->setType($data['type']);

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
                'name' => [
                ],
                'color' => [
                ],
            ],
            $data
        );

        $this->em->persist($element);
        $this->isValid($element);

        return $element;
    }
}