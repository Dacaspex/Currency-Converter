<?php
/**
 * Back end core, responsible for handling requests and returning results
 */

// TODO Create auto import file
require_once 'webservices/WebService.php';
require_once 'webservices/CheapCurrencyConverter.php';
require_once 'webservices/CurrencyConverter.php';
require_once 'auth/Auth.php';

// Only logged in users can access the webservice
if (!Auth::check()) {
    Auth::redirect(Auth::HOME_URL);
}

$requestType = $_POST['requestType'];
$response = [];
$webService = null;

// Failsafe for when no request type is set
if (!isset($_POST['requestType'])) {
    $_POST['requestType'] = 'error';
}

// Depending on the request type, invoke correct webservice
switch ($requestType) {
    case 'graph':
        $webService = new CheapCurrencyConverter();
        $response = $webService->getGraphData($_POST['requestData']);
        break;

    case 'converter':
        $webService = new CurrencyConverter();
        $response = $webService->getResult($_POST['requestData']);
        break;

    default:
        $response['error'] = 'Invalid request type';
        break;
}

// Check if errors are present, else return result
if (isset($response['error'])) {
    header('HTTP/1.1 500 Something went wrong');
    echo $response['error'];
} else {
    echo json_encode($response['result']);
}

?>
