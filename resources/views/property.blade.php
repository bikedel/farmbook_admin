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
table td{padding:5px;}
.records{
padding:0px;
color:white;
margin-right: 6px;
font-weight:900;
float:right;
}
.links a{
background-color: orange;
border-color: red;
}
.street{
color:darkblue;
font-weight: 900;
}
.update{
float:right;
}
.backsearch {
float:left;
}
.id {
border-color: Transparent;
border:none;
}
.fa-btn {
margin-right: 6px;
}
.error{
border-color: Transparent;
color:red;
font-weight:900px;
}
.center {
position: absolute;
top: 55px; /* or whatever top you need */
left: 50%;
width: auto;
-webkit-transform: translateX(-50%);
-moz-transform: translateX(-50%);
-ms-transform: translateX(-50%);
-o-transform: translateX(-50%);
transform: translateX(-50%);
}
#dt {
text-indent: -500px;
height: 25px;
width: 200px;
}
</style>
@section('content')
@if (isset($page) && $page > 0)
<div class='hidden'>
  <h1>{{$page}}</h1>
  {{$url = url()->current().'?page='.$page}}
</div>
@else
{{$url = ""}}
{{$page = ""}}
@endif
<div class="container">
  <div class="row">
    <div class=''>
      {!! $properties->render() !!}
    </div>
  </div>
</div>
@foreach ($properties as $key => $property)
<div class="container">
  <div class="row">
    <div class="col-md-10 col-md-offset-1">
      <div>
        {{ link_to(url('/home'), 'Back to Search', ['class' => 'btn btn-default']) }}
        @if (isset($page) && $page > 0)
        {{ link_to(url($url), 'Selected', ['class' => 'btn btn-default']) }}
        @endif
      </div>
      <div class="panel panel-primary">
        <div class="panel-heading">Property   [  {{$property->strKey }}  ]
          <div class="records">
            [ {{$properties->currentPage()}} of {{$count}} ]
          </div>
        </div>
        @if ( Session::has('flash_message') )
        <div class="alert {{ Session::get('flash_type') }} ">
          <button type="button" class="form-group btn close" data-dismiss="alert" aria-hidden="true">&times;</button>
          <p>{{ Session::get('flash_message') }}</p>
        </div>
        @endif
        <div class="panel-body">
          <div class='property'>
            <table class="table-bordered">
              {{ Form::open(array('method' =>'POST','url'=>'/property/'.$property->strKey)) }}
              <tr>
                <td class='tlabel' width="200">Erf  </td>
                <td width="600">{{$property->numErf }}</td>
              </tr>
              <tr>
                <td class='tlabel' >Portion  </td>
                <td>{{$property->numPortion }}</td>
              </tr>
              <tr>
                <td class='tlabel' width="120">Street No  </td>
                <td class='street' contenteditable='true'><input type="text" name="strStreetNo" value="{{$property->strStreetNo }}"></td>
              </tr>
              <tr>
                <td class='tlabel' >Street Name </td>
                @if (isset($streets))
                <td class='street '>   {!! Form::select('strStreetName', $streets, [$property->strStreetName],  ['class' => 'form-control ','style' =>'width:100%']) !!} </td>
                @else
                <td class='street'>  {{ $property->strStreetName }}</td>
                @endif
              </tr>
              <tr>
                <td class='tlabel' width="100">Complex No  </td>
                <td>{{$property->strComplexNo }}</td>
              </tr>
              <tr>
                <td class='tlabel' >Complex Name </td>
                <td>{{$property->strComplexName }}</td>
              </tr>
              <tr>
                <td class='tlabel' >Sq Meters</td>
                <td>{{$property->strSqMeters }}</td>
              </tr>
              <tr>
                <td class='tlabel' width="120">Reg Date  </td>
                <td>{{$property->dtmRegDate }}</td>
              </tr>
              <tr>
                <td class='tlabel' >Amount </td>
                <td>{{$property->strAmount}}</td>
              </tr>
              <tr>
                <td class='tlabel' >Bond Amount </td>
                <td>{{$property->strBondAmount }}</td>
              </tr>
              <tr>
                <td class='tlabel' width="100">Bond Holder  </td>
                <td>{{$property->strBondHolder }}</td>
              </tr>
              <tr>
                <td class='tlabel' >Owner</td>
                <td>{{$property->strOwners }}</td>
              </tr>
              <tr>
                <td class='tlabel' >Surname</td>
                @if (!is_null($property->owner ))
                <td>{{$property->owner->strSurname }}</td>
                @else
                <td class="error">Error</td>
                @endif
              </tr>
              <tr>
                <td class='tlabel' >Identity</td>
                <td>{{$property->strIdentity }}</td>
              </tr>
              <tr>
                <td class='tlabel' >Home Phone</td>
                @if (!is_null($property->owner ))
                <td contenteditable='true'><input type="text" name="strHomePhoneNo" value="{{ $property->owner->strHomePhoneNo  }}"></td>
                @else
                <td class="error">Error</td>
                @endif
              </tr>
              <tr>
                <td class='tlabel' >Work Phone</td>
                @if (!is_null($property->owner ))
                <td contenteditable='true'><input type="text" name="strWorkPhoneNo" value="{{ $property->owner->strWorkPhoneNo  }}"></td>
                @else
                <td class="error">Error</td>
                @endif
              </tr>
              <tr>
                <td class='tlabel' >Cell Phone</td>
                @if (!is_null($property->owner ))
                <td contenteditable='true'><input type="text" name="strCellPhoneNo" value="{{ $property->owner->strCellPhoneNo  }}"></td>
                @else
                <td class="error">Error</td>
                @endif
              </tr>
              <tr>
                <td class='tlabel' >Email</td>
                @if (!is_null($property->owner ))
                <td min-width="600" contenteditable='true'><input type="text" name="EMAIL" value="{{ $property->owner->EMAIL  }}"></td>
                @else
                <td class="error">Error - Problem with database relationship, please inform System Admin.</td>
                @endif
              </tr>
              <tr>
                <td class='tlabel' >Previous Notes </td>
                @if (!is_null($property->note ))
                <td ><textarea  rows="6" cols="160" name="note" readonly> {{$property->note->memNotes }}</textarea></td>
                @else
                <td class="error"><textarea  rows="6" cols="160" name="note" readonly>Error - Problem with database relationship, please inform System Admin.</textarea></td>
                @endif
              </tr>
              <tr>
                <td class='tlabel' >New Notes </td>
                <td ><textarea  rows="6" cols="160" name="newnote"></textarea></td>
              </tr>
              <tr>
                <td class='tlabel' >Followup Date</td>
                <td >
                  {{Form::date('followup',$property->note->followup )}} Date format yyyy-mm-dd.
                </tr>
              </table>
              @endforeach
              <div class='backsearch'>
                <br>
                {{ link_to(url('/home'), 'Back to Search', ['class' => ' btn btn-default']) }}
              </div>
              <div class=' update'>
                <br>
                <input type="text" name="strKey" class="hidden" value="{{ $property->strKey }}"></input>
                <input type="text" name="strIdentity" class="hidden" value="{{ $property->strIdentity }}"></input>
                <input type="text" name="strOwners" class="hidden" value="{{ $property->strOwners }}"></input>
                {{Form::submit('Update', array('class' => 'btn btn-danger update')) }}
                {{ Form::close() }}
              </div>
            </div>
          </div>
        </div>
        {{ Form::open(array("method" =>"POST","url"=>Session::get('controllerroute'))) }}
        <input type="text" name="selected" class="hidden" value="{{Session::get('search')}}"></input>
        {{ Form::close() }}
      </div>
    </div>
    @endsection
    <script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
    <script >
    $(document).on("ready page:load", function() {
    setTimeout(function() { $(".alert").fadeOut(); }, 4000);
    });
    </script>
