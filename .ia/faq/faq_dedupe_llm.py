import argparse
import csv
import json
import os
import re
import time
import unicodedata
from dataclasses import dataclass, field
from typing import Dict, List, Optional

from classify_faqs import extract_json, load_env, openai_chat_completion


def read_faqs(path: str) -> List[dict]:
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


def basic_normalize(text: str) -> str:
    if not text:
        return ""
    text = text.strip().lstrip("¿").rstrip("?")
    text = re.sub(r"[\"'`]", "", text)
    text = re.sub(r"\s+", " ", text)
    text = text.lower()
    text = unicodedata.normalize("NFKD", text)
    return "".join(ch for ch in text if not unicodedata.combining(ch))


def clean_canonical(text: str) -> str:
    if not text:
        return ""
    text = text.strip().strip("¿?").strip()
    return re.sub(r"\s+", " ", text)


def pick_fallback_question(variants: List[str]) -> str:
    if not variants:
        return ""
    return sorted(variants, key=lambda value: len(value), reverse=True)[0]


@dataclass
class CanonicalEntry:
    entry_id: str
    question: str
    count: int = 0
    variants: List[str] = field(default_factory=list)


def build_prompt(candidate: str, variants: List[str], canonicals: List[CanonicalEntry]) -> str:
    canonical_lines = "\n".join(
        [f"{entry.entry_id}: {entry.question}" for entry in canonicals]
    )
    variant_text = " | ".join(variants[:5]) if variants else ""
    prompt = (
        "Eres un asistente que deduplica FAQs en espanol.\n"
        "Tarea: decide si la pregunta candidata tiene el mismo significado que "
        "alguna de las preguntas canonicas listadas. Si coincide, devuelve el "
        "ID correspondiente en match_id. Si no coincide, deja match_id vacio.\n"
        "Siempre devuelve canonical_question con una version clara y corta de la "
        "pregunta (en espanol). Si coincide con una canonica existente, puedes "
        "mejorar la redaccion.\n\n"
        "Responde SOLO JSON con keys: match_id (string), canonical_question (string).\n\n"
        f"Pregunta candidata: {candidate}\n"
        f"Variantes: {variant_text}\n\n"
        "Preguntas canonicas:\n"
        f"{canonical_lines}\n"
    )
    return prompt


def classify_question(
    candidate: str,
    variants: List[str],
    canonicals: List[CanonicalEntry],
    api_key: str,
    model: str,
    timeout: int,
) -> dict:
    prompt = build_prompt(candidate, variants, canonicals)
    response = openai_chat_completion(
        api_key,
        model,
        [{"role": "user", "content": prompt}],
        timeout,
    )
    content = response["choices"][0]["message"]["content"]
    parsed = extract_json(content) or {}
    match_id = (parsed.get("match_id") or "").strip()
    canonical_question = clean_canonical(parsed.get("canonical_question") or "")
    return {"match_id": match_id, "canonical_question": canonical_question}


def build_match_prompt(candidate: str, canonicals: List[CanonicalEntry]) -> str:
    canonical_lines = "\n".join(
        [f"{entry.entry_id}: {entry.question}" for entry in canonicals]
    )
    prompt = (
        "Eres un asistente que deduplica FAQs en espanol.\n"
        "Tarea: decide si la pregunta candidata tiene el mismo significado que "
        "alguna de las preguntas canonicas listadas. Si coincide, devuelve el "
        "ID correspondiente en match_id. Si no coincide, deja match_id vacio.\n"
        "Responde SOLO JSON con keys: match_id (string).\n\n"
        f"Pregunta candidata: {candidate}\n\n"
        "Preguntas canonicas:\n"
        f"{canonical_lines}\n"
    )
    return prompt


def match_canonical(
    candidate: str,
    canonicals: List[CanonicalEntry],
    api_key: str,
    model: str,
    timeout: int,
) -> str:
    prompt = build_match_prompt(candidate, canonicals)
    response = openai_chat_completion(
        api_key,
        model,
        [{"role": "user", "content": prompt}],
        timeout,
    )
    content = response["choices"][0]["message"]["content"]
    parsed = extract_json(content) or {}
    return (parsed.get("match_id") or "").strip()


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--input", default=".ia/faq/faqs.csv")
    parser.add_argument("--output", default=".ia/faq/faq_min_llm.csv")
    parser.add_argument("--model", default="gpt-4o-mini")
    parser.add_argument("--sleep", type=float, default=0.2)
    parser.add_argument("--timeout", type=int, default=30)
    parser.add_argument("--max-candidates", type=int, default=0)
    parser.add_argument("--progress-every", type=int, default=10)
    args = parser.parse_args()

    load_env(".env")
    api_key = os.environ.get("OPENAI_API_KEY", "").strip()
    if not api_key:
        raise SystemExit("Missing OPENAI_API_KEY in .env")

    rows = read_faqs(args.input)
    grouped: Dict[str, dict] = {}
    for row in rows:
        question = row["faq_question"]
        normalized = basic_normalize(question)
        if not normalized:
            continue
        entry = grouped.setdefault(
            normalized, {"count": 0, "questions": [], "normalized": normalized}
        )
        entry["count"] += row["count"]
        if question not in entry["questions"]:
            entry["questions"].append(question)

    groups = sorted(grouped.values(), key=lambda item: item["count"], reverse=True)
    canonicals: List[CanonicalEntry] = []
    canonical_by_id: Dict[str, CanonicalEntry] = {}
    canonical_by_norm: Dict[str, CanonicalEntry] = {}
    cache: Dict[str, dict] = {}
    match_cache: Dict[str, str] = {}
    next_id = 1

    def add_canonical(question: str, count: int, variants: List[str]) -> None:
        nonlocal next_id
        entry_id = str(next_id)
        next_id += 1
        entry = CanonicalEntry(
            entry_id=entry_id, question=question, count=count, variants=list(variants)
        )
        canonicals.append(entry)
        canonical_by_id[entry_id] = entry
        normalized = basic_normalize(entry.question)
        if normalized and normalized not in canonical_by_norm:
            canonical_by_norm[normalized] = entry
        print(f"Nueva canonica [{entry_id}]: {entry.question}")

    total_groups = len(groups)
    for index, group in enumerate(groups, start=1):
        variants = group["questions"]
        count = group["count"]
        candidate = pick_fallback_question(variants)
        if not candidate:
            continue
        cache_key = basic_normalize(candidate)
        if cache_key in cache:
            result = cache[cache_key]
        else:
            if args.max_candidates and len(canonicals) > args.max_candidates:
                candidates = sorted(
                    canonicals, key=lambda entry: entry.count, reverse=True
                )[: args.max_candidates]
            else:
                candidates = canonicals
            result = classify_question(
                candidate, variants, candidates, api_key, args.model, args.timeout
            )
            cache[cache_key] = result
            if args.sleep:
                time.sleep(args.sleep)

        match_id = result.get("match_id", "")
        canonical_question = result.get("canonical_question", "")
        normalized_candidate = basic_normalize(candidate)
        existing_by_norm = (
            canonical_by_norm.get(normalized_candidate) if normalized_candidate else None
        )
        if existing_by_norm:
            existing_by_norm.count += count
            existing_by_norm.variants.extend(variants)
            if canonical_question:
                existing_by_norm.question = canonical_question
            print(f"Fusionada en [{existing_by_norm.entry_id}]: {existing_by_norm.question}")
        elif match_id and match_id in canonical_by_id:
            entry = canonical_by_id[match_id]
            entry.count += count
            entry.variants.extend(variants)
            if canonical_question:
                entry.question = canonical_question
            print(f"Fusionada en [{entry.entry_id}]: {entry.question}")
        else:
            new_question = canonical_question or candidate
            add_canonical(new_question, count, variants)
        if args.progress_every and index % args.progress_every == 0:
            print(f"Procesadas {index}/{total_groups} preguntas...")

    deduped: List[CanonicalEntry] = []
    seen_ids = set()
    for entry in canonicals:
        if entry.entry_id not in seen_ids:
            deduped.append(entry)
            seen_ids.add(entry.entry_id)
    canonicals = deduped

    if len(canonicals) > 1:
        print("Iniciando segunda pasada de deduplicacion...")
    merged_ids = set()
    for entry in list(canonicals):
        if entry.entry_id in merged_ids:
            continue
        candidates = [c for c in canonicals if c.entry_id != entry.entry_id]
        if not candidates:
            continue
        cache_key = basic_normalize(entry.question)
        if cache_key in match_cache:
            match_id = match_cache[cache_key]
        else:
            match_id = match_canonical(
                entry.question, candidates, api_key, args.model, args.timeout
            )
            match_cache[cache_key] = match_id
            if args.sleep:
                time.sleep(args.sleep)
        if match_id and match_id in canonical_by_id:
            target = canonical_by_id[match_id]
            if target.entry_id == entry.entry_id:
                continue
            target.count += entry.count
            target.variants.extend(entry.variants)
            merged_ids.add(entry.entry_id)
            print(f"Fusionada en 2da pasada [{target.entry_id}]: {target.question}")

    canonicals = [entry for entry in canonicals if entry.entry_id not in merged_ids]

    output_rows = [
        {"faq_question": entry.question, "count": entry.count}
        for entry in sorted(canonicals, key=lambda entry: entry.count, reverse=True)
    ]

    with open(args.output, "w", newline="", encoding="utf-8") as f:
        writer = csv.DictWriter(f, fieldnames=["faq_question", "count"])
        writer.writeheader()
        writer.writerows(output_rows)

    print(f"Wrote {len(output_rows)} rows to {args.output}")


if __name__ == "__main__":
    main()
