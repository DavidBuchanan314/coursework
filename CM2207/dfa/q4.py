import dfa
from sys import argv

if len(argv) != 2:
	exit("USAGE: python3 {} dfa_filename".format(argv[0]))

M = dfa.load(open(argv[1]))

string = M.get_string()

if string is None:
	print("language empty")
else:
	print("language non-empty - {} accepted".format(string or "ε"))
