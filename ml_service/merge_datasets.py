"""
merge_datasets.py

Merges the three datasets used by the ml_service classifier
(GROUP-G-11 / Software Discussion Forum) into one unified training file,
and adds a normalized `category` column that groups all three sources
into the same set of broad topic buckets.

Source files (edit paths below to match your project):
  1. datasets/computerscience/intents.json
       -> {"intents": [{"tag": str, "patterns": [str,...], "responses": [str,...]}, ...]}
  2. datasets/programming/ProgrammingDataset.c   (actually a CSV despite the .c extension)
       -> ID,Language,Title,Description,Code,Snippet Type,Difficulty
  3. datasets/software engineering/Software Questions.csv
       -> Question Number,Question,Answer,Category,Difficulty

Output schema (one row per training example):
  text        -> input text the classifier will see (question/pattern/title)
  response    -> answer/response/code associated with that text
  label       -> original topic/category label from the source file
  category    -> normalized broad bucket (see BUCKETS below)
  difficulty  -> Beginner/Intermediate/Advanced or Easy/Medium/Hard (blank if unknown)
  source      -> "intents" | "programming" | "software_qa"

Usage (from your forumG project root, in Termux):
    python merge_datasets.py

Requires only the standard library — no pandas needed, so it runs fine on Termux.
"""

import json
import csv
import os
import re

# ---- EDIT THESE PATHS TO MATCH YOUR PROJECT ----
INTENTS_PATH = "datasets/computerscience/intents.json"
PROGRAMMING_PATH = "datasets/programming/ProgrammingDataset.csv"
SOFTWARE_QA_PATH = "datasets/software engineering/Software Questions.csv"
OUTPUT_PATH = "datasets/merged_dataset.csv"
# --------------------------------------------------

FIELDS = ["text", "response", "label", "category", "difficulty", "source"]

# Broad buckets every row gets normalized into, regardless of source.
BUCKETS = [
    "oop_concepts",
    "data_structures_algorithms",
    "databases",
    "web_development",
    "software_engineering_process",
    "systems_hardware_os",
    "networking",
    "security",
    "devops_cloud",
    "ai_ml",
    "distributed_systems",
    "theoretical_cs_math",
    "emerging_tech",
    "programming_languages",
    "general_cs",
]

# Exact-match map for the Software Questions.csv "Category" column,
# since those labels are already clean and don't need keyword guessing.
QA_CATEGORY_MAP = {
    "general programming": "general_cs",
    "general program": "general_cs",  # typo variant seen in row 10 of the source file
    "data structures": "data_structures_algorithms",
    "languages and frameworks": "programming_languages",
    "database and sql": "databases",
    "database systems": "databases",
    "web development": "web_development",
    "software testing": "software_engineering_process",
    "version control": "software_engineering_process",
    "system design": "distributed_systems",  # most "design a scalable X" questions are distributed-systems flavored
    "security": "security",
    "devops": "devops_cloud",
    "front-end": "web_development",
    "back-end": "web_development",
    "full-stack": "web_development",
    "machine learning": "ai_ml",
    "artificial intelligence": "ai_ml",
    "distributed systems": "distributed_systems",
    "networking": "networking",
    "low-level systems": "systems_hardware_os",
    "algorithms": "data_structures_algorithms",
    "data engineering": "ai_ml",
}

# Keyword fallback, used for intents.json tags (arbitrary short strings),
# for programming-source rows (see categorize() below), and as a backstop
# for any QA category not in the exact map above.
# NOTE: order matters — collision-prone buckets like ai_ml (covers "neural
# network") are checked BEFORE generic ones like networking (covers bare
# "network") so "neural networks" lands in ai_ml, not networking.
CATEGORY_KEYWORDS = [
    ("oop_concepts", ["oop", "inherit", "polymorph", "encapsulat", "abstraction",
                       "constructor", "destructor", "operator overload", "aggregation",
                       "generic programming", "class and function template", "objects, classes"]),
    ("data_structures_algorithms", ["array", "stack", "queue", "linked list", "sorting",
                                     "sort algorithm", "searching", "hash table", "heap", "graph",
                                     "recursion", "complexity", "big o", "dynamic programming",
                                     "matrix", "deque", "priority queue", "fibonacci", "factorial",
                                     "polish notation", "quick sort", "binary search",
                                     "binary search tree", "algorithm"]),
    ("databases", ["database", "dbms", "sql", "normalization", "denormalization", "bcnf",
                    "degree of relation", "warehousing", "decision support", "orm",
                    "foreign key", "acid propert", "stored procedure", "distributed database",
                    "mongodb", "nosql", "query", "quer"]),
    ("ai_ml", ["machine learning", "neural network", "deep learning", "nlp",
                "natural language", "data science", "genetic algorithm", "evolutionary strateg",
                "logistic regression", "decision tree", "bayesian", "hyperparameter",
                "gradient descent", "backpropagation", "generalization", "vc dimension",
                "artificial intelligence", "expert system", "bias in data", "confusion matrix",
                "dimensionality reduction", "computational intelligence"]),
    ("web_development", ["web development", "http", "https", "dom", "cookie", "session",
                          "cors", "seo", "rest api", "restful", "web api", "react", "front-end",
                          "back-end", "full-stack", "mvc", "lazy loading",
                          "server-side rendering", "client-side rendering", "websocket",
                          "javascript", "closure in javascript"]),
    ("software_engineering_process", ["debugging", "documentation", "version control", "git",
                                       "requirement", "risk management", "quality assurance",
                                       "cocomo", "software maintenance", "code review", "refactor",
                                       "pull request", "merge conflict", "branching", "event logging",
                                       "software testing", "unit testing", "regression testing"]),
    ("systems_hardware_os", ["kernel", "operating system", "deadlock", "scheduling algorithm",
                              "memory management", "file management", "disk scheduling",
                              "register", "cache memory", "flip flop", "counter circuit",
                              "encoder", "decoder", "multiplexer", "adder", "interrupt",
                              "direct memory access", "instruction set", "microprocessor",
                              "digital logic", "computer performance", "amdahl",
                              "computer architecture"]),
    ("networking", ["networking", "computer network", "tcp", "ip address", "ip subnetting",
                     "subnetting", "osi reference", "osi model", "network routing", "sdn",
                     "software defined networking", "cdn", "load balancer", "network protocol"]),
    ("security", ["security", "encryption", "xss", "sql injection", "oauth", "authenticat",
                   "password hash", "ddos", "csrf", "vulnerab", "two-factor", "2fa"]),
    ("devops_cloud", ["devops", "docker", "kubernetes", "continuous integration",
                       "continuous delivery", "cloud computing", "infrastructure as code",
                       "ansible", "deployment pipeline", "blue-green"]),
    ("distributed_systems", ["distributed system", "distributed database", "blockchain",
                              "microservice", "raft", "consensus algorithm", "replication",
                              "sharding", "message broker", "kafka", "big data",
                              "cap theorem"]),
    ("theoretical_cs_math", ["propositional logic", "quantification", "truth table",
                              "predicate logic", "first-order logic", "first order logic",
                              "logical connective"]),
    ("emerging_tech", ["virtual reality", "internet of things", "iot", "quantum"]),
]

# Left-boundary-only regex: blocks false substring hits like "osi" inside
# "propositional" (no word boundary right before that "osi"), while still
# allowing plurals/suffixes like "kernel" matching "kernels" or "neural
# network" matching "neural networks" (no boundary required at the end).
_COMPILED_KEYWORDS = [
    (bucket, [re.compile(r"\b" + re.escape(kw.strip())) for kw in keywords])
    for bucket, keywords in CATEGORY_KEYWORDS
]


def categorize(label, text, source):
    """
    Normalize a row's (label, text, source) into one of the BUCKETS.

    IMPORTANT: source == "programming" is NOT auto-labeled
    "programming_languages" anymore. That used to short-circuit every one
    of the ~210 ProgrammingDataset rows straight into one bucket before
    the keyword matcher ever ran, which flooded training data and made
    the model default to "programming_languages" for almost everything
    at inference time (e.g. "MongoDB", "play with queries and
    normalization" should be "databases", not "programming_languages").

    Now programming-source rows go through the same keyword matching as
    everything else, and only fall back to "programming_languages" if no
    keyword bucket matches — i.e. it's treated as the *default* for that
    source, not an *override*.
    """
    label = (label or "").strip()
    text = (text or "").strip()

    if source == "software_qa":
        exact = QA_CATEGORY_MAP.get(label.lower())
        if exact:
            return exact

    haystack = f" {label} {text} ".lower()
    for bucket, patterns in _COMPILED_KEYWORDS:
        if any(p.search(haystack) for p in patterns):
            return bucket

    if source == "programming":
        return "programming_languages"

    return "general_cs"


def open_text_resilient(path):
    """
    Open a text file trying utf-8 first, then falling back to encodings
    common on Windows-authored CSVs (cp1252 has a stray em-dash byte in
    Software Questions.csv that isn't valid utf-8). Last resort replaces
    any still-broken bytes rather than crashing.
    """
    for encoding in ("utf-8", "utf-8-sig", "cp1252", "latin-1"):
        try:
            with open(path, "r", encoding=encoding, newline="") as f:
                return f.read(), encoding
        except UnicodeDecodeError:
            continue
    with open(path, "r", encoding="utf-8", errors="replace", newline="") as f:
        return f.read(), "utf-8 (with replacement)"


def load_intents(path):
    rows = []
    if not os.path.exists(path):
        print(f"[skip] {path} not found")
        return rows
    content, encoding = open_text_resilient(path)
    data = json.loads(content)
    for intent in data.get("intents", []):
        tag = intent.get("tag", "")
        patterns = intent.get("patterns", [])
        responses = intent.get("responses", [])
        response_text = responses[0] if responses else ""
        for pattern in patterns:
            rows.append({
                "text": pattern,
                "response": response_text,
                "label": tag,
                "difficulty": "",
                "source": "intents",
            })
    tag_note = f" (read as {encoding})" if encoding != "utf-8" else ""
    print(f"[ok] intents.json -> {len(rows)} rows{tag_note}")
    return rows


def load_programming(path):
    rows = []
    if not os.path.exists(path):
        print(f"[skip] {path} not found")
        return rows
    content, encoding = open_text_resilient(path)
    reader = csv.DictReader(content.splitlines())
    for r in reader:
        title = (r.get("Title") or "").strip()
        desc = (r.get("Description") or "").strip()
        text = f"{title}. {desc}".strip(". ").strip()
        rows.append({
            "text": text,
            "response": r.get("Code", ""),
            "label": r.get("Language", ""),
            "difficulty": r.get("Difficulty", ""),
            "source": "programming",
        })
    tag_note = f" (read as {encoding})" if encoding != "utf-8" else ""
    print(f"[ok] ProgrammingDataset -> {len(rows)} rows{tag_note}")
    return rows


def load_software_qa(path):
    rows = []
    if not os.path.exists(path):
        print(f"[skip] {path} not found")
        return rows
    content, encoding = open_text_resilient(path)
    reader = csv.DictReader(content.splitlines())
    for r in reader:
        rows.append({
            "text": r.get("Question", ""),
            "response": r.get("Answer", ""),
            "label": r.get("Category", ""),
            "difficulty": r.get("Difficulty", ""),
            "source": "software_qa",
        })
    tag_note = f" (read as {encoding})" if encoding != "utf-8" else ""
    print(f"[ok] Software Questions.csv -> {len(rows)} rows{tag_note}")
    return rows


def main():
    all_rows = []
    all_rows += load_intents(INTENTS_PATH)
    all_rows += load_programming(PROGRAMMING_PATH)
    all_rows += load_software_qa(SOFTWARE_QA_PATH)

    # Drop rows with no usable text
    all_rows = [r for r in all_rows if r["text"].strip()]

    # Add normalized category
    for r in all_rows:
        r["category"] = categorize(r["label"], r["text"], r["source"])

    # De-duplicate exact (text, label) pairs — the software QA set has
    # repeated question blocks in the raw file
    seen = set()
    deduped = []
    for r in all_rows:
        key = (r["text"].strip().lower(), r["label"].strip().lower())
        if key in seen:
            continue
        seen.add(key)
        deduped.append(r)

    os.makedirs(os.path.dirname(OUTPUT_PATH), exist_ok=True)
    with open(OUTPUT_PATH, "w", encoding="utf-8", newline="") as f:
        writer = csv.DictWriter(f, fieldnames=FIELDS)
        writer.writeheader()
        writer.writerows(deduped)

    print(f"\nTotal rows before dedup: {len(all_rows)}")
    print(f"Total rows after dedup:  {len(deduped)}")

    # Category breakdown summary
    from collections import Counter
    counts = Counter(r["category"] for r in deduped)
    print("\nCategory breakdown:")
    for cat, n in counts.most_common():
        print(f"  {cat:30s} {n}")

    print(f"\nMerged dataset written to: {OUTPUT_PATH}")


if __name__ == "__main__":
    main()
