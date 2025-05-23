<?php
/**
 * Plugin Name: Pdf Embed
 * Plugin URI:  https://www.francescopepe.com/
 * Description: PDF embedded with official Adobe API.
 * Version:     0.5.5
 * Author:      Tropicalista
 * Author URI:  https://www.francescopepe.com
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: pdf-embed
 *
 * @package     Formello/PdfEmbed
 */

require __DIR__ . '/vendor/autoload.php';

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/block-editor/how-to-guides/block-tutorial/writing-your-first-block-type/
 */
function pdf_embed_block_init() {
	register_block_type_from_metadata(
		__DIR__ . '/build'
	);
	$args = array(
		'type'              => 'object',
		'default'        => array(
			'apiKey'              => '',
			'embedMode'           => 'FULL_WINDOW',
			'defaultViewMode'     => 'FIT_PAGE',
			'height'              => '500px',
			'measurementId'       => '',
			'showDownloadPDF'     => false,
			'showPrintPDF'        => false,
			'showFullScreen'      => false,
			'showZoomControl'     => false,
			'showAnnotationTools' => false,
			'showBookmarks'       => false,
			'showThumbnails'      => false,
			'dockPageControls'    => false,
			'enableTextSelection' => false,
			'enableFormFilling'   => false,
			'enableLinearization' => false,
		),
		'show_in_rest' => array(
			'schema' => array(
				'type'  => 'object',
				'additionalProperties' => true,
			),
		),
	);
	register_setting( 'pdf_embed', 'pdf_embed', $args );
}
add_action( 'init', 'pdf_embed_block_init' );

/**
 * Register settings
 */
function pdf_embed_setting() {
	$options = get_option( 'pdf_embed', '' );

	wp_add_inline_script(
		'tropicalista-pdfembed-view-script',
		'const pdf_embed = ' . wp_json_encode( $options ),
		'before'
	);
	wp_add_inline_script(
		'tropicalista-pdfembed-editor-script',
		'const pdf_embed = ' . wp_json_encode( $options ),
		'before'
	);
}
add_action( 'init', 'pdf_embed_setting' );

/**
 * Adds pdf embed to button
 *
 * @param string $block_content The content.
 * @param array  $block The block.
 */
function pdf_embed_render( $block_content, $block ) {
	if ( ! empty( $block['attrs']['embedPdf'] ) ) {
		wp_enqueue_script( 'tropicalista-pdfembed-view-script' );
	}
	return $block_content;
}
add_filter( 'render_block', 'pdf_embed_render', 10, 2 );

/**
 * Initialize the plugin tracker
 *
 * @return void
 */
function pdf_embed_init_tracker() {

	if ( ! class_exists( 'Appsero\Client' ) ) {
		require_once __DIR__ . '/appsero/src/Client.php';
	}

	$client = new Appsero\Client( '48a870fe-75a1-476f-a4d6-bacc22ba54f1', 'Pdf Embed', __FILE__ );

	$client->insights()->init();
}
pdf_embed_init_tracker();

/**
 * Fires after tracking permission allowed (optin)
 *
 * @param array $data The Appsero data.
 *
 * @return void
 */
function pdf_embed_tracker_optin( $data ) {
	$data['project'] = 'pdf-embed';
	$response        = wp_remote_post(
		'https://hook.eu1.make.com/dplrdfggemll51whv3b21yjabuk8po0b',
		array(
			'headers'     => array( 'Content-Type' => 'application/json; charset=utf-8' ),
			'body'        => wp_json_encode( $data ),
			'method'      => 'POST',
			'data_format' => 'body',
		)
	);
}
add_action( 'pdf-embed_tracker_optin', 'pdf_embed_tracker_optin', 10 );
