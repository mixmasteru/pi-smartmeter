#!/usr/bin/env python

from __future__ import print_function
import time
import serial
import json
from AWSIoTPythonSDK.MQTTLib import AWSIoTMQTTClient
from smler import Parser


print("Start\n")
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
sleeps    = 60
total_intv= 300

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
print("connected\n")
#myAWSIoTMQTTClient.subscribe("sdk/test/Python", 1, customCallback)
#time.sleep(2)
last_time = time.time()

try:
    # Publish to the same topic in a loop forever
    parser = Parser()
    while True:
        try:
            byte = port.read()
            fullsml = parser.add_byte(byte)
            if fullsml:
                now = time.time()
                localtime = time.localtime(now)
                payload = {'timestamp': now,
                           'datetime': time.strftime("%Y%m%d%H%M%S", localtime),
                           'value': parser.last_power}
                myAWSIoTMQTTClient.publish(topic_cur, json.dumps(payload), 1)
                if (last_time+total_intv) <= now:
                    payload['value'] = parser.last_total
                    myAWSIoTMQTTClient.publish(topic_cnt, json.dumps(payload), 1)
                    last_time = now
                time.sleep(sleeps)
        except Exception:
            print(Exception+" with sml:\n--------------------")
            print(parser.data)
            print(Exception+"--------------------")
            raise Exception


except KeyboardInterrupt:
    print('Exit')

