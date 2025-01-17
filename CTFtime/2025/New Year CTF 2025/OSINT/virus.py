from pwn import *

p = remote('ctf.mf.grsu.by', 9033)

# Avast Antivirus has detected an unknown computer virus with a rare name "Apanas" on Santa's computer, which has caused Windows to stop loading. And I have some urgent questions that need answers as soon as possible. Can you give the correct answers to help protect Santa Claus from this threat?
# Keep the writing of the answers as on Wikipedia (https://en.wikipedia.org/). Try to be brief. If there are several words in the answer - separate them with a space.

#     What is the name of this file virus in the NOD32 antivirus database and other antiviruses
#     How is the name of the virus translated from Belorussian into English
#     In what year was this virus written
#     In what language was the virus written
#     Hackers of which country wrote this virus
#     What does the author of the virus call himself (two words)
#     What kind of beer is the best, according to the author of the virus
#     The name of the file on the infected computer that contains the body of the virus
#     The last name of the person to whom the author of the virus sends greetings
#     With what score did the famous match Sweden - Belarus end (Olympic Games, 2002, Salt Lake City), to whose goalkeeper the author of the virus sends "best wishes" (answer in the form - number:number). After this match, the Republic of Belarus became known all over the world :)


answers = [
    "Neshta",
    "Something",
    "2005",
    "Delphi",
    "Belarus",
    "Dziadulja Apanas",
    "Alivaria",
    "svchost.com",
    "Lukashenko",
    "3:4"
]

for ans in answers:
    print(p.recvuntil(b">").decode())
    print("Answer:", ans)
    p.sendline(bytes(ans.encode()))
    print(p.recvline().decode())

p.interactive()

# `grodno{db6d702312fe3ba5890adfeced}`