<?php
/**
 * Booking Forms Listing Page.
 *
 * Displays all billing forms in a WP_List_Table with bulk actions.
 *
 * @since      1.3.0
 * @package    Booking_Management
 * @subpackage Booking_Management/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dbhandler   = new BM_DBhandler();
$forms_table = new BM_Forms_List_Table();

// Handle single-row delete action.
if ( isset( $_GET['action'] ) && 'delete' === $_GET['action'] && isset( $_GET['form_id'] ) ) {
	$del_id = absint( $_GET['form_id'] );
	if ( $del_id > 0 && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'delete-form-' . $del_id ) ) {
		$form = $dbhandler->get_row( 'BILLING_FORMS', $del_id );
		if ( $form && empty( $form->is_default ) ) {
			$activator    = new Booking_Management_Activator();
			$fields_table = $activator->get_db_table_name( 'FIELDS' );
			$GLOBALS['wpdb']->delete( $fields_table, array( 'form_id' => $del_id ), array( '%d' ) );
			$dbhandler->remove_row( 'BILLING_FORMS', 'id', $del_id, '%d' );
		}
	}
	wp_safe_redirect( admin_url( 'admin.php?page=sg-booking-forms' ) );
	exit;
}

$forms_table->prepare_items();
$total_forms = (int) $dbhandler->bm_count( 'BILLING_FORMS' );
?>

<div class="sg-admin-main-box">
<div class="wrap listing_table" id="forms_listing">
	<div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; margin-bottom:10px;">
		<div>
			<h2 class="title" style="font-weight:bold; margin:0;">
				<span class="dashicons dashicons-feedback" style="font-size:24px;width:24px;height:24px;margin-right:6px;color:#5b5ea6;vertical-align:text-bottom;"></span>
				<?php esc_html_e( 'Booking Forms', 'service-booking' ); ?>
			</h2>
			<p class="description" style="margin:4px 0 0;">
				<?php
				printf(
					/* translators: %d: total form count */
					esc_html__( '%d form(s) available', 'service-booking' ),
					$total_forms
				);
				?>
				&mdash; <?php esc_html_e( 'Click a form name to open the drag-and-drop builder.', 'service-booking' ); ?>
			</p>
		</div>
		<div style="display:flex; gap:8px; align-items:center;">
			<button class="button" disabled aria-disabled="true" title="<?php esc_attr_e( 'Creating additional forms is a Pro feature.', 'service-booking' ); ?>" style="opacity:0.65;cursor:not-allowed;">
				<span class="dashicons dashicons-plus-alt2" style="font-size:16px;width:16px;height:16px;vertical-align:text-bottom;margin-right:2px;"></span>
				<?php esc_html_e( 'Add New Form', 'service-booking' ); ?>&nbsp;<span class="sg-pro-badge"><?php esc_html_e( 'Pro', 'service-booking' ); ?></span>
			</button>
		</div>
	</div>

	<div style="margin-bottom:15px; padding:12px 16px; background:linear-gradient(135deg,#f5f3ff,#ede9fe); border-left:4px solid #7c3aed; border-radius:3px;">
		<p style="margin:0;">
			<span class="dashicons dashicons-info" style="color:#7c3aed;"></span>
			<strong><?php esc_html_e( 'Drag & Drop Form Builder', 'service-booking' ); ?></strong><br/>
			<small><?php esc_html_e( 'Click "Edit" on any form to open the visual form builder. Rearrange fields, edit settings, and preview your form in real-time.', 'service-booking' ); ?></small>
		</p>
	</div>

	<form method="get">
		<input type="hidden" name="page" value="<?php echo esc_attr( isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : 'sg-booking-forms' ); ?>" />
		<?php $forms_table->display(); ?>
	</form>
</div>

<!-- Popup Messages -->
<div class="popup-message-overlay" id="popup-message-overlay"></div>
<div class="popup-message-container animate__animated animate__jackInTheBox" id="popup-message-container">
	<span id="popup-message"></span>
	<button class="close-popup-message" id="close-popup-message" title="<?php esc_html_e( 'Close', 'service-booking' ); ?>"><?php echo esc_html( '✕' ); ?></button>
</div>

<div class="loader_modal"></div>
</div>
