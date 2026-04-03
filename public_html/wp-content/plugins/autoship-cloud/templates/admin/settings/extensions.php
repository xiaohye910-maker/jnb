<?php
/**
 * This template is used to display the Autoship Cloud extensions in the admin settings area.
 *
 * @package Autoship
 * @since 2.8.0
 */

// Extensions should be added to the extension list by plugin file name ( with or without the php extension ).
$extensions = autoship_get_custom_extensions( $autoship_settings );

// Get the current active plugins.
$active = get_option( 'active_plugins' );

$active_plugins = array();

foreach ( $active as $key => $value ) {
	$active_plugins[ wp_basename( $value ) ] = $value;
}

$extension_count = count( $extensions );

?>

<div class="asc-settings-column">
	<h3><i class="pi pi-th-large"></i> <?php echo esc_html( __( 'Extensions', 'autoship' ) ); ?></h3>
	<p class="asc-card-description"><?php echo esc_html( __( 'Third-party plugins that extend your Autoship Cloud functionality. These plugins are not reviewed or monitored by Patterns In the Cloud LLC.', 'autoship' ) ); ?></p>

	<?php if ( empty( $extensions ) ) : ?>
		<div class="asc-alert asc-alert-info">
			<i class="pi pi-info-circle"></i>
			<div class="asc-alert-content">
				<?php echo esc_html( __( 'No extensions are currently active. Extensions registered with Autoship will appear here automatically.', 'autoship' ) ); ?>
			</div>
		</div>
	<?php else : ?>
		<div class="asc-extensions-header">
			<span class="asc-badge"><?php echo esc_html( $extension_count ); ?></span>
			<span><?php echo esc_html( _n( 'Active Extension', 'Active Extensions', $extension_count, 'autoship' ) ); ?></span>
		</div>

		<div class="asc-extensions-list">
			<?php
			foreach ( $extensions as $extension ) {

				$extension_name = '.php' === substr( $extension, - 4 ) ? substr( $extension, - 4 ) : $extension;

				if ( ! isset( $active_plugins[ $extension_name . '.php' ] ) ) {
					continue;
				}

				$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $active_plugins[ $extension_name . '.php' ] );

				$plugin_meta = array();

				if ( ! empty( $plugin_data['Author'] ) ) {
                    $plugin_meta[] = sprintf( 'By <a href="%s">%s</a>', $plugin_data['AuthorURI'] ?? 'https://www.qpilot.cloud', $plugin_data['Author'] ?? 'Publisher' );
				}

				// Details link using API info, if available.
				if ( isset( $plugin_data['slug'] ) && current_user_can( 'install_plugins' ) ) {
					$plugin_meta[] = sprintf(
						'<a href="%s" class="thickbox open-plugin-details-modal" aria-label="%s" data-title="%s">%s</a>',
						esc_url( network_admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . $plugin_data['slug'] . '&TB_iframe=true&width=600&height=550' ) ),
						/* translators: %s: plugin name */
						esc_attr( sprintf( __( 'More information about %s', 'autoship' ), $plugin_data['name'] ) ),
						esc_attr( $plugin_data['name'] ),
						__( 'View details', 'autoship' )
					);
				} elseif ( ! empty( $plugin_data['PluginURI'] ) ) {
					$plugin_meta[] = sprintf( '<a href="%s">%s</a>', esc_url( $plugin_data['PluginURI'] ), __( 'Visit plugin site', 'autoship' ) );
				}

				/**
				 * Filters the array of row meta for each plugin in the Autoship Extension Plugins list table.
				 *
				 * @param string[] $plugin_meta An array of the plugin's metadata.
				 * @param array $plugin_data An array of plugin data.
				 */


				$plugin_meta = apply_filters( 'autoship_plugin_row_meta', $plugin_meta, $plugin_data );

				?>

				<div class="asc-extension-card">
					<div class="asc-extension-header">
						<div class="asc-extension-title">
							<i class="pi pi-box"></i>
							<span><?php echo esc_html( $plugin_data['Name'] ); ?></span>
						</div>
						<?php if ( ! empty( $plugin_data['Version'] ) ) : ?>
							<span class="asc-extension-version"><?php echo esc_html( sprintf( __( 'v%s', 'autoship' ), $plugin_data['Version'] ) ); ?></span>
						<?php endif; ?>
					</div>
					<div class="asc-extension-body">
						<p class="asc-extension-description"><?php echo wp_kses_post( $plugin_data['Description'] ); ?></p>
						<?php do_action( "autoship_{$extension_name}_plugin_row_additional_content", $extension_name, $plugin_meta, $plugin_data ); ?>
						<?php if ( ! empty( $plugin_meta ) ) : ?>
							<div class="asc-extension-meta">
								<?php echo wp_kses_post( implode( ' <span class="asc-meta-separator">•</span> ', $plugin_meta ) ); ?>
							</div>
						<?php endif; ?>
					</div>
				</div>

			<?php } ?>
		</div>
	<?php endif; ?>
</div>
