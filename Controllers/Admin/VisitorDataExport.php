<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Contracts\View\View;

use Maatwebsite\Excel\Concerns\FromView;

use Maatwebsite\Excel\Concerns\FromCollection;

use Anomaly\Streams\Platform\Model\Reservation\ReservationGroupsEntryModel as ListVisitorEx;

use Anomaly\Streams\Platform\Model\Reservation\ReservationVisitorsEntryModel as Visitors;



class VisitorDataExport implements FromView 

{

    protected $startingDate;

    protected $endingDate;

    

    public function __construct($startingDate, $endingDate)

    {

        $this->startingDate = $startingDate;

        $this->endingDate = $endingDate;

    }





    public function view(): View

    {

        $groupData = ListVisitorEx::query()

            ->whereBetween('arrival_date', [$this->startingDate, $this->endingDate])

            ->orderBy('arrival_date', 'asc')

            ->get();



        // $groupList = ListVisitorEx::query()

        //     ->whereIn('')

        //     ->firstOrfail();

        

        // $visitorListData = Visitors::query()

        //     ->where('pic_name_id', $groupList->id )

        //     ->get();



        //     dd($visitorListData->toArray());



        return view('sam.theme.samcgi::admin/exportVisitor')

            ->with([

                'groupData' => $groupData,

                // 'groupList' => $groupList,

                // 'visitorListData' => $visitorListData,

            ]);

    }

}