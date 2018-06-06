import struct
import fcntl
import math

# this device must be created first via `mknod /dev/benchmark c 100 0`
BENCH_DEVICE = "/dev/benchmark"

IOCTL_BENCH_ISORT = 0
IOCTL_BENCH_CSORT = 1

ISORT_MODE_RANDOM = 0
ISORT_MODE_ASCENDING = 1
ISORT_MODE_DESCENDING = 2

STEP = 256

def run_bench(device, ioctl_no, n, k=0, cache_enabled=1):
	args = struct.pack("<QQQ", n, k, cache_enabled)
	result = fcntl.ioctl(device, ioctl_no, args, False)
	return struct.unpack_from("<Q", result)[0]

with open(BENCH_DEVICE) as b:
	# COUNTING SORT, CACHE ENABLED, K=N
	with open("../results/csort_cache_n.csv", "w") as outfile:
		for i in range(1, 0x40000, STEP):
			time = run_bench(b, IOCTL_BENCH_CSORT, i, i, 1)
			outfile.write("{},\t{}\n".format(i, time))
			print(i)
	
	# COUNTING SORT, NO CACHE, K=N
	with open("../results/csort_nocache_n.csv", "w") as outfile:
		for i in range(1, 0x8000, STEP):
			time = run_bench(b, IOCTL_BENCH_CSORT, i, i, 0)
			outfile.write("{},\t{}\n".format(i, time))
			print(i)
	
	# COUNTING SORT, NO CACHE, K=2N
	with open("../results/csort_nocache_2n.csv", "w") as outfile:
		for i in range(1, 0x8000, STEP):
			time = run_bench(b, IOCTL_BENCH_CSORT, i, 2*i, 0)
			outfile.write("{},\t{}\n".format(i, time))
			print(i)
	
	# COUNTING SORT, NO CACHE, K=1
	with open("../results/csort_nocache_1.csv", "w") as outfile:
		for i in range(1, 0x8000, STEP):
			time = run_bench(b, IOCTL_BENCH_CSORT, i, 1, 0)
			outfile.write("{},\t{}\n".format(i, time))
			print(i)
	
	# COUNTING SORT, NO CACHE, K=50000
	with open("../results/csort_nocache_50000.csv", "w") as outfile:
		for i in range(1, 0x8000, STEP):
			time = run_bench(b, IOCTL_BENCH_CSORT, i, 50000, 0)
			outfile.write("{},\t{}\n".format(i, time))
			print(i)
	
	# COUNTING SORT, CACHE ENABLED, RANDOM ORDER
	with open("../results/isort_cache_random.csv", "w") as outfile:
		for i in range(1, 0x20000000, STEP*8192*2):
			n = int(math.sqrt(i))
			time = run_bench(b, IOCTL_BENCH_ISORT, n, ISORT_MODE_RANDOM)
			outfile.write("{},\t{}\n".format(n, time))
			print(n)
	
	# COUNTING SORT, CACHE ENABLED, ASCENDING ORDER
	with open("../results/isort_cache_ascending.csv", "w") as outfile:
		for i in range(1, 0x20000000, STEP*8192*2):
			n = int(math.sqrt(i))
			time = run_bench(b, IOCTL_BENCH_ISORT, n, ISORT_MODE_ASCENDING)
			outfile.write("{},\t{}\n".format(n, time))
			print(n)
	
	# COUNTING SORT, CACHE ENABLED, DESCENDING ORDER
	with open("../results/isort_cache_descending.csv", "w") as outfile:
		for i in range(1, 0x20000000, STEP*8192*2):
			n = int(math.sqrt(i))
			time = run_bench(b, IOCTL_BENCH_ISORT, n, ISORT_MODE_DESCENDING)
			outfile.write("{},\t{}\n".format(n, time))
			print(n)
	
	# COUNTING SORT, CACHE DISABLED, ASCENDING ORDER
	with open("../results/isort_nocache_ascending.csv", "w") as outfile:
		for i in range(1, 0x8000, STEP):
			time = run_bench(b, IOCTL_BENCH_ISORT, i, ISORT_MODE_ASCENDING, 0)
			outfile.write("{},\t{}\n".format(i, time))
			print(i)
