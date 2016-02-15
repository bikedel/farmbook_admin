@extends('layouts.app')

@section('content')
<style type="text/css">
body {

}

th {

    opacity:.4;
}

#strStreetNo{ color:red;}
</style>


<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-primary">
                <div class="panel-heading">Settings</div>

                <div class="panel-body">

              {{ Form::open(array('method' =>'POST','url'=>'/user/'.$user->id)) }}

              <table>
              <tr>
                <td class='tlabel' >Name</td>
                <td contenteditable='true'><input type="text" name="name" value=" {{$user->name }}"></td>
              </tr>
              <tr>
                <td class='tlabel' >Email</td>
                <td contenteditable='true'><input type="text" name="email" value="{{$user->email}} "></td>
              </tr>
              <tr>
                <td class='tlabel' >Admin</td>
                <td contenteditable='true'><input type="text" name="admin" value="{{$user->admin }}"></td>
              </tr>
              <tr>
                <td class='tlabel' >Active</td>
                <td min-width="600" contenteditable='true'><input type="text" name="active" value="{{$user->active }}"></td>
              </tr>

   {!! Form::select('getfarmbook[]', $farmbooks,$user_farmbooks, ['multiple'=>'multiple','class'=> 'form-control col-md-6', 'id'=>'suburb']) !!}

          </table>
              {{Form::submit('Update', array('class' => 'btn btn-danger update')) }} 
              {{ Form::close() }} 






        </div>

    </div>

</div>
</div>
        @if ( Session::has('flash_message') )
        <div class="alert {{ Session::get('flash_type') }} ">
          <button type="button" class="form-group btn close" data-dismiss="alert" aria-hidden="true">&times;</button>
          <p>{{ Session::get('flash_message') }}</p>
        </div>

        @endif
<br><br>
@endsection


<script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
<script >



$(document).on("ready page:load", function() {
  setTimeout(function() { $(".alert").fadeOut(); }, 4000);

});





</script>
