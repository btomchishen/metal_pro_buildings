<?php
// Avivi #48683 Schedule email message

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Config;
use Bitrix\Main\Mail;

class AviviScheduleEmailMessageCRM {

    public function send($data = [], $userID = 1) {
//        $rawData = (array) \Bitrix\Main\Application::getInstance()->getContext()->getRequest()->getPostList()->getRaw($data);
//        $curUser = CCrmSecurityHelper::GetCurrentUser();
        $rawData = $data;

        if (!CModule::IncludeModule('subscribe'))
        {

//            __CrmActivityEditorEndResponse(array('ERROR' => 'Could not load module "subscribe"!'));
        }

//        $siteID = !empty($_REQUEST['siteID']) ? $_REQUEST['siteID'] : SITE_ID;
        $siteID = SITE_ID;

//        $data = isset($_POST['DATA']) && is_array($_POST['DATA']) ? $_POST['DATA'] : array();
//        if (empty($data))
//            __CrmActivityEditorEndResponse(array('ERROR'=>'SOURCE DATA ARE NOT FOUND!'));

//        $rawData = (array) \Bitrix\Main\Application::getInstance()->getContext()->getRequest()->getPostList()->getRaw('DATA');

        $decodedData = $rawData;
        \CUtil::decodeURIComponent($decodedData);

        $ID = isset($data['ID']) ? intval($data['ID']) : 0;
        $isNew = $ID <= 0;

//        $userID = $curUser->GetID();
//        if ($userID <= 0)
//            __CrmActivityEditorEndResponse(array('ERROR' => getMessage('CRM_ACTIVITY_RESPONSIBLE_NOT_FOUND')));

        $now = convertTimeStamp(time() + \CTimeZone::getOffset(), 'FULL', $siteID);

        $subject = isset($data['subject']) ? strval($data['subject']) : '';
        if ($subject == '')
            $subject = getMessage('CRM_EMAIL_ACTION_DEFAULT_SUBJECT', array('#DATE#'=> $now));

        $arErrors = array();

        $socNetLogDestTypes = array(
            \CCrmOwnerType::LeadName    => 'leads',
            \CCrmOwnerType::DealName    => 'deals',
            \CCrmOwnerType::ContactName => 'contacts',
            \CCrmOwnerType::CompanyName => 'companies',
        );

        $to  = array();
        $cc  = array();
        $bcc = array();

        // Bindings & Communications -->
        $arBindings = array();
        $arComms = array();
        $commData = isset($data['communications']) ? $data['communications'] : array();
        foreach (array('to', 'cc', 'bcc') as $field)
        {
            if (!empty($rawData[$field]) && is_array($rawData[$field]))
            {
                foreach ($rawData[$field] as $item)
                {
                    try
                    {
                        $item = \Bitrix\Main\Web\Json::decode($item);

                        $item['entityType'] = array_search($item['entityType'], $socNetLogDestTypes);
                        $item['type'] = 'EMAIL';
                        $item['value'] = $item['email'];
                        $item['__field'] = $field;

                        $commData[] = $item;
                    }
                    catch (\Exception $e)
                    {
                    }
                }
            }
        }

        $sourceList = \CCrmStatus::getStatusList('SOURCE');
        if (isset($sourceList['EMAIL']))
        {
            $sourceId = 'EMAIL';
        }
        else if (isset($sourceList['OTHER']))
        {
            $sourceId = 'OTHER';
        }

        $contactTypes = \CCrmStatus::getStatusList('CONTACT_TYPE');
        if (isset($contactTypes['CLIENT']))
        {
            $contactType = 'CLIENT';
        }
        else if (isset($contactTypes['OTHER']))
        {
            $contactType = 'OTHER';
        }

        foreach ($commData as &$commDatum)
        {
            $commID = isset($commData['id']) ? intval($commData['id']) : 0;
            $commEntityType = isset($commDatum['entityType'])? mb_strtoupper(strval($commDatum['entityType'])) : '';
            $commEntityID = isset($commDatum['entityId']) ? intval($commDatum['entityId']) : 0;

            $commType = isset($commDatum['type'])? mb_strtoupper(strval($commDatum['type'])) : '';
            if($commType === '')
            {
                $commType = 'EMAIL';
            }
            $commValue = isset($commDatum['value']) ? strval($commDatum['value']) : '';

            if($commType === 'EMAIL' && $commValue !== '')
            {
                if(!check_email($commValue))
                {
                    $arErrors[] = GetMessage('CRM_ACTIVITY_INVALID_EMAIL', array('#VALUE#' => $commValue));
                    continue;
                }

                $rcptFieldName = 'to';
                if (isset($commDatum['__field']))
                {
                    $commDatum['__field'] = mb_strtolower($commDatum['__field']);
                    if (in_array($commDatum['__field'], array('to', 'cc', 'bcc')))
                        $rcptFieldName = $commDatum['__field'];
                }

                ${$rcptFieldName}[] = mb_strtolower(trim($commValue));
            }

            if (isset($commDatum['isEmail']) && $commDatum['isEmail'] == 'Y' && mb_strtolower(trim($commValue)))
            {
                $newEntityTypeId = \Bitrix\Crm\Settings\ActivitySettings::getCurrent()->getOutgoingEmailOwnerTypeId();
                if (\CCrmOwnerType::Contact == $newEntityTypeId && \CCrmContact::checkCreatePermission())
                {
                    $contactFields = array(
                        'NAME'           => isset($commDatum['params']['name']) ? $commDatum['params']['name'] : '',
                        'LAST_NAME'      => isset($commDatum['params']['lastName']) ? $commDatum['params']['lastName'] : '',
                        'RESPONSIBLE_ID' => $userID,
                        'FM'             => array(
                            'EMAIL' => array(
                                'n1' => array(
                                    'VALUE_TYPE' => 'WORK',
                                    'VALUE'      => mb_strtolower(trim($commValue))
                                )
                            )
                        ),
                    );

                    if ('' != $contactType)
                    {
                        $contactFields['TYPE_ID'] = $contactType;
                    }

                    if ('' != $sourceId)
                    {
                        $contactFields['SOURCE_ID'] = $sourceId;
                    }

                    if ($contactFields['NAME'] == '' && $contactFields['LAST_NAME'] == '')
                        $contactFields['NAME'] = mb_strtolower(trim($commValue));

                    $contactEntity = new \CCrmContact();
                    $contactId = $contactEntity->add(
                        $contactFields, true,
                        array(
                            'DISABLE_USER_FIELD_CHECK' => true,
                            'REGISTER_SONET_EVENT'     => true,
                            'CURRENT_USER'             => $userID,
                        )
                    );

                    if ($contactId > 0)
                    {
                        $commEntityType = \CCrmOwnerType::ContactName;
                        $commEntityID   = $contactId;

                        $bizprocErrors = array();
                        \CCrmBizProcHelper::autostartWorkflows(
                            \CCrmOwnerType::Contact, $contactId,
                            \CCrmBizProcEventType::Create,
                            $bizprocErrors
                        );
                    }
                }
                else if (\CCrmLead::checkCreatePermission())
                {
                    $leadFields = array(
                        'TITLE'          => $subject,
                        'NAME'           => isset($commDatum['params']['name']) ? $commDatum['params']['name'] : '',
                        'LAST_NAME'      => isset($commDatum['params']['lastName']) ? $commDatum['params']['lastName'] : '',
                        'STATUS_ID'      => 'NEW',
                        'OPENED'         => 'Y',
                        'FM'             => array(
                            'EMAIL' => array(
                                'n1' => array(
                                    'VALUE_TYPE' => 'WORK',
                                    'VALUE'      => mb_strtolower(trim($commValue))
                                )
                            )
                        ),
                    );

                    if ('' != $sourceId)
                    {
                        $leadFields['SOURCE_ID'] = $sourceId;
                    }

                    $leadEntity = new \CCrmLead(false);
                    $leadId = $leadEntity->add(
                        $leadFields, true,
                        array(
                            'DISABLE_USER_FIELD_CHECK' => true,
                            'REGISTER_SONET_EVENT'     => true,
                            'CURRENT_USER'             => $userID,
                        )
                    );

                    if ($leadId > 0)
                    {
                        $commEntityType = \CCrmOwnerType::LeadName;
                        $commEntityID   = $leadId;

                        $bizprocErrors = array();
                        \CCrmBizProcHelper::autostartWorkflows(
                            \CCrmOwnerType::Lead, $leadId,
                            \CCrmBizProcEventType::Create,
                            $bizprocErrors
                        );

                        $starter = new \Bitrix\Crm\Automation\Starter(\CCrmOwnerType::Lead, $leadId);
                        $starter->setUserIdFromCurrent()->runOnAdd();
                    }
                }
            }

            $key = md5(sprintf(
                '%s_%u_%s_%s',
                $commEntityType,
                $commEntityID,
                $commType,
                mb_strtolower(trim($commValue))
            ));
            $arComms[$key] = array(
                'ID' => $commID,
                'TYPE' => $commType,
                'VALUE' => $commValue,
                'ENTITY_ID' => $commEntityID,
                'ENTITY_TYPE_ID' => CCrmOwnerType::ResolveID($commEntityType)
            );

            if($commEntityType !== '')
            {
                $bindingKey = $commEntityID > 0 ? "{$commEntityType}_{$commEntityID}" : uniqid("{$commEntityType}_");
                if(!isset($arBindings[$bindingKey]))
                {
                    $arBindings[$bindingKey] = array(
                        'OWNER_TYPE_ID' => CCrmOwnerType::ResolveID($commEntityType),
                        'OWNER_ID' => $commEntityID
                    );
                }
            }
        }
        unset($commDatum);

        $to  = array_unique($to);
        $cc  = array_unique($cc);
        $bcc = array_unique($bcc);

        $blackListed =
            Mail\Internal\BlacklistTable::query()
                ->setSelect(["CODE"])
                ->whereIn("CODE",$array = array_merge_recursive($to,$cc,$bcc))
                ->exec()
                ->fetchAll()
        ;

        if (!empty($blackListed = array_column($blackListed,"CODE")))
        {
//            __CrmActivityEditorEndResponse(
//                array(
//                    "ERROR_HTML" => \Bitrix\Main\Localization\Loc::getMessage(
//                        "CRM_ACTIVITY_EMAIL_BLACKLISTED",
//                        array(
//                            "%link_start%" => "<a href=\"/settings/configs/mail_blacklist.php\">",
//                            "%link_end%" => "</a>",
//                            "%emails%" => implode("; ",$blackListed),
//                        )
//                    )
//                )
//            );
        }
        elseif (empty($to))
        {
//            __CrmActivityEditorEndResponse(
//                array('ERROR' => getMessage('CRM_ACTIVITY_EMAIL_EMPTY_TO_FIELD'))
//            );
        }
        elseif (!empty($arErrors))
        {
//            __CrmActivityEditorEndResponse(
//                array('ERROR' => $arErrors)
//            );
        }

        $ownerTypeName = isset($data['ownerType'])? mb_strtoupper(strval($data['ownerType'])) : '';
        $ownerTypeID = !empty($ownerTypeName) ? \CCrmOwnerType::resolveId($ownerTypeName) : 0;
        $ownerID = isset($data['ownerID']) ? intval($data['ownerID']) : 0;

        $bindData = isset($data['bindings']) ? $data['bindings'] : array();
        if (!empty($rawData['docs']) && is_array($rawData['docs']))
        {
            foreach ($rawData['docs'] as $item)
            {
                try
                {
                    $item = \Bitrix\Main\Web\Json::decode($item);
                    $item['entityType'] = array_search($item['entityType'], $socNetLogDestTypes);

                    $bindData[] = $item;
                }
                catch (\Exception $e)
                {
                }
            }
        }

        foreach ($bindData as $item)
        {
            $item['entityTypeId'] = \CCrmOwnerType::resolveID($item['entityType']);
            if ($item['entityTypeId'] > 0 && $item['entityId'] > 0)
            {
                $key = sprintf('%u_%u', $item['entityType'], $item['entityId']);
                if (\CCrmOwnerType::Deal == $item['entityTypeId'] && !isset($arBindings[$key]))
                {
                    $ownerTypeName = \CCrmOwnerType::resolveName($item['entityTypeId']);
                    $ownerTypeID = $item['entityTypeId'];
                    $ownerID = $item['entityId'];

                    $arBindings[$key] = array(
                        'OWNER_TYPE_ID' => $item['entityTypeId'],
                        'OWNER_ID'      => $item['entityId']
                    );
                }
            }
        }

        $nonRcptOwnerTypes = array(
            \CCrmOwnerType::Lead,
            \CCrmOwnerType::Order,
            \CCrmOwnerType::Deal,
            \CCrmOwnerType::DealRecurring,
            \CCrmOwnerType::Quote,
        );
        if (
            'Y' !== $data['ownerRcpt']
            && (in_array($ownerTypeID, $nonRcptOwnerTypes) || CCrmOwnerType::isPossibleDynamicTypeId($ownerTypeID))
            && $ownerID > 0
        )
        {
            $key = sprintf('%s_%u', $ownerTypeName, $ownerID);
            if (!isset($arBindings[$key]))
            {
                $arBindings[$key] = array(
                    'OWNER_TYPE_ID' => $ownerTypeID,
                    'OWNER_ID'      => $ownerID,
                );
            }
        }

        $ownerBinded = false;
        if ($ownerTypeID > 0 && $ownerID > 0)
        {
            foreach ($arBindings as $item)
            {
                if ($ownerTypeID == $item['OWNER_TYPE_ID'] && $ownerID == $item['OWNER_ID'])
                {
                    $ownerBinded = true;
                    break;
                }
            }
        }

        if ($ownerBinded)
        {
            $checkedOwnerType = $ownerTypeID;
            if ($ownerTypeID == \CCrmOwnerType::DealRecurring)
            {
                $checkedOwnerType = \CCrmOwnerType::Deal;
            }
            if (!\CCrmActivity::checkUpdatePermission($checkedOwnerType, $ownerID))
            {
                $errorMsg = getMessage('CRM_PERMISSION_DENIED');
                $entityTitle = \CCrmOwnerType::getCaption($ownerTypeID, $ownerID, false);

                if (\CCrmOwnerType::Contact == $ownerTypeID)
                    $errorMsg = getMessage('CRM_CONTACT_UPDATE_PERMISSION_DENIED', array('#TITLE#' => $entityTitle));
                else if (\CCrmOwnerType::Company == $ownerTypeID)
                    $errorMsg = getMessage('CRM_COMPANY_UPDATE_PERMISSION_DENIED', array('#TITLE#' => $entityTitle));
                else if (\CCrmOwnerType::Lead == $ownerTypeID)
                    $errorMsg = getMessage('CRM_LEAD_UPDATE_PERMISSION_DENIED', array('#TITLE#' => $entityTitle));
                else if (\CCrmOwnerType::Deal == $ownerTypeID || \CCrmOwnerType::DealRecurring == $ownerTypeID)
                    $errorMsg = getMessage('CRM_DEAL_UPDATE_PERMISSION_DENIED', array('#TITLE#' => $entityTitle));

//                __CrmActivityEditorEndResponse(array('ERROR' => $errorMsg));
            }
        }
        else
        {
            $ownerTypeID = 0;
            $ownerID     = 0;

            $typesPriority = array(
                \CCrmOwnerType::Deal    => 1,
                \CCrmOwnerType::Order   => 2,
                \CCrmOwnerType::Contact => 3,
                \CCrmOwnerType::Company => 4,
                \CCrmOwnerType::Lead    => 5,
            );

            foreach ($arBindings as $item)
            {
                if ($ownerTypeID <= 0 || $typesPriority[$item['OWNER_TYPE_ID']] < $typesPriority[$ownerTypeID])
                {
                    if (\CCrmActivity::checkUpdatePermission($item['OWNER_TYPE_ID'], $item['OWNER_ID']))
                    {
                        $ownerTypeID = $item['OWNER_TYPE_ID'];
                        $ownerID     = $item['OWNER_ID'];
                        $ownerBinded = true;
                    }
                }
            }

            if (!$ownerBinded)
            {
//                __CrmActivityEditorEndResponse(array(
//                    'ERROR' => getMessage(
//                        empty($arBindings)
//                            ? 'CRM_ACTIVITY_EMAIL_EMPTY_TO_FIELD'
//                            : 'CRM_PERMISSION_DENIED'
//                    ),
//                ));
            }
        }

        // single deal binding
        $dealBinded = \CCrmOwnerType::Deal == $ownerTypeID;
        foreach ($arBindings as $key => $item)
        {
            if (\CCrmOwnerType::Deal == $item['OWNER_TYPE_ID'])
            {
                if ($dealBinded)
                    unset($arBindings[$key]);

                $dealBinded = true;
            }
        }

        $crmEmail = \CCrmMailHelper::extractEmail(\COption::getOptionString('crm', 'mail', ''));

        $from  = '';
        $reply = '';
        $rawCc = $cc;

        if (isset($decodedData['from']))
            $from = trim(strval($decodedData['from']));

        if ($from == '')
        {
//            __CrmActivityEditorEndResponse(array('ERROR' => getMessage('CRM_ACTIVITY_EMAIL_EMPTY_FROM_FIELD')));
        }
        else
        {
            $fromEmail = $from;
            $fromAddress = new \Bitrix\Main\Mail\Address($fromEmail);

            if ($fromAddress->validate())
            {
                $fromEmail = $fromAddress->getEmail();

                \CBitrixComponent::includeComponentClass('bitrix:main.mail.confirm');
                if (!in_array($fromEmail, array_column(\MainMailConfirmComponent::prepareMailboxes(), 'email')))
                {
//                    __CrmActivityEditorEndResponse(array('ERROR' => getMessage('CRM_ACTIVITY_EMAIL_EMPTY_FROM_FIELD')));
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
//                __CrmActivityEditorEndResponse(array('ERROR' => getMessage('CRM_ACTIVITY_INVALID_EMAIL', array('#VALUE#' => $from))));
            }

            if (\CModule::includeModule('mail'))
            {
                foreach (\Bitrix\Mail\MailboxTable::getUserMailboxes() as $mailbox)
                {
                    if ($fromEmail == $mailbox['EMAIL'])
                    {
                        $userImap = $mailbox;
                    }
                }
            }

            if (empty($userImap))
            {
                if ($crmEmail != '' && $crmEmail != $fromEmail)
                {
                    $reply = $fromEmail . ', ' . $crmEmail;
                }

                $injectUrn = true;
            }

            if ('Y' == $data['from_copy'])
            {
                $cc[] = $fromEmail;
            }
        }

        $messageBody = '';
        $contentType = isset($data['content_type']) && \CCrmContentType::isDefined($data['content_type'])
            ? (int) $data['content_type'] : \CCrmContentType::BBCode;

        if (\CCrmContentType::Html == $contentType)
        {
            if (isset($decodedData['message']))
            {
                $messageBody = (string) $decodedData['message'];

                $messageBody = preg_replace('/<!--.*?-->/is', '', $messageBody);
                $messageBody = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $messageBody);
                $messageBody = preg_replace('/<title[^>]*>.*?<\/title>/is', '', $messageBody);

                $sanitizer = new \CBXSanitizer();
                $sanitizer->setLevel(\CBXSanitizer::SECURE_LEVEL_LOW);
                $sanitizer->applyDoubleEncode(false);
                $sanitizer->addTags(array('style' => array()));

                $messageBody = $sanitizer->sanitizeHtml($messageBody);
                $messageBody = preg_replace('/https?:\/\/bxacid:(n?\d+)/i', 'bxacid:\1', $messageBody);
            }
        }
        else
        {
            if (isset($data['message']))
            {
                $messageBody = (string) $data['message'];

                if (\CCrmContentType::PlainText == $contentType)
                {
                    $messageBody = sprintf(
                        '<html><body>%s</body></html>',
                        preg_replace('/[\r\n]+/'.BX_UTF_PCRE_MODIFIER, '<br>', htmlspecialcharsbx($messageBody))
                    );
                }
                else if (\CCrmContentType::BBCode == $contentType)
                {
                    //Convert BBCODE to HTML
                    $parser = new CTextParser();
                    $parser->allow['SMILES'] = 'N';
                    $messageBody = '<html><body>'.$parser->convertText($messageBody).'</body></html>';
                }
            }
        }

        if (($messageHtml = $messageBody) != '')
            \CCrmActivity::addEmailSignature($messageHtml, \CCrmContentType::Html);

        $parentId = isset($data['REPLIED_ID']) ? (int) $data['REPLIED_ID'] : 0;
        if ($parentId > 0 && !$dealBinded)
        {
            $parentBindings = \CCrmActivity::getBindings($parentId);
            foreach ($parentBindings as $item)
            {
                $key = sprintf('%u_%u', \CCrmOwnerType::resolveName($item['OWNER_TYPE_ID']), $item['OWNER_ID']);
                if (\CCrmOwnerType::Deal == $item['OWNER_TYPE_ID'] && !isset($arBindings[$key]))
                {
                    $arBindings[$key] = array(
                        'OWNER_TYPE_ID' => $item['OWNER_TYPE_ID'],
                        'OWNER_ID'      => $item['OWNER_ID'],
                    );

                    break;
                }
            }
        }

        $arBindings = array_merge(
            array(
                sprintf('%u_%u', \CCrmOwnerType::resolveName($ownerTypeID), $ownerID) => array(
                    'OWNER_TYPE_ID' => $ownerTypeID,
                    'OWNER_ID' => $ownerID,
                ),
            ),
            $arBindings
        );

        $arFields = array(
            'OWNER_ID' => $ownerID,
            'OWNER_TYPE_ID' => $ownerTypeID,
            'TYPE_ID' =>  CCrmActivityType::Email,
            'SUBJECT' => $subject,
            'START_TIME' => $now,
            'END_TIME' => $now,
            'COMPLETED' => 'Y',
            'RESPONSIBLE_ID' => $userID,
            'PRIORITY' => !empty($data['important']) ? \CCrmActivityPriority::High : \CCrmActivityPriority::Medium,
            'DESCRIPTION' => ($description = $messageHtml),
            'DESCRIPTION_TYPE' => \CCrmContentType::Html,
            'DIRECTION' => CCrmActivityDirection::Outgoing,
            'LOCATION' => '',
            'NOTIFY_TYPE' => CCrmActivityNotifyType::None,
            'BINDINGS' => array_values($arBindings),
            'COMMUNICATIONS' => $arComms,
            'PARENT_ID' => $parentId,
        );

        $storageTypeID = isset($data['storageTypeID']) ? intval($data['storageTypeID']) : CCrmActivityStorageType::Undefined;
        if($storageTypeID === CCrmActivityStorageType::Undefined
            || !CCrmActivityStorageType::IsDefined($storageTypeID))
        {
            if($isNew)
            {
                $storageTypeID = CCrmActivity::GetDefaultStorageTypeID();
            }
            else
            {
                $storageTypeID = CCrmActivity::GetStorageTypeID($ID);
                if($storageTypeID === CCrmActivityStorageType::Undefined)
                {
                    $storageTypeID = CCrmActivity::GetDefaultStorageTypeID();
                }
            }
        }

        $arFields['STORAGE_TYPE_ID'] = $storageTypeID;
        if($storageTypeID === CCrmActivityStorageType::File)
        {
            $arUserFiles = isset($data['files']) && is_array($data['files']) ? $data['files'] : array();
            if(!empty($arUserFiles) || !$isNew)
            {
                $arPermittedFiles = array();
                $arPreviousFiles = array();
                if(!$isNew)
                {
                    $arPreviousFields = $ID > 0 ? CCrmActivity::GetByID($ID) : array();
                    CCrmActivity::PrepareStorageElementIDs($arPreviousFields);
                    $arPreviousFiles = $arPreviousFiles['STORAGE_ELEMENT_IDS'];
                    if(is_array($arPreviousFiles) && !empty($arPreviousFiles))
                    {
                        $arPermittedFiles = array_intersect($arUserFiles, $arPreviousFiles);
                    }
                }

                $forwardedID = isset($data['FORWARDED_ID']) ? intval($data['FORWARDED_ID']) : 0;
                if($forwardedID > 0)
                {
                    $arForwardedFields = CCrmActivity::GetByID($forwardedID);
                    if($arForwardedFields)
                    {
                        CCrmActivity::PrepareStorageElementIDs($arForwardedFields);
                        $arForwardedFiles = $arForwardedFields['STORAGE_ELEMENT_IDS'];
                        if(!empty($arForwardedFiles))
                        {
                            $arForwardedFiles = array_intersect($arUserFiles, $arForwardedFiles);
                        }


                        if(!empty($arForwardedFiles))
                        {
                            foreach($arForwardedFiles as $fileID)
                                $arRawFile = CFile::MakeFileArray($fileID);
                            if(is_array($arRawFile))
                            {
                                $fileID = intval(CFile::SaveFile($arRawFile, 'crm'));
                                if($fileID > 0)
                                {
                                    $arPermittedFiles[] = $fileID;
                                }
                            }
                        }
                    }
                }

                $uploadControlCID = isset($data['uploadControlCID']) ? strval($data['uploadControlCID']) : '';
                if($uploadControlCID !== '' && isset($_SESSION["MFI_UPLOADED_FILES_{$uploadControlCID}"]))
                {
                    $uploadedFiles = $_SESSION["MFI_UPLOADED_FILES_{$uploadControlCID}"];
                    if(!empty($uploadedFiles))
                    {
                        $arPermittedFiles = array_merge(
                            array_intersect($arUserFiles, $uploadedFiles),
                            $arPermittedFiles
                        );
                    }
                }

                $arFields['STORAGE_ELEMENT_IDS'] = $arPermittedFiles;
            }
        }
        elseif($storageTypeID === CCrmActivityStorageType::WebDav || $storageTypeID === CCrmActivityStorageType::Disk)
        {
            if ($storageTypeID === CCrmActivityStorageType::Disk)
            {
                $arFileIDs = array_merge(
                    isset($data['diskfiles']) && is_array($data['diskfiles']) ? $data['diskfiles'] : array(),
                    isset($data['__diskfiles']) && is_array($data['__diskfiles'])
                        ? array_map(
                        function ($item)
                        {
                            if (!is_scalar($item))
                                return $item;
                            return ltrim($item, join(array(
                                'n', // \Bitrix\Disk\Uf\FileUserType::NEW_FILE_PREFIX
                            )));
                        },
                        $data['__diskfiles']
                    ) : array()
                );
            }
            else
            {
                $arFileIDs = isset($data['webdavelements']) && is_array($data['webdavelements']) ? $data['webdavelements'] : array();
            }

            $arFileIDs = array_filter($arFileIDs);
            if(!empty($arFileIDs) || !$isNew)
            {
                $arFields['STORAGE_ELEMENT_IDS'] = Bitrix\Crm\Integration\StorageManager::filterFiles($arFileIDs, $storageTypeID, $userID);

                if (!is_array($arFileIDs) || !is_array($arFields['STORAGE_ELEMENT_IDS']))
                {
                    addMessage2Log(
                        sprintf(
                            "crm.activity.editor\ajax.php: Invalid email attachments list\r\n(%s) -> (%s)",
                            $arFileIDs,
                            $arFields['STORAGE_ELEMENT_IDS']
                        ),
                        'crm',
                        0
                    );
                }
                else if (count($arFileIDs) > count($arFields['STORAGE_ELEMENT_IDS']))
                {
                    addMessage2Log(
                        sprintf(
                            "crm.activity.editor\ajax.php: Email attachments list had been filtered\r\n(%s) -> (%s)",
                            join(',', $arFileIDs),
                            join(',', $arFields['STORAGE_ELEMENT_IDS'])
                        ),
                        'crm',
                        0
                    );
                }
            }
        }

        $totalSize = 0;

        $arRawFiles = array();
        if (isset($arFields['STORAGE_ELEMENT_IDS']) && !empty($arFields['STORAGE_ELEMENT_IDS']))
        {
            foreach ((array) $arFields['STORAGE_ELEMENT_IDS'] as $item)
            {
                $arRawFiles[$item] = \Bitrix\Crm\Integration\StorageManager::makeFileArray($item, $storageTypeID);

                $totalSize += $arRawFiles[$item]['size'];

                if (\CCrmContentType::Html == $contentType)
                {
                    $fileInfo = \Bitrix\Crm\Integration\StorageManager::getFileInfo(
                        $item, $storageTypeID, false,
                        array('OWNER_TYPE_ID' => \CCrmOwnerType::Activity, 'OWNER_ID' => $ID)
                    );

                    $description = preg_replace(
                        sprintf('/(https?:\/\/)?bxacid:n?%u/i', $item),
                        htmlspecialcharsbx($fileInfo['VIEW_URL']),
                        $description
                    );
                }
            }
        }

        $maxSize = (int) Config\Option::get('main', 'max_file_size', 0);
        if ($maxSize > 0 && $maxSize <= ceil($totalSize / 3) * 4) // base64 coef.
        {
//            __CrmActivityEditorEndResponse(array('ERROR' => getMessage(
//                'CRM_ACTIVITY_EMAIL_MAX_SIZE_EXCEED',
//                ['#SIZE#' => \CFile::formatSize($maxSize)]
//            )));
        }

        if ($isNew)
        {
            if(!($ID = CCrmActivity::Add($arFields, false, false, array('REGISTER_SONET_EVENT' => true))))
            {
//                __CrmActivityEditorEndResponse(array('ERROR' => CCrmActivity::GetLastErrorMessage()));
            }
        }
        else
        {
            if(!CCrmActivity::Update($ID, $arFields, false, false))
            {
//                __CrmActivityEditorEndResponse(array('ERROR' => CCrmActivity::GetLastErrorMessage()));
            }
        }

        $hostname = \COption::getOptionString('main', 'server_name', '') ?: 'localhost';
        if (defined('BX24_HOST_NAME') && BX24_HOST_NAME != '')
            $hostname = BX24_HOST_NAME;
        else if (defined('SITE_SERVER_NAME') && SITE_SERVER_NAME != '')
            $hostname = SITE_SERVER_NAME;

        $urn = \CCrmActivity::prepareUrn($arFields);
        $messageId = sprintf('<crm.activity.%s@%s>', $urn, $hostname);

        \CCrmActivity::update($ID, array(
            'DESCRIPTION' => $description,
            'URN'         => $urn,
            'SETTINGS'    => array(
                'IS_BATCH_EMAIL'  => Config\Option::get('main', 'track_outgoing_emails_read', 'Y') == 'Y' ? false : null,
                'MESSAGE_HEADERS' => array(
                    'Message-Id' => $messageId,
                    'Reply-To'   => $reply ?: $fromEmail,
                ),
                'EMAIL_META' => array(
                    '__email' => $fromEmail,
                    'from'    => $from,
                    'replyTo' => $reply,
                    'to'      => join(', ', $to),
                    'cc'      => join(', ', $rawCc),
                    'bcc'     => join(', ', $bcc),
                ),
            ),
        ), false, false, array('REGISTER_SONET_EVENT' => true));

        if (!empty($_REQUEST['save_as_template']))
        {
            $templateFields = array(
                'TITLE'          => $subject,
                'IS_ACTIVE'      => 'Y',
                'OWNER_ID'       => $userID,
                'SCOPE'          => \CCrmMailTemplateScope::Personal,
                'ENTITY_TYPE_ID' => 0,
                'EMAIL_FROM'     => $from,
                'SUBJECT'        => $subject,
                'BODY_TYPE'      => \CCrmContentType::Html,
                'BODY'           => $messageBody,
                'UF_ATTACHMENT' => array_map(
                    function ($item)
                    {
                        return is_scalar($item) ? sprintf('n%u', $item) : $item;
                    },
                    $arFields['STORAGE_ELEMENT_IDS']
                ),
                'SORT'           => 100,
            );
            \CCrmMailTemplate::add($templateFields);
        }

        //Save user email in settings -->
        if($from !== CUserOptions::GetOption('crm', 'activity_email_addresser', ''))
        {
            CUserOptions::SetOption('crm', 'activity_email_addresser', $from);
        }
        //<-- Save user email in settings
        if(!empty($arErrors))
        {
//            __CrmActivityEditorEndResponse(array('ERROR' => $arErrors));
        }

        // sending email
        $rcpt    = array();
        $rcptCc  = array();
        $rcptBcc = array();
        foreach ($to as $item)
            $rcpt[] = Mail\Mail::encodeHeaderFrom($item, SITE_CHARSET);
        foreach ($cc as $item)
            $rcptCc[] = Mail\Mail::encodeHeaderFrom($item, SITE_CHARSET);
        foreach ($bcc as $item)
            $rcptBcc[] = Mail\Mail::encodeHeaderFrom($item, SITE_CHARSET);

        $outgoingSubject = $subject;
        $outgoingBody = $messageHtml ?: getMessage('CRM_EMAIL_ACTION_DEFAULT_DESCRIPTION');

        if (!empty($injectUrn)/* && $dealBinded*/)
        {
            switch (\CCrmEMailCodeAllocation::getCurrent())
            {
                case \CCrmEMailCodeAllocation::Subject:
                    $outgoingSubject = \CCrmActivity::injectUrnInSubject($urn, $outgoingSubject);
                    break;
                case \CCrmEMailCodeAllocation::Body:
                    $outgoingBody = \CCrmActivity::injectUrnInBody($urn, $outgoingBody, 'html');
                    break;
            }
        }

        $attachments = array();
        foreach ($arRawFiles as $key => $item)
        {
            $contentId = sprintf(
                'bxacid.%s@%s.crm',
                hash('crc32b', $item['external_id'].$item['size'].$item['name']),
                hash('crc32b', $hostname)
            );

            $attachments[] = array(
                'ID'           => $contentId,
                'NAME'         => $item['ORIGINAL_NAME'] ?: $item['name'],
                'PATH'         => $item['tmp_name'],
                'CONTENT_TYPE' => $item['type'],
            );

            $outgoingBody = preg_replace(
                sprintf('/(https?:\/\/)?bxacid:n?%u/i', $key),
                sprintf('cid:%s', $contentId),
                $outgoingBody
            );
        }

        $outgoingParams = array(
            'CHARSET'      => SITE_CHARSET,
            'CONTENT_TYPE' => 'html',
            'ATTACHMENT'   => $attachments,
            'TO'           => join(', ', $rcpt),
            'SUBJECT'      => $outgoingSubject,
            'BODY'         => $outgoingBody,
            'HEADER'       => array(
                'From'       => $fromEncoded ?: $fromEmail,
                'Reply-To'   => $reply ?: $fromEmail,
                //'To'         => join(', ', $rcpt),
                'Cc'         => join(', ', $rcptCc),
                'Bcc'        => join(', ', $rcptBcc),
                //'Subject'    => $outgoingSubject,
                'Message-Id' => $messageId,
            ),
        );

        $context = new Mail\Context();
        $context->setCategory(Mail\Context::CAT_EXTERNAL);
        $context->setPriority(Mail\Context::PRIORITY_NORMAL);
        $context->setCallback(
            (new Mail\Callback\Config())
                ->setModuleId('crm')
                ->setEntityType('act')
                ->setEntityId($urn)
        );

        $sendResult = Mail\Mail::send(array_merge(
            $outgoingParams,
            array(
                'TRACK_READ' => array(
                    'MODULE_ID' => 'crm',
                    'FIELDS'    => array('urn' => $urn),
                    'URL_PAGE' => '/pub/mail/read.php',
                ),
                'TRACK_CLICK' => array(
                    'MODULE_ID' => 'crm',
                    'FIELDS'    => array('urn' => $urn),
                    'URL_PAGE' => '/pub/mail/click.php',
                ),
                'CONTEXT' => $context,
            )
        ));

        if (!$sendResult)
        {
            if ($isNew)
            {
                $arErrors[] = getMessage('CRM_ACTIVITY_EMAIL_CREATION_CANCELED');
                \CCrmActivity::delete($ID);
            }
//            __CrmActivityEditorEndResponse(array('ERROR' => $arErrors));
        }

        addEventToStatFile('crm', 'send_email_message', $_REQUEST['context'], trim(trim($messageId), '<>'));

        $needUpload = !empty($userImap);

        if ($context->getSmtp() && in_array(mb_strtolower($context->getSmtp()->getHost()), array('smtp.gmail.com', 'smtp.office365.com')))
        {
            $needUpload = false;
        }

        if ($needUpload)
        {
            class_exists('Bitrix\Mail\Helper');

            $outgoing = new \Bitrix\Mail\DummyMail(array_merge(
                $outgoingParams,
                array(
                    'HEADER' => array_merge(
                        $outgoingParams['HEADER'],
                        array(
                            'To'      => $outgoingParams['TO'],
                            'Subject' => $outgoingParams['SUBJECT'],
                        )
                    ),
                )
            ));

            $mailboxHelper = Bitrix\Mail\Helper\Mailbox::createInstance($userImap['ID']);
            $mailboxHelper->uploadMessage($outgoing);
        }

        // Try add event to entity
        $CCrmEvent = new CCrmEvent();

        $eventText  = '';
        $eventText .= GetMessage('CRM_TITLE_EMAIL_SUBJECT').': '.$subject."\n\r";
        $eventText .= GetMessage('CRM_TITLE_EMAIL_FROM').': '.$from."\n\r";
        if (!empty($to))
            $eventText .= getMessage('CRM_TITLE_EMAIL_TO').': '.implode(',', $to)."\n\r";
        if (!empty($rawCc))
            $eventText .= 'Cc: '.implode(',', $rawCc)."\n\r";
        if (!empty($bcc))
            $eventText .= 'Bcc: '.implode(',', $bcc)."\n\r";
        $eventText .= "\n\r";
        $eventText .= $description;

        $eventBindings = array();
        foreach($arBindings as $item)
        {
            $bindingEntityID = $item['OWNER_ID'];
            $bindingEntityTypeID = $item['OWNER_TYPE_ID'];
            $bindingEntityTypeName = \CCrmOwnerType::resolveName($bindingEntityTypeID);

            $eventBindings["{$bindingEntityTypeName}_{$bindingEntityID}"] = array(
                'ENTITY_TYPE' => $bindingEntityTypeName,
                'ENTITY_ID' => $bindingEntityID
            );
        }
        $CCrmEvent->Add(
            array(
                'ENTITY' => $eventBindings,
                'EVENT_ID' => 'MESSAGE',
                'EVENT_TEXT_1' => $eventText,
                'FILES' => array_values($arRawFiles),
            )
        );
        // <-- Sending Email

        $commData = array();
        $communications = CCrmActivity::GetCommunications($ID);
        foreach($communications as &$arComm)
        {
            CCrmActivity::PrepareCommunicationInfo($arComm);
            $commData[] = array(
                'type' => $arComm['TYPE'],
                'value' => $arComm['VALUE'],
                'entityId' => $arComm['ENTITY_ID'],
                'entityType' => CCrmOwnerType::ResolveName($arComm['ENTITY_TYPE_ID']),
                'entityTitle' => $arComm['TITLE'],
                'entityUrl' => CCrmOwnerType::GetEntityShowPath($arComm['ENTITY_TYPE_ID'], $arComm['ENTITY_ID'])
            );
        }
        unset($arComm);

        $userName = '';
        if($userID > 0)
        {
            $dbResUser = CUser::GetByID($userID);
            $userName = is_array(($user = $dbResUser->Fetch()))
                ? CUser::FormatName(CSite::GetNameFormat(false), $user, true, false) : '';
        }

        $nowStr = ConvertTimeStamp(MakeTimeStamp($now), 'FULL', $siteID);

        CCrmActivity::PrepareStorageElementIDs($arFields);
        CCrmActivity::PrepareStorageElementInfo($arFields);

        $jsonFields = array(
            'ID' => $ID,
            'typeID' => CCrmActivityType::Email,
            'ownerID' => $arFields['OWNER_ID'],
            'ownerType' => CCrmOwnerType::ResolveName($arFields['OWNER_TYPE_ID']),
            'ownerTitle' => CCrmOwnerType::GetCaption($arFields['OWNER_TYPE_ID'], $arFields['OWNER_ID']),
            'ownerUrl' => CCrmOwnerType::GetEntityShowPath($arFields['OWNER_TYPE_ID'], $arFields['OWNER_ID']),
            'subject' => $subject,
            'description' => $description,
            'descriptionHtml' => $description,
            'location' => '',
            'start' => $nowStr,
            'end' => $nowStr,
            'deadline' => $nowStr,
            'completed' => true,
            'notifyType' => CCrmActivityNotifyType::None,
            'notifyValue' => 0,
            'priority' => CCrmActivityPriority::Medium,
            'responsibleName' => $userName,
            'responsibleUrl' =>
                CComponentEngine::MakePathFromTemplate(
                    '/company/personal/user/#user_id#/',
                    array('user_id' => $userID)
                ),
            'storageTypeID' => $storageTypeID,
            'files' => isset($arFields['FILES']) ? $arFields['FILES'] : array(),
            'webdavelements' => isset($arFields['WEBDAV_ELEMENTS']) ? $arFields['WEBDAV_ELEMENTS'] : array(),
            'diskfiles' => isset($arFields['DISK_FILES']) ? $arFields['DISK_FILES'] : array(),
            'communications' => $commData
        );

        $responseData = array('ACTIVITY' => $jsonFields);
//        __CrmActivityEditorEndResponse($responseData);

        return true;
    }

}