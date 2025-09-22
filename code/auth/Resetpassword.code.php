<?php
namespace App\Code;

use Logik\Code;

class ResetpasswordCode extends Code
{
    public function think(): void
    {
        // Render view "resetpassword" wrapped in the default axiom "axiom_login"
        $this->view('auth/resetpassword', ['title' => 'Reset Password'], 'axiom_login');
    }
}