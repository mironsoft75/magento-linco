<?php
namespace MegaInfo\CspModule\Plugin;

use Magento\Framework\View\Page\Config;
use Magento\Csp\Helper\CspNonceProvider;

class AddNonceToScripts
{
    protected $cspNonceProvider;

    public function __construct(CspNonceProvider $cspNonceProvider)
    {
        $this->cspNonceProvider = $cspNonceProvider;
    }

    public function beforeAddRemotePageAsset(Config $subject, $url, $type = null, $params = [], $name = null)
    {
        $nonce = $this->cspNonceProvider->generateNonce();
        $params['attributes']['nonce'] = $nonce;

        return [$url, $type, $params, $name];
    }
}

