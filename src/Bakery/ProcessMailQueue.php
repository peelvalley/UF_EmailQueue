<?php


namespace UserFrosting\Sprinkle\EmailQueue\Bakery;

use Carbon\Carbon;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use UserFrosting\Sprinkle\Core\Mail\TwigMailMessage;
use UserFrosting\System\Bakery\BaseCommand;
use Spipu\Html2Pdf\Html2Pdf;
use \UserFrosting\Sprinkle\Core\Facades\Debug;



class ProcessMailQueue extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('process-mail-queue')
             ->setDescription('Process outgoing email queue')
             ->setHelp('This command proccesses the outgoing email queue, sending all pending emails.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Processing Email Queue');

        /** @var \UserFrosting\Support\Repository\Repository */
        $config = $this->ci->config;

        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        $mailer = $this->ci->mailer;
        $phpMailer = $mailer->getPhpMailer();
        $queueCount = $classMapper->staticMethod('mailing_queue','count');

        while ($mailItem = $classMapper->getClassMapping('mailing_queue')::whereNull('metadata->status')->orWhere('metadata->status', '!=', 'error')->first()) {
            $remaining = $classMapper->staticMethod('mailing_queue','count');
            $completed = $queueCount - $remaining +1;
            $this->io->writeln("Sending item {$completed} of {$queueCount}");

            try {
                // Create and send email
                $message = (new TwigMailMessage($this->ci->view, $mailItem->template))
                    ->from($mailItem->from ? [
                        'email' => $mailItem->from['email'],
                        'name' => $mailItem->from['name']

                    ] : $config['address_book.admin'])
                    ->addParams(
                        array_merge($mailItem->data ?? [], ... array_map(function ($paramInfo) use ($classMapper) {
                                return [
                                    $paramInfo['paramName'] => call_user_func_array(
                                        array(
                                            $classMapper,
                                            $paramInfo['function']),
                                        $paramInfo['functionParams']
                                        )
                                ];
                            }, $mailItem->data['params'] ?? [])
                        )
                    );

                foreach ($mailItem->getRecipients() as $recipient) {
                    $message->addEmailRecipient($recipient);
                }

                foreach ($mailItem->attachments as $attachment) {
                    if ($attachment['type'] == 'pdf') {
                        $pdf = $this->generatePDF($attachment['template'],
                            array_merge($attachment['data'] ?? [], ... array_map(function ($paramInfo) use ($classMapper) {
                                return [
                                    $paramInfo['paramName'] => call_user_func_array(
                                        array(
                                            $classMapper,
                                            $paramInfo['function']),
                                        $paramInfo['functionParams']
                                        )
                                    ];
                                }, $attachment['params']) ?? []
                            )
                        );
                        $phpMailer->addStringAttachment($pdf->output(NULL, 'S'), $attachment['filename']);
                    } else if ($attachment['type'] == 'upload') {
                        $upload_id = $attachment['upload_id'];
                        $upload = $classMapper->staticMethod('upload', 'find', $upload_id);
                        if(!$upload) {
                            throw new \Exception("Upload {$upload_id} not found");
                        }
                        $phpMailer->addStringAttachment(stream_get_contents($upload->getReadStream()), $upload->upload_name);
                    } else {
                        throw new \Exception("{$attachment['type']} not implemented");
                    }
                }

                $mailer->send($message);
                $mailItem->delete();
                $this->io->success("Email sent");

            } catch (\Throwable $error) {
                $this->io->error("Unable to send email: {$error->getMessage()}");
                $mailItem->update(['metadata->status' => 'error']);
                $mailItem->update(['metadata->error' => $error->getMessage()]);
                $mailItem->update(['metadata->line' => $error->getLine()]);
                $mailItem->update(['metadata->file' => $error->getFile()]);
                $mailItem->update(['metadata->trace' => $error->getTrace()]);

                $mailItem->save();
                $phpMailer->clearAllRecipients();
            }

            $phpMailer->clearAttachments();

        }
    }
    private function generatePDF($template, $params=[])
    {
        $pdf = new Html2Pdf('P', 'A4', 'en');

        $contents = $this->ci->view->fetch($template, $params);

        $pdf->writeHTML($contents);

        return $pdf;
    }
}

