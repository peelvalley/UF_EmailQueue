<?php

namespace UserFrosting\Sprinkle\EmailQueue\ServicesProvider;

use UserFrosting\Sprinkle\Ems\Twig\EmsExtension;

/**
 * Registers services for the pis sprinkle.
 *
 */
class ServicesProvider
{
    /**
     *
     * @param Container $container A DI container implementing ArrayAccess and container-interop.
     */
    public function register($container)
    {
        $container->extend('classMapper', function ($classMapper, $c) {
            $classMapper->setClassMapping('mailing_queue', 'UserFrosting\Sprinkle\EmailQueue\Database\Models\MailingQueue');
            $classMapper->setClassMapping('mailing_queue_sprunje', 'UserFrosting\Sprinkle\EmailQueue\Sprunje\MailingQueueSprunje');

            return $classMapper;
        });
    }
}