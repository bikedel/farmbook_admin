@extends('layouts.app')
<style>
body {
font-family: 'Lato';
}
.tlabel{
color:#000000;
font-weight: 900;
border-style: none;
border-color: Transparent;
background-color: #f2f2f2
}
textarea{
border-style: none;
border-color: Transparent;
padding:0;
overflow: auto;
width: 100%;
-webkit-box-sizing: border-box; /* <=iOS4, <= Android  2.3 */
-moz-box-sizing: border-box; /* FF1+ */
box-sizing: border-box; /* Chrome, IE8, Opera, Safari 5.1*/
}
input[ type=text ]{
border-style: none;
border-color: Transparent;
padding:5;
width: 100%;
}
.records{
padding:0px;
color:white;
margin-right: 6px;
font-weight:900;
float:right;
}
.street{
color:darkblue;
font-weight: 900;
}
.update{
float:right;
}
.id {
border-color: Transparent;
border:none;
}
.fa-btn {
margin-right: 6px;
}
</style>
@section('content')
<div class="container">
  <div class="row">
    <div class="col-md-10 col-md-offset-1">
      <div class="panel panel-primary">
        <div class="panel-heading">Image Upload
        </div>
        @if ( Session::has('flash_message') )
        <div class="alert {{ Session::get('flash_type') }} ">
          <button type="button" class="form-group btn close" data-dismiss="alert" aria-hidden="true">&times;</button>
          <p>{{ Session::get('flash_message') }}</p>
        </div>
        @endif
        <div class="panel-body">
          <div class="about-section">
            <div class="text-content">
              <div class="span7 offset1">
                @if(Session::has('success'))
                <div class="alert-box success">
                  <h2>{!! Session::get('success') !!}</h2>
                </div>
                @endif
                <div class="secure">Upload form</div>
                {!! Form::open(array('url'=>'uploadimage','method'=>'POST', 'files'=>true)) !!}
                <div class="control-group">
                  <div class="controls">
                    {!! Form::file('image') !!}
                    <p class="errors">{!!$errors->first('image')!!}</p>
                    @if(Session::has('error'))
                    <p class="errors">{!! Session::get('error') !!}</p>
                    @endif
                  </div>
                </div>
                <div id="success"> </div>
                {!! Form::submit('Submit', array('class'=>'send-btn')) !!}
                {!! Form::close() !!}
              </div>
            </div>
          </div>
          <div class=' update'>
            <br>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

@Endsection
<script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
<script >
$(document).on("ready page:load", function() {
setTimeout(function() { $(".alert").fadeOut(); }, 4000);
});
</script>
