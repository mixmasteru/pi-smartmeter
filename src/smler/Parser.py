import re


class Parser:

    MSG_START   = '1b1b1b1b01010101'
    MSG_END     = '00001b1b1b1b1a'
    REGEX_TOTAL = '070100010800ff.{20}(.{8})0177'
    REGEX_POWER = '0701000f0700ff.{14}(.{8})0177'

    def __init__(self):
        self.data = ""
        self.last_total = 0
        self.last_power = 0

    @staticmethod
    def bytes_from_file(filename, chunksize=8192):
        with open(filename, "rb") as f:
            while True:
                chunk = f.read(chunksize)
                if chunk:
                    for b in chunk:
                        yield b
                else:
                    break

    def add_byte(self, char):
        self.data += char.encode('HEX')
        return self.parse()

    def parse(self):
        endidx = self.data.rfind(Parser.MSG_END)
        if endidx > 0:
            startidx = self.data.rfind(Parser.MSG_START)
            if startidx >= 0:
                sml_packet = self.data[startidx+len(Parser.MSG_START):endidx]
                self.last_total = self.parse_total(sml_packet)
                self.last_power = self.parse_power(sml_packet)
                self.data = ""
                return True
            else:
                self.data = ""
        return False

    def parse_total(self, sml_packet):
        total_value = None
        regex = re.compile(Parser.REGEX_TOTAL)
        total = regex.search(sml_packet)
        if total:
            total_value = int(total.group(1), 16) / 1e4
        return total_value

    def parse_power(self, sml_packet):
        power_value = None
        regex = re.compile(Parser.REGEX_POWER)
        power = regex.search(sml_packet)
        if power:
            power_value = int(power.group(1), 16) / 1e1
        return power_value
