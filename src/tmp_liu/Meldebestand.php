<?
class Meldenbestand
{
    public $trennzeichen = ";";
    public $fileinput="meldenbestandskus.csv";
    public $fileoutput="meldungbestand.csv";
    public $fbacustomer="255214515";
    public	$database = 'db180067_12';
    public $conn;


    function __construct(){

        $this->conn = $this->ConnectMysqlShop();

    }

    // connect to datebase from mextronic

    public function ConnectMysqlShop(){

        //config and connect to the datebase
        $host = 'mysql5.mextronic.de';
        $login = 'db180067_12';
        $password = 'wUsgE+wgfd5n';

        // see http://php.net/manual/en/function.mysql-connect.php
        // Warning: This extension was deprecated in PHP 5.5.0, and it was removed in PHP 7.0.0. Instead, the MySQLi or PDO_MySQL extension should be used.
        // See also MySQL: choosing an API guide and related FAQ for more information.
        // Alternatives to this function include: mysqli_connect(), PDO::__construct()
        $conn=mysql_connect($host,$login,$password,TRUE);

        mysql_select_db($this->database,$conn);

        return $conn;

    }

    // Query the SQL
    public function Query($sql){

        return mysql_query($sql,$this->conn);

    }

	// get skus from the csv.
	public function GetSkusFile(){

        $file = $this->fileinput;

        $skus = array();

        $file_name = $file;

        if (file_exists($file_name)){

            $h_file=file($file_name);

            foreach ($h_file as $line){
                $a_skus = explode(';',$line);
                $skus[trim($a_skus[0])] = intval($a_skus[1]);
            }
        }

        return $skus;
    }



    public function getBestand ($itemDays) {

        // factor for the last 3 month
        $factor_last_3 = 0.2;
        // factor for the last 12 month
        $factor_last_12 = 0.3;
        // facotor from the lastyear until the next 3 montah;
        $factor_last_next_3 = 0.5;


        // Days for DATE_SUB

        $last_3 = 90;
        $last_12 = 365;
        $last_next_3 = 90;


        $itemNumbers = array_keys($itemDays);

        $sql = "SELECT items_variations.id AS variation_id,
                       items_variations.extern_id AS variation_extern_id,
                       items_variations.item_id AS item_id,
                       items_variations.number AS variation_number,
                       SUM(IF((orders.enty_date >= (CURDATE() - INTERVAL {$last_3} DAY)), order_items.quantity, 0)) AS quantity_1,
                       SUM(IF((orders.enty_date >= (CURDATE() - INTERVAL {$last_12} DAY)), order_items.quantity, 0)) AS quantity_2,
                       SUM(IF(
                            (
                                (orders.enty_date >= (CURDATE() - INTERVAL 365 DAY)) 
                                AND (orders.enty_date < ((CURDATE() - INTERVAL 365 DAY) + INTERVAL ".($last_next_3 + 1)." DAY))
                            ), 
                            order_items.quantity, 
                            0
                       )) AS quantity_3
                FROM items_variations
                LEFT JOIN order_items ON items_variations.extern_id = order_items.item_variation_id
                LEFT JOIN orders ON order_items.order_id = orders.extern_id and orders.type_id = 1
                WHERE 1
                  and orders.customer_id != {$this->fbacustomer}
                GROUP BY items_variations.extern_id;";

//        echo $sql;

        $rs=$this->Query($sql);

        $res = [];
        while ($row = mysql_fetch_array($rs)) {
             if (in_array($row['variation_number'], $itemNumbers)) {
                 $q1 = $row['quantity_1'] / $last_3 * $factor_last_3;
                 $q2 = $row['quantity_2'] / $last_12 * $factor_last_12;
                 $q3 = $row['quantity_3'] / $last_next_3 * $factor_last_next_3;

                 $sum = ($q1 + $q2 + $q3) * $itemDays[$row['variation_number']];
                 $res[$row['variation_number']] = ceil($sum);
             }
        }
        return $res;
    }



	//check each sku and output the sku with Meldenbestand into a array

	public function GetSkuMeldungbestand() {

        $skus = $this->GetSkusFile();

        $skusmeldenungbestand = [];
        if (!empty($skus)){

            $best = $this->getBestand($skus);
            foreach ($skus as $itemNumber => $days) {
                if (array_key_exists($itemNumber, $best)) {
                    $skusmeldenungbestand[] = [$itemNumber, $best[$itemNumber]];
                } else {
                    $skusmeldenungbestand[] = [$itemNumber, 0];
                }
            }

        }
        return $skusmeldenungbestand;
    }

	public function WriteMeldungbestand(){
        $skusmeldenbestand = $this->GetSkuMeldungbestand();

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="'.$this->fileoutput.'"');
        header('Cache-Control: private, max-age=1');

        foreach ($skusmeldenbestand as $row) {
            echo implode(';', $row) . "\n";
        }
    }
}

$Melden= new Meldenbestand;

$Melden->WriteMeldungbestand();


?>