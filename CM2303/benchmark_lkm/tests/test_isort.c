#include <stdio.h>
#include <stdint.h>
#include <stdlib.h>

#define TESTING
#define ELEM_TYPE uint64_t
#include "../kmod/algorithms.h"

#define TEST(array) \
	puts("\nTesting dataset '" #array "':"); \
	print_array(#array, array, sizeof(array)/sizeof(*array)); \
	isort(array, sizeof(array)/sizeof(*array)); \
	print_array("final " #array, array, sizeof(array)/sizeof(*array));

ELEM_TYPE test1[] = {5, 4, 3, 2, 1}; // reverse sorted
ELEM_TYPE test2[] = {1, 2, 3, 4, 5}; // sorted
ELEM_TYPE test3[] = {7, 1, 0, 5, 9, 2, 7, 1}; // random order with duplicates

int main(int argc, char * argv[])
{
	TEST(test1);
	TEST(test2);
	TEST(test3);
}
