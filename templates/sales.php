<?php
$page_no = isset($_POST['gstat_page']) && !empty($_POST['gstat_page']) ? intval($_POST['gstat_page']) : 1;

$orders = wc_get_orders([
    'status' => array('wc-processing', 'wc-on-hold', 'wc-cancelled', 'wc-completed'),
    'orderby' => 'date',
    'order' => 'DESC',
    'return' => 'ids',
    'limit' => 20,
    'paged' => $page_no
]);
?>
<div class="wrap gstat-wrap">
    <h2><?php _e("Sales Record", 'gac-text'); ?></h2>
    <hr>
    <br>
    <div class="container">
        <form action="<?php echo admin_url('/admin.php?page=gstat_sales'); ?>" method="POST" class="gstat-filter">
            <div class="gstat_field_group">
                <label for="product-type">Product Type:</label>
                <select name="product_type" id="product-type">
                    <option value="">Select a value</option>
                    <option value="prescription">Prescription</option>
                    <option value="no-prescription">No prescription</option>
                </select>
                <script>
                    jQuery("#product-type").val('<?php echo isset($_POST['product_type']) ? $_POST['product_type'] : ''; ?>').change();
                </script>
            </div>
            <div class="gstat_field_group">
                <label for="customer-type">Customer Type:</label>
                <select name="customer_type" id="customer-type">
                    <option value="">Select a value</option>
                    <option value="new">New</option>
                    <option value="repeating">Repeating</option>
                </select>
                <script>
                    jQuery("#customer-type").val('<?php echo isset($_POST['customer_type']) ? $_POST['customer_type'] : ''; ?>').change();
                </script>
            </div>
            <div class="gstat_field_group">
                <label for="patient-list">Patient List:</label>
                <select name="patient_list" id="patient-list">
                    <option value="">Select a value</option>
                    <option value="coach">In patient List</option>
                    <option value="no-coach">Not in patient list</option>
                </select>
                <script>
                    jQuery("#patient-list").val('<?php echo isset($_POST['patient_list']) ? $_POST['patient_list'] : ''; ?>').change();
                </script>
            </div>
            <div class="gstat_field_group">
                <label for="pres_status">Prescription Status:</label>
                <select name="pres_status" id="pres_status">
                    <option value="">Select a value</option>
                    <option value="active">Active</option>
                    <option value="deactive">Deactive</option>
                </select>
                <script>
                    jQuery("#pres_status").val('<?php echo isset($_POST['pres_status']) ? $_POST['pres_status'] : ''; ?>').change();
                </script>
            </div>
            <input type="hidden" name="gstat_page" id="gstat_page" value="1">
            <button type="submit" class="gstat-submit button button-secondary"><?php _e('Filter', 'gac-text'); ?></button>
        </form>
        <table class="gstat-table">
            <thead>
                <tr>
                    <!-- <th width="40px">Sn</th> -->
                    <th width="80px"><?php echo __('Order ID', 'gac-text'); ?></th>
                    <th width="120px"><?php echo __('Order Status', 'gac-text'); ?></th>
                    <th><?php echo __('Order Date', 'gac-text'); ?></th>
                    <th><?php echo __('Product Category', 'gac-text'); ?></th>
                    <th><?php echo __('Customer', 'gac-text'); ?></th>
                    <th><?php echo __('Customer Type', 'gac-text'); ?></th>
                    <th><?php echo __('Prescriber', 'gac-text'); ?></th>
                    <th><?php echo __('Pres. Status', 'gac-text'); ?></th>
                    <th><?php echo __('Taxes', 'gac-text'); ?></th>
                    <th><?php echo __('Refunds', 'gac-text'); ?></th>
                    <th><?php echo __('Shipping', 'gac-text'); ?></th>
                    <th><?php echo __('Order Total', 'gac-text'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($orders)) : ?>
                    <?php
                    $sn = 1;
                    $total_order_total = 0;
                    $total_tax = 0;
                    $total_shipping = 0;
                    $total_refunds = 0;
                    foreach ($this->gstat_generator($orders) as $order_id) :
                        $order = wc_get_order($order_id);
                        $user_id = $order->get_customer_id();
                        $user_purchase = wc_get_orders(array(
                            'customer_id' => $user_id,
                            'return' => 'ids'
                        ));
                        $coach_id = get_user_meta($user_id, 'gac_coach_id', true) ? get_user_meta($user_id, 'gac_coach_id', true) : 0;
                        $status = get_user_meta($user_id, '_gac_pres_status', true) ? get_user_meta($user_id, '_gac_pres_status', true) : '';
                        $is_prescription = 0;
                        foreach ($this->gstat_generator($order->get_items()) as $item_id => $item) {
                            $product = $item->get_product();
                            if (in_array(get_option('gac_category'), $product->get_category_ids() ? $product->get_category_ids() : [])) {
                                $is_prescription++;
                            }
                        }
                        $product_type = $is_prescription > 0 ? 'prescription' : 'no-prescription';
                        $customer_type = count($user_purchase) > 0 ? 'repeating' : 'new';
                        $patient_list = $coach_id > 0 ? 'coach' : 'no-coach';
                        $pres_status = $status ? $status : false;

                        $filter_pt = true;
                        $filter_ct = true;
                        $filter_pl = true;
                        $filter_ps = true;

                        if (isset($_POST['product_type']) && !empty($_POST['product_type'])) {
                            $filter_pt = $_POST['product_type'] == $product_type ? true : false;
                        }

                        if (isset($_POST['customer_type']) && !empty($_POST['customer_type'])) {
                            $filter_ct = $_POST['customer_type'] == $customer_type ? true : false;
                        }

                        if (isset($_POST['patient_list']) && !empty($_POST['patient_list'])) {
                            $filter_pl = $_POST['patient_list'] == $patient_list ? true : false;
                        }

                        if (isset($_POST['pres_status']) && !empty($_POST['pres_status'])) {
                            $filter_ps = $_POST['pres_status'] == $pres_status ? true : false;
                        }

                        if ($filter_pl && $filter_ps && $filter_pt && $filter_ct) :
                    ?>
                            <tr>
                                <!-- <td width="40px"><?php echo $sn; ?></td> -->
                                <td width="100px"><?php echo $order->get_id(); ?></td>
                                <td><span class="order-status <?php echo $order->get_status(); ?>"><?php echo str_replace('-', ' ', $order->get_status()); ?></span></td>
                                <td><?php echo date('F j, Y', strtotime($order->get_date_created())); ?></td>
                                <td>
                                    <ul>
                                        <?php echo $is_prescription > 0 ? 'Prescription' : 'Not Prescription'; ?>
                                        <!-- <?php
                                                foreach ($this->gstat_generator($order->get_items()) as $item_id => $item) {
                                                    $product = $item->get_product();
                                                    $name = $product->get_name();
                                                    $categories = wc_get_product_category_list($product->get_id(), $sep = ', ');;
                                                    $cat_array = [];

                                                    echo '<li>' . $name . ' (' . $categories . ')</li>';
                                                }
                                                ?> -->
                                    </ul>
                                </td>
                                <td><?php echo $order->get_formatted_billing_full_name(); ?></td>
                                <td>
                                    <?php
                                    if (count($user_purchase) > 0) {
                                        _e('Repeating', 'gac-text');
                                    } else {
                                        _e('New', 'gac-text');
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    if ($coach_id > 0) {
                                        echo get_user_by('id', $coach_id)->display_name;
                                    } else {
                                        echo '<span class="error">No Coach</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    echo $status ? '<span class="status-' . $status . '">' . ucwords($status) . '</span>' : '<span class="error">No prescription</span>';
                                    ?>
                                </td>
                                <td><?php echo wc_price($order->get_total_tax()); ?></td>
                                <td><?php echo wc_price($order->get_total_refunded()); ?></td>
                                <td><?php echo wc_price($order->get_shipping_total()); ?></td>
                                <td><?php echo wc_price($order->get_total()); ?></td>
                            </tr>
                    <?php
                            $sn++;
                            $total_order_total += $order->get_total();
                            $total_tax += $order->get_total_tax();
                            $total_shipping += $order->get_shipping_total();
                            $total_refunds += $order->get_total_refunded();
                        endif;
                    endforeach; ?>
            <tfoot>
                <tr>
                    <td style="text-align: left" colspan="8">Calculated Total : </td>
                    <td><?php echo wc_price($total_tax); ?></td>
                    <td><?php echo wc_price($total_refunds); ?></td>
                    <td><?php echo wc_price($total_shipping); ?></td>
                    <td><?php echo wc_price($total_order_total); ?></td>
                </tr>
                <tr>
                    <td colspan="12" align="right" style="background: #fff" class="pagination_column">
                        <?php
                        $pages = 0;
                        $page_orders = wc_get_orders([
                            'status' => array('wc-processing', 'wc-on-hold', 'wc-cancelled', 'wc-completed'),
                            'orderby' => 'date',
                            'order' => 'DESC',
                            'return' => 'ids',
                            'limit' => 20,
                            'paged' => isset($_POST['page']) ? (int) $_POST['page'] - 1 : 2
                        ]);
                        foreach ($this->gstat_generator($page_orders) as $order_id) :
                            $order = wc_get_order($order_id);
                            $user_id = $order->get_customer_id();
                            $user_purchase = wc_get_orders(array(
                                'customer_id' => $user_id,
                                'return' => 'ids'
                            ));
                            $coach_id = get_user_meta($user_id, 'gac_coach_id', true) ? get_user_meta($user_id, 'gac_coach_id', true) : 0;
                            $status = get_user_meta($user_id, '_gac_pres_status', true) ? get_user_meta($user_id, '_gac_pres_status', true) : '';
                            $is_prescription = 0;
                            foreach ($this->gstat_generator($order->get_items()) as $item_id => $item) {
                                $product = $item->get_product();
                                if (in_array(get_option('gac_category'), $product->get_category_ids() ? $product->get_category_ids() : [])) {
                                    $is_prescription++;
                                }
                            }
                            $product_type = $is_prescription > 0 ? 'prescription' : 'no-prescription';
                            $customer_type = count($user_purchase) > 0 ? 'repeating' : 'new';
                            $patient_list = $coach_id > 0 ? 'coach' : 'no-coach';
                            $pres_status = $status ? $status : false;

                            $filter_pt = true;
                            $filter_ct = true;
                            $filter_pl = true;
                            $filter_ps = true;

                            if (isset($_POST['product_type']) && !empty($_POST['product_type'])) {
                                $filter_pt = $_POST['product_type'] == $product_type ? true : false;
                            }

                            if (isset($_POST['customer_type']) && !empty($_POST['customer_type'])) {
                                $filter_ct = $_POST['customer_type'] == $customer_type ? true : false;
                            }

                            if (isset($_POST['patient_list']) && !empty($_POST['patient_list'])) {
                                $filter_pl = $_POST['patient_list'] == $patient_list ? true : false;
                            }

                            if (isset($_POST['pres_status']) && !empty($_POST['pres_status'])) {
                                $filter_ps = $_POST['pres_status'] == $pres_status ? true : false;
                            }

                            if ($filter_pl && $filter_ps && $filter_pt && $filter_ct) {
                                $pages++;
                            }
                        endforeach;
                        ?>
                        <div class="gstat_pagination">
                            <button data-page="<?php echo isset($_POST['page']) && (int)$_POST['page'] > 1 ? (int) $_POST['page'] - 1 : 1 ?>" class="button button-secondary gstat_page_btn gstat_prev <?php echo isset($_POST['page']) && (int) $_POST['page'] > 1 ? "" : 'disabled'; ?>"><?php echo __('Previous', 'gac-text'); ?></button>
                            <button data-page="<?php echo isset($_POST['page']) ? (int) $_POST['page'] + 1 : 2 ?>" class="button button-secondary gstat_page_btn gstat_next <?php echo $pages > 0 ? "" : 'disabled'; ?>"><?php echo __('Next', 'gac-text'); ?></button>
                        </div>
                    </td>
                </tr>
            </tfoot>
        <?php else : ?>
            <tr>
                <td colspan="10"><?php _e('No orders yet', 'gac-text'); ?></td>
            </tr>
        <?php endif; ?>
        </tbody>
        </table>
    </div>
</div>

<?php
/**
                        // $pages = 0;
                        // $page_orders = wc_get_orders([
                        //     'status' => array('wc-processing', 'wc-on-hold', 'wc-cancelled', 'wc-completed'),
                        //     'orderby' => 'date',
                        //     'order' => 'DESC',
                        //     'return' => 'ids',
                        //     'limit' => -1
                        // ]);
                        // foreach ($this->gstat_generator($page_orders) as $order_id) :
                        //     $order = wc_get_order($order_id);
                        //     $user_id = $order->get_customer_id();
                        //     $user_purchase = wc_get_orders(array(
                        //         'customer_id' => $user_id,
                        //         'return' => 'ids'
                        //     ));
                        //     $coach_id = get_user_meta($user_id, 'gac_coach_id', true) ? get_user_meta($user_id, 'gac_coach_id', true) : 0;
                        //     $status = get_user_meta($user_id, '_gac_pres_status', true) ? get_user_meta($user_id, '_gac_pres_status', true) : '';
                        //     $is_prescription = 0;
                        //     foreach ($this->gstat_generator($order->get_items()) as $item_id => $item) {
                        //         $product = $item->get_product();
                        //         if (in_array(get_option('gac_category'), $product->get_category_ids() ? $product->get_category_ids() : [])) {
                        //             $is_prescription++;
                        //         }
                        //     }
                        //     $product_type = $is_prescription > 0 ? 'prescription' : 'no-prescription';
                        //     $customer_type = count($user_purchase) > 0 ? 'repeating' : 'new';
                        //     $patient_list = $coach_id > 0 ? 'coach' : 'no-coach';
                        //     $pres_status = $status ? $status : false;

                        //     $filter_pt = true;
                        //     $filter_ct = true;
                        //     $filter_pl = true;
                        //     $filter_ps = true;

                        //     if (isset($_POST['product_type']) && !empty($_POST['product_type'])) {
                        //         $filter_pt = $_POST['product_type'] == $product_type ? true : false;
                        //     }

                        //     if (isset($_POST['customer_type']) && !empty($_POST['customer_type'])) {
                        //         $filter_ct = $_POST['customer_type'] == $customer_type ? true : false;
                        //     }

                        //     if (isset($_POST['patient_list']) && !empty($_POST['patient_list'])) {
                        //         $filter_pl = $_POST['patient_list'] == $patient_list ? true : false;
                        //     }

                        //     if (isset($_POST['pres_status']) && !empty($_POST['pres_status'])) {
                        //         $filter_ps = $_POST['pres_status'] == $pres_status ? true : false;
                        //     }

                        //     if ($filter_pl && $filter_ps && $filter_pt && $filter_ct) {
                        //         if(count($page_orders) > $pages * 20){
                        //             $pages++ ;
                        //         }
                        //     }
                        // endforeach;
                        // <ul class="gstat_pagination">
                        //     <li data-page="<?php echo $page_no > 1 ? $page_no - 1 :  1;?>"><i class="fa-solid fa-chevron-left"></i></li>
                        //     <?php for ($i = 1; $i <= $pages; $i++) : ?>
                        //         <li data-page="<?php echo $i;?>"><?php echo $i; ?></li>
                        //     <?php endfor; ?>
                        //     <li data-page="<?php echo $page_no > 1 ? $page_no + 1 : 2;?>"><i class="fa-solid fa-chevron-right"></i></li>
                        // </ul>
 */
?>