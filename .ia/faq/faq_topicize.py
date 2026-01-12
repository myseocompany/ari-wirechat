import argparse
import csv
import os
import time

from classify_faqs import extract_json, load_env, openai_chat_completion

TOPICS = [
    "Precio/Cotizacion",
    "Ubicacion/Contacto",
    "FichaTecnica/Info",
    "Video/Demo",
    "Modelos/Recomendacion",
    "Produccion/Capacidad",
    "Envio/Tiempos",
    "Especificaciones/Dimensiones",
    "Accesorios/Compatibilidad",
    "Postventa/Garantia",
    "Compra/Pago/Oferta",
    "Disponibilidad/Stock",
    "Otros",
]


def read_faqs(path: str) -> list[dict]:
    rows = []
    with open(path, newline="", encoding="utf-8") as f:
        reader = csv.DictReader(f)
        if not reader.fieldnames or "faq_question" not in reader.fieldnames:
            raise SystemExit("Missing faq_question column")
        for row in reader:
            question = (row.get("faq_question") or "").strip()
            if not question:
                continue
            count_raw = row.get("count", "")
            try:
                count = int(count_raw) if str(count_raw).strip() else 0
            except ValueError:
                count = 0
            rows.append({"faq_question": question, "count": count})
    return rows


def build_prompt(question: str) -> str:
    topics_text = "\n".join([f"- {topic}" for topic in TOPICS])
    prompt = (
        "Clasifica la pregunta en UN solo tema macro de la lista.\n"
        "Si no encaja claro, usa 'Otros'.\n"
        "Responde SOLO JSON con clave: topic (string).\n\n"
        "Lista de temas:\n"
        f"{topics_text}\n\n"
        f"Pregunta: {question}"
    )
    return prompt


def classify_topic(
    question: str,
    api_key: str,
    model: str,
    timeout: int,
) -> str:
    prompt = build_prompt(question)
    response = openai_chat_completion(
        api_key,
        model,
        [{"role": "user", "content": prompt}],
        timeout,
    )
    content = response["choices"][0]["message"]["content"]
    parsed = extract_json(content) or {}
    topic = (parsed.get("topic") or "").strip()
    if topic not in TOPICS:
        return "Otros"
    return topic


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--input", default=".ia/faq/faq_min.csv")
    parser.add_argument("--output", default=".ia/faq/faq_min_topics.csv")
    parser.add_argument("--model", default="gpt-4o-mini")
    parser.add_argument("--sleep", type=float, default=0.1)
    parser.add_argument("--timeout", type=int, default=30)
    parser.add_argument("--progress-every", type=int, default=25)
    args = parser.parse_args()

    load_env(".env")
    api_key = os.environ.get("OPENAI_API_KEY", "").strip()
    if not api_key:
        raise SystemExit("Missing OPENAI_API_KEY in .env")

    rows = read_faqs(args.input)
    cache: dict[str, str] = {}
    output_rows = []

    total = len(rows)
    for index, row in enumerate(rows, start=1):
        question = row["faq_question"]
        if question in cache:
            topic = cache[question]
        else:
            topic = classify_topic(question, api_key, args.model, args.timeout)
            cache[question] = topic
            if args.sleep:
                time.sleep(args.sleep)
        output_rows.append(
            {
                "faq_question": question,
                "count": row["count"],
                "topic": topic,
            }
        )
        if args.progress_every and index % args.progress_every == 0:
            print(f"Procesadas {index}/{total} preguntas...")

    with open(args.output, "w", newline="", encoding="utf-8") as f:
        writer = csv.DictWriter(f, fieldnames=["faq_question", "count", "topic"])
        writer.writeheader()
        writer.writerows(output_rows)

    print(f"Wrote {len(output_rows)} rows to {args.output}")


if __name__ == "__main__":
    main()
