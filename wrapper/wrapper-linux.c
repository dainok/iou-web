#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <arpa/inet.h>
#include <sys/select.h>
#include <sys/socket.h>
#include <sys/wait.h>

//--[ Telnet Commands ]--------------------------------------------------------
#define IS       0 // Sub-process negotiation IS command
#define SEND     1 // Sub-process negotiation SEND command
#define SE     240 // End of subnegotiation parameters
#define NOP    241 // No operation
#define DATMK  242 // Data stream portion of a sync.
#define BREAK  243 // NVT Character BRK
#define IP     244 // Interrupt Process
#define AO     245 // Abort Output
#define AYT    246 // Are you there
#define EC     247 // Erase Character
#define EL     248 // Erase Line
#define GA     249 // The Go Ahead Signal
#define SB     250 // Sub-option to follow
#define WILL   251 // Will; request or confirm option begin
#define WONT   252 // Wont; deny option request
#define DO     253 // Do = Request or confirm remote option
#define DONT   254 // Don't = Demand or confirm option halt
#define IAC    255 // Interpret as Command
//--[ Telnet Options ]---------------------------------------------------------
#define BINARY   0 // Transmit Binary
#define ECHO     1 // Echo characters back to sender
#define RECON    2 // Reconnection
#define SGA      3 // Suppress Go-Ahead
#define TTYPE   24 // Terminal Type
#define NAWS    31 // Negotiate About Window Size
#define LINEMO  34 // Line Mode

int newsockfd;

void handle_signal(int signal) {

	// Find out which signal we're handling
	switch (signal) {
		case SIGHUP:
			// Closing socket
			printf("Caught SIGHUP, closing sockets\n");
			if (newsockfd > 0) {
				close(newsockfd);
				newsockfd = -1;
			}
			break;
		default:
			printf("ERR: caught wrong signal (%d).\n", signal);
			break;
	}
}

int main (int argc, char *argv[]) {
	char *xtitle = "Terminal Server", *bin = NULL, *bin_cmd = NULL;
	int c, port, delay = 0;
	long bin_cmd_len;

	while ((c = getopt(argc, argv, ":m:p:t:d:")) != -1) {
		switch(c) {
			case 'd':
				delay = atoi(optarg);
				break;
			case 'm':
				bin = optarg;
				break;
			case 'p':
				port = atoi(optarg);
				break;
			case 't':
				xtitle = optarg;
				break;
			case ':':
				printf("Usage: %s [-m /path/to/image] [-p port_number] [-t \"Window Title\"] [-d delay] -- [image options]\n", argv[0]);
				exit(1);
			default:
				exit(1);
		}
	}

	// Check if port is higher than 1024
	if (port <= 1024) {
		printf("ERR: port must be higher than 1024 (%d given).\n", port);
		exit(1);
	}
	// Check if port is used
	int sockfd;
	extern int newsockfd;
	socklen_t clilen;
	struct sockaddr_in6 serv_addr = {}, cli_addr = {};
	sockfd = socket(AF_INET6, SOCK_STREAM, 0);

	if (sockfd < 0) {
		printf("ID_%d: error opening socket.\n", port);
		exit(1);
	}

	serv_addr.sin6_family = AF_INET6;
	serv_addr.sin6_port = htons(port);
	serv_addr.sin6_addr = in6addr_any;

	int m = 1;
	if (setsockopt(sockfd, SOL_SOCKET, SO_REUSEADDR, &m, sizeof(m)) < 0) {
		printf("ID_%d: error setting socket options.\n", port);
		exit(1);
	}
	if (bind(sockfd, (struct sockaddr *) &serv_addr, sizeof(serv_addr)) < 0) {
		printf("ID_%d: error on binding, address alredy in use.\n", port);
		exit(1);
	}
	listen(sockfd,5);
	clilen = sizeof(cli_addr);

	// Check if bin exist
	if (access(bin, F_OK) == -1) {
		printf("ID_%d: file %s does not exist.\n", port, bin);
		exit(1);
	}

	// Allocate memory to store the command
	bin_cmd_len = sysconf(_SC_ARG_MAX);
	if (bin_cmd_len < 0) {
		printf("ERR: Can't determine maximum command length\n");
		exit(1);
	}

	bin_cmd = (char *) malloc(bin_cmd_len * sizeof(char));
	if (!bin_cmd) {
		printf("ERR: Can't allocate memory\n");
		exit(1);
	}
	memset(bin_cmd, 0, bin_cmd_len);

	// Setting the bin command
	int l = strlen(bin);
	if (l > bin_cmd_len) {
		printf("ERR: too many parameters (%s).\n", bin);
		exit(1);
	} else {
		strcpy(bin_cmd, bin);
		strcat(bin_cmd, " ");
		l = strlen(bin_cmd);
	}

	// Setting extra options (given after '--')
	int f, i;
	for (f = 0, i = 1; i <= argc && argv[i] != NULL; i++) {
		if (f == 0 && strncmp(argv[i], "--", strlen(argv[i])) == 0) {
			// Found --, skip to next argument
			f = 1;
			i++;
		}
		if (f == 1) {
			// Building bin_cmd
			if (l + strlen(argv[i]) > bin_cmd_len) {
				printf("ERR: too many parameters (%s).\n", argv[i]);
				exit(1);
			} else {
				strcat(bin_cmd, argv[i]);
				strcat(bin_cmd, " ");
				l = strlen(bin_cmd);
			}
		}
	}

	// https://gist.github.com/aspyct/3462238
	// Handling signals
	signal(SIGPIPE,SIG_IGN); // Ignoring SIGPIPE when a client terminates
	struct sigaction sa;
	// Setup the sighub handler
	sa.sa_handler = &handle_signal;
	// Restart the system call, if at all possible
	sa.sa_flags = SA_RESTART;
	// Signals blocked during the execution of the handler
	sigemptyset(&sa.sa_mask);
	sigaddset(&sa.sa_mask, SIGHUP);  // Signal 1
	sigfillset(&sa.sa_mask);

	// Intercept SIGHUP, SIGINT and SIGTERM
	if (sigaction(SIGHUP, &sa, NULL) == -1) {
		printf("ERR: cannot hangle SIGHUP\n");
	}

	// Parameters are OK, now forking the subprocess
	// Preparing PIPE for output redirection
	int infd[2];	// Array of integers [0] is for reading, [1] is for writing
	int outfd[2];	// Array of integers [0] is for reading, [1] is for writing
	pipe(infd);     // PIPE: the put (write) side
	pipe(outfd);	// PIPE: the get (read) side

	int pid = getpid();	// Parent Process PID
	int bin_pid = fork();
	int bin_rc;
	if (bin_pid == 0) {
		// Child stating subprocess
		if (delay > 0) {
			printf("ID_%d: process delayed (%d seconds)\n", port, delay);
			for (; delay > 0; delay--) {
				write(infd[1], ".", 1);
				sleep(1);
			}
		}
		printf("ID_%d: starting '%s ", port, bin_cmd);
		printf("' on port '%d' with title '%s'.\n", port, xtitle);
		close(STDIN_FILENO);	        // Closing child's stdin
		close(STDOUT_FILENO);	        // Closing child's stdout
		dup2(outfd[0], STDIN_FILENO);	// Linking stdin to PIPE
		dup2(infd[1], STDOUT_FILENO);	// Linking stdout to PIPE
		close(outfd[0]);
		close(outfd[1]);
		close(infd[0]);
		close(infd[1]);
		bin_rc = system(bin_cmd);
		// Subprocess terminated, killing the parent
		fprintf(stderr, "ID_%d: subprocess terminated, killing the parent (%d).\n", port, pid);
		kill(pid, SIGTERM);
		_exit(bin_rc);
	} else if (bin_pid > 0) {
		// Parent
		close(outfd[0]);                // Used by the child
		close(infd[1]);                 // Used by the childA

		fd_set ready_r; // TODO

		int status;
		char subprocess_input[1];
		char client_input[1];
		while (waitpid(bin_pid, &status, WNOHANG|WUNTRACED) == 0) {
			// If process is running, forking for telnet clients
			newsockfd = accept(sockfd, (struct sockaddr *)&cli_addr, &clilen);

			if (newsockfd < 0) {
				printf("ID_%d: error on accept.\n", port);
			} else {
				// Client connected
				char client_ip[INET6_ADDRSTRLEN];
				inet_ntop(AF_INET6, &(cli_addr.sin6_addr), client_ip, INET6_ADDRSTRLEN);
				printf("ID_%d: client connected: %s,\n", port, client_ip);

				// Client init
				char header[] = {
					IAC, WILL, ECHO,	// Sending IAC WILL ECHO
					IAC, WILL, SGA,		// Sending IAC WILL SGA
					IAC, WILL, BINARY,	// Sending IAC WILL BINARY
					IAC, DO, BINARY,	// Requesting BINARY mode (fix ^@ and show run)
					'\033', ']', '0', ';'	// Sending title header (http://tldp.org/HOWTO/Xterm-Title-3.html)
				};
				char trailer[] = {'\007'};	// Sending title trailer (http://tldp.org/HOWTO/Xterm-Title-3.html)
				send(newsockfd, &header, sizeof(header) / sizeof(header[0]), 0);
				send(newsockfd, xtitle, strlen(xtitle), 0);
				send(newsockfd, &trailer, sizeof(trailer) / sizeof(trailer[0]), 0);

				// While bin is running and client is alive
				int maxfd, rc_select;
				while (waitpid(bin_pid, &status, WNOHANG|WUNTRACED) == 0 && newsockfd >= 0) {
					// FD_ZERO() clears out the fd_set, so that it doesn't contain any file descriptors.
					FD_ZERO(&ready_r);
					// FD_SET() adds the file descriptor to the fd_set, so that select() will return data if available
					FD_SET(infd[0], &ready_r);	// subprocess output
					FD_SET(newsockfd, &ready_r);	// client input

					// Looping, waiting for subprocess output or input client
					maxfd = (infd[0] > newsockfd) ? infd[0] : newsockfd;
					rc_select = select(maxfd + 1, &ready_r, NULL, NULL, NULL);
					if (rc_select > 0 && FD_ISSET(infd[0], &ready_r)) {
						// Output from the subprocess
						if (read(infd[0], &subprocess_input[0], 1) <= 0) {
							printf("ID_%d: error while reading data from the subprocess\n", port);
						}
						// Writing to the client
						if (send(newsockfd, &subprocess_input[0], 1, 0) <= 0) {
							printf("ID_%d: error while sending data to the client, closing connection\n", port);
							close(newsockfd);
							newsockfd = -1;
							break;
						}
					} else if (rc_select > 0 && FD_ISSET(newsockfd, &ready_r)) {
						// Output from a client
						if (recv(newsockfd, client_input, 1, 0) < 0) {
							printf("ID_%d:  error while receiving data from the client, closing connection\n", port);
							close(newsockfd);
							newsockfd = -1;
							break;
						}
						if (client_input[0] == (char)IAC) {
							// Received telnet command, skip two more command
							recv(newsockfd, client_input, 1, 0);
							recv(newsockfd, client_input, 1, 0);
						} else {
							// Write to the subprocess
							write(outfd[1], &client_input[0], 1);
						}
					}
				}
			}
		}
		close(outfd[1]);
		close(infd[0]);
		close(sockfd);
	} else {
		// Fork Failed (pid = -1)
		printf("ERR: failed to fork subprocess.\n");
		exit(1);
	}
	exit(0);
}
