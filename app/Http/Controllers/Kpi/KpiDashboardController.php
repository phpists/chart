<?php

namespace getLaunched\Http\Controllers\Kpi;

use getLaunched\Http\Requests;
use getLaunched\KpiEntries;
use Illuminate\Support\Facades\Auth;
use getLaunched\Assets;
use Session;
use Validator, Log;
use Illuminate\Support\Facades\Response;
use getLaunched\KpiCompanies;
use getLaunched\KpiCategories;
use getLaunched\KpiItems;
use getLaunched\KpiCharts;
use getLaunched\Translate;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;
use Config;
use \Carbon\Carbon;

class KpiDashboardController extends \getLaunched\Http\Controllers\BaseController
{

    private $saveChartRules = [
        'chartTitle'    => 'required|min: 3',
        'chartData'     => 'required'
    ];

    public function __construct()
    {

        parent::__construct();

        Assets::js('plugins/highcharts/highcharts.js');
        //Assets::js('plugins/highcharts/modules/exporting.js');

        Assets::js('plugins/bootstrap-daterangepicker-master/moment.min.js');
        Assets::js('plugins/bootstrap-daterangepicker-master/daterangepicker.js');
        Assets::css('plugins/bootstrap-daterangepicker-master/daterangepicker.css');

        Assets::css('css/kpi.css');
        Assets::css('css/inettuts/inettuts.css');
        Assets::css('css/inettuts/inettuts.js.css');
        Assets::js('plugins/inettuts/jquery-ui-personalized-1.6rc2.min.js');
        Assets::js('plugins/inettuts/cookie.jquery.js');
        Assets::js('plugins/inettuts/jquery.browser.min.js');
        Assets::js('plugins/inettuts/inettuts.js');

        Assets::js('js/kpi/inettuts.js');

    }

    public function dashboard()
    {
        return view('kpi.dashboard.dashboard', [
            'pageTitle' => Translate::translate('dashboard'),
            'menu'      => KpiItems::$menuKpi,
            'charts'    => KpiCharts::where('OwnerID', Auth::id())->orderBy('Order')->get()
        ]);
    }

    public function getChartModal()
    {

        $chartType = Input::get('chartType');

        if ($this->ajax() && in_array($chartType, array_keys(KpiCharts::$charts))){

            $items = KpiItems::where('OwnerID', Auth::id())
                ->where('Status', 1)
                ->orderBy('created_at', 'desc')
                ->get();

            $gData = [
                'chart' => [
                    'renderTo'  => 'kpi-chart',
                    'type'      => $chartType,
                    'width'     => 550,
                    'height'    => 400
                ],
                'title' => [
                    'text'      => KpiCharts::$charts[$chartType]['title'],
                    'x'         => -20
                ],
                'subtitle' => [
                    'text'      => KpiCharts::$charts[$chartType]['subtitle'],
                    'x'         => -20
                ]
            ];

            return view('kpi.dashboard.modal.charts', [
                'chartType'     => $chartType,
                'chartTitle'    => KpiCharts::$charts[$chartType]['title'],
                'items'         => $items,
                'gData'         => json_encode($gData, JSON_UNESCAPED_UNICODE),
                'startDate'     => Carbon::now()->toDateString(),
                'endDate'       => Carbon::now()->addDays(-3)->toDateString(),
            ]);
        }
    }

    
    public function refreshChart()
    {
        $params = Input::get('params');

        if ($this->ajax() && in_array($params['chartType'], array_keys(KpiCharts::$charts))) {

            $chartType = $params['chartType'];

            $kpis       = !empty($params['kpi']) ? unserialize(serialize($params['kpi'])) : null;

            $data       = explode(' - ', $params['date']);
            $dateStart  = !empty($data[0]) ? $data[0] : Carbon::now()->addDays(-30);
            $dateEnd    = !empty($data[1]) ? $data[1] : Carbon::now();

            $dateFromRange = $this->getDatesFromRange($dateStart, $dateEnd);

            $result     = [];
            $axisData   = [];

            if (!empty($kpis) && !empty($dateFromRange)){

                if($chartType == 'pie') {
                    $result['series'] = [
                        ['name'          => Translate::translate('KPI'),
                        'colorByPoint'  => true,
                        'data'          => []
                            ]
                    ];
                }

              foreach($kpis as $id){

                  $dataArray  = [];

                  if($id['name'] == 'daterange' || $id['name'] == 'title' || $id['name'] == 'subtitle') continue;

                  foreach($dateFromRange as $date){
                     $arrayCategories[] = Carbon::parse($date)->toFormattedDateString();
                     $dataArray[]       = (int)KpiEntries::where('UserID', Auth::id())->where('KpiID', (int)$id['value'])->where('Date', $date)->pluck('Data');
                  }

                  switch($chartType){
                      case 'pie':
                          $sum = 0;
                          if(!empty($dataArray)){
                             foreach($dataArray as $sumData) {
                                 $sum += $sumData;
                             }
                          }
                          $pieData[] = [
                              'name'    => KpiItems::where('OwnerID', Auth::id())->where('ID', $id['value'])->pluck('Name'),
                              'y'       => $sum
                          ];
                          break;

                      default:
                          $axisData = ['xAxis' => ['categories' => !empty($arrayCategories) ? $arrayCategories : null]];
                          $result['series'][] = [
                              'name' => KpiItems::where('OwnerID', Auth::id())->where('ID', $id['value'])->pluck('Name'),
                              'data' => !empty($dataArray) ? $dataArray : null
                          ];
                  }
              }

                if($chartType == 'pie'){
                    $result['series'][0]['data'] = $pieData;
                }

            }

            $gData = [
                'chart' => [
                    'renderTo'  => 'kpi-chart',
                    'type'      => $chartType,
                    'width'     => 550,
                    'height'    => 400
                ],
                'title' => [
                    'text'  => !empty($params['title']) ? $params['title'] : KpiCharts::$charts[$chartType]['title']
                ],
                'subtitle'  => [
                    'text'  => !empty($params['subtitle']) ? $params['subtitle'] : KpiCharts::$charts[$chartType]['subtitle']
                ],
                'legend' => [
                    'layout'        => 'vertical',
                    'align'         => 'center',
                    'verticalAlign' => 'bottom',
                    'borderWidth'   => 0,
                ],
                'series' => !empty($result['series']) ? $result['series'] : null,
            ];

            die(json_encode([ 'data' => array_merge($gData, $axisData) ]));

        }

    }

    public function saveChart()
    {
        if ($this->ajax() && Input::has('params')){

            $params = Input::get('params');

            $validator = Validator::make($params, $this->saveChartRules);

            if ($validator->fails()){

                Log::error('Save Chart Validation fail', [ 'data' => implode('|', $validator->getData()), 'errors' => implode('|', $validator->errors()->all()) ]);

                return response()->json([ 'error' => implode('|', $validator->errors()->all()) ]);

            } else {

                $chartData = $params['chartData'];

                $chartData['title']['text'] = '';
                $chartData['chart']['renderTo'] = 'kpi-chart-dashboard';
                $chartData['chart']['width'] = 350;
                $chartData['chart']['height'] = 300;

              switch($chartData['chart']['type']){

                  case 'pie':

                      if (!empty($chartData['series'])) {
                          foreach ($chartData['series'][0]['data'] as $key => $series) {
                              $chartData['series'][0]['data'][$key]['y'] = intval($chartData['series'][0]['data'][$key]['y']);
                          }
                      }

                      break;

                  default:
                      if (!empty($chartData['series'])) {
                          foreach ($chartData['series'] as $key => $series) {
                              $tData = $chartData['series'][$key]['data'];

                              $chartData['series'][$key]['data'] = array_map('intval', $tData);
                          }
                      }

              }

                $model = new KpiCharts();
                $model->OwnerID = Auth::id();
                $model->ChartData = json_encode($chartData);
                $model->ChartTitle = $params['chartTitle'];
                $model->save();
//            if ($model->save()){
//                return response()->json([
//                    'containerData' => '#list-charts',
//                    'contentValue'  => View::make('kpi.dashboard.parts.chart-lists', ['charts' => KpiCharts::where('OwnerID', Auth::id())->orderBy('Order')->get()])->render(),
//                    'action'        => 'drawChartDashboard'
//                ]);
//            }

                return response()->json([ 'error' => false ]);

            }
        }
    }

    public function savePosition()
    {
        if ($this->ajax()){
            $model          = KpiCharts::find((int)Input::get('id'));
            $model->Column  = (int)Input::get('column');
            $model->Order   = (int)Input::get('order');
            $model->save();
        }
    }

    public function chartDelete()
    {
        if ($this->ajax() && Input::has('id')){
            $model          = KpiCharts::find((int)Input::get('id'));
            $model->delete();
        }
    }

    private function getDatesFromRange($dateTimeFrom, $dateTimeTo)
    {
        $start  = Carbon::createFromFormat('Y-m-d', substr($dateTimeFrom, 0, 10));
        $end    = Carbon::createFromFormat('Y-m-d', substr($dateTimeTo, 0, 10));

        $dates = [];

        while ($start->lte($end)) {

            $dates[] = $start->copy()->format('Y-m-d');

            $start->addDay();
        }

        return $dates;
    }

}
