<?php
session_start();

// use Dompdf\Dompdf;

/**
 * The function "pm_get_entry_id_after_submission" is used for fetching  entry id on form submit 
 * And saving students id and student entry id to the couples table.
 */
if (!function_exists('pm_get_entry_id_after_submission')) {
    add_action('frm_after_create_entry', 'pm_get_entry_id_after_submission', 10, 2);
    function pm_get_entry_id_after_submission($entry_id, $form_id)
    {
        if (is_user_logged_in()) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'couples';
            $item_metas_table = $wpdb->prefix . 'frm_item_metas';
            $users = wp_get_current_user(); //getting current user details
            $student_id = $users->ID;
            $student_class = get_user_meta($student_id, 'student_class', true);
            $group_entry = '';
            //getting group entry id using session
            if (isset($_SESSION['group_entry'])) {
                $group_entry = $_SESSION['group_entry'];
            }
            $user_roles = $users->roles;

            if (in_array('um_counselor', $user_roles)) {
                $sql = $wpdb->query("UPDATE $table_name set counselor_entry ='$entry_id' WHERE group_entry = '$group_entry' AND form_id = '$form_id'");
            }

            $student_email = $users->user_email;
            //fetching item_id from frm_item_metas table by user email.
            $group_entries = $wpdb->get_results("SELECT item_id FROM $item_metas_table WHERE meta_value = '$student_email' ORDER BY item_id ASC LIMIT 1");
            $group_id = '';
            foreach ($group_entries as $group_entry) {
                $group_id = $group_entry->item_id;
            }

            $group_entries_ids = $wpdb->get_results("SELECT group_entry from $table_name");  //fetching group_entry id from couples table.
            $group_entry_id = [];
            foreach ($group_entries_ids as $group_entries_id) {
                $group_entry_id[] = $group_entries_id->group_entry;
            }

            $couples_form_ids = $wpdb->get_results("SELECT form_id from $table_name WHERE group_entry = '$group_id'"); // fetching form_id form couples table
            $frm_ids = [];
            foreach ($couples_form_ids as $couples_form_id) {
                $frm_ids[] = $couples_form_id->form_id;
            }

            $form_ids = [10, 12, 13, 17, 23, 20, 19, 21, 15, 16, 18, 25];
            if (in_array($form_id, $form_ids)) {
                if (!in_array($group_id, $group_entry_id)) {
                    if ($student_class == 'student1') {
                        $sql = $wpdb->query("INSERT INTO $table_name(student1, student1_entry, form_id, group_entry) values('$student_id','$entry_id','$form_id', '$group_id') ");
                    } else if ($student_class == 'student2') {
                        $sql = $wpdb->query("INSERT INTO $table_name(student2, student2_entry, form_id, group_entry) values('$student_id','$entry_id','$form_id', '$group_id') ");
                    }
                } else {
                    if (!in_array($form_id, $frm_ids)) {
                        if ($student_class == 'student1') {
                            $sql = $wpdb->query("INSERT INTO $table_name(student1, student1_entry, form_id, group_entry) values('$student_id','$entry_id','$form_id', '$group_id') ");
                        } else {
                            if ($student_class == 'student2') {
                                $sql = $wpdb->query("INSERT INTO $table_name(student2, student2_entry, form_id, group_entry) values('$student_id','$entry_id','$form_id', '$group_id') ");
                            }
                        }
                    } else {
                        if ($student_class == 'student1') {
                            $sql = $wpdb->query("UPDATE $table_name set student1 = '$student_id', student1_entry = '$entry_id' WHERE form_id = '$form_id' AND group_entry = '$group_id'");
                        } elseif ($student_class == 'student2') {
                            $sql = $wpdb->query("UPDATE $table_name set student2 = '$student_id', student2_entry = '$entry_id' WHERE form_id = '$form_id' AND group_entry = '$group_id'");
                        }
                    }
                }
            }
            pm_send_pdf_email($entry_id, $form_id);


            $user_id = $users->ID;
            $user_roles = get_userdata($user_id)->roles;

            $redirect_url = '';

            $current_url = $_SERVER['REQUEST_URI'];

            if (in_array('um_adminchurch', $user_roles)) {

                if (strpos($current_url, '/ru/') !== false) {
                    $redirect_url = get_home_url() . '/ru/rucounselor-lp';
                } else {
                    $redirect_url = get_home_url() . '/counselor-lp';
                }

                if ($form_id == 9) {
                    $ca_uid =   $student_id = $users->ID;
                    $total_cre_coup = get_user_meta($ca_uid, 'pm_total_created_couples', true);
                    if ($total_cre_coup) {
                        $total_couple_count = $total_cre_coup + 1;
                    } else {
                        $total_couple_count = 1;
                    }
                    update_user_meta($ca_uid, 'pm_total_created_couples', $total_couple_count);
                }
            } else if (in_array('um_student', $user_roles)) {

                if (strpos($current_url, '/ru/') !== false) {
                    $redirect_url = get_home_url() . '/ru/rustudent';
                } else {
                    $redirect_url = get_home_url() . '/student';
                }
            }


            $quiz_ids = array(10, 12, 13, 17, 23, 20, 19, 21, 15, 16, 18, 25);
            if (in_array($form_id, $quiz_ids)) {

                $table_name_frm = $wpdb->prefix . 'frm_items';

                $query = $wpdb->prepare(
                    "SELECT is_draft
                    FROM $table_name_frm
                    WHERE id = %d",
                    $entry_id,
                );

                $result = $wpdb->get_var($query);

                if ($result !== null) {

                    if ($result == 1) {
                        // draft
                    } else {
                        wp_redirect($redirect_url);
                        exit();
                    }
                } else {
                    // no entry
                }
            }

            // also check this on user creation and add values accordingly 
            if (in_array('um_counselor', $user_roles)) {
                $user_id = $users->ID;
                if ($form_id == 1043) {

                    $sub_planf = 1005533; // field id of sub plan
                    $payed_amtf = 1005543; // field id of payed amt
                    $payment_statusf = 1005529; // payment status field 

                    $sub_plan = $wpdb->get_var("SELECT meta_value from " . $wpdb->prefix . "frm_item_metas WHERE item_id='$entry_id' AND field_id=$sub_planf ");
                    $payment_status = $wpdb->get_var("SELECT meta_value from " . $wpdb->prefix . "frm_item_metas WHERE item_id='$entry_id' AND field_id=$payment_statusf ");

                    if ($payment_status == 'completed') {
                        $payment_statusv = 1;

                        $valid_till = current_time('timestamp') + 300;
                    } else {
                        $payment_statusv = 0;
                    }
                    // this is also per year basis
                    if (strpos($sub_plan, "basic") !== false) {
                        $tallowed_couples = 3;
                    } elseif (strpos($sub_plan, "standard") !== false) {
                        $tallowed_couples = 7;
                    } elseif (strpos($sub_plan, "plus") !== false) {
                        $tallowed_couples = 15;
                    } elseif (strpos($sub_plan, "premium") !== false) {
                        $tallowed_couples = -1;
                    } else {
                        $tallowed_couples = 1;
                    }

                    $tallowed_couples = 1;
                    $payment_status = 0;

                    update_user_meta($user_id, 'pm_paid_subscription', $payment_statusv);
                    update_user_meta($user_id, 'pm_total_allowed_couples', $tallowed_couples);
                    update_user_meta($user_id, 'pm_total_allowed_counselor', '-1'); //un limited for now, when user creation make 1
                    update_user_meta($user_id, 'pm_payment_valid_till', $valid_till);

                    // check what to do after time out
                    $valid_till = get_user_meta($user_id, 'pm_payment_valid_till', true);
                    if ($valid_till && current_time('timestamp') > $valid_till) {
                    }
                }
            }
        }
    }
}

/**
 * The function "couple_data" is used for passing counsellor id , partner1 id , partner 2 id and group_entry id via ajax.
 */

if (!function_exists('pm_couples_data')) {
    add_action('wp_footer', 'pm_couples_data');
    function pm_couples_data()
    {
        $counsellor_url = $_SERVER['REQUEST_URI'];
        $url = "/counselor-lp/entry/";
        //checking for counsellor page
        if (strpos($counsellor_url, $url) !== false) {

            $urlParts = explode('/', $counsellor_url);
            $group_entry = $urlParts[3];   // fetching group entry from url.
?>
            <script>
                jQuery(document).ready(function($) {
                    var href = window.location.href;
                    if (href.indexOf('/counselor-lp/entry/') > -1 || href.indexOf('/rustudent') > -1) {
                        var formIds = []; //storing all forms id in a single form
                        $('.frm4').each(function() {
                            var section = $(this);
                            var formId = section.find('.pm_hidden_form_ID').data('formid');
                            formIds.push(formId);

                        });
                        var groupEntry = '<?php echo $group_entry; ?>';
                        var partner1Id = $('.pm_hidden_student1_ID').data('studentid');
                        var partner2Id = $('.pm_hidden_student2_ID').data('studentid');
                        $.ajax({
                            type: 'POST',
                            url: cpmAjax.ajax_url,
                            data: {
                                action: 'pm_save_groups_details',
                                partner1_id: partner1Id,
                                partner2_id: partner2Id,
                                group_entry: groupEntry,
                                form_id: formIds,


                            },
                        });

                    }
                });
            </script>
<?php
        }
    }
}

/**
 * The function "pm_save_groups_details" is used to saved counsellor id , student1 id, student2 id and group_entry id to the wpbi_couples table.
 */

if (!function_exists('pm_save_groups_details')) {
    add_action('wp_ajax_pm_save_groups_details', 'pm_save_groups_details');
    function pm_save_groups_details()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'couples';
        $group_entry = isset($_POST['group_entry']) ? $_POST['group_entry'] : '';
        $partner1_id = isset($_POST['partner1_id']) ? $_POST['partner1_id'] : '';
        $partner2_id = isset($_POST['partner2_id']) ? $_POST['partner2_id'] : '';
        $counsellor_form_ids = isset($_POST['form_id']) ? $_POST['form_id'] : '';

        $_SESSION['group_entry'] = $group_entry;  //passing group_entry to the session

        $counsellor = wp_get_current_user(); //getting current user details
        $counsellor_id = '';
        $user_roles = $counsellor->roles;

        if (in_array('um_counselor', $user_roles)) {

            $counsellor_id = $counsellor->ID;
        }

        $group_entries_ids = $wpdb->get_results("SELECT group_entry from $table_name");
        $group_entry_id = [];
        foreach ($group_entries_ids as $group_entries_id) {
            $group_entry_id[] = $group_entries_id->group_entry;
        }

        $forms_ids = $wpdb->get_results("SELECT form_id from $table_name where group_entry = '$group_entry'"); //fetching form_id from
        $form_id = [];
        foreach ($forms_ids as $forms_id) {
            $form_id[] = $forms_id->form_id;
        }

        if (!in_array($group_entry, $group_entry_id)) {
            foreach ($counsellor_form_ids as $counsellor_form_id) {
                $sql = $wpdb->query("INSERT INTO $table_name(counselor, student1, student2, group_entry, form_id) values('$counsellor_id','$partner1_id','$partner2_id','$group_entry','$counsellor_form_id') ");
            }
        } else {
            foreach ($counsellor_form_ids as $counsellor_form_id) {
                if (!in_array($counsellor_form_id, $form_id)) {
                    $sql = $wpdb->query("INSERT INTO $table_name(counselor, student1, student2, group_entry, form_id) values('$counsellor_id','$partner1_id','$partner2_id','$group_entry','$counsellor_form_id') ");
                } else {
                    $sql = $wpdb->query("UPDATE $table_name set counselor = '$counsellor_id', student1='$partner1_id', student2= '$partner2_id' WHERE group_entry = '$group_entry' AND form_id = '$counsellor_form_id' ");
                }
            }
        }

        die();
    }
}

//-------------------------------------------------for task 10---------------------------


/**
 * The function  pm_count_total_student_entry() is used for counting total comments done by students.
 */
if (!function_exists('pm_count_total_student_entry')) {
    add_shortcode('pm-form-entries', 'pm_count_total_student_entry');
    function pm_count_total_student_entry($atts)
    {
        global $wpdb;
        $item_metas_table = $wpdb->prefix . 'frm_item_metas';
        $frm_fields_table = $wpdb->prefix . 'frm_fields';

        $form_id = $atts['form-id'];
        $student_id = $atts['student-id'];

        $current_url = $_SERVER['REQUEST_URI'];
        $comments = '';

        if (strpos($current_url, '/ru/') !== false) {
            $comments = 'ответы';
        } else {
            $comments = 'answers';
        }

        $float = '';
        $i = '';
        $get_entry_id = '';
        $student_class = get_user_meta($student_id, 'student_class', true);

        if ($student_class == 'student1') {
            $i = 1;
            $float = 'float:right;';

            $identifiers = array(
                'student1' => $student_id,
                'form_id' => $form_id,
            );
            $get_entry_id = pm_get_couple_data('student1_entry', $identifiers);
        } elseif ($student_class == 'student2') {
            $i = 2;
            $float = 'float:left;';

            $identifiers = array(
                'student2' => $student_id,
                'form_id' => $form_id,
            );
            $get_entry_id = pm_get_couple_data('student2_entry', $identifiers);
        } else {
        }

        // counting total no of comment done by student
        $entries = $wpdb->get_results("SELECT * FROM $item_metas_table WHERE item_id ='$get_entry_id'", ARRAY_A);

        $actual_entries = [];
        foreach ($entries as $entry) {

            $field_id = $entry['field_id'];
            $field_type = $wpdb->get_var("SELECT `type` FROM $frm_fields_table WHERE form_id =$form_id AND id = $field_id ");

            if ($field_type == 'textarea' || $field_type == 'checkbox') {
                array_push($actual_entries, [$field_id]);
            }
        }
        $total_entries = count($actual_entries);


        //counting total no of field in the form
        $total_form_fields = $wpdb->get_results("SELECT count(id) FROM $frm_fields_table WHERE form_id ='$form_id' AND  type NOT IN ('html', 'user_id', 'hidden')");
        $total_form_field = (int)$total_form_fields[0]->{'count(id)'};

        $total_student_field = ($total_form_field / 2) / 2;

        $progress_percent = 0;
        $total_comment = 0;
        $total_comment = $total_entries;
        if ($total_entries > $total_student_field) {
            $progress_percent = 100;
        } else {
            if ($total_entries) {
                $total_comment = $total_entries;
                $progress_percent = round(($total_comment / $total_student_field) * 100);
            }
        }

        $output = $comments . '-' . $total_comment . '</br></br><div role="progressbar' . $i . '" aria-valuenow="' . $progress_percent . '" aria-valuemin="0" aria-valuemax="100" style="--value:' . $progress_percent . '; ' . $float . '"></div>';
        return $output;
    }
}

/**
 * The funtion "pm_overall_progress_page" is used for caculating total comments and total completed form by students
 */

if (!function_exists('pm_overall_progress_page')) {
    add_shortcode('pm-overall-progress', 'pm_overall_progress_page');
    function pm_overall_progress_page($atts)
    {
        $student_id = $atts['student-id'];
        $total_forms = $atts['total-forms'];

        global $wpdb;
        $couples_table = $wpdb->prefix . 'couples';
        $frm_items_table = $wpdb->prefix . 'frm_items';
        $frm_item_metas_table = $wpdb->prefix . 'frm_item_metas';

        $student_class = get_user_meta($student_id, 'student_class', true);

        $current_url = $_SERVER['REQUEST_URI'];
        $comments = '';
        $completed = '';

        if (strpos($current_url, '/ru/') !== false) {
            $comments = 'ответы';
            $completed = 'закончено';
        } else {
            $comments = 'answers';
            $completed = 'completed';
        }

        $form_ids = $wpdb->get_results("SELECT form_id FROM $couples_table  WHERE (student1 = '$student_id'  OR student2 = '$student_id') AND form_id != '40' ");

        $forms_submitted = [];
        foreach ($form_ids as $form_id) {
            $form_id = $form_id->form_id;
            $forms_submitted_ids = $wpdb->get_results("SELECT distinct form_id FROM $frm_items_table WHERE user_id = '$student_id' AND form_id = '$form_id' AND is_draft != 1");
            foreach ($forms_submitted_ids as $forms_submitted_id) {
                $forms_submitted[] = $forms_submitted_id->form_id;
            }
        }

        $unique_form = array_unique($forms_submitted);  // for selecting unique submitted id
        $unique_form_count = count($unique_form);
        if ($unique_form_count > $total_forms) {
            $unique_form_count = $total_forms;
        }

        $progress_percent = round(($unique_form_count / $total_forms) * 100);

        $float = '';
        $i = '';
        if ($student_class == 'student1') {
            $i = 1;
            $float = 'float:right;';
            $student1_entries_ids = [];  // storing student1 entry id in array
            foreach ($forms_submitted as $form_submitted) {
                // getting student 1 entry ids
                $student1_entry_ids  = $wpdb->get_results("SELECT student1_entry FROM $couples_table WHERE  student1 = '$student_id' AND form_id = '$form_submitted'");
                foreach ($student1_entry_ids as $student1_entry_id) {
                    $student1_entries_ids[] = $student1_entry_id->student1_entry;
                }
            }
            $student1_entry_id_count = (int) count($student1_entries_ids);  // counting total number of entries id
            $student1_total_form_comment = [];  // storing total number of comment for each form in array
            foreach ($student1_entries_ids as $student1_entries_id) {

                $student1_comments = $wpdb->get_results("SELECT count(id) FROM $frm_item_metas_table WHERE  item_id = '$student1_entries_id'");
                foreach ($student1_comments as $student1_comment) {
                    $student1_total_form_comment[] = $student1_comment->{'count(id)'};
                }
            }

            $student1_total_comment_with_user_id = (int) array_sum($student1_total_form_comment);
            $student1_total_comment  = $student1_total_comment_with_user_id - $student1_entry_id_count; // getting total number of comment for all form and subtracting 1 extra user field

            $output = '</br> ' . $student1_total_comment . '- ' . $comments . '</br> ' . $unique_form_count . ' of ' . $total_forms . ' - ' . $completed . '</br></br><div role="progressbar1" aria-valuenow="' . $progress_percent . '" aria-valuemin="0" aria-valuemax="100" style="--value:' . $progress_percent . '"></div>';
            return $output;
        } elseif ($student_class == 'student2') {
            $i = 2;
            $float = 'float:left;';

            $student2_entries_ids = [];  // storing student2 entry id in array
            foreach ($forms_submitted as $form_submitted) {
                $student2_entry_ids  = $wpdb->get_results("SELECT student2_entry FROM $couples_table WHERE  student2 = '$student_id' AND form_id = '$form_submitted'");
                foreach ($student2_entry_ids as $student2_entry_id) {
                    $student2_entries_ids[] = $student2_entry_id->student2_entry;
                }
            }
            $student2_entry_id_count = (int) count($student2_entries_ids);  // counting total number of entries id
            $student2_total_form_comment = [];  // storing total number of comment for each form in array
            foreach ($student2_entries_ids as $student2_entries_id) {

                $student2_comments = $wpdb->get_results("SELECT count(id) FROM $frm_item_metas_table WHERE  item_id = '$student2_entries_id'");
                foreach ($student2_comments as $student2_comment) {
                    $student2_total_form_comment[] = $student2_comment->{'count(id)'};
                }
            }

            $student2_total_comment_with_user_id = (int) array_sum($student2_total_form_comment);
            $student2_total_comment  = $student2_total_comment_with_user_id - $student2_entry_id_count; // getting total number of comment for all form and subtracting 1 extra user field

            $output = '</br> ' . $student2_total_comment . '- ' . $comments . '</br> ' . $unique_form_count . ' of ' . $total_forms . ' - ' . $completed . '</br></br><div role="progressbar2" aria-valuenow="' . $progress_percent . '" aria-valuemin="0" aria-valuemax="100" style="--value:' . $progress_percent . '"></div>';
            return $output;
        } else {
        }
    }
}

/**
 * The function pm_completed_percentage is used for show completed percentage in student report
 */
if (!function_exists('pm_completed_percentage')) {
    add_shortcode('pm-completed-precentage', 'pm_completed_percentage');
    function pm_completed_percentage($atts)
    {
        global $wpdb;
        $student_id = $atts['student-id'];
        $total_forms = $atts['total-forms'];
        $couples_table = $wpdb->prefix . 'couples';
        $frm_items_table = $wpdb->prefix . 'frm_items';
        $frm_item_metas_table = $wpdb->prefix . 'frm_item_metas';

        $student_class = get_user_meta($student_id, 'student_class', true);

        $current_url = $_SERVER['REQUEST_URI'];
        $completed = '';

        if (strpos($current_url, '/ru/') !== false) {
            $completed = 'закончено';
        } else {
            $completed = 'completed';
        }

        $form_ids = $wpdb->get_results("SELECT form_id FROM $couples_table  WHERE (student1 = '$student_id'  OR student2 = '$student_id') AND form_id != '40' ");
        $forms_submitted = [];
        foreach ($form_ids as $form_id) {
            $form_id = $form_id->form_id;
            $forms_submitted_ids = $wpdb->get_results("SELECT distinct form_id FROM $frm_items_table WHERE user_id = '$student_id' AND form_id = '$form_id' AND is_draft != 1");
            foreach ($forms_submitted_ids as $forms_submitted_id) {
                $forms_submitted[] = $forms_submitted_id->form_id;
            }
        }

        $unique_form = array_unique($forms_submitted);  // for selecting unique submitted id
        $unique_form_count = count($unique_form);
        if ($unique_form_count > $total_forms) {
            $unique_form_count = $total_forms;
        }

        $progress_percent = round(($unique_form_count / $total_forms) * 100);

        if ($student_class == 'student1') {
            $student1_entries_ids = [];  // storing student1 entry id in array
            foreach ($forms_submitted as $form_submitted) {
                // getting student 1 entry ids
                $student1_entry_ids  = $wpdb->get_results("SELECT student1_entry FROM $couples_table WHERE  student1 = '$student_id' AND form_id = '$form_submitted'");
                foreach ($student1_entry_ids as $student1_entry_id) {
                    $student1_entries_ids[] = $student1_entry_id->student1_entry;
                }
            }
            $student1_total_form_comment = [];  // storing total number of comment for each form in array
            foreach ($student1_entries_ids as $student1_entries_id) {

                $student1_comments = $wpdb->get_results("SELECT count(id) FROM $frm_item_metas_table WHERE  item_id = '$student1_entries_id'");
                foreach ($student1_comments as $student1_comment) {
                    $student1_total_form_comment[] = $student1_comment->{'count(id)'};
                }
            }

            $output =  $unique_form_count . ' of ' . $total_forms . ' - ' . $completed . ' | ' . $progress_percent . '%';
            return $output;
        } elseif ($student_class == 'student2') {
            $student2_entries_ids = [];  // storing student2 entry id in array
            foreach ($forms_submitted as $form_submitted) {
                $student2_entry_ids  = $wpdb->get_results("SELECT student2_entry FROM $couples_table WHERE  student2 = '$student_id' AND form_id = '$form_submitted'");
                foreach ($student2_entry_ids as $student2_entry_id) {
                    $student2_entries_ids[] = $student2_entry_id->student2_entry;
                }
            }
            $student2_total_form_comment = [];  // storing total number of comment for each form in array
            foreach ($student2_entries_ids as $student2_entries_id) {

                $student2_comments = $wpdb->get_results("SELECT count(id) FROM $frm_item_metas_table WHERE  item_id = '$student2_entries_id'");
                foreach ($student2_comments as $student2_comment) {
                    $student2_total_form_comment[] = $student2_comment->{'count(id)'};
                }
            }

            $output = $unique_form_count . ' of ' . $total_forms . ' - ' . $completed . ' | ' . $progress_percent . '%';
            return $output;
        } else {
        }
    }
}


/**
 * The function "pm_send_pdf_email" is used for sending mail to the user on overall form submition.
 */
if (!function_exists("pm_send_pdf_email")) {
    function pm_send_pdf_email($entry_id, $form_id)
    {
        global $wpdb;
        $couples_table = $wpdb->prefix . 'couples';
        $frm_items_table = $wpdb->prefix . 'frm_items';

        $student_id = get_current_user_id();
        $user_data = get_userdata($student_id);
        $users_role = $user_data->roles[0];
        $user_email = $user_data->user_email;
        $user_id = $user_data->ID;

        if ($users_role == "um_student") {
            $form_ids = [10, 12, 13, 17, 23, 20, 19, 21, 15, 16, 18, 25];
            if (in_array($form_id, $form_ids)) {
                $couple_form_ids = $wpdb->get_results("SELECT form_id FROM $couples_table  WHERE (student1 = '$student_id'  OR student2 = '$student_id') AND form_id != '40' ");
                $forms_submitted = [];
                foreach ($couple_form_ids as $couple_form_id) {
                    $form_id = $couple_form_id->form_id;
                    $forms_submitted_ids = $wpdb->get_results("SELECT distinct form_id FROM $frm_items_table WHERE user_id = '$student_id' AND form_id = '$form_id' AND is_draft != 1");
                    foreach ($forms_submitted_ids as $forms_submitted_id) {
                        $forms_submitted[] = $forms_submitted_id->form_id;
                    }
                }
                $result = array_diff($form_ids, $forms_submitted);
                if (!$result) {
                } else {

                    $shortcode_output = do_shortcode('[display-frm-data id=3004593]');
                    $to = $user_email;
                    $subject = 'certificate';
                    $message = $shortcode_output;
                    wp_mail($to, $subject, $message);  //for sending mail

                }
            }
        }
    }
}

/**
 * The function "users_name_for_sending_mail" is used for sending user name to the certificate
 */
if (!function_exists('users_name_for_sending_mail')) {
    add_shortcode('user-name', 'users_name_for_sending_mail');
    function users_name_for_sending_mail()
    {
        $user_id = get_current_user_id();
        $user_first_name = get_user_meta($user_id, 'first_name', true);
        $user_last_name = get_user_meta($user_id, 'last_name', true);
        $result = $user_first_name . ' ' . $user_last_name;
        return $result;
    }
}



if (!function_exists("pm_counselor_signature")) {
    // add_action('wp_footer', 'pm_counselor_signature');
    // add_shortcode('signature', 'pm_counselor_signature');
    function pm_counselor_signature()
    {
        if (is_user_logged_in()) {
            global $wpdb;
            $frm_item_meta = $wpdb->prefix . 'frm_item_metas';
            $frm_items = $wpdb->prefix . 'frm_items';
            $student_id = get_current_user_id();
            $user_data = get_userdata($student_id);
            $users_role = $user_data->roles[0];
            $user_email = $user_data->user_email;
            if ($users_role == 'um_student') {

                $item_id = $wpdb->get_var("SELECT item_id FROM $frm_item_meta WHERE meta_value ='$user_email' AND (field_id = '77' OR field_id ='84') ORDER By id ASC");
                var_dump($item_id);

                $counselor_item_id = $wpdb->get_var("SELECT meta_value FROM $frm_item_meta WHERE item_id ='$item_id' AND field_id = '74'");
                var_dump($counselor_item_id);

                $counselor_id = $wpdb->get_var("SELECT user_id FROM $frm_items WHERE id = '$counselor_item_id' AND form_id ='3'");
                if ($counselor_id) {
                    var_dump($counselor_id);

                    $signature_item_id = $wpdb->get_var("SELECT item_id FROM $frm_item_meta WHERE meta_value ='$counselor_id' and field_id = '3005561'");
                    if ($signature_item_id) {
                        var_dump($signature_item_id);
                        $signature = $wpdb->get_var("SELECT meta_value FROM $frm_item_meta WHERE item_id = '$signature_item_id' AND field_id = '3005560'");
                        if ($signature) {
                            // var_dump($signature);
                            $unserialize_signature = unserialize($signature);
                            $content = $unserialize_signature['output'];
                            if ($content) {
                                echo "<img src = '$content' alt = 'sign' style = 'height:100px'/>";
                            }
                        }
                    }
                }
            }
        }
    }
}

add_shortcode('show-certificate', 'show_certificate');
function show_certificate()
{
    global $wpdb;
    $couples_table = $wpdb->prefix . 'couples';
    $frm_items_table = $wpdb->prefix . 'frm_items';

    $student_id = get_current_user_id();
    $user_data = get_userdata($student_id);
    $users_role = $user_data->roles[0];
    $user_email = $user_data->user_email;
    $user_id = $user_data->ID;

    if ($users_role == "um_student") {
        $form_ids = [10, 12, 13, 17, 23, 20, 19, 21, 15, 16, 18, 25];

        $couple_form_ids = $wpdb->get_results("SELECT form_id FROM $couples_table  WHERE (student1 = '$student_id'  OR student2 = '$student_id') AND form_id != '40' ");
        $forms_submitted = [];
        foreach ($couple_form_ids as $couple_form_id) {
            $form_id = $couple_form_id->form_id;
            $forms_submitted_ids = $wpdb->get_results("SELECT distinct form_id FROM $frm_items_table WHERE user_id = '$student_id' AND form_id = '$form_id' AND is_draft != 1");
            foreach ($forms_submitted_ids as $forms_submitted_id) {
                $forms_submitted[] = $forms_submitted_id->form_id;
            }
        }
        $result = array_diff($form_ids, $forms_submitted);
        if (!$result) {
            //show certificate
        } else {
            //  return do_shortcode('[display-frm-data id=3004593]');

            return do_shortcode('[display-frm-data id=3004593]');
            // $output = '<div class="congrats-certificate" style = "box-shadow: 1px 1px 8px 0 rgba(0,0,0,0.2), 0px 0px 0px 0 rgba(0,0,0,0.19); border: 1px solid #cccccc; border-radius: 8px; margin-top:10px;">';
            // $output .='<p style="text-align: center;">Congratulations! You have successfully finished all of the quizzes.<br />Click below to download the Certificate.</p>';
            // $output .= '<div style="text-align:center; color:">'
            // $output .=do_shortcode('[frm-pdf view="frm-certificate-2" public=1 label="Download certificate" entry_id="'.$user_id.'" orientation=landscape filename="'.$user_id.'-certificate"]');
            // $output .='</div>';
            // return $output;
        }
    }
}

// add_action('wp_footer', 'test');
function test()
{
    var_dump('check 44');
    if (is_user_logged_in()) {
        $student_id = get_current_user_id();
        $user_data = get_userdata($student_id);
        $users_role = $user_data->roles;
        $user_default_role = ['um_adminchurch'];
        $check_diff = array_diff($users_role, $user_default_role);
        var_dump($check_diff);
        if ($check_diff) {
            echo "show nav";
        } else {
            echo 'No nav';
        }
    }
    // $user_email = $user_data->user_email;
}


add_filter('wp_nav_menu_objects', 'pm_remove_menu_item_from_menu', 10, 2);
function pm_remove_menu_item_from_menu($sorted_menu_objects, $args)
{
    if (is_user_logged_in()) {
        // check for the right menu to remove the menu item from
        // here we check for theme location of 'secondary-menu'
        // alternatively you can check for menu name ($args->menu == 'menu_name')
        $student_id = get_current_user_id();
        $user_data = get_userdata($student_id);
        $users_role = $user_data->roles;
        $user_adminchurch_role = ['um_adminchurch'];
        $user_both_role = ['um_adminchruch', 'um_counselor'];
        $check_adminchruch = array_diff($users_role, $user_adminchurch_role);
        $check_both = array_diff($user_both_role, $users_role);

        if ($check_adminchruch) { // Check for primary menu //add nave 
        } else {
            // if(in_array('um_adminchurch'))
            if ($args->theme_location == 'primary') {
                // echo '<pre>';
                // var_dump($sorted_menu_objects);
                // echo '</pre>';
                foreach ($sorted_menu_objects as $key => $menu_object) {
                    if (($menu_object->title == 'Counselor' && $menu_object->url == site_url() . '/counselor-lp/') || ($menu_object->title == 'Консультант' && $menu_object->url == site_url() . '/ru/rucounselor-lp/')) {
                        unset($sorted_menu_objects[$key]);
                        break;
                    }
                }
            }
        }
        if (in_array('um_adminchurch', $users_role)) {
            if ($args->theme_location == 'primary') {
                // echo '<pre>';
                // var_dump($sorted_menu_objects);
                // echo '</pre>';
                foreach ($sorted_menu_objects as $key => $menu_object) {
                    if (($menu_object->title == 'Account' && $menu_object->url == site_url(). '/account-counselors/') || ($menu_object->title == 'Аккаунт' && $menu_object->url == site_url(). '/ru/ruaccount-counselors/')) {
                        unset($sorted_menu_objects[$key]);
                        break;
                    }
                }
            }
        }
    }

    return $sorted_menu_objects;
}


add_filter('wp_nav_menu_items', 'pm_add_menu_item_to_menu', 10, 2);
function pm_add_menu_item_to_menu($items, $args)
{
    if (is_user_logged_in()) {
        $student_id = get_current_user_id();
        $user_data = get_userdata($student_id);
        $users_role = $user_data->roles;
        $user_default_role = ['um_adminchurch', 'um_counselor'];
        $check_diff = array_diff($user_default_role, $users_role);
        if ($check_diff) {
            // Check for primary menu // add nave
        } else {
            if ($args->theme_location == 'primary') {
                $current_url = $_SERVER['REQUEST_URI'];
                if (strpos($items, 'Counselor') === false && strpos($items, 'Консультант') === false) {
                    if (strpos($current_url, '/ru/') !== false) {
                        $new_item = '<li class="menu-item"><a href="' . site_url() . '/ru/rucounselor-lp/">Консультант</a></li>';
                    } else {
                        $new_item = '<li class="menu-item"><a href="' . site_url() . '/counselor-lp/">Counselor</a></li>';
                    }

                    // Split the menu items into an array
                    $menu_items = preg_split('/<\/li>/', $items);

                    // Insert the new item as the 3rd item
                    array_splice($menu_items, 2, 0, $new_item);

                    // Reassemble the menu items
                    $items = implode('</li>', $menu_items);
                    // var_dump($items);
                }
            }
        }
    }
    return $items;
}
