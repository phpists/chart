<?php

namespace getLaunched\Http\Controllers\Kpi;

use Illuminate\Support\Facades\Auth;
use getLaunched\Assets;
use Session;
use Validator, Log;
use Illuminate\Support\Facades\Response;
use getLaunched\KpiCompanies;
use getLaunched\KpiCategories;
use getLaunched\KpiItems;
use getLaunched\Translate;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;
use Config;

class KpiController extends \getLaunched\Http\Controllers\BaseController
{

    private $saveKpiRules = [

        'name'          => 'required|min: 3',
        'categories'    => 'required|not_in:0',
        'direction'     => 'required|not_in:0',
        'entered'       => 'required|not_in:0',
        //'companyID'     => 'required|not_in:0'

    ];

    public function __construct(){

        parent::__construct();
        Assets::css('css/kpi.css');
        Assets::js('js/kpi/categories.js');
        Assets::js('js/kpi/kpi.js');

    }

    public function index()
    {



    }

    public function dashboard()
    {
        $categoriesForSelect = KpiCategories::where('OwnerID', Auth::id())->where('Status', 1)->lists('Name', 'ID');
        $categories          = KpiCategories::where('OwnerID', Auth::id())->where('Status', 1)->lists('Name', 'ID');
        $companies           = KpiCompanies::where('OwnerID', Auth::id())->where('Status', 1)->orderBy('created_at', 'desc')->lists('Name', 'ID');
        
        $items               = KpiItems::where('OwnerID', Auth::id())
                                ->where('Status', 1)
                                ->where('CategoryID', $categories->keys()[0])
                                ->orderBy('created_at', 'desc')
                                ->paginate(Config::get('constants.count_paginate_kpi'));

        if(empty($categoriesForSelect)) {
            $categoriesForSelect[0] = Translate::translate('choose');
        }

        return view('kpi.dashboard', [
            'pageTitle'     => Translate::translate('KPI'),
            'companies'     => $companies,
            'entered'       => KpiItems::$entered,
            'format'        => KpiItems::$format,
            'country'       => KpiItems::$country,
            'direction'     => KpiItems::$direction,
            'categories'    => $categories,
            'categoriesForSelect'    => $categoriesForSelect,
            'items'         => $items,
            'categoryName'  => KpiCategories::find($categories->keys()[0])->Name,
            'menu'          => KpiItems::$menuKpi
        ]);

    }

    public function saveKpi()
    {
        if ($this->ajax() && Input::has('post')) {

            $post = Input::get('post');

            $validator = Validator::make($post, $this->saveKpiRules);

            if ($validator->fails()){

                Log::error('Save KPI Validation fail', [ 'data' => implode('|', $validator->getData()), 'errors' => implode('|', $validator->errors()->all()) ]);

                return response()->json([ 'error' => implode('|', $validator->errors()->all()) ]);

            } else {
                $itemsKpi               = (!empty($post['ID']) && is_numeric($post['ID'])) ? KpiItems::find((int)$post['ID']) : new KpiItems();
                $itemsKpi->CategoryID   = (int)$post['categories'];
                $itemsKpi->CompanyID    = $this->project->ID;
                //$itemsKpi->CompanyID    = !empty($post['companyID']) ? (int)$post['companyID'] : null;
                $itemsKpi->OwnerID      = Auth::id();
                $itemsKpi->CountryCode  = $post['country'];
                $itemsKpi->Name         = $post['name'];
                $itemsKpi->Description  = $post['description'];
                $itemsKpi->Entered      = (int)$post['entered'];
                $itemsKpi->Format       = (int)$post['format'];
                $itemsKpi->Target       = $post['target'];
                $itemsKpi->Direction    = (int)$post['direction'];
                $itemsKpi->Status       = 1;
                $itemsKpi->save();
                return response()->json([ 'error' => false, 'categoryID' => (int)$post['categories'] ]);
            }

        }
    }

    public function removeKpi()
    {
        if ($this->ajax() && Input::has('id')) {
            $itemsKpi = KpiItems::find((int)Input::get('id'));
            $categoryID = $itemsKpi->CategoryID;
            if(!empty($itemsKpi)){
                $itemsKpi->delete();
                return response()->json(['categoryID' => $categoryID ]);
            }
        }
    }

    public function refreshTable()
    {
        if ($this->ajax() && Input::has('categoryID')) {
            $items = KpiItems::where('OwnerID', Auth::id())->where('Status', 1)->where('CategoryID', (int)Input::get('categoryID'))->orderBy('created_at', 'desc')->paginate(Config::get('constants.count_paginate_kpi'));

            return response()->json([
                'containerData' => '#table-kpi-list',
                'contentValue'  => View::make('kpi.parts.table-kpi-list', [
                    'items'     => $items,
                    'entered'   => KpiItems::$entered,
                    'format'    => KpiItems::$format,
                    'country'   => KpiItems::$country,
                    'direction' => KpiItems::$direction,
                    'categoryName'  => KpiCategories::find((int)Input::get('categoryID'))->Name
                ])->render(),
            ]);
        }
    }

    public function loadDataKpi()
    {
        if ($this->ajax() && Input::has('id')) {
            $data = KpiItems::where('ID', (int)Input::get('id'))->where('OwnerID', Auth::id())->where('Status', 1)->first();
            return response()->json($data);
        }
    }

}
