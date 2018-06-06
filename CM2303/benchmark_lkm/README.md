This coursework was about testing the time complexity of counting sort and insertion
sort. We were given a free choice of what language etc. to use.

The PhD student marking the work didn't seem to fully appreciate the awesomeness
of my cycle-counting Linux kernel module, nor my results which clearly showed all
levels of my system's cache hierachy (Or the subsequent results with hardware
caching disabled), and as such this work "only" got 92%.

SYSTEM REQUIREMENTS:
- A modern 64-bit Linux system, with an Intel CPU supporting the RDTSC and RDTSCP instructions
- Python 3

kmod/ contains the source of the kernel module, along with a makefile.
It can be compiled by running `make`.

Once compiled, it can be installed by running `insmod benchmark.ko` (as root)

Then, the device node must be created, by running `mknod /dev/benchmark c 100 0` (as root)


tests/ contains the test source code, along with a makefile.


client/ contains the Python 3 scripts for collecting and graphing the results.


retults/ contains the raw results in .csv format.
