<script>

    var status_key = 0;

    function statusTableRow(car_id, key, from_date, from_hour, from_min, to_date, to_hour, to_min) {
        
        console.log(to_date);

        status_key = key;

        var str = '<tr class="item">' +
                '<td>' +
                '<select name="status[' + car_id + '][' + status_key + '][status]">'+
                '<option value="<?php echo Car_share::UNAVAILABLE ?>"><?php _e('Unavailable', $this->car_share) ?></option>' +
                '<option value="<?php echo Car_share::RENTED ?>"><?php _e('Rented', $this->car_share) ?></option>' +
                '</select>' +
                '</td>' +
                '<td>' +
                '<input id="status-date-from-' + car_id + '_'+ status_key +'" class="status-date-from" type="text" name="status[' + car_id + '][' + status_key + '][from]" value="' + from_date + '">' +
                '<select  name="status[' + car_id + '][' + status_key + '][from_hour]">';
        
                <?php for($i = 0; $i < 24; $i++): ?>
                    str += '<option value="<?php echo $i ?>"'
                    str += '><?php echo sprintf("%02s", $i)  ?></option>';
                <?php endfor; ?>
                
        str += '</select> : ' +
                '<select name="status[' + car_id + '][' + status_key + '][from_min]">';
                
                <?php for($i = 0; $i < 60; $i++): ?>
                    str += '<option value="<?php echo $i ?>"><?php echo sprintf("%02s", $i)  ?></option>';
                <?php endfor; ?>
                
        str +=  '</select>' +
                '</td>' +
                '<td>' +
                '<input id="status-date-to-' + car_id + '_'+ status_key +'" class="status-date-to" type="text" name="status[' + car_id + '][' + status_key + '][to]" value="' + to_date + '">' +
                '<select  name="status[' + car_id + '][' + status_key + '][to_hour]">';
        
                <?php for($i = 0; $i < 24; $i++): ?>
                    str += '<option value="<?php echo $i ?>"><?php echo sprintf("%02s", $i)  ?></option>';
                <?php endfor; ?>        
        
        str +=  '</select>';
        
        str += '<select name="status[' + car_id + '][' + status_key + '][to_min]">';;

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
    
    function apply_

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

        $('.add-status').click(function (e) {

            var car_id = $(this).data('car_id');            
            
            var row = statusTableRow(car_id, status_key, '', '', '', '', '', '');
            
            $(this).parents('.status').find('tbody').append(row);
            
            var element = $(this).parents('.status').find('tbody').find('.item:last');



        });

        $('.status').on('click', 'tbody .remove-row', function (event) {
            $(this).parents(".item").remove();
        });
    });
</script>