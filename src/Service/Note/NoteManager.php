<?php

namespace App\Service\Note;

use App\Core\Service\AbstractCoreService;
use App\Entity\Note;
use Symfony\Bundle\SecurityBundle\Security;

class NoteManager extends AbstractCoreService
{
    const Note_STANDARD = 'standard';
    const Note_PRO = 'pro';
    const Note_ENTERPRISE = 'enterprise';
    public function __construct($container, $entityManager, Security $security)
    {
        parent::__construct($container, $entityManager, [
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
                    'required' => false,
                    'nullable' => true,
                ],
                'content' => [
                    'required' => false,
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
        if (isset($data['object']) && $data['object'] instanceof Note) {
            $element = $data['object'];
        }else{
            $element = $this->find($id);
        }
        
        if (!$element) {
            throw new \Exception($this->ELEMENT_NOT_FOUND);
        }

        $this->setData(
            $element,
            [
                'name' => [
                    'required' => false,
                    'nullable' => true,
                ],
                'content' => [
                    'required' => false,
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