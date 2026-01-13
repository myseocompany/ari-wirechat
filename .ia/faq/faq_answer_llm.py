import argparse
import csv
import os
import time

from classify_faqs import load_env, openai_chat_completion


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


def load_prompt(path: str) -> str:
    with open(path, encoding="utf-8") as f:
        return f.read().strip()


def build_messages(prompt_text: str, question: str) -> list[dict]:
    system = (
        f"{prompt_text}\n\n"
        "Reglas adicionales:\n"
        "- Responde en espanol y en tono cercano.\n"
        "- Respuesta breve y util.\n"
        "- Termina siempre con una pregunta.\n"
        "- Si incluyes URLs, solo texto plano (sin Markdown).\n"
        "- No muestres estados internos ni scoring.\n"
    )
    return [
        {"role": "system", "content": system},
        {"role": "user", "content": question},
    ]


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--input", default=".ia/faq/faq_min_clean.csv")
    parser.add_argument("--output", default=".ia/faq/faq_answers.md")
    parser.add_argument("--prompt", default=".ia/prompt_sellerchat.md")
    parser.add_argument("--model", default="gpt-4o-mini")
    parser.add_argument("--sleep", type=float, default=0.2)
    parser.add_argument("--timeout", type=int, default=30)
    parser.add_argument("--progress-every", type=int, default=10)
    args = parser.parse_args()

    load_env(".env")
    api_key = os.environ.get("OPENAI_API_KEY", "").strip()
    if not api_key:
        raise SystemExit("Missing OPENAI_API_KEY in .env")

    prompt_text = load_prompt(args.prompt)
    rows = read_faqs(args.input)
    rows.sort(key=lambda row: row["count"], reverse=True)

    lines = []
    total = len(rows)
    for index, row in enumerate(rows, start=1):
        question = row["faq_question"]
        messages = build_messages(prompt_text, question)
        response = openai_chat_completion(
            api_key,
            args.model,
            messages,
            args.timeout,
        )
        answer = response["choices"][0]["message"]["content"].strip()
        lines.append(f"## {question}")
        lines.append("")
        lines.append(answer)
        lines.append("")
        if args.progress_every and index % args.progress_every == 0:
            print(f"Procesadas {index}/{total} preguntas...")
        if args.sleep:
            time.sleep(args.sleep)

    with open(args.output, "w", encoding="utf-8") as f:
        f.write("\n".join(lines).rstrip() + "\n")

    print(f"Wrote {len(rows)} answers to {args.output}")


if __name__ == "__main__":
    main()
