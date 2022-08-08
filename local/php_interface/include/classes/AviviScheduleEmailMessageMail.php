<?php
// Avivi #48683 Schedule email message

use Bitrix\Mail;
use Bitrix\Mail\MailMessageTable;
use Bitrix\Main;
use Bitrix\Main\Loader;

class AviviScheduleEmailMessageMail {

    protected static $isCrmEnable = false;

    public function send($data = [], $userID = 1) {
//        $rawData = (array) \Bitrix\Main\Application::getInstance()->getContext()->getRequest()->getPostList()->getRaw('data');
//        $rawData = (array) \Bitrix\Main\Application::getInstance()->getContext()->getRequest()->getPostList()->getRaw($data);
        $rawData = $data;

        $decodedData = $rawData;
        \CUtil::decodeUriComponent($decodedData);

        $hostname = self::getHostname();

        $fromEmail = $decodedData['from'];
        $fromAddress = new \Bitrix\Main\Mail\Address($fromEmail);

        if ($fromAddress->validate())
        {
            $fromEmail = $fromAddress->getEmail();

            \CBitrixComponent::includeComponentClass('bitrix:main.mail.confirm');
            if (!in_array($fromEmail, array_column(\MainMailConfirmComponent::prepareMailboxes(), 'email')))
            {
//                $this->errorCollection[] = new \Bitrix\Main\Error(Loc::getMessage('MAIL_MESSAGE_BAD_SENDER'));
//
//                return;
            }

            if ($fromAddress->getName())
            {
                $fromEncoded = sprintf(
                    '%s <%s>',
                    sprintf('=?%s?B?%s?=', SITE_CHARSET, base64_encode($fromAddress->getName())),
                    $fromEmail
                );
            }
        }
        else
        {
//            p(empty($fromEmail) ? 'MAIL_MESSAGE_EMPTY_SENDER' : 'MAIL_MESSAGE_BAD_SENDER');
//            $this->errorCollection[] = new \Bitrix\Main\Error(Loc::getMessage(
//                empty($fromEmail) ? 'MAIL_MESSAGE_EMPTY_SENDER' : 'MAIL_MESSAGE_BAD_SENDER'
//            ));

//            return;
        }

        $to  = array();
        $cc  = array();
        $bcc = array();
        $toEncoded = array();
        $ccEncoded = array();
        $bccEncoded = array();

        self::$isCrmEnable = Loader::includeModule('crm') && \CCrmPerms::isAccessEnabled();
        if (self::$isCrmEnable)
        {
            $crmCommunication = array();
        }

        foreach (array('to', 'cc', 'bcc') as $field)
        {
            if (!empty($rawData[$field]) && is_array($rawData[$field]))
            {
                $addressList = array();
                foreach ($rawData[$field] as $item)
                {
                    try
                    {
                        $item = \Bitrix\Main\Web\Json::decode($item);

                        $address = new Bitrix\Main\Mail\Address();
                        $address->setEmail($item['email']);
                        $address->setName($item['name']);

                        if ($address->validate())
                        {
                            $fieldEncoded = $field.'Encoded';

                            if ($address->getName())
                            {
                                ${$field}[] = $address->get();
                                ${$fieldEncoded}[] = $address->getEncoded();
                            }
                            else
                            {
                                ${$field}[] = $address->getEmail();
                                ${$fieldEncoded}[] = $address->getEmail();
                            }

                            $addressList[] = $address;

                            if (self::$isCrmEnable)
                            {
                                // crm only
                                if (mb_strpos($item['id'], 'CRM') === 0)
                                {
                                    $crmCommunication[] = $item;
                                }
                            }
                        }
                    }
                    catch (\Exception $e)
                    {
                    }
                }

                if (count($addressList) > 0)
                {
                    self::appendMailContacts($addressList, $field, $userID);
                }
            }
        }

        $to  = array_unique($to);
        $cc  = array_unique($cc);
        $bcc = array_unique($bcc);
        $toEncoded = array_unique($toEncoded);
        $ccEncoded = array_unique($ccEncoded);
        $bccEncoded = array_unique($bccEncoded);

        if (count($to) + count($cc) + count($bcc) > 10)
        {
//            $this->errorCollection[] = new \Bitrix\Main\Error(Loc::getMessage('MAIL_MESSAGE_TO_MANY_RECIPIENTS'));
//            return;
        }

        if (empty($to))
        {
//            $this->errorCollection[] = new \Bitrix\Main\Error(Loc::getMessage('MAIL_MESSAGE_EMPTY_RCPT'));
//            return;
        }

        $messageBody = (string) $decodedData['message'];
        $messageBodyHtml = '';
        if (!empty($messageBody))
        {
            $messageBody = preg_replace('/<!--.*?-->/is', '', $messageBody);
            $messageBody = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $messageBody);
            $messageBody = preg_replace('/<title[^>]*>.*?<\/title>/is', '', $messageBody);

            $sanitizer = new \CBXSanitizer();
            $sanitizer->setLevel(\CBXSanitizer::SECURE_LEVEL_LOW);
            $sanitizer->applyDoubleEncode(false);
            $sanitizer->addTags(array('style' => array()));

            $messageBody = $sanitizer->sanitizeHtml($messageBody);
            $messageBodyHtml = $messageBody;
            $messageBody = preg_replace('/https?:\/\/bxacid:(n?\d+)/i', 'bxacid:\1', $messageBody);
        }

        $outgoingBody = $messageBody;

        $totalSize = 0;
        $attachments = array();
        $attachmentIds = array();
        if (!empty($data['__diskfiles']) && is_array($data['__diskfiles']) && Loader::includeModule('disk'))
        {
            foreach ($data['__diskfiles'] as $item)
            {
                if (!preg_match('/n\d+/i', $item))
                {
                    continue;
                }

                $id = ltrim($item, 'n');

                if (!($diskFile = \Bitrix\Disk\File::loadById($id)))
                {
                    continue;
                }

                if (!($file = \CFile::makeFileArray($diskFile->getFileId())))
                {
                    continue;
                }

                $totalSize += $diskFile->getSize();

                $attachmentIds[] = $id;

                $contentId = sprintf(
                    'bxacid.%s@%s.mail',
                    hash('crc32b', $file['external_id'].$file['size'].$file['name']),
                    hash('crc32b', $hostname)
                );

                $attachments[] = array(
                    'ID'           => $contentId,
                    'NAME'         => $diskFile->getName(),
                    'PATH'         => $file['tmp_name'],
                    'CONTENT_TYPE' => $file['type'],
                );

                $outgoingBody = preg_replace(
                    sprintf('/(https?:\/\/)?bxacid:n?%u/i', $id),
                    sprintf('cid:%s', $contentId),
                    $outgoingBody
                );
            }
        }

        $maxSize = (int) Main\Config\Option::get('main', 'max_file_size', 0);
        $maxSizeAfterEncoding = floor($maxSize/4)*3;
        if ($maxSize > 0 && $maxSize <= ceil($totalSize / 3) * 4) // base64 coef.
        {
//            $this->errorCollection[] = new \Bitrix\Main\Error(Loc::getMessage(
//                'MAIL_MESSAGE_MAX_SIZE_EXCEED',
//                ['#SIZE#' => \CFile::formatSize($maxSizeAfterEncoding,1)]
//            ));
//            return;
        }

        // @TODO: improve mailbox detection

        if ($data['MAILBOX_ID'] > 0)
        {
            if ($mailbox = Mail\MailboxTable::getUserMailbox($data['MAILBOX_ID']))
            {
                $mailboxHelper = Mail\Helper\Mailbox::createInstance($mailbox['ID'], false);
            }
        }

        if (empty($mailboxHelper))
        {
            foreach (Mail\MailboxTable::getUserMailboxes() as $mailbox)
            {
                if ($fromEmail == $mailbox['EMAIL'])
                {
                    $mailboxHelper = Mail\Helper\Mailbox::createInstance($mailbox['ID'], false);
                    break;
                }
            }
        }

        $outgoingParams = array(
            'CHARSET'      => SITE_CHARSET,
            'CONTENT_TYPE' => 'html',
            'ATTACHMENT'   => $attachments,
            'TO'           => implode(', ', $toEncoded),
            'SUBJECT'      => $data['subject'],
            'BODY'         => $outgoingBody,
            'HEADER'       => array(
                'From'       => $fromEncoded ?: $fromEmail,
                'Reply-To'   => $fromEncoded ?: $fromEmail,
                //'To'         => join(', ', $to),
                'Cc'         => implode(', ', $ccEncoded),
                'Bcc'        => implode(', ', $bccEncoded),
                //'Subject'    => $data['subject'],
                //'Message-Id' => $messageId,
                'In-Reply-To' => sprintf('<%s>', $data['IN_REPLY_TO']),
            ),
        );

        $messageBindings = array();

        // crm activity
        if (self::$isCrmEnable && count($crmCommunication) > 0)
        {
            $messageFields = array_merge(
                $outgoingParams,
                array(
                    'BODY' => $messageBodyHtml,
                    'FROM' => $fromEmail,
                    'TO' => $to,
                    'CC' => $cc,
                    'BCC' => $bcc,
                    'IMPORTANT' => !empty($data['important']),
                    'STORAGE_TYPE_ID' => \Bitrix\Crm\Integration\StorageType::Disk,
                    'STORAGE_ELEMENT_IDS' => $attachmentIds,
                )
            );
            $activityFields = array(
                'COMMUNICATIONS' => $crmCommunication,
            );

            if (\CCrmEMail::createOutgoingMessageActivity($messageFields, $activityFields) !== true)
            {
                if (!empty($activityFields['ERROR_TEXT']))
                {
//                    $this->errorCollection[] = new \Bitrix\Main\Error($activityFields['ERROR_TEXT']);
                }
                elseif (!empty($activityFields['ERROR_CODE']))
                {
//                    $this->errorCollection[] = new \Bitrix\Main\Error(Loc::getMessage('MAIL_CLIENT_' . $activityFields['ERROR_CODE']));
                }
                else
                {
//                    $this->errorCollection[] = new \Bitrix\Main\Error(Loc::getMessage('MAIL_CLIENT_ACTIVITY_CREATE_ERROR'));
                }

//                return;
            }

            $messageBindings[] = Mail\Internals\MessageAccessTable::ENTITY_TYPE_CRM_ACTIVITY;

            //$activityId = $activityFields['ID'];
            //$urn = $messageFields['URN'];
            $messageId = $messageFields['MSG_ID'];
        }
        else
        {
            $messageId = self::generateMessageId($hostname);
        }

        $outgoingParams['HEADER']['Message-Id'] = $messageId;

        if (empty($mailboxHelper))
        {
            $context = new Main\Mail\Context();
            $context->setCategory(Main\Mail\Context::CAT_EXTERNAL);
            $context->setPriority(
                isset($addressList) && count($addressList) > 2
                    ? Main\Mail\Context::PRIORITY_LOW
                    : Main\Mail\Context::PRIORITY_NORMAL
            );

            $result = Main\Mail\Mail::send(array_merge(
                $outgoingParams,
                array(
                    'CONTEXT' => $context,
                )
            ));
        }
        else
        {
            $eventKey = Main\EventManager::getInstance()->addEventHandler(
                'mail',
                'onBeforeUserFieldSave',
                function (\Bitrix\Main\Event $event) use (&$messageBindings)
                {
                    $params = $event->getParameters();
                    $messageBindings[] = $params['entity_type'];
                }
            );

            $result = $mailboxHelper->mail(array_merge(
                $outgoingParams,
                array(
                    'HEADER' => array_merge(
                        $outgoingParams['HEADER'],
                        array(
                            'To' => $outgoingParams['TO'],
                            'Subject' => $outgoingParams['SUBJECT'],
                        )
                    ),
                )
            ));

            Main\EventManager::getInstance()->removeEventHandler('mail', 'onBeforeUserFieldSave', $eventKey);
        }

        addEventToStatFile(
            'mail',
            (empty($data['IN_REPLY_TO']) ? 'send_message' : 'send_reply'),
            join(',', array_unique(array_filter($messageBindings))),
            trim(trim($messageId), '<>')
        );

//        return;
        return true;
    }

    protected function getHostname() {
        static $hostname;
        if (empty($hostname))
        {
            $hostname = \COption::getOptionString('main', 'server_name', '') ?: 'localhost';
            if (defined('BX24_HOST_NAME') && BX24_HOST_NAME != '')
            {
                $hostname = BX24_HOST_NAME;
            }
            elseif (defined('SITE_SERVER_NAME') && SITE_SERVER_NAME != '')
            {
                $hostname = SITE_SERVER_NAME;
            }
        }

        return $hostname;
    }

    private function appendMailContacts($addressList, $fromField = '', $userID) {
        $fromField = mb_strtoupper($fromField);
        if (
            !in_array(
                $fromField,
                array(
                    \Bitrix\Mail\Internals\MailContactTable::ADDED_TYPE_TO,
                    \Bitrix\Mail\Internals\MailContactTable::ADDED_TYPE_CC,
                    \Bitrix\Mail\Internals\MailContactTable::ADDED_TYPE_BCC,
                )
            )
        )
        {
            $fromField = \Bitrix\Mail\Internals\MailContactTable::ADDED_TYPE_TO;
        }

        $allEmails = array();
        $contactsData = array();

        /**
         * @var \Bitrix\Main\Mail\Address $address
         */
        foreach ($addressList as $address)
        {
            $allEmails[] = mb_strtolower($address->getEmail());
            $contactsData[] = array(
                'USER_ID' => $userID,
                'NAME' => $address->getName(),
                'ICON' => \Bitrix\Mail\Helper\MailContact::getIconData($address->getEmail(), $address->getName()),
                'EMAIL' => $address->getEmail(),
                'ADDED_FROM' => $fromField,
            );
        }

        \Bitrix\Mail\Internals\MailContactTable::addContactsBatch($contactsData);

        $mailContacts = \Bitrix\Mail\Internals\MailContactTable::query()
            ->addSelect('ID')
            ->where('USER_ID', $userID)
            ->whereIn('EMAIL', $allEmails)
            ->exec();

        $lastRcpt = array();
        while ($contact = $mailContacts->fetch())
        {
            $lastRcpt[] = 'MC'. $contact['ID'];
        }

        if (count($lastRcpt) > 0)
        {
            \Bitrix\Main\FinderDestTable::merge(array(
                'USER_ID' => $userID,
                'CONTEXT' => 'MAIL_LAST_RCPT',
                'CODE' => $lastRcpt,
            ));
        }
    }

    private function generateMessageId($hostname) {
        // @TODO: more entropy
        return sprintf(
            '<bx.mail.%x.%x@%s>',
            time(),
            rand(0, 0xffffff),
            $hostname
        );
    }

}