@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('cruds.ticket.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.tickets.update", [$ticket->id]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group {{ $errors->has('customer_name') ? 'has-error' : '' }}">
                <label for="title">Customer Name <span style="color: red;">*</span></label>
                <input type="text" id="customer_name" name="customer_name" class="form-control" value="{{ old('customer_name', isset($ticket) ? $ticket->customer_name : '') }}" required>
                @if($errors->has('customer_name'))
                    <em class="invalid-feedback">
                        {{ $errors->first('customer_name') }}
                    </em>
                @endif
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group {{ $errors->has('customer_mobile') ? 'has-error' : '' }}">
                        <label for="title">Customer Mobile <span style="color: red;">*</span></label>
                        <input type="tel" maxlength="10" id="customer_mobile" name="customer_mobile" class="form-control" value="{{ old('customer_mobile', isset($ticket) ? $ticket->customer_mobile : '') }}" required>
                        @if($errors->has('customer_mobile'))
                            <em class="invalid-feedback">
                                {{ $errors->first('customer_mobile') }}
                            </em>
                        @endif
                        <p class="helper-block">
                            {{ trans('cruds.ticket.fields.title_helper') }}
                        </p>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group {{ $errors->has('customer_mobile') ? 'has-error' : '' }}">
                        <label for="title">Alternate Mobile (Optional)</label>
                        <input type="tel" maxlength="10" id="customer_alternate_mobile" name="customer_alternate_mobile" class="form-control" value="{{ old('customer_alternate_mobile', isset($ticket) ? $ticket->customer_alternate_mobile : '') }}">
                        @if($errors->has('customer_mobile'))
                            <em class="invalid-feedback">
                                {{ $errors->first('customer_mobile') }}
                            </em>
                        @endif
                        <p class="helper-block">
                            {{ trans('cruds.ticket.fields.title_helper') }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="form-group {{ $errors->has('state') ? 'has-error' : '' }}">
                <label for="priority">State <span style="color: red;">*</span></label>
                <select name="state" id="state" class="form-control select2" required>
                    <option value="">Select State</option>
                    <option value="Bihar" @if($ticket->state == "Bihar") selected @endif>Bihar</option>
                    <option value="UP" @if($ticket->state == "UP") selected @endif>UP</option>
                    <option value="Jharkhand" @if($ticket->state == "Jharkhand") selected @endif>Jharkhand</option>
                    <option value="West Bengal" @if($ticket->state == "West Bengal") selected @endif>West Bengal</option>
                    <option value="Odisha" @if($ticket->state == "Odisha") selected @endif>Odisha</option>
                </select>
                @if($errors->has('state'))
                    <em class="invalid-feedback">
                        {{ $errors->first('state') }}
                    </em>
                @endif
            </div>
            <div class="row">
                <div class="col-sm-6">
                        <div class="form-group {{ $errors->has('city') ? 'has-error' : '' }}">
                        <label for="priority">City <span style="color: red;">*</span></label>
                        <input type="text" name="city" class="form-control" value="{{ old('city', isset($ticket) ? strtoupper($ticket->city) : '') }}" required>
                        @if($errors->has('city'))
                            <em class="invalid-feedback">
                                {{ $errors->first('city') }}
                            </em>
                        @endif
                    </div>
                </div>
                <div class="col-sm-6">
                        <div class="form-group {{ $errors->has('pincode') ? 'has-error' : '' }}">
                        <label for="priority">Pincode <span style="color: red;"></span></label>
                        <input type="number" name="pincode" maxlength="6" class="form-control" value="{{ old('pincode', isset($ticket) ? $ticket->pincode : '') }}">
                        @if($errors->has('pincode'))
                            <em class="invalid-feedback">
                                {{ $errors->first('pincode') }}
                            </em>
                        @endif
                    </div>
                </div>
            </div>
            <div class="form-group {{ $errors->has('address') ? 'has-error' : '' }}">
                <label for="title">Address <span style="color: red;">*</span></label>
                <textarea type="text" id="address" name="address" class="form-control" required>{{ old('address', isset($ticket) ? strtoupper($ticket->address) : '') }}</textarea>
                @if($errors->has('address'))
                    <em class="invalid-feedback">
                        {{ $errors->first('address') }}
                    </em>
                @endif
            </div>
            <div class="form-group {{ $errors->has('product_warranty') ? 'has-error' : '' }}">
                <label for="product_warranty">Product Warranty<span style="color: red;">*</span></label>
                <select name="product_warranty" id="product_warranty" class="form-control" required>
                    <option value="">Select Warranty</option>
                    <option value="yes" @if($ticket->product_warranty == "yes") selected @endif>Yes</option>
                    <option value="no" @if($ticket->product_warranty == "no") selected @endif>No</option>
                </select>
                @if($errors->has('product_warranty'))
                    <em class="invalid-feedback">
                        {{ $errors->first('product_warranty') }}
                    </em>
                @endif
            </div>
            <div class="form-group {{ $errors->has('model') ? 'has-error' : '' }}">
                <label for="title">Model</label>
                <input type="text" id="model" name="model" class="form-control" value="{{ old('model', isset($ticket) ? $ticket->model : '') }}">
                @if($errors->has('model'))
                    <em class="invalid-feedback">
                        {{ $errors->first('model') }}
                    </em>
                @endif
            </div>
            <?php
            $data = explode(',',$ticket->category);
            //dd(($data));
            if(count($data) == 3)
            {
                $cate1 = explode('_',$data[0]);
                $cate2 = explode('_',$data[1]);
                $cate3 = explode('_',$data[2]);
                if($cate1[0] == "Lock"){
                    $category1 = $cate1[1];
                }
                if($cate2[0] == "Paint"){
                    $category2 = $cate2[1];
                }
                if($cate3[0] == "Rust"){
                    $category3 = $cate3[1];
                }
            }
            if(count($data) == 2)
            {
                $cate1 = explode('_',$data[0]);
                $cate2 = explode('_',$data[1]);
                //dd($cate);
                if($cate1[0] == "Lock"){
                    $category1 = $cate1[1];
                }
                if($cate2[0] == "Paint"){
                    $category2 = $cate2[1];
                }
                if($cate1[0] == "Paint"){
                    $category2 = $cate2[1];
                }
                if($cate1[0] == "Rust"){
                    $category3 = $cate1[1];
                }
            }
            if(count($data) == 1)
            {
                $cate = explode('_',$data[0]);
                if($cate[0] == "Lock"){
                    $category1 = $cate[1];
                }
                if($cate[0] == "Paint"){
                    $category2 = $cate[1];
                }
                if($cate[0] == "Rust"){
                    $category3 = $cate[1];
                }
            }

            ?>
            <div class="row">
                <div class="col-sm-4">
                    <label>Lock</label>
                    <select name="category1" id="category1" class="form-control select2">
                        <option value="">Please Select</option>
                        <option value="Main Lock" @if(@$category1 == "Main Lock") selected @endif>Main Lock</option>
                        <option value="Small Lock" @if(@$category1 == "Small Lock") selected @endif>Small Lock</option>
                    </select>
                </div>
                <div class="col-sm-4">
                    <label>Paint</label>
                    <select name="category2" id="category2" class="form-control select2">
                    <option value="">Please Select</option>
                    <option value="Brown" @if(@$category2 == "Brown") selected @endif>Brown</option>
                    <option value="Maroon" @if(@$category2 == "Maroon") selected @endif>Maroon</option>
                    <option value="Pink" @if(@$category2 == "Pink") selected @endif>Pink</option>
                    <option value="Purple" @if(@$category2 == "Purple") selected @endif>Purple</option>
                    <option value="Sky Blue" @if(@$category2 == "Sky Blue") selected @endif>Sky Blue</option>
                    <option value="White" @if(@$category2 == "White") selected @endif>White</option>
                    <option value="Ivory" @if(@$category2 == "Ivory") selected @endif>Ivory</option>
                    <option value="Olive" @if(@$category2 == "Olive") selected @endif>Olive</option>
                </select>
                </div>
                <div class="col-sm-4">
                    <label>Body</label>
                    <select name="category3" id="category3" class="form-control select2">
                    <option value="">Please Select</option>
                    <option value="Rust" @if(@$category3 == "Rust") selected @endif>Rust</option>
                </select>
                </div>
            </div>
            <div class="form-group {{ $errors->has('status_id') ? 'has-error' : '' }}">
                <label for="status">{{ trans('cruds.ticket.fields.status') }} <span style="color: red;">*</span></label>
                <select name="status_id" id="status" class="form-control select2" required>
                    @foreach($statuses as $id => $status)
                        <option value="{{ $id }}" {{ (isset($ticket) && $ticket->status ? $ticket->status->id : old('status_id')) == $id ? 'selected' : '' }}>{{ $status }}</option>
                    @endforeach
                </select>
                @if($errors->has('status_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('status_id') }}
                    </em>
                @endif
            </div>
            <div class="form-group {{ $errors->has('priority_id') ? 'has-error' : '' }}">
                <label for="priority">{{ trans('cruds.ticket.fields.priority') }} <span style="color: red;">*</span></label>
                <select name="priority_id" id="priority" class="form-control select2" required>
                    @foreach($priorities as $id => $priority)
                        <option value="{{ $id }}" {{ (isset($ticket) && $ticket->priority ? $ticket->priority->id : old('priority_id')) == $id ? 'selected' : '' }}>{{ $priority }}</option>
                    @endforeach
                </select>
                @if($errors->has('priority_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('priority_id') }}
                    </em>
                @endif
            </div>
            {{-- @if(auth()->user()->isAdmin()) --}}
                <div class="form-group {{ $errors->has('assigned_to_user_id') ? 'has-error' : '' }}">
                    <label for="assigned_to_user">{{ trans('cruds.ticket.fields.assigned_to_user') }} <span style="color: red;">*</span></label>
                    <select name="assigned_to_user_id" id="assigned_to_user" class="form-control select2" required>
                        @foreach($assigned_to_users as $id => $assigned_to_user)
                            <option value="{{ $id }}" {{ (isset($ticket) && $ticket->assigned_to_user ? $ticket->assigned_to_user->id : old('assigned_to_user_id')) == $id ? 'selected' : '' }}>{{ $assigned_to_user }}</option>
                        @endforeach
                    </select>
                    @if($errors->has('assigned_to_user_id'))
                        <em class="invalid-feedback">
                            {{ $errors->first('assigned_to_user_id') }}
                        </em>
                    @endif
                </div>
            {{-- @endif --}}
            <div>
                <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
            </div>
        </form>


    </div>
</div>
@endsection

@section('scripts')
<script>
    var uploadedAttachmentsMap = {}
Dropzone.options.attachmentsDropzone = {
    url: '{{ route('admin.tickets.storeMedia') }}',
    maxFilesize: 2, // MB
    addRemoveLinks: true,
    headers: {
      'X-CSRF-TOKEN': "{{ csrf_token() }}"
    },
    params: {
      size: 2
    },
    success: function (file, response) {
      $('form').append('<input type="hidden" name="attachments[]" value="' + response.name + '">')
      uploadedAttachmentsMap[file.name] = response.name
    },
    removedfile: function (file) {
      file.previewElement.remove()
      var name = ''
      if (typeof file.file_name !== 'undefined') {
        name = file.file_name
      } else {
        name = uploadedAttachmentsMap[file.name]
      }
      $('form').find('input[name="attachments[]"][value="' + name + '"]').remove()
    },
    init: function () {
@if(isset($ticket) && $ticket->attachments)
          var files =
            {!! json_encode($ticket->attachments) !!}
              for (var i in files) {
              var file = files[i]
              this.options.addedfile.call(this, file)
              file.previewElement.classList.add('dz-complete')
              $('form').append('<input type="hidden" name="attachments[]" value="' + file.file_name + '">')
            }
@endif
    },
     error: function (file, response) {
         if ($.type(response) === 'string') {
             var message = response //dropzone sends it's own error messages in string
         } else {
             var message = response.errors.file
         }
         file.previewElement.classList.add('dz-error')
         _ref = file.previewElement.querySelectorAll('[data-dz-errormessage]')
         _results = []
         for (_i = 0, _len = _ref.length; _i < _len; _i++) {
             node = _ref[_i]
             _results.push(node.textContent = message)
         }

         return _results
     }
}
</script>
@stop
