@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">

            </div>




@linechart('MyStocks', 'chart-div')	
<div id="chart-div">
	

</div>







        </div>
    </div>
</div>
@endsection
