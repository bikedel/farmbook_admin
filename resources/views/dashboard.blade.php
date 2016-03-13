@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">

            </div>




@linechart('Registrations', 'chart-div')	
<div id="chart-div">
	

</div>
@linechart('Prices', 'chart2-div')	
<br>
<div id="chart2-div">
	

</div>

@gaugechart('Temps', 'chart3_div')

<br>
<div id="chart3_div">
	

</div>

        </div>
    </div>
</div>
@endsection
