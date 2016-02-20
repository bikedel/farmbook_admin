<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use Auth;
use Yajra\Datatables\Datatables;

use Input;
use App\DataTables\PropertyDataTable;
use Redirect;
use Route;
use Log;
use DB;
use Exception;
use Carbon;
use App\helpers;
use App\Property;


class DatatablesController extends Controller
{



	public $currUrl = "";


	public function __construct()
	{
		$this->middleware('auth');
	}



    public function index(PropertyDataTable $dataTable)
    {
        return $dataTable->render('table');
    }


		 /**
		 * Displays datatables front end view
		 *
		 * @return \Illuminate\View\View
		 */
		 public function getIndex()
		 {


		 	return view('table');
		 }




		/**
		 * Process datatables ajax request.
		 *
		 * @return \Illuminate\Http\JsonResponse
		 */
		public function anyData()
		{

			$database = Auth::user()->getDatabase();


			$property = new Property;
			$property->changeConnection(    $database  );

			$d = Property::on(   $database)->select('*');


			return Datatables::of($d)->make(true);


		}



		


	}
