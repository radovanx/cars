<?php

class sc_Season {

    protected $id;
    protected $post;

    protected static $dates = array();
    protected static $date_to_column = array();

    public function __construct($post = null) {
        if($post instanceof WP_Post){
            $this->id = $post->ID;
            $this->post = $post;
        } else {
            $this->id = $post;
            $this->post = get_post($post);
        }
    }

    public static function get_dates($season_id){

        global $wpdb;

        $sql = "
            SELECT
                *
            FROM
                sc_season_date
            WHERE
                post_id = '" . $season_id . "'
        ";

        $dates = $wpdb->get_results($sql);
        return $dates;
    }

    public static function date_to_column($post_id){

        if(empty($date_to_column[$post_id])){

            global $wpdb;

            $sql = "
                SELECT
                    *
                FROM
                    sc_season_date
                WHERE
                    post_id = '" . (int) $post_id . "'
                AND
                    date_to > NOW()
                ORDER BY
                    date_from ASC
            ";

            $result = $wpdb->get_results($sql);
            $date_to_column[$post_id] = $result;
        }

        return $date_to_column[$post_id];

    }

}
