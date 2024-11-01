<?php

/**
 * Tab Class
 * @package     Woocommerce Purolator Small
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Woocommerce Setting Tab Class
 */
class WC_Settings_purolator_Small extends WC_Settings_Page {

    /**
     * Constructor
     */
    public function __construct() {
        $this->id = 'purolator_small';
        add_filter('woocommerce_settings_tabs_array', array($this, 'add_settings_tab'), 50);
        add_action('woocommerce_sections_' . $this->id, array($this, 'output_sections'));
        add_action('woocommerce_settings_' . $this->id, array($this, 'output'));
        add_action('woocommerce_settings_save_' . $this->id, array($this, 'save'));

        $this->avaibility_box_sizing_purolator_small_packages_quotes();
    }

    /**
     * Check automatic residential detection installlation
     */
    function avaibility_box_purolator_small_packages_quotes() {
        $class = "notice notice-warning";
        $message = "Click <a target='_blank' href='https://eniture.com/products/'>here</a> to add the box sizing module (<a target='_blank' href='https://eniture.com/products/'>Learn more</a>)";
        echo"<div class=\"$class\"> <p>$message</p></div>";
    }

    function avaibility_box_sizing_purolator_small_packages_quotes() {
        if (!is_plugin_active('en-standard-box-sizes/en-standard-box-sizes.php') &&
                (isset($_GET['tab'])) && ($_GET['tab'] == "purolator_small") &&
                (isset($_GET['section'])) && ($_GET['section'] == "section-1")) {
            
        }
    }

    /**
     * purolator Small Setting Tab For Woocommerce
     * @param array $settings_tabs
     * @return array
     */
    public function add_settings_tab($settings_tabs) {
        $settings_tabs[$this->id] = __('Purolator', 'woocommerce-settings-purolator_small');
        return $settings_tabs;
    }

    /**
     * purolator Small Setting Sections
     */
    public function get_sections() {
        $sections = array(
            '' => __('Connection Settings', 'woocommerce-settings-purolator_small'),
            'section-1' => __('Quote Settings', 'woocommerce-settings-purolator_small'),
            'section-2' => __('Warehouses', 'woocommerce-settings-purolator_small'),
            // fdo va
            'section-4' => __('FreightDesk Online', 'woocommerce-settings-purolator_small'),
            'section-5' => __('Validate Addresses', 'woocommerce-settings-purolator_small'),
            'section-3' => __('User Guide', 'woocommerce-settings-purolator_small')
        );

        // Logs data
        $enable_logs = get_option('purolator_small_enable_logs');
        if ($enable_logs == 'yes') {
            $sections['en-logs'] = 'Logs';
        }

        $sections = apply_filters('en_woo_addons_sections', $sections, en_woo_plugin_purolator_small);
        return apply_filters('woocommerce_get_sections_' . $this->id, $sections);
    }

    /**
     * purolator Small Warehouse Tab
     */
    public function purolator_small_warehouse() {
        require_once 'warehouse-dropship/wild/warehouse/warehouse_template.php';
        require_once 'warehouse-dropship/wild/dropship/dropship_template.php';
    }

    /**
     * purolator Small User Guide Tab
     */
    public function xpo_user_guide() {
        include_once( 'template/guide.php' );
    }

    /**
     * Settings
     * @param $section
     */
    public function get_settings($section = null) {
        ob_start();
        switch ($section) {
            case 'section-0' :
                $settings = purolator_Small_Connection_Settings::purolator_small_con_setting();
                break;

            case 'section-1' :
                $purolator_small_qsettings = new purolator_Small_Quote_Settings();
                $settings = $purolator_small_qsettings->purolator_small_quote_settings_tab();
                break;
            case 'section-2':
                $this->purolator_small_warehouse();
                $settings = array();
                break;
            case 'section-3' :
                $this->xpo_user_guide();
                $settings = array();
                break;
            // fdo va
            case 'section-4' :
                $this->freightdesk_online_section();
                $settings = [];
                break;

            case 'section-5' :
                $this->validate_addresses_section();
                $settings = [];
                break;

            case 'en-logs' :
                require_once 'logs/en-logs.php';
                $settings = [];
                break;    

            default:
                $purolator_small_con_settings = new purolator_Small_Connection_Settings();
                $settings = $purolator_small_con_settings->purolator_small_con_setting();

                break;
        }
        $settings = apply_filters('en_woo_addons_settings', $settings, $section, "purolator_small");
        $settings = $this->avaibility_addon($settings);
        return apply_filters('woocommerce-settings-purolator_small', $settings, $section);
    }

    /**
     * avaibility_addon 
     * @param array type $settings
     * @return array type
     */
    function avaibility_addon($settings) {
        if (is_plugin_active('en-standard-box-sizes/en-standard-box-sizes.php')) {
            unset($settings['avaibility_box_sizing']);
        }

        return $settings;
    }

    /**
     * Output
     * @global $current_section
     */
    public function output() {
        global $current_section;
        $settings = $this->get_settings($current_section);
        WC_Admin_Settings::output_fields($settings);
    }

    /**
     * purolator Small Save Settings
     */
    public function save() {
        global $current_section;
        $settings = $this->get_settings($current_section);
        if (isset($_POST['purolator_small_orderCutoffTime']) && $_POST['purolator_small_orderCutoffTime'] != '') {
            $time24Formate = $this->getTimeIn24Hours($_POST['purolator_small_orderCutoffTime']);
            $_POST['purolator_small_orderCutoffTime'] = $time24Formate;
        }
        WC_Admin_Settings::save_fields($settings);
    }

    /**
     * @param $timeStr
     * @return false|string
     */
    public function getTimeIn24Hours($timeStr) {
        $cutOffTime = explode(' ', $timeStr);
        $hours = $cutOffTime[0];
        $separator = $cutOffTime[1];
        $minutes = $cutOffTime[2];
        $meridiem = $cutOffTime[3];
        $cutOffTime = "{$hours}{$separator}{$minutes} $meridiem";
        return date("H:i", strtotime($cutOffTime));
    }
    // fdo va
    /**
     * FreightDesk Online section
     */
    public function freightdesk_online_section()
    {
        include_once plugin_dir_path(__FILE__) . 'fdo/freightdesk-online-section.php';
    }

    /**
     * Validate Addresses Section
     */
    public function validate_addresses_section()
    {
        include_once plugin_dir_path(__FILE__) . 'fdo/validate-addresses-section.php';
    }

}

return new WC_Settings_purolator_Small();
