#define LINUX

#include <linux/module.h>
#include <linux/kernel.h>
#include <linux/init.h>
#include <linux/vmalloc.h>
#include <linux/random.h>
#include <linux/device.h>
#include <linux/uaccess.h>

#define ELEM_TYPE u64
#include "algorithms.h"

#define DRIVER_AUTHOR "David Buchanan"
#define DRIVER_DESC   "Pointlessly precise benchmarking"
#define DEVICE_NAME   "benchmark"
#define DEVICE_MAJOR  100 // this should not really be hardcoded

#define CR0_CD (1<<30)

#define N_MAX 0x80000 // arbitrary limit on maximum N value
#define IOCTL_BENCH_ISORT 0
#define IOCTL_BENCH_CSORT 1
#define ISORT_MODE_RANDOM 0
#define ISORT_MODE_ASCENDING 1
#define ISORT_MODE_DESCENDING 2

ELEM_TYPE * randcache;

struct ioctl_arg {
	u64 n; // n is reused to store the result
	u64 k; // k is reused by the insertion sort handler to chose the type of data
	u64 cache_enabled;
};

static inline void set_cache(int enabled)
{
	u64 cr0tmp;
	
	/* read the cr0 register into a variable */
	asm volatile (
		"mov %%cr0, %0;"
		: "=r" (cr0tmp)
	);
	
	if (enabled) {
		cr0tmp &= ~CR0_CD; // clear the Cache Disable bit
	} else {
		cr0tmp |= CR0_CD; // set the Cache Disable bit
	}
	
	/* The cache must be invalidated after it is disabled
	in order to maintain cache coherency */
	asm volatile (
		"mov %0, %%cr0;"
		"wbinvd;"
		:
		: "r" (cr0tmp)
	);
}

static inline void flush_cache(void)
{
	asm volatile ("wbinvd;");
}

volatile u64 bench_isort(size_t n, int mode, int cache_enabled)
{
	ELEM_TYPE * a;
	size_t i;
	unsigned long flags;
	u64 t0, t1;
	u32 t0lo, t0hi, t1lo, t1hi;
	
	printk("bench_isort(%lu, %d)\n", n, cache_enabled);
	
	a = vmalloc(n * sizeof(*a));
	
	switch (mode) {
		case ISORT_MODE_RANDOM: // average case
			for (i = 0; i < n; i++) {
				a[i] = randcache[i];
			}
			break;
		case ISORT_MODE_ASCENDING: // best case
			for (i = 0; i < n; i++) {
				a[i] = i;
			}
			break;
		case ISORT_MODE_DESCENDING: // worst case
			for (i = 0; i < n; i++) {
				a[i] = n-i-1;
			}
			break;
		default:
			return -1;
	}
	
	preempt_disable();
	raw_local_irq_save(flags);
	
	flush_cache();
	
	if (!cache_enabled) set_cache(0); // disable CPU cache
	
	asm	volatile (
		"cpuid;"
		"rdtsc;"
		"mov %%edx, %0;"
		"mov %%eax, %1;"
		: "=r" (t0hi), "=r" (t0lo)
		:
		: "rax", "rbx", "rcx", "rdx"
	);
	
	isort(a, n); // the compiler should inline this
	
	asm	volatile (
		"rdtscp;"
		"mov %%edx, %0;"
		"mov %%eax, %1;"
		"cpuid;"
		: "=r" (t1hi), "=r" (t1lo)
		:
		: "rax", "rbx", "rcx", "rdx"
	);
	
	if (!cache_enabled) set_cache(1); // reenable CPU cache
	
	raw_local_irq_restore(flags);
	preempt_enable();
	
	vfree(a);
	
	t0 = ((u64) t0hi << 32) | t0lo;
	t1 = ((u64) t1hi << 32) | t1lo;
	
	return t1-t0;
}

volatile u64 bench_csort(size_t n, ELEM_TYPE k, int cache_enabled)
{
	ELEM_TYPE * a, * b;
	size_t * c, i;
	unsigned long flags;
	u64 t0, t1;
	u32 t0lo, t0hi, t1lo, t1hi;
	
	printk("bench_csort(%lu, %llu, %d)\n", n, k, cache_enabled);
	
	a = vmalloc(n * sizeof(*a));
	b = vmalloc(n * sizeof(*b));
	c = vmalloc(k * sizeof(*c));
	
	for (i = 0; i < n; i++) {
		a[i] = randcache[i] % k;
	}
	
	preempt_disable();
	raw_local_irq_save(flags);
	
	flush_cache();
	
	if (!cache_enabled) set_cache(0); // disable CPU cache
	
	asm	volatile (
		"cpuid;"
		"rdtsc;"
		"mov %%edx, %0;"
		"mov %%eax, %1;"
		: "=r" (t0hi), "=r" (t0lo)
		:
		: "rax", "rbx", "rcx", "rdx"
	);
	
	csort(a, b, c, n, k); // the compiler should inline this
	
	asm	volatile (
		"rdtscp;"
		"mov %%edx, %0;"
		"mov %%eax, %1;"
		"cpuid;"
		: "=r" (t1hi), "=r" (t1lo)
		:
		: "rax", "rbx", "rcx", "rdx"
	);
	
	if (!cache_enabled) set_cache(1); // reenable CPU cache
	
	raw_local_irq_restore(flags);
	preempt_enable();
	
	vfree(a);
	vfree(b);
	vfree(c);
	
	t0 = ((u64) t0hi << 32) | t0lo;
	t1 = ((u64) t1hi << 32) | t1lo;
	
	return t1-t0;
}

/* stub */
static int device_open(struct inode *inode, struct file *file)
{
	return 0;
}

/* stub */
static int device_release(struct inode *inode, struct file *file)
{
	return 0;
}

/* stub */
static ssize_t device_read(
	struct file *f,
	char __user *buf,
	size_t len,
	loff_t *off)
{
	return 0;
}

/* stub */
static ssize_t device_write(
	struct file *f,
	const char __user *buf,
	size_t len,
	loff_t *off)
{
	return len;
}

long device_ioctl(
	struct file *file,
	unsigned int ioctl_num,/* The number of the ioctl */
	unsigned long ioctl_param) /* The parameter to it */
{
	struct ioctl_arg args;
	
	if (copy_from_user(&args, (void *) ioctl_param, sizeof(args)) != 0) {
		return -EACCES;
	}
	
	switch (ioctl_num) {
		case IOCTL_BENCH_ISORT:
			args.n = bench_isort(args.n, args.k, args.cache_enabled);
			
			if (copy_to_user((void *) ioctl_param, &args, sizeof(args)) != 0) {
				return -EACCES;
			}
			
			return 0;
		
		case IOCTL_BENCH_CSORT:
			if (args.n > N_MAX) {
				return -1;
			}
			
			args.n = bench_csort(args.n, args.k, args.cache_enabled);
			
			if (copy_to_user((void *) ioctl_param, &args, sizeof(args)) != 0) {
				return -EACCES;
			}
			
			return 0;
	}
	return -1;
}

const struct file_operations fops = {
	.owner = THIS_MODULE,
	.unlocked_ioctl = device_ioctl,
	.open = device_open,
	.release = device_release,
	.read = device_read,
	.write = device_write
};

int init_module(void)
{
	int result;
	result = register_chrdev(DEVICE_MAJOR, DEVICE_NAME, &fops);
	
	if (result < 0) {
		return result;
	}
	
	/* initialse a large cache of random data to speed things up */
	/* Generating random data each time would be slow  */
	randcache = vmalloc(sizeof(randcache) * N_MAX);
	get_random_bytes(randcache, sizeof(randcache) * N_MAX);
	
	printk("Benchmarker loaded.\n");
	
	return 0;
}

void cleanup_module(void)
{
	printk("Benchmarker unloading\n");
	return unregister_chrdev(DEVICE_MAJOR, DEVICE_NAME);
}

MODULE_LICENSE("Dual MIT/GPL");

MODULE_AUTHOR(DRIVER_AUTHOR);
MODULE_DESCRIPTION(DRIVER_DESC);

