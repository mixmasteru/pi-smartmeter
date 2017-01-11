import unittest
import sys
sys.path.append('../')
from Parser import Parser

class ParserTest(unittest.TestCase):

    def test_something(self):
        parser = Parser()
        for b in parser.bytes_from_file('../../../data/meter2.log'):
            ret = parser.add_byte(b)
            if ret:
                break

        self.assertEqual(parser.last_total, 9817.7794)
        self.assertEqual(parser.last_power, 247.0)


if __name__ == '__main__':
    unittest.main()
