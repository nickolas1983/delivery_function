<?php
session_start();


$input = [
    'total_count' => 163,
    'delivered' => 77,
    'fail' => 82,
    'open' => 3,
    'click' => 3
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

    $main_percents = [
        'delivered' => $percents['delivered'],
        'progress' => $percents['progress'],
        'fail' => $percents['fail']
    ];

    $open_click_percents = [
        'open' => $percents['open'],
        'click' => $percents['click']
    ];



    asort($main_percents);

    // key of max value element
    end($main_percents);
    $max_key = key($main_percents);
    reset($main_percents);


    // check opens and clicks
    /*$difference = 0;
    foreach ($open_click_percents as $key => $percent){
        if($percent == 0){
            $result[$key] = 0;
        }
        else if ($percent >0 && $percent < 4){
            $result[$key] = 4;
            $difference += 4 - $percent;
        }
        else {
            $result[$key] = $percent;
        }
    }
    $percents['delivered'] =*/


    //
    $result = array();

    $difference = 0;
    foreach ($main_percents as $key => $percent){
        if($percent == 0){
            $result[$key] = 0;
        }
        else if ($percent >0 && $percent * $width_koef < $min_width){
            $result[$key] = $min_width;
            $difference += $min_width - $percent * $width_koef;
        }
        else {
            if($key == $max_key && $difference > 0){
                $result[$key] = $percent * $width_koef - $difference;
            }
            else{
                $result[$key] = $percent * $width_koef;
            }
        }
    }

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
                <?= $output['delivered']?>
            </div>
            <div class="progress-bar progress-bar" style="width: <?= $bar_width['progress']?>px">
                <?= $output['progress']?>
            </div>
            <div class="progress-bar progress-bar-danger" style="width:  <?= $bar_width['fail']?>px">
                <?= $output['fail']?>
            </div>
        </div>
    <?php } ?>

</div>


</body>


