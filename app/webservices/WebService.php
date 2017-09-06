<?php

/**
 * Webservice interface for all webservices.
 */
interface WebService
{
    /**
     * This method gets the result from the webservice and returns an array as
     * data. If the array contains the key 'error', the webservice has failed.
     * Otherwise, the array contains the key 'result' where the result should be
     * stored in.
     * @param  $data Array Array of data
     * @return Array The result of the webservice
     */
    public function getResult($data): ConversionRate;
}

?>
