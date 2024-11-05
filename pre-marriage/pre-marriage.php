<?php

/**
 *
 * @wordpress-plugin
 * Plugin Name:       Pre Marriage
 * Plugin URI:        
 * Description:       Extension plugin for Formidible forms. 
 * Version:           1.0.0
 * Author:            Codepixelz Media
 * Author URI:        https://https://codepixelzmedia.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       pre-marrage
 * Domain Path:       /languages
 */


// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}


// enqueue public styles and scripts for plugin
if (!function_exists('cpm_pm_public_enqueue_scripts')) {
    add_action('wp_enqueue_scripts', 'cpm_pm_public_enqueue_scripts');
    function cpm_pm_public_enqueue_scripts()
    {
        wp_enqueue_style('cpm-pm-public-style', plugin_dir_url(__FILE__) . 'assets/css/public-style.css');
        wp_enqueue_media();
        wp_enqueue_script('cpm-pm-public-script', plugin_dir_url(__FILE__) . 'assets/js/public-script.js', array('jquery'), '1.0', true);
        wp_localize_script('cpm-pm-public-script', 'cpmAjax', array('ajax_url' => esc_url(admin_url('admin-ajax.php'))));
        wp_enqueue_script('html2pdf', 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.8.0/html2pdf.bundle.min.js');
    }
}

// enqueue admin styles and scripts for plugin
if (!function_exists('cpm_pm_admin_enqueue_scripts')) {
    add_action('admin_enqueue_scripts', 'cpm_pm_admin_enqueue_scripts');
    function cpm_pm_admin_enqueue_scripts()
    {
        wp_enqueue_style('cpm-pm-admin-style', plugin_dir_url(__FILE__) . 'assets/css/admin-style.css',);
    }
}


include(plugin_dir_path(__FILE__) . 'inc/functions.php');

