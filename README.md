# FinanciaCel - Sistema de Solicitudes de Crédito

FinanciaCel es una aplicación web desarrollada con Laravel que permite gestionar solicitudes de crédito para la compra de celulares. Incluye funcionalidades para simular créditos con interés simple (Crea y muestra tabla de amortización para el plan de pagos), listar clientes y celulares en stock. 

## Requisitos Previos

- PHP >= 8.2
- Composer
- MySQL o PostgreSQL
- Git

## Instalación

Sigue estos pasos para configurar el proyecto en tu máquina:

1. **Clonar el repositorio**

- Vía https:
```bash
git clone https://github.com/PerdomoV/FinanciaCel.git
cd FinanciaCel
```

- Vía SSH:
```bash
git clone git@github.com:PerdomoV/FinanciaCel.git
cd FinanciaCel
```

2. **Instalar dependencias de PHP**
```bash
composer install
```

3. **Configurar el entorno**
```bash
# Copiar el archivo de ejemplo de variables de entorno
cp .env.example .env

# Generar la clave de la aplicación
php artisan key:generate
```

4. **Configurar la base de datos**
- Crear una base de datos en tu sistema gestor de base de datos
- Editar el archivo `.env` con tus credenciales de base de datos:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=financiacel
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
```

5. **Ejecutar las migraciones y seeders**
```bash
# Ejecutar migraciones
php artisan migrate

# Ejecutar seeders 
php artisan db:seed
```

6. **Configurar Swagger/OpenAPI**
```bash
# Publicar la configuración de Swagger
php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"

# Generar la documentación de la API
php artisan l5-swagger:generate
```

## Ejecutar el Proyecto

1. **Iniciar el servidor de desarrollo**
```bash
php artisan serve
```
El formulario de creación de solicitud de crédito, así como los resultados de la simulación de crédito (Tabla de amortización y plan de pago) están disponibles en el endpoint raíz de la aplicación '/'. En desarrollo es posible acceder a él, a través de la url: `http://localhost:8000/`

2. **Acceder a la documentación de la API**
- Visita el endpoint `api/documentation` para ver la documentación interactiva de la API con swaggerUI (En desarrollo: `http://localhost:8000/api/documentation`)

## Estructura de la API

La API está organizada en los siguientes módulos:

### Solicitudes de Crédito
- `POST /api/credits/simulate` - Simular una solicitud de crédito
- `POST /api/credits` - Crear una nueva solicitud de crédito
- `GET /api/credits/{id}/status` - Obtener estado de una solicitud
- `GET /api/credits/{id}/installments` - Obtener cuotas de una solicitud

### Clientes
- `GET /api/clients` - Obtener lista de clientes

### Teléfonos
- `GET /api/phones` - Obtener lista de teléfonos disponibles

## Pruebas

Para ejecutar las pruebas del proyecto:
```bash
php artisan test
```

## Documentación 

La documentación completa de la API está disponible a través de Swagger UI. Puedes acceder a ella en:
- Desarrollo: `http://localhost:8000/api/documentation`
- Producción: `https://tu-dominio.com/api/documentation`
