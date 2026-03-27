/**
 * Form Builder JavaScript
 *
 * Handles drag-and-drop field reordering, field editing via settings panel,
 * live preview, and save operations.
 *
 * @since 1.3.0
 */
(function ($) {
	'use strict';

	var BmFormBuilder = {
		formId: 0,
		nonce: '',
		ajaxUrl: '',
		activeFieldId: null,

		/**
		 * Initialise the form builder.
		 */
		init: function () {
			this.formId  = $('#bm-fb-form-id').val();
			this.nonce   = $('#bm-fb-nonce').val();
			this.ajaxUrl = $('#bm-fb-ajax-url').val();

			this.initSortable();
			this.bindEvents();
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
			// Edit field button.
			$(document).on('click', '.bm-fb-field-edit', function (e) {
				e.preventDefault();
				e.stopPropagation();
				var fieldId = $(this).data('field-id');
				BmFormBuilder.openFieldSettings(fieldId);
			});

			// Also open settings on field card click (except drag handle).
			$(document).on('click', '.bm-fb-field-card', function (e) {
				if ($(e.target).closest('.bm-fb-field-drag-handle').length) {
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
		 * Save the current field order via AJAX.
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
				return;
			}

			$.ajax({
				url: BmFormBuilder.ajaxUrl,
				method: 'POST',
				data: {
					action: 'bm_save_form_field_order',
					nonce: BmFormBuilder.nonce,
					form_id: BmFormBuilder.formId,
					field_order: order
				},
				success: function (res) {
					var data;
					try {
						data = typeof res === 'string' ? JSON.parse(res) : res;
					} catch (e) {
						data = null;
					}
					if (data && data.status === 'success') {
						BmFormBuilder.showToast(data.message || 'Field order saved.', 'success');
					} else {
						BmFormBuilder.showToast((data && data.message) || 'Could not save field order.', 'error');
					}
				},
				error: function () {
					BmFormBuilder.showToast('Network error. Please try again.', 'error');
				}
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

			// Fetch field data via AJAX.
			$.ajax({
				url: BmFormBuilder.ajaxUrl,
				method: 'POST',
				data: {
					action: 'bm_get_field_settings',
					nonce: BmFormBuilder.nonce,
					id: fieldId
				},
				success: function (res) {
					var data;
					try {
						data = typeof res === 'string' ? JSON.parse(res) : res;
					} catch (e) {
						data = null;
					}
					if (data && data.common) {
						BmFormBuilder.renderFieldSettings(data.common, data.field_options || {});
					} else {
						$('#bm-fb-settings-body').html('<p style="text-align:center;color:#ef4444;">Could not load field settings.</p>');
					}
				},
				error: function () {
					$('#bm-fb-settings-body').html('<p style="text-align:center;color:#ef4444;">Network error.</p>');
				}
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

			// Placeholder (if applicable).
			if (['text', 'email', 'tel', 'url', 'password', 'textarea', 'number', 'date', 'time', 'select'].indexOf(type) !== -1) {
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

			// Hidden field to track the field id.
			html += '<input type="hidden" id="bm-fb-s-field-id" value="' + common.id + '" />';
			html += '<input type="hidden" id="bm-fb-s-field-type" value="' + BmFormBuilder.escAttr(type) + '" />';
			html += '<input type="hidden" id="bm-fb-s-field-name" value="' + BmFormBuilder.escAttr(common.field_name || '') + '" />';
			html += '<input type="hidden" id="bm-fb-s-field-key" value="' + BmFormBuilder.escAttr(common.field_key || '') + '" />';
			html += '<input type="hidden" id="bm-fb-s-ordering" value="' + BmFormBuilder.escAttr(common.ordering || '0') + '" />';
			html += '<input type="hidden" id="bm-fb-s-field-position" value="' + BmFormBuilder.escAttr(common.field_position || '0') + '" />';

			$('#bm-fb-settings-body').html(html);
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

			var postData = {
				common_data: commonData,
				conditional: conditional
			};

			$('#bm-fb-save-field-btn').prop('disabled', true).text(bmFbI18n.saving);

			$.ajax({
				url: BmFormBuilder.ajaxUrl,
				method: 'POST',
				data: {
					action: 'bm_save_field_and_setting',
					nonce: BmFormBuilder.nonce,
					post: postData
				},
				success: function (res) {
					var data;
					try {
						data = typeof res === 'string' ? JSON.parse(res) : res;
					} catch (e) {
						data = null;
					}

					$('#bm-fb-save-field-btn').prop('disabled', false).text(bmFbI18n.save_field);

					if (data && (data.status === 'saved' || data.status === 'updated')) {
						BmFormBuilder.showToast(bmFbI18n.field_saved, 'success');
						BmFormBuilder.updateFieldCard(fieldId, commonData, conditional);
					} else {
						BmFormBuilder.showToast((data && data.message) || bmFbI18n.save_error, 'error');
					}
				},
				error: function () {
					$('#bm-fb-save-field-btn').prop('disabled', false).text(bmFbI18n.save_field);
					BmFormBuilder.showToast(bmFbI18n.network_error, 'error');
				}
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

			$.ajax({
				url: BmFormBuilder.ajaxUrl,
				method: 'POST',
				data: {
					action: 'bm_fetch_preview_form',
					nonce: BmFormBuilder.nonce
				},
				success: function (res) {
					if (res && res.length > 10) {
						$body.html(res);
					} else {
						$body.html('<p style="text-align:center;color:#ef4444;">' + bmFbI18n.preview_error + '</p>');
					}
				},
				error: function () {
					$body.html('<p style="text-align:center;color:#ef4444;">' + bmFbI18n.network_error + '</p>');
				}
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
