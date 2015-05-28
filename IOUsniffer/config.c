#include <stdlib.h>
#include <stdio.h>
#include <string.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <unistd.h>
#include "config.h"

#define VERSION "0.2"

#define NETIO_DIR "/tmp/netio0"
#define NETMAP_FILE "NETMAP"

void display_help(char *name)
{
	printf("Usage: %s [-h] [-i <netio_dir>] [-n <NETMAP>] ", name);
	printf("[-s <sniff_dir>] [-f] [-d [-d ...]]\n");
	puts("");
	puts("Arguments:");
	printf("\t-h: displays this help\n");
	printf("\t-i <netio_dir>: Look for netio sockets in <netio_dir>\n");
	printf("\t\t\t[default=/tmp/netio0]\n");
	printf("\t-n <NETMAP>: Location of <NETMAP> file [default=./NETMAP]\n");
	printf("\t-s <sniff_dir>: Directory to place sniffs to ");
	printf("[default=/tmp/iousn*]\n");
	printf("\t-f: Flush at every write to pcap\n");
	printf("\t-d: Increase debug level (may be specified more than once)\n");
	puts("");
	printf("Version: %s\n", VERSION);
	printf("Author: Martin Cechvala\n");
};

int parse_arguments(int argc, char * argv[], char * envp[])
{
	struct stat st;
	int ret;
	char c;

	config.netio_dir = NULL;
	config.netmap_file = NULL;
	config.sniff_dir = NULL;
	config.flush_at_write = 0;
	config.debug_level = 0;

	while ((c = getopt(argc, argv, "hi:n:s:df")) != -1) {
		switch (c) {
			case 'h':
				display_help(argv[0]);
				exit(0);
			case 'i':
				config.netio_dir = (char *)malloc(strlen(optarg) + 1);
				strcpy(config.netio_dir, optarg);
				break;
			case 'n':
				config.netmap_file = (char *)malloc(strlen(optarg) + 1);
				strcpy(config.netmap_file, optarg);
				break;
			case 's':
				config.sniff_dir = (char *)malloc(strlen(optarg) + 1);
				strcpy(config.sniff_dir, optarg);
				break;
			case 'f':
				config.flush_at_write = 1;
				break;
			case 'd':
				config.debug_level++;
				break;
		}
	}

	if (!config.netio_dir) {
		config.netio_dir = (char *)malloc(strlen(NETIO_DIR)+1);
		strcpy(config.netio_dir, NETIO_DIR);
	}
	ret = stat(config.netio_dir, &st);
	if (ret) {
		perror("Netio socket dir access error");
		return -1;
	}
	if (!S_ISDIR(st.st_mode)) {
		fprintf(stderr, "Netio socket path isn't a directory");
		return -1;
	}

	if (!config.netmap_file) {
		config.netmap_file = (char *)malloc(strlen(NETMAP_FILE)+1);
		strcpy(config.netmap_file, NETMAP_FILE);
	}
	ret = stat(config.netmap_file, &st);
	if (ret) {
		perror("NETMAP file access error");
		return -1;
	}
	if (!S_ISREG(st.st_mode)) {
		fprintf(stderr, "NETMAP file isn't a regular file\n");
		return -1;
	}

	if (!config.sniff_dir)
		config.sniff_dir = tempnam("/tmp", "iousniff");
	ret = mkdir(config.sniff_dir, 0644);
	if (ret) {
		ret = stat(config.sniff_dir, &st);
		if (!S_ISDIR(st.st_mode)) {
				perror("Sniff path isn't a directory");
				return -1;
		}
	}

	return 0;
}
