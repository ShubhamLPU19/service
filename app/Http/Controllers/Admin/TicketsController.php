<?php

namespace App\Http\Controllers\Admin;

use App\Category;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\MassDestroyTicketRequest;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Priority;
use App\Status;
use App\Ticket;
use App\User;
use App\TicketAuditLog;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use App\Exports\TicketExport;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Auth;
use Session;
use Illuminate\Support\Facades\Redirect;

class TicketsController extends Controller
{
    use MediaUploadingTrait;

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Ticket::with(['status', 'priority','assigned_to_user', 'comments'])
                // ->where("tickets.status_id","!=",6)
                ->filterTickets($request)
                ->select(sprintf('%s.*', (new Ticket)->table));
            $table = Datatables::of($query);
            $table->editColumn('created_at', function($data){ $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d-m-Y'); return $formatedDate; });
            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate      = 'ticket_show';
                $editGate      = 'ticket_edit';
                $deleteGate    = 'ticket_delete';
                $crudRoutePart = 'tickets';

                return view('partials.datatablesActions', compact(
                    'viewGate',
                    'editGate',
                    'deleteGate',
                    'crudRoutePart',
                    'row'
                ));
            });

            $table->editColumn('id', function ($row) {
                return $row->id ? $row->id : "";
            });
            $table->editColumn('state', function ($row) {
                return $row->state ? $row->state : "";
            });
            $table->editColumn('city', function ($row) {
                return $row->city ? $row->city : "";
            });
            $table->addColumn('status_name', function ($row) {
                return $row->status ? $row->status->name : '';
            });
            $table->addColumn('status_color', function ($row) {
                return $row->status ? $row->status->color : '#000000';
            });

            $table->addColumn('priority_name', function ($row) {
                return $row->priority ? $row->priority->name : '';
            });
            $table->addColumn('priority_color', function ($row) {
                return $row->priority ? $row->priority->color : '#000000';
            });

            $table->addColumn('category', function ($row) {
                return $row->category ? $row->category : '';
            });
            $table->addColumn('assigned_to_user_name', function ($row) {
                return $row->assigned_to_user ? $row->assigned_to_user->name : '';
            });

            $table->addColumn('remark', function ($row) {
                return $row->remark ? $row->remark : '';
            });

            // $table->addColumn('comments_count', function ($row) {
            //     return $row->comments->count();
            // });

            $table->addColumn('view_link', function ($row) {
                return route('admin.tickets.show', $row->id);
            });

            $table->rawColumns(['actions', 'placeholder', 'status', 'priority', 'category', 'assigned_to_user']);

            return $table->make(true);
        }

        $priorities = Priority::all();
        $statuses = Status::all();
        $categories = Category::all();
        $users = User::select("users.*")->join("role_user","users.id","role_user.user_id")->where(["users.status"=>"active"])->where(["role_user.role_id"=>2])->get();
        return view('admin.tickets.index', compact('priorities', 'statuses', 'categories','users'));
    }

    public function create()
    {
        abort_if(Gate::denies('ticket_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $statuses = Status::where("id","!=",1)->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $priorities = Priority::all()->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $categories = Category::all()->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $assigned_to_users = User::whereHas('roles', function($query) {
                $query->whereId(2);
            })
            ->where(["users.status"=>"active"])
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        return view('admin.tickets.create', compact('statuses', 'priorities', 'categories', 'assigned_to_users'));
    }

    public function store(Request $request)
    {
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
        $ticket = new Ticket();
        $ticket->id = $reg_num;
        $ticket->customer_name = $request->customer_name;
        $ticket->customer_mobile = $request->customer_mobile;
        $ticket->customer_alternate_mobile = $request->customer_alternate_mobile;
        $ticket->address = strtolower($request->address);
        $ticket->state = $request->state;
        $ticket->city = strtolower($request->city);
        $ticket->pincode = $request->pincode;
        $ticket->model = $request->model;
        $ticket->category = $category;
        $ticket->status_id = $request->status_id;
        $ticket->priority_id = $request->priority_id;
        $ticket->assigned_to_user_id = $request->assigned_to_user_id;
        $ticket->product_warranty = $request->product_warranty;
        $ticket->created_by = Auth::user()->id;
        $ticket->save();

        $trimStr = ltrim($category,'Lock');
        $str = str_replace("_"," ", $trimStr);
        $category = ltrim($str);
        if($request->status_id == 2)
        {
            //Add customer
            if(!empty($ticket))
            {
                $agent = User::where(["id"=>$request->assigned_to_user_id])->first();
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
                        "agent_name" => @$agent->name,
                        "agent_contact" => @$agent->mobile,
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
                        "event"=> "Ticket Create",
                        "traits"=> array(
                            "ticket_id"=> $ticket->id,
                            "agent_name" => @$agent->name,
                            "agent_contact" => @$agent->mobile,
                            "issue" => $category,
                        )
                    );

                    $response = Http::withHeaders($headers)->post($eventApiURL, $postEventInput);
                    $responseBody = json_decode($response->getBody(), true);
                }
            }
            // Add agent
            if(!empty($ticket))
            {
                // dd("Hi Shubham Kumar");
                $agent = User::where(["id"=>$request->assigned_to_user_id])->first();
                $headers = array(
                    "Content-Type" => 'application/json',
                    "Authorization"=> 'Basic WmN1a0VfLUJEYmdEZXVnMHhVVlZfYVNueFdsaTE1Z2pHSk12M1pDSjA4QTo='
                );
                $apiURL = 'https://api.interakt.ai/v1/public/track/users/';
                $postInput = array(
                    "phoneNumber"=> $agent->mobile,
                    "countryCode"=> "+91",
                    "traits"=> array(
                        "name"=> $agent->name,
                        "phoneNumber"=> $agent->mobile,
                        "workLocation" => $agent->work_location,
                        "customerName" => $request->customer_name,
                        "customerMobile" => $request->customer_mobile,
                        "address" => $request->address,
                        "ticket_id"=> $ticket->id,
                        // "agent_name" => @$agent->name,
                        // "agent_contact" => @$agent->mobile,
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
                        "phoneNumber"=> $agent->mobile,
                        "countryCode"=> "+91",
                        "event"=> "Agent Notify",
                        "traits"=> array(
                            "ticket_id"=> $ticket->id,
                            // "agent_name" => @$agent->name,
                            // "agent_contact" => @$agent->mobile,
                            "customer_name" => $request->customer_name,
                            "customerMobile" => $request->customer_mobile,
                            "address" => $request->address,
                            "issue" => $category,
                        )
                    );

                    $response = Http::withHeaders($headers)->post($eventApiURL, $postEventInput);
                    $responseBody = json_decode($response->getBody(), true);
                }
            }
        }

        $ticketAuditLog = new  TicketAuditLog;
        $ticketAuditLog->ticket_id = $ticket->id;
        $ticketAuditLog->created_by = Auth::user()->id;
        $ticketAuditLog->updated_by = Auth::user()->id;
        $ticketAuditLog->status = $ticket->status_id;
        $ticketAuditLog->save();

        return redirect()->route('admin.tickets.index');
    }

    public function edit(Ticket $ticket)
    {
        abort_if(Gate::denies('ticket_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $statuses = Status::where("id","!=",1)->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $priorities = Priority::all()->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $categories = Category::all()->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $assigned_to_users = User::whereHas('roles', function($query) {
                $query->whereId(2);
            })
            ->where(["users.status"=>"active"])
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $ticket->load('status', 'priority', 'category', 'assigned_to_user');
        $category1 = DB::table("categories")->where(["parent"=>1])->get();
        $category2 = DB::table("categories")->where(["parent"=>2])->get();
        $category3 = DB::table("categories")->where(["parent"=>3])->get();

        return view('admin.tickets.edit', compact('statuses', 'priorities', 'categories', 'assigned_to_users', 'ticket'));
    }

    public function update(Request $request,$id)
    {
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
        // $ticket->update($request->all());
        $arr = [
            "customer_name" => $request->customer_name,
            "customer_mobile" => $request->customer_mobile,
            "customer_alternate_mobile" => $request->customer_alternate_mobile,
            "address" => strtolower($request->address),
            "state" => $request->state,
            "city" => strtolower($request->city),
            "pincode" => $request->pincode,
            "model" => $request->model,
            "category" => $category,
            "status_id" => $request->status_id,
            "priority_id" => $request->priority_id,
            "assigned_to_user_id" => $request->assigned_to_user_id,
            "product_warranty" => $request->product_warranty,
            "updated_by" => Auth::user()->id,
        ];

        $ticketAuditLog = new  TicketAuditLog;
        $ticketAuditLog->ticket_id = $id;
        $ticketAuditLog->created_by = Auth::user()->id;
        $ticketAuditLog->updated_by = Auth::user()->id;
        $ticketAuditLog->status = $request->status_id;
        $ticketAuditLog->save();

        $trimStr = ltrim($category,'Lock');
        $str = str_replace("_"," ", $trimStr);
        $category = ltrim($str);
        $ticket = Ticket::where(["id"=>$id])->first();
        if($ticket->status_id != $request->status_id)
        {
            if($request->status_id == 3 || $request->status_id == 5)
            {
                $status = Status::where(["id"=>$request->status_id])->first();
                if(!empty($ticket) )
                {
                    $agent = User::where(["id"=>$ticket->assigned_to_user_id])->first();
                    $headers = array(
                        "Content-Type" => 'application/json',
                        "Authorization"=> 'Basic WmN1a0VfLUJEYmdEZXVnMHhVVlZfYVNueFdsaTE1Z2pHSk12M1pDSjA4QTo='
                    );
                    $apiURL = 'https://api.interakt.ai/v1/public/track/users/';
                    $postInput = array(
                        "phoneNumber"=> $ticket->customer_mobile,
                        "countryCode"=> "+91",
                        "traits"=> array(
                            "name"=> $ticket->customer_name,
                            "ticket_id"=> $ticket->id,
                            "agent_name" => @$agent->name,
                            "agent_contact" => @$agent->mobile,
                            "status" => @$status->name,
                            "createdAt"=> date("Y-m-d"),
                        )
                    );
                    $response = Http::withHeaders($headers)->post($apiURL, $postInput);
                    $responseBody = json_decode($response->getBody(), true);
                    if($responseBody['result'])
                    {
                        $event = $request->status_id == 3 ? "Ticket Process" : "Hold";
                        $eventApiURL = 'https://api.interakt.ai/v1/public/track/events/';
                        $postEventInput = array(
                            "phoneNumber"=> $ticket->customer_mobile,
                            "countryCode"=> "+91",
                            "event"=> $event,
                            "traits"=> array(
                                "ticket_id"=> $ticket->id,
                                "agent_name" => @$agent->name,
                                "agent_contact" => @$agent->mobile,
                                "status" => @$status->name,
                            )
                        );

                        $response = Http::withHeaders($headers)->post($eventApiURL, $postEventInput);
                        $responseBody = json_decode($response->getBody(), true);
                    }
                }
            }
            if($request->status_id == 4)
            {
                $otp = random_int(100000, 999999);
                Ticket::where(['id'=>$id])->update(["status_id"=>$request->status_id,"otp"=>$otp]);
                // $ticket->load('status', 'priority', 'assigned_to_user');
                $statuses = Status::all();
                $ticket = Ticket::where(["id"=>$id])->first();
                if(!empty($ticket) )
                {
                    $status = Status::where(["id"=>$request->status_id])->first();
                    $agent = User::where(["id"=>$request->assigned_to_user_id])->first();
                    $headers = array(
                        "Content-Type" => 'application/json',
                        "Authorization"=> 'Basic WmN1a0VfLUJEYmdEZXVnMHhVVlZfYVNueFdsaTE1Z2pHSk12M1pDSjA4QTo='
                    );
                    $apiURL = 'https://api.interakt.ai/v1/public/track/users/';
                    $postInput = array(
                        "phoneNumber"=> $ticket->customer_mobile,
                        "countryCode"=> "+91",
                        "traits"=> array(
                            "name"=> $ticket->customer_name,
                            "ticket_id"=> $ticket->id,
                            "agent_name" => @$agent->name,
                            "agent_contact" => @$agent->mobile,
                            "status" => @$status->name,
                            "issue"  => $category,
                            "createdAt"=> date("Y-m-d"),
                            "otp" => $otp,
                        )
                    );
                    $response = Http::withHeaders($headers)->post($apiURL, $postInput);
                    $responseBody = json_decode($response->getBody(), true);
                    if($responseBody['result'])
                    {
                        $eventApiURL = 'https://api.interakt.ai/v1/public/track/events/';
                        $postEventInput = array(
                            "phoneNumber"=> $ticket->customer_mobile,
                            "countryCode"=> "+91",
                            "event"=> "Complete",
                            "traits"=> array(
                                "ticket_id"=> $ticket->id,
                                "agent_name" => @$agent->name,
                                "agent_contact" => @$agent->mobile,
                                "status" => @$status->name,
                                "issue"  => $category,
                                "otp" => $otp,
                            )
                        );

                        $response = Http::withHeaders($headers)->post($eventApiURL, $postEventInput);
                        $responseBody = json_decode($response->getBody(), true);
                    }
                }
                return view('admin.tickets.otp', compact('ticket','statuses'));
            }
        }
        if($ticket->assigned_to_user_id != $request->assigned_to_user_id)
        {
            // Add agent
            if(!empty($ticket))
            {
                $agent = User::where(["id"=>$request->assigned_to_user_id])->first();
                $headers = array(
                    "Content-Type" => 'application/json',
                    "Authorization"=> 'Basic WmN1a0VfLUJEYmdEZXVnMHhVVlZfYVNueFdsaTE1Z2pHSk12M1pDSjA4QTo='
                );
                $apiURL = 'https://api.interakt.ai/v1/public/track/users/';
                $postInput = array(
                    "phoneNumber"=> $agent->mobile,
                    "countryCode"=> "+91",
                    "traits"=> array(
                        "name"=> $agent->name,
                        "phoneNumber"=> $agent->mobile,
                        "workLocation" => $agent->work_location,
                        "customerName" => $request->customer_name,
                        "customerMobile" => $request->customer_mobile,
                        "address" => $request->address,
                        "ticket_id"=> $ticket->id,
                        // "agent_name" => @$agent->name,
                        // "agent_contact" => @$agent->mobile,
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
                        "phoneNumber"=> $agent->mobile,
                        "countryCode"=> "+91",
                        "event"=> "Agent Notify",
                        "traits"=> array(
                            "ticket_id"=> $ticket->id,
                            // "agent_name" => @$agent->name,
                            // "agent_contact" => @$agent->mobile,
                            "customer_name" => $request->customer_name,
                            "customerMobile" => $request->customer_mobile,
                            "address" => $request->address,
                            "issue" => $category,
                        )
                    );

                    $response = Http::withHeaders($headers)->post($eventApiURL, $postEventInput);
                    $responseBody = json_decode($response->getBody(), true);
                }
            }
        }
        Ticket::where(["id"=>$id])->update($arr);
        return redirect()->route('admin.tickets.index');
    }

    public function show(Ticket $ticket)
    {
        abort_if(Gate::denies('ticket_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $ticket->load('status', 'priority', 'assigned_to_user', 'comments');
        $statuses = Status::where("id","!=",1)->get();
        $ticket_history = TicketAuditLog::select('ticket_audit_logs.*','users.name','statuses.name as status')
            ->join('users','ticket_audit_logs.created_by','=','users.id')
            ->join('statuses','ticket_audit_logs.status','=','statuses.id')
            ->where(["ticket_audit_logs.ticket_id"=>$ticket->id])
            ->get();
        return view('admin.tickets.show', compact('ticket','statuses','ticket_history'));
    }

    public function destroy(Ticket $ticket)
    {
        abort_if(Gate::denies('ticket_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $ticket->delete();

        return back();
    }

    public function massDestroy(MassDestroyTicketRequest $request)
    {
        Ticket::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function storeComment(Request $request, Ticket $ticket)
    {
        $request->validate([
            'comment_text' => 'required'
        ]);
        $user = auth()->user();
        $comment = $ticket->comments()->create([
            'author_name'   => $user->name,
            'author_email'  => $user->email,
            'user_id'       => $user->id,
            'comment_text'  => $request->comment_text,
            'ticket_id'   => $ticket->id,
        ]);
        // dd("testing");
        $ticket = Ticket::where(["id"=>$request->ticket_id])->first();
        $trimStr = ltrim($ticket->category,'Lock');
        $str = str_replace("_"," ", $ticket->category);
        $category = ltrim($str);

        $ticketAuditLog = new  TicketAuditLog;
        $ticketAuditLog->ticket_id = $request->ticket_id;
        $ticketAuditLog->created_by = Auth::user()->id;
        $ticketAuditLog->updated_by = Auth::user()->id;
        $ticketAuditLog->status = $request->status;
        $ticketAuditLog->save();
        // dd($request->status,$ticket->status_id);
        if($ticket->status_id != $request->status)
        {

            if($request->status == 3 || $request->status == 5)
            {
                $status = Status::where(["id"=>$request->status_id])->first();
                if(!empty($ticket) )
                {
                    $agent = User::where(["id"=>$ticket->assigned_to_user_id])->first();
                    $headers = array(
                        "Content-Type" => 'application/json',
                        "Authorization"=> 'Basic WmN1a0VfLUJEYmdEZXVnMHhVVlZfYVNueFdsaTE1Z2pHSk12M1pDSjA4QTo='
                    );
                    $apiURL = 'https://api.interakt.ai/v1/public/track/users/';
                    $postInput = array(
                        "phoneNumber"=> $ticket->customer_mobile,
                        "countryCode"=> "+91",
                        "traits"=> array(
                            "name"=> $ticket->customer_name,
                            "ticket_id"=> $ticket->id,
                            "agent_name" => @$agent->name,
                            "agent_contact" => @$agent->mobile,
                            "status" => @$status->name,
                            "createdAt"=> date("Y-m-d"),
                        )
                    );
                    $response = Http::withHeaders($headers)->post($apiURL, $postInput);
                    $responseBody = json_decode($response->getBody(), true);
                    if($responseBody['result'])
                    {
                        $event = $request->status == 3 ? "Ticket Process" : "Hold";
                        $eventApiURL = 'https://api.interakt.ai/v1/public/track/events/';
                        $postEventInput = array(
                            "phoneNumber"=> $ticket->customer_mobile,
                            "countryCode"=> "+91",
                            "event"=> $event,
                            "traits"=> array(
                                "ticket_id"=> $ticket->id,
                                "agent_name" => @$agent->name,
                                "agent_contact" => @$agent->mobile,
                                "status" => @$status->name,
                            )
                        );

                        $response = Http::withHeaders($headers)->post($eventApiURL, $postEventInput);
                        $responseBody = json_decode($response->getBody(), true);
                    }
                }
            }
            if($request->status == 4)
            {
                $otp = random_int(100000, 999999);
                Ticket::where(['id'=>$request->ticket_id])->update(["status_id"=>$request->status,"otp"=>$otp]);
                $ticket->load('status', 'priority', 'assigned_to_user');
                $statuses = Status::where("id","!=",1)->get();
                $ticket = Ticket::where(["id"=>$request->ticket_id])->first();
                if(!empty($ticket) )
                {
                    $status = Status::where(["id"=>$request->status_id])->first();
                    $agent = User::where(["id"=>$ticket->assigned_to_user_id])->first();
                    $headers = array(
                        "Content-Type" => 'application/json',
                        "Authorization"=> 'Basic WmN1a0VfLUJEYmdEZXVnMHhVVlZfYVNueFdsaTE1Z2pHSk12M1pDSjA4QTo='
                    );
                    $apiURL = 'https://api.interakt.ai/v1/public/track/users/';
                    $postInput = array(
                        "phoneNumber"=> $ticket->customer_mobile,
                        "countryCode"=> "+91",
                        "traits"=> array(
                            "name"=> $ticket->customer_name,
                            "ticket_id"=> $ticket->id,
                            "agent_name" => @$agent->name,
                            "agent_contact" => @$agent->mobile,
                            "status" => @$status->name,
                            "issue"  => $category,
                            "createdAt"=> date("Y-m-d"),
                            "otp" => $otp,
                        )
                    );
                    $response = Http::withHeaders($headers)->post($apiURL, $postInput);
                    $responseBody = json_decode($response->getBody(), true);
                    if($responseBody['result'])
                    {
                        $eventApiURL = 'https://api.interakt.ai/v1/public/track/events/';
                        $postEventInput = array(
                            "phoneNumber"=> $ticket->customer_mobile,
                            "countryCode"=> "+91",
                            "event"=> "Complete",
                            "traits"=> array(
                                "ticket_id"=> $ticket->id,
                                "agent_name" => @$agent->name,
                                "agent_contact" => @$agent->mobile,
                                "status" => @$status->name,
                                "issue"  => $category,
                                "otp" => $otp,
                            )
                        );

                        $response = Http::withHeaders($headers)->post($eventApiURL, $postEventInput);
                        $responseBody = json_decode($response->getBody(), true);
                    }
                }
                Session::put('status','OTP has been sent on customer Whatsapp.');
                return view('admin.tickets.otp', compact('ticket','statuses'));
            }
        }
        Ticket::where(['id'=>$request->ticket_id])->update(["status_id"=>$request->status]);
        return redirect()->back()->withStatus('Your comment added successfully');
    }

    public function exportTicket(Request $request)
    {
        return Excel::download(new TicketExport($request->get('agent'),$request->get('status'),$request->get('priority'),$request->get('from'),$request->get('to')),'Tickets.xlsx');
    }

    public function ticketClose(Request $request)
    {
        $ticket = Ticket::where(['id'=>$request->ticket_id])->first();
        if($ticket->otp == $request->otp)
        {
            Ticket::where(['id'=>$request->ticket_id])->update(["status_id"=>6,"otp"=>null]);
            return redirect()->route('admin.tickets.index')->withStatus('Ticket is closed successfully');
        }
        else
        {
            $statuses = Status::where("id","!=",1)->get();
            $ticket = Ticket::where(["id"=>$request->ticket_id])->first();
            Session::put('status','Please enter correct OTP.');
            return view('admin.tickets.otp', compact('ticket','statuses'));
        }

    }
}
