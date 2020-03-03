<?php


namespace UserFrosting\Sprinkle\EmailQueue\Database\Models;

use Illuminate\Database\Eloquent\Builder;
use UserFrosting\Sprinkle\Core\Database\Models\Model;
Use UserFrosting\Sprinkle\EmailQueue\Mail\EmailRecipient;
use Illuminate\Database\Capsule\Manager as Capsule;
use \UserFrosting\Sprinkle\Core\Facades\Debug;



class MailingQueue extends Model
{
    /**
     * @var string The name of the table for the current model.
     */
    protected $table = 'mailing_queue';

    protected $fillable = [
        'template',
        'from',
        'recipients',
        'data',
        'metadata',
        'metadata->error',
        'metadata->status',
        'metadata->file',
        'metadata->line',
        'metadata->trace',
        'attachments'
    ];

    /**
     * @var bool Enable timestamps for this class.
     */
    public $timestamps = true;

    protected $dates = [];

    protected $casts = [
        'recipients' => 'array',
        'from' => 'array',
        'data' => 'array',
        'metadata' => 'array',
        'attachments' => 'array'
    ];



    /**
     * Add a recipient.
     *
     * @param EmailRecipient $to
     */
    public function addRecipient(EmailRecipient $to)
    {
        $serialised = json_encode($to);
        $id = $this->id;
        $this->update(['recipients' =>  Capsule::raw("JSON_ARRAY_APPEND(`recipients`, '$', '$serialised')")]);
        $this->save();
        //     $query = "UPDATE `mailing_queue` SET recipients = JSON_ARRAY_APPEND(`recipients`, '$', '$serialised') WHERE id=$id;";
        //     Debug::debug('query', [$query]);
        //    Capsule::raw($query);
        $this->refresh();
    }

    /**
     * Get all recipient.
     */
    public function getRecipients(EmailRecipient $to)
    {
        return array_map(function ($r) {
            EmailRecipient::fromData($r);
        }, $this->recipients);
    }
}