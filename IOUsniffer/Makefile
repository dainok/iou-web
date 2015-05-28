SRCS=iousniff.c config.c misc.c
OBJS=$(SRCS:.c=.o)
EXE=iousniff
CC=gcc
CFLAGS=-c -g -ggdb -Wall -O0
LDFLAGS=-lpcap

$(EXE): $(OBJS)
	$(CC) -o $(EXE) $(OBJS) $(LDFLAGS)

.c.o:
	$(CC) $(CFLAGS) -o $@ $<

clean:
	rm -f $(EXE)
	rm -f *.o
