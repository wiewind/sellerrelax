<?php

/**
 * Created by PhpStorm.
 * User: benying.zou
 * Date: 04.09.2018
 * Time: 10:48
 */
class ExportController extends AppController
{

    var $uses = [
        'VwSkuQuantity',
        'VwSkuPredictionsV1',
        'VwSkuPredictionsV2',
        'Items'
    ];

    function getStorePlan () {
        $this->layout = 'csv';

        $fields =  [
            'item_id',
            'variation_extern_id',
            'variation_number',
            'recommend',
            'recommend_v2'
        ];

        $data = $this->VwSkuPredictionsV2->find('all', [
            'fields' =>$fields,
            'order' => 'variation_extern_id'
        ]);
        $this->set('header', $fields);
        $data_contents = [];
        if ($data) {
            foreach ($data as $d) {
                $data_contents[] = $d['VwSkuPredictionsV2'];
            }
        }
        $this->set('data', $data_contents);

        $this->render('csv');
    }

}