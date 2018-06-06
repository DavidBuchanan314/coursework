#include <stdio.h>
#include <stdint.h>
#include <stdlib.h>

#define TESTING
#define ELEM_TYPE uint64_t
#include "../kmod/algorithms.h"

// For all tests, k will be 10
#define K 10

#define TEST(array, array_out) \
	puts("\nTesting dataset '" #array "':"); \
	print_array(#array, array, sizeof(array)/sizeof(*array)); \
	csort(array, array_out, counts, sizeof(array)/sizeof(*array), K); \
	print_array(#array_out, array_out, sizeof(array)/sizeof(*array));

ELEM_TYPE test1[] = {5, 4, 3, 2, 1}; // reverse sorted
ELEM_TYPE test2[] = {1, 2, 3, 4, 5}; // sorted
ELEM_TYPE test3[] = {7, 1, 0, 5, 9, 2, 7, 1}; // random order with duplicates

int main(int argc, char * argv[])
{
	ELEM_TYPE * test1_sorted = malloc(sizeof(test1));
	ELEM_TYPE * test2_sorted = malloc(sizeof(test2));
	ELEM_TYPE * test3_sorted = malloc(sizeof(test3));
	
	size_t * counts = malloc(K * sizeof(*counts)); // reused for each test
	
	TEST(test1, test1_sorted);
	TEST(test2, test2_sorted);
	TEST(test3, test3_sorted);
	
	free(test1_sorted);
	free(test2_sorted);
	free(test3_sorted);
	free(counts);
}
