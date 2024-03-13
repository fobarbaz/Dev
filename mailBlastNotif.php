<?php

namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Support\Facades\Storage;



class mailBlastNotif extends Mailable
{
    use Queueable, SerializesModels;
    private $dataBlast;
    private $sender;
    private $senderName;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($dataBlast, $sender, $senderName)

    {

        $this->dataBlast = $dataBlast;
        $this->sender = $sender;
        $this->senderName = $senderName;

    }

    /**
     * Build the message.
     *
     * @return $this

     */

    public function build()

    {
        $this->subject($this->dataBlast['mailSubject'])
            ->view('sam.theme.samcgi::layouts/email/email-template', [
                'documents' => $this->dataBlast['documents'],   
                  
                'mailDesc' => $this->dataBlast['mailDesc'],     
                'subsName' => $this->dataBlast['subsName'],     
                'subsSalutation' => $this->dataBlast['subsSalutation'],
                'unsubscribe_url' => $this->dataBlast['unsubscribe_url'],
            ])
        ->from($this->sender, $this->senderName);

        foreach ($this->dataBlast['documents'] as $document) {
            foreach($document['repeater'] as $repeater) {
                $this->attach(storage_path($repeater['pdf_fileEn']), ['as' => 'Doc -'.$repeater['title'].'-EN.pdf']);
                $this->attach(storage_path($repeater['pdf_fileId']), ['as' => 'Doc -'.$repeater['title'].'-ID.pdf']);
            }
        }

        return $this;

        // ->attach($this->dataBlast['dataDocEn'], [

        //     'as' => 'Doc -'.$this->dataBlast['title'].'.pdf',

        //     'mime' => 'application/pdf',

        // ]);



         // ->attach($this->dataBlast['dataDocEn'])

        // ->attach($this->dataBlast['dataDocId'])

    }

    // public function attachments()
    // {
    //     $attachments = [];

    //     foreach ($this->dataBlast['documents'] as $document) {
    //         foreach($document['repeater'] as $repeater) {
    //         array_push($attachments, [
    //                 Attachment::fromStorage('files/'.$repeater['downloadable_content_en']->path()),
    //                 Attachment::fromStorage('files/'.$repeater['downloadable_content_id']->path())
    //             ]);
    //         }
    //     }

    //     return $attachments;
    // }



}



