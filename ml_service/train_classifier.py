"""
train_classifier.py

Trains the topic classifier used by app.py's /classify endpoint, from the
merged dataset produced by merge_datasets.py.

This version has ZERO compiled dependencies (no numpy/scipy/pandas/
scikit-learn) — it only needs the standard library plus joblib (pure
Python), so it installs and trains instantly on Termux.

Reads:  datasets/merged_dataset.csv   (columns: text, response, label, category, difficulty, source)
Writes: models/classifier.pkl
        models/vectorizer.pkl

Target column is `category` (the normalized 15-bucket topic grouping),
since that's what app.py's /classify endpoint returns to callers.

Usage:
    python3 train_classifier.py

Also invoked automatically by app.py's /retrain endpoint via:
    os.system("python3 train_classifier.py")
"""

import csv
import os
import random
import sys
from collections import Counter

import joblib

from ml_model import SimpleVectorizer, SimpleNaiveBayes

DATA_PATH = "datasets/merged_dataset.csv"
MODEL_DIR = "models"
TARGET_COLUMN = "category"
TEXT_COLUMN = "text"
TEST_SIZE = 0.2
RANDOM_STATE = 42


def load_rows(path):
    with open(path, "r", encoding="utf-8", newline="") as f:
        reader = csv.DictReader(f)
        rows = list(reader)
    return rows


def stratified_split(texts, labels, test_size, seed):
    """Manual stratified train/test split — no sklearn required.
    Groups indices by label, shuffles each group, and peels off a
    proportional slice into the test set so every class is represented
    in both splits (same guarantee sklearn's stratify=y gives)."""
    rng = random.Random(seed)
    by_label = {}
    for idx, label in enumerate(labels):
        by_label.setdefault(label, []).append(idx)

    train_idx, test_idx = [], []
    for label, idxs in by_label.items():
        idxs = idxs[:]
        rng.shuffle(idxs)
        n_test = max(1, round(len(idxs) * test_size))
        test_idx.extend(idxs[:n_test])
        train_idx.extend(idxs[n_test:])

    rng.shuffle(train_idx)
    rng.shuffle(test_idx)

    X_train = [texts[i] for i in train_idx]
    y_train = [labels[i] for i in train_idx]
    X_test = [texts[i] for i in test_idx]
    y_test = [labels[i] for i in test_idx]
    return X_train, X_test, y_train, y_test


def accuracy_score(y_true, y_pred):
    correct = sum(1 for t, p in zip(y_true, y_pred) if t == p)
    return correct / len(y_true) if y_true else 0.0


def classification_report(y_true, y_pred, labels):
    """Minimal per-class precision/recall/f1, formatted like sklearn's report."""
    lines = [f"{'category':30s} {'precision':>10s} {'recall':>10s} {'f1':>10s} {'support':>10s}"]
    for label in labels:
        tp = sum(1 for t, p in zip(y_true, y_pred) if t == label and p == label)
        fp = sum(1 for t, p in zip(y_true, y_pred) if t != label and p == label)
        fn = sum(1 for t, p in zip(y_true, y_pred) if t == label and p != label)
        support = sum(1 for t in y_true if t == label)
        precision = tp / (tp + fp) if (tp + fp) else 0.0
        recall = tp / (tp + fn) if (tp + fn) else 0.0
        f1 = (2 * precision * recall / (precision + recall)) if (precision + recall) else 0.0
        lines.append(f"{label:30s} {precision:10.2f} {recall:10.2f} {f1:10.2f} {support:10d}")
    return "\n".join(lines)


def main():
    if not os.path.exists(DATA_PATH):
        print(f"[error] {DATA_PATH} not found. Run merge_datasets.py first.")
        sys.exit(1)

    rows = load_rows(DATA_PATH)
    before = len(rows)
    rows = [
        r for r in rows
        if (r.get(TEXT_COLUMN) or "").strip() and (r.get(TARGET_COLUMN) or "").strip()
    ]
    dropped = before - len(rows)
    if dropped:
        print(f"[info] dropped {dropped} rows with missing {TEXT_COLUMN}/{TARGET_COLUMN}")

    texts = [r[TEXT_COLUMN] for r in rows]
    labels = [r[TARGET_COLUMN] for r in rows]

    counts = Counter(labels)
    print(f"[info] training on {len(rows)} rows across {len(counts)} categories")
    for cat, n in counts.most_common():
        print(f"  {cat:30s} {n}")

    # Guard against categories too small to stratify-split (need at least
    # 2 examples so both train and test can get at least one each).
    too_small = {cat: n for cat, n in counts.items() if n < 2}
    if too_small:
        print(f"\n[warn] these categories have <2 examples and will be dropped "
              f"from training (not enough data to split): {list(too_small.keys())}")
        keep = [i for i, l in enumerate(labels) if l not in too_small]
        texts = [texts[i] for i in keep]
        labels = [labels[i] for i in keep]

    X_train_text, X_test_text, y_train, y_test = stratified_split(
        texts, labels, TEST_SIZE, RANDOM_STATE
    )

    vectorizer = SimpleVectorizer(min_df=1)
    X_train = vectorizer.fit_transform(X_train_text)
    X_test = vectorizer.transform(X_test_text)

    clf = SimpleNaiveBayes(alpha=1.0)
    clf.fit(X_train, y_train)

    y_pred = clf.predict(X_test)
    acc = accuracy_score(y_test, y_pred)

    print(f"\n[result] test accuracy: {acc:.3f}")
    print("\n[result] per-category performance (on held-out test set):")
    print(classification_report(y_test, y_pred, clf.classes_))

    os.makedirs(MODEL_DIR, exist_ok=True)
    joblib.dump(clf, os.path.join(MODEL_DIR, "classifier.pkl"))
    joblib.dump(vectorizer, os.path.join(MODEL_DIR, "vectorizer.pkl"))
    print(f"[info] saved model to {MODEL_DIR}/classifier.pkl and {MODEL_DIR}/vectorizer.pkl")


if __name__ == "__main__":
    main()
