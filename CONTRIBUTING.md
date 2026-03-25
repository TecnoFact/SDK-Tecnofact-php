# Contribuir a TecnoFact SDK

¡Gracias por tu interés en contribuir al SDK de TecnoFact! Este documento proporciona las pautas para contribuir al proyecto.

## Código de Conducta

Este proyecto y todos sus participantes están sujetos a nuestro Código de Conducta. Al participar, se espera que respetes estos estándares.

## Cómo Contribuir

### Reportar Bugs

Antes de crear un issue, por favor:

1. Verifica que el bug no haya sido reportado previamente
2. Usa el buscador de issues para buscar similares
3. Si no encuentras uno existente, crea un nuevo issue con la plantilla de bug report

#### Estructura del Reporte de Bug

Incluye la siguiente información:

- **Título claro y descriptivo**
- **Versión del SDK:** ej. 1.0.0
- **Versión de PHP:** ej. 8.3
- **Sistema Operativo:** Windows/Linux/Mac
- **Descripción del problema:** Qué está pasando
- **Pasos para reproducir:** Lista numerada de pasos
- **Comportamiento esperado:** Qué debería pasar
- **Comportamiento actual:** Qué está pasando
- **Código de ejemplo:** Ejemplo mínimo reproducible
- **Logs/Stack trace:** Si aplica

### Sugerir Mejoras

Para sugerir nuevas funcionalidades:

1. Abre un issue con el tag `enhancement`
2. Describe claramente la funcionalidad propuesta
3. Explica el caso de uso
4. Proporciona ejemplos de cómo se usaría

### Pull Requests

#### Proceso

1. **Fork** el repositorio
2. **Crea una rama** desde `main`:
   ```bash
   git checkout -b feature/nombre-de-tu-feature
   # o
   git checkout -b fix/nombre-del-bug
   ```
3. **Haz tus cambios** siguiendo las guías de estilo
4. **Escribe tests** para tus cambios
5. **Asegúrate de que todos los tests pasen**:
   ```bash
   composer test
   ```
6. **Actualiza la documentación** si es necesario
7. **Commit** tus cambios con mensajes descriptivos:
   ```bash
   git commit -m "feat: agrega soporte para cancelación masiva"
   ```
8. **Push** a tu fork:
   ```bash
   git push origin feature/nombre-de-tu-feature
   ```
9. **Abre un Pull Request** en GitHub

#### Convención de Commits

Usamos [Conventional Commits](https://www.conventionalcommits.org/):

- `feat:` Nueva funcionalidad
- `fix:` Corrección de bug
- `docs:` Cambios en documentación
- `style:` Cambios de formato (espacios, punto y coma, etc.)
- `refactor:` Refactorización de código
- `test:` Agregar o modificar tests
- `chore:` Tareas de mantenimiento

Ejemplos:
```
feat: agrega soporte para timbrado CFDI 4.0
fix: corrige validación de RFC en emisores
refactor: mejora manejo de errores en cancelación
docs: actualiza ejemplos de timbrado masivo
```

#### Requisitos del Pull Request

- [ ] El código sigue las guías de estilo del proyecto
- [ ] Se han agregado tests para la nueva funcionalidad
- [ ] Todos los tests existentes pasan
- [ ] Se ha actualizado la documentación
- [ ] Los commits siguen la convención de commits
- [ ] El PR tiene una descripción clara de los cambios

## Guías de Estilo

### Código PHP

- Seguimos el estándar **PSR-12**
- Usamos **tipado estricto** (`declare(strict_types=1);`)
- Nombres de clases en **PascalCase**
- Nombres de métodos y variables en **camelCase**
- Nombres de constantes en **UPPER_SNAKE_CASE**
- Documentación en español
- Comentarios de código en español

### Ejemplo de Estilo

```php
<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Models;

/**
 * Representa un emisor de CFDI
 */
class Emisor
{
    /**
     * RFC del emisor
     */
    private string $rfc;
    
    /**
     * Nombre o razón social
     */
    private string $nombre;
    
    /**
     * Constructor
     */
    public function __construct(
        string $rfc,
        string $nombre,
        string $regimenFiscal,
        string $cp
    ) {
        $this->rfc = $rfc;
        $this->nombre = $nombre;
        $this->regimenFiscal = $regimenFiscal;
        $this->cp = $cp;
    }
    
    /**
     * Obtiene el RFC del emisor
     */
    public function getRfc(): string
    {
        return $this->rfc;
    }
    
    /**
     * Convierte a array para serialización JSON
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'rfc' => $this->rfc,
            'nombre' => $this->nombre,
            'regimen_fiscal' => $this->regimenFiscal,
            'cp' => $this->cp,
        ];
    }
}
```

### Tests

- Usamos **PHPUnit** para testing
- Todos los tests deben pasar antes de mergear
- Mantener cobertura de código > 80%
- Tests descriptivos que expliquen el comportamiento:

```php
public function test_timbrar_cfdi_con_datos_validos_retorna_uuid(): void
{
    // Arrange
    $cfdi = $this->createValidCfdi();
    
    // Act
    $response = $this->client->cfdi()->timbrar($cfdi);
    
    // Assert
    $this->assertNotEmpty($response->getUuid());
    $this->assertMatchesRegularExpression(
        '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
        $response->getUuid()
    );
}
```

## Configuración de Desarrollo

### Requisitos

- PHP >= 8.0
- Composer
- Git

### Instalación

```bash
# Clonar el repositorio
git clone https://github.com/TecnoFact/SDK-Tecnofact-php.git
cd SDK-Tecnofact-php

# Instalar dependencias
composer install

# Ejecutar tests
composer test

# Ejecutar análisis estático
composer analyze

# Verificar estilo de código
composer style

# Corregir estilo de código automáticamente
composer fix-style
```

### Scripts de Composer

```bash
# Tests
composer test

# Tests con cobertura
composer test-coverage

# Análisis estático con PHPStan
composer analyze

# Verificar estilo con PHP CS Fixer
composer style

# Corregir estilo
composer fix-style

# Todos los checks (ejecutar antes de PR)
composer check
```

## Documentación

- Actualiza el README.md si agregas nuevas funcionalidades
- Documenta todos los métodos públicos con PHPDoc
- Incluye ejemplos de uso en la documentación
- Mantén el CHANGELOG.md actualizado

## Preguntas

Si tienes preguntas sobre cómo contribuir:

- Revisa la documentación existente
- Busca en los issues existentes
- Abre un issue con la etiqueta `question`
- Contáctanos en soporte@tecnofact.com

## Agradecimientos

Agradecemos a todos los contribuidores que ayudan a mejorar este proyecto. ¡Tu tiempo y esfuerzo son muy valiosos!

---

**Nota:** Al contribuir a este proyecto, aceptas que tus contribuciones serán licenciadas bajo la Licencia MIT.
