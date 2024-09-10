<?php

namespace Manadev\ElasticSearch;

use Magento\Store\Model\StoreManagerInterface;

class CurrencyHelper
{
    protected $currencyRate;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(StoreManagerInterface $storeManager) {
        $this->storeManager = $storeManager;
    }

    public function getCurrencyRate()
    {
        if ($this->currencyRate === null) {
            $this->currencyRate = $this->storeManager->getStore()->getCurrentCurrencyRate();
            if (!$this->currencyRate) {
                $this->currencyRate = 1.0;
            }
        }

        return $this->currencyRate;
    }

    public function convertStats($stats) {
        if ($this->getCurrencyRate() == 1.0) {
            return $stats;
        }

        $stats['min'] = $this->convertFromBaseToDisplay($stats['min']);
        $stats['max'] = $this->convertFromBaseToDisplay($stats['max']);
        $stats['avg'] = $this->convertFromBaseToDisplay($stats['avg']);

        return $stats;
    }

    public function convertFromBaseToDisplay($value) {
        return round($value * $this->getCurrencyRate(), 4);
    }

    public function convertFromDisplayToBase($value) {
        return round($value / $this->getCurrencyRate(), 4);
    }

    public function convertCounts($counts) {
        if ($this->getCurrencyRate() == 1.0) {
            return $counts;
        }

        $result = [];

        foreach ($counts as $value => $count) {
            $result[(string)($this->convertFromBaseToDisplay(floatval($value)))] =
                $count;
        }

        return $result;
    }

    public function convertAppliedRanges($ranges) {
        if ($this->getCurrencyRate() == 1.0) {
            return $ranges;
        }

        return array_map(function($range) {
            $range[0] = (string)($this->convertFromDisplayToBase(floatval(
                $range[0])));
            $range[1] = (string)($this->convertFromDisplayToBase(floatval(
                $range[1])));

            return $range;
        }, $ranges);
    }
}
