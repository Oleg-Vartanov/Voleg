<?php

namespace App\Controller;

use App\Trait\ControllerValidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiController extends AbstractController
{
    use ControllerValidator;
}