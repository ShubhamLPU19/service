<?php

namespace App\Http\Controllers\Admin;

use Gate;
use Symfony\Component\HttpFoundation\Response;
use App\Ticket;
use Auth;

class HomeController
{
    public function index()
    {
        abort_if(Gate::denies('dashboard_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if(auth()->user()->isAgent())
        {
            $totalTickets = Ticket::where(["assigned_to_user_id"=>Auth::user()->id])->count();
            $openTickets = Ticket::where(["assigned_to_user_id"=>Auth::user()->id])->whereHas('status', function($query) {
                $query->whereName('Open');
            })->count();
            $closedTickets = Ticket::where(["assigned_to_user_id"=>Auth::user()->id])->whereHas('status', function($query) {
                $query->whereName('Closed');
            })->count();
        }else{
            $totalTickets = Ticket::count();
            $openTickets = Ticket::whereHas('status', function($query) {
                $query->whereName('Open');
            })->count();
            $closedTickets = Ticket::whereHas('status', function($query) {
                $query->whereName('Closed');
            })->count();
        }

        return view('home', compact('totalTickets', 'openTickets', 'closedTickets'));
    }
}
