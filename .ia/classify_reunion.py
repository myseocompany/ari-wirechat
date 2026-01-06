import argparse
import csv
import json
import os
import re
import time
import urllib.request
from collections import defaultdict


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


def read_sendable_ids(path):
    sendable_ids = []
    with open(path, newline="", encoding="utf-8") as f:
        reader = csv.reader(f)
        for row in reader:
            if not row:
                continue
            if row[0].strip().lower() == "sendable_id":
                continue
            sendable_ids.append(row[0].strip())
    return sendable_ids


def read_gold_ids(path):
    if not path:
        return []
    return read_sendable_ids(path)


def read_messages(path):
    messages_by_id = defaultdict(list)
    with open(path, newline="", encoding="utf-8") as f:
        sample = f.read(2048)
        f.seek(0)
        try:
            dialect = csv.Sniffer().sniff(sample)
        except csv.Error:
            dialect = csv.excel
        reader = csv.DictReader(f, dialect=dialect)
        for row in reader:
            sendable_id = (row.get("sendable_id") or "").strip()
            body = (row.get("body") or "").strip()
            if not sendable_id:
                continue
            messages_by_id[sendable_id].append(body)
    return messages_by_id


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


def detect_date_time(text):
    months = [
        "enero",
        "febrero",
        "marzo",
        "abril",
        "mayo",
        "junio",
        "julio",
        "agosto",
        "septiembre",
        "setiembre",
        "octubre",
        "noviembre",
        "diciembre",
    ]
    weekdays = [
        "lunes",
        "martes",
        "miercoles",
        "miércoles",
        "jueves",
        "viernes",
        "sabado",
        "sábado",
        "domingo",
    ]
    month_pattern = re.compile(r"\b(?:" + "|".join(months) + r")\b", re.IGNORECASE)
    weekday_pattern = re.compile(r"\b(?:" + "|".join(weekdays) + r")\b", re.IGNORECASE)
    date_patterns = [
        re.compile(
            r"\b\d{1,2}\s*de\s*(?:" + "|".join(months) + r")\b", re.IGNORECASE
        ),
        re.compile(r"\b\d{1,2}[/-]\d{1,2}(?:[/-]\d{2,4})?\b"),
    ]
    time_patterns = [
        re.compile(r"\b\d{1,2}\s*(?:am|pm)\b", re.IGNORECASE),
        re.compile(r"\ba\s*las\s*\d{1,2}\b", re.IGNORECASE),
        re.compile(r"\b\d{1,2}:\d{2}\b"),
    ]
    time_words = ["manana", "mañana", "tarde", "noche"]

    has_date = False
    if month_pattern.search(text):
        has_date = True
    if weekday_pattern.search(text):
        has_date = True
    for pattern in date_patterns:
        if pattern.search(text):
            has_date = True
            break

    has_time = False
    for pattern in time_patterns:
        if pattern.search(text):
            has_time = True
            break
    if not has_time:
        lower_text = text.lower()
        for word in time_words:
            if word in lower_text:
                has_time = True
                break

    return has_date and has_time


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--sendable", default=".ia/sendable_id_2026.csv")
    parser.add_argument("--messages", default=".ia/wire_messages_2026.csv")
    parser.add_argument("--output", default=".ia/sendable_id_2026_ai.csv")
    parser.add_argument("--model", default="gpt-4o-mini")
    parser.add_argument("--only-id", default="")
    parser.add_argument("--gold", default="")
    parser.add_argument("--limit", type=int, default=0)
    parser.add_argument("--sleep", type=float, default=0.2)
    parser.add_argument("--timeout", type=int, default=30)
    args = parser.parse_args()

    load_env(".env")
    api_key = os.environ.get("OPENAI_API_KEY", "").strip()
    if not api_key:
        raise SystemExit("Missing OPENAI_API_KEY in .env")

    sendable_ids = read_sendable_ids(args.sendable)
    gold_ids = read_gold_ids(args.gold)
    if gold_ids:
        sendable_ids = gold_ids
    messages_by_id = read_messages(args.messages)

    rows = []
    total = 0
    matched = 0
    mismatched = 0
    expected_true = 0
    expected_false = 0
    for sendable_id in sendable_ids:
        if args.only_id and sendable_id != args.only_id:
            continue
        if args.limit and total >= args.limit:
            break
        bodies = messages_by_id.get(sendable_id, [])
        conversation = " | ".join([b for b in bodies if b])

        if not conversation:
            normalized_flag = False
            reason = "sin mensajes"
            row = {
                "sendable_id": sendable_id,
                "pidio_reunion": "false",
                "reason": reason,
            }
            if gold_ids:
                expected = True
                row["expected_pidio_reunion"] = "true"
                match = normalized_flag is expected
                row["match"] = "true" if match else "false"
                if match:
                    matched += 1
                else:
                    mismatched += 1
                expected_true += 1
            rows.append(row)
            total += 1
            continue

        prompt = (
            "Eres un clasificador. Solo ves mensajes del usuario (no hay respuestas "
            "del bot). Determina si el usuario pidio reunion/cita/demo/llamada o "
            "si envio fecha y/o hora para agendar. Usa el contexto de feria "
            "(CUPOS) y expresiones como '8 de enero', 'a las 4', 'por la tarde', "
            "'miercoles', 'jueves'. Regla: si hay fecha + hora, responde true "
            "aunque no diga 'agendar'. Responde SOLO JSON con claves: "
            "pidio_reunion (true/false) y reason (breve).\n\n"
            "Ejemplos:\n"
            "Conversacion: CUPOS | El 8 por la tarde | A las 4 el 8 de enero\n"
            "Respuesta: {\"pidio_reunion\": true, \"reason\": \"dio fecha y hora\"}\n"
            "Conversacion: Quiero info | Cuanto vale\n"
            "Respuesta: {\"pidio_reunion\": false, \"reason\": \"no agenda\"}\n"
            "Conversacion: Podemos agendar una llamada\n"
            "Respuesta: {\"pidio_reunion\": true, \"reason\": \"pidio llamada\"}\n\n"
            "Conversacion:\n"
            f"{conversation}"
        )

        response = openai_chat_completion(
            api_key,
            args.model,
            [{"role": "user", "content": prompt}],
            args.timeout,
        )
        content = response["choices"][0]["message"]["content"]
        parsed = extract_json(content) or {}
        raw_flag = parsed.get("pidio_reunion", False)
        if isinstance(raw_flag, str):
            normalized_flag = raw_flag.strip().lower() in {"true", "1", "yes", "si"}
        else:
            normalized_flag = bool(raw_flag)

        has_date_time = detect_date_time(conversation)
        if has_date_time and not normalized_flag:
            normalized_flag = True
            parsed["reason"] = "override fecha y hora detectadas"

        parsed["pidio_reunion"] = normalized_flag

        row = {
            "sendable_id": sendable_id,
            "pidio_reunion": str(parsed.get("pidio_reunion", False)).lower(),
            "reason": (parsed.get("reason") or "").strip(),
        }

        if gold_ids:
            expected = True
            row["expected_pidio_reunion"] = "true"
            match = normalized_flag is expected
            row["match"] = "true" if match else "false"
            if match:
                matched += 1
            else:
                mismatched += 1
            expected_true += 1

        rows.append(row)
        total += 1
        if args.sleep:
            time.sleep(args.sleep)

    with open(args.output, "w", newline="", encoding="utf-8") as f:
        fieldnames = ["sendable_id", "pidio_reunion", "reason"]
        if gold_ids:
            fieldnames += ["expected_pidio_reunion", "match"]
        writer = csv.DictWriter(f, fieldnames=fieldnames)
        writer.writeheader()
        writer.writerows(rows)

    print(f"Wrote {len(rows)} rows to {args.output}")
    if gold_ids:
        print(
            "Gold summary: matched="
            f"{matched} mismatched={mismatched} expected_true={expected_true} "
            f"expected_false={expected_false}"
        )


if __name__ == "__main__":
    main()
