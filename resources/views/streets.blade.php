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

th {

    opacity:.8;
}

#strStreetNo{ color:red;}
</style>


<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-primary">
                <div class="panel-heading">Properties [{{$properties->count()}}]  </div>

                <div class="panel-body table-responsive">
                    {{ link_to(url('/street/'.$search), 'Edit All', ['class' => 'btn btn-default']) }}
                    <p><br></p>
                    <table class="table">
                      <tr>
                        <th>Action</th>
                        <th>Erf </th>
                        <th>Street </th> 
                        <th>No</th>
                        <th>Owners</th> 


                    </tr>
                    @foreach ($properties as $property)

                    <div class="row">  
                      <tr>
                       <td>

                        {{ link_to_action('PropertyController@edit','edit', ['id' => $property->id]) }}

                    </td>
                    <td>

                        {{  $property->numErf }}

                    </td>
                    <td>

                        {{ $property->strStreetName }}

                    </td>
                    <td>

                        {{ $property->strStreetNo }}

                    </td>
                    <td>

                        {{ $property->strOwners }}

                    </td>

                </div>
                @endforeach
            </table>
        </div>

    </div>
    {{ link_to(url('/home'), 'Back', ['class' => 'btn btn-default']) }}
 


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
