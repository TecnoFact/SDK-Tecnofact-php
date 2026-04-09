# Guía de Contribución

¡Gracias por tu interés en contribuir al SDK de TecnoFact! Esta guía te ayudará a entender nuestro proceso de desarrollo y versionado.

## 📋 Tabla de Contenidos

- [Conventional Commits](#conventional-commits)
- [Versionado Semántico](#versionado-semántico)
- [Proceso de Desarrollo](#proceso-de-desarrollo)
- [Pull Requests](#pull-requests)
- [Estándares de Código](#estándares-de-código)

---

## 🔖 Conventional Commits

Utilizamos [Conventional Commits](https://www.conventionalcommits.org/) para generar automáticamente versiones y changelogs.

### Formato

```
<tipo>[alcance opcional]: <descripción>

[cuerpo opcional]

[nota(s) al pie opcional(es)]
```

### Tipos de Commits

| Tipo | Descripción | Versión |
|------|-------------|---------|
| `feat` | Nueva funcionalidad | MINOR (1.x.0) |
| `fix` | Corrección de bug | PATCH (1.0.x) |
| `perf` | Mejora de rendimiento | PATCH (1.0.x) |
| `refactor` | Refactorización de código | PATCH (1.0.x) |
| `docs` | Cambios en documentación | PATCH (1.0.x) |
| `build` | Cambios en build system | PATCH (1.0.x) |
| `test` | Agregar/modificar tests | No genera versión |
| `ci` | Cambios en CI/CD | No genera versión |
| `chore` | Tareas de mantenimiento | No genera versión |
| `style` | Cambios de formato | No genera versión |
| `revert` | Revertir cambios | PATCH (1.0.x) |

### Breaking Changes

Para cambios que rompen compatibilidad (MAJOR version):

```bash
feat!: cambio que rompe compatibilidad

BREAKING CHANGE: descripción del cambio incompatible
```

### Ejemplos

```bash
# Nueva funcionalidad (MINOR)
feat: agregar soporte para CFDI 4.0
feat(auth): implementar autenticación OAuth2

# Corrección de bug (PATCH)
fix: corregir validación de RFC
fix(http): resolver timeout en peticiones largas

# Breaking change (MAJOR)
feat!: cambiar estructura de respuesta de API

BREAKING CHANGE: La respuesta ahora retorna un objeto en lugar de array
```

---

## 📦 Versionado Semántico

Seguimos [Semantic Versioning 2.0.0](https://semver.org/):

```
MAJOR.MINOR.PATCH
```

- **MAJOR**: Cambios incompatibles con versiones anteriores
- **MINOR**: Nueva funcionalidad compatible con versiones anteriores
- **PATCH**: Correcciones de bugs compatibles

### Proceso Automático

1. **Commit con formato convencional** → Push a `main` o `develop`
2. **GitHub Actions analiza commits** → Determina tipo de versión
3. **Genera nueva versión** → Crea tag automáticamente
4. **Actualiza CHANGELOG.md** → Documenta cambios
5. **Crea GitHub Release** → Publica release notes
6. **Notifica a Packagist** → Actualiza paquete

### Ramas y Versiones

- **`main`**: Versiones estables (1.0.0, 1.1.0, 2.0.0)
- **`develop`**: Versiones beta (1.0.0-beta.1, 1.1.0-beta.2)

---

## 🔄 Proceso de Desarrollo

### 1. Fork y Clone

```bash
# Fork el repositorio en GitHub
git clone https://github.com/TU_USUARIO/SDK-Tecnofact-php.git
cd SDK-Tecnofact-php
```

### 2. Crear Rama

```bash
# Crear rama desde develop
git checkout develop
git pull origin develop
git checkout -b feat/nueva-funcionalidad
```

### 3. Desarrollar

```bash
# Instalar dependencias
composer install

# Ejecutar tests
vendor/bin/phpunit

# Ejecutar análisis estático
vendor/bin/phpstan analyse
vendor/bin/psalm

# Verificar estilo de código
vendor/bin/php-cs-fixer fix --dry-run
```

### 4. Commit

```bash
# Agregar cambios
git add .

# Commit con formato convencional
git commit -m "feat: agregar nueva funcionalidad"
```

### 5. Push y PR

```bash
# Push a tu fork
git push origin feat/nueva-funcionalidad

# Crear Pull Request en GitHub hacia develop
```

---

## 🔍 Pull Requests

### Checklist

- [ ] Código sigue PSR-12
- [ ] Tests agregados/actualizados
- [ ] PHPStan nivel 9 pasa sin errores
- [ ] Psalm nivel 3 pasa sin errores
- [ ] Documentación actualizada
- [ ] CHANGELOG.md actualizado (opcional, se genera automáticamente)
- [ ] Commits siguen Conventional Commits

### Plantilla de PR

```markdown
## Descripción
Breve descripción de los cambios

## Tipo de Cambio
- [ ] Bug fix (PATCH)
- [ ] Nueva funcionalidad (MINOR)
- [ ] Breaking change (MAJOR)
- [ ] Documentación
- [ ] Refactorización

## ¿Cómo se ha probado?
Describe las pruebas realizadas

## Checklist
- [ ] Tests pasan localmente
- [ ] PHPStan nivel 9 sin errores
- [ ] Código sigue PSR-12
- [ ] Documentación actualizada
```

---

## 📝 Estándares de Código

### PHP

- **Versión**: PHP 8.0+
- **Estándar**: PSR-12
- **Strict Types**: `declare(strict_types=1)` en todos los archivos
- **Type Hints**: Completos en parámetros, retornos y propiedades
- **Final Classes**: Usar `final` donde sea posible
- **Readonly**: Usar `readonly` para propiedades inmutables

### Ejemplo

```php
<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Models;

final readonly class Example
{
    public function __construct(
        private string $name,
        private int $value,
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): int
    {
        return $this->value;
    }
}
```

### Tests

- **Framework**: PHPUnit 10+
- **Cobertura**: Mínimo 80%
- **Naming**: `test` + descripción en camelCase
- **Assertions**: Específicas y descriptivas

```php
public function testValidatesApiKeyLength(): void
{
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('API Key debe tener al menos 10 caracteres');
    
    new Config(apiKey: 'short', apiSecret: 'valid-secret-1234567890');
}
```

---

## 🔒 Seguridad

Si encuentras una vulnerabilidad de seguridad, **NO** abras un issue público. 

Envía un email a: **security@tecnofact.com**

Ver [SECURITY.md](../SECURITY.md) para más detalles.

---

## 📞 Contacto

- **Email**: soporte@tecnofact.com
- **Issues**: [GitHub Issues](https://github.com/TecnoFact/SDK-Tecnofact-php/issues)
- **Documentación**: [docs.tecnofact.com](https://docs.tecnofact.com)

---

## 📄 Licencia

Al contribuir, aceptas que tus contribuciones serán licenciadas bajo la Licencia MIT.

---

**¡Gracias por contribuir al SDK de TecnoFact! 🎉**
