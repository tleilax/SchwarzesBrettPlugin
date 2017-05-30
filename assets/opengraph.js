/*jslint browser: true */
/*global jQuery, STUDIP */
(function ($, STUDIP) {
    'use strict';

    $(document).on('dialog-open dialog-update ready', function () {
        $('.opengraph-area:not(.handled)').each(function () {
            var items = $('.opengraph', this),
                switcher;
            if (items.length > 1) {
                switcher = $('<ul class="switcher">');
                $('<li><button class="switch-left" disabled>&lt;</button></li>').appendTo(switcher);
                $('<li><button class="switch-right">&gt;</button></li>').appendTo(switcher);
                switcher.prependTo(this);
            }

            $(this).addClass('handled');
        });
    }).on('click', '.opengraph-area .switcher button', function () {
        var direction = $(this).is('.switch-left') ? 'left' : 'right',
            current   = $(this).closest('.opengraph-area').find('.opengraph:visible'),
            switcher  = $(this).closest('.switcher'),
            buttons   = {left: $('.switch-left', switcher),
                         right: $('.switch-right', switcher)};

        if (direction === 'left') {
            current = current.hide().prev().show();
            buttons.left.attr('disabled', current.prev('.opengraph').length === 0);
            buttons.right.attr('disabled', false);
        } else {
            current = current.hide().next().show();
            buttons.left.attr('disabled', false);
            buttons.right.attr('disabled', current.next('.opengraph').length === 0);
        }
    });

}(jQuery, STUDIP));
