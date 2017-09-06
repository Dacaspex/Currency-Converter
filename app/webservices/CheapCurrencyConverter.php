<?php

require_once __DIR__ . '/../conversionRate/ConversionRate.php';

/**
 * Webservice that retrieves the current exchange rate of the requested
 * currencies. This web service is cheaper, but less reliable. Therefore this
 * webservice is used to retrieve all data for the graph.
 */
class CheapCurrencyConverter implements WebService
{
    /** Maximum number of days that the graph should display */
    private $MAX_HISTORY_DAYS = 30;

    public function getGraphData($requestData)
    {
        // Check if currencies are the same
        if ($requestData['currencyFrom'] == $requestData['currencyTo']) {
            return 1;
        }

        // Get conversion rate
        $result = $this->getConversionRateFromWebservice($requestData);

        // Check for errors
        if (!$result || (count($result['rates']) >= 1)) {
            $conversionRate = $result['rates'][$requestData['currencyTo']];
            return $conversionRate;
        } else {
            // An error occured and the conversion rate could not be retrieved
            return false;
        }
    }

    public function getResult($requestData): ConversionRate
    {
        $date = new DateTime($requestData['date']);
        $date->modify('-' . $this->MAX_HISTORY_DAYS . ' days');

        $conversionRate = new ConversionRate();

        for ($i = 0; $i < $this->MAX_HISTORY_DAYS; $i++) {
            // Modify the date, 1 day earlier
            $date->modify('+1 day');
            $requestData['date'] = $date->format('Y-m-d');

            // Get the conversion rate
            $conversionResult = $this->getGraphData($requestData);

            $conversionRate->setConversionRate($date, $conversionResult);

            // If result failed, throw error
            if (!$conversionResult) {
                throw new Exception("Conversion rate could not be retrieved or too many requests");
            }
        }

        return $conversionRate;
    }

    public function getConversionRateFromWebservice($requestData)
    {
        try {
            $response = @file_get_contents(
                "http://api.fixer.io/"
                . $requestData['date']
                . "?base="
                . $requestData['currencyFrom']
                . "&symbols="
                . $requestData['currencyTo']
            );
        } catch (Exception $exception) {
            return false;
        }

        $stdClass = json_decode($response);
        $result = json_decode(json_encode($stdClass), true);
        return $result;
    }
}

?>
