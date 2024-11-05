jQuery(document).ready(function ($) {

    // enable disable counselor review button 
    var href = window.location.href;
    if (href.indexOf('/counselor-lp/entry/') > -1 || href.indexOf('/rucounselor-lp/entry/')) {

        // Loop through each section with class 'frm4'
        $('.frm4').each(function () {
            var section = $(this);
            // Get the values from hidden classes

            var formID = section.find(".pm_hidden_form_ID").data("formid");
            var student1ID = section.find(".pm_hidden_student1_ID").data("studentid");
            var student2ID = section.find(".pm_hidden_student2_ID").data("studentid");

            // if (formID && student1ID && student2ID) {
            //     // Make an AJAX request to save the data
            //     $.ajax({
            //         url: cpmAjax.ajax_url,
            //         type: 'POST',
            //         data: {
            //             action: 'pm_check_students_form_status_ajax',
            //             form_ID: formID,
            //             student1_ID: student1ID,
            //             student2_ID: student2ID
            //         },
            //         success: function (response) {
            //             // If the AJAX request is successful and data is saved
            //             // Remove the 'disable_link' class from the section
            //             if (response === 'saved') {
            //                 section.find('.pm_disable_link_ID').removeClass('pm_disable_link pm_disabled_style');
            //             }
            //         },
            //         error: function () {
            //             // Handle AJAX request errors here if needed
            //         }
            //     });
            // }

            // if (student1ID) {
            //     $.ajax({
            //         url: cpmAjax.ajax_url,
            //         type: 'POST',
            //         data: {
            //             action: 'pm_check_single_student_form_status_ajax',
            //             form_ID: formID,
            //             student_ID: student1ID
            //         },
            //         success: function (response) {

            //             //if no draft, yes savd  
            //             if (response === 'no') {
            //                 section.find('.pm-unlock-student1').addClass('pm_dis_btn_unlock')
            //             }
            //         },
            //         error: function () {
            //             // Handle AJAX request errors here if needed
            //         }
            //     });
            // }
            // if (student2ID) {
            //     $.ajax({
            //         url: cpmAjax.ajax_url,
            //         type: 'POST',
            //         data: {
            //             action: 'pm_check_single_student_form_status_ajax',
            //             form_ID: formID,
            //             student_ID: student2ID
            //         },
            //         success: function (response) {

            //             //if no draft, yes saved  
            //             if (response === 'no') {
            //                 section.find('.pm-unlock-student2').addClass('pm_dis_btn_unlock')
            //             }
            //         },
            //         error: function () {
            //             // Handle AJAX request errors here if needed
            //         }
            //     });
            // }


            // Handle click on the buttons
            section.find('.pm-unlock-student1').on('click', function () {
                // Open popup for student1
                openPopup(student1ID);
            });

            section.find('.pm-unlock-student2').on('click', function () {
                // Open popup for student2
                openPopup(student2ID);
            });

            var popupContainer = $('#pmpopupContainer');
            var popupHtml = `<div id="pm-overlay" class="pm-cover"></div><div id="unlockPopup" class="pm-unlock-stu-frm-popup">
            <div class="pm-unlock-stu-frm-popup-content">
            <button class="pm-unlock-stu-frm-popup-close">&times;</button>
            <p>Unlocking this quiz will allow the student to change his/her answers. Are you sure you want to do that?</p>
            <button class="button-6 pm-unlock-stu-frm-popup-btn">Unlock</button>
            </div></div>`;

            section.find('.pm-unlock-student1').on('click', function () {
                var student1ID = section.find(".pm_hidden_student1_ID").data("studentid");
                openPopup(student1ID);
            });

            section.find('.pm-unlock-student2').on('click', function () {
                var student2ID = section.find(".pm_hidden_student2_ID").data("studentid");
                openPopup(student2ID);
            });

            function openPopup(studentID) {
                popupContainer.html(popupHtml);
                $('#pm-overlay').addClass('pm-blur');

                $('.pm-unlock-stu-frm-popup-btn').on('click', function () {
                    // Your AJAX request and other logic
                    $.ajax({
                        url: cpmAjax.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'pm_unlock_stu_form',
                            form_ID: formID,
                            student_ID: studentID
                        },
                        success: function (response) {
                            // Handle success response
                            $('#pm-overlay').removeClass('pm-blur');
                        },
                        error: function (error) {
                            // Handle error response
                        }
                    });

                    // Close the popup
                    closePopup();
                    location.reload();

                });

                $('.pm-unlock-stu-frm-popup-close').on('click', function () {
                    closePopup();
                });
            }

            function closePopup() {
                popupContainer.empty();
                $('#pm-overlay').removeClass('pm-blur');
            }

        });
    }

    if (href.indexOf('/student') > -1 || href.indexOf('/rustudent') > -1) {
        // disable quiz link on student page if church admin has not paid plan
        $.ajax({
            url: cpmAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'pm_check_church_admin_payment_status',
                check: 'access-forms',
            },
            success: function (response) {
                //if true, yes subscribed/paid  
                console.log(response);
                if (response === 'true') {
                    $('.pm_disable_link_ID ').removeClass('pm_disable_link pm_disabled_style');
                }
                // else if (response === 'false') {
                //     $('.has-contrast-color').addClass('pm_disable_link pm_disabled_style');
                // }
                else {

                }
            },
            error: function () {
                // Handle AJAX request errors here if needed
            }
        });


        // $('.frm4').each(function () {
        //     var section = $(this);
        //     // Get the values from hidden classes
        //     var formID = section.find(".pm_hidden_form_ID").data("formid");

        //     if (formID) {
        //         $.ajax({
        //             url: cpmAjax.ajax_url,
        //             type: 'POST',
        //             data: {
        //                 action: 'pm_check_single_student_form_status_ajax',
        //                 form_ID: formID,
        //             },
        //             success: function (response) {
        //                 //if no draft, yes savd  
        //                 if (response === 'yes') {
        //                     if (href.indexOf('/student') > -1) {
        //                         section.find('.pm_ct_stv').text("View");
        //                     }
        //                     if (href.indexOf('/rustudent') > -1) {
        //                         section.find('.pm_ct_stv').text("Просмотреть");
        //                     }
        //                 }
        //             },
        //             error: function () {
        //                 // Handle AJAX request errors here if needed
        //             }
        //         });


        //     }
        // });

    }


    var popupContainer = $('#pmpopupContainer');

    function pm_openPopup(popup2Html) {
        popupContainer.html(popup2Html);
        $('#pm-overlay').addClass('pm-blur');

        // Close the popup
        // closePopupbilling();

        $('.pm-unlock-stu-frm-popup-close').on('click', function () {
            pm_closePopup();
        });
    }

    function pm_closePopup() {
        popupContainer.empty();
        $('#pm-overlay').removeClass('pm-blur');
    }

    //dont allow church admin to add counselor or couples when no payment plan is selected admin-couples
    if (href.indexOf('/admin-couples') > -1 || href.indexOf('/ruadmin-couples') > -1) {


        $('.frm_data_container select').each(function () {
            var optionsHTML = $(this).html();

            $.ajax({
                type: 'POST',
                url: cpmAjax.ajax_url,
                data: {
                    action: 'pm_cstr_cadm_email_tname_couplesdd',
                    optionValues: optionsHTML
                },
                success: function (response) {

                    if (response != 'false') {
                        $(this).html(response);
                    }
                }.bind(this)
            });

        });




        var billing_page_url = '';
        if (href.indexOf('/admin-couples') > -1) {
            billing_page_url = '/billing'
        }
        // else if(href.indexOf('/ruadmin-couples') > -1){
        //     billing_page_url = '/billing'
        // }

        //popup for billing page

        var popup2Html = `<div id="pm-overlay" class="pm-cover"></div><div id="unlockPopup" class="pm-unlock-stu-frm-popup">
                      <div class="pm-unlock-stu-frm-popup-content">
                      <button class="pm-unlock-stu-frm-popup-close">&times;</button>
                      <p>Please select a payment plan to add couples.</p>
                      <a href="` + billing_page_url + `"><button class="button-6">Payment Plan</button></a>
                      </div></div>`;



        // console.log('popup');

        $.ajax({
            url: cpmAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'pm_check_church_admin_payment_status',
                // form_ID: formID,
            },
            success: function (response) {
                //if true, yes subscribed/paid  pm_disable_link_ID pm_disable_link
                console.log(response);
                if (response === 'true') {
                    $('.pm_disable_link_ID').removeClass('pm_disable_link ');
                }
                // else if (response === 'false') {
                //     $('.pm_disable_link_ID').addClass('pm_disable_link ');
                // }
                else {

                }
                // limit couples creation based on church admin paid plan
                $.ajax({
                    url: cpmAjax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'pm_check_total_allowed_couples_counselors',
                        check: 'couple',
                    },
                    success: function (response) {
                        //if true, yes subscribed/paid  
                        console.log(response);
                        if (response == 'true') {
                            $('.pm_disable_link_ID').removeClass('pm_disable_link ');
                        } else if (response == 'false') {

                            $('.pm_disable_link_ID').removeClass('pm_disable_link ');
                            $('.has-contrast-color').removeAttr('data-bs-toggle');
                            $('.has-contrast-color').on('click', function () {
                                // Open popup for student1
                                pm_openPopup(popup2Html);
                            });

                        } else {

                        }
                    },
                    error: function () {
                        // Handle AJAX request errors here if needed
                    }
                });
            },
            error: function () {
                // Handle AJAX request errors here if needed
            }
        });


    }


    if (href.indexOf('/admin-counselor') > -1 || href.indexOf('/ruadmin-counselor') > -1) {
        // disable counselor and couples creation if church admin has not paid plan

        // hide password field when creating counselor
        var hiddenInputValue = $("#field_zah2u2").val();
        if (hiddenInputValue === "0") {
            $("#frm_field_23_container").hide();
        }

        $.ajax({
            url: cpmAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'pm_check_church_admin_payment_status',
                // form_ID: formID,
            },
            success: function (response) {
                //if true, yes subscribed/paid  
                console.log(response);
                if (response === 'true') {
                    $('.pm_disable_link_ID').removeClass('pm_disable_link ');
                }
                //else if (response === 'false') {
                //     $('.pm_disable_link_ID').addClass('pm_disable_link ');
                // }
                else {

                }
            },
            error: function () {
                // Handle AJAX request errors here if needed
            }
        });


        // limit counselors creation based on church admin paid plan
        $.ajax({
            url: cpmAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'pm_check_total_allowed_couples_counselors',
                check: 'counselor',
            },
            success: function (response) {
                //if true, yes subscribed/paid  
                console.log(response);
                if (response === 'true') {
                    $('.pm_disable_link_ID').removeClass('pm_disable_link ');
                } else if (response === 'false') {
                    $('.pm_disable_link_ID').addClass('pm_disable_link ');
                } else {

                }
            },
            error: function () {
                // Handle AJAX request errors here if needed
            }
        });


    }


    if (href.indexOf('/ru') > -1) {
        $('#pm-sbs-button-single').text('Одна Колонка');
        $('#pm-sbs-button-side').text('Две Колонки');
    }

    $('.pm-sbs-buttons').on('click', function () {
        // Remove the pm-sbs-buttons-active class from all buttons
        $('.pm-sbs-buttons').removeClass('pm-sbs-buttons-active');

        // Add the pm-sbs-buttons-active class to the clicked button
        $(this).addClass('pm-sbs-buttons-active');
        // $('.pm_align_items').removeClass('pm_align_items');


        // Check if the Side by Side button is clicked
        if ($(this).hasClass('pm-sbs-button-side')) {
            $('.pm_align_items').addClass('pm_align_items_style'); // Add your new class here
        } else {
            $('.pm_align_items').removeClass('pm_align_items_style'); // Remove your new class here
        }

    });


    if (href.indexOf('/account-admin') > -1 || href.indexOf('/ruaccount-admin') > -1) {
        // delete church admin and all associated users and entries

        // delete church admin and all associated users and entries
        $('#delete_church_admin').on('click', function () {
            var popupHtml = `<div id="pm-overlay" class="pm-cover"></div><div id="unlockPopup" class="pm-unlock-stu-frm-popup">
            <div class="pm-unlock-stu-frm-popup-content">
            <button class="pm-unlock-stu-frm-popup-close">&times;</button>
            <p>Are you sure you want to permanently delete this account? All counselors and couples will be deleted as well.</p>
            <button id="delete_church_admin_confirm" class="button-6 delete_church_admin_confirm">Delete Account</button>
            </div></div>`;

            pm_openPopup(popupHtml);

        })


        $('#pmpopupContainer ').on('click', '#delete_church_admin_confirm', function () {
            // window.alert('Are you sure you want to delete your profile');

            // var result = window.confirm("Are you sure you want to permanently delete this account? All counselors and couples will be deleted as well.");
            var baseUrl = window.location.origin;

            // if (result === true) {

            $.ajax({
                url: cpmAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'pm_delete_all_accounts_entries_under_church_admin',
                },
                success: function (response) {
                    if (response == '1') {
                        // Get the current URL
                        window.location.href = baseUrl;
                    }

                },
                error: function () {
                    // Handle AJAX request errors here if needed
                }
            });

            // }
        });


    }


    // church admin role change Be Counselor Remove Counselor Role

    $('#add_role_counselor').on('click', function () {
        var popupHtml = `<div id="pm-overlay" class="pm-cover"></div><div id="unlockPopup" class="pm-unlock-stu-frm-popup">
        <div class="pm-unlock-stu-frm-popup-content">
        <button class="pm-unlock-stu-frm-popup-close">&times;</button>
        <p>Are you sure you want to be counsellor as well?</p>
        <button id="add_role_counselor_confirm" class="button-6 ">Confirm</button>
        </div></div>`;

        pm_openPopup(popupHtml);

    })

    $('#remove_role_counselor').on('click', function () {
        var popupHtml = `<div id="pm-overlay" class="pm-cover"></div><div id="unlockPopup" class="pm-unlock-stu-frm-popup">
        <div class="pm-unlock-stu-frm-popup-content">
        <button class="pm-unlock-stu-frm-popup-close">&times;</button>
        <p>Are you sure you want to remove counsellor role?</p>
        <button id="remove_role_counselor_confirm" class="button-6 ">Confirm</button>
        </div></div>`;

        pm_openPopup(popupHtml);

    })


    $('#pmpopupContainer ').on('click', '#add_role_counselor_confirm', function () {

        var baseUrl = window.location.origin;
        console.log('clicked');

        $.ajax({
            url: cpmAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'pm_add_new_role_to_user',
            },
            success: function (response) {
                console.log(response);
                if (response == '1') {
                    location.reload();
                }

            },
            error: function () {
                // Handle AJAX request errors here if needed
            }
        });
    });

    $('#pmpopupContainer ').on('click', '#remove_role_counselor_confirm', function () {

        var baseUrl = window.location.origin;
        console.log('clicked');

        $.ajax({
            url: cpmAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'pm_remove_counselor_role_of_user',
            },
            success: function (response) {
                console.log(response);
                if (response == '1') {
                    location.reload();
                }

            },
            error: function () {
                // Handle AJAX request errors here if needed
            }
        });
    });

console.log('here 2');

});