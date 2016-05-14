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

class KpiCategoriesController extends \getLaunched\Http\Controllers\BaseController
{

    public function __construct()
    {

        parent::__construct();
        Assets::css('css/kpi.css');
        Assets::js('js/kpi/categories.js');
        Assets::js('js/kpi/kpi.js');

    }

    public function getModal()
    {
        $categories = KpiCategories::find(Input::has('id') ? (int)Input::get('id') : 0);
        return view('kpi.modal.category', [ 'category' => $categories ]);
    }

    public function saveCategory()
    {
        if ($this->ajax()) {
            $categories = Input::has('categoryID') ? KpiCategories::find((int)Input::get('categoryID')) : new KpiCategories();
            $categories->Name = Input::get('categoryName');
            $categories->OwnerID = Auth::id();
            $categories->Status = 1;
            $categories->save();

            $returnCategories = KpiCategories::where('OwnerID', Auth::id())->where('Status', 1)->lists('Name', 'ID');
            return response()->json([
                'containerData' => '#manage-category',
                'contentValue'  => View::make('kpi.parts.manage-category', [ 'categories' => $returnCategories ])->render(),
            ]);
        }
    }

    public function deleteCategory()
    {
        if ($this->ajax() && Input::has('id')){
            $categories = KpiCategories::find((int)Input::get('id'));
            if(!empty($categories)){
                $categories->delete();
            }
        }
    }

    public function refreshSelectCategories()
    {
        if ($this->ajax()) {

            $currentCategory = Input::has('id') ? (int)Input::get('id') : '';

            $returnCategories = KpiCategories::where('OwnerID', Auth::id())->where('Status', 1)->lists('Name', 'ID');
            if (empty($returnCategories)) {
                $returnCategories[0] = Translate::translate('choose');
            }

            return response()->json([
                'containerData' => '#select-categories',
                'contentValue' => View::make('kpi.parts.select-category', ['categoriesForSelect' => $returnCategories, 'currentCategory' => $currentCategory])->render(),
            ]);
        }
    }

    public function refreshCategoryList(){
        $returnCategories = KpiCategories::where('OwnerID', Auth::id())->where('Status', 1)->lists('Name', 'ID');
        return response()->json([
            'containerData' => '#manage-category',
            'contentValue'  => View::make('kpi.parts.manage-category', [ 'categories' => $returnCategories ])->render(),
        ]);
    }


}
