<?php

/**
 * Description of mailer
 *
 ** @author Valentin Balt <valentin.balt@gmail.com>
 */
class Mailer extends Config
{
    const Q_MAIL = 'mail';

    public function __construct()
    {
        parent::__construct();

        $this->logger = new Logger(get_class());
    }

    public function getSwiftMailer()
    {
        return $this->configureSwift($this->getConfig('transport'));
    }

    private function configureSwift($transportName = 'mail')
    {
        if (!class_exists('Swift')) {
            if ((@include 'swift_required.php') === false) {
                require 'Swift/swift_required.php';
            }
        }

        $opt = $this->getConfig('options');

        if ($transportName == 'smtp') {
            $transport = (new Swift_SmtpTransport($opt['host'], $opt['port']))
                ->setUsername($opt['username'])
                ->setPassword($opt['password'])
                ->setEncryption($opt['encryption']);
        } else {
            $transport = new Swift_SendmailTransport();
        }

        return new Swift_Mailer($transport);

    }

    /**
     * Send email using Swift
     * @param string $address Email address to send to
     * @param string $subject Subject of email
     * @param string $text Message to send (HTML)
     * @param string $from Optional. From address. Leave empty to use "info@yourdomain.tld"
     * @param array $attachments Optional. ['name' => 'FILENAME', 'type' => 'MIME', 'data' => 'CONTENT']
     */
    public function send($address, $subject, $text, $from = null, $text_only = false, $attachments = null)
    {
        if (!$from) {
            $from = $this->getConfig('from');
        }

        $to = $address;

        $header = $this->getVar('emails', 'header');
        $footer = $this->getVar('emails', 'footer');

        $messageHTML = $header . ($text) . $footer;

        try {

            $mailer = $this->getSwiftMailer();

            $message = (new Swift_Message($subject))
                // Set To
                ->setTo($to)
                // Set From message
                ->setFrom($from);

            if ($bcc = $this->getConfig('bcc')) {
                $message->setBcc($bcc);
            }

            if (!$text_only) {
                // Add images
                if ($this->getConfig('images')) {
                    foreach ($this->getConfig('images') as $file => $info) {
                        if (stristr($messageHTML, '"' . $info['src'] . '"') !== false) {
                            $messageHTML = str_replace(
                                '"' . $info['src'] . '"',
                                '"' . $message->embed(Swift_Image::fromPath(PATH_WWW . $file)) . '"',
                                $messageHTML
                            );
                        }
                    }
                }

                // Set HTML message
                $message
                    ->setBody($messageHTML, 'text/html')
                    ->addPart(strip_tags($text));
            } else {
                $message->setBody($text);
            }

            if (!empty ($attachments)) {
                $zipped = [];
                foreach ($attachments as $adata) {
                    // check for "zip" requirements
                    if (!empty($adata['zip'])) {
                        $zipped[] = $adata;
                    } else {
                        $attachment = (new Swift_Attachment())
                            ->setFilename($adata['name'])
                            ->setContentType($adata['type'])
                            ->setBody($adata['data']);
                        $message->attach($attachment);
                    }
                }
                if (!empty($zipped)) {
                    foreach ($zipped as $k => $adata) {
                        $zip = new ZipArchive();
                        $fn = tempnam(sys_get_temp_dir(), uniqid());
                        if ($zip->open($fn, ZipArchive::CREATE) === TRUE) {
                            $zip->addFromString($adata['name'], $adata['data']);
                            $zip->close();
                            $attachment = (new Swift_Attachment())
                                ->setFilename($adata['name'] . '.zip')
                                ->setContentType('application/zip')
                                ->setBody(file_get_contents($fn));
                            $message->attach($attachment);
                        }
                        unlink($fn);
                    }
                }
            }

            $ret = $mailer->send($message);

            $this->logger->debug('Sent email to: ' . $to . ': ' . (!$ret ? 'Error' : 'OK'));
        } catch (\Exception $e) {

            $this->logger->error('Mailer error: ' . $e->getMessage());
        }
    }

    public function queueRun($task)
    {
        $i = unserialize($task['params']);

        $this->send(
            $i['email_address'],
            $i['email_subject'],
            $i['email_html'],
            empty($i['email_from']) ? null : $i['email_from'],
            !empty($i['text_only']),
            empty($i['email_attachments']) ? null : $i['email_attachments']
        );

        return true;
    }
}
