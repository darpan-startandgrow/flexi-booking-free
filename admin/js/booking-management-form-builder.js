/**
 * Form Builder JavaScript
 *
 * Handles drag-and-drop field reordering, click-to-add fields, field editing
 * via settings panel, basic conditional logic, validation rules, pre-built
 * templates, GDPR consent, live preview, and save operations.
 *
 * @since 1.3.0
 */
(function ($) {
	'use strict';

	var BmFormBuilder = {
		formId: 0,
		nonce: '',
		ajaxUrl: '',
		restUrl: '',
		restNonce: '',
		activeFieldId: null,

		/**
		 * Initialise the form builder.
		 */
		init: function () {
			this.formId  = $('#bm-fb-form-id').val();
			this.nonce   = $('#bm-fb-nonce').val();
			this.ajaxUrl   = $('#bm-fb-ajax-url').val();
			this.restUrl   = $('#bm-fb-rest-url').val() || '';
			this.restNonce = $('#bm-fb-rest-nonce').val() || '';

			this.initSortable();
			this.bindEvents();
		},

		/**
		 * Perform a REST API request via the admin-action bridge.
		 */
		restRequest: function (action, data, successCb, errorCb) {
			var url = this.restUrl
				? this.restUrl + 'admin-action/' + action
				: this.ajaxUrl;

			var settings = {
				url: url,
				method: 'POST',
				data: data,
				success: function (res) {
					var parsed;
					try {
						parsed = typeof res === 'string' ? JSON.parse(res) : res;
					} catch (e) {
						parsed = null;
					}
					if (successCb) { successCb(parsed, res); }
				},
				error: errorCb || $.noop
			};

			if (BmFormBuilder.restUrl && BmFormBuilder.restNonce) {
				settings.beforeSend = function (xhr) {
					xhr.setRequestHeader('X-WP-Nonce', BmFormBuilder.restNonce);
				};
			}

			return $.ajax(settings);
		},

		/**
		 * Make the field list sortable (drag-and-drop reorder).
		 */
		initSortable: function () {
			$('#bm-fb-fields-list').sortable({
				items: '.bm-fb-field-card',
				handle: '.bm-fb-field-drag-handle',
				placeholder: 'bm-fb-field-card bm-fb-sortable-placeholder',
				tolerance: 'pointer',
				cursor: 'grabbing',
				opacity: 0.9,
				revert: 100,
				update: function () {
					BmFormBuilder.saveFieldOrder();
				}
			});
		},

		/**
		 * Bind all UI events.
		 */
		bindEvents: function () {
			// Click on free sidebar field type — blocked in free version (Pro feature).
			$(document).on('click', '.bm-fb-field-type-free', function (e) {
				e.preventDefault();
				BmFormBuilder.showToast(bmFbI18n.add_field_pro || 'Adding new fields is a Pro feature.', 'error');
			});

			// Sidebar tabs.
			$(document).on('click', '.bm-fb-sidebar-tab', function () {
				$('.bm-fb-sidebar-tab').removeClass('active');
				$(this).addClass('active');
				var tab = $(this).data('tab');
				$('.bm-fb-tab-content').hide();
				$('#bm-fb-tab-' + tab).show();
			});

			// Template cards — blocked in free version (Pro feature).
			$(document).on('click', '.bm-fb-template-card', function () {
				BmFormBuilder.showToast(bmFbI18n.templates_pro || 'Pre-built templates are a Pro feature.', 'error');
			});

			// Edit field button.
			$(document).on('click', '.bm-fb-field-edit', function (e) {
				e.preventDefault();
				e.stopPropagation();
				var fieldId = $(this).data('field-id');
				BmFormBuilder.openFieldSettings(fieldId);
			});

			// Remove field button — blocked in free version (default fields cannot be deleted).
			$(document).on('click', '.bm-fb-field-remove', function (e) {
				e.preventDefault();
				e.stopPropagation();
				BmFormBuilder.showToast(bmFbI18n.delete_field_pro || 'Default billing fields cannot be deleted in the free version.', 'error');
			});

			// Also open settings on field card click (except drag handle and action buttons).
			$(document).on('click', '.bm-fb-field-card', function (e) {
				if ($(e.target).closest('.bm-fb-field-drag-handle, .bm-fb-field-edit, .bm-fb-field-remove').length) {
					return;
				}
				var fieldId = $(this).data('field-id');
				BmFormBuilder.openFieldSettings(fieldId);
			});

			// Close settings panel.
			$('#bm-fb-settings-close').on('click', function () {
				BmFormBuilder.closeFieldSettings();
			});

			$('#bm-fb-cancel-field-btn').on('click', function () {
				BmFormBuilder.closeFieldSettings();
			});

			// Save field settings.
			$('#bm-fb-save-field-btn').on('click', function () {
				BmFormBuilder.saveFieldSettings();
			});

			// Save form (reorder).
			$('#bm-fb-save-btn').on('click', function () {
				BmFormBuilder.saveFieldOrder();
			});

			// Preview.
			$('#bm-fb-preview-btn').on('click', function () {
				BmFormBuilder.openPreview();
			});

			$('#bm-fb-preview-close').on('click', function () {
				$('#bm-fb-preview-modal').fadeOut(200);
			});

			// Close modal on overlay click.
			$('#bm-fb-preview-modal').on('click', function (e) {
				if ($(e.target).is('.bm-fb-modal-overlay')) {
					$(this).fadeOut(200);
				}
			});
		},

		/**
		 * Add a new field to the canvas by type.
		 */
		addFieldToCanvas: function (type) {
			var label = type.charAt(0).toUpperCase() + type.slice(1).replace(/_/g, ' ');
			if (type === 'gdpr_consent') {
				label = 'GDPR Consent';
			}

			var fieldCount = $('#bm-fb-fields-list .bm-fb-field-card').length;

			var commonData = {
				id: 0,
				form_id: BmFormBuilder.formId,
				field_type: type,
				field_label: label,
				field_name: type + '_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5),
				field_desc: '',
				is_required: (type === 'gdpr_consent') ? 1 : 0,
				is_editable: 1,
				ordering: fieldCount,
				woocommerce_field: '',
				field_key: '',
				field_position: fieldCount
			};

			var conditional = {
				placeholder: '',
				default_value: '',
				custom_class: '',
				field_width: 'full',
				is_visible: 1,
				autocomplete: 1
			};

			if (type === 'gdpr_consent') {
				conditional.placeholder = bmFbI18n.gdpr_default_text || 'I consent to the storage and processing of my personal data.';
			}

			BmFormBuilder.restRequest('bm_save_field_and_setting', {
				nonce: BmFormBuilder.nonce,
				post: {
					common_data: commonData,
					conditional: conditional
				}
			}, function (data) {
				if (data && data.status === 'saved' && data.data) {
					BmFormBuilder.appendFieldCard(data.data, conditional);
					BmFormBuilder.showToast(bmFbI18n.field_added || 'Field added.', 'success');
				} else {
					BmFormBuilder.showToast((data && data.message) || bmFbI18n.save_error, 'error');
				}
			}, function () {
				BmFormBuilder.showToast(bmFbI18n.network_error, 'error');
			});
		},

		/**
		 * Append a new field card to the canvas after creation.
		 */
		appendFieldCard: function (fieldData, options) {
			// Remove empty state if present.
			$('#bm-fb-empty-state').remove();

			var type = fieldData.field_type || 'text';
			var label = fieldData.field_label || 'Field';
			var isRequired = parseInt(fieldData.is_required) === 1;
			var fieldId = fieldData.id;
			var width = (options && options.field_width === 'half') ? 'half' : 'full';
			var placeholder = (options && options.placeholder) || '';

			var html = '<div class="bm-fb-field-card bm-fb-' + width + '" data-field-id="' + BmFormBuilder.escAttr(fieldId) + '" data-field-type="' + BmFormBuilder.escAttr(type) + '">';
			html += '<div class="bm-fb-field-drag-handle"><span class="dashicons dashicons-move"></span></div>';
			html += '<div class="bm-fb-field-content">';
			html += '<label class="bm-fb-field-label">' + BmFormBuilder.escHtml(label);
			if (isRequired) { html += ' <span class="bm-fb-required">*</span>'; }
			html += '</label>';
			html += BmFormBuilder.renderDummyInput(type, placeholder);
			html += '</div>';
			html += '<div class="bm-fb-field-actions">';
			html += '<button type="button" class="bm-fb-field-edit" data-field-id="' + BmFormBuilder.escAttr(fieldId) + '" title="Edit"><span class="dashicons dashicons-admin-generic"></span></button>';
			html += '<button type="button" class="bm-fb-field-remove" data-field-id="' + BmFormBuilder.escAttr(fieldId) + '" title="Remove"><span class="dashicons dashicons-trash"></span></button>';
			html += '</div></div>';

			$('#bm-fb-fields-list').append(html);
		},

		/**
		 * Render dummy input HTML for a field type.
		 */
		renderDummyInput: function (type, placeholder) {
			switch (type) {
				case 'textarea':
					return '<textarea class="bm-fb-dummy-input" placeholder="' + BmFormBuilder.escAttr(placeholder) + '" disabled rows="3"></textarea>';
				case 'select':
					return '<select class="bm-fb-dummy-input" disabled><option>' + BmFormBuilder.escHtml(placeholder || '— Select —') + '</option></select>';
				case 'checkbox':
					return '<label class="bm-fb-dummy-check"><input type="checkbox" disabled /> ' + BmFormBuilder.escHtml(placeholder || 'Option') + '</label>';
				case 'radio':
					return '<label class="bm-fb-dummy-check"><input type="radio" disabled /> ' + BmFormBuilder.escHtml(placeholder || 'Option') + '</label>';
				case 'file':
					return '<div class="bm-fb-dummy-file"><span class="dashicons dashicons-upload"></span> Choose file</div>';
				case 'hidden':
					return '<div class="bm-fb-dummy-hidden"><span class="dashicons dashicons-hidden"></span> Hidden field</div>';
				case 'gdpr_consent':
					var consentText = placeholder || (bmFbI18n.gdpr_default_text || 'I consent to the storage and processing of my personal data.');
					return '<label class="bm-fb-dummy-check bm-fb-gdpr-check"><input type="checkbox" disabled /> <span>' + BmFormBuilder.escHtml(consentText) + '</span></label>';
				default:
					var inputType = ['email', 'tel', 'url', 'password', 'number', 'date', 'time'].indexOf(type) !== -1 ? type : 'text';
					return '<input type="' + BmFormBuilder.escAttr(inputType) + '" class="bm-fb-dummy-input" placeholder="' + BmFormBuilder.escAttr(placeholder) + '" disabled />';
			}
		},

		/**
		 * Remove a field from the canvas and database.
		 */
		removeFieldFromCanvas: function (fieldId) {
			var $card = $('.bm-fb-field-card[data-field-id="' + fieldId + '"]');
			$card.fadeOut(200, function () {
				$(this).remove();
				BmFormBuilder.saveFieldOrder();
				if ($('#bm-fb-fields-list .bm-fb-field-card').length === 0) {
					$('#bm-fb-fields-list').append(
						'<div class="bm-fb-empty-state" id="bm-fb-empty-state">' +
						'<span class="dashicons dashicons-feedback" style="font-size:48px;width:48px;height:48px;color:#ccc;"></span>' +
						'<p>No fields yet. Click a field type from the sidebar or apply a template to get started.</p>' +
						'</div>'
					);
				}
			});
			BmFormBuilder.showToast(bmFbI18n.field_removed || 'Field removed.', 'success');
		},

		/**
		 * Apply a pre-built template by adding its fields sequentially.
		 */
		applyTemplate: function (template) {
			if (!template || !template.fields || !template.fields.length) {
				return;
			}

			var fields = template.fields;
			var idx = 0;

			function addNext() {
				if (idx >= fields.length) {
					BmFormBuilder.showToast(bmFbI18n.template_applied || 'Template applied.', 'success');
					return;
				}
				var f = fields[idx];
				idx++;

				var fieldCount = $('#bm-fb-fields-list .bm-fb-field-card').length;
				var commonData = {
					id: 0,
					form_id: BmFormBuilder.formId,
					field_type: f.type,
					field_label: f.label,
					field_name: f.type + '_' + Date.now() + '_' + idx,
					field_desc: '',
					is_required: f.required || 0,
					is_editable: 1,
					ordering: fieldCount,
					woocommerce_field: '',
					field_key: '',
					field_position: fieldCount
				};
				var conditional = {
					placeholder: '',
					default_value: '',
					custom_class: '',
					field_width: f.width || 'full',
					is_visible: 1,
					autocomplete: 1
				};
				if (f.type === 'gdpr_consent') {
					conditional.placeholder = bmFbI18n.gdpr_default_text || 'I consent to the storage and processing of my personal data.';
				}

				BmFormBuilder.restRequest('bm_save_field_and_setting', {
					nonce: BmFormBuilder.nonce,
					post: {
						common_data: commonData,
						conditional: conditional
					}
				}, function (data) {
					if (data && data.status === 'saved' && data.data) {
						BmFormBuilder.appendFieldCard(data.data, conditional);
					}
					addNext();
				}, function () {
					addNext();
				});
			}

			addNext();
		},

		/**
		 * Save the current field order via REST API.
		 */
		saveFieldOrder: function () {
			var order = [];
			$('#bm-fb-fields-list .bm-fb-field-card').each(function (index) {
				order.push({
					id: $(this).data('field-id'),
					position: index
				});
			});

			if (!order.length) {
				BmFormBuilder.showToast(bmFbI18n.save_error || 'No fields to save.', 'error');
				return;
			}

			BmFormBuilder.restRequest('bm_save_form_field_order', {
				nonce: BmFormBuilder.nonce,
				form_id: BmFormBuilder.formId,
				field_order: order
			}, function (data) {
				if (data && data.status === 'success') {
					BmFormBuilder.showToast(data.message || 'Field order saved.', 'success');
				} else {
					BmFormBuilder.showToast((data && data.message) || 'Could not save field order.', 'error');
				}
			}, function () {
				BmFormBuilder.showToast('Network error. Please try again.', 'error');
			});
		},

		/**
		 * Open the field settings panel for a specific field.
		 */
		openFieldSettings: function (fieldId) {
			if (!fieldId) {
				return;
			}

			// Highlight the active card.
			$('.bm-fb-field-card').removeClass('bm-fb-active');
			$('.bm-fb-field-card[data-field-id="' + fieldId + '"]').addClass('bm-fb-active');

			BmFormBuilder.activeFieldId = fieldId;

			// Show loading.
			$('#bm-fb-settings-body').html('<p style="text-align:center;padding:40px 0;color:#94a3b8;"><span class="dashicons dashicons-update" style="animation:rotation 1s infinite linear;"></span> Loading...</p>');
			$('#bm-fb-settings-panel').addClass('bm-fb-panel-open');
			$('#bm-fb-settings-footer').show();

			// Fetch field data via REST API.
			BmFormBuilder.restRequest('bm_get_field_settings', {
				nonce: BmFormBuilder.nonce,
				id: fieldId
			}, function (data) {
				if (data && data.common) {
					BmFormBuilder.renderFieldSettings(data.common, data.field_options || {});
				} else {
					$('#bm-fb-settings-body').html('<p style="text-align:center;color:#ef4444;">Could not load field settings.</p>');
				}
			}, function () {
				$('#bm-fb-settings-body').html('<p style="text-align:center;color:#ef4444;">Network error.</p>');
			});
		},

		/**
		 * Render the field settings form in the right panel.
		 */
		renderFieldSettings: function (common, options) {
			var type = common.field_type || 'text';
			var html = '';

			$('#bm-fb-settings-title').text(common.field_label || 'Field Settings');

			// Field Label.
			html += '<div class="bm-fb-setting-group">';
			html += '<label for="bm-fb-s-label">' + bmFbI18n.label + '</label>';
			html += '<input type="text" id="bm-fb-s-label" value="' + BmFormBuilder.escAttr(common.field_label || '') + '" />';
			html += '</div>';

			// Field Type (read-only).
			html += '<div class="bm-fb-setting-group">';
			html += '<label>' + bmFbI18n.type + '</label>';
			html += '<input type="text" value="' + BmFormBuilder.escAttr(type) + '" disabled style="background:#f8fafc;" />';
			html += '</div>';

			// Description.
			html += '<div class="bm-fb-setting-group">';
			html += '<label for="bm-fb-s-desc">' + bmFbI18n.description + '</label>';
			html += '<textarea id="bm-fb-s-desc" rows="2">' + BmFormBuilder.escHtml(common.field_desc || '') + '</textarea>';
			html += '</div>';

			// GDPR Consent Text (for gdpr_consent type).
			if (type === 'gdpr_consent') {
				html += '<div class="bm-fb-setting-group">';
				html += '<label for="bm-fb-s-placeholder">' + (bmFbI18n.consent_text || 'Consent Text') + '</label>';
				html += '<textarea id="bm-fb-s-placeholder" rows="3">' + BmFormBuilder.escHtml(options.placeholder || bmFbI18n.gdpr_default_text || '') + '</textarea>';
				html += '</div>';
			}

			// Placeholder (if applicable).
			if (type !== 'gdpr_consent' && ['text', 'email', 'tel', 'url', 'password', 'textarea', 'number', 'date', 'time', 'select'].indexOf(type) !== -1) {
				html += '<div class="bm-fb-setting-group">';
				html += '<label for="bm-fb-s-placeholder">' + bmFbI18n.placeholder + '</label>';
				html += '<input type="text" id="bm-fb-s-placeholder" value="' + BmFormBuilder.escAttr(options.placeholder || '') + '" />';
				html += '</div>';
			}

			// Default Value.
			if (['text', 'email', 'tel', 'url', 'number', 'hidden', 'password'].indexOf(type) !== -1) {
				html += '<div class="bm-fb-setting-group">';
				html += '<label for="bm-fb-s-default">' + bmFbI18n.default_value + '</label>';
				html += '<input type="text" id="bm-fb-s-default" value="' + BmFormBuilder.escAttr(options.default_value || '') + '" />';
				html += '</div>';
			}

			// CSS Class.
			html += '<div class="bm-fb-setting-group">';
			html += '<label for="bm-fb-s-class">' + bmFbI18n.css_class + '</label>';
			html += '<input type="text" id="bm-fb-s-class" value="' + BmFormBuilder.escAttr(options.custom_class || '') + '" />';
			html += '<span class="description">' + bmFbI18n.css_class_desc + '</span>';
			html += '</div>';

			// Field Width.
			html += '<div class="bm-fb-setting-group">';
			html += '<label for="bm-fb-s-width">' + bmFbI18n.field_width + '</label>';
			html += '<select id="bm-fb-s-width">';
			html += '<option value="full"' + ((!options.field_width || options.field_width === 'full') ? ' selected' : '') + '>' + bmFbI18n.full_width + '</option>';
			html += '<option value="half"' + (options.field_width === 'half' ? ' selected' : '') + '>' + bmFbI18n.half_width + '</option>';
			html += '</select>';
			html += '</div>';

			// Toggle: Required (hidden for certain types).
			if (['button', 'submit', 'hidden'].indexOf(type) === -1) {
				html += '<div class="bm-fb-setting-toggle">';
				html += '<label for="bm-fb-s-required">' + bmFbI18n.required + '</label>';
				html += '<span class="bm-fb-toggle-switch">';
				html += '<input type="checkbox" id="bm-fb-s-required"' + (parseInt(common.is_required) === 1 ? ' checked' : '') + ' />';
				html += '<span class="bm-fb-toggle-slider"></span>';
				html += '</span>';
				html += '</div>';
			}

			// Toggle: Visible.
			if (['button', 'submit', 'hidden'].indexOf(type) === -1) {
				html += '<div class="bm-fb-setting-toggle">';
				html += '<label for="bm-fb-s-visible">' + bmFbI18n.visible + '</label>';
				html += '<span class="bm-fb-toggle-switch">';
				html += '<input type="checkbox" id="bm-fb-s-visible"' + ((typeof options.is_visible === 'undefined' || parseInt(options.is_visible) === 1) ? ' checked' : '') + ' />';
				html += '<span class="bm-fb-toggle-slider"></span>';
				html += '</span>';
				html += '</div>';
			}

			// --- Basic Conditional Logic (show/hide fields) ---
			if (['button', 'submit', 'hidden'].indexOf(type) === -1) {
				var condField  = (options.conditional_field) || '';
				var condOp     = (options.conditional_operator) || 'is_equal_to';
				var condVal    = (options.conditional_value) || '';
				var condEnable = (options.conditional_enabled) ? 1 : 0;

				html += '<div class="bm-fb-setting-section">';
				html += '<h4 class="bm-fb-section-title"><span class="dashicons dashicons-randomize"></span> ' + (bmFbI18n.conditional_logic || 'Conditional Logic') + '</h4>';
				html += '<div class="bm-fb-setting-toggle">';
				html += '<label for="bm-fb-s-cond-enable">' + (bmFbI18n.show_field_when || 'Show this field when') + '</label>';
				html += '<span class="bm-fb-toggle-switch">';
				html += '<input type="checkbox" id="bm-fb-s-cond-enable"' + (condEnable ? ' checked' : '') + ' />';
				html += '<span class="bm-fb-toggle-slider"></span>';
				html += '</span>';
				html += '</div>';
				html += '<div class="bm-fb-conditional-rules" id="bm-fb-conditional-rules" style="' + (condEnable ? '' : 'display:none;') + '">';

				// Field selector (populated from existing fields on canvas).
				html += '<div class="bm-fb-setting-group">';
				html += '<select id="bm-fb-s-cond-field">';
				html += '<option value="">' + (bmFbI18n.select_field || 'Select a field') + '</option>';
				$('#bm-fb-fields-list .bm-fb-field-card').each(function () {
					var fId = $(this).data('field-id');
					var fLabel = $(this).find('.bm-fb-field-label').text().replace(/\s*\*\s*$/, '').trim();
					if (String(fId) !== String(common.id)) {
						html += '<option value="' + BmFormBuilder.escAttr(fId) + '"' + (String(condField) === String(fId) ? ' selected' : '') + '>' + BmFormBuilder.escHtml(fLabel) + '</option>';
					}
				});
				html += '</select>';
				html += '</div>';

				// Operator.
				html += '<div class="bm-fb-setting-group">';
				html += '<select id="bm-fb-s-cond-op">';
				html += '<option value="is_equal_to"' + (condOp === 'is_equal_to' ? ' selected' : '') + '>' + (bmFbI18n.is_equal_to || 'is equal to') + '</option>';
				html += '<option value="is_not_equal_to"' + (condOp === 'is_not_equal_to' ? ' selected' : '') + '>' + (bmFbI18n.is_not_equal_to || 'is not equal to') + '</option>';
				html += '<option value="is_empty"' + (condOp === 'is_empty' ? ' selected' : '') + '>' + (bmFbI18n.is_empty || 'is empty') + '</option>';
				html += '<option value="is_not_empty"' + (condOp === 'is_not_empty' ? ' selected' : '') + '>' + (bmFbI18n.is_not_empty || 'is not empty') + '</option>';
				html += '</select>';
				html += '</div>';

				// Value.
				html += '<div class="bm-fb-setting-group" id="bm-fb-cond-val-wrap">';
				html += '<input type="text" id="bm-fb-s-cond-val" placeholder="' + (bmFbI18n.enter_value || 'Enter value') + '" value="' + BmFormBuilder.escAttr(condVal) + '" />';
				html += '</div>';

				html += '</div>'; // .bm-fb-conditional-rules
				html += '</div>'; // .bm-fb-setting-section
			}

			// --- Basic Validation Rules ---
			if (['text', 'email', 'tel', 'url', 'password', 'textarea', 'number'].indexOf(type) !== -1) {
				var minLen  = (options.validation_min_length) || '';
				var maxLen  = (options.validation_max_length) || '';
				var pattern = (options.validation_pattern) || '';
				var errMsg  = (options.validation_error_message) || '';

				html += '<div class="bm-fb-setting-section">';
				html += '<h4 class="bm-fb-section-title"><span class="dashicons dashicons-shield"></span> ' + (bmFbI18n.validation_rules || 'Validation Rules') + '</h4>';

				html += '<div class="bm-fb-setting-group">';
				html += '<label for="bm-fb-s-min-len">' + (bmFbI18n.min_length || 'Minimum Length') + '</label>';
				html += '<input type="number" id="bm-fb-s-min-len" min="0" value="' + BmFormBuilder.escAttr(minLen) + '" />';
				html += '</div>';

				html += '<div class="bm-fb-setting-group">';
				html += '<label for="bm-fb-s-max-len">' + (bmFbI18n.max_length || 'Maximum Length') + '</label>';
				html += '<input type="number" id="bm-fb-s-max-len" min="0" value="' + BmFormBuilder.escAttr(maxLen) + '" />';
				html += '</div>';

				html += '<div class="bm-fb-setting-group">';
				html += '<label for="bm-fb-s-pattern">' + (bmFbI18n.pattern || 'Pattern (Regex)') + '</label>';
				html += '<input type="text" id="bm-fb-s-pattern" value="' + BmFormBuilder.escAttr(pattern) + '" />';
				html += '</div>';

				html += '<div class="bm-fb-setting-group">';
				html += '<label for="bm-fb-s-err-msg">' + (bmFbI18n.custom_error || 'Custom Error Message') + '</label>';
				html += '<input type="text" id="bm-fb-s-err-msg" value="' + BmFormBuilder.escAttr(errMsg) + '" />';
				html += '</div>';

				html += '</div>'; // .bm-fb-setting-section
			}

			// Hidden field to track the field id.
			html += '<input type="hidden" id="bm-fb-s-field-id" value="' + common.id + '" />';
			html += '<input type="hidden" id="bm-fb-s-field-type" value="' + BmFormBuilder.escAttr(type) + '" />';
			html += '<input type="hidden" id="bm-fb-s-field-name" value="' + BmFormBuilder.escAttr(common.field_name || '') + '" />';
			html += '<input type="hidden" id="bm-fb-s-field-key" value="' + BmFormBuilder.escAttr(common.field_key || '') + '" />';
			html += '<input type="hidden" id="bm-fb-s-ordering" value="' + BmFormBuilder.escAttr(common.ordering || '0') + '" />';
			html += '<input type="hidden" id="bm-fb-s-field-position" value="' + BmFormBuilder.escAttr(common.field_position || '0') + '" />';

			$('#bm-fb-settings-body').html(html);

			// Bind conditional logic toggle.
			$('#bm-fb-s-cond-enable').on('change', function () {
				if ($(this).is(':checked')) {
					$('#bm-fb-conditional-rules').slideDown(200);
				} else {
					$('#bm-fb-conditional-rules').slideUp(200);
				}
			});

			// Show/hide value field based on operator.
			$('#bm-fb-s-cond-op').on('change', function () {
				var op = $(this).val();
				if (op === 'is_empty' || op === 'is_not_empty') {
					$('#bm-fb-cond-val-wrap').hide();
				} else {
					$('#bm-fb-cond-val-wrap').show();
				}
			}).trigger('change');
		},

		/**
		 * Save field settings via AJAX.
		 */
		saveFieldSettings: function () {
			var fieldId   = $('#bm-fb-s-field-id').val();
			var fieldType = $('#bm-fb-s-field-type').val();

			if (!fieldId) {
				return;
			}

			var commonData = {
				id:             fieldId,
				field_type:     fieldType,
				field_label:    $('#bm-fb-s-label').val(),
				field_name:     $('#bm-fb-s-field-name').val(),
				field_desc:     $('#bm-fb-s-desc').val(),
				is_required:    $('#bm-fb-s-required').is(':checked') ? 1 : 0,
				is_editable:    1,
				ordering:       $('#bm-fb-s-ordering').val(),
				woocommerce_field: '',
				field_key:      $('#bm-fb-s-field-key').val(),
				field_position: $('#bm-fb-s-field-position').val()
			};

			var conditional = {
				placeholder:   $('#bm-fb-s-placeholder').val() || '',
				default_value: $('#bm-fb-s-default').val() || '',
				custom_class:  $('#bm-fb-s-class').val() || '',
				field_width:   $('#bm-fb-s-width').val() || 'full',
				is_visible:    $('#bm-fb-s-visible').is(':checked') ? 1 : 0,
				autocomplete:  1
			};

			// Conditional logic data.
			if ($('#bm-fb-s-cond-enable').length) {
				conditional.conditional_enabled  = $('#bm-fb-s-cond-enable').is(':checked') ? 1 : 0;
				conditional.conditional_field    = $('#bm-fb-s-cond-field').val() || '';
				conditional.conditional_operator = $('#bm-fb-s-cond-op').val() || '';
				conditional.conditional_value    = $('#bm-fb-s-cond-val').val() || '';
			}

			// Validation rules data.
			if ($('#bm-fb-s-min-len').length) {
				conditional.validation_min_length    = $('#bm-fb-s-min-len').val() || '';
				conditional.validation_max_length    = $('#bm-fb-s-max-len').val() || '';
				conditional.validation_pattern       = $('#bm-fb-s-pattern').val() || '';
				conditional.validation_error_message = $('#bm-fb-s-err-msg').val() || '';
			}

			var postData = {
				common_data: commonData,
				conditional: conditional
			};

			$('#bm-fb-save-field-btn').prop('disabled', true).text(bmFbI18n.saving);

			BmFormBuilder.restRequest('bm_save_field_and_setting', {
				nonce: BmFormBuilder.nonce,
				post: postData
			}, function (data) {
				$('#bm-fb-save-field-btn').prop('disabled', false).text(bmFbI18n.save_field);

				if (data && (data.status === 'saved' || data.status === 'updated')) {
					BmFormBuilder.showToast(bmFbI18n.field_saved, 'success');
					BmFormBuilder.updateFieldCard(fieldId, commonData, conditional);
				} else {
					BmFormBuilder.showToast((data && data.message) || bmFbI18n.save_error, 'error');
				}
			}, function () {
				$('#bm-fb-save-field-btn').prop('disabled', false).text(bmFbI18n.save_field);
				BmFormBuilder.showToast(bmFbI18n.network_error, 'error');
			});
		},

		/**
		 * Update a field card in the canvas after saving.
		 */
		updateFieldCard: function (fieldId, common, options) {
			var $card = $('.bm-fb-field-card[data-field-id="' + fieldId + '"]');
			if (!$card.length) {
				return;
			}

			// Update label.
			var labelHtml = BmFormBuilder.escHtml(common.field_label || '');
			if (parseInt(common.is_required) === 1) {
				labelHtml += ' <span class="bm-fb-required">*</span>';
			}
			$card.find('.bm-fb-field-label').html(labelHtml);

			// Update width.
			$card.removeClass('bm-fb-full bm-fb-half');
			$card.addClass(options.field_width === 'half' ? 'bm-fb-half' : 'bm-fb-full');

			// Update visibility.
			if (parseInt(options.is_visible) === 0) {
				$card.addClass('bm-fb-hidden-field');
				if (!$card.find('.bm-fb-field-hidden-icon').length) {
					$card.find('.bm-fb-field-actions').prepend('<span class="dashicons dashicons-hidden bm-fb-field-hidden-icon" title="Hidden"></span>');
				}
			} else {
				$card.removeClass('bm-fb-hidden-field');
				$card.find('.bm-fb-field-hidden-icon').remove();
			}

			// Update placeholder on dummy input.
			$card.find('.bm-fb-dummy-input').attr('placeholder', options.placeholder || '');

			// Update GDPR consent text.
			if ($card.data('field-type') === 'gdpr_consent') {
				$card.find('.bm-fb-gdpr-check span').text(options.placeholder || bmFbI18n.gdpr_default_text || '');
			}
		},

		/**
		 * Close the field settings panel.
		 */
		closeFieldSettings: function () {
			$('#bm-fb-settings-panel').removeClass('bm-fb-panel-open');
			$('#bm-fb-settings-footer').hide();
			$('.bm-fb-field-card').removeClass('bm-fb-active');
			BmFormBuilder.activeFieldId = null;
		},

		/**
		 * Open the form preview modal.
		 */
		openPreview: function () {
			var $body = $('#bm-fb-preview-body');
			$body.html('<p style="text-align:center;padding:40px 0;color:#94a3b8;"><span class="dashicons dashicons-update" style="animation:rotation 1s infinite linear;"></span> ' + bmFbI18n.loading + '</p>');
			$('#bm-fb-preview-modal').fadeIn(200);

			BmFormBuilder.restRequest('bm_fetch_preview_form', {
				nonce: BmFormBuilder.nonce
			}, function (data, rawRes) {
				// Preview returns HTML, use raw response.
				var html = (typeof rawRes === 'string') ? rawRes : '';
				if (html && html.length > 10) {
					$body.html(html);
				} else if (data && typeof data === 'object' && data.html) {
					$body.html(data.html);
				} else {
					$body.html('<p style="text-align:center;color:#ef4444;">' + bmFbI18n.preview_error + '</p>');
				}
			}, function () {
				$body.html('<p style="text-align:center;color:#ef4444;">' + bmFbI18n.network_error + '</p>');
			});
		},

		/**
		 * Show a toast notification.
		 */
		showToast: function (message, type) {
			var $toast = $('#bm-fb-toast');
			$toast.removeClass('bm-fb-toast-success bm-fb-toast-error');
			$toast.addClass('bm-fb-toast-' + (type || 'success'));
			$toast.text(message).fadeIn(300);
			setTimeout(function () {
				$toast.fadeOut(300);
			}, 3000);
		},

		/**
		 * Escape HTML.
		 */
		escHtml: function (str) {
			if (!str) return '';
			var div = document.createElement('div');
			div.appendChild(document.createTextNode(str));
			return div.innerHTML;
		},

		/**
		 * Escape attribute.
		 */
		escAttr: function (str) {
			if (!str) return '';
			return String(str)
				.replace(/&/g, '&amp;')
				.replace(/"/g, '&quot;')
				.replace(/'/g, '&#039;')
				.replace(/</g, '&lt;')
				.replace(/>/g, '&gt;');
		}
	};

	$(document).ready(function () {
		if ($('#bm-form-builder-wrap').length) {
			BmFormBuilder.init();
		}
	});
})(jQuery);
