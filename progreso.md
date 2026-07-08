# Progreso del Proyecto: Pólizas

## 🚀 Hitos Recientes
* **Actualización de Roles (Junio 2026):** Se ha migrado y renombrado el rol de "Asesor Prefectura" a "Prefecto/a" en toda la plataforma. Esto incluyó actualizaciones en seeders, controladores (`PolizaController.php`), vistas Vue, y la migración `2026_06_16_131159_update_role_asesor_to_prefecto.php`.
* **Desarrollo de Firma Electrónica:** Pruebas e implementación de firmado automático y estampado de PDFs utilizando Python (`sign_pyhanko.py`). Archivos de prueba como `stamp_test.pdf` generados con éxito.
* **Integración Frontend/Backend:** Configuración robusta de Vite para compilar componentes Vue 3 que se conectan de forma reactiva con Laravel a través de Inertia.js.

## 📝 Tareas Pendientes / Siguientes Pasos
* **Implementación de Firmas Múltiples Completada (Julio 2026):**
  * Se implementó exitosamente el flujo de firmas múltiples estilo DocuSign para el rol de `Prefecto/a` en el Frontend (Vue 3).
  * Se actualizó `SignPdfService.php` para enviar el arreglo de coordenadas codificado en `Base64` hacia Python, previniendo los errores de parseo de JSON causados por el shell en entornos Windows.
  * Los roles secundarios (Gestor, Tesorero) mantienen su flujo de un solo sello (retrocompatibilidad).

## 📝 Flujo de Trabajo Actual (Renovaciones)
1. **Póliza por vencer:** Gestora genera el Oficio Base.
2. **Firmas de Oficio:** Gestora firma ➡️ Tesorero firma.
3. **Notificación:** Gestora envía el Oficio firmado a la Aseguradora vía Email (Primer y Segundo Aviso).
4. **Respuesta Aseguradora:** Envían el nuevo documento de renovación física/digital.
5. **Registro de Renovación:** Gestora sube el documento de renovación al sistema (`Registrar Renovación`).
6. **Firma Prefecta:** Prefecta entra a su bandeja, hace clic en N posiciones del documento y lo firma electrónicamente en ráfaga.
7. **Firma Contratista (Subida Final):** Gestora usa la bandeja "Renovaciones Listas para Archivo", recibe el PDF con firmas externas, y lo sube mediante el botón "Subir Firma Contratista". Esto reemplaza el archivo en disco manteniendo las banderas de flujo.
8. **Finalización / Archivo:** El documento legal está completo y almacenado en el sistema.

## 🛠️ Comandos de Mantenimiento y Puesta en Producción (Go-Live)
* **Limpieza del Historial de Firmas:** 
  Para evitar que pólizas antiguas (que ya fueron firmadas en físico) sigan apareciendo como pendientes en las bandejas del Gestor, Tesorero y Prefecta, se creó el comando `LimpiarHistoricoPolizas`.
  Este comando marca automáticamente como firmados los oficios y renovaciones creados antes de una fecha límite.
  
  **Uso en la terminal de Producción (Proxmox / Linux):**
  ```bash
  # Limpia todo lo creado antes del 29 de Junio de 2026 (por defecto)
  php artisan polizas:limpiar-historico

  # Limpia todo lo creado antes de una fecha específica
  php artisan polizas:limpiar-historico --fecha="2026-07-01"
  ```
