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
                    <!-- <tr>
                        <th>
                            {{ trans('cruds.ticket.fields.title') }}
                        </th>
                        <td>
                            {{ $ticket->title }}
                        </td>
                    </tr> -->
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
                    <!-- <tr>
                        <th>
                            {{ trans('cruds.ticket.fields.content') }}
                        </th>
                        <td>
                            {!! $ticket->content !!}
                        </td>
                    </tr> -->
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
                    <!-- <tr>
                        <th>
                            {{ trans('cruds.ticket.fields.author_name') }}
                        </th>
                        <td>
                            {{ $ticket->author_name }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.ticket.fields.author_email') }}
                        </th>
                        <td>
                            {{ $ticket->author_email }}
                        </td>
                    </tr> -->
                    <tr>
                        <th>
                            {{ trans('cruds.ticket.fields.assigned_to_user') }}
                        </th>
                        <td>
                            {{ $ticket->assigned_to_user->name ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                        <div class="form-group ">
                                <label>Select Status</label>
                                <select name="status" class="form-control">
                                    <option value="">Select Status</option>
                                    @foreach($statuses as $status)
                                        <option value="{{$status->id}}">{{$status->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </td>
                    </tr>
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
                            <form action="{{ route('admin.tickets.storeComment', $ticket->id) }}" method="POST">
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
