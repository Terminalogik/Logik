<?php
namespace App\Code;

use Logik\Code;

class LogoutCode extends Code
{
    public function think(): void
    {
        // Log out the user and redirect to home page
        $this->auth->logout();
        $this->redirect('/');
    }
}