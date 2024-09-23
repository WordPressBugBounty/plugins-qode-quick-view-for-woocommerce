(function ( $ ) {
	'use strict';

	$( document ).ready(
		function () {
			qodefInstallOtherPlugins.init();
		}
	);

	var qodefInstallOtherPlugins = {
		init: function () {
			var pluginHolder = $( '.qodef-custom-page-plugin' );

			pluginHolder.each(
				function () {
					var $plugins = $( this );
					qodefInstallOtherPlugins.initItem( $plugins );
				}
			);

		},
		initItem: function ( $plugin ) {

			var link            = $plugin.find( '.qodef-install-plugin' ),
				action          = link.data( 'action' ),
				submittingLabel = '';

			if ( action === 'install' ) {
				submittingLabel = link.data( 'installing-label' );
			} else if ( action === 'activate' ) {
				submittingLabel = link.data( 'activating-label' );
			}

			link.on(
				'click',
				function () {
					link.text( submittingLabel )
					$.ajax(
						{
							type: 'POST',
							data: {
								action: 'qode_quick_view_for_woocommerce_plugin_installation',
								pluginAction: action,
								nonce: nonceHolder.val()
							},
							url: ajaxurl,
							success: function ( data ) {
								var response = $.parseJSON( data );

								if ( response.status === 'success' ) {
									submitButton.text( response.message );
								}
							}
						}
					);
				}
			);
		}
	}

})( jQuery );
