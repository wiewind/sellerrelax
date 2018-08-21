<?php

/**
 * Created by PhpStorm.
 * User: benying.zou
 * Date: 27.06.2018
 * Time: 13:43
 */
class ZoController extends AppController
{
    var $uses = ['EmptyModel', 'Import', 'Order', 'Item', 'OrderStatus', 'propertyType', 'dateType'];
    function test () {
        $json = '[{"id":1,"isErasable":true,"position":"1","names":[{"id":1,"typeId":1,"lang":"de","name":"Gel\u00f6scht am"},{"id":2,"typeId":1,"lang":"en","name":"Deleted on"}]},{"id":2,"isErasable":true,"position":"2","names":[{"id":3,"typeId":2,"lang":"de","name":"Eingang am"},{"id":4,"typeId":2,"lang":"en","name":"Entry on"}]},{"id":3,"isErasable":true,"position":"3","names":[{"id":5,"typeId":3,"lang":"de","name":"Zahlungseingang"},{"id":6,"typeId":3,"lang":"en","name":"Paid on"}]},{"id":4,"isErasable":true,"position":"4","names":[{"id":7,"typeId":4,"lang":"de","name":"zuletzt aktualisiert"},{"id":8,"typeId":4,"lang":"en","name":"Last update"}]},{"id":5,"isErasable":true,"position":"5","names":[{"id":9,"typeId":5,"lang":"de","name":"Beendet am"},{"id":10,"typeId":5,"lang":"en","name":"Completed on"}]},{"id":6,"isErasable":true,"position":"6","names":[{"id":11,"typeId":6,"lang":"de","name":"Retourniert am"},{"id":12,"typeId":6,"lang":"en","name":"Return date"}]},{"id":7,"isErasable":true,"position":"7","names":[{"id":13,"typeId":7,"lang":"de","name":"Zahlungsziel"},{"id":14,"typeId":7,"lang":"en","name":"Payment due date"}]},{"id":8,"isErasable":true,"position":"8","names":[{"id":15,"typeId":8,"lang":"de","name":"voraussichtliches Versanddatum"},{"id":16,"typeId":8,"lang":"en","name":"estimated shipping date"}]},{"id":9,"isErasable":true,"position":"9","names":[{"id":17,"typeId":9,"lang":"de","name":"Startdatum"},{"id":18,"typeId":9,"lang":"en","name":"Start date"}]},{"id":10,"isErasable":true,"position":"10","names":[{"id":19,"typeId":10,"lang":"de","name":"Enddatum"},{"id":20,"typeId":10,"lang":"en","name":"End date"}]},{"id":11,"isErasable":true,"position":"11","names":[{"id":21,"typeId":11,"lang":"de","name":"Versanddatum f\u00fcr Vorbestellung"},{"id":22,"typeId":11,"lang":"en","name":"Shipping date for advanced orders"}]},{"id":12,"isErasable":true,"position":"12","names":[{"id":23,"typeId":12,"lang":"de","name":"\u00dcbertragungsdatum Marktplatz"},{"id":24,"typeId":12,"lang":"en","name":"Transfer date Marketplace"}]},{"id":13,"isErasable":false,"position":"13","names":[{"id":25,"typeId":13,"lang":"de","name":"K\u00fcndigungsdatum"},{"id":26,"typeId":13,"lang":"en","name":"Cancellation date"}]},{"id":14,"isErasable":false,"position":"14","names":[{"id":27,"typeId":14,"lang":"de","name":"Letzter Durchlauf"},{"id":28,"typeId":14,"lang":"en","name":"Last run"}]},{"id":15,"isErasable":false,"position":"15","names":[{"id":29,"typeId":15,"lang":"de","name":"N\u00e4chster Durchlauf"},{"id":30,"typeId":15,"lang":"en","name":"Next run"}]}]';
        $json = json_decode($json);
        //GlbF::printArray($json);

        foreach ($json as $d) {
            $data = [
                'id' => $d->id,
                'master' => 'orders',
                'isErasable' => $d->isErasable,
                'position' => $d->position
            ];
            foreach ($d->names as $name) {
                if ($name->lang == 'de') {
                    $data['nameDe'] = $name->name;
                } else if ($name->lang == 'en') {
                    $data['nameEn'] = $name->name;
                }
            }
            $this->dateType->create();
            $this->dateType->save($data);
        }

        echo "complete with " . count($json) . " statuses!";
        $this->autoRender = false;
    }
}