<?php


namespace App\Http\Controllers;
use Anomaly\Streams\Platform\Http\Controller\PublicController;
use Anomaly\Streams\Platform\Model\ContactUs\ContactUsMainPageEntryModel;
use Anomaly\Streams\Platform\Model\ContactUs\ContactUsInboxEntryModel;
use Anomaly\SettingsModule\Setting\Contract\SettingRepositoryInterface;
use Illuminate\Mail\Mailer;
use Carbon\Carbon;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ContactController extends PublicController{

    public function contact(){
        
        $mainPage = ContactUsMainPageEntryModel::query()->first();

        return view('contact-us', [
            'mainPage' => $mainPage,
        ]);

    }
    
    public function contactSend(Mailer $mailer, SettingRepositoryInterface $setting){

        $dataContact = request()->only([
            'first_name',
            'last_name',
            'city',
            'email',
            'message',
            
        ]);

        \request()->validate([

            'first_name' => 'required|max:100',
            'last_name' => 'required|max:100',
            'city' => 'required|min:3|max:120',
            'email' => 'required|email:rfc,strict,dns,spoof,filter|max:100',
            'message' => 'required|max:100',
            
            'g-recaptcha-response' => ['required'],


        ], [

            'g-recaptcha-response' => 'Captcha Is Required',

        ]);

        $fName = request('first_name');
        $lName = request('last_name');
        $city = request('city');
        $mail = request('email');
        $comment = request('message');

        $dataContact ['created_at'] = Carbon::now();

        $sender = $setting->value('streams::email');
        $senderName = $setting->value('streams::sender');

        $mailer->to([
            'admin.xevcenter@toyota.co.id',
            ])

        ->queue(new \App\Mail\contactPicMail([

            'first_name' => ucwords($fName),
            'last_name' => ucwords($lName),
            'city' => ucwords($city),
            'email' => strtolower($mail),
            'message' => ucwords($comment),
            

        ], $sender, $senderName));

        
        $mailer->to(
            $dataContact['email'])

        ->queue(new \App\Mail\contactMail([

            'first_name' => ucwords($fName),
            'last_name' => ucwords($lName),
            
            

        ], $sender, $senderName));



        $inbox = ContactUsInboxEntryModel::query()
                    ->create($dataContact);
        
        if (!$inbox) {
            return $this->redirect->back()->with([
                'error' => __('Ups.some.thing.worng')
            ]);
        }

        return $this->redirect->back()->with([
            'success' => __('success')
        ]);

    }

}