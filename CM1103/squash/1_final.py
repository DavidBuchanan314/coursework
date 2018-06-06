import random
import re
import matplotlib.pyplot as plt

def game(ra, rb):
	p = ra / (ra+rb)
	sa, sb = 0, 0
	
	while max(sa, sb) < 11 or abs(sa-sb) < 2:
		r = random.random()
		if r < p:
			sa += 1
		else:
			sb += 1
	
	return sa, sb

def winProbability(ra, rb, n):
	total = 0
	for _ in range(n):
		sa, sb = game(ra, rb)
		if sa > sb:
			total += 1
	return total/n

def readCSV(filename):
	csvfile = open(filename, "r")
	csvfile.readline() # discard header
	return [tuple(map(int, re.findall(r"[0-9]+", line))) for line in csvfile.readlines()]

def plotProbabilities(ratios):
	p1 = list(map(lambda r: r[0]/r[1], ratios))
	p2 = list(map(lambda r: winProbability(r[0], r[1], 10000), ratios))

	plt.plot(p1, p2, "ro")
	plt.xlabel("ra/rb")
	plt.ylabel("player a win probability")
	plt.savefig("csvfig.svg")

random.seed(57)

print("Test results:")
print(game(70,30))
print(winProbability(70, 30, 100000))
print(readCSV("test.csv"))

ratios = readCSV("test.csv")
plotProbabilities(ratios)
