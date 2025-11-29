jQuery(document).ready(function ($) {
    // Tab Switching
    $('.nav-tab-wrapper a').on('click', function (e) {
        e.preventDefault();
        var target = $(this).attr('href');

        $('.nav-tab-wrapper a').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');

        $('.wfm-tab-content').hide();
        $(target).show();

        // Store active tab in hidden field or localStorage if needed, but for now simple switching
    });

    // Eye Icon Toggle
    $('.wfm-eye-icon').on('click', function () {
        var input = $(this).prev('input');
        var icon = $(this).find('span');

        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('dashicons-visibility').addClass('dashicons-hidden');
        } else {
            input.attr('type', 'password');
            icon.removeClass('dashicons-hidden').addClass('dashicons-visibility');
        }
    });

    // Initialize: Show first tab
    $('.nav-tab-wrapper a:first').trigger('click');
});
