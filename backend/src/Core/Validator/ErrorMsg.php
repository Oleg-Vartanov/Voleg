<?php

namespace App\Core\Validator;

enum ErrorMsg: string
{
    case MAX_AMOUNT = 'This value {{ value }} should be less than or equal to {{ compared_value }}.';
}
