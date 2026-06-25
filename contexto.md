# Contexto del Proyecto: Pólizas

## 🛠️ Stack Tecnológico
* **Backend:** Laravel 12 (PHP ^8.2)
* **Frontend:** Vue 3 con Inertia.js
* **Estilos:** Tailwind CSS (Vite, PostCSS)
* **Base de Datos / ORM:** Eloquent (Laravel)
* **Gestión de Roles:** Spatie Laravel Permission
* **Auditoría:** Spatie Laravel Activitylog
* **Importación/Exportación:** OpenSpout (Manejo de Excel/CSV)

## 📄 Gestión y Firma de Documentos (PDF)
El ecosistema central del proyecto gira en torno a la generación, validación y firma electrónica de pólizas y documentos:
* **Librerías PHP:** `barryvdh/laravel-dompdf` (generación HTML a PDF), `tecnickcom/tcpdf`, `setasign/fpdi` (manipulación e importación).
* **Firma Electrónica Avanzada:** Interoperabilidad con scripts de Python mediante la librería **pyHanko** (`sign_pyhanko.py`, `test_stamp.py`). Esto permite la inyección de firmas digitales con validez legal (PAdES/PKCS#11) y sellos/estampados visibles en los documentos generados.

## 📂 Entorno de Desarrollo
* **Sistema Operativo / Servidor:** Windows (gestionado mediante Laragon)
* **Directorio Base:** `c:\laragon\www\polizas`
