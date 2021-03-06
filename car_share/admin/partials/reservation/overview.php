<?php
global $wpdb;

$date = new DateTime();
$date->setDate($year, $month, 1);
$date->setTime(0, 0, 0);

$temp_date = clone $date;

$next_month = new DateTime();
$next_month->modify('first day of next month');

$prev_month = new DateTime();
$prev_month = new DateTime('last day of last month');

$sql = "SELECT * FROM sc_single_car ORDER BY parent ASC";
$all_cars = $wpdb->get_results($sql);
?>


<div class="sc-legend sc-reservation">

    <div class="sc-row">
        <div class="day-cell car free sc-pull-left">
            <div class="car booked">
            </div>
        </div>
        <?php _e('Booked', 'car_share'); ?>
        <div class="clear"></div>
    </div>


    <div class="sc-row">
        <div class="day-cell car free sc-pull-left">
            <div class="car rented">
            </div>
        </div>
        <?php _e('Confirmed booking', 'car_share'); ?>
        <div class="clear"></div>
    </div>

    <div class="sc-row">
        <div class="day-cell car free sc-pull-left">
            <div class="car unavailable">
            </div>
        </div>
        <?php _e('Unavailable', 'car_share'); ?>
        <div class="clear"></div>
    </div>

    <div class="sc-row">
        <div class="day-cell car free sc-pull-left">
            <div class="car">
            </div>
        </div>
        <?php _e('Available', 'car_share'); ?>
        <div class="clear"></div>
    </div>
</div>

<div id="sc-reservation" class="sc-reservation">

    <div class="overview">
        <div class="loader">
            <div id="fountainG">
                <div id="fountainG_1" class="fountainG">
                </div>
                <div id="fountainG_2" class="fountainG">
                </div>
                <div id="fountainG_3" class="fountainG">
                </div>
                <div id="fountainG_4" class="fountainG">
                </div>
                <div id="fountainG_5" class="fountainG">
                </div>
                <div id="fountainG_6" class="fountainG">
                </div>
                <div id="fountainG_7" class="fountainG">
                </div>
                <div id="fountainG_8" class="fountainG">
                </div>
            </div>
        </div>
    </div>

    <div class="form-wrap">
        <form id="spz-search" class="spz-search" action="" method="post">
            <label>
                <?php _e('Vehicle registration plate:', 'car_share') ?>
                <input id="sc_spz" type="text" name="sc_spz" value="">
            </label>
            <button id="sc_spz_search" type="button" class="button button-primary button-large"><?php _e('Search', 'car_share') ?></button>
            <input id="sc-current-year" name="sc-current-year" type="hidden" value="<?php echo $date->format('Y'); ?>">
            <input id="sc-current-month" name="sc-current-month" type="hidden" value="<?php echo $date->format('n'); ?>">
            <input id="sc-order" name="sc-order" type="hidden" value="">
        </form>
        <div class="clear"></div>
    </div>





    <div id="sc-months" class="sc-months">

    </div>


    <div class="clear"></div>
</div>

<script>
    jQuery(document).ready(function ($) {

        function load_months(year, month, current_month) {

            $.ajax({
                type: 'post',
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                data: {
                    'action': 'load_months',
                    'year': year,
                    'month': month,
                    'form': $('#spz-search').serialize(),
                    'current_month': current_month
                },
                beforeSend: function () {
                    //self.prop("disabled", true);
                    $('#sc-reservation .overview').show();
                }
            }).done(function (ret) {
                $('#sc-months').html(ret);
            }).fail(function (ret) {

            }).always(function () {
                $('#sc-reservation .overview').hide();
            });
        }

<?php
$now = new DateTime();
?>
        load_months(<?php echo $now->format('Y') ?>, <?php echo $now->format('n') ?>, 0);

        $("#sc-reservation").on("click", "#sc-navigation a", function (e) {
            e.preventDefault();
            load_months($(this).data('year'), $(this).data('month'), 0);
        });

        $("#sc-reservation").on("click", "#sc_spz_search", function (e) {
            e.preventDefault();
            load_months('', '', 1);
        });
        
        $("#sc-reservation").on("click", "#sc-car-sorting a", function (e) {
            e.preventDefault();
            $('#sc-order').val($(this).data('order'));
            load_months('', '', 1);
        });                

    });
</script>