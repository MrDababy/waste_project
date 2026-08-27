<?php
/**
 * System Constants
 * 
 * Global constants used throughout the application.
 */

// Date formats
define('DATE_FORMAT', 'Y-m-d');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');
define('DISPLAY_DATE_FORMAT', 'd M Y');
define('DISPLAY_DATETIME_FORMAT', 'd M Y H:i:s');

// User roles
define('ROLE_ADMIN', 'admin');
define('ROLE_COLLECTOR', 'collector');

// Waste statuses
define('STATUS_PENDING', 'pending');
define('STATUS_APPROVED', 'approved');
define('STATUS_REJECTED', 'rejected');

// Waste units
define('UNIT_KG', 'kg');
define('UNIT_TONS', 'tons');
define('UNIT_PIECES', 'pieces');
define('UNIT_LITERS', 'liters');

// Pagination
define('PER_PAGE', 20);
define('PER_PAGE_OPTIONS', [10, 20, 50, 100]);

// File uploads
define('UPLOAD_PATH', dirname(__DIR__) . '/storage/uploads/evidence/');
define('MAX_UPLOAD_SIZE', 5242880); // 5MB

// Cache
define('CACHE_ENABLED', false);
define('CACHE_LIFETIME', 3600); // 1 hour