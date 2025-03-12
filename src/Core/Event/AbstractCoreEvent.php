<?php

namespace App\Core\Event;

use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractCoreEvent extends Event
{
    public bool $isOk = true;
    public array $data = [];
    public array $errors = [];
    public array $subscribers = [];
    
    public function __construct(array $data = [])
    {   
        $this->data = $data;
    }

    public function isOk(): bool
    {
        if ($this->hasError()) {
            return false;
        }
        return $this->isOk;
    }

    public function hasError(): bool
    {
        return !empty($this->errors);
    }

    public function setOk(bool $isOk): void
    {
        $this->isOk = $isOk;
    }

    public function getErrors(bool $implode = true): mixed
    {
        if ($implode) {
            return implode('||', $this->errors);
        }
        return $this->errors;
    }

    public function addError(string $error): void
    {
        $this->setOk(false);
        $this->errors[] = $error;
    }

    public function setError(string $error): void
    {
        $this->addError($error);
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function getSubscribers(bool $implode = true): mixed
    {
        if ($implode) {
            return implode(' || ', $this->subscribers);
        }
        return $this->subscribers;
    }

    public function addSubscriber(string $key, string $message): void
    {
        $this->subscribers[$key] = $message;
    }
}