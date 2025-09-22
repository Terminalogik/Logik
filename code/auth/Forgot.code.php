<?php
namespace App\Code;

use Logik\Code;

class ForgotCode extends Code
{
    public function think(): void
    {
        // Render view "login" wrapped in the default axiom "axiom_login"
        $this->view('auth/forgot', ['title' => 'Forgot Password'], 'axiom_login');
    }
}