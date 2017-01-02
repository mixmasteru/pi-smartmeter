<?php
namespace pismartmeter;


/**
 * Class SML_PARSER
 *
 * based on code from
 * @see http://blog.bubux.de/raspberry-pi-ehz-auslesen/
 */
class SMLParser {

    const SML_HEADER = '1B1B1B1B01010101';
    const SML_FOOTER = '0000001B1B1B1B1A';

    public $files;

    private $list_indent;

    private $obis_arr = array(
        '0100000000FF' => array('1-0:0.0.0*255','Seriennummer'),
        '0100010700FF' => array('1-0:1.7.0*255','Momentane Wirkleistung Bezug'),
        '0100020700FF' => array('1-0:2.7.0*255','Momentane Wirkleistung Lieferung'),
        '0100010801FF' => array('1-0:1.8.1*255','Wirk-Energie Tarif 1 Bezug'),
        '0100020801FF' => array('1-0:2.8.1*255','Wirk-Energie Tarif 1 Lieferung'),
        '0100010802FF' => array('1-0:1.8.2*255','Wirk-Energie Tarif 2 Bezug'),
        '0100020802FF' => array('1-0:2.8.2*255','Wirk-Energie Tarif 2 Lieferung'),
        '0100010803FF' => array('1-0:1.8.3*255','Wirk-Energie Tarif 3 Bezug'),
        '0100020803FF' => array('1-0:2.8.3*255','Wirk-Energie Tarif 3 Lieferung'),
        '8181C78203FF' => array('129-129:199.130.3*255','Hersteller-ID '),
        '8181C78205FF' => array('129-129:199.130.5*255','Public-Key'),
        '01000F0700FF' => array('1-0:15.7.0*255','Active Power'),
        '0100010800FF' => array('1-0:1.8.0*255','Wirkarbeit Bezug Total: Zaehlerstand'),
        '0100000009FF' => array('1-0:0.0.9*255',' Geraeteeinzelidentifikation'),
        '00006001FFFF' => array('0-0:60.1.255*255','Fabriknummer'),
    );
    private $data;
    private $crc16_global;
    private $crc16_message;

    /**
     * @var bool
     */
    private $debug = false;

    /**
     * Hilfsarray zur Berechnung der CRC
     * @var array
     */
    private $crctab = array(
    0x0000, 0x1189, 0x2312, 0x329b, 0x4624, 0x57ad, 0x6536, 0x74bf,
    0x8c48, 0x9dc1, 0xaf5a, 0xbed3, 0xca6c, 0xdbe5, 0xe97e, 0xf8f7,
    0x1081, 0x0108, 0x3393, 0x221a, 0x56a5, 0x472c, 0x75b7, 0x643e,
    0x9cc9, 0x8d40, 0xbfdb, 0xae52, 0xdaed, 0xcb64, 0xf9ff, 0xe876,
    0x2102, 0x308b, 0x0210, 0x1399, 0x6726, 0x76af, 0x4434, 0x55bd,
    0xad4a, 0xbcc3, 0x8e58, 0x9fd1, 0xeb6e, 0xfae7, 0xc87c, 0xd9f5,
    0x3183, 0x200a, 0x1291, 0x0318, 0x77a7, 0x662e, 0x54b5, 0x453c,
    0xbdcb, 0xac42, 0x9ed9, 0x8f50, 0xfbef, 0xea66, 0xd8fd, 0xc974,
    0x4204, 0x538d, 0x6116, 0x709f, 0x0420, 0x15a9, 0x2732, 0x36bb,
    0xce4c, 0xdfc5, 0xed5e, 0xfcd7, 0x8868, 0x99e1, 0xab7a, 0xbaf3,
    0x5285, 0x430c, 0x7197, 0x601e, 0x14a1, 0x0528, 0x37b3, 0x263a,
    0xdecd, 0xcf44, 0xfddf, 0xec56, 0x98e9, 0x8960, 0xbbfb, 0xaa72,
    0x6306, 0x728f, 0x4014, 0x519d, 0x2522, 0x34ab, 0x0630, 0x17b9,
    0xef4e, 0xfec7, 0xcc5c, 0xddd5, 0xa96a, 0xb8e3, 0x8a78, 0x9bf1,
    0x7387, 0x620e, 0x5095, 0x411c, 0x35a3, 0x242a, 0x16b1, 0x0738,
    0xffcf, 0xee46, 0xdcdd, 0xcd54, 0xb9eb, 0xa862, 0x9af9, 0x8b70,
    0x8408, 0x9581, 0xa71a, 0xb693, 0xc22c, 0xd3a5, 0xe13e, 0xf0b7,
    0x0840, 0x19c9, 0x2b52, 0x3adb, 0x4e64, 0x5fed, 0x6d76, 0x7cff,
    0x9489, 0x8500, 0xb79b, 0xa612, 0xd2ad, 0xc324, 0xf1bf, 0xe036,
    0x18c1, 0x0948, 0x3bd3, 0x2a5a, 0x5ee5, 0x4f6c, 0x7df7, 0x6c7e,
    0xa50a, 0xb483, 0x8618, 0x9791, 0xe32e, 0xf2a7, 0xc03c, 0xd1b5,
    0x2942, 0x38cb, 0x0a50, 0x1bd9, 0x6f66, 0x7eef, 0x4c74, 0x5dfd,
    0xb58b, 0xa402, 0x9699, 0x8710, 0xf3af, 0xe226, 0xd0bd, 0xc134,
    0x39c3, 0x284a, 0x1ad1, 0x0b58, 0x7fe7, 0x6e6e, 0x5cf5, 0x4d7c,
    0xc60c, 0xd785, 0xe51e, 0xf497, 0x8028, 0x91a1, 0xa33a, 0xb2b3,
    0x4a44, 0x5bcd, 0x6956, 0x78df, 0x0c60, 0x1de9, 0x2f72, 0x3efb,
    0xd68d, 0xc704, 0xf59f, 0xe416, 0x90a9, 0x8120, 0xb3bb, 0xa232,
    0x5ac5, 0x4b4c, 0x79d7, 0x685e, 0x1ce1, 0x0d68, 0x3ff3, 0x2e7a,
    0xe70e, 0xf687, 0xc41c, 0xd595, 0xa12a, 0xb0a3, 0x8238, 0x93b1,
    0x6b46, 0x7acf, 0x4854, 0x59dd, 0x2d62, 0x3ceb, 0x0e70, 0x1ff9,
    0xf78f, 0xe606, 0xd49d, 0xc514, 0xb1ab, 0xa022, 0x92b9, 0x8330,
    0x7bc7, 0x6a4e, 0x58d5, 0x495c, 0x3de3, 0x2c6a, 0x1ef1, 0x0f78
    );

    function __construct() {
    }

    /**
     * @param $text
     * @param bool $showhexdata
     */
    function debug($text, $showhexdata=true) {
        if($this->debug)
        {
            echo "DEBUG: '$text'";
            if($showhexdata) echo " : ". substr($this->data,0,150);
            echo "\n";
        }
    }

    /**
     * @param $message
     */
    function error($message) {
        return;
        $e = new \Exception();
        $m = $e->getTraceAsString();
        $m = explode("\n",$m);
        unset($m[0]);
        $m = implode("\n",$m);

        echo("ERROR: $message ! \n".$m."\n");
    }

    /**
     * Vorlage C-Programm siehe:
     * @see  http://www.photovoltaikforum.com/datenlogger-f5/emh-ehz-protokoll-t86509.html
     *
     * @param $part
     * @param bool $global
     */
    function sml_crc16($part,$global=true) {
        $cp = $this->hex2bin($part);

        for ($i=0 ; $i<strlen($cp) ; $i++) {
            $char = ord($cp{$i});

            $this->crc16_message = ($this->crc16_message >> 8) ^ ($this->crctab[($this->crc16_message ^ $char) & 0xff]);
            if(!$global) continue;
            $this->crc16_global  = ($this->crc16_global >> 8)  ^ ($this->crctab[($this->crc16_global  ^ $char) & 0xff]);
        }
    }

    /**
     * @param $hexstr
     * @return string
     */
    public function hex2bin($hexstr) {
        $n = strlen($hexstr);
        $sbin = "";
        $i = 0;
        while($i<$n)
        {
            $a =substr($hexstr,$i,2);
            $c = pack("H*",$a);
            if ($i==0){$sbin=$c;}
            else {$sbin.=$c;}
            $i+=2;
        }
        return $sbin;
    }

    /**
     * @param $match
     */
    private function match($match) {
        if(substr($this->data,0,strlen($match)) <> $match) {
            $this->error("'$match' expected, got '".substr($this->data,0,50)."...'");
        }else{
            $this->sml_crc16($match);
            $this->data = substr($this->data,strlen($match));
            #echo "MATCH: $match\n";
        }
    }

    /**
     * @param $len
     * @return string
     */
    private function read($len) {
        if($len==0) {
            return '';
        }

        if(strlen($this->data)< ($len*2)) $this->error("can't read enough bytes");
        $result = substr($this->data,0,2*$len);
        $this->data = substr($this->data,2*$len);

        $this->sml_crc16($result);
        return $result;
    }

    /**
     * @param int $list_item
     * @return number|string|void
     */
    private function parse_sml_data($list_item=0) {
        $TYPE_LEN = $this->read(1);

        if($TYPE_LEN=='00') {
            return $TYPE_LEN; # EndOfSmlMessage
        }

        $TYPE = $TYPE_LEN{0}.'x';     # only high-nibble
        $LEN  = hexdec($TYPE_LEN{1}); # only low-nibble

        while($TYPE{0} &0x8) {  # Multi-Byte TypeLen-Field
            $LEN = $LEN * 0x10;
            $TYPE_LEN = $this->read(1);
            $TYPE = $TYPE_LEN{0}.'x';     # only high-nibble
            $LEN  += hexdec($TYPE_LEN{1}); # only low-nibble
            $LEN--; # 1 abziehen wegen zusätzlichem TL-Byte
        }

        if($LEN==1) return;

        switch($TYPE) {
            case '0x': # Octet
                #return $this->hex2bin($this->read($LEN-1));
                return $this->read($LEN-1);
                break;

            case '5x': # Integer
                return hexdec($this->read($LEN-1));
                break;

            case '6x': # UnsignedInt
                return hexdec($this->read($LEN-1));
                break;

            case '7x': # List
                $this->list_indent++;
                for($i=1;$i<=$LEN;$i++) $this->parse_sml_data($i);
                $this->list_indent--;
                break;

            default :
                $this->error("Error, unexpected type '$TYPE' TL=$TYPE_LEN ".$this->data);
        }

        #echo "\n";
        return $TYPE_LEN;
    }

    /**
     * @return string|void
     */
    private function readOctet() {
        $TYPE_LEN = $this->read(1);
        if($TYPE_LEN=='01') return;

        if($TYPE_LEN{0}=='0') {
            $LEN  = hexdec($TYPE_LEN{1}); # only low-nibble
            $octet = $this->read($LEN-1);
            return $octet;
        }else{
            return "[Error, cant read octet : $TYPE_LEN]";
        }
    }

    /**
     * @return string|void
     */
    private function readInteger() {
        $TYPE_LEN = $this->read(1);
        if($TYPE_LEN=='01') return;

        if(!empty($TYPE_LEN{0}) && $TYPE_LEN{0}=='5') {
            $LEN  = hexdec($TYPE_LEN{1}); # only low-nibble
            $integer = $this->read($LEN-1);
            return $integer;
        }else{
            return "[Error, cant read unsigned : $TYPE_LEN]";
        }
    }

    /**
     * @return string|void
     */
    private function readUnsigned() {
        $TYPE_LEN = $this->read(1);
        if($TYPE_LEN=='01') return;

        if(!empty($TYPE_LEN{0}) && $TYPE_LEN{0}=='6') {
            $LEN  = hexdec($TYPE_LEN{1}); # only low-nibble
            $unsigned = $this->read($LEN-1);
            return $unsigned;
        }else{
            return "[Error, cant read unsigned : $TYPE_LEN]";
        }
    }

    /**
     * @return int|number
     */
    private function readInteger8() {
        $val = hexdec($this->readInteger());
        if($val & 0x80) $val = 0xfe - $val;

        return $val;
    }

    # =============================================================================================
    # High-Level SML-Funktionen
    # =============================================================================================

    /**
     * @return mixed
     */
    private function readOpenResponse() {
        $this->match('76'); # 76 = List of 6 items
        $result['codepage']    = $this->readOctet();
        $result['clientId']    = $this->readOctet();
        $result['reqFileId']   = $this->readOctet();
        $result['serverId']    = $this->readOctet();
        $result['refTime']     = $this->readOctet();
        $result['sml-Version'] = $this->readOctet();

        return $result;
    }

    /**
     * @return mixed
     */
    private function readCloseResponse() {
        $this->match('71'); # 71 = List of 1 item
        $result['signature']   = $this->readOctet();

        return $result;
    }

    private function readListEntry() {
        $this->match('77'); # 77 = List of 7 item

        $result['objName']          = $this->readOctet();
        $result['status']           = $this->readUnsigned();
        $result['valTime']          = $this->parse_sml_data();
        $result['unit']             = $this->readUnsigned();
        $result['scaler']           = $this->readInteger8();
        $result['value']            = $this->parse_sml_data();
        $result['valueSignature']   = $this->readOctet();

        if(isset($this->obis_arr[$result['objName']])) {
            $result['OBIS']=$this->obis_arr[$result['objName']][0];
            $result['OBIS-Text']=$this->obis_arr[$result['objName']][1];
        }

        if(in_array($result['objName'],array('8181C78203FF','0100000000FF','00006001FFFF'))) {
            # ggfs. weitere objNames in die Liste aufnehmen
            $result['value'] = $this->hex2bin($result['value']);
        }

        switch($result['unit']) {
            case '1B' : $result['unit']='W';
            case '1E' : $result['unit']='Wh';
        }

        if($result['scaler']) $result['scaler'] = pow(10,$result['scaler']);

        return $result;
    }

    private function readValList() {
        $this->debug('ENTER readValList');
        $TYPE_LEN = $this->read(1);

        if($TYPE_LEN{0}=='7') {
            $LEN = hexdec($TYPE_LEN{1});
            for($i=0;$i<$LEN;$i++) {
                $this->debug("ENTER readListEntry [$i]");
                $result[]=$this->readListEntry($this->data);
            }
            $this->debug('EXIT readValList : '.print_r($result,true),false);
            return $result;
        }else{
            echo('Error reading value-list!');
        }
    }

    #####################################################################################

    private function readListResponse() {
        $this->match('77'); # 77 = List of 1 item
        $result['clientId']         = $this->readOctet();
        $result['serverId']         = $this->readOctet();
        $result['listName']         = $this->readOctet();
        $result['actSensorTime']    = $this->parse_sml_data();
        $result['vallist']          = $this->readValList();
        $result['actGatewayTime']   = $this->parse_sml_data();
        $result['signature']        = $this->readOctet();

        return $result;
    }

    private function readMessageBody() {
        $this->match('72'); # 72 = List of 2 items
        $result['choice']  = $this->readUnsigned();
        switch($result['choice']) {

            case '0101':
                $this->debug('PROCESS OpenRequest');

                $result['choice']='OpenRequest';
                $result['body'] = $this->readOpenResponse();
                break;

            case '0201':
                $this->debug('PROCESS CloseRequest');

                $result['choice']='CloseRequest';
                $result['body'] = $this->readCloseResponse();
                break;

            case '0701':
                $this->debug('PROCESS GetListResponse');

                $result['choice']='GetListResponse';
                $result['body'] = $this->readListResponse();
                break;

            default:
                $this->debug('PROCESS UnknownRequest ('.$result['choice'].')');
                //$result['body']  = $this->parse_sml_data($this->data);
        }

        return $result;
    }

    private function parse_sml_message() {
        $this->debug('ENTER parse_sml_message');

        $this->crc16_message = 0xFFFF; # Pruefsumme zuruecksetzen

        $this->match('76');       # 76 = List of 6 items
        $result['transactionId'] = $this->readOctet();
        $result['groupNo']       = $this->readUnsigned();
        $result['abortOnError']  = $this->readUnsigned();
        $result['messageBody']   = $this->readMessageBody();

        $crc_calc = strtoupper(substr('000'.dechex(($this->crc16_message ^ 0xffff)),-4));
        $result['crc_calc'] = substr($crc_calc,-2).substr($crc_calc,0,2); # Wert 4-stellig ausgeben

        $result['crc16']         = $this->readUnsigned();
        $this->match('00');       # endOfSmlMsg = 00

        $result['crcMsgCheck'] = ($result['crc_calc'] == $result['crc16']);

        $this->debug('EXIT parse_sml_message. CRC='.(($result['crcMsgCheck'])?'OK':'FAIL'),false);
        $this->debug('--------------------------------',false);
        return $result;
    }

    # =============================================================================================
    # Schnittstellenfunktionen
    # =============================================================================================

    public function parse_sml_hexdata($hexdata) {
        $this->files = array();
        $this->data = strtoupper($hexdata);

        $start = strpos($this->data,self::SML_HEADER);
        if($start===false) return;

        if($start) {
            #echo "$start bytes skipped at begining!\n";
            $this->data=substr($this->data,$start);
        }

        while($this->data) {
            $skip = false;
            $messages = array();

            $this->crc16_global = 0xffff;
            $this->match(self::SML_HEADER);

            while($this->data<>'' && substr($this->data,0,16)!= self::SML_FOOTER) {
                $message = $this->parse_sml_message();
                if($message['crcMsgCheck']) {
                    $messages[] = $message;
                }/*else{ # if no success, skip to next file
                    $start = strpos($this->data,self::SML_HEADER);
                    if($start===false) return;

                    if($start) {
                        #echo "$start bytes skipped in between!\n";
                        $this->data=substr($this->data,$start);
                        $skip=true;
                        break;
                    }

                }*/
            }

            if($skip) continue;

            $this->match(self::SML_FOOTER);
            $this->match('03');

            $crc_calc = strtoupper(substr('000'.dechex(($this->crc16_global ^ 0xffff)),-4));
            $crc_calc = substr($crc_calc,-2).substr($crc_calc,0,2); # Wert 4-stellig ausgeben
            $crc16 = $this->read(2);

            $this->files[] = array(
                'crcFileCheck'=>($crc_calc == $crc16),
                'messages'=>$messages
            );
        }
    }

    /**
     * @param $string
     */
    public function parse_sml_string($string) {
        return $this->parse_sml_hexdata(bin2hex($string));
    }

    /**
     * @param $filename
     */
    public function parse_sml_file($filename) {
        $this->parse_sml_hexdata(strtoupper(bin2hex(file_get_contents($filename))));
    }

    /**
     * @return array
     */
    public function get_first_values() {
        foreach($this->files as $file) {
            foreach($file['messages'] as $message) {
                if($message['messageBody']['choice']=='GetListResponse') {
                    $vallist = $message['messageBody']['body']['vallist'];

                    $result = array();
                    foreach($vallist as $value)
                        $result[$value['objName']]=$value;

                    return $result;
                }
            }
        }

        return array();
    }
}
