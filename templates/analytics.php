<?php
global $wpdb ; 
$results = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}gstat_data`");

if(isset($_POST['period_start']) && isset($_POST['period_end'])){
    $start = $_POST['period_start'] ;
    $end = $_POST['period_end'] ;

    $results = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}gstat_data` WHERE DATE(date) >= DATE('$start') AND DATE(date) <= DATE('$end')");
}
?>

<div class="wrap">
    <h2><?php _e('Analytics', 'gac-text'); ?></h2>
    <hr>
    <br>
    <div class="container">
        <form class="filter-period" action="" method="POST">
            <div class="gstat_field_group">
                <label for="period_start">From</label>
                <input type="date" name="period_start" id="period_start" value="<?php echo isset($_POST['period_start']) ? $_POST['period_start'] : ''?>" required>
            </div>
            <div class="gstat_field_group">
                <label for="period_end">To</label>
                <input type="date" name="period_end" id="period_end" value="<?php echo isset($_POST['period_end']) ? $_POST['period_end'] : ''?>" required>
            </div>
            <button type="submit" class="button button-primary"><?php _e('Filter', 'gac-text'); ?></button>
        </form>
        <table class="gstat-table">
            <thead>
                <tr>
                    <th><?php echo __('ID', 'gac-text');?></th>
                    <th><?php echo __('Full Name', 'gac-text');?></th>
                    <th><?php echo __('Email', 'gac-text');?></th>
                    <th width="120px"><?php echo __('Date', 'gac-text');?></th>
                    <th><?php echo __('Active Prescriptions', 'gac-text');?></th>
                    <th><?php echo __('New/Renewed Prescriptions', 'gac-text');?></th>
                    <th><?php echo __('Expired/Deactive Prescriptions', 'gac-text');?></th>
                    <th><?php echo __('Registered Patients', 'gac-text');?></th>
                    <th><?php echo __('New/Added Patients', 'gac-text');?></th>
                    <th><?php echo __('Lost Patients', 'gac-text');?></th>
                    <th><?php echo __('Current Balance', 'gac-text');?></th>
                    <th><?php echo __('Total Earned Commission', 'gac-text');?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!empty($results)) :
                    foreach ($this->gstat_generator($results) as $result) :
                        $coach_id = $result->coach_id ;
                        $coach = get_user_by('id', $coach_id);
                        $name = $coach->display_name ;
                        $email = $coach->user_email ;
                ?>
                        <tr>
                            <td><?php echo $coach_id; ?></td>
                            <td><?php echo $name; ?></td>
                            <td><?php echo $email; ?></td>
                            <td><?php echo date('F j, Y', strtotime($result->date)); ?></td>
                            <td><?php echo count(unserialize($result->active_prescriptions));?></td>
                            <td><?php echo count(unserialize($result->new_prescriptions));?></td>
                            <td><?php echo count(unserialize($result->expired_prescriptions));?></td>
                            <td><?php echo count(unserialize($result->registered_patients));?></td>
                            <td><?php echo count(unserialize($result->new_patients));?></td>
                            <td><?php echo count(unserialize($result->lost_patients));?></td>
                            <td><?php echo wc_price($result->current_balance);?></td>
                            <td><?php echo wc_price($result->total_commission);?></td>

                        </tr>
                <?php
                    endforeach;
                endif;
                ?>

            </tbody>
        </table>
    </div>
</div>