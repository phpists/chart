<?php

namespace getLaunched\Http\Controllers\Kpi;

use getLaunched\Http\Requests;
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
use Config;

class KpiCompaniesController extends \getLaunched\Http\Controllers\BaseController
{

    private $saveCompanyRules = ['name' => 'required|min: 3'];

    public function __construct()
    {

        parent::__construct();
        Assets::css('css/kpi.css');
        Assets::js('js/kpi/kpi.js');

    }

    public function saveCompany()
    {
        if ($this->ajax()) {

            $validator = Validator::make(Input::all(), $this->saveCompanyRules);

            if ($validator->fails()){
                Log::error('Save Company KPI Validation fail', [ 'data' => implode('|', $validator->getData()), 'errors' => implode('|', $validator->errors()->all()) ]);
                return response()->json([ 'error' => implode('|', $validator->errors()->all()) ]);
            } else {
                $kpiCompany             = ($kpiComp = KpiCompanies::find((int)Input::get('id'))) ? $kpiComp : new KpiCompanies();
                $kpiCompany->Name       = Input::get('name');
                $kpiCompany->Status     = 1;
                $kpiCompany->OwnerID    = Auth::id();
                $kpiCompany->save();
                return response()->json([ 'error' => false ]);
            }
        }
    }

    public function deleteCompany()
    {
        if ($this->ajax() && Input::has('id')){
            $company = KpiCompanies::find((int)Input::get('id'));
            if(!empty($company)){
                $company->delete();
            }
        }
    }

    public function loadDataForCompany()
    {
        if ($this->ajax() && Input::has('companyID')) {
            $data = KpiCompanies::where('ID', (int)Input::get('companyID'))->where('OwnerID', Auth::id())->where('Status', 1)->first();
            return response()->json($data);
        }
    }

    public function refreshCompany(){
        $companies = KpiCompanies::where('OwnerID', Auth::id())->where('Status', 1)->orderBy('created_at', 'desc')->lists('Name', 'ID');

        return response()->json([
            'containerData' => '#load-companies',
            'contentValue' => View::make('kpi.parts.load-companies', ['companies' => $companies])->render(),
        ]);
    }


}
