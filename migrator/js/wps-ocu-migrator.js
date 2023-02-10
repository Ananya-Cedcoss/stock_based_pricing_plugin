/**
 * Update Scripts for migration
 */
 jQuery(document).ready( function($) {

	const ajaxUrl        = wps_ocu_migrator_obj.ajaxUrl;
	const nonce          = wps_ocu_migrator_obj.nonce;
	const settings       = wps_ocu_migrator_obj.data.settings;
	const pages          = wps_ocu_migrator_obj.data.pages;
	const metas          = wps_ocu_migrator_obj.data.metas;
	const settings_count = settings.length;
	const pages_count    = pages.length;
	const metas_count    = metas.length;
	const action         = 'process_ajax_events';

	/**
	 * Step One : Prompt to initialise the migration.
	 */
	const promptMigrationIsInitiating = () => {
		Swal.fire({
			icon: 'warning',
			title: 'We Have got ' + settings_count + ' Saved Settings!',
			text: 'Click to start import',
			showCloseButton: true,
			showCancelButton: true,
			allowOutsideClick: false,
			focusConfirm: false,
			confirmButtonText:
			  '<i class="fa fa-thumbs-up"></i> Great!',
			confirmButtonAriaLabel: 'Thumbs up, great!',
		  }).then((stater) => {
			if (stater.isConfirmed) {
				Swal.fire({
					title   : 'Settings are being imported!',
					allowOutsideClick: false,
					html    : 'Please do not reload/close this page until prompted.',
					footer  : '<span class="order-progress-report">' + settings_count + ' are left to import',
					didOpen: () => {
						Swal.showLoading()
					}
				});
				startImportSettings( settings );
			}
		})
	}

	/**
	 * Step Two : Migrate options.
	 */
	const startImportSettings = ( settings ) => {
		var event   = 'import_single_option';
		var request = { action, event, nonce, settings };
		jQuery.post( ajaxUrl , request ).done(function( response ){
			settings = response;
		}).then(
		function( settings ) {
			count = Object.keys(settings).length;
			jQuery('.order-progress-report').text( count + ' are left to import' );
			if( ! jQuery.isEmptyObject(settings) ) {
				startImportSettings(settings);
			} else {
				Swal.fire({
					title   : 'Orders/Reports are being imported!',
					allowOutsideClick: false,
					html    : 'Please do not reload/close this page until prompted.',
					footer  : '<span class="order-progress-report">' + metas_count + ' are left to import',
					didOpen: () => {
						Swal.showLoading()
					}
				});

				// All orders imported!
				startImportOrders( metas );
			}
		}, function(error) {
			console.error(error);
		});
	}

	/**
	 * Step Three : Migrate postmeta.
	 */
	const startImportOrders = ( metas ) => {
		var event   = 'import_single_meta';
		var request = { action, event, nonce, metas };
		jQuery.post( ajaxUrl , request ).done(function( response ){
			metas = response;
		}).then(
		function( metas ) {
			count = Object.keys(metas).length;
			jQuery('.order-progress-report').text( count + ' are left to import' );
			if( ! jQuery.isEmptyObject(metas) ) {
				startImportOrders(metas);
			} else {
				startStripeOrders();
			}
		}, function(error) {
			console.error(error);
		});
	}

	/**
	 * Step Four : Change custom stripe payment method value in all orders
	 */
	const startStripeOrders = () => {
		var event   = 'import_stripe_meta';
		var request = { action, event, nonce };
		jQuery.post( ajaxUrl , request ).done(function( response ){
		}).then(
		function() {

			Swal.fire({
				title   : 'Shortcodes are being imported!',
				html    : 'Please do not reload/close this page until prompted.',
				allowOutsideClick: false,
				footer  : '<span class="shortcodes-progress-report">' + pages_count + ' are left to import',
				didOpen: () => {
					Swal.showLoading()
				}
			});
			startImportPages(pages);
		}, function(error) {
			console.error(error);
		});
	}

	/**
	 * Step Three : Migrate postmeta.
	 */
	 const startImportPages = ( pages ) => {
		var event   = 'import_single_page';
		var request = { action, event, nonce, pages };
		jQuery.post( ajaxUrl , request ).done(function( response ){
			pagess = response;
		}).then(
		function( pagess ) {
			pagess_count = Object.keys(pagess).length;
			jQuery('.shortcodes-progress-report').text( pagess_count + ' are left to import' );
			if( ! jQuery.isEmptyObject(pagess) ) {
				startImportPages(pagess);
			} else {
				Swal.fire('Import Completed', '', 'success').then(() => {
					window.location.reload();
				});
			}
		}, function(error) {
			console.error(error);
		});
	}

    // Initiate Migration
	$(document).on('click', '.wps_wocuf_pro_init_migration', function(e) {
		e.preventDefault();
		promptMigrationIsInitiating();		
	});

	// end of scripts.
});
