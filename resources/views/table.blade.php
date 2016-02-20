@extends('layouts.app')

@section('content')

<style>
.dataTables_wrapper .dataTables_paginate .paginate_button {
  padding : 0px;
  margin-left: 0px;
  display: inline;
  border: 0px;
}
.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
  border: 0px;
}
#table_info.dataTables_info {
  margin-top: -40px;
  float:right;
}
#table_length.dataTables_length {
  margin-top: 10px;
  float:left;

}

th{ 
 /* background-color: rgba(37, 91, 171, 1); */
 background-color:#333;
  color:silver
}
th.sorting {color:silver;}

th, td { white-space: nowrap; 
  overflow: ellipsis;
}

tfoot input {
  width: 100%;
  padding: 3px;
  box-sizing: border-box;
}

</style>

<div class="container">
    <div class="row">

            <div class="panel panel-primary">
                <div class="panel-heading">Datatable</div>

                <div class="panel-body">


<table cellpadding="3" cellspacing="0" border="0" style="width:100%; margin: 0 auto 2em auto;">
        <thead>
            <tr>
                <th>Target</th>
                <th>Search text</th>
                <th>Treat as regex</th>
                <th>Use smart search</th>
            </tr>
        </thead>
        <tbody>
            <tr id="filter_global">
                <td>Global search</td>
                <td align="center"><input type="text" class="global_filter" id="global_filter"></td>
                <td align="center"><input type="checkbox" class="global_filter" id="global_regex"></td>
                <td align="center"><input type="checkbox" class="global_filter" id="global_smart" checked="checked"></td>
            </tr>
            <tr id="filter_col1" data-column="0">
                <td>Column - Suburb</td>
                <td align="center"><input type="text" class="column_filter" id="col0_filter"></td>
                <td align="center"><input type="checkbox" class="column_filter" id="col0_regex"></td>
                <td align="center"><input type="checkbox" class="column_filter" id="col0_smart" checked="checked"></td>
            </tr>
            <tr id="filter_col2" data-column="1">
                <td>Column - Erf</td>
                <td align="center"><input type="text" class="column_filter" id="col1_filter"></td>
                <td align="center"><input type="checkbox" class="column_filter" id="col1_regex"></td>
                <td align="center"><input type="checkbox" class="column_filter" id="col1_smart" checked="checked"></td>
            </tr>
            <tr id="filter_col3" data-column="2">
                <td>Column - Portion</td>
                <td align="center"><input type="text" class="column_filter" id="col2_filter"></td>
                <td align="center"><input type="checkbox" class="column_filter" id="col2_regex"></td>
                <td align="center"><input type="checkbox" class="column_filter" id="col2_smart" checked="checked"></td>
            </tr>
            <tr id="filter_col4" data-column="3">
                <td>Column - Street No</td>
                <td align="center"><input type="text" class="column_filter" id="col3_filter"></td>
                <td align="center"><input type="checkbox" class="column_filter" id="col3_regex"></td>
                <td align="center"><input type="checkbox" class="column_filter" id="col3_smart" checked="checked"></td>
            </tr>
            <tr id="filter_col5" data-column="4">
                <td>Column - Start date</td>
                <td align="center"><input type="text" class="column_filter" id="col4_filter"></td>
                <td align="center"><input type="checkbox" class="column_filter" id="col4_regex"></td>
                <td align="center"><input type="checkbox" class="column_filter" id="col4_smart" checked="checked"></td>
            </tr>
            <tr id="filter_col6" data-column="5">
                <td>Column - Salary</td>
                <td align="center"><input type="text" class="column_filter" id="col5_filter"></td>
                <td align="center"><input type="checkbox" class="column_filter" id="col5_regex"></td>
                <td align="center"><input type="checkbox" class="column_filter" id="col5_smart" checked="checked"></td>
            </tr>
        </tbody>



    <table class="table table-bordered table-striped table-responsive" id="table">
      <thead>
        <tr>
         <th>strSuburb</th>
         <th>numErf</th>
         <th>numPortion</th>
         <th>strStreetNo</th>
         <th>strStreetName</th>
         <th>strSqMeters</th>
         <th>strComplexNo</th>
         <th>strComplexName</th>
         <th>dtmRegDate</th>
         <th>strAmount</th>
         <th>strBondHolder</th>
         <th>strBondAmount</th>
         <th>strOwners</th>
         <th>strIdentity</th>
         <th>strSellers</th>
         <th>strTitleDeed</th>
       </tr>
     </thead>
           <tfoot>
        <tr>
         <th>strSuburb</th>
         <th>numErf</th>
         <th>numPortion</th>
         <th>strStreetNo</th>
         <th>strStreetName</th>
         <th>strSqMeters</th>
         <th>strComplexNo</th>
         <th>strComplexName</th>
         <th>dtmRegDate</th>
         <th>strAmount</th>
         <th>strBondHolder</th>
         <th>strBondAmount</th>
         <th>strOwners</th>
         <th>strIdentity</th>
         <th>strSellers</th>
         <th>strTitleDeed</th>
       </tr>
     </tfoot>
   </table>
 </div>
</div>

 </div>
</div>
 </div>

 </div>
</div>
@stop

@push('scripts')

<script>
$(function() {
  $('#table').DataTable({
    processing: true,
    serverSide: true,
    dom: 'Bfrtip',
    dom: 'Bflrtip',
    sDom: '<"top">Brpt<"bottom"lir><"clear">',
    pagingType: 'full',
    searching: true,
    processing: true,
    bProcessing:true,
    serverSide: false,
    select: true,
    scrollX: '100%',
    responsive: true,
    bAutoWidth: true,
    bInfo: true,
    bFilter: true,
    searching: true,
    bAutoWidth: true,
    bStateSave: true,
    lengthChange: true,
    paging:true,
    keys:true,
    scrollY: 390,
    iDisplayLength: 10,
    lengthMenu: [[ 5, 10, 25, 50, -1], [ 5, 10, 25, 50, "All"]],
    ajax: '{!! route('datatables.data') !!}',
    columns: [
    { data: 'strSuburb', name: 'strSuburb' },
    { data: 'numErf', name: 'numErf' },
    { data: 'numPortion', name: 'numPortion' },
    { data: 'strStreetNo', name: 'strStreetNo' },
    { data: 'strStreetName', name: 'strStreetName' },
    { data: 'strSqMeters', name: 'strSqMeters' },
    { data: 'strComplexNo', name: 'strComplexNo' },
    { data: 'strComplexName', name: 'strComplexName' },
    { data: 'dtmRegDate', name: 'dtmRegDate' },
    { data: 'strAmount', name: 'strAmount' },
    { data: 'strBondHolder', name: 'strBondHolder' },
    { data: 'strBondAmount', name: 'strBondAmount' },      
    { data: 'strOwners', name: 'strOwners' },  
    { data: 'strIdentity', name: 'strIdentity' },    
    { data: 'strSellers', name: 'strSellers' },    
    { data: 'strTitleDeed', name: 'strTitleDeed' }  
    ]
  });
});

function filterGlobal () {
    $('#table').DataTable().search(
        $('#global_filter').val(),
        $('#global_regex').prop('checked'),
        $('#global_smart').prop('checked')
    ).draw();
}
 
function filterColumn ( i ) {
    $('#table').DataTable().column( i ).search(
        $('#col'+i+'_filter').val(),
        $('#col'+i+'_regex').prop('checked'),
        $('#col'+i+'_smart').prop('checked')
    ).draw();
}
 
$(document).ready(function() {
    $('#table').DataTable();
 
    $('input.global_filter').on( 'keyup click', function () {
        filterGlobal();
    } );
 
    $('input.column_filter').on( 'keyup click', function () {
        filterColumn( $(this).parents('tr').attr('data-column') );
    } );



} );
</script>


@endpush