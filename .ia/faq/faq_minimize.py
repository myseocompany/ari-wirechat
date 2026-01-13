import argparse
import csv
import os
from collections import defaultdict

from classify_faqs import normalize_question


def read_faqs(path):
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
            rows.append((question, count))
    return rows


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--input", default=".ia/faq/faqs.csv")
    parser.add_argument("--output", default=".ia/faq/faq_min.csv")
    args = parser.parse_args()

    rows = read_faqs(args.input)
    merged_counts = defaultdict(int)
    canonical_question = {}

    for question, count in rows:
        normalized = normalize_question(question)
        if not normalized:
            continue
        merged_counts[normalized] += count
        if normalized not in canonical_question:
            canonical_question[normalized] = normalized

    output_rows = [
        {"faq_question": canonical_question[key], "count": merged_counts[key]}
        for key in sorted(merged_counts.keys(), key=lambda k: merged_counts[k], reverse=True)
    ]

    with open(args.output, "w", newline="", encoding="utf-8") as f:
        writer = csv.DictWriter(f, fieldnames=["faq_question", "count"])
        writer.writeheader()
        writer.writerows(output_rows)

    print(f"Wrote {len(output_rows)} rows to {args.output}")


if __name__ == "__main__":
    main()
