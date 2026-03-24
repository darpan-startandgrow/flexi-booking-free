<?php
$dbhandler    = new BM_DBhandler();
$categories_table = new BM_Categories_List_Table();
$categories_table->prepare_items();

// Build category IDs for bulk shortcode.
$all_cats = $dbhandler->get_all_result( 'CATEGORY', 'id', 1, 'results', 0, false, 'cat_position', false );
$cat_ids  = wp_list_pluck( $all_cats, 'id', 0 );
$cat_ids  = ! empty( $cat_ids ) && is_array( $cat_ids ) ? implode( ',', array_merge( array( 0 ), $cat_ids ) ) : '';

?>


<div class="sg-admin-main-box">
<!-- Categories -->
<div class="wrap listing_table" id="category_records_listing">
    <div class="row">
        <span style="display: inline-block;width:50%;">
            <h2 class="title" style="font-weight: bold;"><?php esc_html_e( 'All Categories', 'service-booking' ); ?></h2>
            <a href="admin.php?page=bm_add_category" class="button button-primary" style="margin-bottom:10px;" title="<?php esc_html_e( 'Add Category', 'service-booking' ); ?>"><?php esc_html_e( 'Add Category', 'service-booking' ); ?>&nbsp;<i class="fa fa-plus" aria-hidden="true"></i></a>
        </span>
        <span class="copyMessagetooltip allShortcode categoryShortcode" style="float:right;">
            <h2 class="title" style="font-weight: bold;margin-left:8px;"><?php esc_html_e( 'Shortcode with multiple category ids ', 'service-booking' ); ?></h2>
            <input class="copytextTooltip overallCategoryShortcode" value="<?php echo esc_html( '[sgbm_service_by_category ids="' . esc_attr( $cat_ids ) . '"]' ); ?>" id="copyInput_0" onclick="bm_copy_text(this)" onmouseout="bm_copy_message(this)" style="width:100%;" readonly>
            <span class="tooltiptext" id="copyTooltip_0"><?php esc_html_e( 'Copy to clipboard', 'service-booking' ); ?></span>
        </span>
    </div>
    <form method="get">
        <input type="hidden" name="page" value="<?php echo esc_attr( isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '' ); ?>" />
        <?php $categories_table->display(); ?>
    </form>
</div>

<input type="hidden" id="category_pagenum" value="<?php echo esc_attr( 1 ); ?>" />
<input type="hidden" name="limit_count" id="limit_count" value="<?php echo esc_attr( $limit ); ?>" />

<div class="popup-message-overlay" id="popup-message-overlay"></div>
<div class="popup-message-container animate__animated animate__swing" id="popup-message-container">
    <span id="popup-message"></span>
    <button class="close-popup-message" id="close-popup-message" title="<?php esc_html_e( 'Close', 'service-booking' ); ?>"><?php echo esc_html( '✕' ); ?></button>
</div>

<div class="loader_modal"></div>
</div>

