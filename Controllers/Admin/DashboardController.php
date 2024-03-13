<?php

namespace App\Http\Controllers\Admin;

use Anomaly\Streams\Platform\Http\Controller\AdminController;
use Anomaly\Streams\Platform\Model\Reservation\ReservationGroupsEntryModel;
use Anomaly\Streams\Platform\Model\Reservation\ReservationVisitorsEntryModel;

use Anomaly\Streams\Platform\Model\FormFeedback\FormFeedbackFeedbackDataEntryModel as feedBack;
use Carbon\Carbon as CarbonCarbon;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class DashboardController extends AdminController

{
    public function visitDetail($listId)

    {

        $dataGroup = ReservationGroupsEntryModel::query()

            ->where('id',$listId)
            ->firstOrfail();

        $dataList = ReservationVisitorsEntryModel::query()

            ->where('pic_name_id', $listId)
            ->get();

        // return $this->redirect->to('admin/visitor_details')

        //     ->with([

        //         'dataGroup' => $dataGroup,

        //         'dataList' => $dataList,

        //     ]);

        return $this->view->make('sam.theme.samcgi::layouts/list_visitors')

            ->with([

                'dataGroup' => $dataGroup,
                'dataList' => $dataList,

            ]);

    }


    public function chartFilter()
    {
        // Validasi datanya 

        // Make sure ending date tidak boleh lebih kecil dari starting date 
        // Dan starting date tidak boleh lebih besar dari ending date
        
        $starting_date = request('starting_date');
        $ending_date = request('ending_date');

        if ( $starting_date > $ending_date ) {

            return $this->redirect->back()
                ->with([
                    'error' => [
                        'name' => 'Starting date tidak boleh lebih besar dari ending date'
                    ]
                ]);
        }
        

        \request()->validate([
            'starting_date' => ['required','date_format:Y-m-d'],
            'ending_date' => ['required','date_format:Y-m-d'],
            
        ]);

        // dd($starting_date. ' | '. $ending_date);
        return back()->withInput()->with([
            'starting_date' => $starting_date,
            'ending_date' => $ending_date,
        ]);
    }
        

    public function chartShort()
    {

        // if ($this->request->has('starting_date')) {
        //     $startDay = Carbon::createFromFormat('Y-m-d', $this->request->input('starting_date'))->format('Y-m-d 00:00:00');    
        // }

        // if ($this->request->has('ending_date')) {
        //     $endDay = Carbon::createFromFormat('Y-m-d', $this->request->input('ending_date'))->format('Y-m-d 23:59:59');            
        // }
        $today = Carbon::now()->format('Y-m-d');

        $visitArrive = ReservationVisitorsEntryModel::query()
            ->where('attend', true)
            ->count();

        $visitWaiting = ReservationVisitorsEntryModel::query()
            ->where('attend', false)
            ->count();
        
        // $visitData = ReservationVisitorsEntryModel::query()
        //     ->where('age')
        //     ->first();

        //     dd($visitData->toArray());

        // $groupVisit = ReservationGroupsEntryModel::query()
        //     ->firts();

        
        // $institutionGov = ReservationVisitorsEntryModel::query()
        //     ->select()
        //     ->where('institution_category', 1)
            
        //     ->get();


        
       // dd($institutionGov);

        

        return view('sam.theme.samcgi::layouts/report_dashboard')

            ->with([
                
                'today' => $today,
                'visitArrive' => $visitArrive,
                'visitWaiting' => $visitWaiting,
                
                
            ]);

    }



}



