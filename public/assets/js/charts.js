<?php
/**
 * Authentication Controller
 * 
 * Handles user login, registration, logout, and email verification.
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Services\AuthService;
use App\Services\SessionService;
use App\Services\RememberMeService;
use App\Validators\AuthValidator;
use App\Validators\UserValidator;

class AuthController extends Controller
{
    /**
     * @var AuthService
     */
    private AuthService $authService;

    /**
     * @var SessionService
     */
    private SessionService $sessionService;

    /**
     * @var RememberMeService
     */
    private RememberMeService $rememberMeService;

    /**
     * @var AuthValidator
     */
    private AuthValidator $authValidator;

    /**
     * @var UserValidator
     */
    private UserValidator $userValidator;

    public function __construct()
    {
        parent::__construct();
        $this->authService = new AuthService();
        $this->sessionService = new SessionService();
        $this->rememberMeService = new RememberMeService();
        $this->authValidator = new AuthValidator();
        $this->userValidator = new UserValidator();
        
        // Use public layout for auth pages
        $this->setLayout('public');
    }

    /**
     * Show login form
     */
    public function loginForm(): string
    {
        // Redirect if already logged in
        if ($this->session->isAuthenticated()) {
            return $this->redirectToDashboard();
        }

        // Check if redirected from logout
        $loggedOut = $this->session->get('logged_out', false);
        if ($loggedOut) {
            $this->session->remove('logged_out');
        }

        return $this->render('auth/login', [
            'pageTitle' => 'Login - Plastic Waste System',
            'csrf_token' => $this->csrfToken(),
            'logged_out' => $loggedOut,
            'remember' => $this->session->get('remember_username', '')
        ]);
    }

    /**
     * Process login
     */
    public function login(): void
    {
        // Check CSRF token
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid security token. Please try again.');
            $this->redirect('/login');
            return;
        }

        // Get credentials
        $username = trim($this->getParam('username', ''));
        $password = $this->getParam('password', '');
        $remember = $this->getParam('remember', false);

        // Validate
        $validation = $this->authValidator->validateLogin([
            'username' => $username,
            'password' => $password
        ]);

        if (!$validation['valid']) {
            foreach ($validation['errors'] as $error) {
                $this->flash('error', $error);
            }
            $this->redirect('/login');
            return;
        }

        // Attempt login
        $result = $this->authService->login($username, $password, $remember);

        if ($result['success']) {
            // Clear any old session data
            $this->session->remove('remember_username');
            
            // Regenerate session ID
            $this->session->regenerate(true);
            
            // Set success message
            $this->flash('success', 'Welcome back, ' . $result['user']['first_name'] . '!');
            
            // Redirect based on role
            $this->redirectToDashboard($result['user']['role']);
        } else {
            // Store username for remember me
            $this->session->set('remember_username', $username);
            
            $this->flash('error', $result['message']);
            $this->redirect('/login');
        }
    }

    /**
     * Show registration form
     */
    public function registerForm(): string
    {
        // Redirect if already logged in
        if ($this->session->isAuthenticated()) {
            return $this->redirectToDashboard();
        }

        return $this->render('auth/register', [
            'pageTitle' => 'Register - Plastic Waste System',
            'csrf_token' => $this->csrfToken()
        ]);
    }

    /**
     * Process registration
     */
    public function register(): void
    {
        // Check CSRF token
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid security token. Please try again.');
            $this->redirect('/register');
            return;
        }

        // Get registration data
        $data = [
            'username' => trim($this->getParam('username', '')),
            'email' => trim($this->getParam('email', '')),
            'password' => $this->getParam('password', ''),
            'password_confirm' => $this->getParam('password_confirm', ''),
            'first_name' => trim($this->getParam('first_name', '')),
            'last_name' => trim($this->getParam('last_name', '')),
            'terms' => $this->getParam('terms', false)
        ];

        // Validate
        $validation = $this->userValidator->validateRegistration($data);

        if (!$validation['valid']) {
            foreach ($validation['errors'] as $error) {
                $this->flash('error', $error);
            }
            $this->redirect('/register');
            return;
        }

        // Attempt registration
        $result = $this->authService->register($data);

        if ($result['success']) {
            $this->flash('success', 'Registration successful! Please check your email to verify your account, then login.');
            $this->redirect('/login');
        } else {
            $this->flash('error', $result['message']);
            $this->redirect('/register');
        }
    }

    /**
     * Logout
     */
    public function logout(): void
    {
        // Get user info for message
        $userData = $this->session->get('user_data', []);
        $firstName = $userData['first_name'] ?? 'User';
        
        // Perform logout
        $this->authService->logout();
        
        // Set logged out flag
        $this->session->set('logged_out', true);
        
        $this->flash('info', 'Goodbye, ' . $firstName . '! You have been logged out.');
        $this->redirect('/login');
    }

    /**
     * Redirect to appropriate dashboard
     */
    private function redirectToDashboard(?string $role = null): void
    {
        if ($role === null) {
            $role = $this->session->getUserRole();
        }

        if ($role === 'admin') {
            $this->redirect('/admin/dashboard');
        } else {
            $this->redirect('/user/dashboard');
        }
    }

    /**
     * Show forgot password form
     */
    public function forgotPasswordForm(): string
    {
        if ($this->session->isAuthenticated()) {
            return $this->redirectToDashboard();
        }

        return $this->render('auth/forgot-password', [
            'pageTitle' => 'Reset Password - Plastic Waste System',
            'csrf_token' => $this->csrfToken()
        ]);
    }

    /**
     * Process forgot password request
     */
    public function forgotPassword(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid security token.');
            $this->redirect('/forgot-password');
            return;
        }

        $email = trim($this->getParam('email', ''));
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flash('error', 'Please enter a valid email address.');
            $this->redirect('/forgot-password');
            return;
        }

        $result = $this->authService->sendPasswordResetLink($email);

        if ($result['success']) {
            $this->flash('success', 'Password reset link has been sent to your email address.');
            $this->redirect('/login');
        } else {
            $this->flash('error', $result['message']);
            $this->redirect('/forgot-password');
        }
    }

    /**
     * Show reset password form
     */
    public function resetPasswordForm(string $token): string
    {
        if ($this->session->isAuthenticated()) {
            return $this->redirectToDashboard();
        }

        // Validate token
        $isValid = $this->authService->validateResetToken($token);
        
        if (!$isValid) {
            $this->flash('error', 'Invalid or expired password reset token.');
            $this->redirect('/forgot-password');
            return '';
        }

        return $this->render('auth/reset-password', [
            'pageTitle' => 'Reset Password - Plastic Waste System',
            'csrf_token' => $this->csrfToken(),
            'token' => $token
        ]);
    }

    /**
     * Process password reset
     */
    public function resetPassword(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid security token.');
            $this->redirect('/login');
            return;
        }

        $token = $this->getParam('token', '');
        $password = $this->getParam('password', '');
        $passwordConfirm = $this->getParam('password_confirm', '');

        // Validate password
        if (strlen($password) < 8) {
            $this->flash('error', 'Password must be at least 8 characters long.');
            $this->redirect('/reset-password/' . $token);
            return;
        }

        if ($password !== $passwordConfirm) {
            $this->flash('error', 'Passwords do not match.');
            $this->redirect('/reset-password/' . $token);
            return;
        }

        $result = $this->authService->resetPassword($token, $password);

        if ($result['success']) {
            $this->flash('success', 'Your password has been reset successfully. Please login with your new password.');
            $this->redirect('/login');
        } else {
            $this->flash('error', $result['message']);
            $this->redirect('/forgot-password');
        }
    }

    /**
     * Verify email
     */
    public function verifyEmail(string $token): void
    {
        $result = $this->authService->verifyEmail($token);

        if ($result['success']) {
            $this->flash('success', 'Your email has been verified. You can now login.');
        } else {
            $this->flash('error', $result['message']);
        }
        
        $this->redirect('/login');
    }

    /**
     * Resend verification email
     */
    public function resendVerification(): void
    {
        if (!$this->session->isAuthenticated()) {
            $this->redirect('/login');
            return;
        }

        $userId = $this->session->getUserId();
        $result = $this->authService->resendVerificationEmail($userId);

        if ($result['success']) {
            $this->flash('success', 'Verification email has been resent.');
        } else {
            $this->flash('error', $result['message']);
        }
        
        $this->redirect('/user/profile');
    }
}