<?php
/**
 * JS
 * @package     Woocommerce Purolator Small
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Connection Section Fields Validation
 * 
 * Quote Setting Section Fields Validation
 * 
 * Test Connection AJAX 
 */
add_action('admin_footer', 'purolator_small_connection_setting');

/**
 * JS
 */
function purolator_small_connection_setting() {
    ?>
    <script>

        // Update plan
        if (typeof en_update_plan != 'function') {
            function en_update_plan(input) {
                let action = jQuery(input).attr('data-action');
                jQuery.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: {action: action},
                    success: function (data_response) {
                        window.location.reload(true);
                    }
                });
            }
        }

        jQuery(document).ready(function () {
            
            var prevent_text_box = jQuery('.prevent_text_box').length;
            if (!prevent_text_box > 0) {
                jQuery("input[name*='wc_pervent_proceed_checkout_eniture']").closest('tr').addClass('wc_pervent_proceed_checkout_eniture');
                jQuery(".wc_pervent_proceed_checkout_eniture input[value*='allow']").after('Allow user to continue to check out and display this message <br><textarea  name="allow_proceed_checkout_eniture" class="prevent_text_box" title="Message" maxlength="250"><?php echo trim(get_option("allow_proceed_checkout_eniture")); ?></textarea><br><span class="description"> Enter a maximum of 250 characters.</span>');
                jQuery(".wc_pervent_proceed_checkout_eniture input[value*='prevent']").after('Prevent user from checking out and display this message<br><textarea name="prevent_proceed_checkout_eniture" class="prevent_text_box" title="Message" maxlength="250"><?php echo trim(get_option("prevent_proceed_checkout_eniture")); ?></textarea><br><span class="description"> Enter a maximum of 250 characters.</span>');
            }
            
            jQuery("#restrict_days_transit_package_purolator_small , #purolator_small_hazardous_fee , #purolator_small_hand_fee_mark_up").focus(function (e) {
                jQuery("#" + this.id).css({'border-color': '#ddd'});
            });

            jQuery("#ground_transit_label").closest('tr').addClass("ground_transit_label");
            jQuery("#purolator_small_hazardous_title").closest('tr').addClass("purolator_small_hazardous_title");
            jQuery("#restrict_days_transit_package_purolator_small").closest('tr').addClass("restrict_days_transit_package_purolator_small");
            jQuery("input[name*='purolator_small_hazardous_fee']").closest('tr').addClass('purolator_small_hazardous_fee');
            jQuery("input[name*='restrict_calendar_transit_small_packages_purolator']").closest('tr').addClass('restrict_calendar_transit_small_packages_purolator');


            jQuery(".purolator_small_quotes_services_markup").closest('tr').addClass("purolator_small_quotes_services_markup_tr");
            jQuery(".purolator_small_quotes_services_markup_label").closest('tr').addClass("purolator_small_quotes_services_markup_label_tr");
            jQuery(".hidden_class").closest('tr').addClass("hidden_class_tr");
            jQuery("#avaibility_box_sizing").closest('tr').addClass("avaibility_box_sizing_tr");
            jQuery("#purolator_small_hand_fee_mark_up").closest('tr').addClass("purolator_small_hand_fee_mark_up_tr");

    //      Estimated delivery options
            jQuery("#purolator_small_cutOffTime_shipDateOffset").closest('tr').addClass("purolator_small_cutOffTime_shipDateOffset_required_label");
            jQuery("#purolator_small_orderCutoffTime").closest('tr').addClass("purolator_small_cutOffTime_shipDateOffset");
            jQuery("#purolator_small_shipmentOffsetDays").closest('tr').addClass("purolator_small_cutOffTime_shipDateOffset");
            jQuery("#purolator_small_timeformate").closest('tr').addClass("purolator_small_timeformate");

            jQuery(".purolator_small_dont_show_estimate_option").closest('tr').addClass("purolator_small_dont_show_estimate_option_tr");
            jQuery("#service_small_estimates_title").closest('tr').addClass("service_small_estimates_title_tr");
            jQuery("input[name=purolator_small_delivery_estimates]").closest('tr').addClass("purolator_small_delivery_estimates_tr");
            jQuery("#service_purolator_small_estimates_title").closest('tr').addClass("service_purolator_small_estimates_title_tr");
            jQuery(".purolator_small_shipment_day").closest('tr').addClass("purolator_small_shipment_day_tr");
            jQuery("#all_shipment_days_purolator_small").closest('tr').addClass("all_shipment_days_purolator_small_tr");
            jQuery('#purolator_small_shipmentOffsetDays').attr('min', 1);
            var fedexSmallCurrentTime = '<?php echo get_option("purolator_small_orderCutoffTime"); ?>';
            if (fedexSmallCurrentTime != '') {
                jQuery('#purolator_small_orderCutoffTime').wickedpicker({
                    now: fedexSmallCurrentTime,
                    title: 'Cut Off Time'
                });
            } else {
                jQuery('#purolator_small_orderCutoffTime').wickedpicker({
                    now: '',
                    title: 'Cut Off Time'
                });
            }
            /*
             * Uncheck Select All Checkbox
             */

            jQuery(".purolator_small_quotes_services").on('change load', function () {
                var checkboxes = jQuery('.purolator_small_quotes_services:checked').size();
                var un_checkboxes = jQuery('.purolator_small_quotes_services').size();
                if (checkboxes === un_checkboxes) {
                    jQuery('.purolator_small_all_services').attr('checked', true);
                } else {
                    jQuery('.purolator_small_all_services').attr('checked', false);
                }
            });

            /*
             * Uncheck Week days Select All Checkbox
             */

            jQuery(".purolator_small_shipment_day").on('change load', function () {
                var checkboxes = jQuery('.purolator_small_shipment_day:checked').size();
                var un_checkboxes = jQuery('.purolator_small_shipment_day').size();
                if (checkboxes === un_checkboxes) {
                    jQuery('.all_shipment_days_purolator_small').attr('checked', true);
                } else {
                    jQuery('.all_shipment_days_purolator_small').attr('checked', false);
                }
            });

            jQuery('#purolator_small_shipmentOffsetDays').on('click', function (event) {
                jQuery('#purolator_small_shipmentOffsetDays').css('border', '');

            })


            var url = getUrlVarsPurolatorSmall()["tab"];
            if (url === 'purolator_small') {
                jQuery('#footer-left').attr('id', 'wc-footer-left');
            }
            /*
             * Add Title To Quote Setting Fields
             */

            jQuery('#purolator_small_hazardous_fee').attr('title', 'Hazardous Material Fee');
            jQuery('#purolator_small_hand_fee_mark_up').attr('title', 'Handling Fee / Markup');


            /*
             * Add maxlength Attribute on Connection Setting Page
             */

            jQuery("#purolator_small_registered_state").attr('maxlength', '2');
            jQuery("#purolator_small_registered_zip").attr('maxlength', '7');

            /*
             * Add maxlength Attribute on Handling Fee Quote Setting Page
             */

            jQuery("#purolator_small_hand_fee_mark_up").attr('maxlength', '7');
            jQuery("#purolator_small_hazardous_fee").attr('maxlength', '7');

            jQuery('.purolator_small_connection_section input[type="text"]').each(function () {
                if (jQuery(this).parent().find('.err').length < 1) {
                    jQuery(this).after('<span class="err"></span>');
                }
            });


            /*
             * Add Title To Connection Setting Fields
             */

            jQuery('.purolator_small_connection_section .form-table').before('<div class="notice-warning purolator_small_warning_message"><p><strong>Note! You have a Purolator Small account to use this application. If you do not have one contact Purolator at 800-742-5877 or <a target="_blank" href="https://eshiponline.purolator.com/ShipOnline/SecurePages/Public/Register.aspx">Register Online</a> </strong></p></div>');
            jQuery('#purolator_small_billing_account_number').attr('title', 'Billing Account Number');
            jQuery('#purolator_small_registered_account_number').attr('title', 'Registered Account Number');
            jQuery('#purolator_small_registered_city').attr('title', 'City');
            jQuery('#purolator_small_registered_state').attr('title', 'State');
            jQuery('#purolator_small_registered_zip').attr('title', 'Zip');
            jQuery('#purolator_small_pro_key').attr('title', 'Production Key ');
            jQuery('#purolator_small_pro_key_pass').attr('title', 'Production Key Password ');
            jQuery('#purolator_small_licence_key').attr('title', 'Plugin License Key');


            /*
             * Add CSS Class To Quote Services
             */

            jQuery('.bold-text').closest('tr').addClass('purolator_small_quotes_services_tr');
            jQuery('.purolator_small_quotes_services').closest('tr').addClass('purolator_small_quotes_services_tr');
            jQuery('.purolator_small_quotes_services').closest('td').addClass('purolator_small_quotes_services_td');
            jQuery('.purolator_small_int_quotes_services').closest('tr').addClass('purolator_small_quotes_services_tr');
            jQuery('.purolator_small_int_quotes_services').closest('td').addClass('purolator_small_quotes_services_td');
            jQuery('.purolator_small_ww_quotes_services').closest('tr').addClass('purolator_small_quotes_services_tr');
            jQuery('.purolator_small_ww_quotes_services').closest('td').addClass('purolator_small_quotes_services_td');


            //** Start: Validation for Canada to Canada service level markup

            jQuery(".purolator_small_quotes_services_markup").keydown(function (e) {
                // Allow: backspace, delete, tab, escape, enter and .
                if (jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190, 53, 189]) !== -1 ||
                        // Allow: Ctrl+A, Command+A
                                (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
                                // Allow: home, end, left, right, down, up
                                        (e.keyCode >= 35 && e.keyCode <= 40)) {
                            // let it happen, don't do anything
                            return;
                        }
                        // Ensure that it is a number and stop the keypress
                        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                            e.preventDefault();
                        }

                        if ((jQuery(this).val().indexOf('.') != -1) && (jQuery(this).val().substring(jQuery(this).val().indexOf('.'), jQuery(this).val().indexOf('.').length).length > 2)) {
                            if (e.keyCode !== 8 && e.keyCode !== 46) { //exception
                                e.preventDefault();
                            }
                        }

                    });
            //** Start: Validat Shipment Offset Days
            jQuery("#purolator_small_shipmentOffsetDays").keydown(function (e) {
                if (e.keyCode == 8)
                    return;

                var val = jQuery("#purolator_small_shipmentOffsetDays").val();
                if (val.length > 1 || e.keyCode == 190) {
                    e.preventDefault();
                }
                // Allow: backspace, delete, tab, escape, enter and .
                if (jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190, 53, 189]) !== -1 ||
                        // Allow: Ctrl+A, Command+A
                                (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
                                // Allow: home, end, left, right, down, up
                                        (e.keyCode >= 35 && e.keyCode <= 40)) {
                            // let it happen, don't do anything
                            return;
                        }
                        // Ensure that it is a number and stop the keypress
                        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                            e.preventDefault();
                        }

                    });
            // Allow: only positive numbers
            jQuery("#purolator_small_shipmentOffsetDays").keyup(function (e) {
                if (e.keyCode == 189) {
                    e.preventDefault();
                    jQuery("#purolator_small_shipmentOffsetDays").val('');
                }

            });
            //**Start: Validation of handling fee
            jQuery("#purolator_small_hand_fee_mark_up").keydown(function (e) {
                // Allow: backspace, delete, tab, escape, enter and .
                if (e.keyCode != 189 && e.keyCode != 109) {
                    if (jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 53, 190]) !== -1 ||
                            // Allow: Ctrl+A, Command+A
                                    (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
                                    // Allow: home, end, left, right, down, up
                                            (e.keyCode >= 35 && e.keyCode <= 40))
                            {
                                // let it happen, don't do anything
                                return;
                            }

                            // Ensure that it is a number and stop the keypress
                            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                                e.preventDefault();
                            }

                            if ((jQuery(this).val().indexOf('.') != -1) && (jQuery(this).val().substring(jQuery(this).val().indexOf('.'), jQuery(this).val().indexOf('.').length).length > 2)) {
                                if (e.keyCode !== 8 && e.keyCode !== 46) { //exception
                                    e.preventDefault();
                                }
                            }
                        }

                    });
            jQuery("#purolator_small_hand_fee_mark_up").keyup(function (e) {

                var val = jQuery("#purolator_small_hand_fee_mark_up").val();

                if (val.split('.').length - 1 > 1) {

                    var newval = val.substring(0, val.length - 1);
                    var countDots = newval.substring(newval.indexOf('.') + 1).length;
                    newval = newval.substring(0, val.length - countDots - 1);
                    jQuery("#purolator_small_hand_fee_mark_up").val(newval);
                }
                if (val.split('-').length - 1 > 1) {

                    var newval = val.substring(0, val.length - 1);
                    var countDots = newval.substring(newval.indexOf('-') + 1).length;
                    newval = newval.substring(0, val.length - countDots - 1);
                    jQuery("#purolator_small_hand_fee_mark_up").val(newval);
                }
                if (val.splipurolator_small_express_markupt('%').length - 1 > 1) {

                    var newval = val.substring(0, val.length - 1);
                    var countDots = newval.substring(newval.indexOf('%') + 1).length;
                    newval = newval.substring(0, val.length - countDots - 1);
                    jQuery("#purolator_small_hand_fee_mark_up").val(newval);
                }
                if (val.split('>').length - 1 > 0) {
                    var newval = val.substring(0, val.length - 1);
                    var countGreaterThan = newval.substring(newval.indexOf('>') + 1).length;
                    newval = newval.substring(newval, newval.length - countGreaterThan - 1);
                    jQuery("#purolator_small_hand_fee_mark_up").val(newval);
                }
                if (val.split('_').length - 1 > 0) {
                    var newval = val.substring(0, val.length - 1);
                    var countUnderScore = newval.substring(newval.indexOf('_') + 1).length;
                    newval = newval.substring(newval, newval.length - countUnderScore - 1);
                    jQuery("#purolator_small_hand_fee_mark_up").val(newval);
                }
            });

            jQuery("#purolator_small_hazardous_fee").keydown(function (e) {
                // Allow: backspace, delete, tab, escape, enter and .
                if (jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
                        // Allow: Ctrl+A, Command+A
                                (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
                                // Allow: home, end, left, right, down, up
                                        (e.keyCode >= 35 && e.keyCode <= 40))
                        {
                            // let it happen, don't do anything
                            return;
                        }

                        // Ensure that it is a number and stop the keypress
                        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                            e.preventDefault();
                        }

                        if ((jQuery(this).val().indexOf('.') != -1) && (jQuery(this).val().substring(jQuery(this).val().indexOf('.'), jQuery(this).val().indexOf('.').length).length > 2)) {
                            if (e.keyCode !== 8 && e.keyCode !== 46) { //exception
                                e.preventDefault();
                            }
                        }

                    });
            jQuery("#purolator_small_hazardous_fee").keyup(function (e) {

                var val = jQuery("#purolator_small_hazardous_fee").val();

                if (val.split('.').length - 1 > 1) {

                    var newval = val.substring(0, val.length - 1);
                    var countDots = newval.substring(newval.indexOf('.') + 1).length;
                    newval = newval.substring(0, val.length - countDots - 1);
                    jQuery("#purolator_small_hazardous_fee").val(newval);
                }
                if (val.split('>').length - 1 > 0) {
                    var newval = val.substring(0, val.length - 1);
                    var countGreaterThan = newval.substring(newval.indexOf('>') + 1).length;
                    newval = newval.substring(newval, newval.length - countGreaterThan - 1);
                    jQuery("#purolator_small_hazardous_fee").val(newval);
                }
                if (val.split('_').length - 1 > 0) {
                    var newval = val.substring(0, val.length - 1);
                    var countUnderScore = newval.substring(newval.indexOf('_') + 1).length;
                    newval = newval.substring(newval, newval.length - countUnderScore - 1);
                    jQuery("#purolator_small_hazardous_fee").val(newval);
                }
            });

            jQuery(".purolator_small_quotes_services_markup").keydown(function (e) {
                // Allow: backspace, delete, tab, escape, enter and .
                if (jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190, 53, 189]) !== -1 ||
                        // Allow: Ctrl+A, Command+A
                                (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
                                // Allow: home, end, left, right, down, up
                                        (e.keyCode >= 35 && e.keyCode <= 40)) {
                            // let it happen, don't do anything
                            return;
                        }
                        // Ensure that it is a number and stop the keypress
                        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                            e.preventDefault();
                        }

                        if ((jQuery(this).val().indexOf('.') != -1) && (jQuery(this).val().substring(jQuery(this).val().indexOf('.'), jQuery(this).val().indexOf('.').length).length > 2)) {
                            if (e.keyCode !== 8 && e.keyCode !== 46) { //exception
                                e.preventDefault();
                            }
                        }

                    });
            jQuery(".purolator_small_quotes_services_markup").keyup(function (e) {

                var selected_domestic_id = jQuery(this).attr("id");
                jQuery("#" + selected_domestic_id).css({"border": "1px solid #ddd"});

                var val = jQuery("#" + selected_domestic_id).val();
                if (val.split('.').length - 1 > 1) {
                    var newval = val.substring(0, val.length - 1);
                    var countDots = newval.substring(newval.indexOf('.') + 1).length;
                    newval = newval.substring(0, val.length - countDots - 1);
                    jQuery("#" + selected_domestic_id).val(newval);

                }
                if (val.split('%').length - 1 > 1) {
                    var newval = val.substring(0, val.length - 1);
                    var countPercentages = newval.substring(newval.indexOf('%') + 1).length;
                    newval = newval.substring(0, val.length - countPercentages - 1);
                    jQuery("#" + selected_domestic_id).val(newval);
                }
                if (val.split('>').length - 1 > 0) {
                    var newval = val.substring(0, val.length - 1);
                    var countGreaterThan = newval.substring(newval.indexOf('>') + 1).length;
                    newval = newval.substring(newval, newval.length - countGreaterThan - 1);
                    jQuery("#" + selected_domestic_id).val(newval);
                }
                if (val.split('_').length - 1 > 0) {
                    var newval = val.substring(0, val.length - 1);
                    var countUnderScore = newval.substring(newval.indexOf('_') + 1).length;
                    newval = newval.substring(newval, newval.length - countUnderScore - 1);
                    jQuery("#" + selected_domestic_id).val(newval);
                }
                if (val.split('-').length - 1 > 1) {
                    var newval = val.substring(0, val.length - 1);
                    var countPercentages = newval.substring(newval.indexOf('-') + 1).length;
                    newval = newval.substring(0, val.length - countPercentages - 1);
                    jQuery("#" + selected_domestic_id).val(newval);
                }
            });

            jQuery("#restrict_days_transit_package_purolator_small").keydown(function (e) {
                if (e.keyCode != 8) {
                    // Allow: backspace, delete, tab, escape, enter and .
                    if ((e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
                            // Allow: home, end, left, right, down, up
                                    (e.keyCode >= 35 && e.keyCode <= 40))
                    {
                        // let it happen, don't do anything
                        return;
                    }

                    // Ensure that it is a number and stop the keypress
                    if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                        e.preventDefault();
                    }

                    if ((jQuery(this).val().indexOf('.') != -1) && (jQuery(this).val().substring(jQuery(this).val().indexOf('.'), jQuery(this).val().indexOf('.').length).length > 2)) {
                        if (e.keyCode !== 8 && e.keyCode !== 46) { //exception
                            e.preventDefault();
                        }
                    }
                }

            });


            /*
             * Uncheck Select All Checkbox
             */

            jQuery(".purolator_small_domestic_quote_service").on('change load', function () {
                var checkboxes = jQuery('.purolator_small_domestic_quote_service:checked').size();
                var un_checkboxes = jQuery('.purolator_small_domestic_quote_service').size();
                if (checkboxes === un_checkboxes) {
                    jQuery('.purolator_small_all_services').attr('checked', true);
                } else {
                    jQuery('.purolator_small_all_services').attr('checked', false);
                }
            });

            /*
             * Uncheck International Services Select All Checkbox
             */

            jQuery(".purolator_small_int_quotes_services").on('change load', function () {
                var int_checkboxes = jQuery('.purolator_small_int_quotes_services:checked').size();
                var int_un_checkboxes = jQuery('.purolator_small_int_quotes_services').size();
                if (int_checkboxes === int_un_checkboxes) {
                    jQuery('.purolator_small_all_int_services').attr('checked', true);
                } else {
                    jQuery('.purolator_small_all_int_services').attr('checked', false);
                }
            });

            /*
             * Uncheck world wide Services Select All Checkbox
             */

            jQuery(".purolator_small_ww_quotes_services").on('change load', function () {
                var int_checkboxes = jQuery('.purolator_small_ww_quotes_services:checked').size();
                var int_un_checkboxes = jQuery('.purolator_small_ww_quotes_services').size();
                if (int_checkboxes === int_un_checkboxes) {
                    jQuery('.purolator_small_all_ww_services').attr('checked', true);
                } else {
                    jQuery('.purolator_small_all_ww_services').attr('checked', false);
                }
            });

            /*
             * Save Changes Action
             */

            jQuery('.purolator_small_quote_section .button-primary').on('click', function (e) {

                jQuery('.error').remove();
                jQuery('.updated').remove();

                var checkedValCustomMsg = jQuery("input[name='wc_pervent_proceed_checkout_eniture']:checked").val();
                var allow_proceed_checkout_eniture = jQuery("textarea[name=allow_proceed_checkout_eniture]").val();
                var prevent_proceed_checkout_eniture = jQuery("textarea[name=prevent_proceed_checkout_eniture]").val();

                if (checkedValCustomMsg == 'allow' && allow_proceed_checkout_eniture == '') {
                    jQuery("#mainform .purolator_small_quote_section").prepend('<div id="message" class="error inline purolator_small_custom_error_message"><p><strong>Error!</strong> Custom message field is empty.</p></div>');
                    jQuery('html, body').animate({
                        'scrollTop': jQuery('.purolator_small_custom_error_message').position().top
                    });
                    return false
                } else if (checkedValCustomMsg == 'prevent' && prevent_proceed_checkout_eniture == '') {
                    jQuery("#mainform .purolator_small_quote_section").prepend('<div id="message" class="error inline purolator_small_custom_error_message"><p><strong>Error!</strong> Custom message field is empty.</p></div>');
                    jQuery('html, body').animate({
                        'scrollTop': jQuery('.purolator_small_custom_error_message').position().top
                    });
                    return false
                }

                if (!groundTransitValidation()) {
                    return false;
                } else if (!handlingFeeValidation()) {
                    return false;
                } else if (!hazardousMaterialFeeValidation()) {
                    return false;
                }

                let purolator_small_quotes_services_markup = jQuery('.purolator_small_quotes_services_markup');
                jQuery(purolator_small_quotes_services_markup).each(function () {

                    if (jQuery('#' + this.id).val() != '' && !purolator_markup_service(this.id)) {
                        e.preventDefault();
                        return false;
                    }
                });

                var num_of_checkboxes = jQuery('.purolator_small_domestic_quote_service:checked').size();
                var num_of_int_checkboxes = jQuery('.purolator_small_int_quotes_services:checked').size();
                var num_of_ww_checkboxes = jQuery('.purolator_small_ww_quotes_services:checked').size();
                /*
                 * Check Number of Selected Services
                 */

                if (num_of_checkboxes < 1 && num_of_int_checkboxes < 1 && num_of_ww_checkboxes < 1) {
                    no_service_selected_purolator_small();
                    return false;
                }

                var purolator_services = [
                    'purolator_small_express_markup', 'purolator_small_express_9_markup', 'purolator_small_express_10_markup',
                    'purolator_small_ground_markup', 'purolator_small_ground_90_markup', 'purolator_small_ground_100_markup',
                    'purolator_small_ground_us_markup', 'purolator_small_express_us_markup', 'purolator_small_express_us_9am_markup',
                    'purolator_small_express_us_10am_markup',
                    'purolator_small_express_inter_markup', 'purolator_small_express_inter_12_markup',
                ];

                jQuery.each(purolator_services, function (index, service_id) {

                    jQuery('.error').remove();
                    jQuery('.updated').remove();
                    var afterDecimalValue = 0;

                    var service_Value = jQuery('#' + service_id).val();
                    var oldservice_Value = service_Value;
                    if (service_Value.slice(service_Value.length - 1) == '%') {
                        service_Value = service_Value.slice(0, service_Value.length - 1);
                    }

                    var split_service_value = service_Value.split('.');

                    if (split_service_value.length == 2 && (split_service_value[1] == '%' || split_service_value[1] == '')) {
                        afterDecimalValue = split_service_value[1];
                        jQuery("#" + service_id).css({"border-color": "red"});
                        jQuery("#mainform .purolator_small_quote_section").prepend('<div id="message" class="error inline purolator_small_service_markup_fee_error"><p><strong>Error! </strong>Service Level Markup fee format should be 100.20 or 10%.</p></div>');
                        jQuery('html, body').animate({
                            'scrollTop': jQuery('.purolator_small_service_markup_fee_error').position().top
                        });
                        return false
                    }
                    if (oldservice_Value !== "" && (oldservice_Value == '%' || service_Value == '%' || service_Value == '.' || isValidNumber(service_Value) == false || afterDecimalValue.length > 2)) {
                        jQuery("#" + service_id).css({"border-color": "red"});
                        jQuery("#mainform .purolator_small_quote_section").prepend('<div id="message" class="error inline purolator_small_service_markup_fee_error"><p><strong>Error! </strong>Service Level Markup fee format should be 100.20 or 10%.</p></div>');
                        jQuery('html, body').animate({
                            'scrollTop': jQuery('.purolator_small_service_markup_fee_error').position().top
                        });
                        return false
                    }
                });

                var purolator_small_days = jQuery("#purolator_small_shipmentOffsetDays").val();
                if (purolator_small_days != "" && purolator_small_days < 1) {

                    jQuery("#mainform .purolator_small_quote_section").prepend('<div id="message" class="error inline purolator_small_orderCutoffTime_error"><p><strong>Error! </strong>Days should not be less than 1.</p></div>');
                    jQuery('html, body').animate({
                        'scrollTop': jQuery('.purolator_small_orderCutoffTime_error').position().top
                    });
                    jQuery("#purolator_small_shipmentOffsetDays").css({'border-color': '#e81123'});
                    return false
                }
                if (purolator_small_days != "" && purolator_small_days > 8) {

                    jQuery("#mainform .purolator_small_quote_section").prepend('<div id="message" class="error inline purolator_small_orderCutoffTime_error"><p><strong>Error! </strong> Days should be less than or equal to 8.</p></div>');
                    jQuery('html, body').animate({
                        'scrollTop': jQuery('.purolator_small_orderCutoffTime_error').position().top
                    });
                    jQuery("#purolator_small_shipmentOffsetDays").css({'border-color': '#e81123'});
                    return false
                }

                var numberOnlyRegex = /^[0-9]+$/;

                if (purolator_small_days != "" && !numberOnlyRegex.test(purolator_small_days)) {

                    jQuery("#mainform .purolator_small_quote_section").prepend('<div id="message" class="error inline purolator_small_orderCutoffTime_error"><p><strong>Error! </strong>Entered Days are not valid.</p></div>');
                    jQuery('html, body').animate({
                        'scrollTop': jQuery('.purolator_small_orderCutoffTime_error').position().top
                    });
                    jQuery("#purolator_small_shipmentOffsetDays").css({'border-color': '#e81123'});
                    return false
                }

                return true;
            });


        });

        function groundTransitValidation() {
            var ground_transit_value = jQuery('#restrict_days_transit_package_purolator_small').val();
            var ground_transit_regex = /^[0-9]{1,2}$/;
            if (ground_transit_value != '' && !ground_transit_regex.test(ground_transit_value)) {
                jQuery("#mainform .purolator_small_quote_section").prepend('<div id="message" class="error inline purolator_ground_transit_error"><p><strong>Error! </strong>Maximum 2 numeric characters are allowed for transit day field.</p></div>');
                jQuery('html, body').animate({
                    'scrollTop': jQuery('.purolator_ground_transit_error').position().top
                });
                jQuery("#restrict_days_transit_package_purolator_small").css({'border-color': '#e81123'});
                return false;
            } else {
                return true;
            }
        }

        function hazardousMaterialFeeValidation() {
            var hazardous_fee = jQuery('#purolator_small_hazardous_fee').val();
            var hazardous_fee_regex = /^(-?[0-9]{1,4}?)$|(\.[0-9]{1,2})$/;
            if (hazardous_fee != '' && !hazardous_fee_regex.test(hazardous_fee) || hazardous_fee.split('.').length - 1 > 1) {
                jQuery("#mainform .purolator_small_quote_section").prepend('<div id="message" class="error inline purolator_small_hazardous_fee_error"><p><strong>Error! </strong>Hazardous material  fee format should be 100.20.</p></div>');
                jQuery('html, body').animate({
                    'scrollTop': jQuery('.purolator_small_hazardous_fee_error').position().top
                });
                jQuery("#purolator_small_hazardous_fee").css({'border-color': '#e81123'});
                return false;
            } else {
                return true;
            }
        }

        function handlingFeeValidation() {

            var handling_fee = jQuery('#purolator_small_hand_fee_mark_up').val();
            var handling_fee_regex = /^(-?[0-9]{1,4}%?)$|(\.[0-9]{1,2})%?$/;
            if (handling_fee != '' && !handling_fee_regex.test(handling_fee) || handling_fee.split('.').length - 1 > 1) {
                jQuery("#mainform .purolator_small_quote_section").prepend('<div id="message" class="error inline purolator_handlng_fee_error"><p><strong>Error! </strong>Handling fee format should be 100.20 or 10%.</p></div>');
                jQuery('html, body').animate({
                    'scrollTop': jQuery('.purolator_handlng_fee_error').position().top
                });
                jQuery("#purolator_small_hand_fee_mark_up").css({'border-color': '#e81123'});
                return false;
            } else {
                return true;
            }
        }

        function purolator_markup_service(id) {

            var purolator_markup_service = jQuery('#' + id).val();
            var purolator_markup_service_regex = /^(-?[0-9]{1,4}%?)$|(\.[0-9]{1,2})%?$/;

            if (!purolator_markup_service_regex.test(purolator_markup_service)) {
                jQuery("#mainform .purolator_small_quote_section").prepend('<div id="message" class="error inline purolator_small_dom_markup_service_error"><p><strong>Error! </strong>Service Level Markup fee format should be 100.20 or 10%.</p></div>');
                jQuery('html, body').animate({
                    'scrollTop': jQuery('.purolator_small_dom_markup_service_error').position().top
                });
                jQuery("#" + id).css({'border-color': '#e81123'});
                return false;
            } else {
                return true;
            }
        }

        /*
         * Select All Services
         */

        var sm_all_checkboxes = jQuery('.purolator_small_domestic_quote_service');
        if (sm_all_checkboxes.length === sm_all_checkboxes.filter(":checked").length) {
            jQuery('.purolator_small_all_services').prop('checked', true);
        }

        jQuery(".purolator_small_all_services").change(function () {
            if (this.checked) {
                jQuery(".purolator_small_domestic_quote_service").each(function () {
                    this.checked = true;
                })
            } else {
                jQuery(".purolator_small_domestic_quote_service").each(function () {
                    this.checked = false;
                })
            }
        });

        /*
         * Select All Services International
         */

        var all_int_checkboxes = jQuery('.purolator_small_int_quotes_services');
        if (all_int_checkboxes.length === all_int_checkboxes.filter(":checked").length) {
            jQuery('.purolator_small_all_int_services').prop('checked', true);
        }

        jQuery(".purolator_small_all_int_services").change(function () {
            if (this.checked) {
                jQuery(".purolator_small_int_quotes_services").each(function () {
                    this.checked = true;
                })
            } else {
                jQuery(".purolator_small_int_quotes_services").each(function () {
                    this.checked = false;
                })
            }
        });
        /*
         * Select All World Wide Services International
         */

        var all_int_checkboxes = jQuery('.purolator_small_ww_quotes_services');
        if (all_int_checkboxes.length === all_int_checkboxes.filter(":checked").length) {
            jQuery('.purolator_small_all_ww_services').prop('checked', true);
        }

        jQuery(".purolator_small_all_ww_services").change(function () {
            if (this.checked) {
                jQuery(".purolator_small_ww_quotes_services").each(function () {
                    this.checked = true;
                })
            } else {
                jQuery(".purolator_small_ww_quotes_services").each(function () {
                    this.checked = false;
                })
            }
        });

        /*
         * Select All Shipment Week days
         */

        var all_int_checkboxes = jQuery('.all_shipment_days_purolator_small');
        if (all_int_checkboxes.length === all_int_checkboxes.filter(":checked").length) {
            jQuery('.all_shipment_days_purolator_small').prop('checked', true);
        }

        jQuery(".all_shipment_days_purolator_small").change(function () {
            if (this.checked) {
                jQuery(".purolator_small_shipment_day").each(function () {
                    this.checked = true;
                });
            } else {
                jQuery(".purolator_small_shipment_day").each(function () {
                    this.checked = false;
                });
            }
        });

        /**
         * Read a page's GET URL variables and return them as an associative array.
         */
        function getUrlVarsPurolatorSmall()
        {
            var vars = [], hash;
            var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
            for (var i = 0; i < hashes.length; i++)
            {
                hash = hashes[i].split('=');
                vars.push(hash[0]);
                vars[hash[0]] = hash[1];
            }
            return vars;
        }

        /*
         * Validate Selecting Services 
         */

        function no_service_selected_purolator_small()
        {
            jQuery('.error').remove();
            jQuery('.updated').remove();
            jQuery(".updated").hide();
            jQuery(".purolator_small_quote_section").before('<div id="message" class="error inline purolator_small_no_srvc_select"><p><strong>Error! </strong>Please select at least one quote service.</p></div>');
            jQuery('html, body').animate({
                'scrollTop': jQuery('.purolator_small_no_srvc_select').position().top
            });
            return false;
        }

        /*
         * Validate Input If Empty or Invalid
         */

        function validateInput(form_id)
        {
            var has_err = true;
            jQuery(form_id + " input[type='text']").each(function () {
                var input = jQuery(this).val();
                var response = validateString(input);

                var errorElement = jQuery(this).parent().find('.err');
                jQuery(errorElement).html('');
                var errorText = jQuery(this).attr('title');
                var optional = jQuery(this).data('optional');
                optional = (optional === undefined) ? 0 : 1;
                errorText = (errorText != undefined) ? errorText : '';
                if ((optional == 0) && (response == false || response == 'empty')) {
                    errorText = (response == 'empty') ? errorText + ' is required.' : 'Invalid input.';
                    jQuery(errorElement).html(errorText);
                }
                has_err = (response != true && optional == 0) ? false : has_err;
            });
            return has_err;
        }

        /*
         * Check Input Value Is Not String
         */

        function isValidNumber(value, noNegative)
        {
            if (typeof (noNegative) === 'undefined')
                noNegative = false;
            var isValidNumber = false;
            var validNumber = (noNegative == true) ? parseFloat(value) >= 0 : true;
            if ((value == parseInt(value) || value == parseFloat(value)) && (validNumber)) {
                if (value.indexOf(".") >= 0) {
                    var n = value.split(".");
                    if (n[n.length - 1].length <= 4) {
                        isValidNumber = true;
                    } else {
                        isValidNumber = 'decimal_point_err';
                    }
                } else {
                    isValidNumber = true;
                }
            }
            return isValidNumber;
        }

        /*
         * Validate Input String 
         */

        function validateString(string)
        {
            if (string == '') {
                return 'empty';
            } else {
                return true;
            }
        }

        /*
         * Connection Settings Input Validation On Save 
         */

        jQuery(".purolator_small_connection_section .button-primary").click(function ()
        {
            var input = validateInput('.purolator_small_connection_section');
            if (input === false) {
                return false;
            }
        });

        /*
         * Test Connection 
         */

        jQuery(".purolator_small_connection_section .woocommerce-save-button").before('<a href="javascript:void(0)" class="button-primary purolator_small_test_connection">Test Connection</a>');
        jQuery('.purolator_small_test_connection').click(function (e)
        {
            var input = validateInput('.purolator_small_connection_section');
            if (input === false) {
                return false;
            }
            var postForm = {
                'action': 'purolator_small_test_connection',
                'purolator_small_billing_acc_number': jQuery('#purolator_small_billing_account_number').val(),
                'purolator_small_registered_acc_number': jQuery('#purolator_small_registered_account_number').val(),
                'purolator_small_registered_city': jQuery('#purolator_small_registered_city').val(),
                'purolator_small_registered_state': jQuery('#purolator_small_registered_state').val(),
                'purolator_small_registered_zip': jQuery('#purolator_small_registered_zip').val(),
                'purolator_small_pro_key': jQuery('#purolator_small_pro_key').val(),
                'purolator_small_pro_key_pass': jQuery('#purolator_small_pro_key_pass').val(),
                'purolator_small_license': jQuery('#purolator_small_licence_key').val(),
            };
            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: postForm,
                dataType: 'json',
                beforeSend: function ()
                {
                    jQuery('#purolator_small_billing_account_number, #purolator_small_registered_account_number, #purolator_small_registered_city, #purolator_small_registered_state, #purolator_small_registered_zip, #purolator_small_pro_key, #purolator_small_pro_key_pass, #purolator_small_licence_key').addClass('purolator_small_test_conn_prosessing');
                },

                success: function (data)
                {
                    if (data['Error']) {

                        jQuery(".updated").hide();
                        jQuery(".purolator_small_error_message").remove();
                        jQuery(".purolator_small_success_message").remove();
                        jQuery('#purolator_small_billing_account_number, #purolator_small_registered_account_number, #purolator_small_registered_city, #purolator_small_registered_state, #purolator_small_registered_zip, #purolator_small_pro_key, #purolator_small_pro_key_pass, #purolator_small_licence_key').removeClass('purolator_small_test_conn_prosessing');
                        jQuery('.purolator_small_warning_message').before('<div class="notice notice-error purolator_small_error_message"><p><strong>Error! </strong>' + data['Error'] + '</p></div>');
                    } else {
                        jQuery(".updated").hide();
                        jQuery('#purolator_small_billing_account_number, #purolator_small_registered_account_number, #purolator_small_registered_city, #purolator_small_registered_state, #purolator_small_registered_zip, #purolator_small_pro_key, #purolator_small_pro_key_pass, #purolator_small_licence_key').removeClass('purolator_small_test_conn_prosessing');
                        jQuery(".purolator_small_success_message").remove();
                        jQuery(".purolator_small_error_message").remove();
                        jQuery('.purolator_small_warning_message').before('<div class="notice notice-success purolator_small_success_message"><p><strong>Success!</strong> The test resulted in a successful connection.</p></div>');

                    }
                }

            });
            e.preventDefault();
        });
    </script>
    <?php
}
