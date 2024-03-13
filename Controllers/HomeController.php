<?php


namespace App\Http\Controllers;
use Anomaly\Streams\Platform\Http\Controller\PublicController;
use Anomaly\Streams\Platform\Model\Home\HomeSliderBannerEntryModel;
use Anomaly\Streams\Platform\Model\Home\HomeMainPageEntryModel;
use Anomaly\Streams\Platform\Model\Home\HomeInnovationContentEntryModel;
use Anomaly\Streams\Platform\Model\Reservation\ReservationGroupsEntryModel;
use Anomaly\Streams\Platform\Model\Reservation\ReservationVisitorsEntryModel;



use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class HomeController extends PublicController{

    public function slideHome(){

        $sliderPage = HomeSliderBannerEntryModel::query()
            ->orderBy('sort_order','desc')
            ->get();

        $mainPage = HomeMainPageEntryModel::query()
            ->first();

        $inovation = HomeInnovationContentEntryModel::query()
            ->where('status', true)
            ->get();

        $groupHome = ReservationGroupsEntryModel::query()
            ->count();

        //$totalGroup = collect($groupHome->toArray())->flatten();

        //$total = (int)$totalGroup;

        $visitorHome = ReservationVisitorsEntryModel::query()
            ->count();

        //dd($visitorHome);    

        return view('home', [

            'groupHome' => $groupHome,
            'visitorHome' => $visitorHome,
            'inovation' => $inovation,
            'sliderPage' => $sliderPage,
            'mainPage' => $mainPage,

        ]);
    }

   


}