/**
 * Deactivation feedback modal.
 *
 * Intercepts the "Deactivate" link on the Plugins page and shows a
 * keep/delete data prompt before proceeding.
 *
 * @package Booking_Management
 * @since   1.1.0
 */
(function ($) {
	'use strict';

	var config   = window.bm_deactivation || {};
	var i18n     = config.i18n || {};
	var deactivateUrl = '';

	/**
	 * Build and inject the modal markup into the page.
	 */
	function injectModal() {
		var html =
			'<div id="bm-deactivation-overlay"></div>' +
			'<div id="bm-deactivation-modal">' +
				'<h3>' + (i18n.title || 'Deactivate Plugin') + '</h3>' +
				'<p>' + (i18n.description || 'Keep or delete plugin data?') + '</p>' +
				'<div class="bm-deactivation-actions">' +
					'<button type="button" class="bm-btn-cancel">' + (i18n.cancel || 'Cancel') + '</button>' +
					'<button type="button" class="bm-btn-delete">' + (i18n['delete'] || 'Delete All Data') + '</button>' +
					'<button type="button" class="bm-btn-keep">' + (i18n.keep || 'Keep Data') + '</button>' +
				'</div>' +
			'</div>';

		$('body').append(html);
	}

	/**
	 * Show the modal.
	 */
	function showModal() {
		$('#bm-deactivation-overlay, #bm-deactivation-modal').fadeIn(200);
	}

	/**
	 * Hide the modal.
	 */
	function hideModal() {
		$('#bm-deactivation-overlay, #bm-deactivation-modal').fadeOut(200);
	}

	/**
	 * Proceed with deactivation by navigating to the original deactivate URL.
	 */
	function proceedDeactivation() {
		if (deactivateUrl) {
			window.location.href = deactivateUrl;
		}
	}

	$(function () {
		if (!config.plugin_basename) {
			return;
		}

		injectModal();

		// Intercept the deactivate link for our plugin.
		var $deactivateLink = $('tr[data-plugin="' + config.plugin_basename + '"] .deactivate a');

		if (!$deactivateLink.length) {
			// Fallback: find by href containing the plugin basename.
			$deactivateLink = $('a[href*="action=deactivate"][href*="' + encodeURIComponent(config.plugin_basename) + '"]');
		}

		$deactivateLink.on('click', function (e) {
			e.preventDefault();
			deactivateUrl = $(this).attr('href');
			showModal();
		});

		// Cancel button — close modal, stay on page.
		$(document).on('click', '.bm-btn-cancel, #bm-deactivation-overlay', function () {
			hideModal();
		});

		// Keep Data — just deactivate without deleting anything.
		$(document).on('click', '.bm-btn-keep', function () {
			hideModal();
			proceedDeactivation();
		});

		// Delete All Data — AJAX call then deactivate.
		$(document).on('click', '.bm-btn-delete', function () {
			if (!confirm(i18n.confirm || 'Are you sure? This cannot be undone.')) {
				return;
			}

			var $btn = $(this);
			$btn.prop('disabled', true).text(i18n.deleting || 'Deleting data…');

			$.post(config.ajax_url, {
				action: 'bm_delete_all_plugin_data',
				nonce: config.nonce
			}).always(function () {
				proceedDeactivation();
			});
		});
	});

})(jQuery);
