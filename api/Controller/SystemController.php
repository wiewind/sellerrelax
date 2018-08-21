<?php

/**
 * Created by PhpStorm.
 * User: benying.zou
 * Date: 04.05.2018
 * Time: 13:56
 */
class SystemController extends AppController
{
    var $uses = ['EmptyModel', 'Import', 'Order', 'Item'];

    var $tHeader = [
        'orders' => [],
        'items' => []
    ];
    var $csvHeader = [];

    var $importPath;
    var $importTmpPath;

    var $importData = [];

    var $startTime = 0;
    var $timeout = 60;


    var $row = 0;

    function setImportPath () {
        $this->importPath = $_SERVER['DOCUMENT_ROOT'] .  Configure::read('system.import.path');
        $this->importTmpPath = $this->importPath . '/' . Configure::read('system.import.tmp.dirname');
        GlbF::mkDir($this->importTmpPath);
    }

    private function __creatImportTableIfNotExist () {
        $sql = "SELECT table_name FROM information_schema.TABLES WHERE table_name ='imports';";
        $data = $this->EmptyModel->query($sql);
        if (!$data) {
            $sql = "CREATE TABLE `imports` (".
                "`id` int(11) unsigned NOT NULL AUTO_INCREMENT,".
                "`filename` varchar(45) NOT NULL,".
                "`row` int(11) unsigned NOT NULL DEFAULT '0',".
                "`imported` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,".
                "`finished` tinyint(1) DEFAULT '0',".
                "PRIMARY KEY (`id`)".
                ") ENGINE=InnoDB DEFAULT CHARSET=utf8;";
            $this->EmptyModel->query($sql);
            CakeLog::write('import', 'create table imports: ' . $sql);
        }
    }

    function setNewImportFile () {
        if (!$this->importPath || !$this->importTmpPath) {
            $this->setImportPath();
        }
        $path = $this->importPath;
        $d = dir($path);
        while (false !== ($entry = $d->read())) {
            if (!is_dir($path.'/'.$entry) && !in_array($entry, ['.', '..']) && GlbF::getFileSuffix($entry) === 'csv') {
                $newFilename = '';
                do {
                    $newFilename = GlbF::getRandomStr(20).'.csv';
                } while (is_file($this->importTmpPath . '/' . $newFilename));

                $newFile = $this->importTmpPath . '/' . $newFilename;
                if (!@ rename($path.'/'.$entry, $newFile)) {
                    ErrorCode::throwException(sprintf(__("%s can't be removed into work path!"), $entry));
                }

                $this->__creatImportTableIfNotExist();
                $this->Import->create();
                $this->Import->save([
                    'filename' => $newFilename
                ]);
            }
        }
        $d->close();
    }

    function setOldImportFile () {
        if (!$this->importPath || !$this->importTmpPath) {
            $this->setImportPath();
        }
        $path = $this->importTmpPath;

        $data = $this->Import->find('all', [
            'conditions' => [
                'finished' => 0
            ]
        ]);

        if ($data) {
            foreach ($data as $d) {
                $im = $d['Import'];
                if (is_file($path . '/' . $im['filename'])) {
                    $this->importData = $im;
                    return;
                } else {
                    $this->Import->save([
                        'id' => $im['id'],
                        'finished' => 1
                    ]);
                }
            }
        }
    }

    function setImportFile () {
        $this->setNewImportFile();
        $this->setOldImportFile();
        if (!$this->importData) {
            ErrorCode::throwException(__('No file to be imported!'));
        }
    }

    function import () {
        ini_set('max_execution_time', 0);
        ini_set("memory_limit","256M");

        $this->setImportFile();

        $importFile = $this->importTmpPath . '/' . $this->importData['filename'];

        $this->startTime = time();

        CakeLog::write('import', '###############################################');
        CakeLog::write('import', '## start import "' . $this->importData['filename'] . '" at ' . date('Y-m-d H:i:s') . '...');

        $finish = 1;
        if (($handle = fopen($importFile, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 10000, ";")) !== FALSE) {
                if ($this->row === 0) {
                    $this->csvHeader = $data;
                    $this->__readHead();
                } else if ($this->row >= $this->importData['row']) {
                    $this->__setTableValues($data);
                }
                $this->row++;
                $nowTime = time();
                if ($nowTime-$this->startTime >= $this->timeout) {
                    $finish = 0;
                    break;
                }
            }
            fclose($handle);
        }

        CakeLog::write('import', 'end import with ' . ($this->row - 1) . ' input.');
        CakeLog::write('import', '###############################################');

        if ($this->row > $this->importData['row'] || $finish != $this->importData['finished']) {
            $this->Import->save([
                'id' => $this->importData['id'],
                'row' => $this->row,
                'finished' => $finish
            ]);
            if ($finish) {
                @ unlink($importFile);
            }
        }

        return [
            'file' => $this->importData['filename'],
            'start' => $this->importData['row'],
            'end' => $this->row,
            'finished' => $finish
        ];
    }

    private function __readHead () {
        foreach ($this->csvHeader as $index => $h) {
            if ($h === 'OrderHeadOrderID') {
                $this->tHeader['orders'][$h] = $index;
                $this->tHeader['items'][$h] = $index;
            } else if (substr($h, 0, strlen('OrderItems')) === 'OrderItems') {
                $this->tHeader['items'][$h] = $index;
            } else {
                $this->tHeader['orders'][$h] = $index;
            }
        }
        $this->__alterTables();
    }

    private function __alterTables () {
        $db = $this->EmptyModel->getDataSource();
        $tables = $db->listSources();

        foreach ($this->tHeader as $tName => $cols) {
            if (count($cols) === 0) continue;
            $tName = strtolower($tName);
            if (in_array($tName, $tables)) {
                // check columns, when new than add
                $tInfo = $db->describe($tName);
                $add = [];
                foreach ($cols as $colName => $colIndex) {
                    if (!array_key_exists($colName, $tInfo)) {
                        $add[] = "ADD COLUMN `{$colName}` TEXT NULL";
                    }
                }
                if ($add) {
                    $sql = "ALTER TABLE `{$tName}` " . implode(',', $add) . ';';
                    $this->EmptyModel->query($sql);

                    CakeLog::write('import', 'change table: ' . $sql);
                }
            } else {
                // create new table
                $sql = "create table `{$tName}` ( `id` int(10) unsigned NOT NULL AUTO_INCREMENT,";
                foreach ($cols as $colName => $colIndex) {
                    $sql .= "`{$colName}` TEXT DEFAULT NULL,";
                }
                $sql .= "`imported` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, ".
                        "PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                $this->EmptyModel->query($sql);

                CakeLog::write('import', 'create table ' . $tName . ': ' . $sql);
            }
        }
    }

    private function __isOrder ($data) {
        return empty($data[$this->tHeader['items']['OrderItemsID']])
            && empty($data[$this->tHeader['items']['OrderItemsItemText']])
            && empty($data[$this->tHeader['items']['OrderItemsReferrerID']])
            && empty($data[$this->tHeader['items']['OrderItemsTypeID']])
            && empty($data[$this->tHeader['items']['OrderItemsQuantity']])
            && empty($data[$this->tHeader['items']['OrderItemsVAT']])
            && empty($data[$this->tHeader['items']['OrderItemsReferrerID']])
            && empty($data[$this->tHeader['items']['OrderItemsAmountPriceBrutto']])
            && empty($data[$this->tHeader['items']['OrderItemsAmountCurrency']])
            && empty($data[$this->tHeader['items']['OrderItemsVariantItemID']]);
    }

    private function __setTableValues ($data) {
        if ($this->__isOrder($data)) {
            $this->Order->deleteAll([
                'OrderHeadOrderID' => $data[$this->tHeader['orders']['OrderHeadOrderID']]
            ]);
            $this->Item->deleteAll([
                'OrderHeadOrderID' => $data[$this->tHeader['items']['OrderHeadOrderID']]
            ]);

            foreach ($this->tHeader['orders'] as $colName => $colIndex) {
                $values[$colName] = $data[$colIndex];
            }
            $this->Order->create();
            $this->Order->save($values);

            CakeLog::write('import', 'input order ' . $data[$this->tHeader['orders']['OrderHeadOrderID']]);
        } else {
            foreach ($this->tHeader['items'] as $colName => $colIndex) {
                $values[$colName] = $data[$colIndex];
            }
            $this->Item->create();
            $this->Item->save($values);
        }
    }


    public function mail () {
        $Email = new CakeEmail('gmail');
        $Email->from('zoubenying@hotmail.com');
        $Email->to('zoubenying@gmail.com');
        $Email->subject('test');

        $Email->send("Hallo");
    }
}