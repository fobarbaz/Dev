<?php

namespace App\Http\Controllers;
use App\ReservationGroup;
use Illuminate\Support\Facades\Validator;

use Anomaly\Streams\Platform\Http\Controller\PublicController;
use Anomaly\Streams\Platform\Model\Reservation\ReservationMainPageEntryModel;
use Anomaly\Streams\Platform\Model\Reservation\ReservationSessionEntryModel;
use Anomaly\Streams\Platform\Model\Reservation\ReservationGroupsEntryModel;
use Anomaly\Streams\Platform\Model\Reservation\ReservationVisitorsEntryModel;
use Anomaly\Streams\Platform\Model\Reservation\ReservationDayOffEntryModel;
use Anomaly\Streams\Platform\Model\Reservation\ReservationSettingsEntryModel;

use Vinkla\Hashids\Facades\Hashids;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Psy\Formatter\Formatter;

class ReservationController extends PublicController
{
    public function page(){



        $mainPage = ReservationMainPageEntryModel::query()->first();

        $session = ReservationSessionEntryModel::query()
            ->get();

        $dataInterval = ReservationSettingsEntryModel::query()
            ->first();

        $disableDate = ReservationDayOffEntryModel::query()
            ->where('status_day', true)
            ->get()
            ->transform(function ($data) {
                return [
                    $data['day_disable']->format('Y-m-d')
                ];
            });

        $allowDays = ReservationSettingsEntryModel::query()
            ->get()
            ->transform(function ($data) {
                return [
                    'allowDays' => $data['allow_days']
                ];
            });


        return view('reservation', [

            'mainPage' => $mainPage,
            'dataInterval' => $dataInterval,


            'session' => $session,
            'disableDate' => collect($disableDate->toArray())->flatten(),
            'allowDays' => collect($allowDays->toArray())->flatten(),

        ]);
    }

    public function send(){
        //dapetin datanya
        $dataReservation = request()->only([
            'arrival_date',
            'select_session',
            'group_name',
            'total_visitor',
            'booking_person_name',
            'address',
            'email',
        ]);

        $boleh = $this->checkVistor($dataReservation['arrival_date'], $dataReservation['select_session']);

        // kalo ga boleh redirect back
        if (false === $boleh) {
            return $this->redirect->back()->with([
                'visitor_full' => 'Error FUll'
            ]);
        }

//         dd('lanjut ke proses dibawah');

        $dataVisitor = request()->only([
            'visitor_name','gender','age','job_title','institution_category','phone_number',
        ]);

        \request()->validate([

            'arrival_date' => 'required|date_format:Y-m-d',
            'select_session' => 'required',
            'group_name' => 'required|min:3|max:120',
            'total_visitor' => 'required|min:1|max:16',
            'booking_person_name' => 'required|max:20',
            'address' => 'required|max:500',
            'email' => 'required|email:rfc,strict,dns,spoof,filter|max:100',

            'visitor_name.*' => 'required|max:100',
            'gender.*' => 'required|in:m,f',
            'age.*' => 'required|min:1',
            'job_title.*' => 'required|max:100',
            'institution_category.*' => 'required',
            'phone_number.*' => ['required', 'regex:/^([0-9\s\-\+\(\)]*)$/', 'min:10'],


            'g-recaptcha-response' => ['required'],


        ], [

            'g-recaptcha-response' => 'Captcha Is Required',

        ]);

        $data = collect($dataReservation)->except(['g-recaptcha-response', 'select_session'])->toArray();


//        $data['arrival_date'] = Carbon::createFromFormat('d-M-Y', $dataReservation['arrival_date'])->format('Y-m-d');
        $data['total_visitor'] = count(\request('visitor_name'));
        $data['select_session_id'] = \request('select_session');
        $data['booking_code'] = $this->generateCode(6);


        $reservation = ReservationGroupsEntryModel::query()
            ->create($data);

        if (count($dataVisitor['visitor_name']) > 0) {
            foreach ($dataVisitor['visitor_name'] as $item => $value) {
                $visitorData = [
                    'visitor_name' => $dataVisitor['visitor_name'][$item],
                    'gender' => $dataVisitor['gender'][$item],
                    'age' => $dataVisitor['age'][$item],
                    'job_title' => $dataVisitor['job_title'][$item],
                    'institution_category' => $dataVisitor['institution_category'][$item],
                    'phone_number' => $dataVisitor['phone_number'][$item],
                ];

                $visitorData['pic_name_id'] = $reservation->id;

                $createVisitor = ReservationVisitorsEntryModel::query()
                    ->create($visitorData);
            }

        }

        //dd($visitorData);

//        $hashids = new Hashids();

        $reservation = Hashids::encode($reservation->id);

        //dd($reservation);


        if (!$reservation) {
            return $this->redirect->back()->with([
                'error' => __('theme::form.error')
            ]);
        }

        //sukses lanjut ke next page

         return redirect()->route('detail-reservation', [$reservation])->with([

            'success' => __('success'),
         ]);

//        return view('reservation', [
//            'reservation' => $reservation,
//            'visitorData' => $visitorData,
//
//        ]);

    }

    /**
     * @param $size
     * @return string length of code
     */
    private function generateCode($size)
    {
        do {
            $code = $this->randomString($size);
            $exists = ReservationGroupsEntryModel::query()
                ->where('booking_code', $code)
                ->exists();
        } while ($exists);

        return $code;
    }


    /**
     * @param $size
     * @return string lenght of code
     */
    private function randomString($size)
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $size; $i++) {
            $randomString .= $characters[mt_rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    private function checkVistor($date, $session, $totalVisitor = 20)
    {
        // dapetin data
//        $formatDate = Carbon::createFromFormat('d-M-Y', $date)->format('Y-m-d');

        // check ada apa ga di tgl dan session ny
        $group = ReservationGroupsEntryModel::query()
            ->whereDate('arrival_date', $date)
            ->where('select_session_id', $session)
            ->first();

        // klo ga ada boleh isi
        if (!$group) {
            if (count(\request('visitor_name')) > $totalVisitor) {
                return false;
            }
            return true;
        }

        if ((int)$group->total_visitor >= $totalVisitor) {
            return false;
        } else {
            return true;
        }


    }

    public function apiTotalVisitor($sessionID, $arrivalDate)
    {
        if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $arrivalDate)) {
            return response()->json([
                'error' => __('please choose the date')
            ], 422);
        }

        $groupData = ReservationGroupsEntryModel::query()
            ->where('select_session_id', $sessionID)
            ->whereDate('arrival_date', $arrivalDate)
            ->get(['id']);

        $dataID = collect($groupData->toArray())->flatten();

        $totalVisitor = ReservationVisitorsEntryModel::query()
            ->whereIn('pic_name_id', $dataID)
            ->count();

        $maxQuota = ReservationSessionEntryModel::query()->find($sessionID)->quota;

//        $sisa = (int) $maxQuota - $totalVisitor;


        return response()->json([
            'max_quota' => (int)$maxQuota,
            'sisa_visitor' => $totalVisitor
        ]);

    }

    public function apiKuotaHarian(){
        $now = Carbon::now();

        $totalQuota = ReservationSessionEntryModel::query()->get(['quota'])->sum('quota');

        $dataInterval = ReservationSettingsEntryModel::query()
            ->first();



        $dataGroup = ReservationGroup::query()
            ->whereDate('arrival_date', '>=', $now->addDays($dataInterval->date_interval)->format('Y-m-d') )
            ->orderBy('arrival_date')
            ->get();

         //dd($dataInterval->toArray());

        $data = [];
        foreach ($dataGroup as $group => $value) {


            $session1 = ReservationGroup::query()
                ->whereDate('arrival_date', $value->arrival_date->format('Y-m-d') )
                ->where('select_session_id',  1 )
                ->get(['id']);

            $session2 = ReservationGroup::query()
                ->whereDate('arrival_date', $value->arrival_date->format('Y-m-d') )
                ->where('select_session_id',  2 )
                ->get(['id']);

            $groupIdSession1 = collect($session1->toArray())->flatten();
            $groupIdSession2 = collect($session2->toArray())->flatten();

            $totalVisitorSession1 = ReservationVisitorsEntryModel::query()
                ->whereIn('pic_name_id', $groupIdSession1)
                ->count();

            $totalVisitorSession2 = ReservationVisitorsEntryModel::query()
                ->whereIn('pic_name_id', $groupIdSession2)
                ->count();

            $data[$group]['start'] = $value->arrival_date->format('Y-m-d');
            $data[$group]['title'] = "sesi 1 = ".$totalVisitorSession1. " sesi 2 = ". $totalVisitorSession2;
            $data[$group]['isFull'] = ($totalQuota - ($totalVisitorSession1 + $totalVisitorSession2)) <= 0 ? true : false;
            $data[$group]['className'] = ($totalQuota - ($totalVisitorSession1 + $totalVisitorSession2)) <= 0 ? 'quota-full' : 'quota-avail';
        }

        //dd($group);

        $collections = collect($data)
            ->unique()
            ->values()
            ->transform(function ($item, $key) {
                return [
                    'id' => "event".++$key,
                    'groupId' => "group". $key,
                    'title' => $item['title'],
                    'start' => $item['start'],
                    'textColor' => "#000",
                    'className' => $item['className'], // kalo full jadi 'quota-full'
                    'color' => "transparent",
                    'allDay' => true,
                    'borderColor' => "transparent",
                    'extendedProps' => [
                        'isFull' => $item['isFull'] // kalo jumlah visitor di tgl ini >= 40 maka true klo ga false
                    ],
                ];
            });


        return response()->json($collections);

    }

    public function disableDay(){

        $dayOff = ReservationDayOffEntryModel::query()
            ->where('status_day', true)
            ->get()
            ->transform(function ($data) {
                return [
                    'date' => $data['day_disable']->format('Y-m-d')
                ];
            });

        return response()->json([
            'data' => $dayOff
        ]);

    }

    public function allowDay(){

        $dayAllow = ReservationSettingsEntryModel::query()
            ->get()
            ->transform(function ($data) {
                return [
                    'allowDays' => $data['allow_days']
                ];
            });

        return response()->json([
            'data' => $dayAllow
        ]);

    }

    public function ListVisitorEx()
    {
        return view('admin-visitor-data-exports')->with([
            'VisitorDataExport' => VisitorDataExport::query()->latest()->get()
        ]);
    }

    public function exportVisitors()
    {
        return Excel::download(new VisitorDataExport, 'Visitor Data.xlsx');
    }

}
