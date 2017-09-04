<?php

/**
 * Webservice that retrieves the current exchange rate of the requested
 * currencies. This web service is more expensive, but also more relaible.
 * Therefore, this webservice is used when the user wants to accurately know
 * the exchange rate. 
 */
class CurrencyConverter implements WebService
{
    public function getResult($requestData)
    {
        // Setup WSDL info
        $wsdl = "http://currencyconverter.kowabunga.net/converter.asmx?WSDL";
        $trace = true;
        $exceptions = true;

        // Convert data to requreid format
        $xml_array['CurrencyFrom'] = $requestData['currencyFrom'];
        $xml_array['CurrencyTo'] = $requestData['currencyTo'];
        $xml_array['RateDate'] = $requestData['date'];

        $client = new SoapClient($wsdl, array(
            'trace' => $trace,
            'exceptions' => $exceptions
        ));

        $clientResponse = $client->GetConversionRate($xml_array);
        $conversionRate = (float) $clientResponse->GetConversionRateResult;

        if ($conversionRate == 0) {
            return [
                'error' => 'Conversion rate could not be retrieved'
            ];
        }

        return [
            'result' => [
                'conversionRate' => round($conversionRate, 3, PHP_ROUND_HALF_UP)
            ]
        ];
    }
}

?>
