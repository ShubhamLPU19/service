<!DOCTYPE html>
<html lang="en">
<head>
  <title>Bootstrap Example</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
  <style>
    .container {
        background-color: beige
    }
  </style>
</head>
<body>
<div class="container">
<div class="page-header justify-content-between">
  <h1>Sangam Complaint System</h1>
</div>
<div>
<div class="container">
    <div class="card">
        @if(session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
        @endif
        <div class="card-body">
            <form action="{{ route("storeComplaint") }}" method="POST" onSubmit="return confirm('Please verify the given data.') " enctype="multipart/form-data">
                @csrf
                <div class="form-group {{ $errors->has('customer_name') ? 'has-error' : '' }}">
                    <label for="title">Customer Name <span style="color: red;">*</span></label>
                    <input type="text" id="customer_name" name="customer_name" class="form-control" value="{{ old('customer_name', isset($ticket) ? $ticket->customer_name : '') }}" maxlength="60" required>
                    @if($errors->has('customer_name'))
                        <em class="invalid-feedback">
                            {{ $errors->first('customer_name') }}
                        </em>
                    @endif
                </div>
                <div class="form-group {{ $errors->has('customer_mobile') ? 'has-error' : '' }}">
                    <label for="title">Customer Whatsapp Number <span style="color: red;">*</span></label>
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
                            <div class="form-group {{ $errors->has('state') ? 'has-error' : '' }}">
                    <label for="priority">State <span style="color: red;">*</span></label>
                    <select name="state" id="state" class="form-control select2" required>
                        <option value="">Select State</option>
                        <option value="Bihar">Bihar</option>
                        <option value="UP">UP</option>
                        <option value="Jharkhand">Jharkhand</option>
                        <option value="West Bengal">West Bengal</option>
                        <option value="Odisha">Odisha</option>
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
                            <input type="text" name="city" class="form-control" maxlength="40" required>
                            @if($errors->has('city'))
                                <em class="invalid-feedback">
                                    {{ $errors->first('city') }}
                                </em>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-6">
                            <div class="form-group {{ $errors->has('pincode') ? 'has-error' : '' }}">
                            <label for="priority">Pincode <span style="color: red;">*</span></label>
                            <input type="tel" maxlength="6" name="pincode" class="form-control" required>
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
                    <textarea type="text" id="address" name="address" class="form-control" required></textarea>
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
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                    </select>
                    @if($errors->has('product_warranty'))
                        <em class="invalid-feedback">
                            {{ $errors->first('product_warranty') }}
                        </em>
                    @endif
                </div>
                <div class="form-group {{ $errors->has('model') ? 'has-error' : '' }}">
                    <label for="title">Model </label>
                    <input type="text" id="model" name="model" maxlength="20" class="form-control" value="{{ old('model', isset($ticket) ? $ticket->model : '') }}">
                    @if($errors->has('model'))
                        <em class="invalid-feedback">
                            {{ $errors->first('model') }}
                        </em>
                    @endif
                </div>
                <div class="row">
                    <div class="col-sm-4">
                        <label><strong>Lock</strong></label>
                        <select name="category1" id="category1" class="form-control select2">
                            <option value="">Please Select</option>
                            <option value="Main Lock">Main Lock</option>
                            <option value="Small Lock">Small Lock</option>
                    </select>
                    </div>
                    <div class="col-sm-4">
                        <label><strong>Paint</strong></label>
                        <select name="category2" id="category2" class="form-control select2">
                        <option value="">Please Select</option>
                        <option value="Brown">Brown</option>
                        <option value="Maroon">Maroon</option>
                        <option value="Pink">Pink</option>
                        <option value="Purple">Purple</option>
                        <option value="Sky Blue">Sky Blue</option>
                        <option value="White">White</option>
                        <option value="Ivory">Ivory</option>
                        <option value="Olive">Olive</option>
                    </select>
                    </div>
                    <div class="col-sm-4">
                        <label><strong>Body</strong></label>
                        <select name="category3" id="category3" class="form-control select2">
                        <option value="">Please Select</option>
                        <option value="Rust">Rust</option>
                    </select>
                    </div>
                </div>
                <br>
                <div class="row">
                    <span>You can only register one complaint with one mobile number untill it closed.</span>
                </div>
                <div class="row">
                    <center>
                    <input type="checkbox" id="check" name="validate" value="1" required>
                    <label for="vehicle1">Please check the above data is correct.</label>
                    </center>
                </div>
                <br>
                <div>
                    <center>
                        <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
                    </center>
                </div>
            </form>


        </div>
    </div>
</div>
</body>
</html>
