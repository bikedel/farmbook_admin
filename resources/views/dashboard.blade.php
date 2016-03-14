

@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">

            </div>




@linechart('Registrations', 'chart-div')	
<div id="chart-div" class='low'>
	

</div>
@linechart('Prices', 'chart2-div')	
<br>
<div id="chart2-div" class='low'>
	

</div>
<br>
@barchart('Votes', 'poll_div')
<div id="poll_div" class='low'>
	

</div>


        </div>
    </div>
</div>
@endsection