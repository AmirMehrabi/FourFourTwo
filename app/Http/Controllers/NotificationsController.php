<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class NotificationsController extends Controller
{
    /**
     * Display the notifications page.
     */
    public function index()
    {
        return Inertia::render('Notifications/Index');
    }
}
