<?php

namespace App\Http\Controllers\Admin;
use Anomaly\Streams\Platform\Model\FormFeedback\FormFeedbackFeedbackDataEntryModel as Testimony;
use Anomaly\Streams\Platform\Http\Controller\AdminController;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Carbon;


class VisitorDataController extends AdminController 
{

    public function VisitDataExcel()

    {
        return $this->view->make("sam.theme.samcgi::/admin/exportDate/VisitorDate");
    }

    public function exportVisitors()
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

        return Excel::download(new VisitorDataExport($startingDate, $endingDate), 'Visitor Data.xlsx');
    }

}