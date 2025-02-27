<?php

declare(strict_types=1);

namespace App\Services;

use Brevo\Client\Model\SendSmtpEmail;
use Brevo\Client\Model\SendSmtpEmailBcc;
use Brevo\Client\Model\SendSmtpEmailCc;
use Brevo\Client\Model\SendSmtpEmailSender;
use Brevo\Client\Model\SendSmtpEmailTo;
use Hofmannsven\Brevo\Brevo;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

class BrevoTransport extends AbstractTransport
{
    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());
        $brevo = new Brevo;

        $mail = new SendSmtpEmail;
        $mail->setSubject($email->getSubject());
        $mail->setHtmlContent($email->getHtmlBody());

        $tos = [];
        foreach ($email->getTo() as $to) {
            $brevoTo = new SendSmtpEmailTo;
            $brevoTo->setEmail($to->getAddress());
            $brevoTo->setName($to->getName() == '' ? null : $to->getName());
            $tos[] = $brevoTo;
        }

        $mail->setTo($tos);

        $bccs = [];
        foreach ($email->getBcc() as $bccs) {
            $brevoBcc = new SendSmtpEmailBcc;
            $brevoBcc->setEmail($bccs->getAddress());
            $brevoBcc->setName($bccs->getName() == '' ? null : $bccs->getName());
            $bccs[] = $brevoBcc;
        }

        $mail->setBcc($bccs);

        $ccs = [];
        foreach ($email->getCc() as $ccs) {
            $brevoCc = new SendSmtpEmailCc;
            $brevoCc->setEmail($ccs->getAddress());
            $brevoCc->setName($ccs->getName() == '' ? null : $ccs->getName());
            $ccs[] = $brevoCc;
        }

        $mail->setBcc($ccs);

        $sender = new SendSmtpEmailSender;
        $sender->setEmail($email->getFrom()[0]->getAddress());
        $sender->setName($email->getFrom()[0]->getName() == '' ? null : $to->getName());
        $mail->setSender($sender);

        $brevo->TransactionalEmailsApi()->sendTransacEmail($mail);
    }

    public function __toString(): string
    {
        return 'brevo';
    }
}
