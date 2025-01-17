import cv2
from pyzbar.pyzbar import decode
import numpy as np

img = cv2.imread('QR-with-fingers.png')

# removing fingerprint 
def retain_from_threshold(img, thresh=0):
    img_gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    output = img_gray.copy()

    # _, binary_mask = cv2.threshold(img_gray, thresh, 255, cv2.THRESH_BINARY)
    # inverse: img=0 will be 255, img!0 will be 0 
    _, binary_mask = cv2.threshold(img_gray, thresh, 255, cv2.THRESH_BINARY_INV)
    output[binary_mask == 0] = 255

    filename = 'mask_black.png'
    print(f"Exported to {filename}")
    cv2.imwrite(filename, output)
    return output

def decode_qr(img):
    decoded_objects = decode(img)
    if decoded_objects:
        for obj in decoded_objects:
            print(f"Decoded Data: {obj.data.decode('utf-8')}")
    else:
        print("No QR code detected or it is too damaged to read.")

qr_img = retain_from_threshold(img, 0)
decode_qr(qr_img)