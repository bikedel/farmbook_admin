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



@foreach ($users as $user)
{{ Form::open(array('method' =>'POST','url'=>'/user/'.$user->id)) }}
<div class="container">
  <div class="row">
    <div class="col-md-10 col-md-offset-1">
      <div class="panel panel-primary">
        <div class="panel-heading">User   [  {{$user->name }}  ]


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
                <td width="600"><input type="text" name="id" value="{{ $user->id }}" readonly></td>
              </tr>
              <tr>
                <td class='tlabel' >Name  </td>
                <td><input type="text" name="name" value="{{ $user->name }}"</td>
              </tr>

              <tr>
                <td class='tlabel' width="120">Email </td>
                <td > <input type="text" name="email" value="{{ $user->email  }}" readonly></td>
              </tr>
              <tr>
                <td class='tlabel' > Role </td>
                <td >{!! Form::select('admin', ['User','Admin'],$user->admin, ['class'=> 'form-control col-md-6', 'id'=>'farmbooks']) !!}</td>
              </tr>

              <tr>
                <td class='tlabel' width="100">Status</td>
                <td>{!! Form::select('active',['Active','Disabled'], $user->active, ['class'=> 'form-control col-md-6', 'id'=>'farmbooks']) !!}</td>
              </tr>

              <tr>
                <td class='tlabel' width="100">Farmbooks</td>
                <td> {!! Form::select('getfarmbook[]', $farmbooks,$user_farmbooks, ['multiple'=>'multiple','class'=> 'sel2 form-control col-md-6', 'id'=>'getfarmbook']) !!}</td>
              </tr>
              <tr>
                <td class='tlabel' width="100"></td>
                <td> {!! Form::select('default', $farmbooks,$user_farmbooks, ['class'=> 'form-control col-md-6 hidden', 'id'=>'default', 'readonly' => 'true', 'hidden' => 'true']) !!}</td>
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


      {{ Form::open(array('method' =>'GET','url'=>'/users/')) }}
      {{Form::submit('Back', array('class' => 'btn btn-default back')) }}
      {{ Form::close() }}

    </div>
  </div>

  @endsection



  <script src="//code.jquery.com/jquery.js"></script>

  <script >



   $( document ).ready(function() {




    setTimeout(function() { $(".alert").fadeOut(); }, 4000);


        $('#getfarmbook').select2();


  });







  </script>
