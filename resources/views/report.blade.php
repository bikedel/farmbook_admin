@extends('layouts.app')

@section('content')


<style>

#owners {

	margin-top:30px;
	margin-left:0;
	float:left;
}
ul  {


	margin-left: 120px;
	float:left;
}


table {
    table-layout:fixed;
    font-size: 80%;
    padding:0;
    align:center;
}

body {

	background-color: white;
}
.t{

    font-size: 80%;
    padding:0;
     clear;
}
table th {
    padding:0;
    overflow: hidden;
    text-overflow: ellipsis;
}

table td {
    padding:0;
    overflow: hidden;
    text-overflow: ellipsis;
}






</style>

<div class="container-fluid col-md-10 col-md-offset-1" >
	<h2> {{ $properties[0]->strSuburb }} - {{ $search }}  	<a href="javascript:window.print()" class='btn btn-success'>Print</a></h2>


	@if ( Session::has('flash_message') )

	<div class="alert {{ Session::get('flash_type') }} ">
		<button type="button" class="form-group btn close" data-dismiss="alert" aria-hidden="true">&times;</button>
		<p>{{ Session::get('flash_message') }}</p>
	</div>

	@endif







		<table class="table table-bordered " style="table-layout: fixed; width: 700px">
			<th width='100px'> Street Name </th>
			<th width='40px'> No </th>
			<th width='50px'> Erf </th>
			<th width='100px'> Owner </th>
			<th width='70px'> Id </th>
			<th width='100px'> H Phone </th>
			<th width='100px'> W Phone </th>
			<th width='100px'> C Phone </th>
		</table>

		@foreach($properties as $property)
		<table class="table table-bordered " style="table-layout: fixed; width: 700px">
			<tbody>
				<tr>
						<td width='100px'> {{ $property->strStreetName }}  </td>
						<td width='40px'> {{ $property->strStreetNo }} </td>
						<td width='50px'> {{ $property->numErf }} </td>
						<td width='100px'> {{ $property->strOwners }} </td>
						<td width='70px'> {{ substr($property->strIdentity ,0,6)}} </td>
						<td width='100px'> {{ $property->owner->strHomePhoneNo }} </td>
						<td width='100px'> {{ $property->owner->strWorkPhoneNo }} </td>
						<td width='100px'> {{ $property->owner->strCellPhoneNo }} </td>
				</tr>
				<tr>
					<td colspan="8"> {{ $property->owner->EMAIL }} </td>
				</tr>
			</tbody>
		</table>

		<p class="t" style="width:700px">
			{{ $property->note->memNotes }}

		</p>


		@endforeach







</div>

@stop
<script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
<script>

$(document).on("ready page:load", function() {
	setTimeout(function() { $(".alert").fadeOut(); }, 4000);

});


function priceFormat(price) {
	alert(price);

	price = price.replace(/[^0-9]/g, '');

	price = Number(price).toLocaleString('en') ;

	return "R "+price

}


function mychange(street){
	alert("dgjfbdgkbdskjgbfkgjfdg");
}

</script>
