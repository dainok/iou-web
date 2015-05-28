#ifndef _CONFIG_H_
#define _CONFIG_H_

struct config_s config;

struct config_s {
	char *netio_dir;
	char *netmap_file;
	char *sniff_dir;
	int debug_level;
	int flush_at_write;
};

int parse_arguments(int argc, char * argv[], char * envp[]);


#endif /* _CONFIG_H_*/
