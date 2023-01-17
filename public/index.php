<?php

use App\Kernel;

if(isset($_SERVER['HTTP_CF_CONNECTING_IP'])) { //Restore remote address behind CF
    $_SERVER['HTTP_X_FORWARDED_FOR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
}

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
