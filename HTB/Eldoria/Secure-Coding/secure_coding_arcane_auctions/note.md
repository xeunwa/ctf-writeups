**Note:** One of the ports is a SMB share that you must mount on your host to make direct changes, and one port is checker where you will find the flag

**Mount the SMB Share:**

- **macOS:**

  ```bash
  mount_smbfs //guest@[IP]:[PORT]/app /Mount_Point
  ```
- **Linux:**


  ```bash
  mount -t cifs //<IP>/app ~/mnt/ -o username=guest,port=<PORT>
  ```


