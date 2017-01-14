# pi-smartmeter

### install python
```commandline
$ sudo apt-get install python-pip
$ sudo pip install pyserial
$ sudo pip install AWSIoTPythonSDK
```

### setup AWS IoT
```commandline
$ pip install awscli
$ aws configure
$ aws iot create-thing --thing-name "theNameOfYourThing"
$ aws iot create-keys-and-certificate --set-as-active --certificate-pem-outfile cert.pem --public-key-outfile publicKey.pem --private-key-outfile privateKey.pem
$ aws iot create-policy --policy-name "PolicyName" --policy-document data/policy.json
$ aws iot attach-principal-policy --principal "certificate-arn" --policy-name "PolicyName"
$ aws iot attach-thing-principal --thing-name "theNameOfYourThing" --principal "certificate-arn"
```

### sml message

#### example message (without new lines):
```
1b1b1b1b010101017607000f163961a36200
62007263010176010107000f09fc75e10b06
454d4801001d461915010163eaaa00760700
0f163961a4620062007263070177010b0645
4d4801001d461915017262016509fca77f77
77078181c78203ff0101010104454d480177
070100000009ff010101010b06454d480100
1d4619150177070100010800ff6301820162
1e52ff560005da131a0177070100010801ff
0101621e52ff560005da131a017707010001
0802ff0101621e52ff560000000000017707
01000f0700ff0101621b52ff55000009ab01
77078181c78205ff010101018302d94aaf14
b61e1f92f50df338935c705fde00d665092e
dfb698c239f4532a2a63b7fe3712557c9c45
676e0952dab2cf2d01010163dcf200760700
0f163961a762006200726302017101633e89
00001b1b1b1b1a
```

#### total part
```
77070100010800ff63018201621e52ff560005da131a017
```
* value(hex): 05da131a
* value(dec): 98177818 (9817.7818 kWh)

#### power part
```
770701000f0700ff0101621b52ff55000009ab017
```
* value(hex): 000009ab
* value(dec): 2475 (247.5 W)
