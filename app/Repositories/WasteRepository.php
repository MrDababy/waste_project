<?php
/**
 * Waste Repository
 * 
 * Data access for plastic waste records.
 */

namespace App\Repositories;

use App\Core\Database;
use App\Models\PlasticWaste;
use App\Interfaces\RepositoryInterface;

class WasteRepository implements RepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function find(int $id): ?PlasticWaste
    {
        return PlasticWaste::find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(array $conditions = [], array $orderBy = [], ?int $limit = null): array
    {
        return PlasticWaste::findAll($conditions, $orderBy, $limit);
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data): PlasticWaste
    {
        $waste = new PlasticWaste($data);
        $waste->save();
        return $waste;
    }

    /**
     * {@inheritdoc}
     */
    public function update(int $id, array $data): ?PlasticWaste
    {
        $waste = $this->find($id);
        if (!$waste) {
            return null;
        }
        $waste->fill($data);
        $waste->save();
        return $waste;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(int $id): bool
    {
        $waste = $this->find($id);
        if (!$waste) {
            return false;
        }
        return $waste->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function count(array $conditions = []): int
    {
        return PlasticWaste::count($conditions);
    }

    /**
     * Get recent approved records
     */
    public function getRecentApproved(int $limit = 5): array
    {
        $sql = "SELECT w.*, 
                       u.first_name, u.last_name,
                       l.name as location_name,
                       p.name as plastic_name
                FROM plastic_wastes w
                JOIN users u ON w.collector_id = u.id
                JOIN locations l ON w.location_id = l.id
                JOIN plastic_types p ON w.plastic_type_id = p.id
                WHERE w.status = 'approved'
                ORDER BY w.created_at DESC
                LIMIT ?";
        
        return Database::fetchAll($sql, [$limit]);
    }

    /**
     * Get pending records for approval
     */
    public function getPendingRecords(int $limit = 20): array
    {
        $sql = "SELECT w.*, 
                       u.first_name, u.last_name, u.username,
                       l.name as location_name,
                       s.name as school_name,
                       p.name as plastic_name,
                       p.code as plastic_code
                FROM plastic_wastes w
                JOIN users u ON w.collector_id = u.id
                JOIN locations l ON w.location_id = l.id
                JOIN schools s ON l.school_id = s.id
                JOIN plastic_types p ON w.plastic_type_id = p.id
                WHERE w.status = 'pending'
                ORDER BY w.created_at ASC
                LIMIT ?";
        
        return Database::fetchAll($sql, [$limit]);
    }

    /**
     * Get records by collector
     */
    public function getByCollector(int $collectorId, array $filters = []): array
    {
        $sql = "SELECT w.*, 
                       l.name as location_name,
                       p.name as plastic_name,
                       p.color_code
                FROM plastic_wastes w
                JOIN locations l ON w.location_id = l.id
                JOIN plastic_types p ON w.plastic_type_id = p.id
                WHERE w.collector_id = ?";
        
        $params = [$collectorId];

        if (!empty($filters['status'])) {
            $sql .= " AND w.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['from_date'])) {
            $sql .= " AND w.collection_date >= ?";
            $params[] = $filters['from_date'];
        }

        if (!empty($filters['to_date'])) {
            $sql .= " AND w.collection_date <= ?";
            $params[] = $filters['to_date'];
        }

        $sql .= " ORDER BY w.created_at DESC";

        return Database::fetchAll($sql, $params);
    }

    /**
     * Get records for admin with filters
     */
    public function getAdminRecords(array $filters = [], int $limit = 20, int $offset = 0): array
    {
        $sql = "SELECT w.*, 
                       u.first_name, u.last_name, u.username,
                       l.name as location_name,
                       s.name as school_name,
                       p.name as plastic_name,
                       a.first_name as approver_first_name,
                       a.last_name as approver_last_name
                FROM plastic_wastes w
                JOIN users u ON w.collector_id = u.id
                JOIN locations l ON w.location_id = l.id
                JOIN schools s ON l.school_id = s.id
                JOIN plastic_types p ON w.plastic_type_id = p.id
                LEFT JOIN users a ON w.approved_by = a.id
                WHERE 1=1";
        
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND w.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['location_id'])) {
            $sql .= " AND w.location_id = ?";
            $params[] = $filters['location_id'];
        }

        if (!empty($filters['plastic_type_id'])) {
            $sql .= " AND w.plastic_type_id = ?";
            $params[] = $filters['plastic_type_id'];
        }

        if (!empty($filters['collector_id'])) {
            $sql .= " AND w.collector_id = ?";
            $params[] = $filters['collector_id'];
        }

        if (!empty($filters['from_date'])) {
            $sql .= " AND w.collection_date >= ?";
            $params[] = $filters['from_date'];
        }

        if (!empty($filters['to_date'])) {
            $sql .= " AND w.collection_date <= ?";
            $params[] = $filters['to_date'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (l.name LIKE ? OR s.name LIKE ? OR u.username LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " ORDER BY w.created_at DESC";
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        return Database::fetchAll($sql, $params);
    }

    /**
     * Count admin records with filters
     */
    public function countAdminRecords(array $filters = []): int
    {
        $sql = "SELECT COUNT(*) 
                FROM plastic_wastes w
                JOIN users u ON w.collector_id = u.id
                JOIN locations l ON w.location_id = l.id
                JOIN schools s ON l.school_id = s.id
                JOIN plastic_types p ON w.plastic_type_id = p.id
                WHERE 1=1";
        
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND w.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['location_id'])) {
            $sql .= " AND w.location_id = ?";
            $params[] = $filters['location_id'];
        }

        if (!empty($filters['plastic_type_id'])) {
            $sql .= " AND w.plastic_type_id = ?";
            $params[] = $filters['plastic_type_id'];
        }

        if (!empty($filters['from_date'])) {
            $sql .= " AND w.collection_date >= ?";
            $params[] = $filters['from_date'];
        }

        if (!empty($filters['to_date'])) {
            $sql .= " AND w.collection_date <= ?";
            $params[] = $filters['to_date'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (l.name LIKE ? OR s.name LIKE ? OR u.username LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        return (int)Database::fetchColumn($sql, $params);
    }
}