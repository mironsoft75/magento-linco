<?php

namespace Lyracons\Bancard\Block;

class Info extends \Magento\Payment\Block\Info
{
    /**
     * Payment config model
     *
     * @var \Magento\Payment\Model\Config
     */
    protected $_paymentConfig;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Payment\Model\Config $paymentConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Model\Config $paymentConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_paymentConfig = $paymentConfig;
    }

    private $_details_to_publish = [
        'amount' => 'Importe abonado',
        'currency' => 'Moneda',
        // 'ticket_number' => 'Cupón',
		'ticket_number' => 'Nro de Ticket',
        'response_code' => 'Código de respuesta',
        'response_description' => 'Respuesta',
        'extended_response_description' => 'Motivo',
        // 'authorization_number' => 'Código de Autorización'
    ];

    /**
     * Prepare credit card related payment info
     *
     * @param \Magento\Framework\DataObject|array $transport
     * @return \Magento\Framework\DataObject
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }
        $transport = parent::_prepareSpecificInformation($transport);

        $data = $this->getPaymentDetails();

        return $transport->setData(array_merge($data, $transport->getData()));
    }

    public function getPaymentDetails()
    {
        $retval = [];

        $info = $this->getInfo();

        if (!$info->getAdditionalInformation()) {
            return $retval;
        }

        foreach ($this->_details_to_publish as $key => $label) {
            $value = $info->getAdditionalInformation($key);
            if (!empty($value)) {
                $retval[$label] = $value;
            }
        }

        return $retval;
    }
}
