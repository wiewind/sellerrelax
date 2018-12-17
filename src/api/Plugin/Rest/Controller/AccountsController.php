<?php

/**
 * Created by PhpStorm.
 * User: benying.zou
 * Date: 05.12.2018
 * Time: 16:42
 */
class AccountsController extends RestAppController
{

    var $restAdress = [
        'accounts'              => 'rest/accounts',
        'contacts'              => 'rest/accounts/contacts?with=addresses,accounts,options',
        'contactTypes'          => 'rest/accounts/contacts/types',
        'contactPositions'      => 'rest/accounts/contacts/positions',
        'contactClasses'        => 'rest/accounts/contacts/classes',
        'contactOptionTypes'    => 'rest/accounts/contacts/option_types',
        'contactOptionSubTypes' => 'rest/accounts/contacts/option_sub_types',

        'addressReletionTypes'  => 'rest/accounts/addresses/relation_types',
        'addressOptionTypes'    => 'rest/accounts/addresses/option_types'
    ];

    function importContactTypes () {
        $this->autoRender = false;

        CakeLog::write('import',  "Import ContactTypes beginn...");

        $url = $this->restAdress['contactTypes'];

        $data = $this->callJsonRest($url);

        foreach ($data as $item) {
            $saveData = [
                'extern_id' => $item->id,
                'non_ersasable' => $item->nonErasable,
                'position' => $item->position
            ];
            if (isset($item->names)) {
                foreach ($item->names as $name) {
                    switch ($name->lang) {
                        case 'de':
                            $saveData['name_de'] = $name->name;
                            break;
                        case 'en':
                            $saveData['name_en'] = $name->name;
                            break;
                    }
                }
            }

            $storedData = $this->ContactType->find('first', [
                'fields' => 'id',
                'conditions' => [
                    'extern_id' => $item->id
                ]
            ]);
            if ($storedData) {
                $saveData['id'] = $storedData['ContactType']['id'];
            } else {
                $this->ContactType->create();
            }
            $this->ContactType->save($saveData);
        }
        CakeLog::write('import', "Import ContactTypes end with " . count($data) . " record(s)!");
    }

    function importContactPositions () {
        $this->autoRender = false;

        CakeLog::write('import',  "Import ContactPosition beginn...");

        $url = $this->restAdress['contactPositions'];

        $data = $this->callJsonRest($url);

        foreach ($data as $item) {
            $saveData = [
                'extern_id' => $item->id,
                'position' => $item->position
            ];
            if (isset($item->names)) {
                foreach ($item->names as $name) {
                    switch ($name->lang) {
                        case 'de':
                            $saveData['name_de'] = $name->name;
                            break;
                        case 'en':
                            $saveData['name_en'] = $name->name;
                            break;
                    }
                }
            }

            $storedData = $this->ContactPosition->find('first', [
                'fields' => 'id',
                'conditions' => [
                    'extern_id' => $item->id
                ]
            ]);
            if ($storedData) {
                $saveData['id'] = $storedData['ContactPosition']['id'];
            } else {
                $this->ContactPosition->create();
            }
            $this->ContactPosition->save($saveData);
        }
        CakeLog::write('import', "Import ContactPosition end with " . count($data) . " record(s)!");
    }

    function importContactClasses () {
        $this->autoRender = false;

        CakeLog::write('import',  "Import ContactClasses beginn...");

        $url = $this->restAdress['contactClasses'];

        $data = $this->callJsonRest($url);

        foreach ($data as $key => $item) {
            $saveData = [
                'extern_id' => $key,
                'name' => $item
            ];
            $storedData = $this->ContactClass->find('first', [
                'fields' => 'id',
                'conditions' => [
                    'extern_id' => $key
                ]
            ]);
            if ($storedData) {
                $saveData['id'] = $storedData['ContactClass']['id'];
            } else {
                $this->ContactClass->create();
            }
            $this->ContactClass->save($saveData);
        }
        CakeLog::write('import', "Import ContactClasses end with " . count($data) . " record(s)!");
    }

    function importContactOptionTypes () {
        $this->autoRender = false;

        CakeLog::write('import',  "Import ContactOptionTypes beginn...");

        $url = $this->restAdress['contactOptionTypes'];

        $data = $this->callJsonRest($url);

        foreach ($data as $item) {
            $saveData = [
                'extern_id' => $item->id,
                'position' => $item->position,
                'non_ersasable' => $item->nonErasable
            ];
            if (isset($item->names)) {
                foreach ($item->names as $name) {
                    switch ($name->lang) {
                        case 'de':
                            $saveData['name_de'] = $name->name;
                            break;
                        case 'en':
                            $saveData['name_en'] = $name->name;
                            break;
                    }
                }
            }

            $storedData = $this->ContactOptionType->find('first', [
                'fields' => 'id',
                'conditions' => [
                    'extern_id' => $item->id
                ]
            ]);
            if ($storedData) {
                $saveData['id'] = $storedData['ContactOptionType']['id'];
            } else {
                $this->ContactOptionType->create();
            }
            $this->ContactOptionType->save($saveData);
        }
        CakeLog::write('import', "Import ContactOptionTypes end with " . count($data) . " record(s)!");
    }

    function importContactOptionSubTypes () {
        $this->autoRender = false;

        CakeLog::write('import',  "Import ContactOptionSubTypes beginn...");

        $url = $this->restAdress['contactOptionSubTypes'];

        $data = $this->callJsonRest($url);

        foreach ($data as $item) {
            $saveData = [
                'extern_id' => $item->id,
                'position' => $item->position,
                'non_ersasable' => $item->nonErasable
            ];
            if (isset($item->names)) {
                foreach ($item->names as $name) {
                    switch ($name->lang) {
                        case 'de':
                            $saveData['name_de'] = $name->name;
                            break;
                        case 'en':
                            $saveData['name_en'] = $name->name;
                            break;
                    }
                }
            }

            $storedData = $this->ContactOptionSubType->find('first', [
                'fields' => 'id',
                'conditions' => [
                    'extern_id' => $item->id
                ]
            ]);
            if ($storedData) {
                $saveData['id'] = $storedData['ContactOptionSubType']['id'];
            } else {
                $this->ContactOptionSubType->create();
            }
            $this->ContactOptionSubType->save($saveData);
        }
        CakeLog::write('import', "Import ContactOptionSubTypes end with " . count($data) . " record(s)!");
    }

    function importAddressOptionTypes () {
        $this->autoRender = false;

        CakeLog::write('import',  "Import AddressOptionTypes beginn...");

        $url = $this->restAdress['addressOptionTypes'];

        $data = $this->callJsonRest($url);

        foreach ($data as $item) {
            $saveData = [
                'extern_id' => $item->id,
                'position' => $item->position,
                'non_ersasable' => $item->nonErasable
            ];
            if (isset($item->names)) {
                foreach ($item->names as $name) {
                    switch ($name->lang) {
                        case 'de':
                            $saveData['name_de'] = $name->name;
                            break;
                        case 'en':
                            $saveData['name_en'] = $name->name;
                            break;
                    }
                }
            }

            $storedData = $this->AddressOptionType->find('first', [
                'fields' => 'id',
                'conditions' => [
                    'extern_id' => $item->id
                ]
            ]);
            if ($storedData) {
                $saveData['id'] = $storedData['AddressOptionType']['id'];
            } else {
                $this->AddressOptionType->create();
            }
            $this->AddressOptionType->save($saveData);
        }
        CakeLog::write('import', "Import AddressOptionTypes end with " . count($data) . " record(s)!");
    }

    function importAddressRelationTypes () {
        $this->autoRender = false;

        CakeLog::write('import',  "Import AddressRelationTypes beginn...");

        $url = $this->restAdress['addressReletionTypes'];

        $data = $this->callJsonRest($url);

        foreach ($data as $item) {
            $saveData = [
                'extern_id' => $item->id,
                'position' => $item->position,
                'non_ersasable' => $item->nonErasable
            ];
            if (isset($item->names)) {
                foreach ($item->names as $name) {
                    switch ($name->lang) {
                        case 'de':
                            $saveData['name_de'] = $name->name;
                            break;
                        case 'en':
                            $saveData['name_en'] = $name->name;
                            break;
                    }
                }
            }

            $storedData = $this->AddressRelationType->find('first', [
                'fields' => 'id',
                'conditions' => [
                    'extern_id' => $item->id
                ]
            ]);
            if ($storedData) {
                $saveData['id'] = $storedData['AddressRelationType']['id'];
            } else {
                $this->AddressRelationType->create();
            }
            $this->AddressRelationType->save($saveData);
        }
        CakeLog::write('import', "Import AddressRelationTypes end with " . count($data) . " record(s)!");
    }

    function importAccounts ($allImport=true) {
        $this->autoRender = false;
        ini_set("memory_limit","1024M");

        $importType = 'accounts';
        $newImport = $this->makeNewImport($importType);

        if ($newImport === false) return;

        $newImport['itemsPerPage'] = Configure::read('system.rest.limitPerImport');
        //$newImport['itemsPerPage'] = 3000;

        $params['page'] = $newImport['page'];
        $params['itemsPerPage'] = $newImport['itemsPerPage'];

        $now = date('Y-m-d H:i:s');

        CakeLog::write('import',  "Import Accounts (page {$params['page']}) beginn...");

        $importData = [
            'type' => $importType,
            'update_from' => (isset($newImport['from'])) ? $newImport['from'] : '',
            'update_to' => $newImport['to'],
            'page' =>  $newImport['page'],
            'import_beginn' => $now,
            'version' => $this->version,
            'url' => $_SERVER['SCRIPT_URI'],
            'ip' => $_SERVER['REMOTE_ADDR']
        ];
        $this->Import->create();
        $this->Import->save($importData);
        $saveImportId = $this->Import->getLastInsertID();

        $url = $this->restAdress['accounts'];

        $data = $this->callJsonRest($url, $params, 'GET', $saveImportId);

        $items = $data->entries;

        $errors = [];
        foreach ($items as $item) {
            $dataSource = $this->Account->getDataSource();
            $dataSource->begin();
            try {
                $this->__doImportAccountsData($item, $now);
                $dataSource->commit();
            } catch (Exception $e) {
                $dataSource->rollback();
                $errors['AccountId:'.$item->id] = [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode()
                ];
            }
        }

        $now2 = date('Y-m-d H:i:s');
        $mengeOfPage = ($data->lastOnPage) ? $data->lastOnPage - $data->firstOnPage + 1 : 0;

        if ($errors) {
            $logStr = "Import Errors: ";
            foreach ($errors as $oid => $err) {
                $logStr .= "\t" . $oid . " (" . $err['code'] . ") " . $err['message'] . "\n";
            }
            CakeLog::write('import', $logStr);
        }

        $importData = [
            'id' => $saveImportId,
            'menge' => $mengeOfPage,
            'is_last_page' => $data->isLastPage,
            'last_page_no' => $data->lastPageNumber,
            'total' => $data->totalsCount,
            'errors' => json_encode($errors),
            'import_end' => $now2
        ];
        $this->Import->save($importData);

        CakeLog::write('import', "Import Accounts (page {$params['page']}) end with $mengeOfPage record(s)!");

        if ($allImport && !$data->isLastPage) {
            $this->importAccounts(true);
        }
    }

    private function __doImportAccountsData ($data, $now="") {
        if (!$now) $now = date('Y-m-d H:i:s');

        $oData = [
            'extern_id'                         => $data->id,
            'company_name'                      => $data->companyName,
            'number'                            => ($data->number) ? $data->number : "",
            'dearler_min_orer_value'            => ($data->dealerMinOrderValue) ? $data->dealerMinOrderValue : 0,
            'delivery_time'                     => ($data->deliveryTime) ? $data->deliveryTime : 0,
            'discount_days'                     => ($data->discountDays) ? $data->discountDays : 0,
            'discount_percent'                  => ($data->discountPercent) ? $data->discountPercent : 0,
            'sales_representative_contact_id'   => ($data->salesRepresentativeContactId) ? $data->salesRepresentativeContactId : 0,
            'tax_id_number'                     => ($data->taxIdNumber) ? $data->taxIdNumber : "",
            'time_for_payment_allowed_days'     => ($data->timeForPaymentAllowedDays) ? $data->timeForPaymentAllowedDays : 0,
            'user_id'                           => ($data->userId) ? $data->userId : 0,
            'valuta'                            => ($data->valuta) ? $data->valuta : 0,
            'created'                           => ($data->createdAt) ? GlbF::iso2Date($data->createdAt) : null,
            'updated'                           => ($data->updatedAt) ? GlbF::iso2Date($data->updatedAt) : null,
            'imported'                          => $now
        ];

        $dbData = $this->Account->find('first', [
            'fields' => 'id',
            'conditions' => [
                'extern_id' => $oData['extern_id']
            ]
        ]);
        if ($dbData) {
            $oData['id'] = $dbData['Account']['id'];
        } else {
            $this->Account->create();
        }

        $this->Account->save($oData);

        if (isset($data->pivot)) {
            $this->__doImportAccountsContactData($data->pivot);
        }
    }

    function importContacts () {
        $this->autoRender = false;
        ini_set("memory_limit","1024M");
        set_time_limit(200);

        $importType = 'contacts';
        $newImport = $this->makeNewImport($importType);

        if ($newImport === false) return;

        //$newImport['itemsPerPage'] = Configure::read('system.rest.limitPerImport');
        $newImport['itemsPerPage'] = 1000;

        $params['page'] = $newImport['page'];
        $params['itemsPerPage'] = $newImport['itemsPerPage'];

        if ($newImport['from']) {
            $from = GlbF::date2Iso($newImport['from']);
            if ($newImport['install']) {
                $params['createdAtAfter'] = GlbF::date2Iso($from);
            } else {
                $params['updatedAtAfter'] = GlbF::date2Iso($from);
            }
        }
        if ($newImport['to']) {
            $to = GlbF::date2Iso($newImport['to']);
            if ($newImport['install']) {
                $params['createdAtBefore'] = GlbF::date2Iso($to);
            } else {
                $params['updatedAtBefore'] = GlbF::date2Iso($to);
            }
        }

        $now = date('Y-m-d H:i:s');

        CakeLog::write('import',  "Import Contacts (page {$params['page']}) beginn...");

        $importData = [
            'type' => $importType,
            'update_from' => (isset($newImport['from'])) ? $newImport['from'] : '',
            'update_to' => $newImport['to'],
            'page' =>  $newImport['page'],
            'import_beginn' => $now,
            'version' => $this->version,
            'url' => $_SERVER['SCRIPT_URI'],
            'ip' => $_SERVER['REMOTE_ADDR']
        ];
        $this->Import->create();
        $this->Import->save($importData);
        $saveImportId = $this->Import->getLastInsertID();

        $url = $this->restAdress['contacts'];

        $data = $this->callJsonRest($url, $params, 'GET', $saveImportId);

        $items = $data->entries;

        $errors = [];
        foreach ($items as $item) {
            $dataSource = $this->Contact->getDataSource();
            $dataSource->begin();
            try {
                $this->__doImportContactsData($item, $now);
                $dataSource->commit();
            } catch (Exception $e) {
                $dataSource->rollback();
                $errors['ContactId:'.$item->id] = [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode()
                ];
            }
        }

        $now2 = date('Y-m-d H:i:s');
        $mengeOfPage = ($data->lastOnPage) ? $data->lastOnPage - $data->firstOnPage + 1 : 0;

        if ($errors) {
            $logStr = "Import Errors: ";
            foreach ($errors as $oid => $err) {
                $logStr .= "\t" . $oid . " (" . $err['code'] . ") " . $err['message'] . "\n";
            }
            CakeLog::write('import', $logStr);
        }

        $importData = [
            'id' => $saveImportId,
            'menge' => $mengeOfPage,
            'is_last_page' => $data->isLastPage,
            'last_page_no' => $data->lastPageNumber,
            'total' => $data->totalsCount,
            'errors' => json_encode($errors),
            'import_end' => $now2
        ];
        $this->Import->save($importData);

        CakeLog::write('import', "Import Contacts (page {$params['page']}) end with $mengeOfPage record(s)!");
    }

    private function __doImportContactsData ($data, $now="") {
        if (!$now) $now = date('Y-m-d H:i:s');
        $oData = [
            'extern_id'                         => $data->id,
            'type_id'                           => $data->typeId,
            'title'                             => ($data->title) ? $data->title : "",
            'full_name'                         => ($data->fullName) ? trim($data->fullName) : "",
            'gender'                            => ($data->gender) ? $data->gender : null,
            'birthday'                          => ($data->birthdayAt) ? $data->birthdayAt : null,
            'class_id'                          => ($data->classId) ? $data->classId : 0,
            'email'                             => ($data->email) ? $data->email : null,
            'external_id'                       => ($data->externalId) ? $data->externalId : null,
            'book_account'                      => ($data->bookAccount) ? $data->bookAccount : null,
            'ebay_name'                         => ($data->ebayName) ? $data->ebayName : null,
            'klarna_personal_id'                => ($data->klarnaPersonalId) ? $data->klarnaPersonalId : null,
            'paypal_email'                      => ($data->paypalEmail) ? $data->paypalEmail : null,
            'paypal_payer_id'                   => ($data->paypalPayerId) ? $data->paypalPayerId : null,
            'dhl_post_ident'                    => ($data->dhlPostIdent) ? $data->dhlPostIdent : null,
            'plenty_id'                         => ($data->plentyId) ? $data->plentyId : 0,
            'privatePhone'                      => ($data->privatePhone) ? $data->privatePhone : null,
            'privateMobile'                     => ($data->privateMobile) ? $data->privateMobile : null,
            'privateFax'                        => ($data->privateFax) ? $data->privateFax : null,
            'rating'                            => ($data->rating) ? $data->rating : 0,
            'referrer_id'                       => ($data->referrerId) ? $data->referrerId : 0,
            'lang'                              => ($data->lang) ? $data->lang : null,
            'contact_person'                    => ($data->contactPerson) ? $data->contactPerson : null,
            'discount_days'                     => ($data->discountDays) ? $data->discountDays : null,
            'discount_percent'                  => ($data->discountPercent) ? $data->discountPercent : null,
            'form_of_address'                   => ($data->formOfAddress) ? $data->formOfAddress : null,
            'marketplace_partner'               => ($data->marketplacePartner) ? $data->marketplacePartner : null,
            'newsletter_allowance_at'           => ($data->newsletterAllowanceAt) ? GlbF::iso2Date($data->newsletterAllowanceAt) : null,
            'sales_representative_contact_id'   => ($data->salesRepresentativeContactId) ? $data->salesRepresentativeContactId : null,
            'single_access'                     => ($data->singleAccess) ? $data->singleAccess : null,
            'time_for_payment_allowed_days'     => ($data->timeForPaymentAllowedDays) ? $data->timeForPaymentAllowedDays : null,
            'user_id'                           => ($data->userId) ? $data->userId : null,
            'valuta'                            => ($data->valuta) ? $data->valuta : null,
            'blocked'                           => ($data->blocked) ? $data->blocked : 0,
            'last_order_at'                     => ($data->lastOrderAt) ? GlbF::iso2Date($data->lastOrderAt) : null,
            'last_login_at'                     => ($data->lastLoginAt) ? GlbF::iso2Date($data->lastLoginAt) : null,
            'created'                           => ($data->createdAt) ? GlbF::iso2Date($data->createdAt) : null,
            'updated'                           => ($data->updatedAt) ? GlbF::iso2Date($data->updatedAt) : null,
            'imported'                          => $now
        ];

        $dbData = $this->Contact->find('first', [
            'fields' => 'id',
            'conditions' => [
                'extern_id' => $oData['extern_id']
            ]
        ]);
        if ($dbData) {
            $oData['id'] = $dbData['Contact']['id'];
        } else {
            $this->Contact->create();
        }

        $this->Contact->save($oData);

        if (!isset($oData['id'])) {
            $oData['id'] = $this->Contact->getInsertID();
        }

        $this->ContactOption->deleteAll([
            'contact_id' => $oData['extern_id']
        ]);
        if (isset($data->options) && $data->options) {
            foreach ($data->options as $opt) {
                $this->__doImportContactOptionsData($opt, $now);
            }
        }

        $this->AccountsContact->deleteAll([
            'contact_id' => $oData['extern_id']
        ]);
        if (isset($data->accounts) && $data->accounts) {
            foreach ($data->accounts as $account) {
                $this->__doImportAccountsData($account, $now);
            }
        }

        $this->ContactAddress->deleteAll([
            'contact_id' => $oData['extern_id']
        ]);
        if (isset($data->addresses) && $data->addresses) {
            foreach ($data->addresses as $address) {
                $this->__doImportAddressesData($address, $now);
            }
        }
    }

    private function __doImportContactOptionsData ($data, $now) {
        if (!$now) $now = date('Y-m-d H:i:s');
        $saveData = [
            'id' => $data->id,
            'extern_id' => $data->id,
            'contact_id' => $data->contactId,
            'type_id' => ($data->typeId) ? $data->typeId : 0,
            'sub_type_id' =>($data->subTypeId) ?  $data->subTypeId : 0,
            'priority' => ($data->priority) ? $data->priority : 0,
            'value' => ($data->value) ? $data->value : "",
            'created' => ($data->createdAt) ? GlbF::iso2Date($data->createdAt) : null,
            'updated' => ($data->updatedAt) ? GlbF::iso2Date($data->updatedAt) : null,
            'imported' => $now
        ];
        $this->ContactOption->save($saveData);
    }

    private function __doImportAddressesOptionsData ($data, $now) {
        if (!$now) $now = date('Y-m-d H:i:s');
        $saveData = [
            'id' => $data->id,
            'extern_id' => $data->id,
            'address_id' => $data->addressId,
            'type_id' => ($data->typeId) ? $data->typeId : 0,
            'position' =>($data->position) ?  $data->position : 0,
            'value' => ($data->value) ? $data->value : "",
            'created' => ($data->createdAt) ? GlbF::iso2Date($data->createdAt) : null,
            'updated' => ($data->updatedAt) ? GlbF::iso2Date($data->updatedAt) : null,
            'imported' => $now
        ];
        $this->AddressOption->save($saveData);
    }

    private function __doImportAccountsContactData($data) {
        $saveData = [
            'id' => $data->id,
            'extern_id' => $data->id,
            'account_id' => $data->accountId,
            'contact_id' => $data->contactId
        ];
        $this->AccountsContact->save($saveData);
    }

    private function __doImportContactAddressData ($data) {
        $saveData = [
            'id' => $data->id,
            'extern_id' => $data->id,
            'address_id' => $data->addressId,
            'contact_id' => $data->contactId,
            'type_id' => ($data->typeId) ? $data->typeId : 0,
            'is_primary' => ($data->isPrimary) ? 1 : 0
        ];
        $this->ContactAddress->save($saveData);
    }

    private function __doImportAddressesData ($data, $now) {
        $address = $data->address1;
        if ($address && $data->address2) {
            $address .= ' ' . $data->address2;
        }
        if ($address && $data->address3) {
            $address .= ' ' . $data->address3;
        }
        if ($address && $data->address4) {
            $address .= ' ' . $data->address4;
        }
        $name = $data->name1;
        if ($name && $data->name2) {
            $name .= ' ' . $data->name2;
        }
        if ($name && $data->name3) {
            $name .= ' ' . $data->name3;
        }
        if ($name && $data->name4) {
            $name .= ' ' . $data->name4;
        }
        $saveData = [
            'id' => $data->id,
            'extern_id' => $data->id,
            'address' => ($address) ? $address : "",
            'postcode' => ($data->postalCode) ? $data->postalCode : null,
            'town' => ($data->town) ? $data->town : null,
            'contry_id' => ($data->countryId) ? $data->countryId : 0,
            'gender' => ($data->gender) ? $data->gender : null,
            'name' => ($name) ? $name : "",
            'state_id' => ($data->stateId) ? $data->stateId : null,
            'checked' => ($data->checkedAt) ? $data->checkedAt : null,
            'created' => ($data->createdAt) ? GlbF::iso2Date($data->createdAt) : null,
            'updated' => ($data->updatedAt) ? GlbF::iso2Date($data->updatedAt) : null,
            'imported' => $now
        ];
        $this->Address->save($saveData);

        if (isset($data->options) && $data->options) {
            $this->AddressOption->deleteAll([
                'address_id' => $data->id
            ]);
            foreach ($data->options as $op) {
                $this->__doImportAddressesOptionsData($op, $now);
            }
        }
        if (isset($data->pivot)) {
            $this->__doImportContactAddressData($data->pivot);
        }
    }
}