@extends('layouts.app')

@section('content')



<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">


        @if ( Session::has('flash_message') )
        <div class="alert {{ Session::get('flash_type') }} ">
          <button type="button" class="form-group btn close" data-dismiss="alert" aria-hidden="true">&times;</button>
          <p>{{ Session::get('flash_message') }}</p>
        </div>

        @endif


            <div class="panel panel-primary">
                <div class="panel-heading">Find a propery by Street Name</div>

                <div class="panel-body">

                    <div class='main_search_form'> 

                        {{ Form::open(array('method' =>'POST','url'=>'/street')) }}
                        <div class="col-sm-4">
                            <input type="text" class="form-control" placeholder="Enter Street Name" name="input" >
                        </div>
                        <div class="col-sm-2">
                           <input type="button" class="form-control readonly disabled id" placeholder="" name="or" value="or">
                        </div>
                        <div class="col-sm-4">
                            {!! Form::select('selected', $streets, null, ['class' => 'form-control']) !!}
                        </div>
                        <div class="col-sm-2">
                            {{Form::submit('Go', array('class' => 'btn btn-success')) }}
                        </div>
                        {{ Form::close() }}
                        <!--  </form> -->

                   </div>
                   <!--  <main_search_form -->
            </div>
            <!--  panel body -->
        </div>
        <!--  col-md-10 -->


            <div class="panel panel-primary">
                <div class="panel-heading ">Find a propery by Complex Name</div>

                <div class="panel-body">

                    <div class='main_search_form'> 

                        {{ Form::open(array('method' =>'POST','url'=>'/complex')) }}
                        <div class="col-sm-4">
                            <input type="text" class="form-control" placeholder="Enter Complex Name" name="input" >
                        </div>
                        <div class="col-sm-2">
                           <input type="button" class="form-control readonly disabled id" placeholder="" name="or" value="or">
                        </div>
                        <div class="col-sm-4">
                            {!! Form::select('selected', $complexes, null, ['class' => 'form-control']) !!}
                        </div>
                        <div class="col-sm-2">
                            {{Form::submit('Go', array('class' => 'btn btn-success')) }}
                        </div>
                        {{ Form::close() }}
                        <!--  </form> -->

                   </div>
                   <!--  <main_search_form -->
            </div>
            <!--  panel body -->
        </div>
        <!--  col-md-10 -->







    </div>
    <!--  row -->
</div>
<!--  pcontainer -->
@endsection

<script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
<script >



$(document).on("ready page:load", function() {
  setTimeout(function() { $(".alert").fadeOut(); }, 4000);

});





</script>

