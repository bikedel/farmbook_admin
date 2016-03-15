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

#table_filter.dataTables_filter {

  float:left;
}

#table_length.dataTables_length {
  margin-top: 10px;
  float:left;

}

th{
 /* background-color: rgba(37, 91, 171, 1); */
 width:120%;
 background: #333;
 color: white;
 font-weight: bold;


}

.ellis {

  max-width:100px;
  overflow: hidden;
  text-overflow: ellipsis;

}

tr.selected{
  background-color:  rgba(37, 99, 171, 1) !important;

  color: white;
  font-weight: bold;

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

/**
 * Datatables Sorting icons on left
 */

 table.dataTable thead > tr > th {
  padding-left: 5px !important;
  padding-right: initial !important;
}

table.dataTable thead .sorting:after,
table.dataTable thead .sorting_asc:after,
table.dataTable thead .sorting_desc:after {
  float:left;

}



</style>

<div class="container">
  <div class="row">

    <div class="panel panel-primary">
      <div class="panel-heading">All Properties</div>

      <div class="panel-body">





        <table class="table table-bordered table-striped table-responsive" id="table">
          <thead>
            <tr>
             <th>Owners</th>
             <th>Identity</th>
             <th>Home Phone</th>
             <th>Home Work</th>
             <th>Home Cell</th>
             <th class='ellis'>Email</th>
             <th>Suburb</th>
             <th>Erf</th>
             <th>Port</th>
             <th>StreetNo</th>
             <th>StreetName</th>

             <th class='ellis'>ComplexNo</th>
             <th>ComplexName</th>
             <th class='ellis'>SqMeters</th>
             <th>RegDate</th>
             <th>Amount</th>
             <th>BondHolder</th>
             <th>BondAmount</th>

             <th>Sellers</th>
             <th>TitleDeed</th>
             <th>Key</th>
             <th>Updated_at</th>
             <th>Notes</th>

           </tr>
         </thead>

       </table>


       <br><br>

       <table cellpadding="3" cellspacing="0" border="0" style="width:100%; margin: 0 auto 2em auto;" class="hidden">
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
      </table>

      <br>
      <button type="button" class="btn btn-success " id ='viewOwner' value=''><span>Owner</span></button>
      <button type="button" class="btn btn-success " id ='viewErf' value=''><span>Erf</span></button>
      <button type="button" class="btn btn-success " id ='viewStreet' value=''><span>Street</span></button>
      <button type="button" class="btn btn-success " id ='viewComplex'>Complex</button>
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
 var table =  $('#table').DataTable({
  processing: true,
  serverSide: true,
  dom: 'Bfrtip',
  dom: 'Bflrtip',
  sDom: '<"top">fBrpt<"bottom"lir><"clear">',
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
  sRowSelect: "single",
  lengthMenu: [[ 5, 10, 25, 50, -1], [ 5, 10, 25, 50, "All"]],
  ajax: '{!! route('datatables.data') !!}',
  columns: [
  { data: 'strOwners', name: 'strOwners' },
  { data: 'strIdentity', name: 'strIdentity' },
  { data: 'owner.strHomePhoneNo', name: 'Home Phone' , width: '110px'},
  { data: 'owner.strWorkPhoneNo', name: 'Work Phone' , width: '110px'},
  { data: 'owner.strCellPhoneNo', name: 'Cell Phone' , width: '110px'},
  { data: 'owner.EMAIL', name: 'Email' , width: '200px'},
  { data: 'strSuburb', name: 'strSuburb' },
  { data: 'numErf', name: 'numErf' },
  { data: 'numPortion', name: 'numPortion' , width: '90px'},
  { data: 'numStreetNo', name: 'numStreetNo' , width: '90px'},
  { data: 'strStreetName', name: 'strStreetName' , width: '100px'},
  { data: 'strComplexNo', name: 'strComplexNo' , width: '90px', class: 'ellis'},
  { data: 'strComplexName', name: 'strComplexName' , width: '100px'},
  { data: 'strSqMeters', name: 'strSqMeters' , width: '80px', class: 'ellis'},
  { data: 'dtmRegDate', name: 'dtmRegDate' },
  { data: 'strAmount', name: 'strAmount' },
  { data: 'strBondHolder', name: 'strBondHolder' , width: '90px'},
  { data: 'strBondAmount', name: 'strBondAmount' , width: '120px'},
  { data: 'strSellers', name: 'strSellers' },
  { data: 'strTitleDeed', name: 'strTitleDeed' } ,
  { data: 'strKey', name: 'strKey' },
  { data: 'updated_at', name: 'updated_at' },
  { data: 'note.memNotes', name: 'Notes' },

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






  $selectedrow = 0;
  $('#table').DataTable();

  $('input.global_filter').on( 'keyup click', function () {
    filterGlobal();
  } );

  $('input.column_filter').on( 'keyup click', function () {
    filterColumn( $(this).parents('tr').attr('data-column') );
  } );
  var table = $('#table').DataTable();


  $('#table tbody').on( 'click', 'tr', function () {

    if ( !$(this).hasClass('selected') ) {
      $(this).removeClass('selected');

    }
    else {
      table.$('tr.selected').removeClass('selected');
      $(this).addClass('selected');
    }

    var owner = table.row( this ).data().strOwners ;
    var erf = table.row( this ).data().numErf ;
    var street = table.row( this ).data().strStreetName ;
    var complex = table.row( this ).data().strComplexName ;

    document.getElementById('viewOwner').innerHTML = owner;
    document.getElementById('viewErf').innerHTML = erf ;
    document.getElementById('viewStreet').innerHTML = street ;
    document.getElementById('viewComplex').innerHTML = complex ;
  } );




$("#viewOwner").click(function(event){

if ($selectedrow>0){

 var st =   document.getElementById('viewOwner').innerHTML;
// route to streetgrid plus street

$path = "{{ URL::to('owner') }}"+"/"+st;

// navigate to route
document.location.href=$path;
}
});

$("#viewErf").click(function(event){

if ($selectedrow>0){

 var st =   document.getElementById('viewErf').innerHTML;
// route to streetgrid plus street

$path = "{{ URL::to('erf') }}"+"/"+st;

// navigate to route
document.location.href=$path;
}
});


$("#viewStreet").click(function(event){

if ($selectedrow>0){

 var st =   document.getElementById('viewStreet').innerHTML;
// route to streetgrid plus street

$path = "{{ URL::to('street') }}"+"/"+st;

// navigate to route
document.location.href=$path;
}
});

$("#viewComplex").click(function(event){

if ($selectedrow>0){

 var st =   document.getElementById('viewComplex').innerHTML;
// route to streetgrid plus street

$path = "{{ URL::to('complex') }}"+"/"+st;

// navigate to route
document.location.href=$path;
}
});

  table
  .on( 'select', function ( e, dt, type, indexes ) {
    var rowData = table.rows( indexes ).data().toArray();
  $selectedrow = 1;
  } )

  .on( 'deselect', function ( e, dt, type, indexes ) {
    var rowData = table.rows( indexes ).data().toArray();

  } )



} );


$('table').mousedown(function(event) {
    event.preventDefault();
});


 (function($){

      $.fn.ctrl = function(key, callback) {

        // Hey, this does not work on Mac OsX!
        // On Mac we should capture Cmd key instead.
        // Anyone having time can add the feature.
        // TODO: read this
        // http://stackoverflow.com/questions/3902635/how-does-one-capture-a-macs-command-key-via-javascript

      if (!$.isArray(key)) {
           key = [key];
        }
      callback = callback || function(){ return false; }
        return $(this).keydown(function(e) {

        $.each(key,function(i,k){
          if(e.keyCode == k.toUpperCase().charCodeAt(0) && e.ctrlKey) {
            return callback(e);
          }
        });
        return true;
        });
    };


    $.fn.disableSelection = function() {

      this.ctrl(['a','s','c']);

        return this.attr('unselectable', 'on')
                   .css({'-moz-user-select':'-moz-none',
                         '-moz-user-select':'none',
               '-o-user-select':'none',
               '-khtml-user-select':'none',
               '-webkit-user-select':'none',
               '-ms-user-select':'none',
               'user-select':'none'})
                 .bind('selectstart', function(){ return false; });
    };

    })(jQuery);



    $(':not(input,select,textarea)').disableSelection();

</script>


@endpush
