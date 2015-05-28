#include <stdio.h>
#include <stdlib.h>
#include <stdarg.h>

#include "misc.h"
#include "config.h"

void debug(int level, const char *format, ...)
{
    va_list args;

	if (level > config.debug_level)
		return;

    va_start(args, format);
    vprintf(format, args);
    va_end(args);
}

void dump_packet(char *buf, int len)
{
	int i, pkt_n = 0;

	while (pkt_n < len) {
		printf("%04x\t", pkt_n);
		for (i = 0; i < 16 && pkt_n+i < len; i++) {
			printf("%02x ", buf[pkt_n+i] & 0xff);
			if (i == 7)
				putchar(' ');
		}

		for (i = 3*(16-i)+2+(i<7); i > 0; i--)
			putchar(' ');

		for (i = 0; i < 16 && pkt_n+i < len; i++) {
			if (buf[pkt_n+i] >= 0x20 && buf[pkt_n+i] <= 0x7E)
				putchar(buf[pkt_n+i]);
			else
				putchar('.');
			if (i == 7)
				putchar(' ');
		}
		putchar('\n');
		pkt_n += 16;
	}
	putchar('\n');
}
