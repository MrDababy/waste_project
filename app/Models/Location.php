<?php
/**
 * Location Model
 * 
 * Represents collection locations.
 */

namespace App\Models;

use App\Core\Model;

class Location extends Model
{
    /**
     * @var string Table name
     */
    protected static string $table = 'locations';

    /**
     * @var array Fillable columns
     */
    protected static array $fillable = [
        'name',
        'school_id',
        'ward_id',
        'latitude',
        'longitude',
        'address_id',
        'is_monitored'
    ];

    /**
     * Get school
     */
    public function getSchool(): ?School
    {
        if (!isset($this->school_id)) {
            return null;
        }
        return School::find($this->school_id);
    }

    /**
     * Get total approved plastic collected
     */
    public function getTotalCollected(): float
    {
        $sql = "SELECT SUM(amount) as total 
                FROM plastic_wastes 
                WHERE location_id = ? AND status = 'approved'";
        
        $result = \App\Core\Database::fetch($sql, [$this->id]);
        return (float)($result['total'] ?? 0);
    }

    /**
     * Get collection count
     */
    public function getCollectionCount(): int
    {
        return PlasticWaste::count([
            'location_id' => $this->id,
            'status' => 'approved'
        ]);
    }

    /**
     * Get latest collection date
     */
    public function getLatestCollectionDate(): ?string
    {
        $sql = "SELECT collection_date 
                FROM plastic_wastes 
                WHERE location_id = ? AND status = 'approved' 
                ORDER BY collection_date DESC 
                LIMIT 1";
        
        $result = \App\Core\Database::fetch($sql, [$this->id]);
        return $result['collection_date'] ?? null;
    }
}