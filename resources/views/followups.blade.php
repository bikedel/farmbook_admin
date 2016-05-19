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
        <div class="panel-heading">Followup Dates [{{sizeof($owners)}}]  </div>
        <div class="panel-body table-responsive">
          {{ link_to(url('/printfollowups/'), 'Print', ['class' => 'btn btn-info']) }}
          <p><br></p>
          <table class="table">
            <tr>
              <th>Action</th>
              <th>Date</th>
              <th>Owners </th>
              <th>Key </th>
            </tr>
            <div class='hidden'>
              {{$i=0}}
            </div>
            @foreach ($followups as $followup)
            <div class='hidden'>
              {{$i++}}
            </div>
            <div class="row">
              @foreach ($followup->properties as $own)
              <tr>
                <td>
                  {{link_to(url('/property/'.$own->id),'View/Edit')}}
                </td>
                <td>
                  {{ $followup->followup  }}
                </td>
                <td>
                  {{   $own->strOwners }}
                </td>
                <td>
                  {{  $own->strKey }}
                </td>
              </tr>
              @endforeach
            </div>
            @endforeach
          </table>
          <br>
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
