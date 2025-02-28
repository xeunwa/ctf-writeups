import string

def decode_whitespace(file_path):
    try:
        with open(file_path, 'r') as file:
            content = file.read()
            ascii_codes = [char for char in content if char.lower() not in string.ascii_lowercase+",.-?’‘"]
            codes = ' '.join(ascii_codes)
            with open('output.txt','w') as file:
                file.write(codes)
            
            
    except FileNotFoundError:
        print("File not found. Please check the file path.")
    except Exception as e:
        print(f"An error occurred: {e}")

# Replace 'yourfile.txt' with the actual file path
decode_whitespace('poemm.txt')