<?php

namespace App\Core\Service;

use App\Core\Utils\Messenger;
use App\Core\Utils\Tools;
use App\Core\Utils\Pagination;
use App\Model\AuthenticateUser;
use ErrorException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractCoreService
{
    public $container;
    public $em;
    public $repo;
    public $entityClass;
    public $elementManagerClass;
    public $identifier;
    public $security;
    public $user;

    public $tools;
    public $messenger;
    public $guardActions;

    // SINGLE ELEMENT
    public const FOUND = 'found';
    public const NOT_FOUND = 'not_found';
    public const CREATED = 'created';
    public const UPDATED = 'updated';
    public const DELETED = 'deleted';
    public const ADDED = 'added';
    public const REMOVED = 'removed';
    public const NOT_ALLOWED = 'not_allowed';
    public const INVALID = 'invalid';
    public const ALREADY_EXISTS = 'already_exists';

    public string $ELEMENT;
    public string $ELEMENT_NOT_FOUND;
    public string $ELEMENT_FOUND;
    public string $ELEMENT_CREATED;
    public string $ELEMENT_UPDATED;
    public string $ELEMENT_DELETED;
    public string $ELEMENT_ADDED;
    public string $ELEMENT_REMOVED;
    public string $ELEMENT_NOT_ALLOWED;
    public string $ELEMENT_INVALID;
    public string $ELEMENT_ALREADY_EXISTS;

    public function __construct($container, $entityManager, array $data = [])
    {
        $this->container = $container;
        $this->em = $entityManager;
        $this->entityClass = $data['entity'];
        $this->repo = $this->em->getRepository($this->entityClass);
        $this->ELEMENT = strtolower($data['code']);
        $this->identifier = $data['identifier'] ?? 'id';
        $this->guardActions = $data['guardActions'] ?? [];

        // UTILS
        $this->tools = $this->container->get(Tools::class);
        $this->messenger = $this->container->get(Messenger::class);

        // Services
        if (isset($data['security']) && $data['security'] instanceof Security) {
            $this->security = $data['security'];
        }

        // Element Manager associated (For OrganisationProject, OrganisationEmploye, OrganisationNote, OrganisationStatus)
        if (isset($data['elementManagerClass']) && $data['elementManagerClass']) {
            $this->elementManagerClass = $data['elementManagerClass'];
        }

        // Code Messages
        $this->ELEMENT_NOT_FOUND = $this->ELEMENT.'.'. $this::NOT_FOUND;
        $this->ELEMENT_NOT_ALLOWED = $this->ELEMENT.'.'. $this::NOT_ALLOWED;
        $this->ELEMENT_FOUND = $this->ELEMENT.'.'. $this::FOUND;
        $this->ELEMENT_CREATED = $this->ELEMENT.'.'. $this::CREATED;
        $this->ELEMENT_UPDATED = $this->ELEMENT.'.'. $this::UPDATED;
        $this->ELEMENT_DELETED = $this->ELEMENT.'.'. $this::DELETED;
        $this->ELEMENT_ADDED = $this->ELEMENT.'.'. $this::ADDED;
        $this->ELEMENT_REMOVED = $this->ELEMENT.'.'. $this::REMOVED;
        $this->ELEMENT_INVALID = $this->ELEMENT.'.'. $this::INVALID;
        $this->ELEMENT_ALREADY_EXISTS = $this->ELEMENT.'.'. $this::ALREADY_EXISTS;
    }

    public function setGuardActions(string $key, string $actions)
    {
        $this->guardActions[] = $actions;
    }

    /**
     * UTILS - METHODS
     */
    public function getUser(): AuthenticateUser
    {
        if (!$this->user || !$this->user instanceof AuthenticateUser) {
            $this->user = new AuthenticateUser($this->security->getUser());
        }
        return $this->user;        
    }

    public function generateDefault(array $data = [])
    {
        // Add here code for default generation
    }
    public function generateUuid(): string
    {
        return $this->tools->generateUuid();
    }

    public function generateCode($name)
    {
        $code = $this->tools->generateCode($name);
        if ($this->findOneBy(['code' => $code])) {
            return $this->generateCode( $name . '-1');
        }else{
            return $code;
        }
    }

    public function debug(mixed $message, array $context = [''])
    {
        if (is_array($message)) {
            $message = json_encode($message);
        }
        $this->messenger->debug($message, $context);
    }

    // Cette fonction permet de valider les données d'un élément avant de le créer ou de le modifier
    public function isValid($element): ?bool
    {
        $errors = $this->tools->validate($element);
        if (count($errors) > 0) {
            $errorsString = '';
            foreach ($errors as $error) {
                $errorsString .= $error->getMessage()."||";
            }
            // throw new \Exception($errorsString);
            throw new ErrorException($errorsString, 400);
            return false;
        }else{
            return true;
        }
    }

    // Permets de modifier les données d'une entité en fonction des index qu'on lui passe
    public function setData($entity,array $index, array $data = [])
    {
        // On vérifie que l'entité est bien un objet
        if (!is_object($entity)) {
            // throw new \Exception('entity.not_found');
            throw new NotFoundHttpException('entity.not_found');
        }

        foreach ($index as $key => $option) {
            if (isset($data[$key])) {
                $setter = 'set'.ucfirst($key);

                if (!method_exists($entity, $setter)) {
                    throw new ErrorException('entity.'.$key.'.undefined');
                }

                if (!(isset($option['nullable']) && $option['nullable'] === true) && ($data[$key] == null || $data[$key] == '')) {
                    // throw new \Exception('entity.'.$key.'.invalid');
                    throw new ErrorException('entity.'.$key.'.invalid', 400);
                }

                // On vérifie la valeur
                $type = $option['type'] ?? 'string';
                if (!in_array($type, ['string', 'int', 'int+', 'integer', 'float', 'float+', 'bool', 'boolean'])) {
                    // throw new \Exception('entity.'.$key.'.type.invalid');
                    throw new ErrorException('entity.'.$key.'.type.invalid', 400);
                }

                if (in_array($type, ['string'])) {
                    // On vérifie si c'est une chaine de caractère
                    if (!is_string($data[$key])) {
                        // throw new \Exception('entity.'.$key.'.invalid');
                        throw new ErrorException('entity.'.$key.'.invalid', 400);
                    }
                    // $data[$key] = (string) $data[$key];
                }elseif (in_array($type, ['int', 'integer', 'float', 'int+', 'float+'])) {
                    // On vérifie si c'est un nombre
                    if (!is_numeric($data[$key])) {
                        // throw new \Exception('entity.'.$key.'.invalid');
                        throw new ErrorException('entity.'.$key.'.invalid', 400);
                    }

                    // Si int+ et float+ on vérifie si c'est un entier positif
                    if (in_array($type, ['int+', 'float+']) && !($data[$key] > 0)) {
                        // throw new \Exception('entity.'.$key.'.invalid');
                        throw new ErrorException('entity.'.$key.'.invalid', 400);
                    }
                }elseif (in_array($type, ['bool', 'boolean'])) {
                    // On vérifie si c'est un booléen
                    if (!in_array($data[$key], ['0', 0, 'false', false, '1', 1, 'true', true, 'on'])) {
                        // throw new \Exception('entity.'.$key.'.invalid');
                        throw new ErrorException('entity.'.$key.'.invalid', 400);
                    }else{
                        $data[$key] = ($data[$key] === true || in_array($data[$key], ['1', 1, 'true', 'on'])) ? true : false;
                    }
                }

                $entity->$setter($data[$key]);
                
            }else if (isset($option['required']) && $option['required'] === true) {
                // Si la valeur est obligatoire et qu'elle n'est pas présente
                // throw new \Exception('entity.'.$key.'.required');
                throw new ErrorException('entity.'.$key.'.required', 400);
            }
        }
        return $entity;
    }

    public function dispatchEvent(Event $event, ?string $eventName = null): ?Event
    {
        return $this->messenger->dispatchEvent($event, $eventName);
    }

    public function getElementManager()
    {
        if (!$this->elementManagerClass) {
            throw new NotFoundHttpException('element.manager.not_found');
        }
        return $this->container->get($this->elementManagerClass);
    }

    // Pour gérer un project il faut que soit défini une organisation
    // Le middleware permet de vérifier si l'organisation est bien défini et si l'utilisateur a les droits
    public function guardMiddleware(array $data): array
    {
        foreach ($this->guardActions as $key => $actions) {
            $data[$key] = $this->$actions($data);
        }

        return $data;
    }

    // Cette fonction permet de vérifier les authentifications et autorisations
    public function middleware(array $data): mixed
    {
        // Add here code for default check
        return $data;
    }

    /**
     * REPOSITORY - METHODS
     */
    public function findAll()
    {
        return $this->repo->findAll();
    }
    public function find($id, bool $throwException = false)
    {
        if ($this->identifier == 'id' && is_numeric($id)) {
            $element = $this->repo->find($id);
        }else{
            $element = $this->repo->findOneBy([$this->identifier => $id]);
        }

        if (!$element && $throwException) {
            // throw new \Exception($this->ELEMENT_NOT_FOUND, 404);
            throw new NotFoundHttpException($this->ELEMENT_NOT_FOUND);
        }

        if (method_exists($element, 'isDeleted')) {
            if ($element->isDeleted() && $throwException) {
                // Si l'utilisateur est un admin, on peut récupérer l'élément
                if ($this->security->isGranted('ROLE_ADMIN')) {
                    # code...
                }else{
                    // throw new \Exception($this->ELEMENT_NOT_FOUND, 404);
                    throw new NotFoundHttpException($this->ELEMENT_NOT_FOUND);
                }
            }
        }

        return $element ?? null;
    }
    
    public function findByIds(array $ids)
    {
        if ($this->identifier == 'id') {
            return $this->repo->findBy(['id' => $ids]);
        }else {
            return $this->repo->findBy([$this->identifier => $ids]);
        }
    }

    public function findOneBy(array $filters = [])
    {
        return $this->repo->findOneBy($filters);
    }

    public function findBy(array $filters = [])
    {
        return $this->repo->findBy($filters);
    }

    public function findOneByAccess(array $data, bool $throwException = true)
    {
        $element = $this->repo->findOneByAccess($data);
        if (!$element && $throwException) {
            // throw new \Exception($this->ELEMENT_NOT_FOUND, 404);
            throw new NotFoundHttpException($this->ELEMENT_NOT_FOUND);
        }
        return $element;
    }

    public function findByAccess(array $data)
    {
        return $this->repo->findByAccess($data);
    }

    /**
     * CRUD - METHODS
     */

    public function _search(array $filters = []): array
    {
        $count = $this->repo->search($filters, true);
        $results = [];
        $resultsArray = [];
        $userAuth = $this->getUser();
        $filters['authenticateUser'] = $this->getUser();
        $filters['isSuperAdmin'] = $userAuth->isSuperAdmin();
        if ($count) {
            $results = $this->repo->search($filters);
            foreach ($results as $element) {
                // On véririe si $product est un objet ou array
                if (is_object($element)) {
                    $resultsArray[] = $element->toArray('search');
                } else {
                    $resultsArray[] = $element;
                }
            }
            $results = $resultsArray;
        }
        $pagination = new Pagination($count, $results, $filters['page'] ?? 1, $filters['limit'] ?? 10);
        return $pagination->getData();
    }

    public function search(array $filters = []): ?array
    {
        try {
            $filters = $this->guardMiddleware($filters);
            $search = $this->_search($filters);

            return $this->messenger->newResponse(
                [
                    'success' => true,
                    'message' => $search['total'] > 0 ? $this->ELEMENT_FOUND : $this->ELEMENT_NOT_FOUND,
                    'code' => 200,
                    'data' => $search
                ]
            );
        } catch (\Throwable $th) {
            return $this->messenger->errorResponse($th);
        }
    }

    public function get($id, array $filters = []): ?array
    {
        try {
            $filters = $this->guardMiddleware($filters);
            $element = $this->_get($id, $filters);

            $this->middleware([
                $this->ELEMENT => $element,
            ]);
            
            return $this->messenger->newResponse(
                [
                    'success' => true,
                    'message' => $this->ELEMENT_FOUND,
                    'code' => 200,
                    'data' => $element->toArray('get')
                ]
            );
        } catch (\Throwable $th) {
            return $this->messenger->errorResponse($th);
        }
    }

    public function _get($id, array $filters = []): mixed
    {
        $element = $id instanceof $this->entityClass ? $id : $this->find($id, true);
        // $this->middleware([
        //     $this->ELEMENT => $element,
        // ]);
        return $element;
    }

    public function update($id, array $data): ?array
    {
        try {
            $data = $this->guardMiddleware($data);
            $element = $this->_update($id, $data);

            $this->em->flush();

            return $this->messenger->newResponse([
                'success' => true,
                'message' => $this->ELEMENT_UPDATED,
                'code' => 200,
                'data' => $element->toArray('update')
            ]);
        } catch (\Throwable $th) {
            return $this->messenger->errorResponse($th);
        }
    }

    public function create(array $data): ?array
    {
        try {
            $data = $this->guardMiddleware($data);
            $element = $this->_create($data);

            $this->em->flush();

            return $this->messenger->newResponse([
                'success' => true,
                'message' => $this->ELEMENT_CREATED,
                'code' => 201,
                'data' => $element->toArray('create')
            ]);
        } catch (\Throwable $th) {
            return $this->messenger->errorResponse($th);
        }
    }

    public function add(array $data): ?array
    {
        try {
            $data = $this->guardMiddleware($data);
            $element = $this->_add($data);

            $this->em->flush();

            return $this->messenger->newResponse([
                'success' => true,
                'message' => $this->ELEMENT_ADDED,
                'code' => 201,
                'data' => $element->toArray('add')
            ]);
        } catch (\Throwable $th) {
            return $this->messenger->errorResponse($th);
        }
    }

    public function remove(array $data): ?array
    {
        try {
            $data = $this->guardMiddleware($data);
            $element = $this->_remove($data);

            $this->em->flush();

            return $this->messenger->newResponse([
                'success' => true,
                'message' => $this->ELEMENT_REMOVED,
                'code' => 200,
                'data' => $element->toArray('remove')
            ]);
        } catch (\Throwable $th) {
            return $this->messenger->errorResponse($th);
        }
    }

    public function delete($id, array $data = []): ?array
    {
        try {
            $data = $this->guardMiddleware($data);
            $element = $this->_delete($id, $data);

            $this->em->flush();

            return $this->messenger->newResponse([
                'success' => true,
                'message' => $this->ELEMENT_DELETED,
                'code' => 200,
            ]);
        } catch (\Throwable $th) {
            return $this->messenger->errorResponse($th);
        }
    }
    
    /**
     * By Default, UPDATE / CREATE / ADD / REMOVE / DELETE are set to "not allowed"
     * You can override these methods in your service for specific needs
     */
    public function _update($id, array $data)
    {
        throw new \Exception($this->ELEMENT.'.update.not_allowed');
    }

    public function _create(array $data)
    {
        throw new \Exception($this->ELEMENT.'.create.not_allowed');
    }

    public function _add(array $data)
    {
        throw new \Exception($this->ELEMENT.'.add.not_allowed');
    }

    public function _remove(array $data)
    {
        throw new \Exception($this->ELEMENT.'.remove.not_allowed');
    }

    public function _delete($id, array $data = []) 
    {
        $element = $this->_get($id);
        if (method_exists($element, 'setDeleted')) {
            $element->setDeleted(true);
        }
        if (method_exists($element, 'setDeletedAt')) {
            $element->setDeletedAt(new \DateTime());
        }

        $this->em->persist($element);
        $this->isValid($element);

        return $element;
    }
}
