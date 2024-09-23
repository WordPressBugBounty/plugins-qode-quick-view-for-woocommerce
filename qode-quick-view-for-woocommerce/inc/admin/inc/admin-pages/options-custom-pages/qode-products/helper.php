<?php
if ( ! defined( 'ABSPATH' ) ) {
	// Exit if accessed directly.
	exit;
}

if ( ! function_exists( 'qode_quick_view_for_woocommerce_get_list_of_other_plugins' ) ) {
	/**
	 * Function that return list of QODE plugins
	 */
	function qode_quick_view_for_woocommerce_get_list_of_other_plugins() {

		$current_plugin = basename( plugin_dir_path( QODE_QUICK_VIEW_FOR_WOOCOMMERCE_PLUGIN_BASE_FILE ) );

		$plugins         = array();
		$transient_name  = 'qode_quick_view_for_woocommerce_qode_products' . str_replace( '.', '_', QODE_QUICK_VIEW_FOR_WOOCOMMERCE_VERSION );
		$transient_value = get_transient( $transient_name );

		if ( false !== $transient_value ) {
			$plugins = $transient_value;
		} else {

			$url = 'https://export.qodethemes.com/qode-plugins/qode-list-of-plugins.txt';

			$response = wp_remote_get( $url );

			if ( is_wp_error( $response ) ) {
				return $response;
			}

			$code = wp_remote_retrieve_response_code( $response );

			if ( 200 === $code ) {
				$body         = wp_remote_retrieve_body( $response );
				$body_decoded = json_decode( $body, true );

				if ( ! empty( $body_decoded ) || is_array( $body_decoded ) ) {

					if ( isset( $body_decoded[ $current_plugin ] ) ) {
						unset( $body_decoded[ $current_plugin ] );
					}

					set_transient( $transient_name, $body_decoded, WEEK_IN_SECONDS );

					return $body_decoded;
				}
			}
		}

		return $plugins;
	}
}

if ( ! function_exists( 'qode_quick_view_for_woocommerce_get_plugin_by_slug_from_others_plugins' ) ) {
	/**
	 * Function that return list of QODE plugins
	 */
	function qode_quick_view_for_woocommerce_get_plugin_by_slug_from_others_plugins( $slug ) {
		$plugin  = array();
		$plugins = qode_quick_view_for_woocommerce_get_list_of_other_plugins();

		if ( isset( $plugins[ $slug ] ) ) {
			$plugin = $plugins[ $slug ];
		}

		return $plugin;
	}
}

if ( ! function_exists( 'qode_quick_view_for_woocommerce_plugin_installation' ) ) {
	function qode_quick_view_for_woocommerce_plugin_installation() {

		if ( isset( $_POST ) && isset( $_POST['plugin'] ) ) {
			$plugin_key = sanitize_text_field( wp_unslash( $_POST['plugin'] ) );
			check_ajax_referer( 'qode-quick-view-for-woocommerce-install-' . $plugin_key, 'nonce' );

			$plugin = qode_quick_view_for_woocommerce_get_plugin_by_slug_from_others_plugins( $plugin_key );

			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			$plugin_action = isset( $_POST['pluginAction'] ) ? sanitize_text_field( wp_unslash( $_POST['pluginAction'] ) ) : '';
			$plugin_slug   = isset( $_POST['version'] ) && 'free' === sanitize_text_field( wp_unslash( $_POST['version'] ) ) ? $plugin['slug'] : $plugin['premium_slug'];
			$download_url  = $plugin['download_url'];

			if ( 'install' === $plugin_action ) {

				ob_start();
				include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
				wp_cache_flush();

				$skin     = new WP_Ajax_Upgrader_Skin();
				$upgrader = new Plugin_Upgrader( $skin );

				$install_result = $upgrader->install( $download_url );

				if ( ! is_wp_error( $install_result ) && $install_result ) {
					$activate = activate_plugin( $plugin_slug, '', false, true );

					if ( null === $activate ) {
						$button = qode_quick_view_for_woocommerce_plugin_get_plugin_link( $plugin_key, $plugin );
						qode_quick_view_for_woocommerce_get_ajax_status( 'success', esc_html__( 'Installed and activated', 'qode-quick-view-for-woocommerce' ), array( 'button' => $button ) );
					}
				}
			} else {
				$activate = activate_plugin( $plugin_slug, '', false, true );

				if ( null === $activate ) {

					$button = qode_quick_view_for_woocommerce_plugin_get_plugin_link( $plugin_key, $plugin );
					qode_quick_view_for_woocommerce_get_ajax_status( 'success', esc_html__( 'Activated', 'qode-quick-view-for-woocommerce' ), array( 'button' => $button ) );
				}
			}

			wp_die();
		}
	}
	add_action( 'wp_ajax_qode_quick_view_for_woocommerce_plugin_installation', 'qode_quick_view_for_woocommerce_plugin_installation' );
}

if ( ! function_exists( 'qode_quick_view_for_woocommerce_plugin_get_plugin_link' ) ) {

	function qode_quick_view_for_woocommerce_plugin_get_plugin_link( $plugin_key, $plugin ) {

		$params = array(
			'plugin_key' => $plugin_key,
		);
		$status = qode_quick_view_for_woocommerce_plugin_status( $plugin );

		switch ( $status ) :
			case 'installed':
				$params = array(
					'class'   => 'qodef-install-plugin',
					'action'  => 'activate',
					'version' => 'free',
					'label'   => esc_html__( 'Activate', 'qode-quick-view-for-woocommerce' ),
				);
				break;
			case 'activated':
				$params = array(
					'class'   => 'qodef-buy-plugin',
					'action'  => 'upgrade',
					'version' => 'free',
					'label'   => esc_html__( 'Upgrade', 'qode-quick-view-for-woocommerce' ),
				);
				break;
			case 'installed_pro':
				$params = array(
					'class'   => 'qodef-install-plugin',
					'action'  => 'activate',
					'version' => 'pro',
					'label'   => esc_html__( 'Activate', 'qode-quick-view-for-woocommerce' ),
				);
				break;
			case 'activated_pro':
				$params = array(
					'class'   => 'qodef-installed-plugin',
					'action'  => 'nothing',
					'version' => 'pro',
					'label'   => esc_html__( 'Activated', 'qode-quick-view-for-woocommerce' ),
				);
				break;
			default:
				$params = array(
					'class'   => 'qodef-install-plugin',
					'action'  => 'install',
					'version' => 'free',
					'label'   => esc_html__( 'Get Free Version', 'qode-quick-view-for-woocommerce' ),
				);
				break;
		endswitch;

		$params['plugin_key'] = $plugin_key;
		$params['plugin_url'] = isset( $plugin['upgrade_url'] ) ? $plugin['upgrade_url'] : '';

		return qode_quick_view_for_woocommerce_framework_get_template_part( QODE_QUICK_VIEW_FOR_WOOCOMMERCE_ADMIN_PATH . '/inc', 'admin-pages/options-custom-pages/qode-products', 'templates/parts/plugin-link', '', $params );
	}
}
if ( ! function_exists( 'qode_quick_view_for_woocommerce_is_specific_plugin_installed' ) ) {
	function qode_quick_view_for_woocommerce_is_specific_plugin_installed( $plugin ) {
		$plugins = get_plugins();

		if ( isset( $plugins[ $plugin ] ) ) {
			return true;
		}

		return false;
	}
}

if ( ! function_exists( 'qode_quick_view_for_woocommerce_plugin_status' ) ) {
	function qode_quick_view_for_woocommerce_plugin_status( $plugin ) {

		$status       = '';
		$is_installed = qode_quick_view_for_woocommerce_is_specific_plugin_installed( $plugin['slug'] );

		if ( $is_installed ) {

			$status = 'installed';

			$is_activated = is_plugin_active( $plugin['slug'] );

			if ( $is_activated ) {
				$status           = 'activated';
				$is_installed_pro = qode_quick_view_for_woocommerce_is_specific_plugin_installed( $plugin['premium_slug'] );

				if ( $is_installed_pro ) {
					$status           = 'installed_pro';
					$is_activated_pro = is_plugin_active( $plugin['premium_slug'] );

					if ( $is_activated_pro ) {
						$status = 'activated_pro';
					}
				}
			}
		}

		return $status;
	}
}
