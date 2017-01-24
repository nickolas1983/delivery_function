<?php
session_start();

if (isset($_POST['total_count']) && $_POST['total_count'] && isset($_POST['delivered']) && $_POST['delivered']){
    $input = [
        'total_count' => $_POST['total_count'],
        'delivered' => $_POST['delivered'],
        'fail' => $_POST['fail'],
        'open' => $_POST['open'],
        'click' => $_POST['click']
    ];
}
else{
    $message = "Не введены обязательные реквизиты, в график подставленны значения по умолчанию.";
    $input = [
        'total_count' => 20000,
        'delivered' => 15000,
        'fail' => 2000,
        'open' => 3000,
        'click' => 500
    ];
}



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

    /*echo "<pre>";
    var_dump($percents);
    echo "</pre>";*/

    // define min width opens and clicks
    foreach ($open_click_width as $key => $width){
        if($width == 0){
            $open_click_width[$key] = 0;
        }
        else if ($width >0 && $width < $min_width){
            $open_click_width[$key] = $min_width;
        }
    }

    // conditions for delivered
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

                if (!strnatcmp ( $key , 'delivered' ) && ($result[$key] - ($result['open'] + $result['click']) < $min_width)){
                    if($result['open'] > $result['click'] * 2){
                        $result['open'] -= $difference;
                    }
                    else if($result['open'] < $result['click'] * 2){
                        $result['open'] -= $difference/2;
                        $result['click'] -= $difference/2;
                    }
                }
            }
            else{
                $result[$key] = $width;
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

    <script src="jquery-3.1.1.min.js"></script>
    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>


</head>
<body>
<div style="margin: 100px; width: <?= $width?>px">
    <form action="" method="post">
        <div class="form-group">
            <label for="total_count">Общее количество</label>
            <input type="text" class="form-control" id="total_count" name="total_count" <?php if (isset($_POST['total_count']) && $_POST['total_count']) echo "value = ".$_POST['total_count'].""; ?> placeholder="total_count">
        </div>
        <div class="form-group">
            <label for="delivered">Количество доставленных писем</label>
            <input type="text" class="form-control" id="delivered" name="delivered" <?php if (isset($_POST['delivered']) && $_POST['delivered']) echo "value = ".$_POST['delivered'].""; ?> placeholder="delivered">
        </div>
        <div class="form-group">
            <label for="fail">Не доставлено</label>
            <input type="text" class="form-control" id="fail" name="fail" <?php if (isset($_POST['fail']) && $_POST['fail']) echo "value = ".$_POST['fail'].""; ?> placeholder="fail">
        </div>
        <div class="form-group">
            <label for="open">Количество открытых писем</label>
            <input type="text" class="form-control" id="open" name="open" <?php if (isset($_POST['open']) && $_POST['open']) echo "value = ".$_POST['open'].""; ?> placeholder="open">
        </div>
        <div class="form-group">
            <label for="click">Количество переходов по ссылке</label>
            <input type="text" class="form-control" id="click" name="click" <?php if (isset($_POST['click']) && $_POST['click']) echo "value = ".$_POST['click'].""; ?> placeholder="click">
        </div>

        <button type="submit" class="btn btn-default">Submit</button>
    </form>
</div>


<div style="margin: 100px; width: <?= $width?>px">
    <?php
    if(isset($_SESSION['message'])){
        foreach ($_SESSION['message'] as $message) { ?>
            <div class="alert alert-danger" role="alert"><?=$message?></div>
        <?php }
    }
    else {
        if(isset($message)){ ?>
                <div class="alert alert-danger" role="alert"><?=$message?></div>
            <?php }
        $bar_width = bar_width($output, $width_koef); ?>
        <div class="progress">
            <div class="progress-bar progress-bar-success" data-toggle="tooltip" data-placement="bottom" title="Количество доставленных писем:  <?= $input['delivered']?>" style="width: <?= $bar_width['delivered']?>px">
                <div class="progress-bar progress-bar-info" data-toggle="tooltip" data-placement="top" title="Количество переходов по ссылке: <?= $input['click']?>" style="width: <?= $bar_width['click']?>px">
                    <?= $output['click']?>
                </div>
                <div class="progress-bar progress-bar-warning" data-toggle="tooltip" data-placement="top" title="Количество открытых писем: <?= $input['open']?>" style="width:  <?= $bar_width['open']?>px">
                    <?= $output['open']?>
                </div>
                <?= $output['delivered']?>
            </div>
            <div class="progress-bar" data-toggle="tooltip" data-placement="top" title="В процессе отправки: <?= $input['total_count'] - $input['delivered'] - $input['fail']?>" style="width: <?= $bar_width['progress']?>px">
                <?= $output['progress']?>
            </div>
            <div class="progress-bar progress-bar-danger" data-toggle="tooltip" data-placement="top" title="Не доставлено: <?= $input['fail']?>" style="width:  <?= $bar_width['fail']?>px">
                <?= $output['fail']?>
            </div>
        </div>
    <?php } ?>

</div>

<div style="margin: 100px; width: <?= $width?>px">
    <h1>Тестовые примеры</h1>
    <?php
    $tests = [
        [
            'total_count' => 100,
            'delivered' => 100,
            'fail' => 0,
            'open' => 100,
            'click' => 100
        ],
        [
            'total_count' => 100,
            'delivered' => 50,
            'fail' => 50,
            'open' => 50,
            'click' => 50
        ],
        [
            'total_count' => 100,
            'delivered' => 1,
            'fail' => 1,
            'open' => 1,
            'click' => 1
        ],
        [
            'total_count' => 100,
            'delivered' => 50,
            'fail' => 20,
            'open' => 30,
            'click' => 10
        ],
        [
            'total_count' => 100,
            'delivered' => 98,
            'fail' => 1,
            'open' => 88,
            'click' => 10
        ],
        [
            'total_count' => 100,
            'delivered' => 80,
            'fail' => 5,
            'open' => 60,
            'click' => 25
        ],
    ];
        foreach ($tests as $test){
        $output_test = get_percent($test);
        $test_bar_width = bar_width($output_test, $width_koef); ?>
        <div class="progress">
            <div class="progress-bar progress-bar-success" data-toggle="tooltip" data-placement="bottom" title="Количество доставленных писем:  <?= $test['delivered']?>" style="width: <?= $test_bar_width['delivered']?>px">
                <div class="progress-bar progress-bar-info" data-toggle="tooltip" data-placement="top" title="Количество переходов по ссылке: <?= $test['click']?>" style="width: <?= $test_bar_width['click']?>px">
                    <?= $output_test['click']?>
                </div>
                <div class="progress-bar progress-bar-warning" data-toggle="tooltip" data-placement="top" title="Количество открытых писем: <?= $test['open']?>" style="width:  <?= $test_bar_width['open']?>px">
                    <?= $output_test['open']?>
                </div>
                <?= $output_test['delivered']?>
            </div>
            <div class="progress-bar" data-toggle="tooltip" data-placement="top" title="В процессе отправки: <?= $test['total_count'] - $test['delivered'] - $input['fail']?>" style="width: <?= $test_bar_width['progress']?>px">
                <?= $output_test['progress']?>
            </div>
            <div class="progress-bar progress-bar-danger" data-toggle="tooltip" data-placement="top" title="Не доставлено: <?= $test['fail']?>" style="width:  <?= $test_bar_width['fail']?>px">
                <?= $output_test['fail']?>
            </div>
        </div>
    <?php } ?>
</div>

<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })
</script>
</body>


