IOUsniffer
Version 0.2
Author: Martin Cechvala <martin@cechvala.eu>
Link: http://cechvala.blogspot.com

This tool provides a way to sniff on IOU Netio sockets.

IOU instance is a single software emulated router/switch, it's IOS compiled for
Unix. IOU instance communicates with other instances (and stuff like
IOUlive/iou2net.pl) using sockets in /tmp/netioXXX directory (XXX is User ID of
user running IOU). These sockets are UNIX sockets, which make it impossible to
sniff on them directly.

Little analysis using strace showed that IOUs are communicating in a way that
allows us to do a 'man-in-the-middle' on those sockets and sniff traffic.

Every IOU, when starts, create a socket with IOU ID (instance ID) in
/tmp/netioXXX directory. IOU reads this socket using non-blocking calls.
On the other way, sending is done differently. Mappings between IOU instances
are defined by NETMAP file in a well-known format. These mappings tell IOU to
which socket file it should write when it want to send data to other IOU. This
data sending is done directly using sendto() to appropriate socket file.

Our tool does the following:
 1. looks for socket files in /tmp/netio dir (changable at startup)
 2. renames/moves every socket to temporary name (e.g. 100 -> 100_real)
 3. creates new socket with original name (e.g. 100)
 4. reads from 100 and writes to 100_real (and to pcap file)

Because of how UNIX works, it's possible to move file without disrupting data
flow.

== ENCAPSULATION ==
PCAP tools need to know encapsulation (layer 2 protocol, DLT, LLT, ...) when
displaying dumps. Because there is no way how to distinguish for example
Ethernet from HLDC just by looking at raw data (other than higher layer logic
and megabytes of code), this DLT (Datalink Type) is stored inside PCAP file
in PCAP header.

This tool faces the same problem. It can't distinguish encapsulation types
from each other, therefore it needs this to be set by a user.

For this purpose we used the same NETMAP file IOU uses. It is possible to add
DLT number at the end of each line. DLT number are located at
http://www.tcpdump.org/linktypes.html. By default, if no DLT is specified,
Ethernet (DLT 1) is assumed. 

Beware that setting wrong DLT type will cause that for example Wireshark
won't be able to parse packets, altough you can manually edit PCAP file,
you can only do this for an entire file.

Another issue is, that if you change DLT (using encapsulation command on
interface) to something other than DLT defined in NETMAP, it will cause
the same problem.

Possible solution to this exists: PCAP-NG (using NTAR library), which can
specify multiple interfaces (and DLT with them) and then assign each packet
to one of those interfaces. Newest Wireshark really can read files with
multiple Datalink Types. This would also require to dynamically tell IOUsniffer
to change DLT on particular link, but I can't think of user-friendly style
of doing this (maybe changing NETMAP file and reloading it?).

Example NETMAP:
100:0/0@test1 101:0/0@test1 1
100:1/0@test1 101:1/0@test1 9
100:1/1@test1 101:1/1@test1 104
100:1/2@test1 101:1/2@test1 107
100:1/3@test1 102:1/3@test1

"test1" is the hostname of local machine, but it will work even without "@hostname"
for example:
100:0/0 101:0/0 1
100:1/0 101:1/0 9
100:1/1 101:1/1 104
100:1/2 101:1/2 107
100:1/3  102:1/3

== FEATURES ==
- automatically detecting launch of new IOU, adding it's socket to handle list
- automatically detecting termination of IOU, removing it's socket from handle
	list
- optional flush at write on PCAPs
- handling ctrl+c and other signals and graceful shutdown
- correct cleanup, even doing IOU's work
- when one kills wrapper-linux, it sends SIGKILL to IOU process. Therefore
	IOU doesn't delete any of its sockets/lockfiles. This is solved by trying
	to lock IOU's lockfile on sendto fail. (TODO: do this on poll?)

== CAVEATS ==
- When changing NETMAP while IOUsniffer is running, when you define new
	connection between already running IOU and other IOU, IOUsniffer won't
	notice this new link (that means it won't start sniffing on it) until
	you restart process of that running IOU.

== TODO ==
- make it work when user specifies other than point-to-point connection in NETMAP (3+ instances on one line)
