define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('mage.notificationsConfig', {
        options: {},

        /** @inheritdoc */
        _create: function () {
            var self = this;

            $('body').on('click', '.message', function(){
                $(this).fadeOut('slow');
            });

            // Selecciona el elemento .messages
            const messagesElement = document.querySelector('.messages');

            // Verifica si el elemento existe antes de intentar observar
            if (messagesElement) {
                // ConfiguraciÃ³n de MutationObserver para observar cambios en el DOM
                const observer = new MutationObserver(function(mutationsList) {
                    for (const mutation of mutationsList) {
                        if (mutation.type === 'childList' || mutation.type === 'subtree') {
                            $(messagesElement).find('.message-success').css({
                                'border-color': self.options.successBorderColor,
                                'background-color': self.options.successBackgroundColor,
                                'color': self.options.successTextColor
                            });

                            $(messagesElement).find('.message-notice').css({
                                'border-color': self.options.warningBorderColor,
                                'background-color': self.options.warningBackgroundColor,
                                'color': self.options.warningTextColor
                            });

                            $(messagesElement).find('.message-error').css({
                                'border-color': self.options.errorBorderColor,
                                'background-color': self.options.errorBackgroundColor,
                                'color': self.options.errorTextColor
                            });

                            if(self.options.successNotificationDelay && self.options.successNotificationDelay > 0){
                                $(messagesElement).find('.message-success').delay(self.options.successNotificationDelay).fadeOut('slow');
                            }

                            if(self.options.warningNotificationDelay && self.options.warningNotificationDelay > 0){
                                $(messagesElement).find('.message-notice').delay(self.options.warningNotificationDelay).fadeOut('slow');
                            }

                            if(self.options.errorNotificationDelay && self.options.errorNotificationDelay > 0){
                                $(messagesElement).find('.message-error').delay(self.options.errorNotificationDelay).fadeOut('slow');
                            }
                        }
                    }
                });

                // Opciones para observar cambios en el DOM
                const config = { childList: true, subtree: true };

                // Observa la clase .messages para detectar cambios en su DOM
                observer.observe(messagesElement, config);
            } else {
                console.warn("No se encontrÃ³ el elemento .messages en el DOM.");
            }
        }
    });

    return $.mage.notificationsConfig;
});

