<?php
/**
 * Location Repository
 * 
 * Data access for locations.
 */

namespace App\Repositories;

use App\Core\Database;
use App\Models\Location;
use App\Interfaces\RepositoryInterface;

class LocationRepository implements RepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function find(int $id): ?Location
    {
        return Location::find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(array $conditions = [], array $orderBy = [], ?int $limit = null): array
    {
        return Location::findAll($conditions, $orderBy, $limit);
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data): Location
    {
        $location = new Location($data);
        $location->save();
        return $location;
    }

    /**
     * {@inheritdoc}
     */
    public function update(int $id, array $data): ?Location
    {
        $location = $this->find($id);
        if (!$location) {
            return null;
        }
        $location->fill($data);
        $location->save();
        return $location;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(int $id): bool
    {
        $location = $this->find($id);
        if (!$location) {
            return false;
        }
        return $location->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function count(array $conditions = []): int
    {
        return Location::count($conditions);
    }

    /**
     * Get all locations with statistics
     */
    public function getAllWithStats(): array
    {
        $sql = "SELECT 
                    l.*,
                    s.name as school_name,
                    s.id as school_id,
                    w.name as ward_name,
                    d.name as district_name,
                    r.name as region_name,
                    COUNT(wr.id) as collection_count,
                    SUM(wr.amount) as total_collected,
                    MAX(wr.collection_date) as latest_collection
                FROM locations l
                JOIN schools s ON l.school_id = s.id
                JOIN wards w ON l.ward_id = w.id
                JOIN districts d ON w.district_id = d.id
                JOIN regions r ON d.region_id = r.id
                LEFT JOIN plastic_wastes wr ON l.id = wr.location_id AND wr.status = 'approved'
                WHERE l.is_monitored = 1
                GROUP BY l.id
                ORDER BY total_collected DESC";
        
        return Database::fetchAll($sql);
    }

    /**
     * Get locations by school
     */
    public function getBySchool(int $schoolId): array
    {
        return Location::findAll(['school_id' => $schoolId]);
    }

    /**
     * Get monitored locations with coordinates
     */
    public function getMonitoredWithCoordinates(): array
    {
        $sql = "SELECT 
                    id,
                    name,
                    school_id,
                    latitude,
                    longitude,
                    is_monitored
                FROM locations
                WHERE is_monitored = 1
                AND latitude IS NOT NULL
                AND longitude IS NOT NULL";
        
        return Database::fetchAll($sql);
    }
}