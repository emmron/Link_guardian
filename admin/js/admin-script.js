/**
 * Advanced Link Checker - Admin Script
 * Handles AJAX operations for the admin interface.
 */
(function($) {
    'use strict';

    $(document).ready(function() {

        // Recheck single link
        $('.alc-recheck-link').on('click', function(e) {
            e.preventDefault();
            var $button = $(this);
            var linkId = $button.data('link-id');

            $button.prop('disabled', true).text(alc_admin.rechecking_text || 'Rechecking...');

            $.ajax({
                url: alc_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'alc_recheck_link',
                    link_id: linkId,
                    security: alc_admin.recheck_nonce
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.data || 'Failed to recheck link.');
                        $button.prop('disabled', false).text('Recheck');
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                    $button.prop('disabled', false).text('Recheck');
                }
            });
        });

        // Resolve single link
        $('.alc-resolve-link').on('click', function(e) {
            e.preventDefault();
            var $button = $(this);
            var linkId = $button.data('link-id');

            if (!confirm('Are you sure you want to mark this link as resolved?')) {
                return;
            }

            $button.prop('disabled', true);

            $.ajax({
                url: alc_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'alc_resolve_link',
                    link_id: linkId,
                    security: alc_admin.resolve_nonce
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.data || 'Failed to resolve link.');
                        $button.prop('disabled', false);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                    $button.prop('disabled', false);
                }
            });
        });

        // Recheck all links
        $('#alc-recheck-all').on('click', function(e) {
            e.preventDefault();
            var $button = $(this);

            if (!confirm('This will recheck all broken links. Continue?')) {
                return;
            }

            $button.prop('disabled', true).text('Rechecking all...');

            $.ajax({
                url: alc_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'alc_recheck_all_links',
                    security: alc_admin.recheck_nonce
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.data || 'Failed to recheck links.');
                        $button.prop('disabled', false).text('Recheck All Links');
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                    $button.prop('disabled', false).text('Recheck All Links');
                }
            });
        });

        // Export CSV
        $('#alc-export-csv').on('click', function(e) {
            e.preventDefault();
            window.location.href = alc_admin.ajax_url + '?action=alc_export_csv&security=' + alc_admin.export_nonce;
        });
    });

})(jQuery);
