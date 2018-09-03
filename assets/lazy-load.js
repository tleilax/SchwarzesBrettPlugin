/*jslint esversion: 6, browser: true */
/*global jQuery */
(function ($) {
    'use strict';

    // Lazy load
    function lazyLoad() {
        $('img.lazy:not(.loaded)').each(function () {
            var src = this.src;
            var img = new Image();
            var placeholder = null;
            var width = $(this).attr('width');
            var height = $(this).attr('height');

            if (width && height) {
                placeholder = $('<div>').css({
                    position: 'relative',
                    display: $(this).css('display'),
                    margin: 'auto',
                    backgroundColor: 'rgba(0, 0, 0, 0.05)',
                    backgroundImage: 'url(' + STUDIP.ASSETS_URL + '/images/ajax-indicator-black.svg' + ')',
                    backgroundSize: 'contain 80%',
                    backgroundRepeat: 'no-repeat',
                    backgroundPosition: 'center'
                }).width(width).height(height);
                $(this).after(placeholder).hide();
            } else {
                this.src = STUDIP.ASSETS_URL + '/images/ajax-indicator-black.svg';
            }

            img.onload = img.onerror = (event) => {
                if (placeholder !== null) {
                    placeholder.remove();
                }

                $(this).addClass('loaded')
                       .toggleClass('has-error', event.type !== 'load')
                       .attr('src', src)
                       .show('fade');
            };
            img.src = src;
        });
    }
    $(document).ready(lazyLoad).on('dialog-update', lazyLoad);

}(jQuery));
