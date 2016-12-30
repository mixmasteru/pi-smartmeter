#!/usr/bin/python

# created: Alexander Kabza, Mar 1, 2016
# last mod: Alexander Kabza, Mar 10, 2016

import sys
import serial
import time

def bytes_from_file(filename, chunksize=8192):
    with open(filename, "rb") as f:
        while True:
            chunk = f.read(chunksize)
            if chunk:
                for b in chunk:
                    yield b
            else:
                break

# example:ech
#for b in bytes_from_file('meter.log'):
#    print b

#port = serial.Serial(
#	port='/dev/ttyAMA0',
#	baudrate=9600,
#	parity=serial.PARITY_NONE,
#	stopbits=serial.STOPBITS_ONE,
#	bytesize=serial.EIGHTBITS
#)

#port.open();

start = '1b1b1b1b01010101'
end = '1b1b1b1b1a'

data = ''

#while True:
for b in bytes_from_file('meter3.log'):
	#char = port.read()
	char = b
	#print char.encode('HEX')
	data = data + char.encode('HEX')

	pos = data.find(start)
	if (pos <> -1):
		data = data[pos:len(data)]
	pos = data.find(end)
	if (pos <> -1):
		#print data + '\n'
		result = (time.strftime("%Y-%m-%d ") + time.strftime("%H:%M:%S"))

		search = '070100000000ff'
		pos = data.find(search)
		if (pos <> -1):
			pos = pos + len(search) + 10
			value = data[pos:pos + 18]
			print 'ID: ' + search + ' = ' + value + ' = ' + value.decode('HEX')

		search = '070100010800ff'
		search = '0100010800ff'
		#search = '070100020801ff'
		#search = '078181c78203ff'
		pos = data.find(search)
		if (pos <> -1):
			print 'all ' + data[pos:pos + len(search) + 14 + 16]
			pos = pos + len(search) + 14
			value = data[pos:pos + 16]
			print 'kWh: ' + search + ' = ' + value + ' = ' + str(int(value, 16) / 1000) + ' kWh'
			result = result + ';' +  str(int(value, 16) / 1e4)

		search = '070100010700ff'
		pos = data.find(search)
		if (pos <> -1):
			pos = pos + len(search) + 14
			value = data[pos:pos + 8]
#			print 'W: ' + search + ' = ' + value + ' = ' + str(int(value, 16) / 1e2) + ' W'
			result = result + ';' +  str(int(value, 16) / 1e2)

		file = open('index.html', 'w')
		file.write (result)
		file.close()

		file = open('output.csv', 'a')
		file.write (result + '\n')
		file.close()

#		file = open('message.dat', 'w')
#		file.write (data)
#		file.close()

		data = ''
