<?php

namespace App\Http\Controllers;

use App\DataTables\PropertyDataTable;
use App\Http\Controllers\Controller;
use App\Property;
use Auth;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;

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
        $property->changeConnection($database);

        $d = Property::on($database)->with('note', 'owner')->select('*');

        //$d->load('owner', 'note');

        return Datatables::of($d)->make(true);

    }

}
