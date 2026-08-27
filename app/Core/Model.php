<?php
/**
 * Base Model
 * 
 * Provides CRUD operations and database interaction.
 */

namespace App\Core;

use App\Core\Database;
use App\Core\Config;

abstract class Model
{
    /**
     * @var string Table name
     */
    protected static string $table;

    /**
     * @var string Primary key column
     */
    protected static string $primaryKey = 'id';

    /**
     * @var array Fillable columns
     */
    protected static array $fillable = [];

    /**
     * @var array Hidden columns (not returned in toArray)
     */
    protected static array $hidden = [];

    /**
     * @var array Encrypted columns
     */
    protected static array $encrypted = [];

    /**
     * @var array Original data before changes
     */
    protected array $original = [];

    /**
     * @var array Current data
     */
    protected array $attributes = [];

    /**
     * @var bool Whether model is new (not yet saved)
     */
    protected bool $isNew = true;

    /**
     * @var bool Whether model has changes
     */
    protected bool $isDirty = false;

    /**
     * Constructor
     * 
     * @param array $data Initial data
     */
    public function __construct(array $data = [])
    {
        $this->fill($data);
        $this->original = $this->attributes;
        
        // If ID is set, consider it existing
        if (isset($this->attributes[static::$primaryKey])) {
            $this->isNew = false;
        }
    }

    /**
     * Get the table name
     */
    public static function getTable(): string
    {
        if (!isset(static::$table)) {
            throw new \RuntimeException('Table name must be defined in model class.');
        }
        return static::$table;
    }

    /**
     * Get the primary key
     */
    public static function getPrimaryKey(): string
    {
        return static::$primaryKey;
    }

    /**
     * Get fillable columns
     */
    public static function getFillable(): array
    {
        return static::$fillable;
    }

    /**
     * Get encrypted columns
     */
    public static function getEncrypted(): array
    {
        return static::$encrypted;
    }

    /**
     * Fill model with data
     * 
     * @param array $data Data to fill
     * @return self
     */
    public function fill(array $data): self
    {
        foreach ($data as $key => $value) {
            if (in_array($key, static::$fillable)) {
                $this->attributes[$key] = $value;
                $this->isDirty = true;
            }
        }
        return $this;
    }

    /**
     * Set an attribute
     * 
     * @param string $key Attribute name
     * @param mixed $value Attribute value
     */
    public function setAttribute(string $key, $value): void
    {
        if (in_array($key, static::$fillable)) {
            $this->attributes[$key] = $value;
            $this->isDirty = true;
        }
    }

    /**
     * Get an attribute
     * 
     * @param string $key Attribute name
     * @return mixed
     */
    public function getAttribute(string $key)
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * Magic getter
     */
    public function __get(string $key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Magic setter
     */
    public function __set(string $key, $value): void
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Convert model to array
     * 
     * @param bool $decrypt Whether to decrypt encrypted fields
     * @return array
     */
    public function toArray(bool $decrypt = true): array
    {
        $data = $this->attributes;

        // Decrypt encrypted fields if needed
        if ($decrypt && !empty(static::$encrypted)) {
            $encryptionService = new \App\Services\EncryptionService();
            foreach (static::$encrypted as $field) {
                if (isset($data[$field]) && !empty($data[$field])) {
                    try {
                        $data[$field] = $encryptionService->decrypt($data[$field]);
                    } catch (\Exception $e) {
                        // Keep encrypted value if decryption fails
                    }
                }
            }
        }

        // Remove hidden fields
        foreach (static::$hidden as $field) {
            unset($data[$field]);
        }

        return $data;
    }

    /**
     * Convert model to JSON
     * 
     * @param bool $decrypt Whether to decrypt encrypted fields
     * @return string
     */
    public function toJson(bool $decrypt = true): string
    {
        return json_encode($this->toArray($decrypt));
    }

    /**
     * Save the model
     * 
     * @return bool
     */
    public function save(): bool
    {
        // Encrypt encrypted fields before saving
        $this->encryptFields();

        if ($this->isNew) {
            return $this->insert();
        } else {
            return $this->update();
        }
    }

    /**
     * Insert new record
     * 
     * @return bool
     */
    protected function insert(): bool
    {
        $data = $this->attributes;
        unset($data[static::$primaryKey]);

        $columns = array_keys($data);
        $placeholders = array_map(function($col) {
            return ':' . $col;
        }, $columns);

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            static::getTable(),
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = Database::execute($sql, $data);
        
        if ($stmt->rowCount() > 0) {
            $this->attributes[static::$primaryKey] = Database::lastInsertId();
            $this->isNew = false;
            $this->original = $this->attributes;
            $this->isDirty = false;
            return true;
        }

        return false;
    }

    /**
     * Update existing record
     * 
     * @return bool
     */
    protected function update(): bool
    {
        if ($this->isNew) {
            return false;
        }

        $data = $this->attributes;
        $primaryKey = static::$primaryKey;
        $id = $data[$primaryKey] ?? null;

        if ($id === null) {
            return false;
        }

        unset($data[$primaryKey]);

        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "{$key} = :{$key}";
        }

        $sql = sprintf(
            "UPDATE %s SET %s WHERE %s = :id",
            static::getTable(),
            implode(', ', $set),
            $primaryKey
        );

        $data['id'] = $id;
        $stmt = Database::execute($sql, $data);
        
        if ($stmt->rowCount() > 0) {
            $this->original = $this->attributes;
            $this->isDirty = false;
            return true;
        }

        return false;
    }

    /**
     * Delete the record
     * 
     * @return bool
     */
    public function delete(): bool
    {
        if ($this->isNew) {
            return false;
        }

        $primaryKey = static::$primaryKey;
        $id = $this->attributes[$primaryKey] ?? null;

        if ($id === null) {
            return false;
        }

        $sql = sprintf(
            "DELETE FROM %s WHERE %s = :id",
            static::getTable(),
            $primaryKey
        );

        $stmt = Database::execute($sql, ['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Encrypt fields marked for encryption
     */
    protected function encryptFields(): void
    {
        if (empty(static::$encrypted)) {
            return;
        }

        $encryptionService = new \App\Services\EncryptionService();

        foreach (static::$encrypted as $field) {
            if (isset($this->attributes[$field]) && !empty($this->attributes[$field])) {
                // Only encrypt if not already encrypted
                // Simple check: if it's not base64 encoded, encrypt it
                $value = $this->attributes[$field];
                if (!preg_match('/^[A-Za-z0-9+\/=]+$/', $value) || strlen($value) < 32) {
                    $this->attributes[$field] = $encryptionService->encrypt($value);
                }
            }
        }
    }

    /**
     * Decrypt fields marked for encryption
     */
    public function decryptFields(): void
    {
        if (empty(static::$encrypted)) {
            return;
        }

        $encryptionService = new \App\Services\EncryptionService();

        foreach (static::$encrypted as $field) {
            if (isset($this->attributes[$field]) && !empty($this->attributes[$field])) {
                try {
                    $this->attributes[$field] = $encryptionService->decrypt($this->attributes[$field]);
                } catch (\Exception $e) {
                    // Keep as is if decryption fails
                }
            }
        }
    }

    /**
     * Find a record by ID
     * 
     * @param int $id Record ID
     * @param bool $decrypt Whether to decrypt fields
     * @return self|null
     */
    public static function find(int $id, bool $decrypt = true): ?self
    {
        $sql = sprintf(
            "SELECT * FROM %s WHERE %s = :id LIMIT 1",
            static::getTable(),
            static::$primaryKey
        );

        $result = Database::fetch($sql, ['id' => $id]);
        
        if ($result === null) {
            return null;
        }

        $model = new static($result);
        if ($decrypt) {
            $model->decryptFields();
        }
        return $model;
    }

    /**
     * Find all records
     * 
     * @param array $conditions WHERE conditions
     * @param array $orderBy ORDER BY clause
     * @param int|null $limit Limit
     * @param bool $decrypt Whether to decrypt fields
     * @return array
     */
    public static function findAll(
        array $conditions = [],
        array $orderBy = [],
        ?int $limit = null,
        bool $decrypt = true
    ): array {
        $sql = "SELECT * FROM " . static::getTable();
        
        $params = [];
        
        // WHERE clause
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "{$key} = :{$key}";
                $params[$key] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        // ORDER BY
        if (!empty($orderBy)) {
            $order = [];
            foreach ($orderBy as $column => $direction) {
                $order[] = "{$column} {$direction}";
            }
            $sql .= " ORDER BY " . implode(', ', $order);
        }
        
        // LIMIT
        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit;
        }
        
        $results = Database::fetchAll($sql, $params);
        
        $models = [];
        foreach ($results as $result) {
            $model = new static($result);
            if ($decrypt) {
                $model->decryptFields();
            }
            $models[] = $model;
        }
        
        return $models;
    }

    /**
     * Count records
     * 
     * @param array $conditions WHERE conditions
     * @return int
     */
    public static function count(array $conditions = []): int
    {
        $sql = "SELECT COUNT(*) FROM " . static::getTable();
        
        $params = [];
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "{$key} = :{$key}";
                $params[$key] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        return (int)Database::fetchColumn($sql, $params);
    }

    /**
     * Check if model is new
     */
    public function isNew(): bool
    {
        return $this->isNew;
    }

    /**
     * Check if model has changes
     */
    public function isDirty(): bool
    {
        return $this->isDirty;
    }

    /**
     * Get original data
     */
    public function getOriginal(): array
    {
        return $this->original;
    }

    /**
     * Get the ID
     */
    public function getId(): ?int
    {
        return $this->attributes[static::$primaryKey] ?? null;
    }

    /**
     * Set the ID
     */
    public function setId(int $id): void
    {
        $this->attributes[static::$primaryKey] = $id;
        $this->isNew = false;
    }
}