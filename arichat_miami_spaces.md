# Arichat.co en miami

Notas para retomar conexion y busqueda de archivos en DigitalOcean Spaces.

## SSH

Alias local:

```bash
ssh miami
```

Servidor:

```text
/home/forge/arichat.co
```

No guardar ni imprimir contrasenas, tokens, access keys ni secrets en este archivo.

## Spaces / S3

El proyecto `/home/forge/arichat.co` tiene variables S3/Spaces en `.env`:

```text
AWS_ACCESS_KEY_ID
AWS_SECRET_ACCESS_KEY
AWS_DEFAULT_REGION
AWS_BUCKET
AWS_ENDPOINT
AWS_USE_PATH_STYLE_ENDPOINT
```

Estado observado el 2026-04-27:

- `aws` CLI existe en el servidor: `/snap/bin/aws`
- `doctl` no estaba disponible en PATH.
- Laravel reporto `Laravel Framework 11.47.0`.
- El bucket configurado respondio via `aws s3 ls`.
- El disco default de Laravel aparecio como `FILESYSTEM_DISK=local`.
- `config/filesystems.php` tiene un disco `spaces` compatible con S3.

Comando seguro para listar el bucket configurado sin mostrar secretos:

```bash
ssh miami 'cd /home/forge/arichat.co && set -a && . ./.env && set +a && aws s3 ls "s3://$AWS_BUCKET" --endpoint-url "$AWS_ENDPOINT" --region "$AWS_DEFAULT_REGION"'
```

## Busqueda: JOSE DANIEL VINASCO

Busqueda ejecutada en el Space configurado:

```bash
ssh miami 'cd /home/forge/arichat.co && set -a && . ./.env && set +a && aws s3 ls "s3://$AWS_BUCKET" --recursive --endpoint-url "$AWS_ENDPOINT" --region "$AWS_DEFAULT_REGION" 2>/dev/null | grep -Ei "vinasco|jose[ _.-]*daniel|daniel[ _.-]*vinasco|jose[ _.-]*daniel[ _.-]*vinasco"'
```

Resultados relevantes encontrados bajo `files/672524/`:

```text
files/672524/ANTECEDENTES JOSE DANIEL VINASCO.PNG
files/672524/CLINTON JOSE DANIEL VINAZCO.PNG
files/672524/FRA JOSE DANIEL VINASCO.pdf
files/672524/ORDEN DE PRODUCCION JOSE DANIEL VINASCO.jpeg
files/672524/PAGO JOSE DANIEL VINASCO.jpeg
files/672524/PROFORMA  JOSE DANIEL VINASCO.pdf
```

Nota: uno de los archivos aparece escrito como `VINAZCO`, no `VINASCO`.

## Correccion customer 668520

El 2026-04-27 se reviso `https://arichat.co/customers/668520/show`.

Hallazgo:

- El customer real en BD es `668520`: `Jose Daniel Vinazco Dias`.
- Los registros de `customer_files` apuntaban a `customer_id = 668520`.
- El controlador abre archivos en Spaces con la key `files/{customer_id}/{url}`.
- Los seis archivos antiguos estaban fisicamente en `files/672524/`, pero no existia customer `672524` en BD.
- Por eso los links de la ficha buscaban `files/668520/...` y fallaban.

Correccion aplicada, no destructiva:

```bash
ssh miami 'cd /home/forge/arichat.co && set -a && . ./.env && set +a && aws s3 cp "s3://$AWS_BUCKET/files/672524/" "s3://$AWS_BUCKET/files/668520/" --recursive --endpoint-url "$AWS_ENDPOINT" --region "$AWS_DEFAULT_REGION"'
```

Verificacion posterior:

- `files/668520/` contiene los seis archivos copiados y el DOCX nuevo.
- Los siete registros de `customer_files` para `668520` reportan `status = OK` desde el accessor del modelo.
