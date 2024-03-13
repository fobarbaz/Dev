<?php

namespace App\Http\Controllers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Validator;
use Anomaly\Streams\Platform\Http\Controller\PublicController;

use Anomaly\Streams\Platform\Model\Reservation\ReservationSessionEntryModel;
use Anomaly\Streams\Platform\Model\Reservation\ReservationGroupsEntryModel;
use Anomaly\Streams\Platform\Model\Reservation\ReservationVisitorsEntryModel;
use Anomaly\Streams\Platform\Model\Reservation\ReservationMainPageEntryModel;

use Carbon\Carbon;
use Doctrine\DBAL\Schema\Visitor\Visitor;
use Illuminate\Http\Request;

class CheckInReservController extends PublicController {


    public function checkIn(Guard $auth){

        if (!$auth->user()->hasAnyRole(['pic_user', 'admin'])) {
            abort(404);
        }

        $mainPage = ReservationMainPageEntryModel::query()->first();

        $date = Carbon::now();
        $formatedDate = $date->format('Y-m-d');

        $session = ReservationSessionEntryModel::query()
            ->get();

        $dataGroup = ReservationGroupsEntryModel::query()
             ->whereDate('arrival_date', Carbon::now()->format('Y-m-d'))
             ->get();

        return view('check-in-detail',[
            'formatedDate' => $formatedDate,
            'session' => $session,
            'dataGroup' => $dataGroup,
            'mainPage' => $mainPage,
        ]);

    }



    public function apiCheckSession($sessionID, $doDate){

        $dataGroup = ReservationGroupsEntryModel::query()
            ->where('select_session_id', $sessionID)
            ->whereDate('arrival_date', $doDate)
            ->get()
            ->transform(function ($data){
                return [
                    'id' => $data['id'],
                    'group_name' => $data['group_name']
                ];
            });

        return response()->json([
            'data' => $dataGroup
        ]);

    }

    public function apiGroup($groupID){
        //groupid dapetin visitor
        $dataGroup = ReservationVisitorsEntryModel::query()
            ->where('pic_name_id', $groupID)
            ->get()
            ->transform(function ($data){
                return [
                    'id' => $data['id'],
                    'group_name' => $data['pic_name']['group_name'],
                    'visitor_name' => $data['visitor_name'],
                    'age' => $data['age'],
                    'gender' => $data['gender'] == 'm' ? 'Male' : 'Female',
                    'job_title' => $data['job_title'],
                    'institution_category' => $data->getFieldTypePresenter('institution_category')->value(),
                    'phone_number' => $data['phone_number'],
                    'attend' => $data['attend'] == true ? 1 : 0,
                ];
            });

        // $dataGroup = ReservationVisitorsEntryModel::query()
        // ->where('pic_name_id', $groupID)
        // ->get();

        // $groups = [];
        // foreach ($dataGroup as $item) {
        //     $groups['id'] = $item->id;
        //     $groups['group_name'] = $item->pic_name->group_name;
        // }

        return response()->json([
            'data' => $dataGroup
        ]);

    }

    // public function apiAbsen($groupID){

    //     dd(\request()->all());
    //     $dataVisitor = ReservationVisitorsEntryModel::query()
    //         ->where('pic_name_id', $groupID)
    //         ->get();
    //         //dd($dataGroup->toArray());

    //     return response()->json([

    //         'data' => \request()->all()

    //     ]);

    // }



}

