# 🔍 Comandos para Comparar Ramas

## 📋 Comandos Útiles para Ver Diferencias

### 1. Ver diferencias en archivos específicos

#### Comparar PaymentGatewayService.php entre `origin/prod` y `oficial`:
```bash
git diff origin/prod oficial -- app/Services/PaymentGatewayService.php
```

#### Comparar PaymentController.php entre `origin/prod` y `oficial`:
```bash
git diff origin/prod oficial -- app/Http/Controllers/PaymentController.php
```

#### Comparar VentaController.php entre `origin/prod` y `oficial`:
```bash
git diff origin/prod oficial -- app/Http/Controllers/VentaController.php
```

---

### 2. Ver qué archivos existen en una rama pero no en otra

#### Archivos que existen en `origin/prod` pero NO en `oficial`:
```bash
git diff --name-only --diff-filter=A origin/prod oficial
```

#### Archivos que existen en `oficial` pero NO en `origin/prod`:
```bash
git diff --name-only --diff-filter=D origin/prod oficial
```

#### Archivos modificados entre `origin/prod` y `oficial`:
```bash
git diff --name-only --diff-filter=M origin/prod oficial
```

---

### 3. Ver diferencias en archivos relacionados con Pago Fácil

#### Todos los archivos relacionados con pagos:
```bash
git diff origin/prod oficial --name-only | grep -i "pago\|payment"
```

#### Ver contenido de un archivo en otra rama (sin cambiar de rama):
```bash
# Ver PaymentGatewayService.php de origin/prod
git show origin/prod:app/Services/PaymentGatewayService.php

# Ver PaymentController.php de origin/prod
git show origin/prod:app/Http/Controllers/PaymentController.php
```

---

### 4. Comparar con master (CUIDADO - cambios estructurales)

#### Ver qué archivos críticos fueron eliminados en master:
```bash
git diff --name-only --diff-filter=D master oficial | grep -E "(Payment|Venta|Pago)"
```

#### Ver qué archivos nuevos hay en master:
```bash
git diff --name-only --diff-filter=A master oficial | head -20
```

---

### 5. Ver commits específicos de cada rama

#### Últimos 10 commits de origin/prod:
```bash
git log origin/prod --oneline -10
```

#### Últimos 10 commits de master:
```bash
git log master --oneline -10
```

#### Ver un commit específico:
```bash
git show <commit-hash>
```

---

### 6. Ver diferencias lado a lado (más legible)

#### Usar herramienta visual (si tienes configurada):
```bash
git difftool origin/prod oficial -- app/Services/PaymentGatewayService.php
```

---

### 7. Crear un archivo con todas las diferencias

#### Guardar diferencias de origin/prod vs oficial:
```bash
git diff origin/prod oficial > diferencias_prod_vs_oficial.patch
```

#### Guardar solo nombres de archivos diferentes:
```bash
git diff --name-status origin/prod oficial > archivos_diferentes.txt
```

---

## 🎯 Comandos Recomendados para Tu Caso

### Paso 1: Ver mejoras en Pago Fácil de origin/prod
```bash
# Ver diferencias en el servicio
git diff origin/prod oficial -- app/Services/PaymentGatewayService.php > diff_payment_service.txt

# Ver diferencias en el controlador
git diff origin/prod oficial -- app/Http/Controllers/PaymentController.php > diff_payment_controller.txt
```

### Paso 2: Ver qué archivos nuevos hay en origin/prod
```bash
git diff --name-only --diff-filter=A origin/prod oficial
```

### Paso 3: Ver qué archivos fueron modificados en origin/prod
```bash
git diff --name-only --diff-filter=M origin/prod oficial | grep -E "(Controller|Service|Model)"
```

---

## ⚠️ Comandos de Precaución (NO EJECUTAR sin revisar)

### NO hacer esto sin revisar primero:
```bash
# ❌ NO hacer merge directo
git merge master  # NO HACER - rompería tu código

# ❌ NO hacer pull sin revisar
git pull origin master  # NO HACER
```

### ✅ Hacer esto primero:
```bash
# ✅ Ver diferencias primero
git diff origin/prod oficial

# ✅ Crear una rama de prueba
git checkout -b prueba-mejoras-prod

# ✅ Traer cambios específicos (cherry-pick)
git cherry-pick <commit-hash>
```

---

## 📝 Notas Importantes

1. **Siempre revisa las diferencias antes de aplicar cambios**
2. **Crea una rama de prueba antes de hacer merge**
3. **Haz backup de tu código actual**
4. **Revisa archivo por archivo los cambios críticos**



