<?php


namespace App\Http\Controllers;

use Anomaly\Streams\Platform\Http\Controller\PublicController;

use Anomaly\Streams\Platform\Model\FormFeedback\FormFeedbackFeedbackDataEntryModel;

use Carbon\Carbon;

class FeedBackController extends PublicController
{

    public function feedBack()
    {


        return view('form-feedback', []);
    }

    public function feedSend()
    {

        $dataFeedback = request()->only([

            'email',
            'institution',
            'age',
            'date',
            
            'how_they_know',
            'how_they_know_other',
            'knowledge_before_xev',
            'knowledge_after_xev',
            'knowledge_increases',
            'increases_other',
            'content_opinion',
            'reason_opinion',
            'presenter_score',
            'reason_score',
            'xev_center_facility',
            'reason_xev_center_facility',
            'reason_xev_center_is_worth',
            'testimoni',
            'interested_to_buy',
            'car_series',
            'car_type',
            // 'registration_process_problem',
            // 'online_registration',
            // 'feature_website',
            // 'before_attend_information',
            // 'attend_information_other',
            // 'buy_merchandise',
            // 'where_buy_merchandise',


        ]);

        //dd($dataFeedback);

        \request()->validate([

            'email' => ['required', 'email', 'regex:/^([a-zA-Z0-9\-\@\.\_]*)$/', 'max:100'],
            'institution' => ['required', "regex:/^([a-zA-Z0-9\s]*)$/", 'max:100'],
            'age' => ['required', "regex:/^([0-9]*)$/", 'integer'],
            'date' => ['required',],

            

            'how_they_know' => ['required_if:how_they_know, 1, 2, 3, 4, 6, 7'],
            'how_they_know_other' => ["regex:/^([a-zA-Z0-9\s]*)$/"],

            'knowledge_before_xev' => ['required', "regex:/^([0-9]*)$/", 'integer'],
            'knowledge_after_xev' => ['required', "regex:/^([0-9]*)$/", 'integer'],

            'knowledge_increases' => ['required_if:knowledge_increases,1,2,3,4'],

            'increases_other' => ["regex:/^([a-zA-Z0-9\s]*)$/"],
            'content_opinion' => ['required', "regex:/^([0-9]*)$/", 'integer'],
            'reason_opinion' => ['required', "regex:/^([a-zA-Z0-9\s]*)$/", 'max:100'],
            'presenter_score' => ['required', "regex:/^([0-9]*)$/", 'integer'],
            'reason_score' => ['required', "regex:/^([a-zA-Z0-9\s]*)$/"],
            'xev_center_facility' => ['required',],
            'reason_xev_center_facility' => ['required', "regex:/^([a-zA-Z0-9\s]*)$/"],
            'reason_xev_center_is_worth' => ['required', "regex:/^([a-zA-Z0-9\s]*)$/"],
            'testimoni' => ['required', "regex:/^([a-zA-Z0-9\s]*)$/"],
            'interested_to_buy' => ['required', 'in:y,n'],
            'car_series' => ['in:hev,phev,bev,fcev', "regex:/^([a-zA-Z\s]*)$/"],
            'car_type' => ["regex:/^([0-9]*)$/", 'integer'],

            // 'registration_process_problem' => ['required', "regex:/^([a-zA-Z0-9\s]*)$/", 'max:100'],
            // 'online_registration' => ['required', "regex:/^([a-zA-Z0-9\s]*)$/", 'max:100'],
            // 'feature_website' => ['required', "regex:/^([a-zA-Z0-9\s]*)$/", 'max:100'],

            // 'before_attend_information' => ['required_if:before_attend_information,1,2,3,4'],

            // 'attend_information_other' => ["regex:/^([a-zA-Z0-9\s]*)$/"],
            // 'buy_merchandise' => ['required', 'in:y,n'],
            // 'where_buy_merchandise' => ['required', 'in:1,2'],

            'g-recaptcha-response' => ['required'],


        ], [

            'g-recaptcha-response' => 'Captcha Is Required',

        ]);

        // $data = collect($dataFeedback)->except(['g-recaptcha-response'])->toArray();
        // $data['date'] = $dataFeedback->date->format('Y-m-d');

        //dd($dataFeedback);

        $feedBack = FormFeedbackFeedbackDataEntryModel::query()
            ->create($dataFeedback);



        if (!$feedBack) {
            return $this->redirect->back()->with([
                'error' => __('error')
            ]);
        }

        return $this->redirect->back()->with([
            'success' => __('success')
        ]);
    }

    public function TestimonyEx()
    {
        return view('admin-testi-data-exports')->with([
            'TestiExport' => TestiExport::query()->latest()->get()
        ]);
    }

    public function exportTestimony()
    {
        return Excel::download(new TestiExport, 'Testimony Data.xlsx');
    }
}
