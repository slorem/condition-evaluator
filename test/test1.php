<?php

$data = array(
    'a' => 42,
    'b' => 'apple',
);

$cond = 'not a > 30 or b === "apple" or 1 ==== 1';


require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'autoloader' . DIRECTORY_SEPARATOR . 'ConditionEvaluatorAutoloader.php';

$ce = new \Slorem\ConditionEvaluator\ConditionEvaluator();

var_dump($ce->evaluate($cond, $data));

