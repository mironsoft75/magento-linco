<?php


namespace WeSupply\Toolbox\Api;

interface GiftcardInterface{

    public function initData();

    public function createAndDeliverGiftCard($giftCardAmount, $customerEmail, $customerName, $websiteId = 1);

    public function getGeneratedCode();
}