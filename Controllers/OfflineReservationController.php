<?php

namespace App\Http\Controllers;

use Anomaly\AddonsModule\Console\Update;
use Illuminate\Support\Facades\Validator;

use Anomaly\Streams\Platform\Http\Controller\PublicController;
use Anomaly\Streams\Platform\Model\Reservation\ReservationMainPageEntryModel;
use Anomaly\Streams\Platform\Model\Reservation\ReservationSessionEntryModel;
use Anomaly\Streams\Platform\Model\Reservation\ReservationGroupsEntryModel;
use Anomaly\Streams\Platform\Model\Reservation\ReservationVisitorsEntryModel;

use Carbon\Carbon;
use Illuminate\Http\Request;

class OfflineReservationController extends PublicController {


    public function offlineReserv(){

        $mainPage = ReservationMainPageEntryModel::query()->first();

        

        $date = Carbon::now();
        $formatedDate = $date->format('Y-m-d');

        $session = ReservationSessionEntryModel::query()
            ->get();

        //dd($formatedDate);

        return view('new-offline', [

            'formatedDate' => $formatedDate,
            'session' => $session,
            'mainPage' => $mainPage,

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



        $data['select_session_id'] = \request('select_session');
        $data['booking_code'] = $this->generateCode(6);
        $data['arrival_date'] = Carbon::now();


        $reservation = ReservationGroupsEntryModel::query()
            ->create($data);

        if ($dataVisitor['visitor_name']) {
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
                $visitorData['attend'] = true;

                $createVisitor = ReservationVisitorsEntryModel::query()
                    ->create($visitorData);
            }

        }

        if (!$reservation) {
            return $this->redirect->back()->with([
                'error' => __('theme::form.error')
            ]);
        }

        return redirect()->to('check-in-detail')->with([

            'success' => __('Your booking has success'),
         ]);

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
}
