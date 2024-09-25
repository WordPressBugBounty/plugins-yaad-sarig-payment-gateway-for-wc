<?php
header('Content-Type: application/json');

switch($_POST['action']) {
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