@extends('layouts.app')

@section('content')


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
.btn{float:right;}

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
.form-group{

	padding:5;
}

</style>



<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="panel panel-primary">
				<div class="panel-heading"> Import from SAPTG
				</div>



				<div class="panel-body">

					@if ( Session::has('flash_message') )

					<div class="row alert {{ Session::get('flash_type') }} ">
						<button type="button" class="form-group btn btn-info close" data-dismiss="alert" aria-hidden="true">&times;</button>
						<p>{{ Session::get('flash_message') }}</p>
					</div>

					@endif



					{!! Form::close() !!}
					{!! Form::open([ 'url' => 'import', 'method' => 'post', 'files' => 'true','onsubmit' => 'return ConfirmDelete()']) !!}

					<div class="form-group">
						{{ Form::label('text','SAPTG csv ',array('id'=>'','class'=>'')) }}
						{!! Form::file('csv_import', ['class' => 'csv_import form-control input-sm']) !!}
					</div>
					<div class="form-group">

						{!! Form::submit('Import',  array('class'=>'btn btn-success ')) !!}
					</div>
					{!! Form::close() !!}

				</div>
			</div>
		</div>
	</div>
</div>

<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="panel panel-primary">
				<div class="panel-heading">Update from SAPTG - FH ready for testing - ST not implemented yet.
				</div>
				<div class="panel-body">

					@if ( Session::has('flash_message') )

					<div class="row alert {{ Session::get('flash_type') }} ">
						<button type="button" class="form-group btn btn-info close" data-dismiss="alert" aria-hidden="true">&times;</button>
						<p>{{ Session::get('flash_message') }}</p>
					</div>

					@endif

					{!! Form::open([ 'url' => 'update', 'method' => 'post', 'files' => 'true','onsubmit' => 'return ConfirmDelete()']) !!}


					<div class="form-group">
						{{ Form::label('text','SAPTG csv ',array('id'=>'','class'=>'')) }}
						{!! Form::file('csv_update', ['class' => 'csv_import form-control input-sm']) !!}
					</div>


					<div class="form-group">

						{!! Form::submit('Update',  array('class'=>'btn btn-success ')) !!}
					</div>
					{!! Form::close() !!}
				</div>
			</div>
		</div>
	</div>
</div>

<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="panel panel-primary">
				<div class="panel-heading"> Create Database
				</div>
				<div class="panel-body">

					@if ( Session::has('flash_message') )

					<div class="row alert {{ Session::get('flash_type') }} ">
						<button type="button" class="form-group btn btn-info close" data-dismiss="alert" aria-hidden="true">&times;</button>
						<p>{{ Session::get('flash_message') }}</p>
					</div>

					@endif

					{!! Form::open([ 'url' => 'createdatabase', 'method' => 'post', 'files' => 'true','onsubmit' => 'return ConfirmDelete()']) !!}
					<div class="form-group">
                        {{ Form::label('text','Suburb Name ',array('id'=>'','class'=>'')) }}
						{{ Form::text('database') }}
					</div>

					<div class="form-group">
						{{ Form::label('text','Select Type ',array('id'=>'','class'=>'')) }}
						{!! Form::select('type', $type,null, ['class'=> 'form-control input-sm', 'id'=>'type']) !!}
					</div>
					<div class="form-group">

						{!! Form::submit('Create',  array('class'=>'btn btn-danger ')) !!}
					</div>
					{!! Form::close() !!}
				</div>
			</div>
		</div>
	</div>
</div>


<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="panel panel-primary">
				<div class="panel-heading"> Delete Database
				</div>
				<div class="panel-body">

					@if ( Session::has('flash_message') )

					<div class="row alert {{ Session::get('flash_type') }} ">
						<button type="button" class="form-group btn btn-info close" data-dismiss="alert" aria-hidden="true">&times;</button>
						<p>{{ Session::get('flash_message') }}</p>
					</div>

					@endif

					{!! Form::open([ 'url' => 'deletedatabase', 'method' => 'post', 'files' => 'true','onsubmit' => 'return ConfirmDelete()']) !!}
					<div class="form-group">
					</div>

					<div class="form-group">
						{{ Form::label('text','Delete Database ',array('id'=>'','class'=>'')) }}
						{!! Form::select('database', $data,null, ['class'=> 'form-control input-sm', 'id'=>'database']) !!}
					</div>
					<div class="form-group">

						{!! Form::submit('Delete',  array('class'=>'btn btn-danger ')) !!}
					</div>
					{!! Form::close() !!}
				</div>
			</div>
		</div>
	</div>
</div>




@stop


<script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
<script >



$(document).on("ready page:load", function() {
  setTimeout(function() { $(".alert").fadeOut(); }, 4000);

});


function ConfirmDelete()
{
    var x = confirm("Are you sure you want to perform this action?");
  if (x)
    return true;
else
    return false;
}



</script>
