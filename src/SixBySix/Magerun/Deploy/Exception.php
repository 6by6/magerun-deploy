<?php

namespace SixBySix\Magerun\Deploy;

class Exception extends \Exception
{
    const INVALID_PERMISSIONS_ISSUE = 100;

    const CONFIG_NOT_FOUND = 200;
    const CONFIG_INVALID_FORMAT = 201;
    const CONFIG_OUT_OF_DATE = 202;
    const CONFIG_INVALID_VALUE = 203;

    const IO_ERROR = 300;
}