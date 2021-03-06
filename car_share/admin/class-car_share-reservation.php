<?php

class Car_share_Reservation {

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
        
        add_action('admin_menu', array($this, 'admin_menu'));
        
        add_action('wp_ajax_load_months', array($this, 'load_months'));
    }
    
    /**
     *
     */
    public function admin_menu(){
        /*
        add_submenu_page(
                'edit.php?post_type=sc-car',
                __('Reservations', $this->car_share),
                __('Reservations', $this->car_share),
                'manage_options',
                'car_reservation',
                array($this, 'car_reservation')
        );*/
        
        add_menu_page( 
                __('Planning', $this->car_share), 
                __('Planning', $this->car_share), 
                'manage_options', 
                'car_reservation', 
                array($this, 'car_reservation'), 
                "dashicons-clipboard", 
                89 
        );
    }    
    
    
    public function overview($year, $month){
        include 'partials/reservation/overview.php';
    }
    
    public function month($year, $month, $form_data, $show_cars = false){
        include 'partials/reservation/month.php';
    }    
    
    
    public function car_reservation(){
        include 'partials/reservation/index.php';
    }
    
    public function load_months(){

        parse_str($_POST['form'], $form_data);
        
        if(1 == $_POST['current_month']){
            $month_number = $form_data['sc-current-month'];
            $year_number = $form_data['sc-current-year'];            
        } else {
            $month_number = $_POST['month'];
            $year_number = $_POST['year'];            
        }
        
        $month = new DateTime();
        $month->setDate($year_number, $month_number, 1);
        
        $prev_month = clone $month;
        $prev_month->modify('last day of last month');
        
        $next_month = clone $month;
        $next_month->modify( 'first day of next month' );  
        
        
        
        ?>
        <div id="sc-navigation" class="sc-navigation">            
            <a href="#" data-month="<?php echo $prev_month->format('n') ?>" data-year="<?php echo $prev_month->format('Y') ?>" class="sc-prev" title="<?php _e('Prev', 'car_share') ?>">&laquo;</a>
            <a href="#" data-month="<?php echo $next_month->format('n') ?>" data-year="<?php echo $next_month->format('Y') ?>" class="sc-next" title="<?php _e('Next', 'car_share') ?>">&raquo;</a>
        </div>
        <?php
                
        $this->month($month->format('Y'), $month->format('n'), $form_data, true);
        $this->month($next_month->format('Y'), $next_month->format('n'), $form_data);
        
        die();
    }
}
