<?php
/**
 * Service Interface
 * 
 * Defines the contract for service classes.
 */

namespace App\Interfaces;

interface ServiceInterface
{
    /**
     * Initialize the service
     */
    public function initialize(): void;

    /**
     * Check if service is available
     */
    public function isAvailable(): bool;

    /**
     * Get service name
     */
    public function getName(): string;
}