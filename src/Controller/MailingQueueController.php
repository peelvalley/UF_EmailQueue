<?php

namespace UserFrosting\Sprinkle\EmailQueue\Controller;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\NotFoundException;
use UserFrosting\Fortress\Adapter\JqueryValidationAdapter;
use UserFrosting\Fortress\RequestSchema;
use UserFrosting\Fortress\RequestDataTransformer;
use UserFrosting\Fortress\ServerSideValidator;
use UserFrosting\Sprinkle\Core\Controller\SimpleController;
use UserFrosting\Support\Exception\ForbiddenException;
use UserFrosting\Support\Exception\BadRequestException;






class MailingQueueController extends SimpleController
{
    public function getList(Request $request, Response $response, $args)
    {
        // GET parameters
        $params = $request->getQueryParams();
        /** @var \UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager $authorizer */
        $authorizer = $this->ci->authorizer;
        /** @var \UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface $currentUser */
        $currentUser = $this->ci->currentUser;
        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'view_mailing_queue')) {
            throw new ForbiddenException();
        }
        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;
        $sprunje = $classMapper->createInstance('mailing_queue_sprunje', $classMapper, $params);
        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $sprunje->toResponse($response);
    }

    public function pageList(Request $request, Response $response, $args)
    {
        /** @var \UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager $authorizer */
        $authorizer = $this->ci->authorizer;
        /** @var \UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface $currentUser */
        $currentUser = $this->ci->currentUser;
        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'view_mailing_queue')) {
            throw new ForbiddenException();
        }
        return $this->ci->view->render($response, 'EmailQueue/pages/mailing-queue.html.twig', [

        ]);
    }

       protected function getMailingQueueFromParams($params)
    {
        // Load the request schema
        $schema = new RequestSchema('schema://requests/mailing-queue/get-by-id.yaml');

        // Whitelist and set parameter defaults
        $transformer = new RequestDataTransformer($schema);
        $data = $transformer->transform($params);

        // Validate, and throw exception on validation errors.
        $validator = new ServerSideValidator($schema, $this->ci->translator);
        if (!$validator->validate($data)) {
            // TODO: encapsulate the communication of error messages from ServerSideValidator to the BadRequestException
            $e = new BadRequestException();
            foreach ($validator->errors() as $idx => $field) {
                foreach($field as $eidx => $error) {
                    $e->addUserMessage($error);
                }
            }
            throw $e;
        }

        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        // Get the specified event record
        $mailingQueue = $classMapper->staticMethod('mailing_queue', 'find', $data['mq_id']);
        return $mailingQueue;
    }

}

