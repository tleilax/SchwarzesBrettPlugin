/*jslint browser: true, unparam: true */
/*global jQuery, STUDIP */
(function ($, STUDIP) {
    'use strict';

    var messages = [];
    $(document).on('dialog-load', function (event, data) {
        var href = $(data.options.origin).attr('href'),
            msgs = data.xhr.getResponseHeader('X-Messages');
        if (href && href.indexOf('category/visit') !== -1) {
            $('.sb-category,.sb-articles').find('.unseen').toggleClass('unseen seen');
            $('.sb-categories .unseen').toggleClass('unseen seen');

            $(data.options.origin).hide();
        }

        if (msgs) {
            messages.push(JSON.parse(msgs));
        }
    }).on('dialog-close', function () {
        if (messages.length > 0) {
            $('#layout_content').prepend(messages.join(''));
            messages = [];
        }
    }).on('click', 'a.article.unseen', function () {
        $(this).toggleClass('unseen seen');
    }).on('change', 'select.has-disclaimer', function () {
        var id = $(this).val();
        $(this).closest('form').find('.category-disclaimer').hide().filter('#disclaimer-' + id).show();
    });

    // OpenGraph && toolbar
    $(document).on('dialog-open dialog-update ready', function () {
        $('.add_toolbar').addToolbar();

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
    });

    $(document).on('click', '.opengraph-area .switcher button', function () {
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

    STUDIP.Dialog.handlers.header['X-Article-Watched'] = function (id) {
        $('[data-article-id="' + id + '"]').addClass('watched');
    };

    STUDIP.Dialog.handlers.header['X-Article-Unwatched'] = function (id) {
        var i, l;

        if (!$.isArray(id)) {
            id = [id];
        }

        for (i = 0, l = id.length; i < l; i += 1) {
            $('[data-article-id="' + id[i] + '"]').removeClass('watched');
        }
    };

}(jQuery, STUDIP));