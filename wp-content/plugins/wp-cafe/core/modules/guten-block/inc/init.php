<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wp_version;
$filter_hook = "block_categories";
if( version_compare($wp_version, '5.8') >= 0){
	$filter_hook = "block_categories_all";
}

//register WP Cafe block category
function wpc_block_category( $categories, $post ) {
	return array_merge(
		$categories,
		array(
			array(
				'slug' => 'wp-cafe-blocks',
				'title' => esc_html__( 'WP Cafe', 'wpcafe' ),
			),
		)
	);
}
add_filter( $filter_hook , 'wpc_block_category', 10, 2);

//register block assets
function wpc_block_assets() {
    global $wp_version;

    if( version_compare($wp_version, '5.8') >= 0){
        $wp_editor = [ 'wp-block-editor'];
    } else{
        $wp_editor = [ 'wp-editor'];
    }

	// Register block styles for both frontend + backend.
	wp_register_style(
		'wpc-block-style-css',
		plugins_url( 'dist/blocks.style.build.css', dirname( __FILE__ ) ),
		is_admin() ? $wp_editor: null, null
	);

	// Register block editor styles for backend.
	wp_register_style(
			'wpc-block-editor-style-css',
			plugins_url( 'dist/blocks.editor.build.css', dirname( __FILE__ ) ),
			array( 'wp-edit-blocks' ),
			null
	);

	// Register block editor script for backend.
	wp_register_script(
		'wpc-block-js',
		plugins_url( '/dist/blocks.build.js', dirname( __FILE__ ) ), // Block.build.js: We register the block here. Built with Webpack.
		array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-compose', 'wp-server-side-render' ),
		null,
		true
	);

	// WP Localized globals. Use dynamic PHP stuff in JavaScript via `cgbGlobal` object.
	wp_localize_script(
		'wpc-block-js',
		'tsGlobal',
		[
			'pluginDirPath' => plugin_dir_path( __DIR__ ),
			'pluginDirUrl'  => plugin_dir_url( __DIR__ ),

		]
	);
}

// Hook: Block assets.
add_action( 'init', 'wpc_block_assets' );

//include food list block
if( file_exists( \Wpcafe::plugin_dir() . 'core/modules/guten-block/inc/blocks/food-list.php' )){
	include_once \Wpcafe::plugin_dir() . 'core/modules/guten-block/inc/blocks/food-list.php';
}

//include food tab block
if( file_exists( \Wpcafe::plugin_dir() . 'core/modules/guten-block/inc/blocks/food-tab.php' )){
	include_once \Wpcafe::plugin_dir() . 'core/modules/guten-block/inc/blocks/food-tab.php';
}
