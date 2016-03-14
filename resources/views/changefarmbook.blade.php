@extends('layouts.app')

@section('content')
<style type="text/css">
body {

}
.update{

  float:right;
}
th {

  opacity:.4;
}



#strStreetNo{ color:red;}
</style>


<div class="container">
  <div class="row">
    <div class="col-md-10 col-md-offset-1">
      <div class="panel panel-primary">
        <div class="panel-heading">Change Farmbook</div>

        <div class="panel-body">
        @if ( Session::has('flash_message') )
        <div class="alert {{ Session::get('flash_type') }} ">
          <button type="button" class="form-group btn close" data-dismiss="alert" aria-hidden="true">&times;</button>
          <p>{{ Session::get('flash_message') }}</p>
        </div>

        @endif
          {{ Form::open(array('method' =>'POST','url'=>'/setuserfarmbook')) }}

          <div>
            {{ Form::label('Farmbook','Select Farmbook',array('id'=>'','class'=>'')) }}
            {!! Form::select('getfarmbook[]', $farmbooks,$default, ['class'=> 'form-control col-md-6', 'id'=>'farmbook']) !!}

          </div>
          <div>

          </div>
          <div><br></div>
          <div class="row">
            <p><br></p>
          </div>
          {{Form::submit('Set', array('class' => 'btn btn-danger update')) }} 
          {{ Form::close() }} 
        </div>

        




      </div>


      <div class="panel panel-primary hidden">
        <div class="panel-heading">Add Street</div>
    
        <div class="panel-body">
    @if ( Session::has('flash_message') )
        <div class="alert {{ Session::get('flash_type') }} ">
          <button type="button" class="form-group btn close" data-dismiss="alert" aria-hidden="true">&times;</button>
          <p>{{ Session::get('flash_message') }}</p>
        </div>

        @endif
          {{ Form::open(array('method' =>'POST','url'=>'/addStreet')) }}

          <div>
            {{ Form::label('Enter Street Name') }}
            {{ Form::input('text', 'street', null, ['class' => 'form-control col-md-6']) }}

          </div>
          <div>

          </div>
          <div><br></div>
          <div class="row">
            <p><br></p>
          </div>
          {{Form::submit('Add', array('class' => 'btn btn-danger update')) }} 
          {{ Form::close() }} 
        </div>

        
     

        

      </div>

    </div>
  </div>

   @endsection

  <script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
  <script >



  $(document).on("ready page:load", function() {
    setTimeout(function() { $(".alert").fadeOut(); }, 4000);

  });





  </script>
