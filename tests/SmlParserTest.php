<?php
namespace test;

use PHPUnit\Framework\TestCase;
use pismartmeter\SMLParser;


class SmlParserTest extends TestCase
{
    /**
     * @var SMLParser
     */
    protected $sml_parser;

    /**
     * @var string
     */
    protected $test_data_file1 =  __DIR__ ."/data/meter.bin";

    public function setUp()
    {
        parent::setUp();
        $this->sml_parser = new SMLParser();
    }

    /**
     * @dataProvider providerParseFile
     * @param $arr_expect
     */
    public function testParseFile($arr_expect)
    {
        $this->sml_parser->parse_sml_file($this->test_data_file1);
        $arr_parsed = $this->sml_parser->get_first_values();
        $this->assertEquals($arr_parsed,$arr_expect);
    }

    /**
     * @return array
     */
    public function providerParseFile(){
        return
            array(
                array(
                    'arr_expect' =>
                        array (
                            '8181C78203FF' =>
                                array (
                                    'objName' => '8181C78203FF',
                                    'status' => NULL,
                                    'valTime' => NULL,
                                    'unit' => NULL,
                                    'scaler' => 0,
                                    'value' => 'EMH',
                                    'valueSignature' => NULL,
                                    'OBIS' => '129-129:199.130.3*255',
                                    'OBIS-Text' => 'Hersteller-ID ',
                                ),
                            '0100000009FF' =>
                                array (
                                    'objName' => '0100000009FF',
                                    'status' => NULL,
                                    'valTime' => NULL,
                                    'unit' => NULL,
                                    'scaler' => 0,
                                    'value' => '06454D4801001D461915',
                                    'valueSignature' => NULL,
                                    'OBIS' => '1-0:0.0.9*255',
                                    'OBIS-Text' => ' Geraeteeinzelidentifikation',
                                ),
                            '0100010800FF' =>
                                array (
                                    'objName' => '0100010800FF',
                                    'status' => '0182',
                                    'valTime' => NULL,
                                    'unit' => 'Wh',
                                    'scaler' => 0.10000000000000001,
                                    'value' => 98177794,
                                    'valueSignature' => NULL,
                                    'OBIS' => '1-0:1.8.0*255',
                                    'OBIS-Text' => 'Wirkarbeit Bezug Total: Zaehlerstand',
                                ),
                            '0100010801FF' =>
                                array (
                                    'objName' => '0100010801FF',
                                    'status' => NULL,
                                    'valTime' => NULL,
                                    'unit' => 'Wh',
                                    'scaler' => 0.10000000000000001,
                                    'value' => 98177794,
                                    'valueSignature' => NULL,
                                    'OBIS' => '1-0:1.8.1*255',
                                    'OBIS-Text' => 'Wirk-Energie Tarif 1 Bezug',
                                ),
                            '0100010802FF' =>
                                array (
                                    'objName' => '0100010802FF',
                                    'status' => NULL,
                                    'valTime' => NULL,
                                    'unit' => 'Wh',
                                    'scaler' => 0.10000000000000001,
                                    'value' => 0,
                                    'valueSignature' => NULL,
                                    'OBIS' => '1-0:1.8.2*255',
                                    'OBIS-Text' => 'Wirk-Energie Tarif 2 Bezug',
                                ),
                            '01000F0700FF' =>
                                array (
                                    'objName' => '01000F0700FF',
                                    'status' => NULL,
                                    'valTime' => NULL,
                                    'unit' => 'Wh',
                                    'scaler' => 0.10000000000000001,
                                    'value' => 2470,
                                    'valueSignature' => NULL,
                                    'OBIS' => '1-0:15.7.0*255',
                                    'OBIS-Text' => 'Active Power',
                                ),
                            '8181C78205FF' =>
                                array (
                                    'objName' => '8181C78205FF',
                                    'status' => NULL,
                                    'valTime' => NULL,
                                    'unit' => NULL,
                                    'scaler' => 0,
                                    'value' => 'D94AAF14B61E1F92F50DF338935C705FDE00D665092EDFB698C239F4532A2A63B7FE3712557C9C45676E0952DAB2CF2D',
                                    'valueSignature' => NULL,
                                    'OBIS' => '129-129:199.130.5*255',
                                    'OBIS-Text' => 'Public-Key',
                                ),
                        )
                )
            );
    }
}