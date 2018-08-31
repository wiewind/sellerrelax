<?php

/**
 * Created by PhpStorm.
 * User: benying.zou
 * Date: 04.06.2018
 * Time: 15:51
 */
class ItemsController extends AppController
{
    var $uses = ['Order', 'Item'];

    public function getTopArticle ($date_from, $date_to, $menge=10) {
        $sql = "select OrderItemsItemText
                from items
                inner join orders on items.OrderHeadOrderID = orders.OrderHeadOrderID and OrderHeadOrderTypeID = 1 and OrderCompletedAt > ''
                ";
    }
}