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

    table.print-friendly tr td table, table.print-friendly tr th table{
        page-break-inside: avoid;
    }

.report-entry table {

    page-break-after: auto;
    page-break-inside: avoid;
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
		<tr>
			<th width='100px'> Street Name </th>
			<th width='100px'> No </th>
			<th width='100px'> Erf </th>
			<th width='100px'> Id </th>
			<th width='300px'> Owner </th>
        </tr>
        <tr>
        	<th width='100px'> </th>
			<th width='100px'> H Phone </th>
			<th width='100px'> W Phone </th>
			<th width='100px'> C Phone </th>
			<th colspan="3" width='300px'>Email  </th>

		</tr>
		</table>

		@foreach($properties as $property)
		 <div class="report-entry">
		<table class="table table-bordered print-friendly" style="table-layout: fixed; width: 700px">
			<tbody>
				<tr>
						<td width='100px'> {{ $property->strStreetName }}  </td>
						<td width='100px'> {{ $property->strStreetNo }} </td>
						<td width='100px'> {{ $property->numErf }} </td>
						<td width='100px'> {{ substr($property->strIdentity ,0,6)}} </td>
						@if  ($property->strOwners)
						<td width='300px'> {{ $property->strOwners }} </td>
						@endif
				</tr>
				@if  ($property->owner)
				<tr>
				    <th width='100px'> </th>
					<td width='100px'> {{ $property->owner->strHomePhoneNo }} </td>
					<td width='100px'> {{ $property->owner->strWorkPhoneNo }} </td>
					<td width='100px'> {{ $property->owner->strCellPhoneNo }} </td>
					<td colspan="3"> {{ $property->owner->EMAIL }} </td>
				</tr>
				@endif
				@if  ($property->note)
				<tr>
				    <th width='50px'> </th>
					<td colspan="6"> {{ $property->note->memNotes }} </td>
				</tr>
                @endif
			</tbody>
		</table>
       </div>
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
