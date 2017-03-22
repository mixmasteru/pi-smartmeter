from __future__ import print_function

import boto3
import json

print('Loading function')



def lambda_handler(event, context):
    '''Write data from IoT devices to dynamodb via Lambda
    '''
    #print("Received event: " + json.dumps(event, indent=2))

    dynamo = boto3.resource('dynamodb').Table('TableName')
    dynamo.put_item()

    return True
