<?php

namespace getLaunched\Http\Controllers\Kpi;

use getLaunched\Http\Requests;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use getLaunched\Assets;
use Session;
use Validator, Log;
use getLaunched\KpiItems;
use getLaunched\KpiEntries;
use getLaunched\Translate;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;
use Config;
use Carbon\Carbon;
use getLaunched\KpiCategories;

class KpiEntriesController extends \getLaunched\Http\Controllers\BaseController
{

    public function __construct()
    {
        parent::__construct();
        Assets::css('css/kpi.css');
        Assets::js('js/kpi/categories.js');
        Assets::js('js/kpi/entries.js');
    }

    public function input($id = 0)
    {
        if ($id > 0) {

            $entered = KpiItems::$entered;

            $kpi = [];
            if (!empty($entered)){
                foreach($entered as $enteredID => $enteredValue){
                    $getInputData = KpiItems::getInput($enteredID)->all();
                    if (!empty($getInputData)) {
                        foreach ($getInputData as $inputKey => $inputValue) {
                            $kpi['values'][$enteredID]['data'][$inputKey] = $inputValue;
                            $kpi['entered'][$enteredID] = count($kpi['values'][$enteredID]['data']);
                        }
                    }
                }
            }

            $kpiItem = KpiItems::find((int)$id)->pluck('Name');
            $categories          = KpiCategories::where('OwnerID', Auth::id())->where('Status', 1)->lists('Name', 'ID');

            return view('kpi.input.index', [
                'pageTitle' => str_replace('%name%', $kpiItem, Translate::translate('entries_data_for')),
                'kpi'       => $kpi,
                'activeID'  => (int)$id,
                'entered'   => $entered,
                'country'   => KpiItems::$country,
                'shift'     => 0,
                'menu'      => KpiItems::$menuKpi,
                'categories' => $categories,

            ]);

        }
    }

    public function shift()
    {
        if ($this->ajax() && Input::has('shift') && Input::has('tab')) {

            $entered = KpiItems::$entered;

            $kpi = [];
            if (!empty($entered)){
                foreach($entered as $enteredID => $enteredValue){
                    $getInputData = KpiItems::getInput($enteredID)->all();
                    if (!empty($getInputData)) {
                        foreach ($getInputData as $inputKey => $inputValue) {
                            $kpi['values'][$enteredID]['data'][$inputKey] = $inputValue;
                            $kpi['entered'][$enteredID] = count($kpi['values'][$enteredID]['data']);
                        }
                    }
                }
            }

            return response()->json([
                'containerData' => '#' . (int)Input::get('tab'),
                'contentValue'  => View::make('kpi.input.parts.kpi-entries', [
                    'shift'     => (int)Input::get('shift'),
                    'kpi'       => $kpi,
                    'activeID'  => 0,
                    'entered'   => $entered,
                    'country'   => KpiItems::$country,
                    'enteredID' => (int)Input::get('tab')
                ])->render(),
            ]);
        }
    }

    public function saveEntries()
    {
        if ($this->ajax() && Input::has('params')) {

            $post = Input::get('params');

            $entriesKpi             = (is_numeric($post['EntriesID']) && $post['EntriesID'] < 999999) ? KpiEntries::find((int)$post['EntriesID']) : new KpiEntries();
            $entriesKpi->UserID     = Auth::id();
            $entriesKpi->KpiID      = (int)$post['KpiID'];
            $entriesKpi->Date       = Carbon::now()->addDays((int)$post['Date']);
            $entriesKpi->Data       = $post['Data'];
            $entriesKpi->Type       = (int)$post['Type'];
            $entriesKpi->save();

            return response()->json(['id' => $entriesKpi->ID, 'oldId' => (int)$post['EntriesID']]);

        }
    }

}
