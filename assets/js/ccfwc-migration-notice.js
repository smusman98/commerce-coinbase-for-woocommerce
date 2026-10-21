jQuery( document ).ready(
	function ( $ ) {
		'use strict';

		$( document ).on(
			'click',
			'.ccfwc-business-migration-notice .notice-dismiss',
			function ( e ) {
				e.preventDefault();

				var $notice = $( this ).closest( '.ccfwc-business-migration-notice' );

				$.ajax(
					{
						url: ccfwcMigrationNotice.ajax_url,
						type: 'POST',
						data: {
							action: 'ccfwc_dismiss_business_migration_notice',
							nonce: ccfwcMigrationNotice.nonce,
						},
						success: function ( response ) {
							if ( response && response.success ) {
								$notice.fadeOut(
									300,
									function () {
										$( this ).remove();
									}
								);
							}
						},
					}
				);
			}
		);
	}
);
