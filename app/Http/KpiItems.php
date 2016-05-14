<?php namespace getLaunched;

use Illuminate\Database\Eloquent\Model;
use DB,Config;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class KpiItems extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'kpi_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'CategoryID',
        'CompanyID',
        'OwnerID',
        'CountryCode',
        'Name',
        'Description',
        'Entered',
        'Format',
        'Target',
        'Direction',
        'Status',
    ];


    /**
     * Overriding primary key from id to ID
     */
    protected $primaryKey = 'ID';

    public static $menuKpi = [
        [
            'name'  => 'KPI',
            'link'  => '/kpi',
            'icon'  => 'icon-archery',
            'getActive' => [ '/kpi' ]
        ],
        [
            'name'  => 'dashboard',
            'link'  => '/kpi/dashboard',
            'icon'  => 'icon-meter-fast',
            'getActive' => [ '/kpi/dashboard' ]
        ]
    ];

    public static $entered = [
        1 => 'daily',
        2 => 'weekly',
        3 => 'monthly',
        4 => 'quarterly',
        5 => 'yearly'
    ];

    public static $format = [
        1   => '1,234',
        2   => '1,234.56',
        3   => '12%',
        4   => '12.34%',
        6   => '$1,234.56',
        7   => '£1,234.56',
        8   => '€1,234.56',
        10  => '12 secs',
        11  => '12 mins',
        12  => '12 hrs',
        13  => '12 days',
        14  => '12 wks',
        15  => '12 mths',
        16  => '12 qtrs',
        17  => '12 yrs'
    ];

    public static $country = [
        'us'   => '',
        'en'   => '',
        'europeanunion'   => '',
        'fr'   => '',
        'ge'   => ''
    ];

    public static $direction = [
        1   =>  'up',
        2   =>  'down',
        3   =>  'none'
    ];

    public static function getInput($entered, $isPaginate = false, $categoryID = false)
    {

        if (!empty($entered)) {
            $sql = KpiItems::where('OwnerID', Auth::id())
                ->where('Entered', (int)$entered)
                ->where('Status', 1)
                ->orderBy('created_at', 'desc');

            if ($categoryID) {
                $sql->where('CategoryID', (int)$categoryID);
            }

            if($isPaginate){
                return $sql->paginate(Config::get('constants.count_paginate_kpi'));
            } else{
                return $sql->get();
            }
        }

    }

    public static function getEntries($shift, $type , $id){

        return KpiEntries::where('Type', (int)$type)
            ->where('UserID', Auth::id())
            ->where('KpiID', (int)$id)
            ->where('Date', Carbon::now()->addDays($shift)->format('Y-m-d'))
            ->first(['Data', 'ID']);

    }

}
