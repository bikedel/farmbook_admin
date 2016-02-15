@extends('layouts.app')

@section('content')
<style type="text/css">
body {

}
table td{padding:5px;}
th {

    opacity:.4;
}

#strStreetNo{ color:red;}
</style>


<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-primary">
                <div class="panel-heading">Users  [{{$users->count()}}]  </div>

                <div class="panel-body">

                 <table style="width:100%" class="table-striped">
                  <tr>
                    <th>[Action]</th>
                    <th>[Id] </th>
                    <th>[Name] </th> 
                    <th> [Email]</th>
                    <th>[F]</th> 
                    <th>[L]</th> 
                    <th>[A]</th> 

                </tr>
                @foreach ($users as $user)

                <div class="row">  
                  <tr>
                     <td>

                        {{ link_to_action('UserController@edit','edit', ['id' => $user->id]) }}

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
