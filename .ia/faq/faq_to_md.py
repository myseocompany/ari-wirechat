import argparse
import csv


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


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--input", default=".ia/faq/faq_min_clean.csv")
    parser.add_argument("--output", default=".ia/faq/faq_answers.md")
    args = parser.parse_args()

    rows = read_faqs(args.input)
    rows.sort(key=lambda row: row["count"], reverse=True)

    lines = []
    for row in rows:
        question = row["faq_question"]
        lines.append(f"## {question}")
        lines.append("")
        lines.append("")

    with open(args.output, "w", encoding="utf-8") as f:
        f.write("\n".join(lines).rstrip() + "\n")

    print(f"Wrote {len(rows)} questions to {args.output}")


if __name__ == "__main__":
    main()
