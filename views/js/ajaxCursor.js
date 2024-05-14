$(document).on('ajaxStart', function () {
    $('body').css('cursor', 'wait');
});
$(document).on('ajaxStop', function () {
    $('body').css('cursor', 'default');
});