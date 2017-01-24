<?php
session_start();


$input = [
    'total_count' => 150,
    'delivered' => 147,
    'fail' => 4,
    'open' => 155,
    'click' => 169
];

function get_percent(array $input){
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
    $output = array();

    return $output;
}

var_dump(get_percent($input));