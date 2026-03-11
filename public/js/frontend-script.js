/**
 * Advanced Link Checker - Frontend Script
 * Adds tooltips and visual indicators for broken links.
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        // Add tooltip to broken links
        $('.alc-broken-link').each(function() {
            $(this).attr('title', 'This link may be broken');
        });

        // Optionally add click warning for broken links
        $('.alc-broken-link').on('click', function(e) {
            var proceed = confirm('This link has been detected as broken. Do you still want to visit it?');
            if (!proceed) {
                e.preventDefault();
            }
        });
    });

})(jQuery);
