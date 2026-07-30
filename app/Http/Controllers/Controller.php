<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    // Laravel 11+ ships a bare base controller. AuthorizesRequests is added
    // back so actions can call $this->authorize(...) against the policies.
    use AuthorizesRequests;
}
