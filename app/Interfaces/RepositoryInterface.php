<?php
/**
 * Repository Interface
 * 
 * Defines the contract for repository classes.
 */

namespace App\Interfaces;

interface RepositoryInterface
{
    /**
     * Find a record by ID
     * 
     * @param int $id Record ID
     * @return mixed|null
     */
    public function find(int $id);

    /**
     * Find all records
     * 
     * @param array $conditions WHERE conditions
     * @param array $orderBy ORDER BY clause
     * @param int|null $limit Limit
     * @return array
     */
    public function findAll(array $conditions = [], array $orderBy = [], ?int $limit = null): array;

    /**
     * Create a new record
     * 
     * @param array $data Data to create
     * @return mixed
     */
    public function create(array $data);

    /**
     * Update a record
     * 
     * @param int $id Record ID
     * @param array $data Data to update
     * @return mixed
     */
    public function update(int $id, array $data);

    /**
     * Delete a record
     * 
     * @param int $id Record ID
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Count records
     * 
     * @param array $conditions WHERE conditions
     * @return int
     */
    public function count(array $conditions = []): int;
}