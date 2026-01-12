import argparse
import csv
import os
import time

from classify_faqs import extract_json, load_env, openai_chat_completion

STAGES = ["TOFU", "MOFU", "BOFU"]


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
    prompt = (
        "Clasifica la pregunta en una etapa del embudo B2B.\n"
        "TOFU = info general/descubrimiento.\n"
        "MOFU = comparacion/diagnostico/especificaciones.\n"
        "BOFU = compra/cotizacion/pago/tiempos/garantia.\n"
        "Responde SOLO JSON con key: stage (string) usando TOFU/MOFU/BOFU.\n\n"
        f"Pregunta: {question}"
    )
    return prompt


def classify_stage(
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
    stage = (parsed.get("stage") or "").strip().upper()
    if stage not in STAGES:
        return "MOFU"
    return stage


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--input", default=".ia/faq/faq_min_clean.csv")
    parser.add_argument("--output", default=".ia/faq/faq_funnel.csv")
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
            stage = cache[question]
        else:
            stage = classify_stage(question, api_key, args.model, args.timeout)
            cache[question] = stage
            if args.sleep:
                time.sleep(args.sleep)
        output_rows.append(
            {
                "faq_question": question,
                "count": row["count"],
                "stage": stage,
            }
        )
        if args.progress_every and index % args.progress_every == 0:
            print(f"Procesadas {index}/{total} preguntas...")

    with open(args.output, "w", newline="", encoding="utf-8") as f:
        writer = csv.DictWriter(f, fieldnames=["faq_question", "count", "stage"])
        writer.writeheader()
        writer.writerows(output_rows)

    print(f"Wrote {len(output_rows)} rows to {args.output}")


if __name__ == "__main__":
    main()
