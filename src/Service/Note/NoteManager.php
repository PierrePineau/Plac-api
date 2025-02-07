<?php

namespace App\Service\Note;

use App\Entity\Note;
use App\Core\Service\AbstractCoreService;
use Symfony\Bundle\SecurityBundle\Security;

class NoteManager extends AbstractCoreService
{
    public function __construct($container, $entityManager, Security $security)
    {
        parent::__construct($container, $entityManager, [
            'identifier' => 'uuid',
            'code' => 'Note',
            'entity' => Note::class,
            'security' => $security,
        ]);
    }

    public function _create(array $data)
    {
        $element = new Note();
        $this->setData(
            $element,
            [
                'name' => [
                    'nullable' => false,
                ],
                'content' => [
                    'nullable' => true,
                ],
            ],
            $data
        );

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
                    'nullable' => false,
                ],
                'content' => [
                    'nullable' => true,
                ],
            ],
            $data
        );

        $this->em->persist($element);
        $this->isValid($element);

        return $element;
    }
}