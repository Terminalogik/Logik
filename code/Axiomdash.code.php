<?php
namespace App\Code;

class DashCode extends Code
{
    public function userid($id): void
    {
        $this->view('user', ['title' => "User #{$id}", 'id' => $id], 'axiom_dash');
    }
}