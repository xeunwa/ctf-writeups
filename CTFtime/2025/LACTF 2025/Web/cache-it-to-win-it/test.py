def normalize_uuid(uuid: str):
    uuid_l = list(uuid)
    i = 0
    for i in range(len(uuid)):
        uuid_l[i] = uuid_l[i].upper()
        if uuid_l[i] == "-":
            uuid_l.pop(i)
            uuid_l.append("x")

    return "".join(uuid_l)

uuid="a1afd0cd-edde-4854-8566-3090feb9c8e3"

print(normalize_uuid(uuid))

# A1AFD0CDeDDE485485663090FEB9C8E3xxxx
