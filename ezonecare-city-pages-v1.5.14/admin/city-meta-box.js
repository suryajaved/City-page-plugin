/**
 * EzoneCare City Meta Box JS
 * Handles service selection + brand sub-selection
 */
(function($) {
    'use strict';

    // ── State ───────────────────────────────────────────
    var state = {
        // { service_id: [brand_id, brand_id], ... }
        data: {},
        selectedServiceId: null
    };

    // ── Init ────────────────────────────────────────────
    $(document).ready(function() {
        // Load saved data
        try {
            var saved = $('#ez_service_brands_data').val();
            state.data = saved ? JSON.parse(saved) : {};
        } catch(e) {
            state.data = {};
        }

        // Convert keys to string (JSON keys are always strings)
        var normalized = {};
        $.each(state.data, function(k, v) {
            normalized[String(k)] = v;
        });
        state.data = normalized;

        // Re-render active services from saved state
        renderActiveServices();

        // ── Event: Click available service → Add ──────
        $(document).on('click', '#ez-available-services .ez-item:not(.ez-item-used)', function() {
            var id    = String($(this).data('id'));
            var title = $(this).data('title');

            // Add to state with empty brands array
            if (!state.data[id]) {
                state.data[id] = [];
            }

            // Mark as used in left box
            $(this).addClass('ez-item-used');
            $(this).find('.ez-tag').remove();
            $(this).append('<span class="ez-tag">Added</span>');

            // Add to right box
            addActiveServiceItem(id, title);

            // Hide empty message
            $('#ez-services-empty').hide();

            saveData();
        });

        // ── Event: Click active service → Open brand panel ──
        $(document).on('click', '#ez-active-services .ez-item-active .ez-item-title', function() {
            var $item = $(this).closest('.ez-item');
            var id    = String($item.data('id'));
            var title = $item.data('title');

            // Toggle: if already selected, close
            if (state.selectedServiceId === id) {
                state.selectedServiceId = null;
                $('#ez-brands-panel').slideUp(150);
                $item.removeClass('ez-item-selected');
                return;
            }

            // Deselect previous
            $('#ez-active-services .ez-item').removeClass('ez-item-selected');

            // Select this
            $item.addClass('ez-item-selected');
            state.selectedServiceId = id;

            // Show brand panel
            openBrandPanel(id, title);
        });

        // ── Event: Remove active service ──────────────
        $(document).on('click', '#ez-active-services .ez-remove', function(e) {
            e.stopPropagation();
            var $item = $(this).closest('.ez-item');
            var id    = String($item.data('id'));

            // Remove from state
            delete state.data[id];

            // Remove from right box
            $item.remove();

            // Re-enable in left box
            $('#ez-available-services .ez-item[data-id="' + id + '"]')
                .removeClass('ez-item-used')
                .find('.ez-tag').remove();

            // Show empty if needed
            if ($('#ez-active-services .ez-item-active').length === 0) {
                $('#ez-services-empty').show();
            }

            // Close brand panel if this service was selected
            if (state.selectedServiceId === id) {
                state.selectedServiceId = null;
                $('#ez-brands-panel').slideUp(150);
            }

            saveData();
        });

        // ── Event: Click available brand → Add ────────
        $(document).on('click', '#ez-available-brands .ez-item:not(.ez-item-used)', function() {
            var id    = String($(this).data('id'));
            var title = $(this).data('title');
            var sid   = state.selectedServiceId;

            if (!sid) return;

            // Add to state
            if (!state.data[sid]) state.data[sid] = [];
            if (state.data[sid].indexOf(parseInt(id)) === -1) {
                state.data[sid].push(parseInt(id));
            }

            // Mark as used in left brand box
            $(this).addClass('ez-item-used');

            // Add to right brand box
            addActiveBrandItem(id, title);
            $('#ez-brands-empty').hide();

            saveData();
        });

        // ── Event: Remove active brand ─────────────────
        $(document).on('click', '#ez-active-brands .ez-remove', function(e) {
            e.stopPropagation();
            var $item = $(this).closest('.ez-item');
            var id    = parseInt($item.data('id'));
            var sid   = state.selectedServiceId;

            // Remove from state
            if (sid && state.data[sid]) {
                state.data[sid] = state.data[sid].filter(function(b) {
                    return b !== id;
                });
            }

            // Remove from right box
            $item.remove();

            // Re-enable in left brand box
            $('#ez-available-brands .ez-item[data-id="' + id + '"]')
                .removeClass('ez-item-used');

            // Show empty if needed
            if ($('#ez-active-brands .ez-item-active').length === 0) {
                $('#ez-brands-empty').show();
            }

            saveData();
        });
    });

    // ── Render active services from saved state ──────────
    function renderActiveServices() {
        var $active = $('#ez-active-services');

        // Clear existing active items (keep empty message)
        $active.find('.ez-item-active').remove();

        var hasAny = false;

        $.each(state.data, function(sid, brands) {
            sid = String(sid);
            // Find service title from EZ_SERVICES
            var title = getServiceTitle(sid);
            if (!title) return;

            addActiveServiceItem(sid, title);

            // Mark as used in available list
            $('#ez-available-services .ez-item[data-id="' + sid + '"]')
                .addClass('ez-item-used')
                .not(':has(.ez-tag)')
                .append('<span class="ez-tag">Added</span>');

            hasAny = true;
        });

        if (hasAny) {
            $('#ez-services-empty').hide();
        }
    }

    // ── Add item to active services box ─────────────────
    function addActiveServiceItem(id, title) {
        var html = '<div class="ez-item ez-item-active" data-id="' + id + '" data-title="' + escAttr(title) + '">' +
                   '<span class="ez-item-title">' + escHtml(title) + '</span>' +
                   '<span class="ez-remove" title="Remove">✕</span>' +
                   '</div>';
        $('#ez-active-services').append(html);
    }

    // ── Add item to active brands box ───────────────────
    function addActiveBrandItem(id, title) {
        var html = '<div class="ez-item ez-item-active" data-id="' + id + '" data-title="' + escAttr(title) + '">' +
                   '<span class="ez-item-title">' + escHtml(title) + '</span>' +
                   '<span class="ez-remove" title="Remove">✕</span>' +
                   '</div>';
        $('#ez-active-brands').append(html);
    }

    // ── Open brand panel for selected service ────────────
    function openBrandPanel(sid, title) {
        $('#ez-selected-service-name').text(title);

        var $avail  = $('#ez-available-brands');
        var $active = $('#ez-active-brands');

        // Clear
        $avail.find('.ez-item').remove();
        $active.find('.ez-item-active').remove();
        $('#ez-brands-empty').show();

        // Find brands for this service from EZ_SERVICES
        var brands = getBrandsForService(sid);

        if (brands.length === 0) {
            $avail.html('<div class="ez-no-brands-notice">⚠️ No brand posts linked to this service.<br>Edit the service post → "Brand Service Links" → add brand posts first.</div>');
        } else {
            $avail.find('.ez-no-brands-notice').remove();
            $avail.find('.ez-empty').remove();

            // Get currently active brand IDs for this service
            var activeBrandIds = (state.data[sid] || []).map(function(b) { return parseInt(b); });

            $.each(brands, function(i, brand) {
                var isActive = activeBrandIds.indexOf(parseInt(brand.id)) !== -1;
                var html = '<div class="ez-item ' + (isActive ? 'ez-item-used' : '') + '" ' +
                           'data-id="' + brand.id + '" data-title="' + escAttr(brand.title) + '">' +
                           escHtml(brand.title) +
                           (isActive ? '<span class="ez-tag">Added</span>' : '') +
                           '</div>';
                $avail.append(html);

                if (isActive) {
                    addActiveBrandItem(brand.id, brand.title);
                    $('#ez-brands-empty').hide();
                }
            });
        }

        // Show panel
        $('#ez-brands-panel').slideDown(200);
    }

    // ── Helpers ──────────────────────────────────────────
    function getServiceTitle(sid) {
        var title = null;
        if (typeof EZ_SERVICES !== 'undefined') {
            $.each(EZ_SERVICES, function(i, svc) {
                if (String(svc.id) === String(sid)) {
                    title = svc.title;
                    return false;
                }
            });
        }
        return title;
    }

    function getBrandsForService(sid) {
        var brands = [];
        if (typeof EZ_SERVICES !== 'undefined') {
            $.each(EZ_SERVICES, function(i, svc) {
                if (String(svc.id) === String(sid)) {
                    brands = svc.brands || [];
                    return false;
                }
            });
        }
        return brands;
    }

    function saveData() {
        $('#ez_service_brands_data').val(JSON.stringify(state.data));
    }

    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function escAttr(str) {
        return String(str).replace(/"/g, '&quot;');
    }

})(jQuery);
