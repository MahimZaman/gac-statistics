<?php
global $wpdb ;

$sql = [] ;

$coaches = get_users(
    array(
        'role' => array('coach'),
    )
);

if (!empty($coaches)) {
    foreach ($this->gstat_generator($coaches) as $coach) {
        $date = date('Y-m-d');
        $coach_id = $coach->ID;

        /** Data : Active Patients */
        $active_patients =  get_users(array(
            'role' => 'customer',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => '_gac_pres_status',
                    'value' => 'active',
                    'compare' => '='
                ),
                array(
                    'key' => 'gac_coach_id',
                    'value' => $coach_id,
                    'compare' => '='
                ),
            ),
            'fields' => 'ID'
        ));
        $active_patients = serialize($active_patients);

        /** Data : Deactive Patients */
        $deactive_patients = get_users(array(
            'role' => 'customer',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => '_gac_pres_status',
                    'value' => 'deactive',
                    'compare' => '='
                ),
                array(
                    'key' => 'gac_coach_id',
                    'value' => $coach_id,
                    'compare' => '='
                ),
            ),
            'fields' => 'ID'
        ));
        $deactive_patients = serialize($deactive_patients);

        /** Data : Registered Patients */
        $registered_patients = get_users(array(
            'role' => 'customer',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'gac_coach_id',
                    'value' => $coach_id,
                    'compare' => '='
                ),
            ),
            'fields' => 'ID'
        ));
        $registered_patients = serialize($registered_patients);

        /** Data : Current Balance */
        $current_balance = get_user_meta($coach_id, 'gac_commission_total', true) ? get_user_meta($coach_id, 'gac_commission_total', true) : 0;

        /** Data : Total Commission */
        $all_patients = array_merge(unserialize($registered_patients), unserialize($active_patients), unserialize($deactive_patients));
        $all_patients = array_unique($all_patients);
        $commission_total = 0;

        foreach ($this->gstat_generator($all_patients) as $patient) {
            $patient_comm = get_user_meta($coach_id, 'gac_commission_from_' . $patient, true) ? floatval(get_user_meta($coach_id, 'gac_commission_from_' . $patient, true)) : 0;
            $commission_total += (float)$patient_comm;
        }

        /** Data : Patients Gained */
        $patient_gain = get_user_meta($coach_id, 'gstat_user_gain', true) ? get_user_meta($coach_id, 'gstat_user_gain', true) : [];
        $gained = [];

        foreach ($this->gstat_generator($patient_gain) as $item) {
            if (strtotime($item['date']) == strtotime($date)) {
                $gained[] = $item['patient_id'];
            }
        }

        $gained = serialize($gained);

        /** Data : Patients Lost */
        $patient_lost = get_user_meta($coach_id, 'gstat_user_lost', true) ? get_user_meta($coach_id, 'gstat_user_lost', true) : [];
        $lost = [];

        foreach ($this->gstat_generator($patient_lost) as $item) {
            if (strtotime($item['date']) == strtotime($date)) {
                $lost[] = $item['patient_id'];
            }
        }

        $lost = serialize($lost);

        /* Data : New/Renewed Prescriptions */
        $coach_new_patients = get_user_meta($coach_id, 'gstat_prescriptions', true) ? get_user_meta($coach_id, 'gstat_prescriptions', true) : [];

        $new_patients = [] ;

        foreach($this->gstat_generator($coach_new_patients) as $array){
            if(strtotime($date) == strtotime($array['date'])){
                $new_patients[] = $array['patient_id'];
            }
        }

        $new_patients = serialize($new_patients);

        $data_date = date('Y-m-d');

        $exists = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}gstat_data` WHERE active_prescriptions = '$active_patients' AND new_prescriptions = '$new_patients' AND expired_prescriptions = '$deactive_patients' AND registered_patients = '$registered_patients' AND new_patients = '$gained' AND lost_patients = '$lost' AND current_balance = '$current_balance' AND total_commission = '$commission_total'");

        if(empty($exists)){
            $sql[] = "INSERT INTO `{$wpdb->prefix}gstat_data` (id, coach_id, active_prescriptions, new_prescriptions, expired_prescriptions, registered_patients, new_patients, lost_patients, current_balance, total_commission, date) VALUES (NULL, '$coach_id', '$active_patients', '$new_patients', '$deactive_patients', '$registered_patients', '$gained', '$lost', '$current_balance', '$commission_total', '$data_date')";
        }

    }

    foreach($this->gstat_generator($sql) as $query){
        $wpdb->query($query);
    }

}
