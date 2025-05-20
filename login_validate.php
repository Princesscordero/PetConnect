<?php
session_start();
require 'includes/db.php';
require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

// Load .env variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad(); // Safe in case .env is missing

// Ensure the request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // reCAPTCHA check
    $recaptchaSecret = $_ENV['RECAPTCHA_SECRET_KEY'] ?? '';
    $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';

    if (empty($recaptchaResponse)) {
        $_SESSION['error'] = "Please complete the reCAPTCHA.";
        header('Location: login.php');
        exit();
    }

    $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$recaptchaSecret}&response={$recaptchaResponse}");
    $captchaSuccess = json_decode($verify);

    if (!$captchaSuccess || !$captchaSuccess->success) {
        $_SESSION['error'] = "Captcha verification failed. Try again.";
        header('Location: login.php');
        exit();
    }

    // Sanitize and validate input
    $usernameOrEmail = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING));
    $password = $_POST['password'] ?? '';

    if (empty($usernameOrEmail) || empty($password)) {
        $_SESSION['error'] = "Username and password are required.";
        header('Location: login.php');
        exit();
    }

    // Fetch user from DB
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Regenerate session ID for security
        session_regenerate_id(true);

        // Set complete user session data
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'name' => $user['name'] ?? '',
            'email' => $user['email'] ?? ''
        ];

        header('Location: home.php');
        exit();
    } else {
        $_SESSION['error'] = "Invalid username/email or password.";
        header('Location: login.php');
        exit();
    }
} else {
    // Reject GET requests
    header('Location: login.php');
    exit();
}
