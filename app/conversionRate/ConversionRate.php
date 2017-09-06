<?php

class ConversionRate
{
    private $conversionRateHistory;

    function __construct()
    {
        $this->conversionRateHistory = [];
    }

    public function setConversionRate(DateTime $date, float $conversionRate)
    {
        $this->conversionRateHistory[$date->format('d-m')] = $conversionRate;
    }

    public function getConversionRate(): array
    {
        return [
            'labels' => array_keys($this->conversionRateHistory),
            'data' => array_values($this->conversionRateHistory)
        ];
    }
}
