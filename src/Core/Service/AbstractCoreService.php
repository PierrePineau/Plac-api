<?php

namespace App\Core\Service;

use App\Core\Utils\Messenger;
use App\Core\Utils\Tools;
use App\Core\Utils\Pagination;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractCoreService
{
    public $container;
    public $em;
    public $entityClass;
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
        $this->guardActions[$key] = $actions;
    }

    /**
     * UTILS - METHODS
     */
    public function deniedException(string $message = 'access.denied', int $code = 403)
    {
        throw new \Exception($message, $code);
    }
    
    public function errorException(string $message, int $code = 400)
    {
        throw new \Exception($message, $code);
    }

    public function notFoundException(string $message = 'not_found', int $code = 404)
    {
        throw new \Exception($message, $code);
    }
    
    public function getUser(): User
    {
        if (!$this->user || $this->user instanceof User) {
            $this->user = $this->security->getUser();
        }
        return $this->user;        
    }

    public function generateDefault()
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
            $this->errorException($errorsString);
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
            $this->notFoundException('entity.not_found');
        }

        foreach ($index as $key => $option) {
            if (isset($data[$key])) {
                $setter = 'set'.ucfirst($key);

                if (!method_exists($entity, $setter)) {
                    // throw new \Exception($this->ELEMENT.'.'.$key.'.undefined');
                    $this->errorException($this->ELEMENT.'.'.$key.'.undefined');
                }

                if (!(isset($option['nullable']) && $option['nullable'] === true) && ($data[$key] == null || $data[$key] == '')) {
                    // throw new \Exception($this->ELEMENT.'.'.$key.'.invalid');
                    $this->errorException($this->ELEMENT.'.'.$key.'.invalid');
                }

                // On vérifie la valeur
                $type = $option['type'] ?? 'string';
                if (!in_array($type, ['string', 'int', 'int+', 'integer', 'float', 'float+', 'bool', 'boolean'])) {
                    // throw new \Exception($this->ELEMENT.'.'.$key.'.type.invalid');
                    $this->errorException($this->ELEMENT.'.'.$key.'.type.invalid');
                }

                if (in_array($type, ['string'])) {
                    // On vérifie si c'est une chaine de caractère
                    if (!is_string($data[$key])) {
                        // throw new \Exception($this->ELEMENT.'.'.$key.'.invalid');
                        $this->errorException($this->ELEMENT.'.'.$key.'.invalid');
                    }
                    // $data[$key] = (string) $data[$key];
                }elseif (in_array($type, ['int', 'integer', 'float', 'int+', 'float+'])) {
                    // On vérifie si c'est un nombre
                    if (!is_numeric($data[$key])) {
                        // throw new \Exception($this->ELEMENT.'.'.$key.'.invalid');
                        $this->errorException($this->ELEMENT.'.'.$key.'.invalid');
                    }

                    // Si int+ et float+ on vérifie si c'est un entier positif
                    if (in_array($type, ['int+', 'float+']) && !($data[$key] > 0)) {
                        // throw new \Exception($this->ELEMENT.'.'.$key.'.invalid');
                        $this->errorException($this->ELEMENT.'.'.$key.'.invalid');
                    }
                }elseif (in_array($type, ['bool', 'boolean'])) {
                    // On vérifie si c'est un booléen
                    if (!in_array($data[$key], ['0', 0, 'false', false, '1', 1, 'true', true, 'on'])) {
                        // throw new \Exception($this->ELEMENT.'.'.$key.'.invalid');
                        $this->errorException($this->ELEMENT.'.'.$key.'.invalid');
                    }else{
                        $data[$key] = ($data[$key] === true || in_array($data[$key], ['1', 1, 'true', 'on'])) ? true : false;
                    }
                }

                $entity->$setter($data[$key]);
                
            }else if (isset($option['required']) && $option['required'] === true) {
                // Si la valeur est obligatoire et qu'elle n'est pas présente
                // throw new \Exception($this->ELEMENT.'.'.$key.'.required');
                $this->errorException($this->ELEMENT.'.'.$key.'.required');
            }
        }
        return $entity;
    }

    public function dispatchEvent(Event $event, ?string $eventName = null): ?Event
    {
        return $this->messenger->dispatchEvent($event, $eventName);
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
    public function find($id, bool $throwException = true)
    {
        if ($this->identifier == 'id' && is_numeric($id)) {
            $element = $this->em->getRepository($this->entityClass)->find($id);
        }else{
            $element = $this->em->getRepository($this->entityClass)->findOneBy([$this->identifier => $id]);
        }

        if (!$element && $throwException) {
            // throw new \Exception($this->ELEMENT_NOT_FOUND, 404);
            $this->notFoundException($this->ELEMENT_NOT_FOUND);
        }

        return $element;
    }
    
    public function findByIds(array $ids)
    {
        if ($this->identifier == 'id') {
            return $this->em->getRepository($this->entityClass)->findBy(['id' => $ids]);
        }else {
            return $this->em->getRepository($this->entityClass)->findBy([$this->identifier => $ids]);
        }
    }

    public function findOneBy(array $filters = [])
    {
        return $this->em->getRepository($this->entityClass)->findOneBy($filters);
    }

    public function findBy(array $filters = [])
    {
        return $this->em->getRepository($this->entityClass)->findBy($filters);
    }

    public function findOneByAccess(array $data, bool $throwException = true)
    {
        $element = $this->em->getRepository($this->entityClass)->findOneByAccess($data);
        if (!$element && $throwException) {
            // throw new \Exception($this->ELEMENT_NOT_FOUND, 404);
            $this->notFoundException($this->ELEMENT_NOT_FOUND);
        }
        return $element;
    }

    public function findByAccess(array $data)
    {
        return $this->em->getRepository($this->entityClass)->findByAccess($data);
    }

    /**
     * CRUD - METHODS
     */

    public function _search(array $filters = []): array
    {
        $count = $this->em->getRepository($this->entityClass)->search($filters, true);
        $results = [];
        $resultsArray = [];
        if ($count) {
            $results = $this->em->getRepository($this->entityClass)->search($filters);
            foreach ($results as $element) {
                // On véririe si $product est un objet ou array
                if (is_object($element)) {
                    $resultsArray[] = $element->toArray();
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

            // $this->middleware([
            //     $this->ELEMENT => $element,
            // ]);
            
            return $this->messenger->newResponse(
                [
                    'success' => true,
                    'message' => $this->ELEMENT_FOUND,
                    'code' => 200,
                    'data' => $element->toArray()
                ]
            );
        } catch (\Throwable $th) {
            return $this->messenger->errorResponse($th);
        }
    }

    public function _get($id, array $filters = []): mixed
    {
        $element = $id instanceof $this->entityClass ? $id : $this->find($id);
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
                'data' => $element->toArray()
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
                'data' => $element->toArray()
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
                'data' => $element->toArray()
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
                'data' => $element->toArray()
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
        $element->setDeleted(true);

        $this->em->persist($element);
        $this->isValid($element);

        return $element;
    }
}
