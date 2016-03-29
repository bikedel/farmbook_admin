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
                <div class="panel-heading">Farmbooks  [{{$farmbooks->count()}}]  </div>

                <div class="panel-body ">
        @if ( Session::has('flash_message') )
        <div class="alert {{ Session::get('flash_type') }} ">
          <button type="button" class="form-group btn close" data-dismiss="alert" aria-hidden="true">&times;</button>
          <p>{{ Session::get('flash_message') }}</p>
        </div>

        @endif
                 <table class="table table-responsive" >
                  <tr>
                    <th>Action</th>
                    <th>Id </th>
                    <th>Name </th>


                </tr>
                @foreach ($farmbooks as $farm)

                <div class="row">
                  <tr>
                     <td>


                            {!! Form::open(['method' => 'get', 'url' => ['/farmbook', $farm->id]]) !!}
                            {!! Form::button('<i class="glyphicon glyphicon-edit"></i>', array('type' => 'submit', 'class' => 'specialButton in')) !!}
                            {!! Form::close() !!}
                            {!! Form::open(['method' => 'post', 'url' => ['/farmbookdelete', $farm->id], 'onsubmit' => 'return ConfirmDelete()']) !!}
                            {!! Form::button('<i class="glyphicon glyphicon-trash"></i>', array('type' => 'submit', 'class' => 'specialButton in')) !!}
                            {!! Form::close() !!}
                    </td>
                    <td>

                        {{  $farm->id }}

                    </td>
                    <td>

                        {{ $farm->name }}

                    </td>




                    </td>

                </div>
                @endforeach
            </table>

              {{ link_to_action('CsvImportController@index','Import Farmbook', ['id' => $farm->id], ['class' => 'btn btn-info']) }}
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
    var x = confirm("Are you sure you want to delete this Farmbook?");
  if (x)
    return true;
else
    return false;
}



</script>
