<?php
/**
 * Database Connection Manager
 * 
 * PDO wrapper with prepared statements and connection management.
 */

namespace App\Core;

use PDO;
use PDOException;
use App\Core\Config;

class Database
{
    /**
     * @var PDO Database connection
     */
    private static ?PDO $connection = null;

    /**
     * @var array Connection configuration
     */
    private static array $config = [];

    /**
     * @var int Query count for debugging
     */
    private static int $queryCount = 0;

    /**
     * @var array Query log for debugging
     */
    private static array $queryLog = [];

    /**
     * Initialize database configuration
     */
    private static function initConfig(): void
    {
        if (empty(self::$config)) {
            self::$config = Config::get('database.default', []);
        }
    }

    /**
     * Get database connection (Singleton)
     * 
     * @return PDO
     * @throws \PDOException
     */
    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            self::initConfig();
            
            $dsn = sprintf(
                "%s:host=%s;port=%d;dbname=%s;charset=%s",
                self::$config['driver'] ?? 'mysql',
                self::$config['host'] ?? 'localhost',
                self::$config['port'] ?? 3306,
                self::$config['database'] ?? '',
                self::$config['charset'] ?? 'utf8mb4'
            );

            try {
                self::$connection = new PDO(
                    $dsn,
                    self::$config['username'] ?? '',
                    self::$config['password'] ?? '',
                    self::$config['options'] ?? []
                );
            } catch (PDOException $e) {
                throw new PDOException('Database connection failed: ' . $e->getMessage(), (int)$e->getCode());
            }
        }

        return self::$connection;
    }

    /**
     * Begin a transaction
     */
    public static function beginTransaction(): bool
    {
        return self::getConnection()->beginTransaction();
    }

    /**
     * Commit a transaction
     */
    public static function commit(): bool
    {
        return self::getConnection()->commit();
    }

    /**
     * Rollback a transaction
     */
    public static function rollback(): bool
    {
        return self::getConnection()->rollback();
    }

    /**
     * Execute a prepared statement
     * 
     * @param string $sql SQL query with named placeholders
     * @param array $params Parameters to bind
     * @return \PDOStatement
     */
    public static function execute(string $sql, array $params = []): \PDOStatement
    {
        self::$queryCount++;
        
        if (Config::get('app.debug', false)) {
            self::$queryLog[] = [
                'sql' => $sql,
                'params' => $params,
                'time' => microtime(true)
            ];
        }

        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Fetch all rows
     * 
     * @param string $sql SQL query
     * @param array $params Parameters
     * @return array
     */
    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::execute($sql, $params)->fetchAll();
    }

    /**
     * Fetch a single row
     * 
     * @param string $sql SQL query
     * @param array $params Parameters
     * @return array|null
     */
    public static function fetch(string $sql, array $params = []): ?array
    {
        $result = self::execute($sql, $params)->fetch();
        return $result ?: null;
    }

    /**
     * Fetch a single column value
     * 
     * @param string $sql SQL query
     * @param array $params Parameters
     * @param int $column Column index
     * @return mixed
     */
    public static function fetchColumn(string $sql, array $params = [], int $column = 0)
    {
        return self::execute($sql, $params)->fetchColumn($column);
    }

    /**
     * Get the last insert ID
     * 
     * @return string
     */
    public static function lastInsertId(): string
    {
        return self::getConnection()->lastInsertId();
    }

    /**
     * Get the number of rows affected by the last query
     * 
     * @return int
     */
    public static function rowCount(): int
    {
        return self::getConnection()->rowCount();
    }

    /**
     * Get the query count
     */
    public static function getQueryCount(): int
    {
        return self::$queryCount;
    }

    /**
     * Get the query log
     */
    public static function getQueryLog(): array
    {
        return self::$queryLog;
    }

    /**
     * Clear the query log
     */
    public static function clearQueryLog(): void
    {
        self::$queryLog = [];
    }

    /**
     * Close the database connection
     */
    public static function close(): void
    {
        self::$connection = null;
    }
}