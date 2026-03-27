<?php
/**
 * Shared Extras Dashboard – CRUD for global extras.
 *
 * @since      1.5.0
 * @package    Booking_Management
 * @subpackage Booking_Management/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dbhandler  = new BM_DBhandler();
$bmrequests = new BM_Request();
$currency_symbol = $bmrequests->bm_get_currency_symbol( $dbhandler->get_global_option_value( 'bm_booking_currency', 'EUR' ) );

$action          = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
$global_extra_id = isset( $_GET['global_extra_id'] ) ? absint( $_GET['global_extra_id'] ) : 0;

// ── Handle single-row delete via GET ─────────────────────────────────────
if ( 'delete' === $action && $global_extra_id > 0 ) {
	if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'bm_delete_global_extra_' . $global_extra_id ) ) {
		$dbhandler->remove_row( 'SERVICE_GLOBAL_EXTRA', 'global_extra_id', $global_extra_id, '%d' );
		$dbhandler->remove_row( 'GLOBAL_EXTRA', 'id', $global_extra_id, '%d' );
		wp_safe_redirect( esc_url_raw( admin_url( 'admin.php?page=bm_shared_extras&deleted=1' ) ) );
		exit;
	}
}

// ── Handle save / update ─────────────────────────────────────────────────
if ( isset( $_POST['bm_save_global_extra'] ) || isset( $_POST['bm_update_global_extra'] ) ) {
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'bm_global_extra_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'service-booking' ) );
	}

	$fields = array(
		'extra_name'             => isset( $_POST['extra_name'] ) ? sanitize_text_field( wp_unslash( $_POST['extra_name'] ) ) : '',
		'extra_desc'             => isset( $_POST['extra_desc'] ) ? wp_kses_post( wp_unslash( $_POST['extra_desc'] ) ) : '',
		'extra_price'            => isset( $_POST['extra_price'] ) ? floatval( $_POST['extra_price'] ) : 0,
		'extra_duration'         => isset( $_POST['extra_duration'] ) ? floatval( $_POST['extra_duration'] ) : 0,
		'extra_operation'        => isset( $_POST['extra_operation'] ) ? floatval( $_POST['extra_operation'] ) : 0,
		'extra_max_cap'          => ! empty( $_POST['extra_max_cap'] ) ? absint( $_POST['extra_max_cap'] ) : 1,
		'is_extra_service_front' => isset( $_POST['is_extra_service_front'] ) ? 1 : 0,
		'is_linked_wc_extrasvc'  => isset( $_POST['is_linked_wc_extrasvc'] ) ? 1 : 0,
		'svcextra_wc_product'    => isset( $_POST['is_linked_wc_extrasvc'] ) && ! empty( $_POST['svcextra_wc_product'] ) ? absint( $_POST['svcextra_wc_product'] ) : null,
	);

	if ( isset( $_POST['bm_save_global_extra'] ) ) {
		$new_id = $dbhandler->insert_row( 'GLOBAL_EXTRA', $fields );

		// Auto-link to selected services.
		if ( $new_id && ! empty( $_POST['link_service_ids'] ) && is_array( $_POST['link_service_ids'] ) ) {
			foreach ( array_map( 'absint', $_POST['link_service_ids'] ) as $sid ) {
				if ( $sid > 0 ) {
					$dbhandler->insert_row( 'SERVICE_GLOBAL_EXTRA', array( 'service_id' => $sid, 'global_extra_id' => $new_id ) );
				}
			}
		}
		wp_safe_redirect( esc_url_raw( admin_url( 'admin.php?page=bm_shared_extras&saved=1' ) ) );
		exit;
	}

	if ( isset( $_POST['bm_update_global_extra'] ) ) {
		$edit_id = absint( $_POST['global_extra_id'] );
		if ( $edit_id > 0 ) {
			$fields['extras_updated_at'] = $bmrequests->bm_fetch_current_wordpress_datetime_stamp();
			$dbhandler->update_row( 'GLOBAL_EXTRA', 'id', $edit_id, $fields, '', '%d' );
		}
		wp_safe_redirect( esc_url_raw( admin_url( 'admin.php?page=bm_shared_extras&updated=1' ) ) );
		exit;
	}
}

// ── Handle link / unlink service association ─────────────────────────────
if ( isset( $_POST['bm_link_services'] ) ) {
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'bm_link_services_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'service-booking' ) );
	}
	$ge_id       = absint( $_POST['global_extra_id'] );
	$service_ids = isset( $_POST['link_service_ids'] ) ? array_map( 'absint', (array) $_POST['link_service_ids'] ) : array();

	// Remove existing links for this extra, then re-create selected ones.
	$dbhandler->remove_row( 'SERVICE_GLOBAL_EXTRA', 'global_extra_id', $ge_id, '%d' );
	foreach ( $service_ids as $sid ) {
		if ( $sid > 0 ) {
			$dbhandler->insert_row( 'SERVICE_GLOBAL_EXTRA', array( 'service_id' => $sid, 'global_extra_id' => $ge_id ) );
		}
	}
	wp_safe_redirect( esc_url_raw( admin_url( 'admin.php?page=bm_shared_extras&linked=1' ) ) );
	exit;
}

// ── Load edit data if editing ────────────────────────────────────────────
$edit_row = null;
if ( 'edit' === $action && $global_extra_id > 0 ) {
	$edit_row = $dbhandler->get_row( 'GLOBAL_EXTRA', $global_extra_id );
}

// ── Load services for association ────────────────────────────────────────
$all_services = $dbhandler->get_all_result( 'SERVICE', '*', 1, 'results' );

// ── Flash messages ───────────────────────────────────────────────────────
if ( isset( $_GET['saved'] ) ) {
	echo '<div class="bm-notice bm-success">' . esc_html__( 'Shared Extra created successfully.', 'service-booking' ) . '</div>';
} elseif ( isset( $_GET['updated'] ) ) {
	echo '<div class="bm-notice bm-success">' . esc_html__( 'Shared Extra updated successfully.', 'service-booking' ) . '</div>';
} elseif ( isset( $_GET['deleted'] ) ) {
	echo '<div class="bm-notice bm-success">' . esc_html__( 'Shared Extra deleted successfully.', 'service-booking' ) . '</div>';
} elseif ( isset( $_GET['linked'] ) ) {
	echo '<div class="bm-notice bm-success">' . esc_html__( 'Service associations updated.', 'service-booking' ) . '</div>';
}
?>

<div class="wrap listing_table" id="shared_extras_listing">

	<?php if ( 'edit' === $action && $edit_row ) : ?>

		<!-- ══════ EDIT FORM ══════ -->
		<h2 class="title" style="font-weight:bold;"><?php esc_html_e( 'Edit Shared Extra', 'service-booking' ); ?></h2>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=bm_shared_extras' ) ); ?>">
			<?php wp_nonce_field( 'bm_global_extra_nonce' ); ?>
			<input type="hidden" name="global_extra_id" value="<?php echo esc_attr( $edit_row->id ); ?>" />
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="extra_name"><?php esc_html_e( 'Name', 'service-booking' ); ?></label><strong class="required_asterisk"> *</strong></th>
					<td><input name="extra_name" type="text" id="extra_name" value="<?php echo esc_attr( $edit_row->extra_name ); ?>" class="regular-text" required /></td>
				</tr>
				<tr>
					<th scope="row"><label for="extra_price"><?php echo sprintf( esc_html__( 'Price (%s)', 'service-booking' ), esc_html( $currency_symbol ) ); ?></label><strong class="required_asterisk"> *</strong></th>
					<td><input name="extra_price" type="text" id="extra_price" value="<?php echo esc_attr( $edit_row->extra_price ); ?>" class="regular-text" required /></td>
				</tr>
				<tr>
					<th scope="row"><label for="extra_max_cap"><?php esc_html_e( 'Max Capacity (Shared Pool)', 'service-booking' ); ?></label></th>
					<td><input name="extra_max_cap" type="number" min="1" id="extra_max_cap" value="<?php echo esc_attr( $edit_row->extra_max_cap ); ?>" class="regular-text" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="extra_duration"><?php esc_html_e( 'Duration (hrs)', 'service-booking' ); ?></label></th>
					<td><input name="extra_duration" type="number" step="0.5" min="0" id="extra_duration" value="<?php echo esc_attr( $edit_row->extra_duration ); ?>" class="regular-text" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="extra_operation"><?php esc_html_e( 'Total Operation Time (hrs)', 'service-booking' ); ?></label></th>
					<td><input name="extra_operation" type="number" step="0.5" min="0" id="extra_operation" value="<?php echo esc_attr( $edit_row->extra_operation ); ?>" class="regular-text" /></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Visible in frontend?', 'service-booking' ); ?></th>
					<td class="bm-checkbox-td">
						<input name="is_extra_service_front" type="checkbox" id="is_extra_service_front" class="bm_toggle" value="1" <?php checked( $edit_row->is_extra_service_front, 1 ); ?> />
						<label for="is_extra_service_front"></label>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="extra_desc"><?php esc_html_e( 'Description', 'service-booking' ); ?></label></th>
					<td><textarea name="extra_desc" id="extra_desc" rows="4" class="large-text"><?php echo esc_textarea( $edit_row->extra_desc ); ?></textarea></td>
				</tr>
			</table>

			<h3 style="margin-top:20px;"><?php esc_html_e( 'Linked Services', 'service-booking' ); ?></h3>
			<?php
			$linked_ids = array();
			$links      = $dbhandler->get_all_result( 'SERVICE_GLOBAL_EXTRA', '*', array( 'global_extra_id' => $edit_row->id ), 'results' );
			if ( ! empty( $links ) ) {
				$linked_ids = wp_list_pluck( $links, 'service_id' );
			}
			?>
			<div class="bm-shared-services-checkboxes" style="max-height:200px;overflow-y:auto;border:1px solid #ddd;padding:10px;margin-bottom:15px;">
				<?php if ( ! empty( $all_services ) ) : ?>
					<?php foreach ( $all_services as $svc ) : ?>
						<label style="display:block;margin-bottom:5px;">
							<input type="checkbox" name="link_service_ids[]" value="<?php echo esc_attr( $svc->id ); ?>" <?php checked( in_array( (int) $svc->id, array_map( 'intval', $linked_ids ), true ) ); ?> />
							<?php echo esc_html( $svc->service_name ); ?>
						</label>
					<?php endforeach; ?>
				<?php else : ?>
					<p><?php esc_html_e( 'No services available.', 'service-booking' ); ?></p>
				<?php endif; ?>
			</div>

			<input type="submit" name="bm_update_global_extra" class="button button-primary" value="<?php esc_attr_e( 'Update Shared Extra', 'service-booking' ); ?>" />
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=bm_shared_extras' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Cancel', 'service-booking' ); ?></a>
		</form>

	<?php elseif ( 'link' === $action && $global_extra_id > 0 ) : ?>

		<!-- ══════ BULK LINK FORM ══════ -->
		<?php
		$link_row = $dbhandler->get_row( 'GLOBAL_EXTRA', $global_extra_id );
		$linked_ids = array();
		$links      = $dbhandler->get_all_result( 'SERVICE_GLOBAL_EXTRA', '*', array( 'global_extra_id' => $global_extra_id ), 'results' );
		if ( ! empty( $links ) ) {
			$linked_ids = wp_list_pluck( $links, 'service_id' );
		}
		?>
		<h2 class="title" style="font-weight:bold;"><?php echo sprintf( esc_html__( 'Link Services to "%s"', 'service-booking' ), esc_html( $link_row->extra_name ) ); ?></h2>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=bm_shared_extras' ) ); ?>">
			<?php wp_nonce_field( 'bm_link_services_nonce' ); ?>
			<input type="hidden" name="global_extra_id" value="<?php echo esc_attr( $global_extra_id ); ?>" />
			<div class="bm-shared-services-checkboxes" style="max-height:300px;overflow-y:auto;border:1px solid #ddd;padding:10px;margin-bottom:15px;">
				<?php if ( ! empty( $all_services ) ) : ?>
					<?php foreach ( $all_services as $svc ) : ?>
						<label style="display:block;margin-bottom:5px;">
							<input type="checkbox" name="link_service_ids[]" value="<?php echo esc_attr( $svc->id ); ?>" <?php checked( in_array( (int) $svc->id, array_map( 'intval', $linked_ids ), true ) ); ?> />
							<?php echo esc_html( $svc->service_name ); ?>
						</label>
					<?php endforeach; ?>
				<?php else : ?>
					<p><?php esc_html_e( 'No services available.', 'service-booking' ); ?></p>
				<?php endif; ?>
			</div>
			<input type="submit" name="bm_link_services" class="button button-primary" value="<?php esc_attr_e( 'Save Associations', 'service-booking' ); ?>" />
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=bm_shared_extras' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Cancel', 'service-booking' ); ?></a>
		</form>

	<?php else : ?>

		<!-- ══════ LIST + CREATE FORM ══════ -->
		<div class="row">
			<h2 class="title" style="font-weight:bold;"><?php esc_html_e( 'Shared Extras', 'service-booking' ); ?></h2>
		</div>

		<!-- Create new shared extra form (collapsible) -->
		<div id="bm-shared-extra-create-section" style="display:none;margin-bottom:20px;background:#f9f9f9;padding:15px;border:1px solid #ddd;">
			<h3><?php esc_html_e( 'Create Shared Extra', 'service-booking' ); ?></h3>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=bm_shared_extras' ) ); ?>">
				<?php wp_nonce_field( 'bm_global_extra_nonce' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="extra_name"><?php esc_html_e( 'Name', 'service-booking' ); ?></label><strong class="required_asterisk"> *</strong></th>
						<td><input name="extra_name" type="text" id="extra_name" class="regular-text" required /></td>
					</tr>
					<tr>
						<th scope="row"><label for="extra_price"><?php echo sprintf( esc_html__( 'Price (%s)', 'service-booking' ), esc_html( $currency_symbol ) ); ?></label><strong class="required_asterisk"> *</strong></th>
						<td><input name="extra_price" type="text" id="extra_price" class="regular-text" required /></td>
					</tr>
					<tr>
						<th scope="row"><label for="extra_max_cap"><?php esc_html_e( 'Max Capacity (Shared Pool)', 'service-booking' ); ?></label></th>
						<td><input name="extra_max_cap" type="number" min="1" id="extra_max_cap" value="1" class="regular-text" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="extra_duration"><?php esc_html_e( 'Duration (hrs)', 'service-booking' ); ?></label></th>
						<td><input name="extra_duration" type="number" step="0.5" min="0" id="extra_duration" value="0" class="regular-text" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="extra_operation"><?php esc_html_e( 'Total Operation Time (hrs)', 'service-booking' ); ?></label></th>
						<td><input name="extra_operation" type="number" step="0.5" min="0" id="extra_operation" value="0" class="regular-text" /></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Visible in frontend?', 'service-booking' ); ?></th>
						<td class="bm-checkbox-td">
							<input name="is_extra_service_front" type="checkbox" id="is_extra_service_front_new" class="bm_toggle" value="1" checked />
							<label for="is_extra_service_front_new"></label>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="extra_desc"><?php esc_html_e( 'Description', 'service-booking' ); ?></label></th>
						<td><textarea name="extra_desc" id="extra_desc" rows="3" class="large-text"></textarea></td>
					</tr>
				</table>

				<h4><?php esc_html_e( 'Link to Services (optional)', 'service-booking' ); ?></h4>
				<div class="bm-shared-services-checkboxes" style="max-height:200px;overflow-y:auto;border:1px solid #ddd;padding:10px;margin-bottom:15px;">
					<?php if ( ! empty( $all_services ) ) : ?>
						<?php foreach ( $all_services as $svc ) : ?>
							<label style="display:block;margin-bottom:5px;">
								<input type="checkbox" name="link_service_ids[]" value="<?php echo esc_attr( $svc->id ); ?>" />
								<?php echo esc_html( $svc->service_name ); ?>
							</label>
						<?php endforeach; ?>
					<?php else : ?>
						<p><?php esc_html_e( 'No services available.', 'service-booking' ); ?></p>
					<?php endif; ?>
				</div>

				<input type="submit" name="bm_save_global_extra" class="button button-primary" value="<?php esc_attr_e( 'Save Shared Extra', 'service-booking' ); ?>" />
				<button type="button" class="button button-secondary" onclick="document.getElementById('bm-shared-extra-create-section').style.display='none';"><?php esc_html_e( 'Cancel', 'service-booking' ); ?></button>
			</form>
		</div>

		<p>
			<button type="button" class="button button-primary" onclick="document.getElementById('bm-shared-extra-create-section').style.display='block';"><?php esc_html_e( 'Create Shared Extra', 'service-booking' ); ?>&nbsp;<i class="fa fa-plus" aria-hidden="true"></i></button>
		</p>

		<?php
		$global_extras_table = new BM_Global_Extras_List_Table();
		$global_extras_table->prepare_items();
		?>
		<form method="get">
			<input type="hidden" name="page" value="<?php echo esc_attr( isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '' ); ?>" />
			<?php $global_extras_table->display(); ?>
		</form>

	<?php endif; ?>

</div>
