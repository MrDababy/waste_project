<?php
/**
 * Migration: Add session fields to users table
 */

$sql = "
ALTER TABLE users
ADD COLUMN is_verified BOOLEAN NOT NULL DEFAULT FALSE AFTER is_active,
ADD COLUMN last_activity TIMESTAMP NULL DEFAULT NULL AFTER last_login,
ADD COLUMN login_attempts INT NOT NULL DEFAULT 0 AFTER last_activity,
ADD COLUMN locked_until TIMESTAMP NULL DEFAULT NULL AFTER login_attempts;
";

return $sql;