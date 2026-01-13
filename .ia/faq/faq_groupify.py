import argparse
import csv
import os
import time

from classify_faqs import extract_json, load_env, openai_chat_completion

GROUPS = {
    "Calificado": [
        "Necesidad",
        "Presupuesto/Precio",
        "Caso de uso",
        "Urgencia/Plazo",
        "Autoridad/Decisor",
        "Otros",
    ],
    "Diagnostico": [
        "Capacidad/Produccion",
        "Producto/Modelo",
        "Requisitos tecnicos",
        "Dimensiones/Instalacion",
        "Compatibilidad/Materiales",
        "Otros",
    ],
    "Otros": ["Otros"],
}


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
    groups_text = "\n".join(
        [
            f"- {group}: {', '.join(subgroups)}"
            for group, subgroups in GROUPS.items()
        ]
    )
    prompt = (
        "Clasifica la pregunta en un Grupo y un Subgrupo.\n"
        "Usa solo los valores listados. Si no encaja claro, usa Otros.\n"
        "Responde SOLO JSON con keys: group (string), subgroup (string).\n\n"
        "Grupos y subgrupos:\n"
        f"{groups_text}\n\n"
        f"Pregunta: {question}"
    )
    return prompt


def classify_group(
    question: str,
    api_key: str,
    model: str,
    timeout: int,
) -> dict:
    prompt = build_prompt(question)
    response = openai_chat_completion(
        api_key,
        model,
        [{"role": "user", "content": prompt}],
        timeout,
    )
    content = response["choices"][0]["message"]["content"]
    parsed = extract_json(content) or {}
    group = (parsed.get("group") or "").strip()
    subgroup = (parsed.get("subgroup") or "").strip()
    if group not in GROUPS:
        return {"group": "Otros", "subgroup": "Otros"}
    if subgroup not in GROUPS[group]:
        return {"group": group, "subgroup": "Otros"}
    return {"group": group, "subgroup": subgroup}


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--input", default=".ia/faq/faq_min_clean.csv")
    parser.add_argument("--output", default=".ia/faq/faq_grouped.csv")
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
    cache: dict[str, dict] = {}
    output_rows = []

    total = len(rows)
    for index, row in enumerate(rows, start=1):
        question = row["faq_question"]
        if question in cache:
            result = cache[question]
        else:
            result = classify_group(question, api_key, args.model, args.timeout)
            cache[question] = result
            if args.sleep:
                time.sleep(args.sleep)
        output_rows.append(
            {
                "faq_question": question,
                "count": row["count"],
                "group": result["group"],
                "subgroup": result["subgroup"],
            }
        )
        if args.progress_every and index % args.progress_every == 0:
            print(f"Procesadas {index}/{total} preguntas...")

    with open(args.output, "w", newline="", encoding="utf-8") as f:
        writer = csv.DictWriter(
            f, fieldnames=["faq_question", "count", "group", "subgroup"]
        )
        writer.writeheader()
        writer.writerows(output_rows)

    print(f"Wrote {len(output_rows)} rows to {args.output}")


if __name__ == "__main__":
    main()
