<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers to handle CORS and JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get form data
$fullName = isset($_POST['FNAME']) ? trim($_POST['FNAME']) : '';
$phone = isset($_POST['PHONE']) ? trim($_POST['PHONE']) : '';
$email = isset($_POST['EMAIL']) ? trim($_POST['EMAIL']) : '';
$projectType = isset($_POST['PROJECTTYP']) ? trim($_POST['PROJECTTYP']) : '';
$message = isset($_POST['MESSAGE']) ? trim($_POST['MESSAGE']) : '';
$checkbox = isset($_POST['checkbox']) ? $_POST['checkbox'] : '';

// Validate required fields
$errors = [];
if (empty($fullName)) {
    $errors[] = 'Full name is required';
}
if (empty($phone)) {
    $errors[] = 'Phone number is required';
}
if (empty($email)) {
    $errors[] = 'Email is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address';
}
if (empty($projectType)) {
    $errors[] = 'Project type is required';
}
if (empty($message)) {
    $errors[] = 'Message is required';
}

// If there are validation errors, return them
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Validation failed', 'errors' => $errors]);
    exit;
}

// Prepare email content
$to = 'info@appinnovative.com';
$subject = 'New Project Inquiry: ' . $projectType . ' - ' . $fullName;

$emailBody = "
New contact form submission received from AppInnovative website.

Form Details:
============
Full Name: {$fullName}
Phone Number: {$phone}
Email Address: {$email}
Project Type: {$projectType}
Message: {$message}
Communication Consent: " . ($checkbox ? 'Yes' : 'No') . "

Submitted on: " . date('Y-m-d H:i:s') . "
IP Address: " . $_SERVER['REMOTE_ADDR'] . "
User Agent: " . $_SERVER['HTTP_USER_AGENT'] . "

---
This email was sent from the contact form on appinnovative.com
";

// Email headers
$headers = [
    'From: noreply@appinnovative.com',
    'Reply-To: ' . $email,
    'X-Mailer: PHP/' . phpversion(),
    'Content-Type: text/plain; charset=UTF-8'
];

// Send email
$mailSent = mail($to, $subject, $emailBody, implode("\r\n", $headers));

// Log for debugging
error_log("Contact form submission - To: $to, Subject: $subject, Mail sent: " . ($mailSent ? 'Yes' : 'No'));

if ($mailSent) {
    // Send confirmation email to user
    $userSubject = 'Thank you for your ' . $projectType . ' inquiry - AppInnovative';
    $userBody = "
Dear {$fullName},

Thank you for contacting AppInnovative! We have received your inquiry and our team will get back to you within 24 hours.

Your submission details:
- Project Type: {$projectType}
- Message: {$message}

If you have any urgent questions, please feel free to call us at +1 (647) 679-1568.

Best regards,
The AppInnovative Team
";

    $userHeaders = [
        'From: info@appinnovative.com',
        'X-Mailer: PHP/' . phpversion(),
        'Content-Type: text/plain; charset=UTF-8'
    ];

    mail($email, $userSubject, $userBody, implode("\r\n", $userHeaders));

    // Return success response
    echo json_encode([
        'success' => true, 
        'message' => 'Thank you! Your submission has been received!'
    ]);
} else {
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Oops! Something went wrong while submitting the form. Please try again or contact us directly.'
    ]);
}
?>
