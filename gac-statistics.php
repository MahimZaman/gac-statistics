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

if (!defined('ABSPATH')) {
   return;
}

define('gstat_path', trailingslashit(plugin_dir_path(__FILE__)));
define('gstat_url', trailingslashit(plugin_dir_url(__FILE__)));

class GAC_Statistics
{
   public function __construct()
   {
      add_action('admin_enqueue_scripts', [$this, 'gstat_admin_script']);
      add_action('admin_menu', [$this, 'gstat_admin_menu']);

      add_action('init', [$this, 'gstat_create_table']);
      add_action('gac_coach_request_accepted', [$this, 'gstat_coach_request']);

      add_action('init', [$this, 'gstat_cron_schedular']);
      add_action('gstat_cron_collector', [$this, 'gstat_cron_callback']);

      add_action('gac_coach_request_accepted', [$this, 'gstat_add_remove_patient']);

      add_action('gac_prescription_updated', [$this, 'gstat_prescription_update']);

      register_deactivation_hook(__FILE__, 'gstat_deactivator');
   }

   function gstat_prescription_update($data, $patient_id){
      $coach_id = $data['coach_id'];
      $past_data = $data['past_data'];
      $updated_data = $data['updated_data'];

      $old_status = $past_data['status'];
      $new_status = $updated_data['status'];

      $patient_id = $data['patient_id'];

      $coach_new_patients = get_user_meta($coach_id, 'gstat_prescriptions', true) ? get_user_meta($coach_id, 'gstat_prescriptions', true) : [] ;

      if($old_status == 'deactive' && $new_status == 'active'){
         $coach_new_patients[] = [
            'date' => date('Y-m-d'),
            'patient_id' => $patient_id,
         ];
      }

   }

   function gstat_add_remove_patient($old_coach, $new_coach, $patient){
      $lost_patients = get_user_meta($old_coach, 'gstat_user_lost', true) ? get_user_meta($old_coach, 'gstat_user_lost', true) : [];
      $lost_patients[] = array(
         'date' => date('Y-m-d'),
         'patient_id' => $patient
      );
      update_user_meta($old_coach, 'gstat_user_lost', $lost_patients);
      $gained_patients = get_user_meta($old_coach, 'gstat_user_gain', true) ? get_user_meta($old_coach, 'gstat_user_gain', true) : [];
      $gained_patients[] = array(
         'date' => date('Y-m-d'),
         'patient_id' => $patient
      );
      update_user_meta($new_coach, 'gstat_user_gain', $gained_patients);
   }

   function gstat_deactivator()
   {
      $timestamp = wp_next_scheduled('gstat_cron_collector');
      wp_unschedule_event($timestamp, 'gstat_cron_collector');
   }

   function gstat_cron_schedular()
   {
      if (!wp_next_scheduled('gstat_cron_collector')) {
         wp_schedule_event(time(), 'hourly', 'gstat_cron_collector');
      }
   }

   function gstat_generator($array){
      foreach($array as $item){
         yield $item ;
      }
   }

   function gstat_cron_callback()
   {
      include gstat_path . 'includes/cron-callback.php';
   }

   function gstat_create_table()
   {
      global $wpdb;

      $sql_array = [
         "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}gstat_data` (
            id int NOT NULL AUTO_INCREMENT, 
            coach_id int, 
            active_prescriptions LONGTEXT,
            new_prescriptions LONGTEXT,
            expired_prescriptions LONGTEXT, 
            registered_patients LONGTEXT, 
            new_patients LONGTEXT,
            lost_patients LONGTEXT,
            current_balance float(100, 2),
            total_commission float(100, 2),
            date DATETIME,
            PRIMARY KEY(id)
         )"
      ];

      foreach ($sql_array as $sql) {
         $res = $wpdb->query($sql);
      }
   }

   function gstat_admin_script()
   {
      wp_enqueue_style('gstat-fa-css', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css');
      wp_enqueue_style('gstat-admin-css', gstat_url . 'assets/admin.css');
      wp_enqueue_script('gstat-admin-js', gstat_url . 'assets/admin.js');
   }

   function gstat_admin_menu()
   {
      add_menu_page('Sales', 'GAC Statistics', 'manage_options', 'gstat_sales', [$this, 'gstat_sales_page'], 'dashicons-chart-line', 99);
      add_submenu_page('gstat_sales', 'Analytics', 'Analytics', 'manage_options', 'gstat_analytics', [$this, 'gstat_analytics_page'], null);
   }

   function gstat_sales_page()
   {
      include(gstat_path . 'templates/sales.php');
   }

   function gstat_analytics_page()
   {
      include(gstat_path . 'templates/analytics.php');
   }

   function gstat_coach_request($old_coach, $new_coach, $user)
   {
   }
}

new GAC_Statistics();
