
from bcrypt import hashpw, gensalt, checkpw

def password_hasher(raw_password):
    return hashpw(raw_password.encode('utf-8'), gensalt(rounds=10)).decode('utf-8')

def password_checker(hashed_password, raw_password):
    return checkpw(raw_password.encode('utf-8'), hashed_password.encode('utf-8'))
