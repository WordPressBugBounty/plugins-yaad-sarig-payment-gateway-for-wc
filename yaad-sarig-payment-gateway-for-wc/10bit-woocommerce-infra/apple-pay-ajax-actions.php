<?php
header('Content-Type: application/json');

require_once dirname(__FILE__, 5) . '/wp-load.php';

if (!is_user_logged_in() || !current_user_can('manage_woocommerce')) {
    http_response_code(403);
    exit;
}

$action = isset($_POST['action'])
    ? sanitize_key(wp_unslash($_POST['action']))
    : '';

switch($action) {
    case 'copy':
        mkdir(getenv("DOCUMENT_ROOT") . "/.well-known", 0755, true);
        $source = "https://icom.yaad.net/.well-known/apple-developer-merchantid-domain-association";
        $destination = getenv("DOCUMENT_ROOT") . "/.well-known/apple-developer-merchantid-domain-association";
        copy($source, $destination);
        break;
    case 'update':
        $source = "https://icom.yaad.net/.well-known/apple-developer-merchantid-domain-association";
        $destination = getenv("DOCUMENT_ROOT") . "/.well-known/apple-developer-merchantid-domain-association";
        copy($source, $destination);
        break;
    case 'delete':
        $destination = getenv("DOCUMENT_ROOT") . "/.well-known/apple-developer-merchantid-domain-association";
        unlink($destination);
        break;
    default:
        break;
}