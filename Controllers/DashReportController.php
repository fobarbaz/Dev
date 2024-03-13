<?php


namespace App\Http\Controllers;
use Anomaly\Streams\Platform\Http\Controller\PublicController;

use Anomaly\Streams\Platform\Model\FormFeedback\FormFeedbackFeedbackDataEntryModel as feedBack;

use Illuminate\Support\Carbon;

class DashReportController extends PublicController {

    
    public function visitorsPage()
    {


        return $this->redirect->back();

    }

    public function chartShort()
    {
        $startingDate = Carbon::createFromFormat('Y-m-d', $this->request->input('starting_date'))->format('Y-m-d 00:00:00');
        $endingDate = Carbon::createFromFormat('Y-m-d', $this->request->input('ending_date'))->format('Y-m-d 23:59:59');

        if ($startingDate > $endingDate)
        {
            return $this->redirect->back()
                ->with([
                    'error' => [
                        'name' => 'Starting date should be greater than ending date'
                    ]
                ]);
        }

        $dataFeedBack = feedBack::query()
            
            
            ->get();

            //dd($dataFeedBack);

        return $this->redirect->back()->with([
            'dataFeedBack' => $dataFeedBack,
        ]);

    }


}