<?php

namespace App\Service\Status;

use App\Entity\Status;
use App\Core\Service\AbstractCoreService;
use ErrorException;
use Symfony\Bundle\SecurityBundle\Security;

class StatusManager extends AbstractCoreService
{
    public const TYPE_PROJECT = 'PROJECT';
    public const TYPE_TASK = 'TASK';

    public const TYPES = [
        self::TYPE_PROJECT,
        self::TYPE_TASK,
    ];

    // Le status par défaut pour chaque type
    public const ACTION_DEFAULT = 'DEFAULT';
    public const ACTION_FINISHED = 'FINISHED';
    public const ACTION_ARCHIVED = 'ARCHIVED';

    public const POSITIONS = [
        self::ACTION_DEFAULT => -1,
        self::ACTION_FINISHED => 999,
        self::ACTION_ARCHIVED => 1000,
    ];

    public const DEFAULT_STATUS = [
        self::TYPE_PROJECT => [
            [
                'code' => 'A_FAIRE',
                'name' => 'À effectuer', // Bleu
                'color' => '#295BFF',
                'action' => self::ACTION_DEFAULT,
                'position' => self::POSITIONS[self::ACTION_DEFAULT],
            ],
            [
                'code' => 'EN_COURS',
                'name' => 'En cours', // Orange
                'color' => '#F5811A',
                'position' => 0,
            ],
            [
                'code' => 'TERMINE',
                'name' => 'Terminé', // Vert
                'color' => '#22C55E',
                'action' => self::ACTION_FINISHED,
                'position' => self::POSITIONS[self::ACTION_FINISHED],
            ],
            [
                'code' => 'ARCHIVE',
                'name' => 'Archivé', // Gris
                'color' => '#B0B0B0',
                'action' => self::ACTION_ARCHIVED,
                'position' => self::POSITIONS[self::ACTION_ARCHIVED],
            ],
        ],
        self::TYPE_TASK => [
            [
                'code' => 'A_FAIRE',
                'name' => 'À effectuer', // Bleu
                'color' => '#295BFF',
                'action' => self::ACTION_DEFAULT,
                'position' => self::POSITIONS[self::ACTION_DEFAULT],
            ],
            [
                'code' => 'EN_COURS',
                'name' => 'En cours', // Orange
                'color' => '#F5811A',
                'position' => 0,
            ],
            [
                'code' => 'TERMINE',
                'name' => 'Terminé', // Vert
                'color' => '#22C55E',
                'action' => self::ACTION_FINISHED,
                'position' => self::POSITIONS[self::ACTION_FINISHED],
            ],
            [
                'code' => 'ARCHIVE',
                'name' => 'Archivé', // Gris
                'color' => '#B0B0B0',
                'action' => self::ACTION_ARCHIVED,
                'position' => self::POSITIONS[self::ACTION_ARCHIVED],
            ],
        ],
    ];

    public function __construct($container, $entityManager, Security $security)
    {
        parent::__construct($container, $entityManager, [
            'identifier' => 'uuid',
            'code' => 'Status',
            'entity' => Status::class,
        ]);
    }

    public function generateDefault(array $data = [])
    {
        $newStatus = [];
        $type = $data['type'];
        $needFlush = $data['flush'] ?? false;

        if (!in_array($type, self::TYPES)) {
            throw new ErrorException($this->ELEMENT_INVALID. '.type');
        }

        $defaultStatus = self::DEFAULT_STATUS[$type];

        foreach ($defaultStatus as $dataStatus) {
            $newStatus[] = $this->_create($dataStatus);
        }

        if ($needFlush) {
            $this->em->flush();
        }

        return $newStatus;
    }

    public function _create(array $data)
    {
        $element = new Status();

        if (isset($data['type']) && !in_array($data['type'], self::TYPES)) {
            throw new ErrorException($this->ELEMENT_INVALID. '.type');
        }

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
                'action' => [
                    'nullable' => true,
                ],
                'position' => [
                    'nullable' => true,
                    'type' => 'float',
                ],
                'type' => [
                    'nullable' => false,
                ],
            ],
            $data
        );

        // Si une action est définie, on modifie sa position par sa valeur
        if ($element->getAction()) {
            $element->setPosition(self::POSITIONS[$element->getAction()]);
        }

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