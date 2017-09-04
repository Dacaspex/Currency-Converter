<?php
/**
 * Back end core, responsible for handling requests and returning results
 */

// TODO Create auto import file
require_once 'webservices/WebService.php';
require_once 'webservices/CheapCurrencyConverter.php';
require_once 'webservices/CurrencyConverter.php';

$requestType = $_POST['requestType'];
$response = [];
$webService = null;

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
