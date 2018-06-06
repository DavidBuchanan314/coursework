#ifndef __HAVE_ARCH_MEMSET
#include <string.h>
#endif

/* used for debugging */
#ifdef TESTING
void print_array(const char * name, ELEM_TYPE * array, size_t length)
{
	printf("%s = {", name);
	for (int i = 0; i < length; i++) {
		printf("%u, ", array[i]);
	}
	printf("\b\b}\n");
}
#endif

void isort(ELEM_TYPE * a, size_t n)
{
	typeof(*a) tmp;
	typeof(n) i, j;
	
	for (i = 1; i < n; i++) {
		tmp = a[i];
		for (j = i; j-- > 0 && a[j] > tmp;) {
			a[j+1] = a[j];
		}
		a[j+1] = tmp;
#ifdef TESTING
		printf("i = %u, ", i);
		print_array("a", a, n);
#endif
	}
}

static inline void csort(
	ELEM_TYPE * a, /* input array */
	ELEM_TYPE * b, /* output array */
	size_t * c, /* count array */
	size_t n, /* number of elements in a (and therfore b) */
	ELEM_TYPE k) /* number of elements in c (upper limit of values in a) */
{
	typeof(n) j;
	typeof(k) i;
	
	/* idiomatic implementation of first loop from pseudocode */
	memset(c, 0, k * sizeof(*c));
	
	for (j = 0; j < n; j++) c[a[j]]++;
	
#ifdef TESTING
	print_array("c after 2nd loop", c, k);
#endif
	
	for (i = 1; i < k; i++) c[i] += c[i-1];
	
#ifdef TESTING
	print_array("c after 3rd loop", c, k);
#endif
	
	for (j = n; j-- > 0; ) {
		b[c[a[j]] - 1] = a[j];
		c[a[j]]--;
	}
}
