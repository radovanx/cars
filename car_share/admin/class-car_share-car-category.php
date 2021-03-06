<?php

class Car_share_CarCategory {

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

        //add_action('in_admin_footer', array($this, 'new_season_to_category'));
        //add_action('in_admin_footer', array($this, 'new_season_to_category'));
        //add_action('wp_ajax_add_season_to_category', array($this, 'add_season_to_category_callback'));
        //add_action('wp_ajax_edit_season_to_category', array($this, 'edit_season_to_category_callback'));
        add_action('wp_ajax_reload_season2category', array($this, 'reload_season2category_callback'));
        add_action('wp_ajax_new_season_to_category', array($this, 'ajax_new_season_to_category'));
        add_action('wp_ajax_edit_season_to_category', array($this, 'ajax_edit_season_to_category'));
        add_action('wp_ajax_season2category_days', array($this, 'ajax_season2category_days'));
        add_action('wp_ajax_delete_season_to_category', array($this, 'ajax_delete_season_to_category'));
        add_action('wp_ajax_save_season2category', array($this, 'ajax_save_season2category'));

        add_action('wp_ajax_discount_upon_duration_row', array($this, 'discount_upon_duration_row'));
        add_action('wp_ajax_s2c_discount_upon_duration_row', array($this, 's2c_discount_upon_duration_row'));
        //add_action('wp_ajax_season2category_discount', array($this, 'season2category_discount'));

        add_filter('manage_sc-car-category_posts_columns', array($this, 'column_head'));
        add_action('manage_sc-car-category_posts_custom_column', array($this, 'column_content'), 10, 2);
    }

    public function column_head($defaults) {
        $date = $defaults['date'];
        unset($defaults['date']);
        $defaults['cat_seasons'] = __('Seasons', $this->car_share);
        $defaults['date'] = $date;
        return $defaults;
    }

    public function column_content($column_name, $post_id) {
        switch ($column_name) {
            case 'cat_seasons':
                global $wpdb;
                $sql = "SELECT season_id FROM day_prices WHERE car_category_id = '" . esc_sql($post_id) . "' AND season_id != 0 GROUP BY season_id ";
                $seasons_ids = $wpdb->get_col($sql);

                $seasons = array();
                foreach ($seasons_ids as $season_id) {
                    // $t = get_the_title($season_id);
                    $seasons[] = get_the_title($season_id);
                }

                echo implode(', ', $seasons);
                break;
        }
    }

    /*
      public function season2category_discount(){


      discount_upon_duration_row

      //$category_id = $_POST['category_id'];

      //$params = array();
      //parse_str($_POST['form'], $params);

      die();
      } */

    public function s2c_discount_upon_duration_row() {
        $row_key = $_POST['row_key'];
        $input_name = '_s2c_discount_upon_duration';
        include 'partials/car-category/discount_upon_duration_row.php';
        die();
    }

    public function discount_upon_duration_row() {
        $row_key = $_POST['row_key'];
        $input_name = '_discount_upon_duration';
        include 'partials/car-category/discount_upon_duration_row.php';
        die();
    }

    public function ajax_save_season2category() {

        global $wpdb;
        $car_category_id = (int) $_POST['id']; //

        $params = array();
        parse_str($_POST['form'], $params);

        // category day prices
        if (!empty($params['_season_to_category_prices'])) {
            foreach ($params['_season_to_category_prices'] as $dayname => $price) {
                $sql = "
                    REPLACE INTO day_prices (car_category_id, season_id, dayname, price) VALUES (
                        '" . $car_category_id . "',
                        '" . (int) $params['_season_to_category'] . "',
                        '" . esc_sql($dayname) . "',
                        '" . floatval($price) . "'
                    )
                    ";
                $wpdb->query($sql);
            }
        }



        //
        if (!empty($params['_s2c_discount_upon_duration'])) {

            // odstranim pouze sezonu, kterou preukladam
            $s2c_discount_upon_duration = get_post_meta($car_category_id, '_s2c_discount_upon_duration', true);

            $arr_to_save = array();

            if (isset($s2c_discount_upon_duration[$params['_season_to_category']])) {
                unset($s2c_discount_upon_duration[$params['_season_to_category']]);
            }

            // podrzim slevy na ostatni prirazene sezony
            if (is_array($s2c_discount_upon_duration)) {
                $arr_to_save = $s2c_discount_upon_duration;
            }

            $arr_to_save[(int) $params['_season_to_category']] = array();

            foreach ($params['_s2c_discount_upon_duration'] as $key => $discount) {
                $day_number = $discount['days'];
                unset($discount['days']);
                $arr_to_save[(int) $params['_season_to_category']][$day_number] = $discount;
            }
            update_post_meta($car_category_id, '_s2c_discount_upon_duration', $arr_to_save);
        } else {
            //delete_post_meta($car_category_id, '_s2c_discount_upon_duration');
        }

        $category = new sc_Category($car_category_id);
        $season2category_prices = $category->season_to_category_prices();

        $s2c_discount_upon_duration = get_post_meta($car_category_id, '_s2c_discount_upon_duration', true);

        include 'partials/car-category/content_assigned_season.php';
        exit();
    }

    public function add_custom_boxes() {

        add_meta_box(
                'car_category_different_location_return_price', __('Price for return to different location', $this->car_share), array($this, 'different_location_return_price_box'), 'sc-car-category'
        );

        add_meta_box(
                'car_category_young_driver_surcharge', __('Young driver surcharge ', $this->car_share), array($this, 'young_driver_surcharge_box'), 'sc-car-category'
        );

        add_meta_box(
                'car_category_day_prices', __('Price', $this->car_share), array($this, 'day_prices_box'), 'sc-car-category'
        );

        add_meta_box(
                'car_category_discount_upon_duration', __('Discount upon duration', $this->car_share), array($this, 'discount_upon_duration_box'), 'sc-car-category'
        );

        add_meta_box(
                'car_category_assign_season', __('Assigned seasons', $this->car_share), array($this, 'assigned_season_box'), 'sc-car-category'
        );
    }

    function ajax_delete_season_to_category() {

        $category_id = $_POST['id'];
        $season_id = $_POST['season_id'];

        global $wpdb;

        $sql = "
            DELETE FROM
                day_prices
            WHERE
                car_category_id = '" . (int) $category_id . "'
            AND
                season_id = '" . (int) $season_id . "'
        ";

        return $wpdb->query($sql);
    }

    /**
     *
     * @global type $wpdb
     */
    function ajax_season2category_days() {

        $date_error = array();

        $post_id = $_POST['id']; // category id
        $season_id = $_POST['season_id'];
        global $wpdb;

        $season = new sc_Season($season_id);



        $sql = "
                SELECT
                    season_id
                FROM
                    day_prices
                WHERE
                    car_category_id = '" . (int) $post_id . "'
                AND
                    season_id = '" . (int) $season_id . "'
            ";

        $exists = $wpdb->get_var($sql);

        if (!empty($exists)) {
            header("HTTP/1.0 404 Not Found");
            $date_error[] = array(
                'message' => __('Error, this season is already assigned..')
            );
            echo json_encode($date_error);
            exit;
        }

        $assigned_season_intervals = sc_Season::get_dates($season_id);

        if (empty($assigned_season_intervals)) {
            header("HTTP/1.0 404 Not Found");
            //_e('Please, first define start and end date on this season.', $this->car_share);
            $date_error[] = array(
                //'message' => __('Error, please fill some date intervals in season..')
                'message' => sprintf(__('Error: date intervals of picked season are in conflict with date intervals in season %s:'),  get_the_title($season_id))
            );
            echo json_encode($date_error);
            exit;
        }

        /*
        foreach ($assigned_season_intervals as $interval) {

            $new_season_from = DateTime::createFromFormat('Y-m-d H:i:s', $interval->date_from);
            $new_season_to = DateTime::createFromFormat('Y-m-d H:i:s', $interval->date_to);

            // find all assigned season, which are in conflict with new assigned season
            $sql = "SELECT
                        s.post_title,
                        s.ID,
                        sdate.date_from as date_from,
                        sdate.date_to as date_to
                    FROM
                        $wpdb->posts s
                    JOIN
                        sc_season_date as sdate
                    WHERE
                        s.post_status NOT IN ('trash') AND s.post_type='sc-season'
                    AND
                        s.ID != '" . (int) $season_id . "'
                    AND
                        sdate.date_to > NOW()
                    AND
                    (
                        (sdate.date_from BETWEEN '" . $new_season_from->format('Y-m-d  H:i:s') . "' AND '" . $new_season_to->format('Y-m-d  H:i:s') . "')
                            OR
                        ('" . $new_season_from->format('Y-m-d  H:i:s') . "' BETWEEN sdate.date_from AND sdate.date_to)
                    )
                    GROUP BY s.ID
                ";

            $conflict_seasons = $wpdb->get_results($sql);

            if (!empty($conflict_seasons)) {
                foreach ($conflict_seasons as $cs) {
                    
                    $from = DateTime::createFromFormat('Y-m-d H:i:s', $cs->date_from);
                    $to = DateTime::createFromFormat('Y-m-d H:i:s', $cs->date_to); 
                    
                    $interval = $from->format(get_option('date_format')) . ' - ' . $to->format(get_option('date_format')); 
                    
                    $date_error[] = array(
                        'season_id' => $cs->ID,
                        'message' => sprintf(__('Error: date intervals of picked season are in conflict with date intervals in season %s: (%s)'), __($cs->post_title), $interval) . '<br>'
                    );
                }
            }
        }*/

        if (!empty($date_error)) {
            header("HTTP/1.0 404 Not Found");
            //_e('You cannot assign two season with overlaping dates.', $this->car_share);

            echo json_encode($date_error);
            exit;
        }

        include 'partials/car-category/s2c_days_price_inputs.php';
        die();
    }

    public function ajax_edit_season_to_category() {
        //global $post;
        $post_id = $_POST['id']; // category id
        $season_id = $_POST['season_id'];

        $car_category = new sc_Category($post_id);
        $category_day_prices = $car_category->day_prices_indexed_with_dayname($season_id);

        $s2c_discount_upon_duration = get_post_meta($post_id, '_s2c_discount_upon_duration', true);

        include 'partials/car-category/s2c_days_price_inputs.php';
        die();
    }

    public function ajax_new_season_to_category() {
        //global $post;
        global $wpdb;
        $post_id = $_POST['id'];


        $sql = "SELECT * FROM $wpdb->posts WHERE post_type = 'sc-season' AND post_status IN ('publish')";
        $seasons = $wpdb->get_results($sql);

        //$s2c_discount_upon_duration = get_post_meta($car_category_id, '_s2c_discount_upon_duration', true);

        include 'partials/car-category/new_season_to_category.php';
        die();
    }

    public function reload_season2category_callback() {
        global $wpdb;

        $car_category_id = $_GET['id'];

        $category = new sc_Category($car_category_id);
        $season2category = $category->day_prices_indexed_with_dayname();

        $s2c_discount_upon_duration = get_post_meta($car_category_id, '_s2c_discount_upon_duration', true);

        include 'partials/car-category/content_assigned_season.php';
        die();
    }

    public function different_location_return_price_box($post) {
        //global $post;
        $location_price = get_post_meta($post->ID, '_location_price', true);
        //$apply_location_price = get_post_meta($post_id, '_apply_location_price', true);
        include 'partials/car-category/different_location_price.php';
    }

    public function young_driver_surcharge_box() {
        global $post;
        $surcharge_age = get_post_meta($post->ID, '_surcharge_age', true);
        $surcharge_fee = get_post_meta($post->ID, '_surcharge_fee', true);
        $surcharge_active = get_post_meta($post->ID, '_surcharge_active', true);
        include 'partials/car-category/minimum_driver_age.php';
    }

    public function day_prices_box() {
        global $post;
        $category = new sc_Category($post);
        $category_day_prices = $category->day_prices_indexed_with_dayname();
        include 'partials/car-category/day_prices.php';
    }

    public function assigned_season_box() {
        global $post;
        $category = new sc_Category($post);
        $season2category_prices = $category->season_to_category_prices();

        //
        $s2c_discount_upon_duration = get_post_meta($post->ID, '_s2c_discount_upon_duration', true);

        include 'partials/car-category/season2category.php';
    }

    public function discount_upon_duration_box() {
        global $post;
        $discount_upon_duration = get_post_meta($post->ID, '_discount_upon_duration', true);

        if (is_array($discount_upon_duration)) {
            ksort($discount_upon_duration);
        }

        include 'partials/car-category/discount_upon_duration.php';
        wp_nonce_field(__FILE__, 'car_category_nonce');
    }

    public function save() {
        //$date = DateTime::createFromFormat('m.d.Y', $_POST['Select-date']);
        if (isset($_POST['car_category_nonce']) && wp_verify_nonce($_POST['car_category_nonce'], __FILE__)) {

            global $wpdb;
            global $post;

            // category day prices
            if (!empty($_POST['_category_day_prices'])) {
                foreach ($_POST['_category_day_prices'] as $dayname => $price) {
                    $sql = "
                        REPLACE INTO day_prices (car_category_id, season_id, dayname, price) VALUES (
                            '" . (int) $post->ID . "',
                            0,
                            '" . esc_sql($dayname) . "',
                            '" . floatval($price) . "'
                        )
                    ";
                    $wpdb->query($sql);
                }
            }

            delete_post_meta($post->ID, '_discount_upon_duration');

            if (!empty($_POST['_discount_upon_duration'])) {
                $arr_to_save = array();

                foreach ($_POST['_discount_upon_duration'] as $discount) {
                    $days = (int) $discount['days'];
                    unset($discount['days']);
                    //$percentage = floatval($discount['percentage']);
                    $arr_to_save[$days] = $discount;
                }

                update_post_meta($post->ID, '_discount_upon_duration', $arr_to_save);
            }

            //
            $keys = array(
                '_surcharge_age',
                '_surcharge_fee',
                '_location_price'
            );

            foreach ($keys as $key) {
                if (isset($_POST[$key]) && "" != trim($_POST[$key])) {
                    update_post_meta((int) $post->ID, $key, esc_attr($_POST[$key]));
                } else {
                    delete_post_meta((int) $post->ID, $key);
                }
            }

            $checkboxes = array(
                //'_apply_location_price',
                '_surcharge_active'
            );

            foreach ($checkboxes as $index) {
                if (isset($_POST[$index]) && 1 == $_POST[$index]) {
                    update_post_meta((int) $post->ID, $index, 1);
                } else {
                    delete_post_meta((int) $post->ID, $index);
                }
            }
        }
    }

}
