import dfa
from sys import argv

if len(argv) != 3:
	exit("USAGE: python3 {} dfa1_filename dfa2_filename".format(argv[0]))

M = dfa.load(open(argv[1]))
Mʹ = dfa.load(open(argv[2]))

intersection = M & Mʹ
print(intersection)

dfa.save_jff(intersection, "q2.jff")
# jflap2tikz -k -i q2.jff -o q2.tex
