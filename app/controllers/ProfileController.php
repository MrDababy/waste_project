<?php
/**
 * Profile Controller
 * 
 * Handles user profile management.
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Services\AuthService;
use App\Validators\UserValidator;

class ProfileController extends Controller
{
    /**
     * @var AuthService
     */
    private AuthService $authService;

    /**
     * @var UserValidator
     */
    private UserValidator $validator;

    public function __construct()
    {
        parent::__construct();
        $this->authService = new AuthService();
        $this->validator = new UserValidator();
        
        // Require authentication for all profile actions
        $this->requireAuth();
    }

    /**
     * Show user profile
     */
    public function show(): string
    {
        $user = $this->authService->getCurrentUser();
        
        if (!$user) {
            $this->flash('error', 'User not found.');
            $this->redirect('/login');
        }

        // Get user statistics
        $stats = [
            'total_collected' => $user->getTotalCollected(),
            'collection_count' => $user->getCollectionCount(),
            'approved_count' => $this->getApprovedCount($user->id),
            'pending_count' => $this->getPendingCount($user->id)
        ];

        // Set layout based on role
        $this->setLayout($user->role === 'admin' ? 'admin' : 'user');

        return $this->render('user/profile', [
            'pageTitle' => 'My Profile - Plastic Waste System',
            'user' => $user->toArray(),
            'stats' => $stats
        ]);
    }

    /**
     * Show edit profile form
     */
    public function edit(): string
    {
        $user = $this->authService->getCurrentUser();
        
        if (!$user) {
            $this->flash('error', 'User not found.');
            $this->redirect('/login');
        }

        $this->setLayout($user->role === 'admin' ? 'admin' : 'user');

        return $this->render('user/edit-profile', [
            'pageTitle' => 'Edit Profile - Plastic Waste System',
            'user' => $user->toArray(),
            'csrf_token' => $this->csrfToken()
        ]);
    }

    /**
     * Update profile
     */
    public function update(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid security token.');
            $this->redirect('/user/profile/edit');
            return;
        }

        $user = $this->authService->getCurrentUser();
        if (!$user) {
            $this->flash('error', 'User not found.');
            $this->redirect('/login');
        }

        $data = [
            'first_name' => trim($this->getParam('first_name', '')),
            'last_name' => trim($this->getParam('last_name', '')),
            'email' => trim($this->getParam('email', ''))
        ];

        // Validate
        $validation = $this->validator->validateProfileUpdate($data, $user->id);

        if (!$validation['valid']) {
            foreach ($validation['errors'] as $error) {
                $this->flash('error', $error);
            }
            $this->redirect('/user/profile/edit');
            return;
        }

        // Update user
        $user->fill($data);
        
        if ($user->save()) {
            // Update session data
            $this->session->set('user_data', [
                'username' => $user->username,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name
            ]);
            
            $this->flash('success', 'Profile updated successfully.');
        } else {
            $this->flash('error', 'Failed to update profile. Please try again.');
        }

        $this->redirect('/user/profile');
    }

    /**
     * Show change password form
     */
    public function changePasswordForm(): string
    {
        $user = $this->authService->getCurrentUser();
        
        if (!$user) {
            $this->flash('error', 'User not found.');
            $this->redirect('/login');
        }

        $this->setLayout($user->role === 'admin' ? 'admin' : 'user');

        return $this->render('user/change-password', [
            'pageTitle' => 'Change Password - Plastic Waste System',
            'csrf_token' => $this->csrfToken()
        ]);
    }

    /**
     * Process password change
     */
    public function changePassword(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid security token.');
            $this->redirect('/user/change-password');
            return;
        }

        $user = $this->authService->getCurrentUser();
        if (!$user) {
            $this->flash('error', 'User not found.');
            $this->redirect('/login');
        }

        $currentPassword = $this->getParam('current_password', '');
        $newPassword = $this->getParam('new_password', '');
        $confirmPassword = $this->getParam('confirm_password', '');

        // Validate
        if (empty($currentPassword)) {
            $this->flash('error', 'Current password is required.');
            $this->redirect('/user/change-password');
            return;
        }

        if (strlen($newPassword) < 8) {
            $this->flash('error', 'New password must be at least 8 characters long.');
            $this->redirect('/user/change-password');
            return;
        }

        if ($newPassword !== $confirmPassword) {
            $this->flash('error', 'Passwords do not match.');
            $this->redirect('/user/change-password');
            return;
        }

        // Attempt password change
        $result = $this->authService->changePassword($user->id, $currentPassword, $newPassword);

        if ($result['success']) {
            $this->flash('success', 'Password changed successfully.');
            $this->redirect('/user/profile');
        } else {
            $this->flash('error', $result['message']);
            $this->redirect('/user/change-password');
        }
    }

    /**
     * Get approved count for user
     */
    private function getApprovedCount(int $userId): int
    {
        $sql = "SELECT COUNT(*) FROM plastic_wastes 
                WHERE collector_id = ? AND status = 'approved'";
        return (int)\App\Core\Database::fetchColumn($sql, [$userId]);
    }

    /**
     * Get pending count for user
     */
    private function getPendingCount(int $userId): int
    {
        $sql = "SELECT COUNT(*) FROM plastic_wastes 
                WHERE collector_id = ? AND status = 'pending'";
        return (int)\App\Core\Database::fetchColumn($sql, [$userId]);
    }
}