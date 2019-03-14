<?php
/**
 * Created by PhpStorm.
 * User: benying.zou
 * Date: 11.03.2019
 * Time: 17:19
 */

class Address extends AppModel {

    function doSaveAddress ($data, $now, $savePivot=1) {
        $saveData = [
            'id' => $data->id,
            'extern_id' => $data->id,
            'address1' => ($data->address1) ? $data->address1 : null,
            'address2' => ($data->address2) ? $data->address2 : null,
            'address3' => ($data->address3) ? $data->address3 : null,
            'address4' => ($data->address4) ? $data->address4 : null,
            'postcode' => ($data->postalCode) ? $data->postalCode : null,
            'town' => ($data->town) ? $data->town : null,
            'contry_id' => ($data->countryId) ? $data->countryId : 0,
            'gender' => ($data->gender) ? $data->gender : null,
            'name1' => ($data->name1) ? $data->name1 : null,
            'name2' => ($data->name2) ? $data->name2 : null,
            'name3' => ($data->name3) ? $data->name3 : null,
            'name4' => ($data->name4) ? $data->name4 : null,
            'state_id' => ($data->stateId) ? $data->stateId : null,
            'checked' => ($data->checkedAt) ? $data->checkedAt : null,
            'created' => ($data->createdAt) ? GlbF::iso2Date($data->createdAt) : null,
            'updated' => ($data->updatedAt) ? GlbF::iso2Date($data->updatedAt) : null,
            'imported' => $now
        ];
        $this->save($saveData);

        if (isset($data->options) && $data->options) {
            $this->AddressOption = ClassRegistry::init('AddressOption');
            $this->AddressOption->deleteAll([
                'address_id' => $data->id
            ]);
            foreach ($data->options as $op) {
                $this->__doImportAddressesOptionsData($op, $now);
            }
        }
        if ($savePivot && isset($data->pivot)) {
            $this->__doImportContactAddressData($data->pivot);
        }
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
        $this->AddressOption = ClassRegistry::init('AddressOption');
        $this->AddressOption->save($saveData);
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
        $this->ContactAddress = ClassRegistry::init('ContactAddress');
        $this->ContactAddress->save($saveData);
    }
}