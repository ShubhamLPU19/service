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
                            {{ trans('cruds.ticket.fields.id') }}
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
                            {{ $ticket->created_at }}
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
                    <tr>
                        <th>
                            {{ trans('cruds.ticket.fields.category') }}
                        </th>
                        <td>
                            {{ $ticket->category ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.ticket.fields.assigned_to_user') }}
                        </th>
                        <td>
                            {{ $ticket->assigned_to_user->name ?? '' }}
                        </td>
                    </tr>
                    <form action="{{ route('admin.tickets.ticketClose') }}" method="POST">
                    @csrf
                    <input type="hidden" name="ticket_id" value="{{$ticket->id}}">
                    @if(auth()->user()->isAgent())
                    <tr>
                        <th>Status</th>
                        <td>
                        <div class="form-group ">
                                <label>Select Status</label>
                                <select name="status" class="form-control" disabled="true">
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
                    <tr>
                        <th>OTP</th>
                        <td>
                        <div class="form-group ">
                                <input type="tel" maxlength="6" name="otp" class="form-control" required>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th></th>
                        <td>
                            <button type="submit" class="btn btn-primary">@lang('global.submit')</button>
                        </td>
                    </tr>
                    </form>
                </tbody>
            </table>
        </div>
        <a class="btn btn-default my-2" href="{{ route('admin.tickets.index') }}">
            {{ trans('global.back_to_list') }}
        </a>
        <nav class="mb-3">
            <div class="nav nav-tabs">

            </div>
        </nav>
    </div>
</div>
@endsection
