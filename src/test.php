<?php
require_once 'SMLParser.php';


$sml_parser = new \pismartmeter\SMLParser();
$sml_parser->parse_sml_file('../data/meter2.log');
$values = $sml_parser->get_first_values();

print_r($values);

/*
$time = date('Y-m-d H:i:s',filemtime($pathname.$file));

$OBIS_1_8_1 = $values['0100010801FF']['value']*$values['0100010801FF']['scaler']/1000; # Wh -> kWh
$public_key = $values['8181C78205FF']['value'];
$active_power = $values['01000F0700FF']['value'];
*/
