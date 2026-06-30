import sys
import os
import qrcode
from io import BytesIO
from pyhanko.sign import signers, fields
from pyhanko.sign.timestamps import HTTPTimeStamper
from pyhanko.pdf_utils.reader import PdfFileReader
from pyhanko.pdf_utils.incremental_writer import IncrementalPdfFileWriter
from pyhanko.sign.fields import append_signature_field, SigFieldSpec
from pyhanko.pdf_utils import generic
from pyhanko.sign.signers.pdf_byterange import SignatureObject, BuildProps

# ---------- MONKEY-PATCH PARA PARIDAD CON FIRMAEC ----------
# FirmaEC tiene dos particularidades:
# 1. Incluye /ContactInfo, /Location y /Reason como textos vacíos ('') si no se proveen.
# 2. Guarda todas las llaves del diccionario de firma en orden ALFABÉTICO.

_original_SignatureObject_init = SignatureObject.__init__

def _patched_SignatureObject_init(self, *args, **kwargs):
    # Llamamos al constructor original
    _original_SignatureObject_init(self, *args, **kwargs)
    
    # Aseguramos presencia de campos obligatorios para FirmaEC
    for key in ['/ContactInfo', '/Location', '/Reason']:
        pdf_key = generic.pdf_name(key)
        if pdf_key not in self:
            # Inyectamos objeto de texto vacío
            self[pdf_key] = generic.TextStringObject('')
        else:
            # Si es None o un espacio (fallback anterior), lo limpiamos a vacío total
            val = self[pdf_key]
            if not val or str(val).strip() == "":
                self[pdf_key] = generic.TextStringObject('')

    # RE-ORDENAMIENTO ALFABÉTICO (Crucial para paridad binaria/visual en inspector)
    # En Python 3.7+, dict preserva orden de inserción. Re-insertamos todo alfabéticamente.
    # PyHanko DictionaryObject usa `generic.pdf_name` para sus llaves.
    items = sorted(self.items(), key=lambda x: str(x[0]).strip('/'))
    self.clear()
    for k, v in items:
        # Aseguramos que k sea un NameObject válido con su barra invertida original
        if isinstance(k, str) and not k.startswith('/'):
            k = '/' + k
        self[generic.pdf_name(k)] = v

# Aplicamos el parche globalmente a la clase
SignatureObject.__init__ = _patched_SignatureObject_init
# -----------------------------------------------------------


def _split_name_into_apellidos_nombres(full_name: str):
    """
    Divide el nombre completo en apellidos y nombres.
    FirmaEC Ecuador: APELLIDO1 APELLIDO2 NOMBRE1 [NOMBRE2]
    Divide en dos mitades: la primera mitad = apellidos, la segunda = nombres.
    """
    parts = full_name.strip().upper().split()
    if len(parts) <= 1:
        return full_name.upper(), ""
    mid = (len(parts) + 1) // 2
    return " ".join(parts[:mid]), " ".join(parts[mid:])


def _draw_custom_stamp(output_pdf_path: str, box, signer_name: str):
    """
    Dibuja el sello visual sobre el PDF usando ReportLab:
      - Genera un PDF pequeño con el ancho y alto del "box" exacto.
      - PyHanko se encargará de incrustarlo preservando las firmas anteriores.
    """
    from reportlab.pdfgen import canvas
    from reportlab.lib import colors
    from reportlab.lib.utils import ImageReader
    from reportlab.pdfbase import pdfmetrics
    from reportlab.pdfbase.ttfonts import TTFont

    # ---- Registrar Courier New TTF (Windows) para soportar tildes ----
    FONT_REG  = "CourierNew"
    FONT_BOLD = "CourierNew-Bold"
    FONTS_DIR = r"C:\Windows\Fonts"
    try:
        pdfmetrics.registerFont(TTFont(FONT_REG,  f"{FONTS_DIR}\\cour.ttf"))
        pdfmetrics.registerFont(TTFont(FONT_BOLD, f"{FONTS_DIR}\\courbd.ttf"))
    except Exception:
        # Fallback a las fuentes internas (sin tildes) si no se encuentran los TTF
        FONT_REG  = "Courier"
        FONT_BOLD = "Courier-Bold"

    x1, y1, x2, y2 = box
    stamp_w = float(abs(x2 - x1))
    stamp_h = float(abs(y2 - y1))

    apellidos, nombres = _split_name_into_apellidos_nombres(signer_name)

    # ---------- QR code ----------
    from datetime import datetime as _dt
    _ts = _dt.now().strftime('%Y-%m-%dT%H:%M:%S')
    qr_data = (
        f"https://www.firmadigital.gob.ec/"
        f"?firmado_por={signer_name}"
        f"&fecha_firma={_ts}"
        f"&ubicacion=Latacunga,Cotopaxi,Ecuador"
        f"&motivo=Firma_Electronica_Institucional"
    )
    qr = qrcode.QRCode(
        version=None,
        box_size=10,
        border=1,
        error_correction=qrcode.constants.ERROR_CORRECT_H
    )
    qr.add_data(qr_data)
    qr.make(fit=True)
    qr_img = qr.make_image(fill_color="black", back_color="white")
    qr_buf = BytesIO()
    qr_img.save(qr_buf, format='PNG')

    # ---------- Canvas ReportLab ----------
    c = canvas.Canvas(output_pdf_path, pagesize=(stamp_w, stamp_h))

    # ---- QR a la izquierda ----
    PAD = 3
    qr_size = stamp_h - PAD * 2
    qr_x = PAD
    qr_y = PAD
    qr_buf.seek(0)
    c.drawImage(ImageReader(qr_buf), qr_x, qr_y,
                width=qr_size, height=qr_size, preserveAspectRatio=True)

    # ---- Texto a la derecha: centrado verticalmente respecto al QR ----
    text_x = qr_size + PAD * 2

    font_label = 7.5    # Regular
    font_name  = 9.5    # Bold
    lh_label   = font_label + 2.5   # interlineado etiqueta
    lh_name    = font_name  + 2.5   # interlineado nombre

    # Altura total del bloque de texto
    n_name_lines = 2 if nombres else 1
    block_h = lh_label + (lh_name * n_name_lines) + lh_label

    # Centro vertical del QR → primera línea encima del centro, última debajo
    qr_center_y = qr_y + qr_size / 2
    y_cur = qr_center_y + block_h / 2 - font_label

    c.setFillColor(colors.black)

    # Línea 1 – pie (ahora arriba) — fuente ligeramente menor para que quepa completo
    c.setFont(FONT_REG, 7.0)
    c.drawString(text_x, y_cur, "Validar únicamente en FirmaEC.")

    # Línea 2 – etiqueta
    y_cur -= lh_label
    c.drawString(text_x, y_cur, "Firmado electrónicamente por:")

    # Línea 3 – apellidos
    y_cur -= lh_name
    c.setFont(FONT_BOLD, font_name)
    c.drawString(text_x, y_cur, apellidos)

    # Línea 4 – nombres (si existen)
    if nombres:
        y_cur -= lh_name
        c.drawString(text_x, y_cur, nombres)

    c.save()


def sign_pdf(input_pdf, output_pdf, name, p12_path=None, password=None, pem_path=None, reason="",
             is_tesorero=False, is_gestor=False,
             sig_x=None, sig_y=None, sig_page=None,
             location="", app_version="", app_name="", tsa_url=""):

    import oscrypto.keys
    # ---------- Cargar clave privada / certificado ----------
    if pem_path:
        with open(pem_path, 'rb') as f:
            pem_bytes = f.read()
        private_key_oscrypto = oscrypto.keys.parse_private(pem_bytes)
        certificate_oscrypto = oscrypto.keys.parse_certificate(pem_bytes)
    elif p12_path and password:
        with open(p12_path, 'rb') as f:
            p12_bytes = f.read()
        private_key_oscrypto, certificate_oscrypto, _ = oscrypto.keys.parse_pkcs12(
            p12_bytes, password.encode()
        )
    else:
        raise ValueError("Se debe proveer --pem o bien --p12 y --password")

    signer = signers.SimpleSigner(
        signing_cert=certificate_oscrypto,
        signing_key=private_key_oscrypto,
        cert_registry=None
    )

    # ---------- Determinar coordenadas y página ----------
    with open(input_pdf, 'rb') as doc:
        reader = PdfFileReader(doc, strict=False)
        try:
            page_count = len(reader.root['/Pages']['/Kids'])
        except KeyError:
            page_count = 1

        if sig_x is not None and sig_y is not None:
            box = (sig_x, sig_y, sig_x + 250, sig_y + 80)
        else:
            x1 = 380 if is_tesorero else 70
            box = (x1, 680, x1 + 250, 800)

        target_page = page_count - 1
        if sig_page is not None and 1 <= sig_page <= page_count:
            target_page = int(sig_page) - 1

    # ---- PASO 1: Estampado visual (ReportLab) ----
    import tempfile
    stamped_tmp = tempfile.mktemp(suffix=".pdf")
    _draw_custom_stamp(stamped_tmp, box, name)

    # ---- PASO 2: Firma criptográfica (pyHanko) ----
    with open(input_pdf, 'rb') as doc:
        reader = PdfFileReader(doc, strict=False)
        writer = IncrementalPdfFileWriter(doc, strict=False)

        existing = len(reader.embedded_signatures)
        if is_tesorero:
            sig_field_name = f"Signature_Tesoreria_{existing + 1}"
        elif is_gestor:
            sig_field_name = f"Signature_Gestor_{existing + 1}"
        else:
            sig_field_name = f"Signature{existing + 1}"

        # Metadata y Versión (Prop_Build)
        # Adobe muestra: "La firma se creó con la versión {app_version} ({app_name})"
        build_label = f"{app_name} {app_version}".strip() if app_name else app_version
        bp = BuildProps(name=build_label) if build_label else None
        meta = signers.PdfSignatureMetadata(
            field_name=sig_field_name,
            reason=reason if reason else "",
            location=location if location else "",
            contact_info="", # El parche lo convertirá a TextStringObject('')
            md_algorithm='sha512',
            app_build_props=bp
        )

        from pyhanko.sign.signers.pdf_signer import PdfSigner
        from pyhanko.stamp import StaticStampStyle

        # Insertamos el diseño visual dinámico dibujado previamente en el archivo temporal
        stamp_style = StaticStampStyle.from_pdf_file(stamped_tmp, border_width=0)

        timestamper = HTTPTimeStamper(tsa_url) if tsa_url else None

        pdf_signer_instance = PdfSigner(
            signature_meta=meta,
            signer=signer,
            stamp_style=stamp_style,
            timestamper=timestamper
        )

        append_signature_field(
            writer,
            SigFieldSpec(
                sig_field_name=sig_field_name,
                on_page=target_page,
                box=box
            )
        )

        with open(output_pdf, 'wb') as outf:
            pdf_signer_instance.sign_pdf(writer, in_place=False, output=outf)

    try:
        os.unlink(stamped_tmp)
    except Exception:
        pass

    print("SUCCESS")


if __name__ == "__main__":
    import argparse
    parser = argparse.ArgumentParser(description="Firmar PDF con pyHanko + sello visual ReportLab")
    parser.add_argument("--input",    required=True)
    parser.add_argument("--output",   required=True)
    parser.add_argument("--p12",      required=False, default=None)
    parser.add_argument("--password", required=False, default=None)
    parser.add_argument("--pem",      required=False, default=None)
    parser.add_argument("--name",     required=True)
    parser.add_argument("--reason",   default="")
    parser.add_argument("--sig-x",    type=float, default=None)
    parser.add_argument("--sig-y",    type=float, default=None)
    parser.add_argument("--sig-page", type=int,   default=None)
    parser.add_argument("--location", default="")
    parser.add_argument("--app-version", default="")
    parser.add_argument("--app-name",  default="")
    parser.add_argument("--tsa-url",   default="")
    parser.add_argument("--roles",     nargs='*',  default=[])

    try:
        args = parser.parse_args()
    except Exception as e:
        print(f"ERROR PARSING ARGS: {str(e)}")
        sys.exit(1)

    is_tesorero = "Tesorero" in args.roles
    is_gestor   = "Gestor" in args.roles or "Gestor de Tesorería" in args.roles

    try:
        sign_pdf(args.input, args.output, args.name,
                 p12_path=args.p12, password=args.password, pem_path=args.pem,
                 reason=args.reason, is_tesorero=is_tesorero, is_gestor=is_gestor,
                 sig_x=args.sig_x, sig_y=args.sig_y, sig_page=args.sig_page,
                 location=args.location, app_version=args.app_version,
                 app_name=args.app_name, tsa_url=args.tsa_url)
    except Exception as e:
        import traceback
        print(f"ERROR: {str(e)}")
        traceback.print_exc()
        sys.exit(1)
