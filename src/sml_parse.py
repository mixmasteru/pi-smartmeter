#!/usr/bin/env python

import serial
from smler import Parser

port = serial.Serial(
	port='/dev/ttyUSB0',
	baudrate=9600,
	parity=serial.PARITY_NONE,
	stopbits=serial.STOPBITS_ONE,
	bytesize=serial.EIGHTBITS
)

#port.open();

parser = Parser()
while True:
    byte = port.read()
    parser.add_byte(byte)