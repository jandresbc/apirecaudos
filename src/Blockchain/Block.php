<?php
/*
* @autor: Julio Andres Barrera Carvajal - devstudio.me
* @Description: Clases para implementar blockchain - 2018
*/

namespace App\Blockchain;

class Block {

    public $data = [];
    public $previousHash = null;
    public $hash = null;
    private $keyHashSoftware = "QWe89..//&&";

    public function __construct($xData = [], $previousHash = '') {
        $this->previousHash = $previousHash == '' ? 'Init Genesis' : $previousHash;
        $this->data = $xData;
        $this->hash = $this->calculateHash();
    }

    public function calculateHash() {
      if(isset($this->data["data"])){
        $this->data["data"] = is_array($this->data["data"]) ? implode("~",$this->data["data"]) : $this->data["data"];
        $dataChain = implode("~",$this->data);
      }else{//Para calcular el Genesis.
        $dataChain = implode("~",$this->data);
      }

      return hash("sha256",$this->keyHashSoftware.$dataChain.$this->keyHashSoftware);
    }
}
