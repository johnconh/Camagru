<?php
session_start();

require_once '../src/config/database.php';
require_once '../src/controllers/authController.php';
require_once '../src/models/user.php';
require_once '../src/services/emailService.php';
require_once '../src/controllers/userController.php';

$page = $_GET['page'] ?? 'home';
$auth = new AuthController();

switch ($page) {
    case 'register':
        $auth->register();
        break;
    case 'login':
        $auth->login();
        break;
    case 'logout':
        $auth->logout();
        break;
    case 'verify':
        $auth->verify();
        break;
    case 'forgotPassword':
        $auth->forgotPassword();
        break;
    case 'reset-password':
        $auth->resetPassword();
        break;
    case 'editProfile':
        $controller = new UserController();
        $controller->editProfile();
        break;
    case 'home':
    default:
        $view = '../src/views/home.php';
        require_once '../src/views/layouts/main.php';
        break;
}
