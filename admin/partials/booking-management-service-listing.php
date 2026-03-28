<?php
$services_table = isset( $this ) && method_exists( $this, 'get_list_table' ) ? $this->get_list_table( 'bm_all_services' ) : null;
if ( ! $services_table ) {
	$services_table = new BM_Services_List_Table();
}
$services_table->prepare_items();
?>

<!-- Services -->
<div class="wrap listing_table" id="service_records_listing">
    <div class="row">
        <h2 class="title" style="font-weight: bold;"><?php esc_html_e( 'All Services', 'service-booking' ); ?></h2>
        <?php if ( apply_filters( 'bm_can_add_service', true ) ) : ?>
            <a href="admin.php?page=bm_add_service" class="button-primary"><?php esc_html_e( 'Add Service', 'service-booking' ); ?></a>
        <?php else : ?>
            <button class="button" disabled title="Upgrade to Pro for unlimited services"><?php esc_html_e( 'Add Service (Limit Reached)', 'service-booking' ); ?></button>
        <?php endif; ?>
    </div>
    <form method="get">
        <input type="hidden" name="page" value="<?php echo esc_attr( isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '' ); ?>" />
        <?php $services_table->display(); ?>
    </form>
    
    <!-- Global Shortcodes Section -->
    <h2 class="title" style="font-weight: bold; margin-top: 30px;"><?php esc_html_e( 'Global Shortcodes', 'service-booking' ); ?></h2>
    <table class="wp-list-table widefat striped">
        <thead>
            <tr>
                <th style="text-align: center;font-weight: 600;"><?php esc_html_e( 'Shortcode', 'service-booking' ); ?></th>
                <th style="text-align: center;font-weight: 600;"><?php esc_html_e( 'Info', 'service-booking' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center;">
                    <div class="copyMessagetooltip">
                        <input class="copytextTooltip" value="[sgbm_service_search]" onclick="bm_copy_text(this)" onmouseout="bm_copy_message(this)" readonly>
                        <span class="tooltiptext"><?php esc_html_e( 'Copy to clipboard', 'service-booking' ); ?></span>
                    </div>
                </td>
                <td style="text-align: center;">
                    <button type="button" class="bm-info-button" data-shortcode="sgbm_service_search" title="<?php esc_html_e( 'Shortcode Info', 'service-booking' ); ?>">i</button>
                </td>
            </tr>
            <tr>
                <td style="text-align: center;">
                    <div class="copyMessagetooltip">
                        <input class="copytextTooltip" value="[sgbm_service_fullcalendar]" onclick="bm_copy_text(this)" onmouseout="bm_copy_message(this)" readonly>
                        <span class="tooltiptext"><?php esc_html_e( 'Copy to clipboard', 'service-booking' ); ?></span>
                    </div>
                </td>
                <td style="text-align: center;">
                    <button type="button" class="bm-info-button" data-shortcode="sgbm_service_fullcalendar" title="<?php esc_html_e( 'Shortcode Info', 'service-booking' ); ?>">i</button>
                </td>
            </tr>
            <tr>
                <td style="text-align: center;">
                    <div class="copyMessagetooltip">
                        <input class="copytextTooltip" value="[sgbm_service_timeslot_fullcalendar]" onclick="bm_copy_text(this)" onmouseout="bm_copy_message(this)" readonly>
                        <span class="tooltiptext"><?php esc_html_e( 'Copy to clipboard', 'service-booking' ); ?></span>
                    </div>
                </td>
                <td style="text-align: center;">
                    <button type="button" class="bm-info-button" data-shortcode="sgbm_service_timeslot_fullcalendar" title="<?php esc_html_e( 'Shortcode Info', 'service-booking' ); ?>">i</button>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Shortcode Info Modal -->
<div id="bm-shortcode-info-modal" class="bm-shortcode-modal" style="display:none;">
    <div class="bm-shortcode-modal-content">
        <span class="bm-close-shortcode-modal">&times;</span>
        <h2 id="bm-shortcode-title"></h2>
        <div id="bm-shortcode-description"></div>
        <h3><?php esc_html_e( 'Attributes', 'service-booking' ); ?></h3>
        <table id="bm-shortcode-attributes" class="widefat">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Attribute', 'service-booking' ); ?></th>
                    <th><?php esc_html_e( 'Description', 'service-booking' ); ?></th>
                    <th><?php esc_html_e( 'Default', 'service-booking' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <!-- Will be populated by JavaScript -->
            </tbody>
        </table>
        <h3><?php esc_html_e( 'Examples', 'service-booking' ); ?></h3>
        <pre id="bm-shortcode-examples"></pre>
    </div>
</div>

<input type="hidden" id="service_pagenum" value="<?php echo esc_attr( 1 ); ?>" />
<input type="hidden" name="limit_count" id="limit_count" value="<?php echo esc_attr( $limit ); ?>" />

<div class="popup-message-overlay" id="popup-message-overlay"></div>
<div class="popup-message-container animate__animated animate__shakeY" id="popup-message-container">
    <span id="popup-message"></span>
    <button class="close-popup-message" id="close-popup-message" title="<?php esc_html_e( 'Close', 'service-booking' ); ?>"><?php echo esc_html( '✕' ); ?></button>
</div>

<div class="loader_modal"></div>

<script>
var bm_shortcode_info = {
    'sgbm_single_service': {
        title: '<?php esc_html_e( 'Single Service', 'service-booking' ); ?>',
        description: '<?php esc_html_e( 'Displays a single service with details.', 'service-booking' ); ?>',
        attributes: [
            {name: 'id', description: '<?php esc_html_e( 'Service ID', 'service-booking' ); ?>', default: '<?php esc_html_e( 'Required', 'service-booking' ); ?>'}
        ],
        examples: ['[sgbm_single_service id="1"]']
    },
    'sgbm_single_service_calendar': {
        title: '<?php esc_html_e( 'Single Service Calendar', 'service-booking' ); ?>',
        description: '<?php esc_html_e( 'Displays booking calendar for a single service.', 'service-booking' ); ?>',
        attributes: [
            {name: 'id', description: '<?php esc_html_e( 'Service ID', 'service-booking' ); ?>', default: '<?php esc_html_e( 'Required', 'service-booking' ); ?>'}
        ],
        examples: ['[sgbm_single_service_calendar id="1"]']
    },
    'sgbm_service_search': {
        title: '<?php esc_html_e( 'Service Search', 'service-booking' ); ?>',
        description: '<?php esc_html_e( 'Displays default service shortcode and filters.', 'service-booking' ); ?>',
        attributes: [
            {name: 'show_date', description: '<?php esc_html_e( 'Show date selector', 'service-booking' ); ?>', default: 'default'},
            {name: 'show_category_filter', description: '<?php esc_html_e( 'Show category filter', 'service-booking' ); ?>', default: 'default'},
            {name: 'show_service_filter', description: '<?php esc_html_e( 'Show service filter', 'service-booking' ); ?>', default: 'default'},
            {name: 'show_service_sorting', description: '<?php esc_html_e( 'Show sorting options', 'service-booking' ); ?>', default: 'default'},
            {name: 'show_grid_list_button', description: '<?php esc_html_e( 'Show grid/list toggle', 'service-booking' ); ?>', default: 'default'},
            {name: 'show_list_button', description: '<?php esc_html_e( 'Show list view button', 'service-booking' ); ?>', default: 'default'},
            {name: 'show_service_limit', description: '<?php esc_html_e( 'Show results per page selector', 'service-booking' ); ?>', default: 'default'},
            {name: 'service_view_type', description: '<?php esc_html_e( 'Default view type (grid/list)', 'service-booking' ); ?>', default: 'grid'}
        ],
        examples: [
            '[sgbm_service_search show_date="true" show_category_filter="true"]',
            '[sgbm_service_search show_service_filter="false" show_service_sorting="false"]',
            '[sgbm_service_search show_date="true" show_category_filter="false" show_grid_list_button="true" show_list_button="false"]',
            '[sgbm_service_search show_date="default" show_service_filter="true" show_category_filter="false" show_service_sorting="false" show_grid_list_button="false" show_service_limit="default" service_view_type="grid"]'
        ]
    },
    'sgbm_service_timeslot_fullcalendar': {
        title: '<?php esc_html_e( 'Service Timeslot Full Calendar', 'service-booking' ); ?>',
        description: '<?php esc_html_e( 'Displays timeslot-based full calendar for services.', 'service-booking' ); ?>',
        attributes: [
            {name: 'show_filters', description: '<?php esc_html_e( 'Show all filters', 'service-booking' ); ?>', default: 'true'},
            {name: 'show_category_filter', description: '<?php esc_html_e( 'Show category filter', 'service-booking' ); ?>', default: 'true'},
            {name: 'show_service_filter', description: '<?php esc_html_e( 'Show service filter', 'service-booking' ); ?>', default: 'true'},
            {name: 'cat_ids', description: '<?php esc_html_e( 'Comma-separated category IDs', 'service-booking' ); ?>', default: '[]'}
        ],
        examples: [
            '[sgbm_service_timeslot_fullcalendar]',
            '[sgbm_service_timeslot_fullcalendar show_filters="false"]',
            '[sgbm_service_timeslot_fullcalendar show_category_filter="false"]',
            '[sgbm_service_timeslot_fullcalendar show_service_filter="false"]',
            '[sgbm_service_timeslot_fullcalendar cat_ids="1,2"]'
        ]
    },
    'sgbm_service_fullcalendar': {
        title: '<?php esc_html_e( 'Service Full Calendar', 'service-booking' ); ?>',
        description: '<?php esc_html_e( 'Displays a full calendar view of services.', 'service-booking' ); ?>',
        attributes: [
            {name: 'show_filters', description: '<?php esc_html_e( 'Show all filters', 'service-booking' ); ?>', default: 'true'},
            {name: 'show_category_filter', description: '<?php esc_html_e( 'Show category filter', 'service-booking' ); ?>', default: 'true'},
            {name: 'show_service_filter', description: '<?php esc_html_e( 'Show service filter', 'service-booking' ); ?>', default: 'true'},
            {name: 'cat_ids', description: '<?php esc_html_e( 'Comma-separated category IDs', 'service-booking' ); ?>', default: '[]'}
        ],
        examples: [
            '[sgbm_service_fullcalendar]',
            '[sgbm_service_fullcalendar show_filters="false"]',
            '[sgbm_service_fullcalendar show_category_filter="false"]',
            '[sgbm_service_fullcalendar show_service_filter="false"]',
            '[sgbm_service_fullcalendar cat_ids="1,2"]'
        ]
    }
};

jQuery(document).ready(function($) {
    $('.bm-info-button').on('click', function(e) {
        e.preventDefault();
        var shortcode = $(this).data('shortcode');
        var info = bm_shortcode_info[shortcode];
        
        if (info) {
            $('#bm-shortcode-title').text(info.title);
            $('#bm-shortcode-description').text(info.description);
            
            var attributesBody = $('#bm-shortcode-attributes tbody');
            attributesBody.empty();
            
            if (info.attributes.length > 0) {
                $.each(info.attributes, function(i, attr) {
                    attributesBody.append(
                        '<tr>' +
                        '<td>' + attr.name + '</td>' +
                        '<td>' + attr.description + '</td>' +
                        '<td>' + attr.default + '</td>' +
                        '</tr>'
                    );
                });
            } else {
                attributesBody.append(
                    '<tr><td colspan="3"><?php esc_html_e( 'No attributes available', 'service-booking' ); ?></td></tr>'
                );
            }
            
            var examplesHtml = info.examples.join('\n');
            $('#bm-shortcode-examples').text(examplesHtml);
            
            $('#bm-shortcode-info-modal').show();
        }
    });
    
    $('.bm-close-shortcode-modal').on('click', function() {
        $('#bm-shortcode-info-modal').hide();
    });
    
    $(window).on('click', function(event) {
        if ($(event.target).is('#bm-shortcode-info-modal')) {
            $('#bm-shortcode-info-modal').hide();
        }
    });
});
</script>

