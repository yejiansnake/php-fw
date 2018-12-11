<?php

require(__DIR__ . '/../config/env.php');

if (empty(YII_DEBUG))
{
    return;
}

phpinfo();