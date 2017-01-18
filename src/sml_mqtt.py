#!/usr/bin/env python

from AWSIoTPythonSDK.MQTTLib import AWSIoTMQTTClient
import time
import serial
from smler import Parser

port = serial.Serial(
    port='/dev/ttyUSB0',
    baudrate=9600,
    parity=serial.PARITY_NONE,
    stopbits=serial.STOPBITS_ONE,
    bytesize=serial.EIGHTBITS
)

host      = "ABCDEFGHIJKLMN.iot.ZONE.amazonaws.com"
topic_root= "n4"
topic_cnt = topic_root+"/meter/power/count"
topic_cur = topic_root+"/meter/power/current"
sleeps    = 5
total_intv= 60

myAWSIoTMQTTClient = AWSIoTMQTTClient("smartpi1")
myAWSIoTMQTTClient.configureEndpoint(host, 8883)
myAWSIoTMQTTClient.configureCredentials("root-CA.crt", "private.key", "cert.pem")

# AWSIoTMQTTClient connection configuration
myAWSIoTMQTTClient.configureAutoReconnectBackoffTime(1, 32, 20)
myAWSIoTMQTTClient.configureOfflinePublishQueueing(-1)  # Infinite offline Publish queueing
myAWSIoTMQTTClient.configureDrainingFrequency(2)  # Draining: 2 Hz
myAWSIoTMQTTClient.configureConnectDisconnectTimeout(10)  # 10 sec
myAWSIoTMQTTClient.configureMQTTOperationTimeout(5)  # 5 sec

# Connect and subscribe to AWS IoT
myAWSIoTMQTTClient.connect()
#myAWSIoTMQTTClient.subscribe("sdk/test/Python", 1, customCallback)
#time.sleep(2)
last_time = time.time()

try:
    # Publish to the same topic in a loop forever
    parser = Parser()
    while True:
        byte = port.read()
        fullsml = parser.add_byte(byte)
        if fullsml:
            now = time.time()
            myAWSIoTMQTTClient.publish(topic_cur, parser.last_power, 1)
            if (last_time+total_intv) <= now:
                myAWSIoTMQTTClient.publish(topic_cnt, parser.last_total, 1)
                last_time = now
            time.sleep(sleeps)
except KeyboardInterrupt:
    print 'Exit'