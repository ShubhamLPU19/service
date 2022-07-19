@extends('layouts.admin')
@section('content')
@can('ticket_create')
    {{-- <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route("admin.tickets.create") }}">
                {{ trans('global.add') }} {{ trans('cruds.ticket.title_singular') }}
            </a>
        </div>
    </div>  --}}
@endcan
@if(auth()->user()->isAdmin() || auth()->user()->isExecutive())
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                <form action="{{route('admin.tickets.export')}}" method="post">
                    @csrf
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group ">
                                <label>Select Agent</label>
                                <select name="agent" class="form-control">
                                    <option value="">All Agents</option>
                                    @foreach($users as $user)
                                        <option value="{{$user->id}}">{{$user->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group ">
                                <label>Select Status</label>
                                <select name="status" class="form-control">
                                    <option value="">All Statuses</option>
                                    @foreach($statuses as $status)
                                        <option value="{{$status->id}}">{{$status->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group ">
                                <label>Select Priority</label>
                                <select name="priority" class="form-control">
                                    <option value="">All Priorities</option>
                                    @foreach($priorities as $priority)
                                        <option value="{{$priority->id}}">{{$priority->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group ">
                                <label>From Date</label>
                                <input type="date" name="from" class="form-control" value="" placeholder="From Date">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>To Date</label>
                                <input type="date" name="to" class="form-control" value="" placeholder="To Date">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label></label>
                            <div class="form-group button-group p-t-15" style="margin-top: 5px;">
                                <button type="submit" class="btn btn-info" id="export" style="width: 110px;">Export Ticket</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@if(session('status'))
    <div class="alert alert-success" role="alert">
        {{ session('status') }}
    </div>
@endif
<div class="card">
    <div class="card-header">
        {{ trans('cruds.ticket.title_singular') }} {{ trans('global.list') }}
         @if(auth()->user()->isAdmin() || auth()->user()->isExecutive())
        <a class="btn btn-success" style="float:right;" href="{{ route("admin.tickets.create") }}">
            {{ trans('global.add') }} {{ trans('cruds.ticket.title_singular') }}
        </a>
        @endif
    </div>
    <div class="card-body">
        <table class=" table table-bordered table-striped table-hover ajaxTable datatable datatable-Ticket">
            <thead>
                <tr>
                    <th width="10">

                    </th>
                    <th>
                        {{ trans('cruds.ticket.fields.id') }}
                    </th>
                    <th>
                        State
                    </th>
                    <th>
                        City
                    </th>
                    <th>
                        Address
                    </th>
                    <th>
                        {{ trans('cruds.ticket.fields.status') }}
                    </th>
                    {{-- <th>
                        {{ trans('cruds.ticket.fields.priority') }}
                    </th> --}}
                    <th>
                        Issue
                    </th>
                    <th>
                       Customer Name
                    </th>
                    <th>
                        Customer Mobile
                    </th>
                    <th>
                        Assigned To Agent
                    </th>
                    <th>
                        Created At
                    </th>
                    <th>
                        {{-- &nbsp; --}}
                        Action
                    </th>
                </tr>
            </thead>
        </table>


    </div>
</div>
@endsection
@section('scripts')
@parent
<script>
    $(function () {
let filters = `
<form class="form-inline" id="filtersForm">
  <div class="form-group mx-sm-3 mb-2">
    <select class="form-control" name="status">
      <option value="">All statuses</option>
      @foreach($statuses as $status)
        <option value="{{ $status->id }}"{{ request('status') == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
      @endforeach
    </select>
  </div>
  <div class="form-group mx-sm-3 mb-2">
    <select class="form-control" name="priority">
      <option value="">All priorities</option>
      @foreach($priorities as $priority)
        <option value="{{ $priority->id }}"{{ request('priority') == $priority->id ? 'selected' : '' }}>{{ $priority->name }}</option>
      @endforeach
    </select>
  </div>
  <div class="form-group mx-sm-3 mb-2">
    <select class="form-control" name="assigned_to_user_id">
      <option value="">All Agents</option>
      @foreach($users as $user)
        <option value="{{ $user->id }}"{{ request('assigned_to_user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
      @endforeach
    </select>
  </div>
</form>`;
$('.card-body').on('change', 'select', function() {
  $('#filtersForm').submit();
})
  let dtButtons = []
@can('ticket_delete')
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}';
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.tickets.massDestroy') }}",
    className: 'btn-danger',
    action: function (e, dt, node, config) {
      var ids = $.map(dt.rows({ selected: true }).data(), function (entry) {
          return entry.id
      });

      if (ids.length === 0) {
        alert('{{ trans('global.datatables.zero_selected') }}')

        return
      }

      if (confirm('{{ trans('global.areYouSure') }}')) {
        $.ajax({
          headers: {'x-csrf-token': _token},
          method: 'POST',
          url: config.url,
          data: { ids: ids, _method: 'DELETE' }})
          .done(function () { location.reload() })
      }
    }
  }
  dtButtons.push(deleteButton)
@endcan
  let searchParams = new URLSearchParams(window.location.search)
  let dtOverrideGlobals = {
    buttons: dtButtons,
    processing: true,
    serverSide: true,
    retrieve: true,
    aaSorting: [],
    ajax: {
      url: "{{ route('admin.tickets.index') }}",
      data: {
        'status': searchParams.get('status'),
        'priority': searchParams.get('priority'),
        'assigned_to_user_id': searchParams.get('assigned_to_user_id')
      }
    },
    columns: [
      { data: 'placeholder', name: 'placeholder' },
      { data: 'id', name: 'id' },
{
    data: 'state',
    name: 'state',
},
{
    data: 'city',
    name: 'city',
},
{
    data: 'address',
    name: 'address',
},

{
  data: 'status_name',
  name: 'status.name',
  render: function ( data, type, row) {
      return '<span style="color:'+row.status_color+'">'+data+'</span>';
  }
},
//{
  //data: 'priority_name',
  //name: 'priority.name',
  //render: function ( data, type, row) {
    //  return '<span style="color:'+row.priority_color+'">'+data+'</span>';
  //}
//},
{
  data: 'category',
  name: 'category',
  render: function ( data, type, row) {
      return '<span style="color:'+row.category_color+'">'+data+'</span>';
  }
},
{ data: 'customer_name', name: 'customer_name' },
{ data: 'customer_mobile', name: 'customer_mobile' },
{ data: 'assigned_to_user_name', name: 'assigned_to_user.name' },
{ data: 'created_at', name: 'created_at' },
{ data: 'actions', name: '{{ trans('global.actions') }}' }
    ],
    order: [[ 1, 'desc' ]],
    pageLength: 100,
  };
$(".datatable-Ticket").one("preInit.dt", function () {
 $(".dataTables_filter").after(filters);
});
  $('.datatable-Ticket').DataTable(dtOverrideGlobals);
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
        $($.fn.dataTable.tables(true)).DataTable()
            .columns.adjust();
    });
});
</script>
@endsection
