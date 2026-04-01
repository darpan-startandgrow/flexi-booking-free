<?php
/**
 * Drag-and-Drop Form Builder.
 *
 * Two-panel layout: left panel has available field types to drag,
 * right panel shows the current form fields as a live preview.
 *
 * @since      1.3.0
 * @package    Booking_Management
 * @subpackage Booking_Management/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dbhandler  = new BM_DBhandler();
$bmrequests = new BM_Request();
$form_id    = filter_input( INPUT_GET, 'form_id', FILTER_VALIDATE_INT );

if ( empty( $form_id ) ) {
	$form_id = 1;
}

$form = $dbhandler->get_row( 'BILLING_FORMS', $form_id );
if ( ! $form ) {
	echo '<div class="wrap"><div class="notice notice-error"><p>' . esc_html__( 'Form not found.', 'service-booking' ) . '</p></div></div>';
	return;
}

$form_name = isset( $form->form_name ) ? $form->form_name : '';
$form_desc = isset( $form->form_description ) ? $form->form_description : '';
$is_default = isset( $form->is_default ) ? (int) $form->is_default : 0;

// Fetch all fields for this form, ordered by field_position.
$activator    = new Booking_Management_Activator();
$fields_table = $activator->get_db_table_name( 'FIELDS' );
$fields       = $GLOBALS['wpdb']->get_results(
	$GLOBALS['wpdb']->prepare(
		"SELECT * FROM {$fields_table} WHERE form_id = %d ORDER BY field_position ASC",
		$form_id
	)
);

// Free field type definitions for the left panel.
$field_types = array(
	array( 'type' => 'text',     'label' => __( 'Text', 'service-booking' ),           'icon' => 'dashicons-editor-textcolor' ),
	array( 'type' => 'email',    'label' => __( 'Email', 'service-booking' ),          'icon' => 'dashicons-email' ),
	array( 'type' => 'tel',      'label' => __( 'Phone', 'service-booking' ),          'icon' => 'dashicons-phone' ),
	array( 'type' => 'textarea', 'label' => __( 'Textarea', 'service-booking' ),       'icon' => 'dashicons-editor-paragraph' ),
	array( 'type' => 'select',   'label' => __( 'Dropdown', 'service-booking' ),       'icon' => 'dashicons-arrow-down-alt2' ),
	array( 'type' => 'checkbox', 'label' => __( 'Checkbox', 'service-booking' ),       'icon' => 'dashicons-yes' ),
	array( 'type' => 'radio',    'label' => __( 'Radio', 'service-booking' ),          'icon' => 'dashicons-marker' ),
	array( 'type' => 'number',   'label' => __( 'Number', 'service-booking' ),         'icon' => 'dashicons-editor-ol' ),
	array( 'type' => 'date',     'label' => __( 'Date', 'service-booking' ),           'icon' => 'dashicons-calendar-alt' ),
	array( 'type' => 'time',     'label' => __( 'Time', 'service-booking' ),           'icon' => 'dashicons-clock' ),
	array( 'type' => 'url',      'label' => __( 'URL', 'service-booking' ),            'icon' => 'dashicons-admin-links' ),
	array( 'type' => 'file',     'label' => __( 'File Upload', 'service-booking' ),    'icon' => 'dashicons-upload' ),
	array( 'type' => 'hidden',   'label' => __( 'Hidden', 'service-booking' ),         'icon' => 'dashicons-hidden' ),
	array( 'type' => 'password', 'label' => __( 'Password', 'service-booking' ),       'icon' => 'dashicons-lock' ),
	array( 'type' => 'gdpr_consent', 'label' => __( 'GDPR Consent', 'service-booking' ), 'icon' => 'dashicons-shield' ),
);

// Pro-only field type teasers.
$pro_field_teasers = array(
	array( 'label' => __( 'Multi-Page Forms', 'service-booking' ),      'icon' => 'dashicons-media-document', 'desc' => __( 'Upgrade to Pro to create multi-page forms!', 'service-booking' ) ),
	array( 'label' => __( 'Payment (Stripe)', 'service-booking' ),      'icon' => 'dashicons-money-alt',      'desc' => __( 'Accept payments with Stripe! Pro only.', 'service-booking' ) ),
	array( 'label' => __( 'Advanced Conditions', 'service-booking' ),   'icon' => 'dashicons-randomize',      'desc' => __( 'Unlock advanced conditional rules! Pro only.', 'service-booking' ) ),
	array( 'label' => __( 'AI-Generated Fields', 'service-booking' ),   'icon' => 'dashicons-lightbulb',      'desc' => __( 'Let AI auto-fill fields! Pro only.', 'service-booking' ) ),
	array( 'label' => __( 'CAPTCHA Protection', 'service-booking' ),    'icon' => 'dashicons-shield-alt',     'desc' => __( 'Block spam with CAPTCHA! Pro only.', 'service-booking' ) ),
	array( 'label' => __( 'CRM Integrations', 'service-booking' ),      'icon' => 'dashicons-networking',     'desc' => __( 'Sync with HubSpot! Pro only.', 'service-booking' ) ),
	array( 'label' => __( 'Workflow Automation', 'service-booking' ),    'icon' => 'dashicons-controls-repeat', 'desc' => __( 'Automate workflows! Pro only.', 'service-booking' ) ),
	array( 'label' => __( '2,000+ Templates', 'service-booking' ),      'icon' => 'dashicons-layout',         'desc' => __( '2,000+ premium templates! Pro only.', 'service-booking' ) ),
	array( 'label' => __( 'Offline Forms', 'service-booking' ),          'icon' => 'dashicons-cloud',          'desc' => __( 'Enable offline forms! Pro only.', 'service-booking' ) ),
	array( 'label' => __( 'Digital Signatures', 'service-booking' ),     'icon' => 'dashicons-edit',           'desc' => __( 'Add digital signatures! Pro only.', 'service-booking' ) ),
	array( 'label' => __( 'Surveys', 'service-booking' ),                'icon' => 'dashicons-forms',          'desc' => __( 'Create surveys! Pro only.', 'service-booking' ) ),
	array( 'label' => __( 'Multi-Site Support', 'service-booking' ),     'icon' => 'dashicons-admin-multisite', 'desc' => __( 'Unlock multi-site support! Pro only.', 'service-booking' ) ),
);

// Pre-built templates.
$templates = array(
	'contact' => array(
		'name'   => __( 'Contact Form', 'service-booking' ),
		'icon'   => 'dashicons-email-alt',
		'desc'   => __( 'A simple contact form with name, email, phone, and message fields.', 'service-booking' ),
		'fields' => array(
			array( 'type' => 'text',     'label' => __( 'Full Name', 'service-booking' ),  'required' => 1, 'width' => 'full' ),
			array( 'type' => 'email',    'label' => __( 'Email Address', 'service-booking' ), 'required' => 1, 'width' => 'half' ),
			array( 'type' => 'tel',      'label' => __( 'Phone Number', 'service-booking' ), 'required' => 0, 'width' => 'half' ),
			array( 'type' => 'textarea', 'label' => __( 'Message', 'service-booking' ),     'required' => 1, 'width' => 'full' ),
			array( 'type' => 'gdpr_consent', 'label' => __( 'Privacy Consent', 'service-booking' ), 'required' => 1, 'width' => 'full' ),
		),
	),
	'booking' => array(
		'name'   => __( 'Booking Form', 'service-booking' ),
		'icon'   => 'dashicons-calendar-alt',
		'desc'   => __( 'A booking form with name, email, date, time, and notes fields.', 'service-booking' ),
		'fields' => array(
			array( 'type' => 'text',     'label' => __( 'First Name', 'service-booking' ),  'required' => 1, 'width' => 'half' ),
			array( 'type' => 'text',     'label' => __( 'Last Name', 'service-booking' ),   'required' => 1, 'width' => 'half' ),
			array( 'type' => 'email',    'label' => __( 'Email', 'service-booking' ),        'required' => 1, 'width' => 'half' ),
			array( 'type' => 'tel',      'label' => __( 'Phone', 'service-booking' ),        'required' => 0, 'width' => 'half' ),
			array( 'type' => 'date',     'label' => __( 'Preferred Date', 'service-booking' ), 'required' => 1, 'width' => 'half' ),
			array( 'type' => 'time',     'label' => __( 'Preferred Time', 'service-booking' ), 'required' => 0, 'width' => 'half' ),
			array( 'type' => 'textarea', 'label' => __( 'Additional Notes', 'service-booking' ), 'required' => 0, 'width' => 'full' ),
			array( 'type' => 'gdpr_consent', 'label' => __( 'GDPR Consent', 'service-booking' ), 'required' => 1, 'width' => 'full' ),
		),
	),
);
?>


<div class="wrap" id="bm-form-builder-wrap">

	<!-- Top bar -->
	<div class="bm-fb-topbar">
		<div class="bm-fb-topbar-left">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=sg-booking-forms' ) ); ?>" class="bm-fb-back" title="<?php esc_attr_e( 'Back to Forms', 'service-booking' ); ?>">
				<span class="dashicons dashicons-arrow-left-alt"></span>
			</a>
			<div class="bm-fb-form-info">
				<h2 class="bm-fb-title"><?php echo esc_html( $form_name ); ?></h2>
				<?php if ( $is_default ) : ?>
					<span class="bm-fb-badge bm-fb-badge-default"><?php esc_html_e( 'Default', 'service-booking' ); ?></span>
				<?php endif; ?>
			</div>
		</div>
		<div class="bm-fb-topbar-right">
			<button type="button" class="button bm-fb-btn-preview" id="bm-fb-preview-btn">
				<span class="dashicons dashicons-visibility"></span>
				<?php esc_html_e( 'Preview', 'service-booking' ); ?>
			</button>
			<button type="button" class="button button-primary bm-fb-btn-save" id="bm-fb-save-btn">
				<span class="dashicons dashicons-saved"></span>
				<?php esc_html_e( 'Save Form', 'service-booking' ); ?>
			</button>
		</div>
	</div>

	<!-- Main builder area -->
	<div class="bm-fb-main">

		<!-- Left Panel: Available Fields -->
		<div class="bm-fb-sidebar" id="bm-fb-sidebar">

			<!-- Sidebar tabs -->
			<div class="bm-fb-sidebar-tabs">
				<button type="button" class="bm-fb-sidebar-tab active" data-tab="fields"><?php esc_html_e( 'Fields', 'service-booking' ); ?></button>
				<button type="button" class="bm-fb-sidebar-tab" data-tab="templates"><?php esc_html_e( 'Templates', 'service-booking' ); ?></button>
			</div>

			<!-- Fields tab -->
			<div class="bm-fb-tab-content" id="bm-fb-tab-fields">
				<div class="bm-fb-sidebar-header">
					<h3><span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e( 'Add Fields', 'service-booking' ); ?></h3>
					<p class="description"><?php esc_html_e( 'Click a field to add it to the form.', 'service-booking' ); ?></p>
				</div>

				<div class="bm-fb-field-types" id="bm-fb-field-types">
					<?php foreach ( $field_types as $ft ) : ?>
						<div class="bm-fb-field-type bm-fb-field-type-free bm-fb-pro-locked" data-type="<?php echo esc_attr( $ft['type'] ); ?>" title="<?php echo esc_attr( $ft['label'] ); ?>" style="opacity:0.6;cursor:not-allowed;">
							<span class="dashicons <?php echo esc_attr( $ft['icon'] ); ?>"></span>
							<span class="bm-fb-ft-label"><?php echo esc_html( $ft['label'] ); ?></span>
							<span class="sg-pro-badge" style="font-size:9px;margin-left:auto;"><?php esc_html_e( 'Pro', 'service-booking' ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>

				<!-- Pro Feature Teasers -->
				<div class="bm-fb-pro-teasers">
					<h4 class="bm-fb-pro-teasers-title">
						<span class="dashicons dashicons-star-filled"></span>
						<?php esc_html_e( 'Pro Features', 'service-booking' ); ?>
					</h4>
					<?php foreach ( $pro_field_teasers as $teaser ) : ?>
						<div class="bm-fb-field-type bm-fb-pro-field-teaser" title="<?php echo esc_attr( $teaser['desc'] ); ?>">
							<span class="dashicons <?php echo esc_attr( $teaser['icon'] ); ?>"></span>
							<span class="bm-fb-ft-label"><?php echo esc_html( $teaser['label'] ); ?></span>
							<span class="sg-pro-badge" style="font-size:9px;margin-left:auto;"><?php esc_html_e( 'Pro', 'service-booking' ); ?></span>
						</div>
					<?php endforeach; ?>
					<a href="<?php echo esc_url( Booking_Management_Limits::get_pro_upsell_url() ); ?>" target="_blank" class="button bm-fb-pro-cta" style="width:100%;text-align:center;margin-top:8px;">
						<?php esc_html_e( 'Upgrade to Pro →', 'service-booking' ); ?>
					</a>
				</div>
			</div>

			<!-- Templates tab -->
			<div class="bm-fb-tab-content" id="bm-fb-tab-templates" style="display:none;">
				<div class="bm-fb-sidebar-header">
					<h3><span class="dashicons dashicons-layout"></span> <?php esc_html_e( 'Pre-built Templates', 'service-booking' ); ?></h3>
					<p class="description"><?php esc_html_e( 'Click a template to apply it to the form.', 'service-booking' ); ?></p>
				</div>

				<div class="bm-fb-template-list" id="bm-fb-template-list">
					<?php foreach ( $templates as $tpl_key => $tpl ) : ?>
						<div class="bm-fb-template-card" data-template="<?php echo esc_attr( $tpl_key ); ?>">
							<div class="bm-fb-template-icon">
								<span class="dashicons <?php echo esc_attr( $tpl['icon'] ); ?>"></span>
							</div>
							<div class="bm-fb-template-info">
								<strong><?php echo esc_html( $tpl['name'] ); ?></strong>
								<small><?php echo esc_html( $tpl['desc'] ); ?></small>
							</div>
						</div>
					<?php endforeach; ?>
				</div>

				<div class="bm-fb-sidebar-promo">
					<span class="dashicons dashicons-lock" style="color:#7c3aed;"></span>
					<p>
						<strong><?php esc_html_e( '2,000+ Premium Templates', 'service-booking' ); ?></strong><br/>
						<small><?php esc_html_e( 'Access thousands of professional templates with Pro.', 'service-booking' ); ?></small>
					</p>
					<a href="<?php echo esc_url( Booking_Management_Limits::get_pro_upsell_url() ); ?>" target="_blank" class="button" style="width:100%;text-align:center;margin-top:8px;"><?php esc_html_e( 'Upgrade to Pro →', 'service-booking' ); ?></a>
				</div>
			</div>
		</div>

		<!-- Right Panel: Form Canvas -->
		<div class="bm-fb-canvas-wrap">
			<div class="bm-fb-canvas" id="bm-fb-canvas">
				<div class="bm-fb-canvas-header">
					<h3><?php echo esc_html( $form_name ); ?></h3>
					<?php if ( ! empty( $form_desc ) ) : ?>
						<p class="description"><?php echo esc_html( $form_desc ); ?></p>
					<?php endif; ?>
				</div>

				<div class="bm-fb-fields-list" id="bm-fb-fields-list">
					<?php if ( ! empty( $fields ) ) : ?>
						<?php foreach ( $fields as $field ) :
							$field_options = isset( $field->field_options ) ? maybe_unserialize( $field->field_options ) : array();
							$is_visible    = isset( $field_options['is_visible'] ) ? (int) $field_options['is_visible'] : 1;
							$is_def        = isset( $field_options['is_default'] ) ? (int) $field_options['is_default'] : 0;
							$placeholder   = isset( $field_options['placeholder'] ) ? $field_options['placeholder'] : '';
							$field_width   = isset( $field_options['field_width'] ) ? $field_options['field_width'] : 'full';
							$is_required   = isset( $field->is_required ) ? (int) $field->is_required : 0;
							?>
							<div class="bm-fb-field-card <?php echo $field_width === 'half' ? 'bm-fb-half' : 'bm-fb-full'; ?> <?php echo ! $is_visible ? 'bm-fb-hidden-field' : ''; ?>"
								 data-field-id="<?php echo esc_attr( $field->id ); ?>"
								 data-field-type="<?php echo esc_attr( $field->field_type ); ?>">
								<div class="bm-fb-field-drag-handle">
									<span class="dashicons dashicons-move"></span>
								</div>
								<div class="bm-fb-field-content">
									<label class="bm-fb-field-label">
										<?php echo esc_html( $field->field_label ); ?>
										<?php if ( $is_required ) : ?>
											<span class="bm-fb-required">*</span>
										<?php endif; ?>
									</label>
									<?php
									// Render a dummy input based on field type.
									switch ( $field->field_type ) {
										case 'textarea':
											echo '<textarea class="bm-fb-dummy-input" placeholder="' . esc_attr( $placeholder ) . '" disabled rows="3"></textarea>';
											break;
										case 'select':
											echo '<select class="bm-fb-dummy-input" disabled><option>' . esc_html( $placeholder ? $placeholder : '— ' . __( 'Select', 'service-booking' ) . ' —' ) . '</option></select>';
											break;
										case 'checkbox':
											echo '<label class="bm-fb-dummy-check"><input type="checkbox" disabled /> ' . esc_html( $placeholder ? $placeholder : __( 'Option', 'service-booking' ) ) . '</label>';
											break;
										case 'radio':
											echo '<label class="bm-fb-dummy-check"><input type="radio" disabled /> ' . esc_html( $placeholder ? $placeholder : __( 'Option', 'service-booking' ) ) . '</label>';
											break;
										case 'file':
											echo '<div class="bm-fb-dummy-file"><span class="dashicons dashicons-upload"></span> ' . esc_html__( 'Choose file', 'service-booking' ) . '</div>';
											break;
										case 'hidden':
											echo '<div class="bm-fb-dummy-hidden"><span class="dashicons dashicons-hidden"></span> ' . esc_html__( 'Hidden field', 'service-booking' ) . '</div>';
											break;
										case 'gdpr_consent':
											$consent_text = ! empty( $placeholder ) ? $placeholder : __( 'I consent to the storage and processing of my personal data.', 'service-booking' );
											echo '<label class="bm-fb-dummy-check bm-fb-gdpr-check"><input type="checkbox" disabled /> <span>' . esc_html( $consent_text ) . '</span></label>';
											break;
										default:
											$input_type = in_array( $field->field_type, array( 'email', 'tel', 'url', 'password', 'number', 'date', 'time' ), true ) ? $field->field_type : 'text';
											echo '<input type="' . esc_attr( $input_type ) . '" class="bm-fb-dummy-input" placeholder="' . esc_attr( $placeholder ) . '" disabled />';
											break;
									}
									?>
								</div>
								<div class="bm-fb-field-actions">
									<?php if ( ! $is_visible ) : ?>
										<span class="dashicons dashicons-hidden bm-fb-field-hidden-icon" title="<?php esc_attr_e( 'This field is hidden', 'service-booking' ); ?>"></span>
									<?php endif; ?>
									<?php if ( $is_def ) : ?>
										<span class="bm-fb-badge bm-fb-badge-default" style="font-size:10px;"><?php esc_html_e( 'Default', 'service-booking' ); ?></span>
									<?php endif; ?>
									<button type="button" class="bm-fb-field-edit" data-field-id="<?php echo esc_attr( $field->id ); ?>" title="<?php esc_attr_e( 'Edit Field', 'service-booking' ); ?>">
										<span class="dashicons dashicons-admin-generic"></span>
									</button>
									<button type="button" class="bm-fb-field-remove" data-field-id="<?php echo esc_attr( $field->id ); ?>" title="<?php esc_attr_e( 'Remove Field', 'service-booking' ); ?>">
										<span class="dashicons dashicons-trash"></span>
									</button>
								</div>
							</div>
						<?php endforeach; ?>
					<?php else : ?>
						<div class="bm-fb-empty-state" id="bm-fb-empty-state">
							<span class="dashicons dashicons-feedback" style="font-size:48px;width:48px;height:48px;color:#ccc;"></span>
							<p><?php esc_html_e( 'No fields yet. Click a field type from the sidebar or apply a template to get started.', 'service-booking' ); ?></p>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<!-- Field Settings Panel (slides in from right) -->
		<div class="bm-fb-settings-panel" id="bm-fb-settings-panel">
			<div class="bm-fb-settings-header">
				<h3 id="bm-fb-settings-title"><?php esc_html_e( 'Field Settings', 'service-booking' ); ?></h3>
				<button type="button" class="bm-fb-settings-close" id="bm-fb-settings-close" title="<?php esc_attr_e( 'Close', 'service-booking' ); ?>">
					<span class="dashicons dashicons-no-alt"></span>
				</button>
			</div>
			<div class="bm-fb-settings-body" id="bm-fb-settings-body">
				<p class="bm-fb-settings-placeholder"><?php esc_html_e( 'Select a field to edit its settings.', 'service-booking' ); ?></p>
			</div>
			<div class="bm-fb-settings-footer" id="bm-fb-settings-footer" style="display:none;">
				<button type="button" class="button button-primary bm-fb-save-field-btn" id="bm-fb-save-field-btn">
					<?php esc_html_e( 'Save Field', 'service-booking' ); ?>
				</button>
				<button type="button" class="button bm-fb-cancel-field-btn" id="bm-fb-cancel-field-btn">
					<?php esc_html_e( 'Cancel', 'service-booking' ); ?>
				</button>
			</div>
		</div>
	</div>

</div>

<!-- Preview Modal -->
<div id="bm-fb-preview-modal" class="bm-fb-modal-overlay" style="display:none;">
	<div class="bm-fb-modal">
		<div class="bm-fb-modal-header">
			<h3><?php esc_html_e( 'Form Preview', 'service-booking' ); ?></h3>
			<button type="button" class="bm-fb-modal-close" id="bm-fb-preview-close"><span class="dashicons dashicons-no-alt"></span></button>
		</div>
		<div class="bm-fb-modal-body" id="bm-fb-preview-body"></div>
	</div>
</div>

<!-- Toast notifications -->
<div class="bm-fb-toast" id="bm-fb-toast" style="display:none;"></div>

<!-- Hidden data for JS -->
<input type="hidden" id="bm-fb-form-id" value="<?php echo esc_attr( $form_id ); ?>" />
<input type="hidden" id="bm-fb-nonce" value="<?php echo esc_attr( wp_create_nonce( 'ajax-nonce' ) ); ?>" />
<input type="hidden" id="bm-fb-ajax-url" value="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" />
<input type="hidden" id="bm-fb-rest-url" value="<?php echo esc_url( rest_url( 'sg-booking/v1/' ) ); ?>" />
<input type="hidden" id="bm-fb-rest-nonce" value="<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>" />

<div class="loader_modal"></div>
