<?php
session_start();


$input = [
    'total_count' => 163,
    'delivered' => 147,
    'fail' => 4,
    'open' => 47,
    'click' => 21
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
    $progress =  100 - $delivered - $fail;
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
echo "<pre>";
var_dump(get_percent($input));
echo "</pre>";
