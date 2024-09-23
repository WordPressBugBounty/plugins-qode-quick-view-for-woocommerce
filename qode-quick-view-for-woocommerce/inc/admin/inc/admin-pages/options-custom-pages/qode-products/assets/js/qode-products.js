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
			var button = $plugin.find( '.qodef-install-plugin' );

			if ( button.length ) {
				var action          = button.data( 'action' ),
					pluginKey       = button.data( 'plugin' ),
					pluginVersion   = button.data( 'version' ),
					submittingLabel = '';

				if ( action === 'install' ) {
					submittingLabel = button.data( 'installing-label' );
				} else if ( action === 'activate' ) {
					submittingLabel = button.data( 'activating-label' );
				}

				button.on(
					'click',
					function ( e ) {

						e.preventDefault();
						var nonce = $( this ).data( 'nonce' );
						button.text( submittingLabel );

						$.ajax(
							{
								type: 'POST',
								data: {
									action: 'qode_quick_view_for_woocommerce_plugin_installation',
									pluginAction: action,
									version: pluginVersion,
									plugin: pluginKey,
									nonce: nonce
								},
								url: ajaxurl,
								success: function ( data ) {
									var response = $.parseJSON( data );

									if ( response.status === 'success' ) {
										button.replaceWith( response.data.button );
									}
								}
							}
						);
					}
				);
			}
		}
	};

})( jQuery );
