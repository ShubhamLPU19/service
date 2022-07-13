<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use App\Ticket;

class ServiceController extends Controller
{
    public function createComplaint()
    {
        return view('service.create');
    }

    public function storeComplaint(Request $request)
    {
        // dd($request->all());
        $category = '';
        if(!empty($request->category1))
        {
            $category = "Lock_" . $request->category1;
        }elseif(!empty($request->category2))
        {
            $category = "Paint_" . $request->category2;
        }elseif(!empty($request->category3))
        {
            $category = "Rust_" . $request->category3;
        }
        if(!empty($request->category1) && !empty($request->category2))
        {
            $category = "Lock_" . $request->category1 . ',' . "Paint_" . $request->category2;
        }
        if(!empty($request->category1) && !empty($request->category3))
        {
            $category = "Lock_" . $request->category1 . ',' . "Rust_" . $request->category3;
        }
        if(!empty($request->category2) && !empty($request->category3))
        {
            $category = "Paint_" . $request->category2 . ',' . "Rust_" . $request->category3;
        }
        if(!empty($request->category1) && !empty($request->category2) && !empty($request->category3))
        {
            $category = "Lock_" . $request->category1 . ',' . "Paint_" . $request->category2 . ',' . "Rust_" . $request->category3;
        }
        if(empty($request->category1) && empty($request->category2) && empty($request->category3))
        {
            return redirect()->back()->with('message', 'Please select problem.');
        }
        $today_regs = DB::table('tickets')->whereDate('tickets.created_at',Carbon::now())->count();
        $number = $today_regs + 1;
        $year = date('Y') % 100;
        $month = date('m');
        $day = date('d');

        $reg_num = $year . $month . $day . $number;
        dd($reg_num);
        $ticket = new Ticket();
        $ticket->id = $reg_num;
        $ticket->customer_name = $request->customer_name;
        $ticket->customer_mobile = $request->customer_mobile;
        $ticket->address = $request->address;
        $ticket->state = $request->state;
        $ticket->city = $request->city;
        $ticket->pincode = $request->pincode;
        $ticket->model = $request->model;
        $ticket->category = $category;
        $ticket->status_id = 1;
        $ticket->save();
        dd($ticket);
        if(!empty($ticket->id))
        {
            dd("Testing");
            $headers = array(
                "Content-Type" => 'application/json',
                "Authorization"=> 'Basic WmN1a0VfLUJEYmdEZXVnMHhVVlZfYVNueFdsaTE1Z2pHSk12M1pDSjA4QTo='
            );
            $apiURL = 'https://api.interakt.ai/v1/public/track/users/';
            $postInput = array(
                "phoneNumber"=> $request->customer_mobile,
                "countryCode"=> "+91",
                "traits"=> array(
                    "name"=> $request->customer_name,
                    "phoneNumber"=> $request->customer_mobile,
                    "address" => $request->address,
                    "ticket_id"=> $ticket->id,
                    "issue" => $category,
                    "createdAt"=> date("Y-m-d"),
                )
            );
            $response = Http::withHeaders($headers)->post($apiURL, $postInput);
            $responseBody = json_decode($response->getBody(), true);
            if($responseBody['result'])
            {
                $eventApiURL = 'https://api.interakt.ai/v1/public/track/events/';
                $postEventInput = array(
                    "phoneNumber"=> $request->customer_mobile,
                    "countryCode"=> "+91",
                    "event"=> "New Status",
                    "traits"=> array(
                        "ticket_id"=> $ticket->id,
                        "issue" => $category,
                    )
                );

                $response = Http::withHeaders($headers)->post($eventApiURL, $postEventInput);
                $responseBody = json_decode($response->getBody(), true);
            }
        }
    }
}
