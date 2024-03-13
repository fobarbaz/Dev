<?php


namespace App\Http\Controllers\Admin;

use Anomaly\Streams\Platform\Http\Controller\AdminController;

use Illuminate\Contracts\View\View;

use Maatwebsite\Excel\Concerns\FromView;

use Maatwebsite\Excel\Concerns\FromCollection;

use Anomaly\Streams\Platform\Model\FormFeedback\FormFeedbackFeedbackDataEntryModel as FeedbackDataEntry;



class TestiExport extends AdminController implements FromView
{

    protected $startingDate;
    protected $endingDate;

    public function __construct($startingDate, $endingDate)
    {

        $this->startingDate = $startingDate;
        $this->endingDate = $endingDate;
    }

    public function view(): View
    {

        $dataTesti = FeedbackDataEntry::query()
            ->whereBetween('created_at', [$this->startingDate, $this->endingDate])
            ->orderBy('date', 'asc')
            ->get()
            ->transform(function ($data) {
                return [
                    // sisanya lo tambah fieldnya y...
                    'date' => $data['date'],
                    'email' => $data['email'],
                    'institution' => $data->getFieldTypePresenter('institution'),
                    
                    'knowledge_increases' => $data->getFieldTypePresenter('knowledge_increases')->selections(),

                    'content_opinion' => $data->getFieldTypePresenter('content_opinion'),
                    'presenter_score' => $data->getFieldTypePresenter('presenter_score'),
                    'xev_center_facility' => $data->getFieldTypePresenter('xev_center_facility'),
                    'testimoni' => $data->getFieldTypePresenter('testimoni'),
                    'advice' => $data->getFieldTypePresenter('advice'),
                ];
            });

            //dd( $dataTesti->toArray('date'));

        return view('sam.theme.samcgi::admin/exportTesti')

            ->with([
                'dataTesti' => $dataTesti,
                
            ]);
    }
}