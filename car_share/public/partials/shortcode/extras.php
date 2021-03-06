<div class="car-share-w4a extras">
<?php
$sc_options = get_option('sc-pages');
$extras_car_url = isset($sc_options['checkout']) ? get_page_link($sc_options['checkout']) : ''; 

$currency = sc_Currency::get_instance();
?> 

<form action="<?php echo $extras_car_url ?>" method="post">
    <?php
    $args = array(
        'post_type' => 'sc-service',
        'post_status' => 'publish'
    ); 
    $query = new WP_Query($args);
    if ($query->have_posts()) :
        while ($query->have_posts()) : $query->the_post();
            $service_fee = get_post_meta(get_the_ID(), '_service_fee', true);
            $_per_service = get_post_meta(get_the_ID(), '_per_service', true);
            $_service_quantity_box = get_post_meta(get_the_ID(), '_service_quantity_box', true);
            $service_id = get_the_ID(); 
            ?>
            <div class="extras-row">
            <h3><?php the_title() ?></h3> 
            <?php
            if (has_post_thumbnail()) { // check if the post has a Post Thumbnail assigned to it.
                the_post_thumbnail('thumbnail');
            }
            ?>
            
            <div class="form-group"> 
            <?php
            // check if the custom field has a value
            if (!empty($service_fee)) {
                 echo $currency->format($service_fee);
            }
            ?>
             <?php
            // check if the custom field has a value
            if (!empty($_per_service)) {  
                
                   $values =  array(
                     1  => __('Per day', $this->car_share),
                     2  => __('Per rental', $this->car_share), 
                    ); 
                   echo $values[$_per_service]; 
            }
            ?>
            </div>    
                 
            <div class="form-group"> 
            <select name="service[<?php echo $service_id; ?>]"> 
                <?php  
                for ($i = 0; $i <= $_service_quantity_box; $i++) { 
                  echo '<option value="'.$i.'">'.$i.'</option>'; 
                }          
                ?> 
            </select> 
            </div>  
            </div>
            <?php
        endwhile;
        wp_reset_postdata();
    endif;
    ?>  
   <a href="#" type="submit" class="btn btn-secondary"><?php _e('BACK', $this->car_share); ?></a>           
   <button type="submit" class="btn btn-default"><?php _e('CHECKOUT', $this->car_share); ?></button>                 
</form> 
</div> 