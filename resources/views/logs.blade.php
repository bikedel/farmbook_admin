@extends('layouts.app')

@section('content')



<div class="container-fluid">	


	@if ( Session::has('flash_message') )

	<div class="alert {{ Session::get('flash_type') }} ">
		<button type="button" class="form-group btn close" data-dismiss="alert" aria-hidden="true">&times;</button>
		<p>{{ Session::get('flash_message') }}</p>
	</div>

	@endif



	<div class="container">
		<div class="row">
			<div class="col-md-10 col-md-offset-1">
				<div class="panel panel-primary">
					<div class="panel-heading">Logs</div>
					<div class='hidden'>
						{{$i=0}}
					</div>
					<div class="panel-body table-responsive">
						<ul class="list-group">
							
							@foreach($logs as $log)
							
							<a href="#" class="list-group-item">{{$i++}} - {{$log}}</a>
							@endforeach

						</ul>

					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@stop




