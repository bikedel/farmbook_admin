@extends('layouts.app')

@section('content')
<style type="text/css">
body {

}
table td{padding:5px;}
th {

    opacity:.2;
}

#strStreetNo{ color:red;}
</style>


<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-primary">
                <div class="panel-heading">Farmbooks  [{{$farmbooks->count()}}]  </div>

                <div class="panel-body">

                 <table style="width:100%" class="table-striped">
                  <tr>
                    <th>[Action]</th>
                    <th>[Id] </th>
                    <th>[Name] </th> 
                    <th> [Database]</th>
       <th> [Type]</th>

                </tr>
                @foreach ($farmbooks as $farm)

                <div class="row">  
                  <tr>
                     <td>

                        {{ link_to_action('FarmbookController@edit','edit', ['id' => $farm->id]) }}

                    </td>
                    <td>

                        {{  $farm->id }}

                    </td>
                    <td>

                        {{ $farm->name }}

                    </td>
                    <td>

                        {{ $farm->database }}

                    </td>
                 <td>

                        {{ $farm->type }}

                    </td>


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
