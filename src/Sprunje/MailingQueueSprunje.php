<?php


namespace UserFrosting\Sprinkle\EmailQueue\Sprunje;

use Illuminate\Database\Schema\Builder;
use UserFrosting\Sprinkle\Core\Facades\Translator;
use UserFrosting\Sprinkle\Core\Sprunje\Sprunje;

/**
 * MailingQueueSprunje.
 *
 * Implements Sprunje for the mailing queue API.
 *
 * @author PeelValley Software (https://peelvalley.com.au)
 */
class MailingQueueSprunje extends Sprunje
{
    protected $name = 'mailing_queue';

    protected $sortable = [
        'to_email',
        'to_name'
    ];

    protected $filterable = [
        'to_email',
        'to_name'
    ];

    /**
     * {@inheritdoc}
     */
    protected function baseQuery()
    {
        return $this->classMapper->createInstance('mailing_queue');
    }



}


function map(callable $fn)
{
    $result = array();

    foreach ($this as $item) {
        $result[] = $fn($item);
    }

    return $result;
}