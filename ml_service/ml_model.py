"""
Shared model classes for the lightweight text classifier.

IMPORTANT: Both train_classifier.py and app.py must import these classes
from THIS module (not define them locally), otherwise joblib.load() will
fail to resolve the class when loading the pickle in a different script.
"""

import math
import re
from collections import defaultdict, Counter


def tokenize(text):
    """Lowercase, strip punctuation, split on whitespace."""
    text = text.lower()
    text = re.sub(r"[^a-z0-9\s]", " ", text)
    return [tok for tok in text.split() if tok]


class SimpleVectorizer:
    """Minimal bag-of-words vectorizer, no numpy/scipy required."""

    def __init__(self, min_df=1):
        self.vocab_ = {}
        self.min_df = min_df

    def fit(self, texts):
        doc_freq = Counter()
        for text in texts:
            for tok in set(tokenize(text)):
                doc_freq[tok] += 1

        vocab = [tok for tok, df in doc_freq.items() if df >= self.min_df]
        self.vocab_ = {tok: idx for idx, tok in enumerate(sorted(vocab))}
        return self

    def transform(self, texts):
        """Returns a list of Counter objects: {vocab_index: count}."""
        vectors = []
        for text in texts:
            counts = Counter()
            for tok in tokenize(text):
                idx = self.vocab_.get(tok)
                if idx is not None:
                    counts[idx] += 1
            vectors.append(counts)
        return vectors

    def fit_transform(self, texts):
        self.fit(texts)
        return self.transform(texts)


class SimpleNaiveBayes:
    """Multinomial Naive Bayes, pure Python, sklearn-compatible-ish interface."""

    def __init__(self, alpha=1.0):
        self.alpha = alpha
        self.classes_ = []
        self.class_log_prior_ = {}
        self.feature_log_prob_ = {}
        self.vocab_size_ = 0

    def fit(self, X, y):
        """X: list of Counter (from vectorizer.transform), y: list of labels."""
        self.classes_ = sorted(set(y))
        class_counts = Counter(y)
        total_docs = len(y)

        self.class_log_prior_ = {
            c: math.log(class_counts[c] / total_docs) for c in self.classes_
        }

        vocab_size = max((max(vec.keys(), default=-1) for vec in X), default=-1) + 1
        self.vocab_size_ = vocab_size

        feature_counts = {c: defaultdict(int) for c in self.classes_}
        class_totals = {c: 0 for c in self.classes_}

        for vec, label in zip(X, y):
            for idx, count in vec.items():
                feature_counts[label][idx] += count
                class_totals[label] += count

        self.feature_log_prob_ = {}
        for c in self.classes_:
            denom = class_totals[c] + self.alpha * vocab_size
            probs = {}
            for idx in range(vocab_size):
                num = feature_counts[c].get(idx, 0) + self.alpha
                probs[idx] = math.log(num / denom)
            self.feature_log_prob_[c] = probs

        return self

    def _joint_log_likelihood(self, vec):
        """Returns None when vec has no tokens that overlap the training
        vocabulary at all — i.e. we have zero signal about this input.
        Without this guard, every class's score falls back to just its
        class_log_prior_, so predict() silently returns the majority
        training class and predict_proba() returns the same fixed
        prior-based distribution for every unknown input (bug: unrelated
        titles like "University"/"Cars"/"Users" all got classified as
        the majority class with an identical, meaningless confidence)."""
        if not vec:
            return None

        scores = {}
        for c in self.classes_:
            score = self.class_log_prior_[c]
            log_probs = self.feature_log_prob_[c]
            for idx, count in vec.items():
                if idx in log_probs:
                    score += log_probs[idx] * count
            scores[c] = score
        return scores

    def predict(self, X):
        preds = []
        for vec in X:
            scores = self._joint_log_likelihood(vec)
            preds.append(max(scores, key=scores.get) if scores else None)
        return preds

    def predict_proba(self, X):
        """Returns a ProbaMatrix: rows support .max() like a numpy array row,
        and column order matches self.classes_ (sorted). A row of all
        zeros signals "no real prediction" (empty/unknown-vocab input) —
        it will never occur in a genuine prediction because a real
        softmax distribution always sums to 1."""
        rows = []
        for vec in X:
            scores = self._joint_log_likelihood(vec)
            if not scores:
                rows.append(ProbaRow([0.0] * len(self.classes_)))
                continue
            max_score = max(scores.values())
            exp_scores = {c: math.exp(s - max_score) for c, s in scores.items()}
            total = sum(exp_scores.values())
            row = ProbaRow(exp_scores[c] / total for c in self.classes_)
            rows.append(row)
        return ProbaMatrix(rows)


class ProbaRow(list):
    """A single prediction's probabilities. Supports .max() like numpy."""

    def max(self):
        return max(self) if self else 0.0


class ProbaMatrix(list):
    """List of ProbaRow. Supports .max() (global) like a numpy 2D array,
    matching calls such as model.predict_proba(X).max()."""

    def max(self):
        return max(row.max() for row in self) if self else 0.0
