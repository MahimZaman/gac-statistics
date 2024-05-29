<?php
/*
 * Plugin Name:       GAC - Statistics Extension
 * Plugin URI:        https://github.com/MahimZaman/gac-statistics
 * Description:       Handle the basics with this plugin.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      8.0
 * Author:            Mahim Zaman
 * Author URI:        https://github.com/MahimZaman/gac-statistics
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://github.com/MahimZaman/gac-statistics
 * Text Domain:       gac-text
 * Domain Path:       /languages
 * Requires Plugins:  woocommerce, get-a-coach
 */

 if( !defined( 'ABSPATH' ) ){
    return ;
 }

 define('gstat_path', trailingslashit(plugin_dir_path(__FILE__)));
 define('gstat_url', trailingslashit(plugin_dir_url(__FILE__)));

 class GAC_Statistics{
    public function __construct(){
        add_action('admin_enqueue_scripts', array($this, 'gstat_admin_scripts'));
    }

    public function gstat_admin_scripts(){
        wp_enqueue_style('gstat_admin_css', gstat_url . 'assets/admin.css', array(), null, 'all');
        wp_enqueue_script('gstat_admin_js', gstat_url . 'assets/admin.js', array(), null, false);
    }
 }