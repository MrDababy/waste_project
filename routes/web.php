<?php
/**
 * Web Routes
 * 
 * Defines all application routes.
 */

use App\Core\Application;

$app = Application::getInstance();
$router = $app->getRouter();

// ================================================================
// PUBLIC ROUTES
// ================================================================

// Homepage
$router->get('/', 'PublicController@index');

// Public dashboard
$router->get('/dashboard', 'PublicController@dashboard');

// Public map
$router->get('/map', 'PublicController@map');

// ================================================================
// AUTHENTICATION ROUTES (Guest Only)
// ================================================================

$router->group('/auth', function($router) {
    // Login
    $router->get('/login', 'AuthController@loginForm');
    $router->post('/login', 'AuthController@login');
    
    // Register
    $router->get('/register', 'AuthController@registerForm');
    $router->post('/register', 'AuthController@register');
    
    // Password Reset
    $router->get('/forgot-password', 'AuthController@forgotPasswordForm');
    $router->post('/forgot-password', 'AuthController@forgotPassword');
    $router->get('/reset-password/{token}', 'AuthController@resetPasswordForm');
    $router->post('/reset-password', 'AuthController@resetPassword');
    
    // Email Verification
    $router->get('/verify/{token}', 'AuthController@verifyEmail');
    $router->post('/resend-verification', 'AuthController@resendVerification');
}, ['App\Middleware\GuestMiddleware']);

// Logout (Authenticated)
$router->get('/logout', 'AuthController@logout');

// ================================================================
// USER ROUTES (Authenticated Users)
// ================================================================

$router->group('/user', function($router) {
    // Dashboard
    $router->get('/dashboard', 'UserController@dashboard');
    
    // Profile
    $router->get('/profile', 'ProfileController@show');
    $router->get('/profile/edit', 'ProfileController@edit');
    $router->post('/profile/update', 'ProfileController@update');
    $router->get('/profile/change-password', 'ProfileController@changePasswordForm');
    $router->post('/profile/change-password', 'ProfileController@changePassword');
    
    // Waste Records
    $router->get('/records', 'UserController@records');
    $router->get('/submit', 'UserController@submitForm');
    $router->post('/submit', 'UserController@submit');
    $router->get('/record/{id}', 'UserController@viewRecord');
    $router->get('/statistics', 'UserController@statistics');
}, ['App\Middleware\AuthMiddleware']);

// ================================================================
// ADMIN ROUTES
// ================================================================

$router->group('/admin', function($router) {
    // Dashboard
    $router->get('/dashboard', 'AdminController@dashboard');
    
    // User Management
    $router->get('/users', 'AdminController@users');
    $router->get('/users/create', 'AdminController@createUser');
    $router->post('/users', 'AdminController@storeUser');
    $router->get('/users/{id}/edit', 'AdminController@editUser');
    $router->put('/users/{id}', 'AdminController@updateUser');
    $router->delete('/users/{id}', 'AdminController@deleteUser');
    
    // School Management
    $router->get('/schools', 'AdminController@schools');
    $router->get('/schools/create', 'AdminController@createSchool');
    $router->post('/schools', 'AdminController@storeSchool');
    $router->get('/schools/{id}/edit', 'AdminController@editSchool');
    $router->put('/schools/{id}', 'AdminController@updateSchool');
    $router->delete('/schools/{id}', 'AdminController@deleteSchool');
    
    // Location Management
    $router->get('/locations', 'AdminController@locations');
    $router->get('/locations/create', 'AdminController@createLocation');
    $router->post('/locations', 'AdminController@storeLocation');
    $router->get('/locations/{id}/edit', 'AdminController@editLocation');
    $router->put('/locations/{id}', 'AdminController@updateLocation');
    $router->delete('/locations/{id}', 'AdminController@deleteLocation');
    
    // Plastic Type Management
    $router->get('/plastic-types', 'AdminController@plasticTypes');
    $router->get('/plastic-types/create', 'AdminController@createPlasticType');
    $router->post('/plastic-types', 'AdminController@storePlasticType');
    $router->get('/plastic-types/{id}/edit', 'AdminController@editPlasticType');
    $router->put('/plastic-types/{id}', 'AdminController@updatePlasticType');
    $router->delete('/plastic-types/{id}', 'AdminController@deletePlasticType');
    
    // Waste Records Management
    $router->get('/records', 'AdminController@records');
    $router->get('/records/{id}', 'AdminController@viewRecord');
    $router->get('/records/{id}/edit', 'AdminController@editRecord');
    $router->put('/records/{id}', 'AdminController@updateRecord');
    $router->delete('/records/{id}', 'AdminController@deleteRecord');
    
    // Approvals
    $router->get('/approvals', 'AdminController@approvals');
    $router->post('/approvals/{id}/approve', 'AdminController@approve');
    $router->post('/approvals/{id}/reject', 'AdminController@reject');
    
    // Reports
    $router->get('/reports', 'AdminController@reports');
    $router->get('/reports/generate', 'AdminController@generateReport');
    $router->get('/reports/export', 'AdminController@exportReport');
    
    // Audit Logs
    $router->get('/audit', 'AdminController@audit');
    
    // Search
    $router->get('/search', 'AdminController@search');
    
    // Settings
    $router->get('/settings', 'AdminController@settings');
    $router->post('/settings', 'AdminController@updateSettings');
}, ['App\Middleware\AuthMiddleware', 'App\Middleware\AdminMiddleware']);

// ================================================================
// API ROUTES
// ================================================================

$router->group('/api', function($router) {
    // Public API
    $router->get('/statistics', 'ApiController@statistics');
    $router->get('/locations', 'ApiController@locations');
    $router->get('/chart-data', 'ApiController@chartData');
    
    // Authenticated API
    $router->get('/user/records', 'ApiController@userRecords');
    $router->post('/waste', 'ApiController@storeWaste');
    
    // Admin API
    $router->get('/admin/records', 'ApiController@adminRecords');
    $router->put('/admin/records/{id}', 'ApiController@updateRecord');
    $router->delete('/admin/records/{id}', 'ApiController@deleteRecord');
}, ['App\Middleware\AuthMiddleware']);