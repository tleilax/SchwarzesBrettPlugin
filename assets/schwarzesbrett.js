(function ($) {
    var messages = [];
    $(document).on('dialog-load', function (event, data) {
        var href = $(data.options.origin).attr('href');
        if (href && href.indexOf('category/visit') !== -1) {
            $('.sb-category,.sb-articles').find('.new-article').removeClass('new-article');
            $('.sb-categories .new-category').removeClass('new-category');

            $(data.options.origin).hide();
        }

        var msgs = data.xhr.getResponseHeader('X-Messages');
        if (msgs) {
            messages.push(JSON.parse(msgs));
        }
    }).on('dialog-close', function () {
        if (messages.length > 0) {
            $('#layout_content').prepend(messages.join(''));
            messages = [];
        }
    }).on('click submit', '[data-confirm]', function (event) {
        var message = $(this).data().confirm || $(this).attr('title') || $(this).text();
        if (!confirm(message)) {
            event.preventDefault();
        }
    }).on('click', '.new-article a[href*="article/view/"]', function () {
        $(this).closest('.new-article').removeClass('new-article');
    });

    // OpenGraph
    $(document).on('dialog-open ready', function () {
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
}(jQuery));