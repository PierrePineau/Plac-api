<?php

namespace App\Service\Project;

use App\Entity\Project;
use App\Core\Service\AbstractCoreService;
use Symfony\Bundle\SecurityBundle\Security;

class ProjectManager extends AbstractCoreService
{
    public function __construct($container, $entityManager, Security $security)
    {
        parent::__construct($container, $entityManager, [
            'identifier' => 'uuid',
            'code' => 'Project',
            'entity' => Project::class,
            'security' => $security,
        ]);
    }

    public function _create(array $data)
    {
        $this->debug($data);
        throw new \Exception(json_encode($data['name'] ?? 'NONN'), 1);
        
        $element = new Project();
        $this->setData(
            $element,
            [
                'name' => [
                    'required' => true,
                    'nullable' => false,
                ],
                'reference' => [
                    'nullable' => false,
                ],
                'description' => [
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
                'reference' => [
                    'nullable' => false,
                ],
                'description' => [
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