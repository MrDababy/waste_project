<?php
/**
 * Plastic Waste Model
 * 
 * Represents plastic waste collection records with encryption.
 */

namespace App\Models;

use App\Core\Model;

class PlasticWaste extends Model
{
    /**
     * @var string Table name
     */
    protected static string $table = 'plastic_wastes';

    /**
     * @var array Fillable columns
     */
    protected static array $fillable = [
        'collector_id',
        'location_id',
        'plastic_type_id',
        'collection_date',
        'amount',
        'unit',
        'description',
        'evidence_image',
        'latitude',
        'longitude',
        'status',
        'approval_notes',
        'approved_by',
        'approved_at',
        'rejection_reason'
    ];

    /**
     * @var array Encrypted columns
     */
    protected static array $encrypted = [
        'description',
        'latitude',
        'longitude',
        'approval_notes',
        'rejection_reason'
    ];

    /**
     * @var array Hidden columns
     */
    protected static array $hidden = [
        'encrypted_data'
    ];

    /**
     * Check if record is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if record is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if record is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Approve the record
     */
    public function approve(int $adminId): bool
    {
        $this->status = 'approved';
        $this->approved_by = $adminId;
        $this->approved_at = date('Y-m-d H:i:s');
        $this->rejection_reason = null;
        return $this->save();
    }

    /**
     * Reject the record
     */
    public function reject(int $adminId, string $reason): bool
    {
        $this->status = 'rejected';
        $this->approved_by = $adminId;
        $this->approved_at = date('Y-m-d H:i:s');
        $this->rejection_reason = $reason;
        return $this->save();
    }

    /**
     * Get amount in kg (normalized)
     */
    public function getAmountInKg(): float
    {
        return match($this->unit) {
            'kg' => (float)$this->amount,
            'tons' => (float)$this->amount * 1000,
            'pieces' => (float)$this->amount * 0.05, // Rough estimate
            'liters' => (float)$this->amount * 0.9, // Rough estimate
            default => (float)$this->amount
        };
    }
}