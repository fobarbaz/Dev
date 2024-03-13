<?php


namespace App\Http\Controllers;
use Anomaly\Streams\Platform\Http\Controller\PublicController;

use Anomaly\Streams\Platform\Model\Faq\FaqMainPageEntryModel;
use Anomaly\Streams\Platform\Model\Faq\FaqFaqContentEntryModel;



class FaqController extends PublicController{

    public function faq(){

        $mainPage = FaqMainPageEntryModel::query()->first();

        $faqQuest = FaqFaqContentEntryModel::query()

            ->where('status', true)
            ->orderBy('sort_order', 'desc')
            ->get();
        
        return view('faq', [

            'mainPage' =>$mainPage,
            'faqQuest' =>$faqQuest

        ]);

    }

}