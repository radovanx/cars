<script>

    var status_key = new Array();
    var new_car_key = 1;

    function statusTableRow(car_id, key, from_date, from_hour, from_min, to_date, to_hour, to_min, selected_status) {
        
        //console.log(selected_status);
        //console.log(from_date);                
        <?php               
        $statuses = array(
            Car_share::STATUS_UNAVAILABLE => 'Unavailable',
            Car_share::STATUS_RENTED => 'Rented',
        );
        
        ?>
        var str = '<tr class="item">' +
                '<td>' +
                '<select name="car[' + car_id + '][status][' + status_key[car_id] + '][status]">';
    
                <?php foreach($statuses as $key => $label): ?>
                    
                    str += '<option value="<?php echo $key ?>" ';
                    str += <?php echo $key ?> == selected_status ? ' selected="selected" ' : '';
                    str += '><?php _e($label, $this->car_share) ?></option>';                    
                    
                <?php endforeach; ?>    
                
        str +='</select>' +
                '</td>' +
                '<td>' +
                '<input id="date-from-' + car_id + '_'+ status_key[car_id] +'" class="date-from" type="text" name="car[' + car_id + '][status][' + status_key[car_id] + '][from]" value="' + from_date + '">' +
                '<select  name="car[' + car_id + '][status][' + status_key[car_id] + '][from_hour]">';

                <?php for($i = 0; $i < 24; $i++): ?>
                    str += '<option value="<?php echo $i ?>"'
                    str += <?php echo $i ?> == from_hour ? ' selected="selected" ' : '';
                    str += '><?php echo sprintf("%02s", $i)  ?></option>';
                <?php endfor; ?>

        str += '</select> : ' +
                '<select name="car[' + car_id + '][status][' + status_key[car_id] + '][from_min]">';

                <?php for($i = 0; $i < 60; $i++): ?>
                    str += '<option value="<?php echo $i ?>"';
                    str += <?php echo $i ?> == from_min ? ' selected="selected" ' : '';
                    str += '><?php echo sprintf("%02s", $i)  ?></option>';
                <?php endfor; ?>

        str +=  '</select>' +
                '</td>' +
                '<td>' +
                '<input id="date-to-' + car_id + '_'+ status_key[car_id] +'" class="date-to" type="text" name="car[' + car_id + '][status][' + status_key[car_id] + '][to]" value="' + to_date + '">' +
                '<select  name="car[' + car_id + '][status][' + status_key[car_id] + '][to_hour]">';

                <?php for($i = 0; $i < 24; $i++): ?>
                    str += '<option value="<?php echo $i ?>"';
                    str += <?php echo $i ?> == to_hour ? ' selected="selected" ' : '';
                    str += '><?php echo sprintf("%02s", $i)  ?></option>';
                <?php endfor; ?>

        str +=  '</select>';

        str += '<select name="car[' + car_id + '][status][' + status_key[car_id] + '][to_min]">';;

                <?php for($i = 0; $i < 60; $i++): ?>
                    str += '<option value="<?php echo $i ?>"';
                    str += <?php echo $i ?> == to_min ? ' selected="selected" ' : '';
                    str += '><?php echo sprintf("%02s", $i)  ?></option>';
                <?php endfor; ?>

        str +=  '</select>' +
                '</td>' +
                '<td>' +
                '<button class="remove-row" type="button"><?php _e("X", $this->car_share) ?></button>' +
                '</td>' +
                '</tr>';

        status_key[car_id]++;
        return str;
    }

    function apply_datepicker(element){

            var date_from = element.find(".date-from");
            var date_to = element.find(".date-to");

            date_from.datepicker({
                dateFormat: 'dd-mm-yy',
                onSelect: function (selected_date) {
                    date_to.datepicker("option", "minDate", selected_date);
                }
            });

            date_to.datepicker({
                dateFormat: 'dd-mm-yy',
                onSelect: function (selected_date) {
                    date_from.datepicker("option", "maxDate", selected_date);
                }
            });

    }

    jQuery(document).ready(function ($) {

        // add single car
        $('.postbox-container').on('click', '.new-car', function (event) {

            var self = $(this);
            var id = $(this).data('car_id');

            jQuery.ajax({
                type: 'post',
                url: ajaxurl,
                //dataType: "json",
                data: {
                    'id': id,
                    'action': 'create_single_car',
                },
                beforeSend: function () {
                        self.prop("disabled", true);
                    }
                }).done(function (ret) {
                    var new_element = $('#single_car_box_' + id).after(ret);
                }).fail(function (ret) {
                    alert('<?php esc_attr_e('Create new car failed', $this->car_share) ?>');
                }).always(function () {
                    self.prop("disabled", false);
                });
        });

        // clone single car
        $('.postbox-container').on('click', '.clone-car', function (event) {

            var self = $(this);
            var id = $(this).data('car_id');

            jQuery.ajax({
                type: 'post',
                url: ajaxurl,
                //dataType: "json",
                data: {
                    'id': id,
                    'action': 'create_single_car',
                    'form': $(this).parents('form').serialize()
                },
                beforeSend: function () {
                        self.prop("disabled", true);
                    }
                }).done(function (data) {
                    
                    //console.log(data);
                    
                    var new_element = $('#single_car_box_' + id).after(data);
                    //var statuses = data.car_status;                    
                    //$('#single_car_box_' + id).after(data.html);

                    //console.log(statuses);

                    self.prop("disabled", false);
                }).fail(function (ret) {
                    alert('<?php esc_attr_e('Cloning car failed', $this->car_share) ?>');
                }).always(function () {
                    self.prop("disabled", false);
                });

            //var new_box = $(this).parents('.postbox').clone();
        });

        //delete single car
        $('.postbox-container').on('click', '.delete-car', function (event) {

            var self = $(this);
            var id = $(this).data('car_id');

            jQuery.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: "json",
                data: {
                    'id': id,
                    'action': 'delete_single_car',
                },
                beforeSend: function () {
                        self.prop("disabled", true);
                    }
                }).done(function (ret) {
                    self.prop("disabled", false);
                    $('#single_car_box_' + id).remove();
                }).fail(function (ret) {
                    alert('<?php esc_attr_e('Delete failed', $this->car_share) ?>');
                }).always(function () {
                    self.prop("disabled", false);
                });
        });

        // add new status
        $('.postbox-container').on('click', '.add-status', function (event) {
            var car_id = $(this).data('car_id');
            var row = statusTableRow(car_id, status_key, '', '', '', '', '', '', '');
            $(this).parents('.status').find('tbody').append(row);
            var element = $(this).parents('.status').find('tbody').find('.item:last');
            apply_datepicker(element);
        });

        // remove status
        $('.postbox-container').on('click', 'tbody .remove-row', function (event) {
            $(this).parents(".item").remove();
        });
    });
</script>