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
                <div class="panel-heading"><i class="farmbooks glyphicon glyphicon-road"></i>Global Search </div>

                <div class="panel-body">

                    <div class='main_search_form'>

                        {{ Form::open(array('method' =>'POST','url'=>'/globalsearch')) }}

                        <div class="col-sm-4">
                      {!!   Form::label('Surname', 'Surname');!!}
                            {!! Form::text('surname'); !!}
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
