<?php

namespace App\Http\Controllers\Admin;

use Anomaly\SettingsModule\Setting\Contract\SettingRepositoryInterface;
use Anomaly\Streams\Platform\Http\Controller\AdminController;

use Anomaly\Streams\Platform\Model\InvestorRelation\InvestorRelationInvestorContentsEntryModel as InvestorParent;
use Anomaly\Streams\Platform\Model\InvestorRelation\InvestorRelationInvestorSubLevel1EntryModel as InvestorFirst;
use Anomaly\Streams\Platform\Model\InvestorRelation\InvestorRelationInvestorSubLevel2EntryModel as InvestorSec;

use Anomaly\Streams\Platform\Model\InvestorRelation\InvestorRelationMailBlastEntryModel as SettingBlast;
use Anomaly\Streams\Platform\Model\InvestorRelation\InvestorRelationInvestorSubscriptionEntryModel as Subscribe;
use Anomaly\Streams\Platform\Model\Repeater\RepeaterInvestorDocumentsEntryModel;

use Illuminate\Support\Facades\Storage;

use Illuminate\Mail\Mailer;
use Illuminate\Support\Facades\URL;
use Anomaly\Streams\Platform\Image\Image;
use Anomaly\Streams\Platform\Ui\Table\Component\View\Type\All;

class InvestorBlastController extends AdminController
{

    public function blast($slug, Mailer $mailer, SettingRepositoryInterface $setting)
    {

        $investor = InvestorParent::query()
            ->where('status', true)
            ->find($slug)
            ->get()
            ->reject(function($value) {
                $asset = $value->document_assets->where('blast_mail', true)->count();
                return $asset < 1;
            })
            ->transform(function ($asetDoc) {
                return [
                    'repeater' => $asetDoc->document_assets
                            ->where('blast_mail', true)
                            ->transform(function($item) {
                                return [
                                    'id' => $item->id,
                                    'title' => $item->title,
                                    'date' => $item->publish_date->format('d F y'),  // human readable date
                                    'pdf_fileEn' => 'streams/sam/files-module/local/'.$item['downloadable_content_en']->path(),
                                    'pdf_fileId' => 'streams/sam/files-module/local/'.$item['downloadable_content_id']->path(),
                                    // 'pdf_fileEn' => url()->to('files/'.urlencode($item['downloadable_content_en'])),
                                    // 'pdf_fileId' => url()->to('files/'.urlencode($item['downloadable_content_id'])),
                                ];
                    })->toArray()
                ];
            });

            


        if ($investor->count() == '' || $investor->count() == null) {
            return $this->redirect->to('admin/investor_relation/investor_contents')
                ->with([
                'error' => [
                    'messages' => 'Sorry... no document is active, please check document status !!'
                ]
                ]);
        }

        $subscribe = Subscribe::query()
            ->first();

        $settingBlast = SettingBlast::query()   
            ->first();
       

        $sender = $setting->value('streams::email');
        $senderName = $setting->value('streams::sender');

        foreach ($subscribe as $subscribes) {
            $mailer->to($subscribes->email)
                ->queue(new \App\Mail\mailBlastNotif([
                    'documents' => $investor->toArray(),
                    'subsName' => $subscribes->name,
                    'subsSalutation' => $subscribes->salutation,
                    'mailSubject' => $settingBlast->title,
                    'mailDesc' => $settingBlast->mail_desc,
                    'unsubscribe_url' => URL::signedRoute('page::unsubscribe',['email' => $subscribes->email]),
                ],
                
                $sender, $senderName));
        }

            return $this->redirect->to('admin/investor_relation/investor_contents')
                ->with([
                    'success' => [
                        'messages' => "Successfully blast the email"
                    ]
                ]);

    }



}
