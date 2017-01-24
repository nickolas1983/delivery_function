<?php
session_start();


$input = [
    'total_count' => 100,
    'delivered' => 50,
    'fail' => 50,
    'open' => 15,
    'click' => 1
];

function get_percent(array $input){
    unset($_SESSION['message']);

    if ($input['delivered']+$input['fail'] > $input['total_count']){
        $_SESSION['message'][]='Cумма доставленных и не доставленных писем превышает общее количество';
    }
    if ($input['open'] > $input['delivered']){
        $_SESSION['message'][]='Количество открытых писем первышает количество доставленных';
    }
    if ($input['click'] > $input['open']){
        $_SESSION['message'][]='Переходы по ссылке превышают количество открытых писем';
    }
    if (isset($_SESSION['message'])){
        return $_SESSION['message'];
    }

    $delivered = round(($input['delivered']/$input['total_count'])*100, 1);
    $fail =      round(($input['fail']/$input['total_count'])*100, 1);
    $progress =  round(100 - $delivered - $fail, 1);
    $open =      round(($input['open']/$input['delivered'])*100, 1);
    $click =     round(($input['click']/$input['delivered'])*100, 1);


    $output = [
        'total_count' => 100.0,
        'delivered' => $delivered,
        'progress' => $progress,
        'fail' => $fail,
        'open' => $open,
        'click' => $click

    ];

    return $output;
}


function bar_width(array $percents, $width_koef, $min_width = 32){

    $main_width = [
        'delivered' => $percents['delivered'] * $width_koef,
        'progress' => $percents['progress'] * $width_koef,
        'fail' => $percents['fail'] * $width_koef
    ];

    $open_click_width = [
        'open' => $percents['open'] * $width_koef * ($percents['delivered'] / 100),
        'click' => $percents['click'] * $width_koef * ($percents['delivered'] / 100)
    ];

    echo "<pre>";
    var_dump($percents);
    echo "</pre>";

    echo "<pre>";
    var_dump($main_width);
    var_dump($open_click_width);
    echo "</pre>";

    // define min width opens and clicks
    foreach ($open_click_width as $key => $width){
        if($width == 0){
            $open_click_width[$key] = 0;
        }
        else if ($width >0 && $width < $min_width){
            $open_click_width[$key] = $min_width;
        }
    }

    // define min width delivered
    $difference_deliver = 0;
    if($open_click_width['open'] == 0 && $open_click_width['click'] == 0 && $main_width['delivered'] < $min_width){
        $difference_deliver = $min_width - $main_width['delivered'];
        $main_width['delivered'] = $min_width;
    }
    else if(($open_click_width['open'] > 0 && $open_click_width['click'] == 0)){
        if ($main_width['delivered'] < $min_width * 2){
            $difference_deliver = $min_width * 2 - $main_width['delivered'];
            $main_width['delivered'] = $min_width * 2;
            $open_click_width['open'] = $min_width;
        }
        else if ($main_width['delivered'] > $min_width * 2){
            if ($main_width['delivered'] - $open_click_width['open'] < $min_width){
                $open_click_width['open'] = $main_width['delivered'] - $min_width;
            }
        }
    }
    else if($open_click_width['open'] > 0 && $open_click_width['click'] > 0 ){
        if ($main_width['delivered'] < $min_width * 3){
            $difference_deliver = $min_width * 3 - $main_width['delivered'];
            $main_width['delivered'] = $min_width * 3;
            $open_click_width['open'] = $min_width;
            $open_click_width['click'] = $min_width;
        }
        else if($main_width['delivered'] > $min_width * 3){

            if($main_width['delivered'] - $open_click_width['open'] < $min_width){
                if ($open_click_width['open'] - $open_click_width['click'] < $min_width){
                    $open_click_width['open'] = $min_width;
                    $open_click_width['click'] -= $min_width * 2;
                }
                else if(($open_click_width['open'] - $open_click_width['click']) > $min_width ){
                    if ($open_click_width['click'] > $min_width * 2){
                        $open_click_width['open'] = $open_click_width['open'] - $open_click_width['click'] ;
                        $open_click_width['click'] -= $min_width ;
                    }
                    else if($open_click_width['click'] < $min_width * 2){
                        $open_click_width['open'] = $open_click_width['open'] - $open_click_width['click'] - $min_width;
                    }

                }
            }
            else if($main_width['delivered'] - $open_click_width['open'] > $min_width){

                if ($main_width['delivered'] - ($open_click_width['open'] + $open_click_width['click']) < $min_width){
                    $full_stek = $main_width['delivered'] - $min_width;
                    $open_click = $open_click_width['open'] + $open_click_width['click'];
                    $open_click_width['open'] *=  $full_stek/$open_click;
                    $open_click_width['click'] *=  $full_stek/$open_click;
                    echo "<pre>";
                    var_dump($main_width);
                    var_dump($open_click_width);
                    echo "</pre>";

                }
                else if($main_width['delivered'] - ($open_click_width['open'] + $open_click_width['click'])  > $min_width){

                }

            }

        }
    }


    // key of max width element of $main_width
    asort($main_width);
    end($main_width);
    $max_key = key($main_width);
    reset($main_width);

    $result = array();

    $result['open'] = $open_click_width['open'];
    $result['click'] = $open_click_width['click'];

    $difference = 0;
    foreach ($main_width as $key => $width){
        if($width == 0){
            $result[$key] = 0;
        }
        else if ($width >0 && $width < $min_width){
            $result[$key] = $min_width;
            $difference += $min_width - $width;
        }
        else {
            if($key == $max_key && ($difference > 0 || $difference_deliver >0) ){
                $result[$key] = $width - $difference - $difference_deliver;
            }
            else{
                $result[$key] = $width;
            }
        }
    }



    echo "<pre>";
    var_dump($result);
    echo "</pre>";
    return $result;
}


//widt in pixels
$width = 800;
$width_koef = $width/100;
$output = get_percent($input); ?>

<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Delivery progress</title>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>


</head>
<body>
<div style="margin: 100px; width: <?= $width?>px">
    <?php
    if(isset($_SESSION['message'])){
        foreach ($_SESSION['message'] as $message) {
            echo $message;
        }
    }
    else {
        $bar_width = bar_width($output, $width_koef); ?>
        <div class="progress">
            <div class="progress-bar progress-bar-success" style="width: <?= $bar_width['delivered']?>px">
                <div class="progress-bar progress-bar-info" style="width: <?= $bar_width['click']?>px">
                    <?= $output['click']?>
                </div>
                <div class="progress-bar progress-bar-warning" style="width:  <?= $bar_width['open']?>px">
                    <?= $output['open']?>
                </div>
                <?= $output['delivered']?>
            </div>
            <div class="progress-bar" style="width: <?= $bar_width['progress']?>px">
                <?= $output['progress']?>
            </div>
            <div class="progress-bar progress-bar-danger" style="width:  <?= $bar_width['fail']?>px">
                <?= $output['fail']?>
            </div>
        </div>
    <?php } ?>

</div>


</body>


