<?php


namespace App\Http\Controllers;
use Anomaly\Streams\Platform\Http\Controller\PublicController;
use Anomaly\Streams\Platform\Model\Reservation\ReservationGroupsEntryModel;
use Anomaly\Streams\Platform\Model\Reservation\ReservationVisitorsEntryModel;

use Illuminate\Support\Facades\URL;
use Illuminate\Mail\Mailer;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CancelReservController extends PublicController{

    public function cancel(){

        $booking_code = ReservationGroupsEntryModel::query()
            ->where('booking_code', \request('booking_code'))
            ->first();

        $today = Carbon::now();
        $expired = Carbon::createFromTimestamp(\request('expires'));

        if ( $expired < $today ) {
            return redirect()->to('/')->with([
                'error' => __('Your booking expired to cancel')
            ]);
        }

        if ( is_null($booking_code) ) {
            return redirect()->to('/')->with([
                'error' => __('Your booking has deteled')
            ]);
        }

        if (! \request()->hasValidSignature()) {

            abort(404);
        }



        // if (!$bookingCode) {
        //     return redirect()->to('/')->with([
        //         'error' => __('your booking has deleted')
        //     ]);
        // }



        //        dd(Carbon::createFromTimestamp(\request('expires'))->format('d-m-Y'));

//        dd(\request()->all());


        // $groupData = ReservationGroupsEntryModel::query()->findOrFail($groupDetailId);

        // $dataGroup = ReservationGroupsEntryModel::query()
        //     ->where('id', $groupData->id)
        //     ->first();

        // dd($dataGroup->toArray());

        return view('cancel-booking', [
            'bookingCode' => \request('booking_code')
        ]);
    }

    public function destroy($bookingCode){

        $groupData = ReservationGroupsEntryModel::query()
            ->where('booking_code', $bookingCode)
            ->first();

        //cek datanya ada apa enggak
        if (!$groupData) {
            abort(404);
        }

        //Hapus Datanya
        $deleteGroup = ReservationGroupsEntryModel::query()
            ->where('id', $groupData->id)
            ->delete();

        $deleteVistor = ReservationVisitorsEntryModel::query()
            ->where('pic_name_id', $groupData->id)
            ->delete();

        if (!$deleteGroup) {
            return redirect()->back()->with([
                'error' => __('theme::form.data.not.found')
            ]);
        }

        return $this->redirect->to('/')->with([
            'success' => __('theme::form.success.message')
        ]);
    }





}
