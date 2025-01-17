import barcode
from barcode.writer import ImageWriter


# saving barcode
def generate_barcode(id):
    barcode_format = "code128" 
    barcode_obj = barcode.get(barcode_format, id, writer=ImageWriter())
    filename = barcode_obj.save(f"barcode_{id}")
    return filename

while True:
    id = input("Enter barcode value to generate: ")
    generate_barcode(id)

print(f"Barcode saved as {filename}")


