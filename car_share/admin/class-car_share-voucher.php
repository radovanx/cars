<?php

class Car_share_Voucher {

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

        add_action('add_meta_boxes', array($this, 'add_custom_boxes'));
        add_action('save_post', array($this, 'save'));
        
        add_filter('manage_sc-voucher_posts_columns', array($this, 'column_head'));
        add_action('manage_sc-voucher_posts_custom_column', array($this, 'column_content'), 10, 2);        
    }

    public function column_head($defaults){            
        $defaults['code'] = __('Code', $this->car_share);
        $defaults['discount'] = __('Discount', $this->car_share);
        return $defaults;
    }
    
    public function column_content($column_name, $post_id){        
        switch($column_name){
            case 'code':
                echo esc_attr(get_post_meta($post_id, '_voucher_code', true));
                break;            
            case 'discount':
                echo esc_attr(get_post_meta($post_id, '_discount', true)) . ' ' . __('%', $this->car_share);
                break;                        
        }
    }
    
    public function add_custom_boxes() { 
        add_meta_box(
                'voucher_code_box', __('Code', $this->car_share), array($this, 'voucher_code_box'), 'sc-voucher'
        );
        
        add_meta_box(
                'voucher_discount_box', __('Discount (percentage)', $this->car_share), array($this, 'voucher_discount_box'), 'sc-voucher'
        );        
    }
    
    public function voucher_code_box(){
        global $post;        
        $voucher_code = get_post_meta($post->ID, '_voucher_code', true);        
        include 'partials/voucher/code.php';
        wp_nonce_field(__FILE__, 'voucher_nonce');
    }
    
    public function voucher_discount_box(){
        global $post;        
        $discount = get_post_meta($post->ID, '_discount', true);        
        include 'partials/voucher/discount.php';        
    }

    public function save() { 
        //$date = DateTime::createFromFormat('m.d.Y', $_POST['Select-date']);
        if (isset($_POST['voucher_nonce']) && wp_verify_nonce($_POST['voucher_nonce'], __FILE__)) {
            
            global $post;
            
            $keys = array();
            
            if(empty($_POST['_voucher_code'])){
                delete_post_meta($post->ID, '_voucher_code');
            } else {
                update_post_meta($post->ID, '_voucher_code', sanitize_text_field($_POST['_voucher_code']));
            }
            
            if(empty($_POST['_discount'])){
                delete_post_meta($post->ID, '_discount');
            } else {
                update_post_meta($post->ID, '_discount', floatval($_POST['_discount']));
            }            

        }
    } 
}