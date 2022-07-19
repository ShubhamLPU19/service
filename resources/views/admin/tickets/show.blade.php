@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.ticket.title') }}
    </div>

    <div class="card-body">
        @if(session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @endif
        <div class="mb-2">
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>
                            Ticket Id
                        </th>
                        <td>
                            {{ $ticket->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.ticket.fields.created_at') }}
                        </th>
                        <td>
                            {{ date('l d F Y h:i A',strtotime($ticket->created_at))}}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            Customer Name
                        </th>
                        <td>
                            {{ $ticket->customer_name }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            Customer Mobile
                        </th>
                        <td>
                            {{ $ticket->customer_mobile }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            Alternate Mobile
                        </th>
                        <td>
                            {{ $ticket->customer_alternate_mobile }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            Product Warranty
                        </th>
                        <td>
                            {{ strtoupper($ticket->product_warranty) }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            State
                        </th>
                        <td>
                            {{$ticket->state}}
                        </td>
                    </tr>
                     <tr>
                        <th>
                            City
                        </th>
                        <td>
                            {{strtoupper($ticket->city)}}
                        </td>
                    </tr>
                     <tr>
                        <th>
                            Address
                        </th>
                        <td>
                            {{strtoupper($ticket->address)}}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.ticket.fields.status') }}
                        </th>
                        <td>
                            {{ $ticket->status->name ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.ticket.fields.priority') }}
                        </th>
                        <td>
                            {{ $ticket->priority->name ?? '' }}
                        </td>
                    </tr>
                    @php
                        $trimStr = ltrim($ticket->category,'Lock');
                        $str = str_replace("_"," ", $trimStr);
                        $category = ltrim($str);
                    @endphp
                    <tr>
                        <th>
                            Issue
                        </th>
                        <td>
                            {{ $category ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            Assigned To Agent
                        </th>
                        <td>
                            {{ $ticket->assigned_to_user->name ?? '' }}
                        </td>
                    </tr>
                    <form action="{{ route('admin.tickets.storeComment', $ticket->id) }}" method="POST">
                        <input type="hidden" name="ticket_id" value="{{$ticket->id}}">
                    @if(auth()->user()->isAgent())
                    <tr>
                        <th>Status</th>
                        <td>
                        <div class="form-group ">
                                <label>Select Status</label>
                                <select name="status" class="form-control">
                                    <option value="">Select Status</option>
                                        <option value="3" {{$ticket->status_id == 3  ? 'selected' : ''}}>Process</option>
                                        <option value="5" {{$ticket->status_id == 5  ? 'selected' : ''}}>On Hold</option>
                                        <option value="4" {{$ticket->status_id == 4  ? 'selected' : ''}}>Complete</option>
                                </select>
                            </div>
                        </td>
                    </tr>
                    @else
                    <tr>
                        <th>Status</th>
                        <td>
                        <div class="form-group ">
                                <label>Select Status</label>
                                <select name="status" class="form-control">
                                    <option value="">Select Status</option>
                                    @foreach($statuses as $status)
                                        <option value="{{$status->id}}" {{$ticket->status_id == $status->id  ? 'selected' : ''}}>{{$status->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </td>
                    </tr>
                    @endif
                    {{-- <tr>
                        <th>Remark</th>
                        <td>
                        <div class="form-group ">
                                <textarea type="text" name="remark" rows="3"> {{$ticket->remark}}</textarea>
                            </div>
                        </td>
                    </tr> --}}
                    <tr>
                        <th>
                            {{ trans('cruds.ticket.fields.comments') }}
                        </th>
                        <td>
                            @forelse ($ticket->comments as $comment)
                                <div class="row">
                                    <div class="col">
                                        <p class="font-weight-bold"><a href="mailto:{{ $comment->author_email }}">{{ $comment->author_name }}</a> ({{ $comment->created_at }})</p>
                                        <p>{{ $comment->comment_text }}</p>
                                    </div>
                                </div>
                                <hr />
                            @empty
                                <div class="row">
                                    <div class="col">
                                        <p>There are no comments.</p>
                                    </div>
                                </div>
                                <hr />
                            @endforelse
                            {{-- <form action="{{ route('admin.tickets.storeComment', $ticket->id) }}" method="POST">  --}}
                                @csrf
                                <div class="form-group">
                                    <label for="comment_text">Leave a comment</label>
                                    <textarea class="form-control" id="comment_text" name="comment_text" rows="3" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">@lang('global.submit')</button>
                            </form>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        @if(auth()->user()->isAdmin() || auth()->user()->isExecutive())
        <table class="table table-hover">
            <thead>
                <tr>
                <th scope="col">Ticket Id</th>
                <th scope="col">Action Performed By</th>
                <th scope="col">Status</th>
                <th scope="col">Created At</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ticket_history as $history)
                <tr>
                    <td>{{$history->ticket_id}}</td>
                    <td>{{$history->name}}</td>
                    <td>{{$history->status}}</td>
                    <td>{{date('l d F Y h:i A',strtotime($history->created_at))}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
        <a class="btn btn-default my-2" href="{{ route('admin.tickets.index') }}">
            {{ trans('global.back_to_list') }}
        </a>

        <a href="{{ route('admin.tickets.edit', $ticket->id) }}" class="btn btn-primary">
            @lang('global.edit') @lang('cruds.ticket.title_singular')
        </a>

        <nav class="mb-3">
            <div class="nav nav-tabs">

            </div>
        </nav>
    </div>
</div>
@endsection
