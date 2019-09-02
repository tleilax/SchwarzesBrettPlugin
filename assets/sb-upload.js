/*jslint browser: true, esversion: 6 */
/*global jQuery, STUDIP */
(function ($, STUDIP) {
    'use strict';

    function Queue(options) {
        this.options = $.extend({
            limit: 3
        }, options || {});

        this.queue = [];
        this.avail = this.options.limit;
    }
    Queue.prototype.add = function (target, file, row, index) {
        let formdata = new FormData();
        formdata.append('image', file, file.name);

        this.queue.push({
            target: target,
            index: index,
            data: formdata,
            name: file.name,
            type: file.type,
            row: row
        });

        if (!this.processing) {
            this.process();
        }
    };
    Queue.prototype.process = function () {
        if (this.queue.length === 0) {
            this.processing = false;
            return;
        }

        // Limit of parallel executions reached
        if (this.avail === 0) {
            return;
        }

        this.processing = true;
        this.avail -= 1;

        let item = this.queue.shift(),
            progress = $('progress', item.row);

        item.row.removeClass('queued').addClass('uploading');

        $.ajax(item.target, {
            method: 'POST',
            data: item.data,
            contentType: false,
            cache: false,
            processData: false,
            xhr: function () {
                let xhr = $.ajaxSettings.xhr();
                if (xhr.upload) {
                    xhr.upload.onprogress = function (e) {
                        if (e.lengthComputable) {
                            progress.val(100 * e.loaded / e.total);
                        }
                    };
                }
                return xhr;
            }
        }).done(function (response) {
            item.row.empty().append([
                '<td><input type="hidden" name="new[' + response.id + '][position]"></td>',
                '<td><input type="text" name="new[' + response.id + '][title]"></td>',
                $('<td>').append([
                    '<input type="checkbox" class="studip-checkbox" id="image-' + response.id + '" name="new[' + response.id + '][delete]" value="1" tabindex="-1">',
                    '<label for="image-' + response.id + '">'
                ])
            ]).addClass('add uploaded');

            this.src = STUDIP.ASSETS_URL + '/images/ajax-indicator-black.svg';

            let img = $('<img>').attr('src', STUDIP.ASSETS_URL + '/images/ajax-indicator-black.svg'),
                lzy = new Image();
            lzy.onload = () => img.attr('src', response.url);
            lzy.src = response.url;

            $('td:first', item.row).append(img);
        }).fail(function () {
            item.row.addClass('upload-error').find('td').text(
                'Datei %s konnte nicht hochgeladen werden'.toLocaleString().replace('%s', item.name)
            );

            setTimeout(function () {
                item.row.hide('fade', 'slow', function () {
                    $(this).remove();
                });
            }, 3000);
        }).always(() => {
            item.row.removeClass('uploading');
            item.row.closest('.ui-sortable').sortable('refresh');

            this.avail += 1;

            this.process();
        });

        if (this.avail > 0) {
            this.process();
        }

    };

    var queue = new Queue();

    function fileUploadEnhance(event) {
        $('form .sb-file-upload', event ? '.ui-dialog' : document).each(function () {
            $('input[type="file"]', this).change((event) => {
                var target = $(event.target).data().targetUrl,
                    files = event.target.files;

                // Prepare form data and filter non images
                for (let i = 0; i < files.length; i += 1) {
                    if (!files[i].type.match('image.*')) {
                        console.log('no image');
                        continue;
                    }

                    let row = $('<tr class="queued">');
                    $('<td colspan="3">')
                        .text(files[i].name)
                        .prepend('<progress class="upload-progress" max="100" value="0">')
                        .appendTo(row);

                    $(this).closest('form').find('.sb-article-images-edit tbody').append(row);

                    queue.add(target, files[i], row, i);
                }

                $(event.target).val('');
            }).on('dragover dragenter', (event) => {
                $(event.target).closest('label').addClass('drag-over');
            }).on('dragleave dragend drop', (event) => {
                $(event.target).closest('label').removeClass('drag-over');
            }).on('drop', function (event) {
                event.stopPropagation();
            });
        });
    }

    $(document).ready(function () {
        var div = document.createElement('div'),
            allowed = false;
        allowed = ('draggable' in div) || ('ondragstart' in div && 'ondrop' in div);
        allowed = allowed && 'FormData' in window;
        allowed = allowed && 'FileReader' in window;

        $('html').toggleClass('allows-file-upload', allowed);

        if (!allowed) {
            return;
        }

        fileUploadEnhance();
    }).on('dialog-update', fileUploadEnhance);

}(jQuery, STUDIP));

// reader.onload = (e) => {
//     $('<img>').attr('src', e.target.result).appendTo($('td:first', row));
//
//     if (i === 0) {
//         setTimeout(function () {
//             $('input[type="text"]', row).focus();
//         }, 0);
//     }
//
//     let formdata = new FormData();
// };
// reader.readAsDataURL(event.target.files[i]);
