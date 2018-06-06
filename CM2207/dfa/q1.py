import dfa
from sys import argv

if len(argv) != 2:
	exit("USAGE: python3 {} dfa_filename".format(argv[0]))

M = dfa.load(open(argv[1]))
print(~M)
