<?php
/**
 * Authentication Controller
 * 
 * Handles user login, registration, and logout.
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Services\AuthService;
use App\Validators\UserValidator;

class AuthController extends Controller
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
            $role = $this->session->getUserRole();
            if ($role === 'admin') {
                $this->redirect('/admin/dashboard');
            } else {
                $this->redirect('/user/dashboard');
            }
        }

        return $this->render('auth/login', [
            'pageTitle' => 'Login - Plastic Waste System'
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
        $username = $this->getParam('username', '');
        $password = $this->getParam('password', '');
        $remember = $this->getParam('remember', false);

        // Validate
        if (empty($username) || empty($password)) {
            $this->flash('error', 'Please enter both username and password.');
            $this->redirect('/login');
            return;
        }

        // Attempt login
        $result = $this->authService->login($username, $password, $remember);

        if ($result['success']) {
            $this->flash('success', 'Welcome back, ' . $result['user']['first_name'] . '!');
            
            // Redirect based on role
            if ($result['user']['role'] === 'admin') {
                $this->redirect('/admin/dashboard');
            } else {
                $this->redirect('/user/dashboard');
            }
        } else {
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
            $this->redirect('/dashboard');
        }

        return $this->render('auth/register', [
            'pageTitle' => 'Register - Plastic Waste System'
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
            'username' => $this->getParam('username', ''),
            'email' => $this->getParam('email', ''),
            'password' => $this->getParam('password', ''),
            'password_confirm' => $this->getParam('password_confirm', ''),
            'first_name' => $this->getParam('first_name', ''),
            'last_name' => $this->getParam('last_name', '')
        ];

        // Validate
        $validation = $this->validator->validateRegistration($data);

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
            $this->flash('success', 'Registration successful! Please login.');
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
        $this->authService->logout();
        $this->flash('info', 'You have been logged out.');
        $this->redirect('/');
    }
}