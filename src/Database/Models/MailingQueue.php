<?php


namespace UserFrosting\Sprinkle\EmailQueue\Database\Models;

use Illuminate\Database\Eloquent\Builder;
use UserFrosting\Sprinkle\Core\Database\Models\Model;


class MailingQueue extends Model
{
    /**
     * @var string The name of the table for the current model.
     */
    protected $table = 'mailing_queue';

    protected $fillable = [
        'template',
        'to',
        'from',
        'cc',
        'bcc',
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
        'to' => 'array',
        'from' => 'array',
        'cc' => 'array',
        'bcc' => 'array',
        'data' => 'array',
        'metadata' => 'array',
        'attachments' => 'array'
    ];

    public function getEmailAttribute() {
        return $this->to[0];
    }
}