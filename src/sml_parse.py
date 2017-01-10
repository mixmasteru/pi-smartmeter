#!/usr/bin/env python

import sys
import re
import serial

start   = '1b1b1b1b01010101'
end     = '00001b1b1b1b1a'

fileName = '../data/meter3.log'

regex_total = '070100010800ff.{20}(.{8})0177'
regex_power = '0701000f0700ff.{14}(.{8})0177'
data = ""

def bytes_from_file(filename, chunksize=8192):
    with open(filename, "rb") as f:
        while True:
            chunk = f.read(chunksize)
            if chunk:
                for b in chunk:
                    yield b
            else:
                break

def addByte(char):
    global data
    #print char.encode('HEX')
    data = data + char.encode('HEX')
    endidx = data.rfind(end)
    if endidx > 0:
        startidx = data.rfind(start)
        if(startidx >= 0):
            sml_packet = data[startidx+len(start):endidx]
            #print data[startidx:(endidx+len(end))]
            #print sml_packet
            total = re.search(regex_total,sml_packet)
            total_value = str(int(total.group(1), 16) / 1e4)
            power = re.search(regex_power,sml_packet)
            power_value = str(int(power.group(1), 16) / 1e1)
            print total.group(1) + " "+total_value+" kWh"
            print power.group(1) + " "+power_value+" W"
            data = ""
            #sys.exit()

#for b in bytes_from_file(fileName):
#    addByte(b)

port = serial.Serial(
	port='/dev/ttyUSB0',
	baudrate=9600,
	parity=serial.PARITY_NONE,
	stopbits=serial.STOPBITS_ONE,
	bytesize=serial.EIGHTBITS
)

#port.open();

while True:
    char = port.read()
    addByte(char)