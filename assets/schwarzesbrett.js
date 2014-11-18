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
}(jQuery));