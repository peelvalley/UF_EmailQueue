<?php

use UserFrosting\Sprinkle\Core\Util\NoCache;


$app->group('/mailing_queue', function () {
    $this->get('', 'UserFrosting\Sprinkle\EmailQueue\Controller\MailingQueueController:pageList')
            ->setName('uri_mailing_queue');
})->add('authGuard')->add(new NoCache());

$app->group('/api/mailing_queue', function () {
    $this->get('', 'UserFrosting\Sprinkle\EmailQueue\Controller\MailingQueueController:getList');

    $this->get('/mq/{mq_id}', 'UserFrosting\Sprinkle\EmailQueue\Controller\MailingQueueController:getInfo');

    $this->delete('', 'UserFrosting\Sprinkle\EmailQueue\Controller\MailingQueueController:deleteAll');

    $this->delete('/mq/{mq_id}', 'UserFrosting\Sprinkle\EmailQueue\Controller\MailingQueueController:delete');

})->add('authGuard')->add(new NoCache());

$app->group('/modals/mailing_queue', function () {
    $this->get('/confirm-delete', 'UserFrosting\Sprinkle\EmailQueue\Controller\MailingQueueController:getModalConfirmDelete');

    $this->get('/confirm-delete-all', 'UserFrosting\Sprinkle\EmailQueue\Controller\MailingQueueController:getModalConfirmDeleteAll');

})->add('authGuard')->add(new NoCache());