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


</style>

@section('content')



@foreach ($farmbooks as $farm)
              {{ Form::open(array('method' =>'POST','url'=>'/farmbook/'.$farm->id)) }}
<div class="container">
  <div class="row">
    <div class="col-md-10 col-md-offset-1">
      <div class="panel panel-primary">
        <div class="panel-heading">Farmbook   [  {{$farm->name }}  ]



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

              <tr>
                <td class='tlabel' width="200">Id</td>
                <td width="600"><input type="text" name="id" value="{{ $farm->id }}" readonly></td>
              </tr>
              <tr>
                <td class='tlabel' >Name  </td>
                <td><input type="text" name="name" value="{{ $farm->name }}"</td>
              </tr>

              <tr>
                <td class='tlabel' width="120">Database </td>
                <td > <input type="text" name="database" value="{{ $farm->database  }}" ></td>
              </tr>


              <tr>
                <td class='tlabel' width="120">Deed Suburbs </td>
                <td>{!! Form::select('suburbs[]',$suburbs, $suburb_farmbooks, ['class'=> 'form-control col-md-6', 'multiple'=>'multiple','id'=>'suburbs']) !!}</td>

              </tr>

            </table>



            <div class=' update'>
              <br>




              {{Form::submit('Update', array('class' => 'btn btn-danger update')) }}
              {{ Form::close() }}

  @endforeach

            </div>

          </div>

        </div>


      </div>


  {{ Form::open(array('method' =>'GET','url'=>'/farmbooks/')) }}
      {{Form::submit('Back', array('class' => 'btn btn-default back')) }}
      {{ Form::close() }}

    </div>
  </div>

  @endsection



<script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
<script >



$(document).on("ready page:load", function() {
  setTimeout(function() { $(".alert").fadeOut(); }, 4000);
       $('#suburbs').select2();
});







</script>
