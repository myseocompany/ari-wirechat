import argparse
import csv
import json
import os
import re
import time
import urllib.request
from collections import defaultdict
import unicodedata


def load_env(env_path):
    if not os.path.exists(env_path):
        return
    with open(env_path, "r", encoding="utf-8") as f:
        for line in f:
            line = line.strip()
            if not line or line.startswith("#") or "=" not in line:
                continue
            key, value = line.split("=", 1)
            os.environ.setdefault(key.strip(), value.strip())


def openai_chat_completion(api_key, model, messages, timeout):
    payload = {
        "model": model,
        "messages": messages,
        "temperature": 0,
    }
    data = json.dumps(payload).encode("utf-8")
    req = urllib.request.Request(
        "https://api.openai.com/v1/chat/completions",
        data=data,
        headers={
            "Content-Type": "application/json",
            "Authorization": f"Bearer {api_key}",
        },
        method="POST",
    )
    with urllib.request.urlopen(req, timeout=timeout) as resp:
        return json.loads(resp.read().decode("utf-8"))


def extract_json(text):
    match = re.search(r"\{.*\}", text, re.DOTALL)
    if not match:
        return None
    try:
        return json.loads(match.group(0))
    except json.JSONDecodeError:
        return None


def normalize_row(row):
    if len(row) == 1 and "\t" in row[0]:
        return row[0].split("\t")
    return row


def iter_message_rows(path):
    with open(path, newline="", encoding="utf-8") as f:
        sample = f.read(2048)
        f.seek(0)
        try:
            dialect = csv.Sniffer().sniff(sample)
        except csv.Error:
            dialect = csv.excel
        reader = csv.reader(f, dialect=dialect)
        header = None
        for row in reader:
            if not row:
                continue
            row = normalize_row(row)
            if not row:
                continue
            if header is None:
                lower = [cell.strip().lower() for cell in row]
                if "conversation_id" in lower and "body" in lower:
                    header = lower
                    continue
            if header:
                data = {header[i]: row[i] if i < len(row) else "" for i in range(len(header))}
                yield data
            else:
                if len(row) < 7:
                    continue
                yield {
                    "id": row[0],
                    "conversation_id": row[1],
                    "sendable_id": row[2],
                    "sendable_type": row[3],
                    "body": row[4],
                    "type": row[5],
                    "created_at": row[6],
                }


def read_conversations(path, sendable_type, min_messages):
    conversations = defaultdict(list)
    for row in iter_message_rows(path):
        body = (row.get("body") or "").strip()
        if not body:
            continue
        message_type = (row.get("type") or "").strip().lower()
        if message_type and message_type != "text":
            continue
        if sendable_type and (row.get("sendable_type") or "").strip() != sendable_type:
            continue
        conversation_id = (row.get("conversation_id") or "").strip()
        if not conversation_id:
            continue
        conversations[conversation_id].append(body)

    filtered = {}
    for conversation_id, bodies in conversations.items():
        if len(bodies) >= min_messages:
            filtered[conversation_id] = bodies
    return filtered


def sort_conversation_ids(conversation_ids):
    def sort_key(value):
        try:
            return int(value)
        except ValueError:
            return value

    return sorted(conversation_ids, key=sort_key)


def canonicalize_normalized(normalized):
    if not normalized:
        return ""
    if re.search(r"\b(cop|pesos colombianos)\b", normalized):
        return "cual es el precio en cop"
    if re.search(r"\b(precio|precios|cuanto cuesta|cuanto vale|cuanto valen|costo|costos)\b", normalized):
        if re.search(r"\b(maquina|maquinas)\b", normalized):
            return "cual es el precio de la maquina"
        return "cual es el precio de la maquina"
    if re.search(r"\b(oferta|ofertas|descuento|promocion)\b", normalized):
        return "tienen ofertas"
    if re.search(r"\b(donde|ubicacion|direccion|empresa|estan|encuentran)\b", normalized):
        return "donde estan ubicados"
    if re.search(r"\b(video|funcionamiento)\b", normalized):
        return "tienen video del funcionamiento"
    if re.search(r"\b(foto|fotos|imagenes|imagen)\b", normalized):
        return "tienen fotos o videos"
    if re.search(r"\b(ficha tecnica|especificaciones|caracteristicas)\b", normalized):
        return "pueden enviar la ficha tecnica"
    if re.search(r"\b(cotiz|presupuesto)\b", normalized):
        return "como puedo cotizar una maquina"
    if re.search(r"\b(comprar|adquirir|proceso de compra|retomar el proceso de compra)\b", normalized):
        return "como puedo comprar una maquina"
    if re.search(r"\b(pago|pagos|formas de pago|metodos de pago|medios de pago)\b", normalized):
        return "cuales son las formas de pago"
    if re.search(r"\b(funciones|funcionalidades|que hace)\b", normalized):
        return "que funciones tiene la maquina"
    if re.search(r"\b(mas mecanizada|mas automatica|mas productiva)\b", normalized):
        return "tienen una maquina mas mecanizada"
    if re.search(r"\b(capacidad|produccion|por hora|por minuto|empanadas por hora|cuantas empanadas)\b", normalized):
        return "cual es la capacidad de produccion"
    if re.search(r"\b(molde|moldes|kit arepa|arepa)\b", normalized):
        return "tienen moldes o kits"
    if re.search(r"\b(garantia)\b", normalized):
        return "tiene garantia"
    if re.search(r"\b(repuesto|repuestos|soporte tecnico|mantenimiento)\b", normalized):
        return "tienen repuestos y soporte tecnico"
    if re.search(r"\b(voltaje|110v|220v|energia|corriente)\b", normalized):
        return "que voltaje requiere la maquina"
    if re.search(r"\b(dimensiones|medidas|tamano|tamaño|peso|diametro|radio|circunferencia|grosor)\b", normalized):
        return "que dimensiones tiene la maquina"
    if re.search(r"\b(material|acero inoxidable|inoxidable)\b", normalized):
        return "de que material esta hecha la maquina"
    if re.search(r"\b(envio|enviar|entrega|despacho|flete|shipping)\b", normalized):
        return "como es el envio"
    if re.search(r"\b(cuanto se demora|tiempo demora|en cuanto tiempo llegaria|cuando llega)\b", normalized):
        return "cuanto se demora en llegar"
    if re.search(r"\b(instalacion|puesta en marcha|capacitacion|entrenamiento)\b", normalized):
        return "incluye instalacion o capacitacion"
    if re.search(r"\b(modelo|modelos|referencia|ref|que maquinas|maquinas tienen|que opciones|que tipos)\b", normalized):
        return "que modelos tienen"
    if re.search(r"\b(pelapapas|pela papas|pelar papas)\b", normalized):
        return "tienen pelapapas"
    if re.search(r"\b(laminadora)\b", normalized):
        return "tienen laminadora de trigo"
    if re.search(r"\b(disponible|disponibilidad)\b", normalized):
        return "hay disponibilidad"
    if re.search(r"\b(demo|demostracion|cita|agenda|exhibicion)\b", normalized):
        return "como agendar una demo"
    if re.search(r"\b(credito|financiamiento|financiacion|facilidades de pago)\b", normalized):
        return "tienen opciones de financiamiento"
    return normalized


def normalize_question(text):
    if not text:
        return ""
    text = text.strip()
    text = text.lstrip("¿").rstrip("?")
    text = re.sub(r"[\"'`]", "", text)
    text = re.sub(r"\s+", " ", text)
    text = text.lower()
    text = unicodedata.normalize("NFKD", text)
    text = "".join(ch for ch in text if not unicodedata.combining(ch))
    return canonicalize_normalized(text)


def pre_classify_question(text):
    normalized = normalize_question(text)
    if not normalized:
        return ""

    rules = [
        (r"\b(precio|valor|costo|cuanto vale|cuanto cuesta)\b", "cual es el precio de la maquina"),
        (r"\b(pesos colombianos|cop|colombian pesos)\b", "cual es el precio en cop"),
        (r"\b(cotiza|cotizar|cotizacion|presupuesto)\b", "como puedo cotizar una maquina"),
        (r"\b(donde estan|ubicacion|direccion|de donde son|donde se encuentran)\b", "donde estan ubicados"),
        (r"\b(video|funcionamiento|ver la maquina)\b", "tienen video del funcionamiento"),
        (r"\b(oferta|descuento|promocion)\b", "tienen ofertas"),
        (r"\b(mas mecanizada|mas automatica|mas productiva)\b", "tienen una maquina mas mecanizada"),
        (r"\b(funciones|funcionalidades|que hace)\b", "que funciones tiene la maquina"),
        (r"\b(ficha tecnica|ficha|especificaciones|caracteristicas)\b", "pueden enviar la ficha tecnica"),
        (r"\b(envio|enviar|entrega|despacho|flete|shipping)\b", "como es el envio"),
        (r"\b(garantia|garant[a]?|asegurar)\b", "tiene garantia"),
        (r"\b(repuesto|repuestos|partes|mantenimiento|soporte tecnico)\b", "tienen repuestos y soporte tecnico"),
        (r"\b(instalacion|instalar|puesta en marcha|capacitacion|entrenamiento)\b", "incluye instalacion o capacitacion"),
        (r"\b(modelo|modelos|referencia|ref\.)\b", "que modelos tienen"),
        (r"\b(voltaje|110v|220v|energia|corriente)\b", "que voltaje requiere la maquina"),
        (r"\b(material|acero inoxidable|inoxidable)\b", "de que material esta hecha la maquina"),
        (r"\b(dimensiones|medidas|tamano|tamaño|peso)\b", "que dimensiones tiene la maquina"),
        (r"\b(capacidad|produccion|cuantas por hora|empanadas por hora)\b", "cual es la capacidad de produccion"),
        (r"\b(pelapapas|pela papas|pelar papas)\b", "tienen pelapapas"),
        (r"\b(laminadora|laminadora de trigo|laminadora con variador|laminadora pizza|fondan)\b", "tienen laminadora de trigo"),
        (r"\b(molde|moldes|kit arepa|arepa rellena|arepa tela)\b", "tienen moldes o kits"),
    ]

    for pattern, canonical in rules:
        if re.search(pattern, normalized, re.IGNORECASE):
            return canonical
    return ""


def classify_question(clean_body, api_key, model, timeout, cache):
    if clean_body in cache:
        return cache[clean_body]

    pre_question = pre_classify_question(clean_body)
    if pre_question:
        cache[clean_body] = pre_question
        return pre_question

    prompt = (
        "Eres un clasificador para generar FAQs. Recibes solo un mensaje "
        "del usuario. Decide si es una pregunta tipica sobre maquinas de "
        "empanadas, precios, envio, ubicacion, modelos, tiempos o compras. "
        "Si es pregunta, devuelve una version canonica corta en espanol. "
        "Si no es pregunta o es ruido, devuelve faq_question vacio.\n\n"
        "Responde SOLO JSON con claves: faq_question (string) y "
        "is_question (true/false).\n\n"
        f"Mensaje: {clean_body}"
    )
    response = openai_chat_completion(
        api_key,
        model,
        [{"role": "user", "content": prompt}],
        timeout,
    )
    content = response["choices"][0]["message"]["content"]
    parsed = extract_json(content) or {}
    raw_question = (parsed.get("faq_question") or "").strip()
    is_question = parsed.get("is_question", False)
    if isinstance(is_question, str):
        is_question = is_question.strip().lower() in {"true", "1", "yes", "si"}
    if not is_question or not raw_question:
        faq_question = ""
    else:
        faq_question = raw_question

    cache[clean_body] = faq_question
    return faq_question


def read_existing_faqs(path):
    if not path or not os.path.exists(path):
        return {}, {}
    existing_counts = {}
    existing_questions = {}
    with open(path, newline="", encoding="utf-8") as f:
        sample = f.read(2048)
        f.seek(0)
        try:
            dialect = csv.Sniffer().sniff(sample)
        except csv.Error:
            dialect = csv.excel
        reader = csv.DictReader(f, dialect=dialect)
        if not reader.fieldnames or "faq_question" not in reader.fieldnames:
            return {}, {}
        for row in reader:
            question = (row.get("faq_question") or "").strip()
            if not question:
                continue
            normalized = normalize_question(question)
            if not normalized:
                continue
            count = row.get("count")
            try:
                count_value = int(count) if count is not None and str(count).strip() else 0
            except ValueError:
                count_value = 0
            existing_counts[normalized] = existing_counts.get(normalized, 0) + count_value
            if normalized not in existing_questions:
                existing_questions[normalized] = normalized
    return existing_counts, existing_questions


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--messages", default=".ia/faq/conversations.csv")
    parser.add_argument("--output", default=".ia/faq/faqs.csv")
    parser.add_argument("--model", default="gpt-4o-mini")
    parser.add_argument("--min-messages", type=int, default=2)
    parser.add_argument("--min-length", type=int, default=6)
    parser.add_argument("--sendable-type", default="App\\Models\\Customer")
    parser.add_argument("--limit-conversations", type=int, default=0)
    parser.add_argument("--existing-faqs", default=".ia/faq/faqs.csv")
    parser.add_argument("--sleep", type=float, default=0.2)
    parser.add_argument("--timeout", type=int, default=30)
    parser.add_argument("--max-examples", type=int, default=0)
    parser.add_argument("--progress-every", type=int, default=25)
    parser.add_argument("--flush-every", type=int, default=100)
    args = parser.parse_args()

    load_env(".env")
    api_key = os.environ.get("OPENAI_API_KEY", "").strip()
    if not api_key:
        raise SystemExit("Missing OPENAI_API_KEY in .env")

    conversations = read_conversations(
        args.messages, args.sendable_type, args.min_messages
    )
    if args.limit_conversations:
        limited = {}
        for conversation_id in sort_conversation_ids(conversations.keys())[
            : args.limit_conversations
        ]:
            limited[conversation_id] = conversations[conversation_id]
        conversations = limited
    counts, display_questions = read_existing_faqs(args.existing_faqs)
    counts = defaultdict(int, counts)
    existing_normalized = set(counts.keys())
    cache = {}
    processed_messages = 0

    def write_output():
        rows = [
            {"faq_question": display_questions.get(key, key), "count": counts[key]}
            for key in sorted(counts.keys(), key=lambda k: counts[k], reverse=True)
        ]
        with open(args.output, "w", newline="", encoding="utf-8") as f:
            writer = csv.DictWriter(f, fieldnames=["faq_question", "count"])
            writer.writeheader()
            writer.writerows(rows)
        return len(rows)

    for bodies in conversations.values():
        seen_in_conversation = set()
        for body in bodies:
            clean_body = body.strip()
            if len(clean_body) < args.min_length:
                continue
            processed_messages += 1
            if args.progress_every and processed_messages % args.progress_every == 0:
                print(f"Processed {processed_messages} mensajes...")
            if args.flush_every and processed_messages % args.flush_every == 0:
                write_output()
            faq_question = classify_question(
                clean_body, api_key, args.model, args.timeout, cache
            )

            normalized = normalize_question(faq_question)
            if not normalized or normalized in seen_in_conversation:
                continue
            seen_in_conversation.add(normalized)
            if normalized not in existing_normalized:
                print(f"Nueva pregunta: {normalized}")
                existing_normalized.add(normalized)
            counts[normalized] += 1
            if normalized not in display_questions:
                display_questions[normalized] = normalized

            if args.sleep:
                time.sleep(args.sleep)

    total_rows = write_output()

    print(f"Wrote {total_rows} rows to {args.output}")


if __name__ == "__main__":
    main()
