import math
from flask import Flask, request, jsonify
from collections import Counter
from dotenv import load_dotenv
import joblib
import os

from ml_model import tokenize

load_dotenv()

app = Flask(__name__)

API_KEY = os.environ.get("ML_API_KEY", "dev-key-change-me")

classifier = joblib.load("models/classifier.pkl")
vectorizer = joblib.load("models/vectorizer.pkl")


@app.before_request
def check_api_key():
    if request.path == "/health":
        return
    if request.headers.get("X-API-KEY") != API_KEY:
        return jsonify({"error": "unauthorized"}), 401


@app.route("/health", methods=["GET"])
def health():
    return jsonify({"status": "ok"})


@app.route("/classify", methods=["POST"])
def classify():
    data = request.get_json(force=True)
    text = data.get("text", "").strip()
    if not text:
        return jsonify({"category": "General"})

    X = vectorizer.transform([text])

    # X[0] is a Counter of {vocab_index: count}. If it's empty, none of
    # this title's words exist in the training vocabulary at all, so the
    # model has zero signal — predict()/predict_proba() would otherwise
    # silently fall back to prior-only scoring (always the majority
    # training class, always the same "confidence"). Treat that as a
    # genuine classification failure instead of a real prediction, so
    # the caller (TopicClassifierService::classify in Laravel) falls
    # back to its local keyword classifier, matching how it already
    # handles any other non-success response from this service.
    if not X[0]:
        return jsonify({
            "error": "no_vocabulary_overlap",
            "message": "Input text has no overlap with the training vocabulary.",
        }), 422

    category = classifier.predict(X)[0]
    proba = classifier.predict_proba(X).max()

    if category is None:
        return jsonify({
            "error": "no_vocabulary_overlap",
            "message": "Input text has no overlap with the training vocabulary.",
        }), 422

    return jsonify({"category": category, "confidence": round(float(proba), 3)})


def _build_profile(history):
    """Aggregates a bag-of-words 'interest profile' from every title the
    user has engaged with (one entry per reply, so topics replied to more
    often contribute more weight)."""
    profile = Counter()
    for h in history:
        title = h.get("title", "") or ""
        for tok in tokenize(title):
            profile[tok] += 1
    return profile


def _cosine_sim(vec_a, vec_b):
    """Plain cosine similarity between two token-count Counters, no numpy
    required. Returns 0.0 if either vector is empty (no shared vocabulary,
    or one side has no tokens at all)."""
    if not vec_a or not vec_b:
        return 0.0
    common = set(vec_a.keys()) & set(vec_b.keys())
    dot = sum(vec_a[t] * vec_b[t] for t in common)
    norm_a = math.sqrt(sum(v * v for v in vec_a.values()))
    norm_b = math.sqrt(sum(v * v for v in vec_b.values()))
    if norm_a == 0 or norm_b == 0:
        return 0.0
    return dot / (norm_a * norm_b)


@app.route("/recommend", methods=["POST"])
def recommend():
    data = request.get_json(force=True)
    history = data.get("user_history", [])
    candidates = data.get("candidate_topics", [])

    if not history or not candidates:
        return jsonify({
            "recommendations": [
                {"topic_id": c["topic_id"], "relevance_score": 0.1}
                for c in candidates
            ]
        })

    category_counts = Counter(h.get("category") for h in history if h.get("category"))
    max_count = max(category_counts.values()) if category_counts else 0
    profile = _build_profile(history)

    results = []
    for c in candidates:
        cat = c.get("category", "General")
        title = c.get("title", "") or ""

        # Category weight: how much of the user's activity falls in this
        # candidate's category (0..1, highest for their most-active category).
        category_score = (category_counts.get(cat, 0) / max_count) if max_count > 0 else 0.0

        # Content weight: how similar THIS specific topic's title is to
        # the titles the user has actually engaged with (0..1). This is
        # what differentiates individual topics within the same category
        # instead of every same-category candidate getting one flat score.
        content_score = _cosine_sim(Counter(tokenize(title)), profile)

        # Category dominates the ranking (so the most-active category still
        # surfaces first overall); content similarity breaks ties within it.
        relevance = round(min(1.0, 0.6 * category_score + 0.4 * content_score), 3)
        relevance = max(relevance, 0.05)

        results.append({"topic_id": c["topic_id"], "relevance_score": relevance})

    results.sort(key=lambda r: r["relevance_score"], reverse=True)
    return jsonify({"recommendations": results})


@app.route("/retrain", methods=["POST"])
def retrain():
    os.system("python3 train_classifier.py")
    global classifier, vectorizer
    classifier = joblib.load("models/classifier.pkl")
    vectorizer = joblib.load("models/vectorizer.pkl")
    return jsonify({"status": "retrained"})


if __name__ == "__main__":
    app.run(host="127.0.0.1", port=5001)
