@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">Add User</div>
                <div class="panel-body">
                    <form class="form-horizontal" role="form" method="POST" action="{{ url('/adduser') }}">
                        {!! csrf_field() !!}

                        <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">Name</label>

                            <div class="col-md-6">
                                <input type="text" class="form-control" name="name" value="{{ old('name') }}">

                                @if ($errors->has('name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">E-Mail Address</label>

                            <div class="col-md-6">
                                <input type="email" class="form-control" name="email" value="{{ old('email') }}">

                                @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('admin') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">Role</label>

                            <div class="col-md-6">
                               
                                {!! Form::select('admin', ['User','Admin'], [0], ['class' => 'form-control']) !!}
                                @if ($errors->has('admin'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('admin') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('farmbooks') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">Farmbooks</label>

                            <div class="col-md-6">
                               
                                {!! Form::select('farmbooks[]', $farmbooks,[0], ['multiple'=>'multiple','class'=> 'form-control col-md-6', 'id'=>'farmbooks']) !!}
                                @if ($errors->has('farmbooks'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('farmbooks') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>



                        <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">

                           

                            <label class="col-md-4 control-label">Password</label>

                            <div class="col-md-6">
                                <input type="password" class="form-control" name="password" id="password">
                                <span id="password_strength"></span>
                                  <a href="#" data-toggle="tooltip" title="Use more than 8 characters, uppercase, lowercase, numbers and special characters.">Hint</a>
                                @if ($errors->has('password'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">Confirm Password</label>

                            <div class="col-md-6">
                                <input type="password" class="form-control" name="password_confirmation">

                                @if ($errors->has('password_confirmation'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password_confirmation') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-btn fa-user"></i>Add User
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

   <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
    <script type="text/javascript">


$(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();   
});


        $(function () {
            $("#password").bind("keyup", function () {
                //TextBox left blank.
                if ($(this).val().length == 0) {
                    $("#password_strength").html("");
                    return;
                }

                //Regular Expressions.
                var regex = new Array();
                regex.push("[A-Z]"); //Uppercase Alphabet.
                regex.push("[a-z]"); //Lowercase Alphabet.
                regex.push("[0-9]"); //Digit.
                regex.push("[$@$!%*#?&]"); //Special Character.

                var passed = 0;

                //Validate for each Regular Expression.
                for (var i = 0; i < regex.length; i++) {
                    if (new RegExp(regex[i]).test($(this).val())) {
                        passed++;
                    }
                }


                //Validate for length of Password.
                if (passed > 2 && $(this).val().length > 7) {
                    passed++;
                }

                //Display status.
                var color = "";
                var strength = "";
                switch (passed) {
                    case 0:
                    case 1:
                        strength = "Weak";
                        color = "red";
                        break;
                    case 2:
                        strength = "Good";
                        color = "orange";
                        break;
                    case 3:
 
                    case 4:
                        strength = "Strong";
                        color = "green";
                        break;
                    case 5:
                        strength = "Very Strong";
                        color = "darkgreen";
                        break;
                }
                $("#password_strength").html(strength);
                $("#password_strength").css("color", color);
            });
        });
    </script>




