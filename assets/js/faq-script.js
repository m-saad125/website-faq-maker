jQuery(document).ready(function ($) {
    $('.wfm-accordion-item button').on('click', function () {
        var btn = $(this);
        var content = btn.next('.wfm-accordion-content');
        var isExpanded = btn.attr('aria-expanded') === 'true';

        // Close all others
        // $('.wfm-accordion-item button').attr('aria-expanded', 'false');
        // $('.wfm-accordion-content').slideUp().attr('hidden', true);

        // Toggle current
        btn.attr('aria-expanded', !isExpanded);

        if (isExpanded) {
            content.slideUp(200, function () {
                $(this).attr('hidden', true);
            });
        } else {
            content.removeAttr('hidden').slideDown(200);
        }
    });
});
