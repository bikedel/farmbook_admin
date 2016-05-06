@extends('layouts.app')
@section('content')
<!--  @  gaugechart('Temps', 'chart3_div') -->
<div id="chart3_div" class='center'>
</div>
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
					<div class="panel-heading">Update Logs</div>
					<div class='hidden'>
						{{$i=0}}
					</div>
					<div class="panel-body ">
						<div>
							<p><br></p>
						</div>
						<ul class="list-group">
							@foreach($logs as $log)
							<div class="row">
								<div class="col-sm-8">
									<a href="{{url($links[$i])}}" class="list-group-item">   {{$log}}</a>
								</div>
								<div class="col-sm-4">
									<a href="{{url($links[$i])}}" class="list-group-item">   {{$times[$i]}} </a>
								</div>
							</div>
							<div class='hidden'>
								{{$i++}}
							</div>
							@endforeach
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@stop
