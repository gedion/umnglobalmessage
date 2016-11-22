define(['jquery'], function($) { return {
    settings : function() {
        var mySerialize = function(formid) {
                var data = {},
                    name = '',
                    value = '';
                $('#' + formid + ' input[name]:not([disabled]):not([type="checkbox"]),' +
                  '#' + formid + ' select:not([disabled]),' +
                  '#' + formid + ' textarea').each(function() {
                    name = $(this).attr('name');
                    value = $(this).val();
                    data[name] = value;
                });
                $('#' + formid + ' input[type="checkbox"]').each(function() {
                    name = $(this).attr('name');
                    value = $(this).prop('checked') ? '1' : '0';
                    data[name] = value;
                });
                name = 'message[text]';
                value = $('#' + formid + ' div.editor_atto_content').html();
                data[name] = value;
                return data;
            },
            search = '',
            reverse = false,
            sort = '',
            ioRun = function(e, method, action, id, formid) {
                e.preventDefault();
                e.stopPropagation();
                var uri = M.cfg.wwwroot + '/local/umnglobalmessage/ajax_local_umnglobalmessage.php',
                    data = {
                            'action': action,
                            'id': id,
                            'sort': sort,
                            'reverse': reverse,
                            'search': search,
                            'ajax': true
                        },
                    start = function() {
                            $('#local_umnglobalmessage_dialogue .content, #local_umnglobalmessage_table').css('opacity', '0.6');
                        },
                    complete = function(o) {
                        $('#local_umnglobalmessage_dialogue .content, #local_umnglobalmessage_table').css('opacity', '1');
                        var response = o,
                            form = $.parseHTML(response.html);
                        $('#local_umnglobalmessage_settings div#local_umnglobalmessage_table').html(form);
                        if ($('span.error').length > 0) {
                            $('span.error').focus();
                        } else if ($('div.local_umnglobalmessage_error').length > 0) {
                            $('div.local_umnglobalmessage_error').focus();
                        } else {
                            $('input.local_umnglobalmessage_search').focus();
                        }
                    };
                if (method === 'POST') {
                    $.extend(data, mySerialize(formid));
                }
                $.ajax({
                    url: uri,
                    data: data,
                    method: method,
                    beforeSend: start,
                    success: complete,
                    dataType: 'json'
                });
            };

        $('body').delegate('.local_umnglobalmessage_button', 'click', function(e) {
            // Button controls
            var id = $(this).attr('data-id'),
                action = $(this).attr('data-action');
            ioRun(e, 'GET', action, id, null);
        });

        $('body').delegate('table.generaltable tr th a', 'click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var action = 'sort';
            sort = $(this).attr('id');
            reverse = false;
            if ($(this).find('.uparrow').length > 0) {
                reverse = true;
            }
            ioRun(e, 'GET', action, null, null);
        });

        $('body').delegate('.bool-slider .inset', 'click', function(e) {
            var id = $(this).attr('data-id');
            if (!$(this).parent().hasClass('disabled')) {
                if ($(this).parent().hasClass('true')) {
                    $(this).parent().addClass('false').removeClass('true');
                    ioRun(e, 'GET', 'disable', id, null);
                } else {
                    $(this).parent().addClass('true').removeClass('false');
                    window.location.href = '/local/umnglobalmessage/ajax_local_umnglobalmessage.php?action=enable&id='+id;
                }
            }
        });

        $('body').delegate('#local_umnglobalmessage_settings input.local_umnglobalmessage_search', 'input', function(e) {
            search = $(this).val();
            ioRun(e, 'GET', 'search', null, null);
        });

        $('body').delegate('#local_umnglobalmessage_settings #local_umnglobalmessage_table tbody td.c0',
                           'mouseenter mouseleave', function() {
            var description = $(this).find('.local_umnglobalmessage_description');
            description.toggleClass('hidden');
            if ((window.innerHeight + $(window).scrollTop()) < (description.offset().top + description.height())) {
                $(this).prepend(description);
                description.css('margin-top', (0 - description.height()));
            } else {
                $(this).append(description);
                description.css('margin-top', 0);
            }
        });
    },

    message : function() {
        var umngm = $('.umngm'),
            maxZindex = 1;
        if (umngm.length > 0) {
            var umngm_height = umngm.outerHeight() || 0;
            if (umngm.length > 1 || !umngm.hasClass('popup')) {
                $('header .dropdown-panel').each(function() {
                    var current = parseInt($(this).css('top'), 10),
                        next = current + umngm_height;
                    $(this).css('top', next);
                });
            }
            if (umngm.hasClass('popup')) {
                $('.moodle-has-zindex').each(function() {
                    var zindex = $($(this)).css('zIndex') || $($(this)).parent().css('zIndex');
                    if (zindex && parseInt(zindex, 10) > maxZindex) {
                        maxZindex = parseInt(zindex, 10);
                    }
                });
                $('.umngm.popup').css('zIndex', maxZindex);
            }
            $('body').delegate('.umngm .umngm_close', 'click', function() {
                var uri = M.cfg.wwwroot + '/local/umnglobalmessage/ajax_local_umnglobalmessage.php',
                    method = 'POST',
                    data = {
                            'action': $(this).attr('data-action'),
                            'id': $(this).attr('data-id')
                        };
                $.ajax({
                    url: uri,
                    data: data,
                    method: method,
                    dataType: 'json'
                });
                $(this).closest('.umngm').animate({
                    marginTop: 0 - umngm_height,
                }, 0.1, function() {
                    $(this).closest('.umngm').remove();
                    if (!$(this).closest('.umngm').hasClass('popup')) {
                        $('header .dropdown-panel').each(function() {
                            $(this).css('top', parseInt($(this).css('top'), 10) - umngm_height);
                        });
                    }
                });
            });
        }
    },

    form : function() {
        var target = $('select[name="target"]').val();
        if (target === 'other') {
            $('input[name="othertarget"]').removeAttr('disabled');
            $('select[name="category"]').attr('disabled', 'disabled');
        } else if (target === 'category') {
            $('input[name="othertarget"]').attr('disabled', 'disabled');
            $('select[name="category"]').removeAttr('disabled');
        } else {
            $('input[name="othertarget"]').attr('disabled', 'disabled');
            $('select[name="category"]').attr('disabled', 'disabled');
        }
    },
};});
