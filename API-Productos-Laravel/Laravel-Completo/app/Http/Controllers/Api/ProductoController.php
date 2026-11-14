<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class ProductoController extends Controller
{
    private function archivo(): string
    {
        $directorio = storage_path('app');

        if (!File::exists($directorio)) {
            File::makeDirectory($directorio, 0755, true);
        }

        $archivo = $directorio . '/productos.json';

        if (!File::exists($archivo)) {
            File::put($archivo, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        return $archivo;
    }

    private function leerProductos(): array
    {
        $productos = json_decode(File::get($this->archivo()), true);
        return is_array($productos) ? $productos : [];
    }

    private function guardarProductos(array $productos): void
    {
        File::put(
            $this->archivo(),
            json_encode(array_values($productos), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            true
        );
    }

    private function validarId(string $id): ?JsonResponse
    {
        if (!ctype_digit($id) || (int) $id < 1) {
            return response()->json([
                'message' => 'El ID debe ser un número entero positivo.',
            ], 400);
        }

        return null;
    }

    private function buscarIndice(array $productos, string $id): ?int
    {
        foreach ($productos as $indice => $producto) {
            if (isset($producto['id']) && (string) $producto['id'] === $id) {
                return $indice;
            }
        }

        return null;
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'message' => 'Productos obtenidos correctamente.',
            'data' => $this->leerProductos(),
        ], 200);
    }

    public function show(string $id): JsonResponse
    {
        if ($error = $this->validarId($id)) {
            return $error;
        }

        $productos = $this->leerProductos();
        $indice = $this->buscarIndice($productos, $id);

        if ($indice === null) {
            return response()->json(['message' => 'Producto no encontrado.'], 404);
        }

        return response()->json([
            'message' => 'Producto obtenido correctamente.',
            'data' => $productos[$indice],
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'precio' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Los datos enviados no son válidos.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $datos = $validator->validated();
        $productos = $this->leerProductos();
        $ids = array_map(fn ($producto) => (int) ($producto['id'] ?? 0), $productos);
        $ahora = now()->toISOString();

        $producto = [
            'id' => empty($ids) ? 1 : max($ids) + 1,
            'nombre' => $datos['nombre'],
            'descripcion' => $datos['descripcion'] ?? null,
            'precio' => (float) $datos['precio'],
            'stock' => (int) $datos['stock'],
            'created_at' => $ahora,
            'updated_at' => $ahora,
        ];

        $productos[] = $producto;
        $this->guardarProductos($productos);

        return response()->json([
            'message' => 'Producto creado correctamente.',
            'data' => $producto,
        ], 201);
    }

    // PUT reemplaza los datos editables del producto; por eso todos son obligatorios.
    public function update(Request $request, string $id): JsonResponse
    {
        if ($error = $this->validarId($id)) {
            return $error;
        }

        $productos = $this->leerProductos();
        $indice = $this->buscarIndice($productos, $id);

        if ($indice === null) {
            return response()->json(['message' => 'Producto no encontrado.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'precio' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Para PUT debes enviar nombre, descripcion, precio y stock con datos válidos.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $datos = $validator->validated();
        $productos[$indice] = array_merge($productos[$indice], [
            'nombre' => $datos['nombre'],
            'descripcion' => $datos['descripcion'] ?? null,
            'precio' => (float) $datos['precio'],
            'stock' => (int) $datos['stock'],
            'updated_at' => now()->toISOString(),
        ]);

        $this->guardarProductos($productos);

        return response()->json([
            'message' => 'Producto actualizado completamente mediante PUT.',
            'data' => $productos[$indice],
        ], 200);
    }

    // PATCH modifica solamente los campos que se envíen.
    public function patch(Request $request, string $id): JsonResponse
    {
        if ($error = $this->validarId($id)) {
            return $error;
        }

        if (count($request->all()) === 0) {
            return response()->json([
                'message' => 'Debes enviar al menos un campo para actualizar.',
            ], 400);
        }

        $productos = $this->leerProductos();
        $indice = $this->buscarIndice($productos, $id);

        if ($indice === null) {
            return response()->json(['message' => 'Producto no encontrado.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => ['sometimes', 'required', 'string', 'max:150'],
            'descripcion' => ['sometimes', 'nullable', 'string'],
            'precio' => ['sometimes', 'required', 'numeric', 'min:0'],
            'stock' => ['sometimes', 'required', 'integer', 'min:0'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Los datos enviados no son válidos.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $datos = $validator->validated();
        $productos[$indice] = array_merge($productos[$indice], $datos, [
            'updated_at' => now()->toISOString(),
        ]);

        if (array_key_exists('precio', $datos)) {
            $productos[$indice]['precio'] = (float) $datos['precio'];
        }
        if (array_key_exists('stock', $datos)) {
            $productos[$indice]['stock'] = (int) $datos['stock'];
        }

        $this->guardarProductos($productos);

        return response()->json([
            'message' => 'Producto actualizado parcialmente mediante PATCH.',
            'data' => $productos[$indice],
        ], 200);
    }

    public function destroy(string $id): JsonResponse
    {
        if ($error = $this->validarId($id)) {
            return $error;
        }

        $productos = $this->leerProductos();
        $indice = $this->buscarIndice($productos, $id);

        if ($indice === null) {
            return response()->json(['message' => 'Producto no encontrado.'], 404);
        }

        $eliminado = $productos[$indice];
        unset($productos[$indice]);
        $this->guardarProductos($productos);

        return response()->json([
            'message' => 'Producto eliminado correctamente.',
            'data' => $eliminado,
        ], 200);
    }
}
