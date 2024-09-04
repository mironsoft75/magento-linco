<?php
namespace MegaInfo\CspModule\Helper;

use Magento\Csp\Helper\CspNonceProvider;

class CspNonce
{
    /**
     * @var CspNonceProvider
     */
    private $cspNonceProvider;

    /**
     * Constructor
     *
     * @param CspNonceProvider $cspNonceProvider
     */
    public function __construct(CspNonceProvider $cspNonceProvider)
    {
        $this->cspNonceProvider = $cspNonceProvider;
    }

    /**
     * Obtener el Nonce de CSP
     *
     * @return string
     */
    public function getNonce(): string
    {
        return $this->cspNonceProvider->generateNonce();
    }
}

