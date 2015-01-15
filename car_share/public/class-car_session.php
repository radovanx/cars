<?php

class Car_Cart {

    private $cart_name;       // The name of the cart/session variable
    private $items = array(); // The array for storing items in the cart
    private $total_price;

    /**
     * __construct() - Constructor. This assigns the name of the cart
     *                 to an instance variable and loads the cart from
     *                 session.
     *
     * @param string $name The name of the cart.
     */

    public function __construct($name) {
        $hash = sha1('whatsthecallme063214056*');
        $this->cart_name = $name . $hash;
        $this->items = empty($_SESSION[$this->cart_name]) ? array() : $_SESSION[$this->cart_name];
    }

    /**
     * setItemQuantity() - Set the quantity of an item.
     *
     * @param string $order_code The order code of the item.
     * @param int $quantity The quantity.
     */
    public function setItemSearch($pick_up_location, $drop_off_location, $car_datefrom, $car_dateto, $car_category) {
        $this->items['pick_up_location'] = $pick_up_location;
        $this->items['drop_off_location'] = $drop_off_location;
        $this->items['car_datefrom'] = $car_datefrom;
        $this->items['car_dateto'] = $car_dateto;
        $this->items['car_category'] = $car_category;
    }
     
    public function setYoungDriverSurcharte(){
        $this->items['young_driver_surcharge'] = $pick_up_location;
    }
    
    
    public function setItemCategory($carID) {         
          //car category from car post id     
        $car_category = get_post_meta( $carID, '_car_category' );    
        $this->items['car_category'] = $car_category;            
    } 
    
    public function getItemSearch() {
        return $this->items;
    }

    /**
     * getItemPrice() - Get the price of an item.
     *
     * @param string $order_code The order code of the item.
     * @return int The price.
     */
   
    public function get_car_price($single_car_id, DateTime $from, DateTime $to) {     
     
        global $wpdb; 
        //$car_id = $wpdb->get_var("SELECT parent FROM sc_single_car WHERE single_car_id = '" . (int) $single_car_id . "'"); 
        $car_id = sc_car::get_parent_by_single_id($single_car_id);
        $day_interval = DateInterval::createFromDateString('1 day'); 
        $period = new DatePeriod($from, $day_interval, $to);
        $diff = $to->diff($from);
        $days = $diff->days;

        $category_id = (int)get_post_meta($car_id, '_car_category', true); 
        /**
         *
         */
        if (empty($category_id)) {
            // the car dont have a category or the price
        } 
        
        $car_category = new sc_Category($category_id); 
        $category_prices = $car_category->day_prices_indexed_with_dayname();

        // find all assigned season
        $sql = "SELECT
                    ID,
                    start.meta_value as date_from,
                    end.meta_value as date_to
                FROM
                    $wpdb->posts s
                JOIN
                    postmeta_date start ON s.ID = start.post_id AND start.meta_key = '_from'
                JOIN
                    postmeta_date end ON s.ID = end.post_id AND end.meta_key = '_to'
                JOIN
                    day_prices dp ON dp.season_id = s.ID AND car_category_id = '" . (int) $category_id . "'
                WHERE
                    s.post_status = 'publish' AND s.post_type='sc-season'
                AND
                (
                    (start.meta_value BETWEEN '" . $from->format('Y-m-d  H:i:s') . "' AND '" . $to->format('Y-m-d  H:i:s') . "')
                        OR
                    ('" . $from->format('Y-m-d  H:i:s') . "' BETWEEN start.meta_value AND end.meta_value)
                )
                GROUP BY s.ID
                ";

        $seasons = $wpdb->get_results($sql);

        $applied_sessions = array();
        foreach ((array) $seasons as $session) {
            $begin = DateTime::createFromFormat('Y-m-d H:i:s', $session->date_from);
            $end = DateTime::createFromFormat('Y-m-d H:i:s', $session->date_to);        

            $season_prices = $car_category->day_prices_indexed_with_dayname($session->ID);

            $applied_sessions[] = array(
                'start' => $begin,
                'end' => $end,
                'prices' => $season_prices
            );
        }

        $total_price = 0;

        foreach ($period as $day) {

            // find out if day belongs to some season
            $day_name = $day->format("l");

            $day_price = 0;
            $mam = false;

            foreach ($applied_sessions as $applied_season) {
                if (($applied_season['start'] < $day) && ($day < $applied_season['end'])) {
                    $mam = true;
                    $day_price = isset($applied_season['prices'][$day_name]) ? $applied_season['prices'][$day_name] : 0;
                }
            }

            if ($mam == false) {
                if (isset($category_prices[$day_name])) {
                    $day_price = isset($category_prices[$day_name]) ? $category_prices[$day_name] : 0;
                }
            }

            $total_price += floatval($day_price);
        }

        // apply time discount
        $time_discount = get_post_meta($category_id, '_discount_upon_duration', true);

        $discount = 0;
        if (!empty($time_discount)) {
            ksort($time_discount);
            foreach ($time_discount as $key => $val) {
                if ($key < $days) {
                    $discount = $val;
                } else {
                    break;
                }
            }
        }

        //
        if ($discount > 0) {
            $total_price = $total_price - $total_price * $discount / 100;
        } 
        //
        return $total_price;
    } 
    
    public function getTotalPrice(){
        
        //$total_price = 0;
        
        $car_price = $this->get_car_price($this->items['car_ID'], $this->items['car_datefrom'],$this->items['car_dateto']);
        $surcharge_price = $this->get_driver_surchage_price();
        $extra_price = $this->sc_get_extras_price($this->items['car_datefrom'], $this->items['car_dateto']);

        $this->total_price = floatval($car_price) + floatval($surcharge_price) + floatval($extra_price);
        //$total_price
        return $this->total_price;
        
    }
    
    public function getPaypablePrice(){
        //$total_price = $this->getTotalPrice();
        $payable_price = $this->total_price;
        
        $sc_setting = get_option('sc_setting');
        
        if(isset($sc_setting['deposit_active']) && 1 == $sc_setting['deposit_active']){
            
            $deposit_percentage = floatval($sc_setting['deposit_amount']);
            
            $payable_price = $payable_price * $deposit_percentage / 100;            
        }
        
        return $payable_price;        
    }
    
    public function get_driver_surchage_price(){        
        
        $surcharge_price = 0;
        
        $items = $this->getItems();
        
        if(isset($items['apply_surcharge']) && 1 == $items['apply_surcharge']){
            
            $car_id = sc_Car::get_parent_by_single_id($items['car_ID']);
            $category_id = (int)get_post_meta($car_id, '_car_category', true); 
            
            if(!empty($category_id)){
                
                $surcharge_active = get_post_meta($category_id, '_surcharge_active', true);
                if(1 == $surcharge_active){
                    $surcharge_fee = get_post_meta($category_id, '_surcharge_fee', true);
                    
                    $surcharge_price += floatval($surcharge_fee);
                }                
            }            
        }
        
        return $surcharge_price;        
    }
    


    public function sc_get_extras_price(DateTime $from, DateTime $to) {
 
        global $wpdb;
         
            $day_interval = DateInterval::createFromDateString('1 day');
            $period = new DatePeriod($from, $day_interval, $to);
            $diff = $to->diff($from);
            $days = $diff->d;
     
            $Cars_cart_items = $this->getItems();
            $extras = $Cars_cart_items['service']; 
            $extras_prices=''; 
            $extras_prices=''; 
             
    foreach ($extras as $key => $extras_value)
        {   
            $service_fee = get_post_meta($key, '_service_fee', true);
            $_per_service = (int)get_post_meta($key, '_per_service', true);  
            //1 = per day
            if($_per_service == '1' )
            {    
                $extras_prices = ($service_fee * $days * $extras_value) + $extras_prices; 
            }   
            else
            {
                $extras_prices = ($service_fee * $extras_value) + $extras_prices;    
            }   
        } 
        return $extras_prices; 
    }
    
    
    
    /**
     * 
     * @global type $wpdb
     * @param type $single_car_id single_car_id v tabuli sc_single_car
     * @return type
     */
    public function get_ItembyID($single_car_id)
    {    
        global $wpdb;
      
        $sql = "
            SELECT DISTINCT 
                *
            FROM
                sc_single_car sc_single_car
            JOIN
                $wpdb->posts posts
            ON
                posts.ID = sc_single_car.parent
            WHERE
                sc_single_car.single_car_id = '" . (int) $single_car_id . "'"; 
        
        $car_result = $wpdb->get_results($sql);       
        return $car_result;        
    } 
     
    /**
     * getItemName() - Get the name of an item.
     *
     * @param string $order_code The order code of the item.
     */
     
    public function setItemId($id_code) {
        $this->items['car_ID'] = $id_code;
    }

    public function setItemService($service) {
        $this->items['service'] = $service;
    }
    
    public function applySurcharge($value) {
        $this->items['apply_surcharge'] = $value;
    }    

    /**
     * getItems() - Get all items.
     *
     * @return array The items.
     */
    public function getItems() {
        return $this->items;
    }

    /**
     * hasItems() - Checks to see if there are items in the cart.
     *
     * @return bool True if there are items.
     */
    public function hasItems() {
        return (bool) $this->items;
    }

    /**
     * getItemQuantity() - Get the quantity of an item in the cart.
     *
     * @param string $order_code The order code.
     * @return int The quantity.
     */
    public function getItemQuantity($order_code) {
        return (int) $this->items[$order_code];
    }

    /**
     * clean() - Cleanup the cart contents. If any items have a
     *           quantity less than one, remove them.
     */
    public function clean() {

        foreach ($this->items as $order_code => $quantity) {
            if ($quantity < 1)
                unset($this->items[$order_code]);
        }
    }

    /**
     * save() - Saves the cart to a session variable.
     */
    public function save() {
        $_SESSION[$this->cart_name] = $this->items;
    }

}
