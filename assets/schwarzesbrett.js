/*jslint esversion: 6, browser: true, unparam: true */
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
    }).on('click', '.article.unseen', function () {
        $(this).toggleClass('unseen seen');
    }).on('change', 'select.has-disclaimer', function () {
        var id = $(this).val();
        $(this).closest('form').find('.category-disclaimer').hide().filter('#disclaimer-' + id).show();
    });

    // OpenGraph && toolbar
    $(document).on('dialog-open dialog-update ready', function () {
        $('.add_toolbar').addToolbar();
    });

    function reloadWatchlist() {
        if (location.href.match('schwarzesbrettplugin/watchlist') === null) {
            return;
        }

        $.get(STUDIP.URLHelper.getURL('plugins.php/schwarzesbrettplugin/watchlist')).then(function (response) {
            $('#watchlist', response).replaceAll('#watchlist');
        });
    }

    STUDIP.Dialog.handlers.header['X-Article-Watched'] = function (id) {
        $('[data-article-id="' + id + '"]').addClass('watched');
        reloadWatchlist();
    };

    STUDIP.Dialog.handlers.header['X-Article-Unwatched'] = function (id) {
        var i, l;

        if (!$.isArray(id)) {
            id = [id];
        }

        for (i = 0, l = id.length; i < l; i += 1) {
            $('[data-article-id="' + id[i] + '"]').removeClass('watched');
        }

        reloadWatchlist();
    };

    // Article edit - images sortable
    function sortableImages() {
        $('.sb-article-images-edit:not(.ui-sortable)').each(function () {
            $(this).sortable({
                axis: 'y',
                containment: this,
                helper: function (event, element) {
                    var helper = $(element).clone().addClass('sb-sortable-helper');
                    $('td', element).each(function (index) {
                        $('td:eq(' + index + ')', helper).width($(this).width());
                    });
                    return helper;
                },
                items: 'tbody > tr',
                cancel: 'input,.queued,.uploading,.upload-error,.empty-placeholder',
                placeholder: 'sb-sortable-placeholder',
                tolerance: 'pointer',

                start: function (event, ui) {
                    $('.sb-sortable-placeholder', this).height(
                        $(ui.item).height()
                    );
                },
                update: function (event, ui) {
                    var position = 0;
                    $(this).find('tbody > tr').each(function () {
                        $(this).find('input[name*="position"]').val(position);

                        position += 1;
                    });
                }
            });
        });
    }
    STUDIP.ready(sortableImages);

    $(document).on('change', '.sb-article-images-edit :checkbox[name*="delete"]', function () {
        $(this).closest('tr').toggleClass('remove', this.checked);
    });

}(jQuery, STUDIP));
