@extends('layouts.app')

@section('content')
<style type="text/css">
body {

}
table { 
  width: 100%; 
  border-collapse: collapse; 
}
/* Zebra striping */
tr:nth-of-type(odd) { 
  background: #eee; 
}
th { 
  background: #333; 
  color: white; 
  font-weight: bold; 
}
td, th { 
  padding: 5px; 
  border: 1px solid #ccc; 
  text-align: left; 
}
.in {
display:inline;
 display: block;
}

form {
    display: inline-block; //Or display: inline; 
}


th {

    opacity:.8;
}

#strStreetNo{ color:red;}
</style>


<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-primary">
                <div class="panel-heading">Users  [{{$users->count()}}]  </div>

                <div class="panel-body ">
                    @if ( Session::has('flash_message') )
                    <div class="alert {{ Session::get('flash_type') }} ">
                      <button type="button" class="form-group btn close" data-dismiss="alert" aria-hidden="true">&times;</button>
                      <p>{{ Session::get('flash_message') }}</p>
                  </div>

                  @endif
                  <table class="table table-responsive">
                      <tr>
                        <th>Action</th>
                        <th>Id </th>
                        <th>Name </th> 
                        <th>Email</th>
                        <th>Farmbook</th> 
                        <th>Active</th> 
                        <th>Admin</th> 

                    </tr>
                    @foreach ($users as $user)

                    <div class="row">  
                      <tr>
                         <td>

                          <!--  {{ link_to_action('UserController@edit','edit', ['id' => $user->id]) }} -->


                            {!! Form::open(['method' => 'get', 'url' => ['/user', $user->id]]) !!}
                            {!! Form::button('<i class="glyphicon glyphicon-edit"></i>', array('type' => 'submit', 'class' => 'specialButton in')) !!}
                            {!! Form::close() !!}
                            {!! Form::open(['method' => 'post', 'url' => ['/deleteuser', $user->id], 'onsubmit' => 'return ConfirmDelete()']) !!}
                            {!! Form::button('<i class="glyphicon glyphicon-trash"></i>', array('type' => 'submit', 'class' => 'specialButton in')) !!}
                            {!! Form::close() !!}
                        </td>
                        <td>

                            {{  $user->id }}

                        </td>
                        <td>

                            {{ $user->name }}

                        </td>
                        <td>

                            {{ $user->email }}

                        </td>
                        <td>

                            {{ $user->farmbook }}

                        </td>

                        <td>

                            {{ $user->active }}

                        </td>

                        <td>

                            {{ $user->admin }}

                        </td>

                    </div>
                    @endforeach
                </table>

                {{ link_to_action('UserController@adduser','Add User', ['id' => $user->id], ['class' => 'btn btn-info']) }}

            </div>

        </div>



    </div>
</div>

<br><br>
@endsection


<script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
<script >



$(document).on("ready page:load", function() {
  setTimeout(function() { $(".alert").fadeOut(); }, 4000);

});




function ConfirmDelete()
{
    var x = confirm("Are you sure you want to delete this user?");
  if (x)
    return true;
else
    return false;
}



</script>
