<?php
/**
 * The dashboard-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Car_share
 * @subpackage Car_share/admin
 */

/**
 * The dashboard-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    Car_share
 * @subpackage Car_share/admin
 * @author     My name <mail@example.com>
 */
class Car_share_Setting {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $car_share    The ID of this plugin.
     */
    private $car_share;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @var      string    $car_share       The name of this plugin.
     * @var      string    $version    The version of this plugin.
     */
    public function __construct($car_share, $version) {

        $this->car_share = $car_share;
        $this->version = $version;

        add_action('admin_menu', array($this, 'add_plugin_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));

        // Add an action link pointing to the options page.
        $plugin_basename = plugin_basename(plugin_dir_path(__DIR__) . $this->car_share . '.php');
        add_filter('plugin_action_links_' . $plugin_basename, array($this, 'add_action_links'));

        // Setting
    }

    /**
     * Register the administration menu for this plugin into the WordPress Dashboard menu.
     */
    public function add_plugin_admin_menu() {
        /*
         * Add a settings page for this plugin to the Settings menu.
         *
         */
        $this->plugin_screen_hook_suffix = add_menu_page(
                __('Car plugin settings', $this->car_share), 
                __('Car plugin setting', $this->car_share), 
                'manage_options', 
                $this->car_share, 
                array($this, 'display_plugin_admin_page'),
                'dashicons-admin-tools',
                90
        );

        add_submenu_page(
                $this->car_share, 
                __('Checkout form setup', $this->car_share), 
                __('Checkout form setup', $this->car_share), 
                'manage_options', 
                'checkout-form-setup', 
                array($this, 'checkout_form_setup')
        );        
    }

    public function checkout_form_setup() {
        //$screen = get_current_screen();
        if (isset($_POST['save_checkout_form_setup'])) { 
            $default_fields = get_default_checkout_fields();
            $arr_to_save = array();
            foreach ($default_fields as $input_key => $input_value) { 
                $enabled = isset($_POST['billing_inputs'][$input_key]['enabled']) && 1 == $_POST['billing_inputs'][$input_key]['enabled'] ? 1 : 0;
                $required = isset($_POST['billing_inputs'][$input_key]['required']) && 1 == $_POST['billing_inputs'][$input_key]['required'] ? 1 : 0; 
                $arr_to_save[$input_key] = array(
                    'enabled' => $enabled,
                    'required' => $required,
                );
            }
            update_option('sc-checkout-inputs', $arr_to_save);
        }

        $checkout_fields = get_checkout_fields(); 
        include_once( 'partials/checkout_form_setup.php' );
        wp_nonce_field(__FILE__, 'checkout_form_nonce');
    }

    /**
     * Render the settings page for this plugin.
     *
     * removed from the plugin added by me
     */
    public function display_plugin_admin_page() {
        include_once( 'partials/car_share-admin-display.php' );
    } 
    function register_settings() { 
        /**
         * Rent car subsection
         */
        add_settings_section(
                'main-settings-demand-deposit', __('Rent car setting', $this->car_share), array($this, 'print_demand_deposit_section_info'), 'car-share-deposit-settings-section'
        ); 
        add_settings_field(
                'deposit_amount_field', __('Payable now amount (%)', $this->car_share), array($this, 'create_input_deposit_amount'), 'car-share-deposit-settings-section', 'main-settings-demand-deposit'
        ); 
        add_settings_field(
                'deposit_active_field', __('Apply payable now', $this->car_share), array($this, 'create_input_deposit_active'), 'car-share-deposit-settings-section', 'main-settings-demand-deposit'
        ); 
        add_settings_field(
                'block_car_field', __('Block car interval', $this->car_share), array($this, 'create_input_block_car_interval'), 'car-share-deposit-settings-section', 'main-settings-demand-deposit'
        );

        /*
        add_settings_field(
                'block_till_next_day_box', __('Block car till next day', $this->car_share), array($this, 'create_block_till_next_day'), 'car-share-deposit-settings-section', 'main-settings-demand-deposit'
        );     */

        add_settings_field(
                'block_diff_location_car_field', __('Block car interval after return to another location', $this->car_share), array($this, 'create_input_block_car_interval_diff_location'), 'car-share-deposit-settings-section', 'main-settings-demand-deposit'
        );

        /*
        add_settings_field(
                'block_till_next_day_diff', __('Block car till next day after return to another location (hours)  ', $this->car_share), array($this, 'create_block_to_next_day_diff_loc'), 'car-share-deposit-settings-section', 'main-settings-demand-deposit'
        );
        */
        register_setting('deposit-setting-group', 'sc_setting', array($this, 'sc_settings_validate'));




        // add_settings_section( $id, $title, $callback, $page )
        add_settings_section(
                'main-settings-section', 'General Settings', array($this, 'print_main_settings_section_info'), 'car-plugin-main-settings-section'
        );
        // add_settings_field( $id, $title, $callback, $page, $section, $args )
        
        add_settings_field(
                'name_of_company', __('Name of the company:', 'car_share'), array($this, 'create_input_name_of_company'), 'car-plugin-main-settings-section', 'main-settings-section'
        );    
        
        add_settings_field(
                'sc_logo_url', __('Logo url:', 'car_share'), array($this, 'create_input_email_logo_url'), 'car-plugin-main-settings-section', 'main-settings-section'
        );          
        
        add_settings_field(
                'sc_footer_text', __('Email footer text:', 'car_share'), array($this, 'create_input_email_footer_text'), 'car-plugin-main-settings-section', 'main-settings-section'
        );           
        
        add_settings_field(
                'notemail', __('Notification Email:', 'car_share'), array($this, 'create_input_some_setting'), 'car-plugin-main-settings-section', 'main-settings-section'
        );
        add_settings_field(
                'showcategory', __('Show category:', 'car_share'), array($this, 'create_input_some_show_cat'), 'car-plugin-main-settings-section', 'main-settings-section'
        ); 
        add_settings_field(
                'catalogoption', __('Catalog - order by email', 'car_share'), array($this, 'create_input_some_show_catalog'), 'car-plugin-main-settings-section', 'main-settings-section'
        );
        
        
        
        // register_setting( $option_group, $option_name, $sanitize_callback )

        /*
        add_settings_field(
                'companyname-setting', 'Company Name:', array($this, 'create_name_setting'), 'car-plugin-main-settings-section', 'main-settings-section'
        );

        add_settings_field(
                'streetaddress-setting', 'Street Address:', array($this, 'create_input_street_setting'), 'car-plugin-main-settings-section', 'main-settings-section'
        );

        add_settings_field(
                'city-setting', 'City:', array($this, 'create_input_city_setting'), 'car-plugin-main-settings-section', 'main-settings-section'
        );

        add_settings_field(
                'state-setting', 'State:', array($this, 'create_input_state_setting'), 'car-plugin-main-settings-section', 'main-settings-section'
        );

        add_settings_field(
                'country-setting', 'Country:', array($this, 'create_input_country'), 'car-plugin-main-settings-section', 'main-settings-section'
        );

        add_settings_field(
                'zip-setting', 'Zip / Post code:', array($this, 'create_input_zip'), 'car-plugin-main-settings-section', 'main-settings-section'
        );

        add_settings_field(
                'phone-setting', 'Phone :', array($this, 'create_input_phone'), 'car-plugin-main-settings-section', 'main-settings-section'
        );

        add_settings_field(
                'fax-setting', 'Fax:', array($this, 'create_input_fax'), 'car-plugin-main-settings-section', 'main-settings-section'
        );

        add_settings_field(
                'email-setting', 'Contact Email:', array($this, 'create_input_email'), 'car-plugin-main-settings-section', 'main-settings-section'
        ); 
         */




        register_setting('main-settings-group', 'car_plugin_options_arraykey', array($this, 'plugin_main_settings_validate'));

// add_settings_section( $id, $title, $callback, $page )
        add_settings_section(
                'additional-settings-section', 'Payment Options', array($this, 'print_additional_settings_section_info'), 'test-plugin-additional-settings-section'
        );

// add_settings_field( $id, $title, $callback, $page, $section, $args )
       /* add_settings_field(
                'another-setting', 'Measurement Unit: ', array($this, 'create_input_another_setting'), 'test-plugin-additional-settings-section', 'additional-settings-section'
        );
        */

        add_settings_field(
                'currency-setting', 'Currency Setting', array($this, 'create_currency_another_setting'), 'test-plugin-additional-settings-section', 'additional-settings-section'
        );
        /*
        add_settings_field(
                'payablenow-setting', 'Payable Setting', array($this, 'create_payable_setting'), 'test-plugin-additional-settings-section', 'additional-settings-section'
        );*/
        add_settings_field(
                'apiusername-setting', 'API Username', array($this, 'create_apiusername_setting'), 'test-plugin-additional-settings-section', 'additional-settings-section'
        );
        add_settings_field(
                'apipassword-setting', 'API Password', array($this, 'create_apipassword_setting'), 'test-plugin-additional-settings-section', 'additional-settings-section'
        );
        add_settings_field(
                'apisignature-setting', 'API Signature', array($this, 'create_apisignature_setting'), 'test-plugin-additional-settings-section', 'additional-settings-section'
        );
        add_settings_field(
                'paypalemail-setting', 'PayPal Email', array($this, 'create_paypalemail_setting'), 'test-plugin-additional-settings-section', 'additional-settings-section'
        );
        add_settings_field(
                'paypalsandbox-setting', 'PayPal Sandbox', array($this, 'create_paypalsandbox_setting'), 'test-plugin-additional-settings-section', 'additional-settings-section'
        );

// register_setting( $option_group, $option_name, $sanitize_callback )
        register_setting('additional-settings-group', 'second_set_arraykey', array($this, 'plugin_additional_settings_validate'));
    }

    function create_name_setting() {

        $options = get_option('car_plugin_options_arraykey');
        ?><input type="text" name="car_plugin_options_arraykey[companyname-setting]" value="<?php echo isset($options['companyname-setting']) ? $options['companyname-setting'] : '' ?>" />
        <?php
    }

    function create_input_street_setting() {

        $options = get_option('car_plugin_options_arraykey');
        ?><input type="text" name="car_plugin_options_arraykey[streetaddress-setting]" value="<?php echo isset($options['streetaddress-setting']) ? $options['streetaddress-setting'] : '' ?>" />
        <?php
    }

    function create_input_city_setting() {

        $options = get_option('car_plugin_options_arraykey');
        ?><input type="text" name="car_plugin_options_arraykey[city-setting]" value="<?php echo isset($options['city-setting']) ? $options['streetaddress-setting'] : '' ?>" />
        <?php
    }

    function create_input_country() {

        $options = get_option('car_plugin_options_arraykey');
        ?><input type="text" name="car_plugin_options_arraykey[country-setting]" value="<?php echo isset($options['country-setting']) ? $options['country-setting'] : '' ?>" />
        <?php
    }

    function create_input_state_setting() {

        $options = get_option('car_plugin_options_arraykey');
        ?><input type="text" name="car_plugin_options_arraykey[state-setting]" value="<?php echo isset($options['state-setting']) ? $options['state-setting'] : '' ?>" />
        <?php
    }

    function create_input_zip() {

        $options = get_option('car_plugin_options_arraykey');
        ?><input type="text" name="car_plugin_options_arraykey[zip-setting]" value="<?php echo isset($options['zip-setting']) ? $options['zip-setting'] : '' ?>" />
        <?php
    }

    function create_input_phone() {

        $options = get_option('car_plugin_options_arraykey');
        ?><input type="text" name="car_plugin_options_arraykey[phone-setting]" value="<?php echo isset($options['phone-setting']) ? $options['phone-setting'] : '' ?>" />
        <?php
    }

    function create_input_fax() {

        $options = get_option('car_plugin_options_arraykey');
        ?><input type="text" name="car_plugin_options_arraykey[fax-setting]" value="<?php echo isset($options['fax-setting']) ? $options['fax-setting'] : '' ?>" />
        <?php
    }

    function create_input_email() {

        $options = get_option('car_plugin_options_arraykey');
        ?><input type="text" name="car_plugin_options_arraykey[email-setting]" value="<?php echo isset($options['email-setting']) ? $options['email-setting'] : '' ?>" />
        <?php
    }

    function print_demand_deposit_section_info() {
        echo '<p>' . _e('Rent car setting.', $this->car_share) . '</p>';
    }

    function print_main_settings_section_info() {
        echo '<p>' . _e('General Setting.', $this->car_share) . '</p>';
    }

    // deposit section inputs
    public function create_input_deposit_amount() {
        $sc_setting = get_option('sc_setting');
        ?>
        <input type="number" step="0.01" max="100" name="sc_setting[deposit_amount]" value="<?php echo isset($sc_setting['deposit_amount']) ? $sc_setting['deposit_amount'] : '' ?>">
        <?php
    }

    public function create_input_deposit_active() {
        $sc_setting = get_option('sc_setting');
        ?>
        <input type="checkbox" name="sc_setting[deposit_active]" value="1" <?php echo isset($sc_setting['deposit_active']) && 1 == $sc_setting['deposit_active'] ? 'checked="checked" ' : '' ?> />
        <?php
    }

    function create_input_block_car_interval(){
        $sc_setting = get_option('sc_setting'); 
        $block_type = isset($sc_setting['block_type']) ? $sc_setting['block_type'] : ''; 
        $options = array(
            'hours' => __("hours", $this->car_share),
            'next_day' => __("next day", $this->car_share),
        ); 
        ?> 
        <script>
        jQuery(document).ready(function ($) {
            $( "#block_type" ).change(function() {
                if('next_day' == $(this).val()){
                    $(this).parents('.block_option').find(':input[type="number"]').hide();
                } else {
                    $(this).parents('.block_option').find(':input[type="number"]').show();
                }
            });
        });
        </script>         
        <div class="block_option">
            <select  id="block_type" name="block_type">
                <?php foreach($options as $key => $label): ?>
                    <option value="<?php echo $key ?>" <?php echo $key == $block_type ? ' selected="selected" ' : '' ?>><?php echo $label; ?></option>
                <?php endforeach; ?>
            </select>

            <input <?php echo $block_type == 'next_day' ? ' style="display:none;"' : '' ?> type="number" step="0.05" name="sc_setting[block_interval]" value="<?php echo isset($sc_setting['block_interval']) ? floatval($sc_setting['block_interval']) : '0' ?>" />
        </div>
        <?php
    }

    /*
    function create_block_till_next_day(){
        $sc_setting = get_option('sc_setting');
        ?>
        <input type="checkbox" name="sc_setting[block_till_next_day]" value="1" <?php echo isset($sc_setting['block_till_next_day']) && 1 == $sc_setting['block_till_next_day'] ? 'checked="checked" ' : '' ?> />
        <?php
    }   */

    function create_input_block_car_interval_diff_location(){
        $sc_setting = get_option('sc_setting');
        $block_type = isset($sc_setting['block_type_diff_loc']) ? $sc_setting['block_type_diff_loc'] : '';

        $options = array(
            'hours' => 'hours',
            'next_day' => 'next day',
        );

        ?>
        <script>
        jQuery(document).ready(function ($) {
            $( "#block_type_diff_loc" ).change(function() {
                if('next_day' == $(this).val()){
                    $(this).parents('.block_option').find(':input[type="number"]').hide();
                } else {
                    $(this).parents('.block_option').find(':input[type="number"]').show();
                }
            });
        });
        </script>

        <div class="block_option">
            <select id="block_type_diff_loc" name="block_type_diff_loc">
                <?php foreach($options as $key => $label): ?>
                    <option value="<?php echo $key ?>" <?php echo $key == $block_type ? ' selected="selected" ' : '' ?>><?php _e($label, $this->car_share) ?></option>
                <?php endforeach; ?>
            </select>

            <input <?php echo $block_type == 'next_day' ? ' style="display:none;"' : '' ?> type="number" step="0.05" name="sc_setting[block_interval_diff_loc]" value="<?php echo isset($sc_setting['block_interval_diff_loc']) ? floatval($sc_setting['block_interval_diff_loc']) : '0' ?>" />
        </div>
        <?php
    }

    /*
    function create_block_to_next_day_diff_loc(){
        $sc_setting = get_option('sc_setting');
        ?>
        <input type="checkbox" name="sc_setting[block_to_next_day_diff_loc]" value="1" <?php echo isset($sc_setting['block_to_next_day_diff_loc']) && 1 == $sc_setting['block_to_next_day_diff_loc'] ? 'checked="checked" ' : '' ?> />
        <?php
    }    */
    
    function create_input_name_of_company(){
        $options = get_option('car_plugin_options_arraykey');
        ?><input type="text" name="car_plugin_options_arraykey[name_of_company]" value="<?php echo isset($options['name_of_company']) ? $options['name_of_company'] : '' ?>" />
        <?php        
    }
    
    function create_input_email_logo_url(){
        $options = get_option('car_plugin_options_arraykey');
        ?>
        <input style="width: 600px;" type="text" name="car_plugin_options_arraykey[logo_url]" value="<?php echo isset($options['logo_url']) ? esc_url($options['logo_url']) : '' ?>">
        <?php
    }
            
    function create_input_email_footer_text(){
        $options = get_option('car_plugin_options_arraykey');
        ?>        
        <textarea name="car_plugin_options_arraykey[footer_text]" rows="5" cols="60"><?php echo isset($options['footer_text']) ? esc_attr($options['footer_text']) : '' ?></textarea>
        <?php
    }

    function create_input_some_setting() {
        $options = get_option('car_plugin_options_arraykey');
        ?><input type="text" name="car_plugin_options_arraykey[notemail]" value="<?php echo isset($options['notemail']) ? $options['notemail'] : '' ?>" />
        <?php
    } 
    function create_input_some_show_cat() { 
        $options = get_option('car_plugin_options_arraykey');
        ?>
        <input type="checkbox" name="car_plugin_options_arraykey[showcategory]" value="1" <?php
        if (isset($options['showcategory']) && ($options['showcategory'] == 1)) {
            echo 'checked';
        }
        ?> />
        <?php
    } 
    function create_input_some_show_catalog() {  
        $options = get_option('car_plugin_options_arraykey'); 
        ?>
        <input type="checkbox" name="car_plugin_options_arraykey[catalogoption]" value="1" <?php
        if (isset($options['catalogoption']) && ($options['catalogoption'] == 1)) {
            echo 'checked';
        }
        ?> />
        <?php
    } 
    /**
     * @param type $arr_input
     */
    function sc_settings_validate($arr_input) {
        $arr_input['deposit_amount'] = floatval($arr_input['deposit_amount']);
        $arr_input['deposit_active'] = intval($arr_input['deposit_active']);
        $arr_input['block_interval'] = floatval($arr_input['block_interval']);
        $arr_input['block_interval_diff_loc'] = floatval($arr_input['block_interval_diff_loc']); 
        $arr_input['block_type'] = isset($_POST['block_type']) ? esc_attr($_POST['block_type']) : '';
        $arr_input['block_type_diff_loc'] = isset($_POST['block_type_diff_loc']) ? esc_attr($_POST['block_type_diff_loc']) : ''; 
        return $arr_input;
    }

    function plugin_main_settings_validate($arr_input) {
        //$options = get_option('car_plugin_options_arraykey');
        
        array_walk($arr_input, 'trim');
        return $arr_input;
        
        
        
        /*
        $options['notemail'] = trim($arr_input['notemail']);
        $options['showcategory'] = trim($arr_input['showcategory']);
        $options['catalogoption'] = trim($arr_input['catalogoption']);   
        $options['companyname-setting'] = trim($arr_input['companyname-setting']);
        $options['streetaddress-setting'] = trim($arr_input['streetaddress-setting']);
        $options['city-setting'] = trim($arr_input['city-setting']);
        $options['state-setting'] = trim($arr_input['state-setting']);
        $options['country-setting'] = trim($arr_input['country-setting']);
        $options['zip-setting'] = trim($arr_input['zip-setting']);
        $options['phone-setting'] = trim($arr_input['phone-setting']);
        $options['fax-setting'] = trim($arr_input['fax-setting']);
        $options['email-setting'] = trim($arr_input['email-setting']);
        */

        //return $options;
    }

    function print_additional_settings_section_info() {
        echo '<p>Paypal Settings</p>';
    }

    function create_input_another_setting() {
        $options = get_option('second_set_arraykey');
        ?><input type="text" name="second_set_arraykey[sc-unit]" value="<?php echo isset($options['sc-unit']) ? $options['sc-unit'] : '' ?>" /><?php
           }

           function create_payable_setting() {
               $options = get_option('second_set_arraykey');
               ?>
        <input type="text" name="second_set_arraykey[payablenow-setting]" value="<?php if (!empty($options['payablenow-setting'])) {
            echo $options['payablenow-setting'];
        } ?>" />
        <?php
    }

    function create_currency_another_setting() {
        $options = get_option('second_set_arraykey');
        include_once( 'partials/currencies.php' );
        echo "<select name='second_set_arraykey[sc-currency]'>";
        foreach ($currencies as $currency_code => $currency_details) {
            $selected = "";
            if ($options['sc-currency'] == $currency_code) {
                $selected = " selected ";
            }
            echo "<option value='" . esc_attr($currency_code) . "'" . esc_attr($selected) . ">" . esc_html($currency_details['name']) . "</option>";
        }
        echo "</select>";
    }

    function create_apiusername_setting() {
        $options = get_option('second_set_arraykey');
        ?><input type="text" name="second_set_arraykey[apiusername-setting]" value="<?php echo isset($options['apiusername-setting']) ? $options['apiusername-setting'] : '' ?>" /><?php
    }

    function create_apipassword_setting() {


        $options = get_option('second_set_arraykey');
        ?><input type="text" name="second_set_arraykey[apipassword-setting]" value="<?php echo isset($options['apipassword-setting']) ? $options['apipassword-setting'] : '' ?>" /><?php
    }

    function create_apisignature_setting() {

        $options = get_option('second_set_arraykey');
        ?><input type="text" name="second_set_arraykey[apisignature-setting]" value="<?php echo isset($options['apisignature-setting']) ? $options['apisignature-setting'] : '' ?>" /><?php
    }

    function create_paypalemail_setting() {

        $options = get_option('second_set_arraykey');
        ?><input type="text" name="second_set_arraykey[paypalemail_setting]" value="<?php echo isset($options['paypalemail_setting']) ? $options['paypalemail_setting'] : '' ?>" /><?php
    }

    function create_paypalsandbox_setting() {

        $options = get_option('second_set_arraykey');
        ?>
        <input type="checkbox" name="second_set_arraykey[paypalsandbox-setting]" value="1" <?php
        if (isset($options['paypalsandbox-setting']) && ($options['paypalsandbox-setting'] == 1)) {
            echo 'checked';
        }
        ?> />
        <?php
    }

    function plugin_additional_settings_validate($arr_input) {
 
        array_walk($arr_input, 'trim');
        return $arr_input;
        
        /*
        $options = get_option('second_set_arraykey');
        $options['sc-unit'] = trim($arr_input['sc-unit']);
        $options['sc-currency'] = trim($arr_input['sc-currency']);
        $options['apiusername-setting'] = trim($arr_input['apiusername-setting']);
        $options['apisignature-setting'] = trim($arr_input['apisignature-setting']);
        $options['apipassword-setting'] = trim($arr_input['apipassword-setting']);
        $options['paypalemail_setting'] = trim($arr_input['paypalemail_setting']);
        $options['paypalsandbox-setting'] = trim($arr_input['paypalsandbox-setting']);
        $options['payablenow-setting'] = trim($arr_input['payablenow-setting']);
 
        return $options;
        */
    } 
    /**
     * Add settings action link to the plugins page.
     *
     * removed from the plugin added by me
     */
    public function add_action_links($links) {
        return array_merge(
                array(                    
                    'settings' => '<a href="' . admin_url('options-general.php?page=' . $this->car_share) . '">' . __('Settings', $this->car_share) . '</a>'
                ), $links
        );
    } 
    /*
     *
     * create the page for the plugin
     *
     */ 
    public function my_plugin_install_function() { 
    } 
}