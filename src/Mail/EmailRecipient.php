<?php


namespace UserFrosting\Sprinkle\EmailQueue\Mail;

use UserFrosting\Sprinkle\Core\Mail\EmailRecipient as CoreEmailRecipient;

/**
 * EmailRecipient Class.
 *
 * A class representing a recipient for a MailMessage, with associated parameters.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class EmailRecipient extends CoreEmailRecipient implements \JsonSerializable
{

    public function jsonSerialize() {
        return [
            'email' => $this->email,
            'name' => $this->name,
            'params' => $this->params,
            'cc' => $this->cc,
            'bcc' => $this->bcc
        ];
    }

    public static function fromData ($data) {
        if (is_string($data)) {
            $data = json_decode($data, true);
        }

        $recipient = new EmailRecipient(
            $data['email'],
            $data['name'],
            $data['params']
        );

        foreach ($data['cc'] as $cc) {
            $recipient->cc($cc['email'], $cc['name']);
        }

        foreach ($data['bcc'] as $bcc) {
            $recipient->bcc($bcc['email'], $bcc['name']);
        }
    }

}
