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
          {{ link_to(url('/erf/'.$search), 'Edit All', ['class' => 'btn btn-default']) }} 
          {{ link_to(url('/home'), 'Back to Search', ['class' => 'btn btn-default']) }}
          <p><br></p>
          <table class="table">
            <tr>
              <th>Action</th>
              <th>Erf </th>
              <th>Street </th> 
              <th>No</th>
              <th>Owners</th> 
            </tr>
            <div class='hidden'>
              {{$i=0}}
            </div>

            @foreach ($properties as $property)
           <div class='hidden'>
              {{$i++}}
            </div>
            <div class="row">  
              <tr>
               <td>

            {{ link_to_action('PropertyController@edit','view/edit', ['id' => $property->id]) }} 
                <!--    {!!link_to('/erf/'.$property->numErf.'/page/'.$i, 'View/Edit', $parameters = array('eventid' => $i, 'userid' => $i) )!!} -->

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
          <br>
        <!--  {{ link_to(url('/erf/'.$search), 'Edit All', ['class' => 'btn btn-default']) }}  -->
          {{ link_to(url('/home'), 'Back to Search', ['class' => 'btn btn-default']) }}

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
