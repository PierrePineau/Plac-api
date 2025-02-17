<?php

namespace App\Core\Interface;

interface FileServiceInterface
{
    /**
     * Get a file by its identifier.
     *
     * @param array $options
     * @return mixed
     */
    public function get(array $options, bool $throwException = false): mixed;

    /**
     * Upload a file.
     *
     * @param array $options
     * @return mixed
     */
    public function upload(array $options): void;

    /**
     * Delete a file by its identifier.
     *
     * @param array $options
     * @return bool
     */
    public function delete(array $options): void;
}