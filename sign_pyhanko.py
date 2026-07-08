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

    # ---- Texto a la derecha ----
    text_x = qr_size + PAD      # Acercar texto al QR
    font_label = 4.8            # Tamaño letra normal
    font_name  = 8.1            # Tamaño nombre en negrita
    
    gap_head  = 7.5     # Acercar nombre a "Firmado..."
    gap_lines = 8.5     # Entre nombre y apellido
    gap_foot  = 10.0    # Alejar "Validar..." de nombre

    n_name_lines = 2 if nombres else 1
    # Distancia vertical total entre baselines
    total_baseline_dist = gap_head + gap_foot + (gap_lines if n_name_lines == 2 else 0)
    
    qr_center_y = PAD + qr_size / 2
    y_cur = qr_center_y + total_baseline_dist / 2

    def _draw_with_charspace(canvas_obj, x, y, text, font, size, char_space=0.0, double_pass_offset=0.0):
        """Dibuja texto con espaciado entre caracteres usando PDFTextObject."""
        t = canvas_obj.beginText(x, y)
        t.setFont(font, size)
        t.setCharSpace(char_space)
        t.textOut(text)
        canvas_obj.drawText(t)
        if double_pass_offset > 0:
            # Segunda pasada desplazada para dar más peso/bold
            t2 = canvas_obj.beginText(x + double_pass_offset, y)
            t2.setFont(font, size)
            t2.setCharSpace(char_space)
            t2.textOut(text)
            canvas_obj.drawText(t2)

    c.setFillColor(colors.black)
    # Doble pasada sutil (0.15) a letras normales para imitar el "peso" que tiene en FirmaEC
    _draw_with_charspace(c, text_x, y_cur, "Firmado electrónicamente por:", FONT_REG, font_label, double_pass_offset=0.15)

    y_cur -= gap_head
    # Nombres con charspace ligero y pasada fuerte (0.3)
    _draw_with_charspace(c, text_x, y_cur, apellidos, FONT_BOLD, font_name,
                         char_space=0.3, double_pass_offset=0.3)

    if nombres:
        y_cur -= gap_lines
        _draw_with_charspace(c, text_x, y_cur, nombres, FONT_BOLD, font_name,
                             char_space=0.3, double_pass_offset=0.3)

    y_cur -= gap_foot
    _draw_with_charspace(c, text_x, y_cur, "Validar únicamente en FirmaEC.", FONT_REG, font_label, double_pass_offset=0.15)

    c.save()


def sign_pdf(input_pdf, output_pdf, name, p12_path=None, password=None, cert_path=None, key_path=None, reason="",
             is_tesorero=False, is_gestor=False,
             sig_x=None, sig_y=None, sig_page=None,
             location="", app_version="", app_name="", tsa_url="", positions=None):
 
    import oscrypto.keys
    import tempfile
    # ---------- Cargar clave privada / certificado ----------
    if cert_path and key_path:
        with open(key_path, 'rb') as f:
            key_bytes = f.read()
        with open(cert_path, 'rb') as f:
            cert_bytes = f.read()
        private_key_oscrypto = oscrypto.keys.parse_private(key_bytes)
        certificate_oscrypto = oscrypto.keys.parse_certificate(cert_bytes)
    elif p12_path and password:
        with open(p12_path, 'rb') as f:
            p12_bytes = f.read()
        private_key_oscrypto, certificate_oscrypto, _ = oscrypto.keys.parse_pkcs12(
            p12_bytes, password.encode()
        )
    else:
        raise ValueError("Se debe proveer --cert y --key, o bien --p12 y --password")

    signer = signers.SimpleSigner(
        signing_cert=certificate_oscrypto,
        signing_key=private_key_oscrypto,
        cert_registry=None
    )

    if not positions:
        positions = [{"x": sig_x, "y": sig_y, "page": sig_page}]

    current_input = input_pdf

    for i, pos in enumerate(positions):
        # ---------- Determinar coordenadas y página ----------
        x = pos.get("x")
        y = pos.get("y")
        page = pos.get("page")

        with open(current_input, 'rb') as doc:
            reader = PdfFileReader(doc, strict=False)
            try:
                page_count = len(reader.root['/Pages']['/Kids'])
            except KeyError:
                page_count = 1

            if x is not None and y is not None:
                box = (x, y, x + 250, y + 80)
            else:
                x1 = 380 if is_tesorero else 70
                box = (x1, 680, x1 + 250, 800)

            target_page = page_count - 1
            if page is not None and 1 <= int(page) <= page_count:
                target_page = int(page) - 1

        # ---- PASO 1: Estampado visual (ReportLab) ----
        stamped_tmp = tempfile.mktemp(suffix=".pdf")
        _draw_custom_stamp(stamped_tmp, box, name)

        is_last = (i == len(positions) - 1)
        current_output = output_pdf if is_last else tempfile.mktemp(suffix=".pdf")

        # ---- PASO 2: Firma criptográfica (pyHanko) ----
        with open(current_input, 'rb') as doc:
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

            with open(current_output, 'wb') as outf:
                pdf_signer_instance.sign_pdf(writer, in_place=False, output=outf)

        try:
            os.unlink(stamped_tmp)
        except Exception:
            pass

        if current_input != input_pdf:
            try:
                os.unlink(current_input)
            except Exception:
                pass

        current_input = current_output

    print("SUCCESS")


if __name__ == "__main__":
    import argparse
    import json
    parser = argparse.ArgumentParser(description="Firmar PDF con pyHanko + sello visual ReportLab")
    parser.add_argument("--input",    required=True)
    parser.add_argument("--output",   required=True)
    parser.add_argument("--p12",      required=False, default=None)
    parser.add_argument("--password", required=False, default=None)
    parser.add_argument("--cert",     required=False, default=None)
    parser.add_argument("--key",      required=False, default=None)
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
    parser.add_argument("--positions-base64", default=None, help="Base64 encoded JSON string con arreglo de coordenadas")

    try:
        args = parser.parse_args()
    except Exception as e:
        print(f"ERROR PARSING ARGS: {str(e)}")
        sys.exit(1)

    is_tesorero = "Tesorero" in args.roles
    is_gestor   = "Gestor" in args.roles or "Gestor de Tesorería" in args.roles
    
    positions_list = None
    if args.positions_base64:
        import base64
        try:
            decoded = base64.b64decode(args.positions_base64).decode('utf-8')
            positions_list = json.loads(decoded)
        except Exception as e:
            print(f"ERROR PARSING POSITIONS BASE64: {str(e)}")
            sys.exit(1)

    try:
        sign_pdf(args.input, args.output, args.name,
                 p12_path=args.p12, password=args.password, cert_path=args.cert, key_path=args.key,
                 reason=args.reason, is_tesorero=is_tesorero, is_gestor=is_gestor,
                 sig_x=args.sig_x, sig_y=args.sig_y, sig_page=args.sig_page,
                 location=args.location, app_version=args.app_version,
                 app_name=args.app_name, tsa_url=args.tsa_url, positions=positions_list)
    except Exception as e:
        import traceback
        print(f"ERROR: {str(e)}")
        traceback.print_exc()
        sys.exit(1)
