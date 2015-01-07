<script>

    var status_key = 0;

    function statusTableRow(car_id, key, from_date, from_hour, from_min, to_date, to_hour, to_min) {
        
        console.log(to_date);

        status_key = key;

        var str = '<tr class="item">' +
                '<td>' +
                '<select name="car[' + car_id + '][status][' + status_key + '][status]">'+
                '<option value="<?php echo Car_share::UNAVAILABLE ?>"><?php _e('Unavailable', $this->car_share) ?></option>' +
                '<option value="<?php echo Car_share::RENTED ?>"><?php _e('Rented', $this->car_share) ?></option>' +
                '</select>' +
                '</td>' +
                '<td>' +
                '<input id="status-date-from-' + car_id + '_'+ status_key +'" class="status-date-from" type="text" name="car[' + car_id + '][status][' + status_key + '][from]" value="' + from_date + '">' +
                '<select  name="car[' + car_id + '][status][' + status_key + '][from_hour]">';
        
                <?php for($i = 0; $i < 24; $i++): ?>
                    str += '<option value="<?php echo $i ?>"'
                    str += '><?php echo sprintf("%02s", $i)  ?></option>';
                <?php endfor; ?>
                
        str += '</select> : ' +
                '<select name="car[' + car_id + '][status][' + status_key + '][from_min]">';
                
                <?php for($i = 0; $i < 60; $i++): ?>
                    str += '<option value="<?php echo $i ?>"><?php echo sprintf("%02s", $i)  ?></option>';
                <?php endfor; ?>
                
        str +=  '</select>' +
                '</td>' +
                '<td>' +
                '<input id="status-date-to-' + car_id + '_'+ status_key +'" class="status-date-to" type="text" name="car[' + car_id + '][status][' + status_key + '][to]" value="' + to_date + '">' +
                '<select  name="car[' + car_id + '][status][' + status_key + '][to_hour]">';
        
                <?php for($i = 0; $i < 24; $i++): ?>
                    str += '<option value="<?php echo $i ?>"><?php echo sprintf("%02s", $i)  ?></option>';
                <?php endfor; ?>        
        
        str +=  '</select>';
        
        str += '<select name="car[' + car_id + '][status][' + status_key + '][to_min]">';;

                <?php for($i = 0; $i < 60; $i++): ?>
                    str += '<option value="<?php echo $i ?>"><?php echo sprintf("%02s", $i)  ?></option>';
                <?php endfor; ?>

        str +=  '</select>' +
                '</td>' +
                '<td>' +
                '<button class="remove-row" type="button"><?php _e("X", $this->car_share) ?></button>' +
                '</td>' +
                '</tr>';

        status_key++;
        return str;
    }
    
    function apply_datepicker(element){
        
            var date_from = element.find(".status-date-from");
            var date_to = element.find(".status-date-to");

            date_from.datepicker({
                dateFormat: 'dd.mm.yy',
                onSelect: function (selected_date) {
                    date_to.datepicker("option", "minDate", selected_date);
                }
            });
            
            date_to.datepicker({
                dateFormat: 'dd.mm.yy',
                onSelect: function (selected_date) {
                    date_from.datepicker("option", "maxDate", selected_date);
                }
            });        
        
    }

/*
    function reload_date_picker(){
        $( "#post-body" ).find(".status .item").each(function( index ) {

            var date_from = $( this ).find('.status-date-from');
            var date_to = $( this ).find('.status-date-to');

            //date_from.datepicker();
            //date_to.datepicker();

        });
    }*/

    jQuery(document).ready(function ($) {
        
        $('.postbox').on('click', '.new-car', function (event) {            
            var id = $(this).data('car_id');                              
            var new_box = $(this).parents('.postbox').clone();            
            $('#single_car_box_' + id).after(new_box);
        });
        
        $('.postbox').on('click', '.clone-car', function (event) {            
            var new_box = $(this).parents('.postbox').clone();
            console.log('clone car');
        });
        
        $('.postbox').on('click', '.delete-car', function (event) {
            //$(this).parents(".item").remove();
            //var new_box = $(this).parent('.postbox').cone();
            console.log('delete car');
        });        

        $('.add-status').click(function (e) {
            var car_id = $(this).data('car_id');                        
            var row = statusTableRow(car_id, status_key, '', '', '', '', '', '');            
            $(this).parents('.status').find('tbody').append(row);            
            var element = $(this).parents('.status').find('tbody').find('.item:last');
            apply_datepicker(element);
        });

        $('.status').on('click', 'tbody .remove-row', function (event) {
            $(this).parents(".item").remove();
        });
    });
</script>