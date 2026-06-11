import sys
import os
import qrcode
from io import BytesIO
import tempfile
from reportlab.pdfgen import canvas
from reportlab.lib import colors
from reportlab.lib.utils import ImageReader
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont

def _split_name_into_apellidos_nombres(full_name: str):
    parts = full_name.strip().upper().split()
    if len(parts) <= 1:
        return full_name.upper(), ""
    mid = (len(parts) + 1) // 2
    return " ".join(parts[:mid]), " ".join(parts[mid:])

def create_stamp_pdf(box, signer_name, output_path):
    x1, y1, x2, y2 = box
    stamp_w = float(x2 - x1)
    stamp_h = float(y2 - y1)

    apellidos, nombres = _split_name_into_apellidos_nombres(signer_name)
    
    FONT_REG  = "Courier"
    FONT_BOLD = "Courier-Bold"

    from datetime import datetime as _dt
    _ts = _dt.now().strftime('%Y-%m-%dT%H:%M:%S')
    qr_data = (
        f"https://www.firmadigital.gob.ec/"
        f"?firmado_por={signer_name}"
        f"&fecha_firma={_ts}"
        f"&ubicacion=Latacunga,Cotopaxi,Ecuador"
        f"&motivo=Firma_Electronica_Institucional"
    )
    qr = qrcode.QRCode(version=None, box_size=10, border=1, error_correction=qrcode.constants.ERROR_CORRECT_H)
    qr.add_data(qr_data)
    qr.make(fit=True)
    qr_img = qr.make_image(fill_color="black", back_color="white")
    qr_buf = BytesIO()
    qr_img.save(qr_buf, format='PNG')

    c = canvas.Canvas(output_path, pagesize=(stamp_w, stamp_h))

    PAD = 3
    qr_size = stamp_h - PAD * 2
    qr_x = PAD
    qr_y = PAD
    
    qr_buf.seek(0)
    c.drawImage(ImageReader(qr_buf), qr_x, qr_y, width=qr_size, height=qr_size, preserveAspectRatio=True)

    text_x = qr_size + PAD * 2

    font_label = 7.5
    font_name  = 9.5
    lh_label   = font_label + 2.5
    lh_name    = font_name  + 2.5

    n_name_lines = 2 if nombres else 1
    block_h = lh_label + (lh_name * n_name_lines) + lh_label

    qr_center_y = qr_y + qr_size / 2
    y_cur = qr_center_y + block_h / 2 - font_label

    c.setFillColor(colors.black)
    c.setFont(FONT_REG, font_label)
    c.drawString(text_x, y_cur, "Firmado electrónicamente por:")

    y_cur -= lh_name
    c.setFont(FONT_BOLD, font_name)
    c.drawString(text_x, y_cur, apellidos)

    if nombres:
        y_cur -= lh_name
        c.drawString(text_x, y_cur, nombres)

    y_cur -= lh_label + 1
    c.setFont(FONT_REG, font_label)
    c.drawString(text_x, y_cur, "Validar únicamente con FirmaEC")

    c.save()

if __name__ == "__main__":
    create_stamp_pdf((0, 0, 180, 80), "JUAN PEREZ", "stamp_test.pdf")
