<?php
namespace App\Code;

use Logik\Code;

class WelcomeCode extends Code
{
    public function index(): void
    {
        $this->view('welcome', ['title' => 'Welcome'], 'axiom_welcome');
    }
}