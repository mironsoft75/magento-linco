<?php
namespace Lyracons\Notifications\Helper;

class NotificationsHelper extends \Magento\Framework\App\Helper\AbstractHelper {
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
    }

    public function getConfigValues()
    {
        $config = array(
            'success_border_color'       => $this->scopeConfig->getValue("notifications_configuration/success_messages/success_border_color", \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE),
            'success_background_color'   => $this->scopeConfig->getValue("notifications_configuration/success_messages/success_background_color", \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'success_text_color'         => $this->scopeConfig->getValue("notifications_configuration/success_messages/success_text_color", \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'success_notification_delay' => $this->scopeConfig->getValue("notifications_configuration/success_messages/success_notification_delay", \Magento\Store\Model\ScopeInterface::SCOPE_STORE),

            'warning_border_color'       => $this->scopeConfig->getValue("notifications_configuration/warning_messages/warning_border_color", \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE),
            'warning_background_color'   => $this->scopeConfig->getValue("notifications_configuration/warning_messages/warning_background_color", \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'warning_text_color'         => $this->scopeConfig->getValue("notifications_configuration/warning_messages/warning_text_color", \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'warning_notification_delay' => $this->scopeConfig->getValue("notifications_configuration/warning_messages/warning_notification_delay", \Magento\Store\Model\ScopeInterface::SCOPE_STORE),

            'error_border_color'         => $this->scopeConfig->getValue("notifications_configuration/error_messages/error_border_color", \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE),
            'error_background_color'     => $this->scopeConfig->getValue("notifications_configuration/error_messages/error_background_color", \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'error_text_color'           => $this->scopeConfig->getValue("notifications_configuration/error_messages/error_text_color", \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'error_notification_delay'   => $this->scopeConfig->getValue("notifications_configuration/error_messages/error_notification_delay", \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
        );

        return $config;
    }
}