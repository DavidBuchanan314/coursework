import matplotlib.pyplot as plt
import numpy as np

SCALE=(8,5)

def read_data(filename):
	return [(int(n.strip()) for n in line.split(",")) for line in open(filename).read().split("\n") if line]

def print_10_points(data):
	print("\\begin{center}")
	print("\\begin{tabular}{ |c|c| } ")
	print("\\hline")
	print("n & clock cycles \\\\")
	print("\\hline")
	num_points = len(x)
	for i in range(0, num_points, num_points//10):
		print("{} \\\\".format(" & ".join([str(n) for n in data[i]])))
	print("\\hline")
	print("\\end{tabular}")
	print("\\end{center}")

x, y = zip(*read_data("../results/csort_cache_n.csv"))

print("COUNTING CACHE")
print_10_points(list(zip(x, y)))

plt.figure(figsize=SCALE)
plt.scatter(x, y, s=4, linewidth=0.1, c="k", marker="x")
plt.ylabel("clock cycles")
plt.xlabel("n")
plt.axvline(x=(3096*1024)/(8*3), c="r", ls="--", label="memory needed = L3 cache size")
plt.axvline(x=(512*1024)/(8*3), c="g", ls="--", label="memory needed = L2 cache size")
plt.axvline(x=(128*1024)/(8*3), ls="--", label="memory needed = L1 cache size")
plt.legend()
plt.grid()
plt.savefig("../report/plots/csort_cache_n.svg")
plt.show()

print("COUNTING NOCACHE")
plt.figure(figsize=SCALE)
ys = []
for filename in ["n", "2n", "1", "50000"]:
	x, y = zip(*read_data("../results/csort_nocache_{}.csv".format(filename)))
	ys.append(y)
	coef = np.corrcoef(x, y)[1][0]
	plt.scatter(x, y, s=10, linewidth=1, marker="x", label="k = {}     (Correlation coefficient {:.6f})".format(filename, coef))
print_10_points(list(zip(*([x]+ys))))

plt.ylabel("clock cycles")
plt.xlabel("n")
plt.legend()
plt.grid()
plt.savefig("../report/plots/csort_nocache.svg")
plt.show()

print("INSERTION CACHE")
plt.figure(figsize=SCALE)
ys = []
for filename in ["random", "ascending", "descending"]:
	x, y = zip(*read_data("../results/isort_cache_{}.csv".format(filename)))
	x = [n*n for n in x]
	ys.append(y)
	coef = np.corrcoef(x, y)[1][0]
	plt.scatter(x, y, s=10, linewidth=1, marker="x", label="{} order (Correlation coefficient {:.6f})".format(filename, coef))
print_10_points(list(zip(*([x]+ys))))

plt.ylabel("clock cycles")
plt.xlabel("n\u00B2")
plt.legend()
plt.grid()
plt.savefig("../report/plots/isort_cache.svg")
plt.show()

print("INSERTION NOCACHE")
plt.figure(figsize=SCALE)
x, y = zip(*read_data("../results/isort_nocache_ascending.csv"))
print_10_points(list(zip(x, y)))
coef = np.corrcoef(x, y)[1][0]
plt.scatter(x, y, s=10, linewidth=1, marker="x", label="ascending order (Correlation coefficient {:.6f})".format(coef))

plt.ylabel("clock cycles")
plt.xlabel("n")
plt.legend()
plt.grid()
plt.savefig("../report/plots/isort_nocache_ascending.svg")
plt.show()
