<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Anomaly\Streams\Platform\Http\Controller\PublicController;
use Anomaly\Streams\Platform\Model\Reservation\ReservationMainPageEntryModel;
use Anomaly\Streams\Platform\Model\Reservation\ReservationSessionEntryModel;
use Anomaly\Streams\Platform\Model\Reservation\ReservationGroupsEntryModel;
use Anomaly\Streams\Platform\Model\Reservation\ReservationVisitorsEntryModel;
use Anomaly\SettingsModule\Setting\Contract\SettingRepositoryInterface;

use Vinkla\Hashids\Facades\Hashids;


use PDF;

use Illuminate\Support\Facades\URL;
use Illuminate\Mail\Mailer;
use Carbon\Carbon;
use Illuminate\Http\Request;

use function PHPUnit\Framework\returnSelf;

class ReservationCodeController extends PublicController{

    public function reservDetail($groupDetailId){

        $groupDetailId = Hashids::decode($groupDetailId);

        //dapetin data group ID'nyaQ
        $groupData = ReservationGroupsEntryModel::query()
            ->where('id', $groupDetailId)
            ->firstOrFail();

        $visitorData = ReservationVisitorsEntryModel::query()
            ->where('pic_name_id', $groupData->id)
            ->get();

        $detailSession = ReservationSessionEntryModel::query()->first();

        //dd($groupData->toArray());

        $mainPage = ReservationMainPageEntryModel::query()->first();

       //dd($pdfConvert ->toArray());

        return view('confirmation-detail', [

            'groupData' => $groupData,
            'visitorData' => $visitorData,
            'detailSession' => $detailSession,
            'mainPage' => $mainPage,


        ]);
    }

    public function pdf($groupDetailId){

        $groupDetailId = Hashids::decode($groupDetailId);
        

        //dapetin group id'nya
        $dataConvert = ReservationGroupsEntryModel::query()
            ->where('id', $groupDetailId)
            ->firstOrFail();

        //dapetin visitornya
        $visitorData = ReservationVisitorsEntryModel::query()
            ->where('pic_name_id', $dataConvert->id)
            ->get();

        if ($dataConvert) {
            $pdf = PDF::loadView('pdf', compact(['visitorData', 'dataConvert']));

            return $pdf->stream();
        } else {
            return redirect()->back();
        }

        return view('pdf', [

            'dataConvert' => $dataConvert,
            'visitorData' => $visitorData,


        ]);
    }

    public function codeSend(Mailer $mailer, SettingRepositoryInterface $setting, $groupDetailId){

        \request()->validate([

            'email' => 'required|email:rfc,strict,dns,spoof,filter|max:100',

            'g-recaptcha-response' => ['required'],
        ], [

            'g-recaptcha-response' => 'Captcha Is Required',

        ]);

        $groupDetailId = Hashids::decode($groupDetailId);
        // dapetin Booking Code'nya
        $groupData = ReservationGroupsEntryModel::query()
            ->where('id', $groupDetailId)
            ->firstOrFail();

        $visitorData = ReservationVisitorsEntryModel::query()
            ->where('pic_name_id', $groupData->id)
            ->get();

        $expiredDate = $groupData->arrival_date->sub('hours', 72);


        // check email apakah sesuai dengan data group email nya
        if ($groupData->email != \request()->email) {
            return redirect()->back()->with([
                'error' => __('email.incorrect')
            ]);
        }


        $sender = $setting->value('streams::email');
        $senderName = $setting->value('streams::sender');


        //parsing booking codenya ke mail qeue

        $mailer->to(strtolower(\request('email')))
        ->queue(new \App\Mail\bookingNotifi([
            'groupData' => $groupData,
            'visitorData' => $visitorData,
            'booking_code' => $groupData->booking_code,
            'booking_person_name' => $groupData->booking_person_name,

        ], $sender, $senderName));

        //dd(URL::signedRoute('booking-cancel',['email' => $groupData['email']]));


        return $this->redirect->to('confirmation-code')->with([
            
            'success' => __('Your form is complete, the data has been sent successfully.'),
            
        ]);

    }

    public function verifiCode(){

        $groupData = ReservationGroupsEntryModel::query()->first();
        $mainPage = ReservationMainPageEntryModel::query()->first();


        return view ('confirmation-code',[
            'groupData' => $groupData,
            'mainPage' => $mainPage,
        ]);
    }

    public function verifiSend(Mailer $mailer, SettingRepositoryInterface $setting){

        //dapetin data inputnya
        $dataInput = request()->only(['code_booking']);

        \request()->validate([

            'code_booking' => 'required',

            'g-recaptcha-response' => ['required'],
        ], [

            'g-recaptcha-response' => 'Captcha Is Required',

        ]);

        $checkBookingCode = ReservationGroupsEntryModel::query()
            ->where('booking_code', $dataInput['code_booking'])
            ->first();

        if (!$checkBookingCode) {
            return redirect()->back()->with([
                'error' => __('theme::form.email.incorrect')
            ]);
        }

        $visitorData = ReservationVisitorsEntryModel::query()
            ->where('pic_name_id', $checkBookingCode->id)
            ->get();

        $expiredDate = $checkBookingCode->arrival_date->sub('hours', 72);


        $sender = $setting->value('streams::email');
        $senderName = $setting->value('streams::sender');


        //parsing booking codenya ke mail qeue

        $mailer->to($checkBookingCode->email)
        ->queue(new \App\Mail\bookingCancel([

            'checkBookingCode' => $checkBookingCode,
            'visitorData' => $visitorData,
            'booking_person_name' => $checkBookingCode->booking_person_name,
            'link_page' => URL::temporarySignedRoute('booking-cancel', $expiredDate, ['booking_code' => $checkBookingCode['booking_code']]),

        ], $sender, $senderName));

        // update registering status jadi true
        $updateStatus = ReservationGroupsEntryModel::query()
            ->where('booking_code', $dataInput['code_booking'])
            ->update([
                'registering_status' => true
            ]);

        if (!$updateStatus) {
            return redirect()->back()->with([
                'error' => __('theme::form.not_saved')
            ]);
        }

        return $this->redirect->to('complete-regist')->with([
            'success' => __('Your booking has complete, Check your email for cancelation booking.')
        ]);
    }

}
