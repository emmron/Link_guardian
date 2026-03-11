/**
 * Advanced Link Checker - Front End Scripts (alias)
 * This file is loaded by front-end-highlighting.php
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        $('.alc-broken-link').each(function() {
            $(this).attr('title', 'This link may be broken');
        });
    });

})(jQuery);
