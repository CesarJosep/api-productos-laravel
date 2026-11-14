# API REST de Productos - Laravel 12 (sin base de datos)

API CRUD para administrar productos usando un archivo JSON local en `storage/app/productos.json`. No requiere MySQL, Firebase ni ejecutar migraciones. Thunder Client o Postman se utilizan únicamente para enviar y probar las peticiones HTTP.

## Requisitos
- PHP 8.2 o superior
- Composer
- Laravel 12

## Instalación
1. Abre la carpeta `Laravel-Completo` en Visual Studio Code.
2. Abre la terminal en esa carpeta.
3. Ejecuta `composer install` si no existe la carpeta `vendor`.
4. Si no existe `.env`, copia `.env.example` como `.env`.
5. Ejecuta `php artisan key:generate`.
6. Ejecuta `php artisan optimize:clear`.
7. Inicia el servidor con `php artisan serve`.

No ejecutes `php artisan migrate`: esta versión guarda los productos en `storage/app/productos.json`.

## Endpoints
Base URL: `http://127.0.0.1:8000/api`

| Método | URL | Acción | Éxito |
|---|---|---|---|
| GET | `/productos` | Consultar todos | 200 |
| GET | `/productos/{id}` | Consultar uno | 200 |
| POST | `/productos` | Crear | 201 |
| PUT | `/productos/{id}` | Reemplazar/actualizar todos los campos | 200 |
| PATCH | `/productos/{id}` | Actualizar solo campos enviados | 200 |
| DELETE | `/productos/{id}` | Eliminar | 200 |

## Encabezados para Postman o Thunder Client
`Accept: application/json`
`Content-Type: application/json`

## Body para POST y PUT
```json
{
  "nombre": "Laptop Gamer",
  "descripcion": "Laptop con 16GB RAM y 1TB SSD",
  "precio": 15000.50,
  "stock": 10
}
```

Para PUT deben enviarse los cuatro campos. Para PATCH se puede enviar solo el campo que se desea modificar, por ejemplo:
```json
{
  "precio": 13999.99
}
```

## Códigos HTTP
- `200`: consulta, actualización o eliminación exitosa.
- `201`: producto creado.
- `400`: ID inválido o PATCH sin campos.
- `404`: producto no encontrado.
- `422`: datos que no pasan las validaciones.

## Pruebas sugeridas
1. GET `/api/productos`.
2. POST `/api/productos` con el JSON de ejemplo.
3. GET `/api/productos/1` (usa el ID que devolvió POST).
4. PUT `/api/productos/1` con los cuatro campos.
5. PATCH `/api/productos/1` con un solo campo.
6. DELETE `/api/productos/1`.
7. Prueba un ID inexistente para comprobar el 404.
8. Envía precio negativo o nombre vacío para comprobar el 422.
9. Envía PATCH con `{}` para comprobar el 400.

## GitHub
No subas `.env`, `vendor/` ni `node_modules/`. Conserva `.env.example` y el archivo `storage/app/productos.json` si quieres incluir los datos de prueba. Revisa `.gitignore` antes de publicar.
