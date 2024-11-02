<?php

namespace App\Controller;

use App\Trait\ControllerValidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApiController extends AbstractController
{
    use ControllerValidator;
}