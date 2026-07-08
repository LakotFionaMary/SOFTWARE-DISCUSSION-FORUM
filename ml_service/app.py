from flask import Flask, request, jsonify
from collections import Counter
import re

app = Flask(__name__)

# ---------------------------------------------------------
# Keyword-based classifier
# ---------------------------------------------------------
CATEGORY_KEYWORDS = {
    "Software Engineering": [
        "software", "engineering", "architecture", "design pattern",
        "microservice", "monolith", "sdlc", "agile", "scrum", "uml",
        "requirements", "testing", "deployment", "api", "framework"
    ],
    "Databases": [
        "database", "sql", "query", "normalization", "table", "schema",
        "index", "mysql", "postgresql", "join", "erd", "transaction"
    ],
    "Programming": [
        "python", "java", "javascript", "code", "function", "class",
        "object", "variable", "loop", "algorithm", "oop", "syntax", "debug"
    ],
    "Networks": [
        "network", "protocol", "tcp", "udp", "socket", "router", "ip address",
        "bandwidth", "latency", "packet", "dns", "http", "websocket"
    ],
    "Mathematics": [
        "math", "calculus", "algebra", "matrix", "probability", "statistics",
        "equation", "theorem", "proof", "vector", "derivative", "integral"
    ],
    "General": []  # fallback
}

def classify_text(text: str) -> str:
    text_lower = text.lower()
    scores = {}
    for category, keywords in CATEGORY_KEYWORDS.items():
        if category == "General":
            continue
        score = sum(1 for kw in keywords if kw in text_lower)
        if score > 0:
            scores[category] = score

    if not scores:
        return "General"

    return max(scores, key=scores.get)


@app.route("/classify", methods=["POST"])
def classify():
    data = request.get_json(force=True)
    text = data.get("text", "")
    if not text.strip():
        return jsonify({"category": "General"})
    category = classify_text(text)
    return jsonify({"category": category})


# ---------------------------------------------------------
# Content-based recommendation
# ---------------------------------------------------------
@app.route("/recommend", methods=["POST"])
def recommend():
    """
    Expects JSON:
    {
        "user_history": [
            {"topic_id": 1, "category": "Databases"},
            {"topic_id": 2, "category": "Databases"},
            {"topic_id": 3, "category": "Networks"}
        ],
        "candidate_topics": [
            {"topic_id": 10, "category": "Databases"},
            {"topic_id": 11, "category": "Mathematics"},
            {"topic_id": 12, "category": "Networks"}
        ]
    }
    Returns candidate topics ranked by how often their category
    appears in the user's history, normalised to a 0-1 relevance_score.
    """
    data = request.get_json(force=True)
    history = data.get("user_history", [])
    candidates = data.get("candidate_topics", [])

    if not history or not candidates:
        # Cold start: no history yet, just return candidates with a flat low score
        return jsonify({
            "recommendations": [
                {"topic_id": c["topic_id"], "relevance_score": 0.1}
                for c in candidates
            ]
        })

    category_counts = Counter(h["category"] for h in history)
    max_count = max(category_counts.values())

    results = []
    for c in candidates:
        cat = c.get("category", "General")
        raw_score = category_counts.get(cat, 0)
        relevance = round(raw_score / max_count, 2) if max_count > 0 else 0.1
        # ensure even unseen categories get a small non-zero score
        relevance = max(relevance, 0.05)
        results.append({"topic_id": c["topic_id"], "relevance_score": relevance})

    results.sort(key=lambda r: r["relevance_score"], reverse=True)
    return jsonify({"recommendations": results})


if __name__ == "__main__":
    app.run(host="127.0.0.1", port=5001)