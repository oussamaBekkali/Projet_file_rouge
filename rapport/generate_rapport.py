#!/usr/bin/env python3
"""
Build rapport.md from rapport-skelleton.md without using AI.

How it works:
- Reads the skeleton and supported text files recursively.
- Keeps the skeleton's headings and their order.
- Copies source Markdown sections into matching report sections using
  deterministic keyword rules (see SECTION_RULES below).
- Leaves a report section empty when no source content matches it.
- Rewrites local Markdown image paths so they work from rapport.md.
- Adds images found beside a matched source document when they are not
  already referenced in that section.

Run:
    python generate_rapport.py
    python generate_rapport.py --root "C:/path/to/rapport-folder"

The script does not summarize, translate, or invent content. It copies
existing text. Adjust SECTION_RULES if your folder/file names differ.
"""

from __future__ import annotations

import argparse
import re
from pathlib import Path
from collections import defaultdict

SKELETON_NAME = "rapport-skelleton.md"
OUTPUT_NAME = "rapport.md"

TEXT_EXTENSIONS = {
    ".md", ".markdown", ".txt", ".rst",
    ".html", ".htm", ".php", ".py", ".js", ".ts",
    ".css", ".json", ".xml", ".yml", ".yaml",
}

IMAGE_EXTENSIONS = {
    ".png", ".jpg", ".jpeg", ".gif", ".webp", ".svg",
}

# Each report heading has deterministic keywords.
# A source section is copied to the heading with the highest keyword score.
# Add/edit keywords to adapt the script to your own folder structure.
SECTION_RULES = {
    "Remerciement": ["remerciement", "acknowledgement"],
    "Introduction": ["introduction", "présentation générale"],
    "Contexte du projet": [
        "contexte", "project context", "présentation du projet",
        "fleettrack", "projet fil rouge",
    ],
    "Cahier des charges": [
        "cahier des charges", "besoins", "exigences", "fonctionnalités",
        "main features", "objectifs",
    ],
    "Méthodologie Scrum": ["scrum", "méthode scrum", "méthodologie scrum"],
    "Processus 2TUP": ["2tup", "two tracks unified process"],
    "Design Thinking": ["design thinking", "empathie", "idéation", "prototype", "test"],
    "Planification": ["planification", "planning", "sprint", "calendrier"],
    "Carte d’empathie": ["carte d'empathie", "carte d’empathie", "empathy map"],
    "Définition du problème": ["définition du problème", "problem statement"],
    "Idéation": ["idéation", "ideation", "brainstorming"],
    "Diagramme de cas d'utilisation": [
        "diagramme de cas d'utilisation", "use case", "cas d'utilisation",
    ],
    "Stack technologique": [
        "stack technologique", "choix technologiques", "technologies",
        "backend", "frontend", "base de données", "outils et utilitaires",
    ],
    "Architecture logicielle": [
        "architecture logicielle", "architecture du système", "architecture mvc",
        "architecture 3-tiers", "architecture globale", "architecture",
    ],
    "Prototype (fonctionnalités, classes)": [
        "projet technique", "prototype", "fonctionnalités clés",
        "classes principales", "relations principales", "task management",
    ],
    "Diagramme de classes": ["diagramme de classes", "class diagram"],
    "Maquettes": ["maquettes", "maquette", "wireframe", "mockup"],
    "Interfaces": ["interfaces", "interface", "ui", "captures d'écran", "screenshots"],
    "Conclusion": ["conclusion", "bilan", "perspectives"],
}

# File/folder-name hints are used only when the source file has no useful
# Markdown heading. These rules are intentionally explicit and editable.
PATH_RULES = {
    "scrum": "Méthodologie Scrum",
    "2tup": "Processus 2TUP",
    "design_thinking": "Design Thinking",
    "architecture": "Architecture logicielle",
    "branch_technique": "Stack technologique",
    "branche_technique": "Stack technologique",
    "projet-technique": "Prototype (fonctionnalités, classes)",
    "projet_technique": "Prototype (fonctionnalités, classes)",
}


def normalize(value: str) -> str:
    value = value.lower().replace("’", "'")
    value = re.sub(r"[`*_#>|()\[\]{}:.,;!?/\\-]+", " ", value)
    return re.sub(r"\s+", " ", value).strip()


def parse_skeleton(skeleton_text: str):
    """Return front matter and ordered (heading, markdown_level) pairs."""
    front_matter = ""
    body = skeleton_text

    if body.startswith("---"):
        match = re.match(r"\A---\s*\n.*?\n---\s*\n?", body, flags=re.S)
        if match:
            front_matter = match.group(0).rstrip() + "\n\n"
            body = body[match.end():]

    headings = []
    for raw_line in body.splitlines():
        if not raw_line.strip():
            continue
        if raw_line.lstrip().startswith("<!--"):
            continue

        explicit = re.match(r"^\s*(#{1,6})\s+(.+?)\s*$", raw_line)
        if explicit:
            headings.append((explicit.group(2).strip(), len(explicit.group(1))))
            continue

        # The provided skeleton uses indentation to indicate nested sections.
        indent = len(raw_line) - len(raw_line.lstrip(" "))
        title = raw_line.strip()
        if title.lower() == "rapport planning":
            headings.append((title, 1))
        elif title.lower() == "table des matières":
            headings.append((title, 4))
        else:
            headings.append((title, 2 if indent >= 4 else 1))

    return front_matter, headings


def split_markdown_sections(text: str):
    """
    Split Markdown into heading-based chunks.
    Content before the first heading is returned with an empty heading.
    """
    lines = text.splitlines()
    chunks = []
    current_heading = ""
    current_lines = []

    for line in lines:
        match = re.match(r"^(#{1,6})\s+(.+?)\s*$", line)
        if match:
            if current_lines or current_heading:
                chunks.append((current_heading, "\n".join(current_lines).strip()))
            current_heading = match.group(2).strip()
            current_lines = [line]
        else:
            current_lines.append(line)

    if current_lines or current_heading:
        chunks.append((current_heading, "\n".join(current_lines).strip()))

    return [(h, c) for h, c in chunks if c]


def score_heading(source_heading: str, source_path: Path, report_heading: str) -> int:
    """Score a source chunk against a report heading using literal keywords."""
    target = normalize(report_heading)
    source = normalize(source_heading)
    path_text = normalize(str(source_path))
    score = 0

    for keyword in SECTION_RULES.get(report_heading, [report_heading]):
        key = normalize(keyword)
        if not key:
            continue
        if key in source:
            score += 8
        if key in path_text:
            score += 5

    # Direct heading overlap is a useful fallback.
    target_words = set(target.split())
    source_words = set(source.split())
    overlap = target_words & source_words
    score += len(overlap) * 3

    for path_hint, destination in PATH_RULES.items():
        if destination == report_heading and path_hint.replace("_", " ") in path_text:
            score += 6

    return score


def find_destination(source_heading: str, source_path: Path, report_headings):
    ranked = []
    for heading in report_headings:
        if heading in {"Rapport Planning", "Table des matières"}:
            continue
        score = score_heading(source_heading, source_path, heading)
        if score > 0:
            ranked.append((score, heading))

    if not ranked:
        # Try path hints for documents without descriptive headings.
        path_text = normalize(str(source_path))
        for hint, destination in PATH_RULES.items():
            if normalize(hint.replace("_", " ")) in path_text and destination in report_headings:
                return destination
        return None

    ranked.sort(key=lambda item: item[0], reverse=True)
    # Avoid arbitrary assignment when two headings tie.
    if len(ranked) > 1 and ranked[0][0] == ranked[1][0]:
        return None
    return ranked[0][1]


def is_inside_code_fence(lines, line_index):
    fence_count = 0
    for line in lines[:line_index + 1]:
        if re.match(r"^\s*(```|~~~)", line):
            fence_count += 1
    return fence_count % 2 == 1


def rewrite_image_paths(markdown: str, source_file: Path, root: Path) -> str:
    """Rewrite relative Markdown image paths relative to the report root."""
    pattern = re.compile(r"(!\[[^\]]*\]\()([^)]+)(\))")

    def replace(match):
        prefix, raw_path, suffix = match.groups()
        raw_path = raw_path.strip()

        if re.match(r"^(https?://|data:|/|#)", raw_path, flags=re.I):
            return match.group(0)

        # Keep optional title text separate from the path.
        path_part = raw_path
        title_part = ""
        title_match = re.match(r'^(\S+)(\s+["\'].*["\'])$', raw_path)
        if title_match:
            path_part, title_part = title_match.groups()

        resolved = (source_file.parent / path_part).resolve()
        try:
            relative = resolved.relative_to(root.resolve()).as_posix()
        except ValueError:
            relative = Path(path_part).as_posix()

        return f"{prefix}{relative}{title_part}{suffix}"

    return pattern.sub(replace, markdown)


def collect_images(root: Path):
    return sorted(
        p for p in root.rglob("*")
        if p.is_file()
        and p.suffix.lower() in IMAGE_EXTENSIONS
        and not any(part.startswith(".") for part in p.relative_to(root).parts)
    )


def referenced_image_paths(markdown: str):
    return set(re.findall(r"!\[[^\]]*\]\(([^)]+)\)", markdown))


def add_unreferenced_images(section_content: str, source_file: Path, root: Path):
    """Append images from the source document's folder if not already linked."""
    images = [
        p for p in source_file.parent.iterdir()
        if p.is_file() and p.suffix.lower() in IMAGE_EXTENSIONS
    ]
    if not images:
        return section_content

    already = referenced_image_paths(section_content)
    additions = []
    for image_path in sorted(images):
        relative = image_path.relative_to(root).as_posix()
        if relative not in already and image_path.name not in already:
            additions.append(f"![{image_path.stem}]({relative})")

    if additions:
        section_content = section_content.rstrip()
        section_content += "\n\n" + "\n\n".join(additions)
    return section_content


def main():
    parser = argparse.ArgumentParser(description="Generate rapport.md from a Markdown skeleton.")
    parser.add_argument(
        "--root",
        default=".",
        help="Folder containing rapport-skelleton.md (default: current folder).",
    )
    args = parser.parse_args()

    root = Path(args.root).expanduser().resolve()
    skeleton_path = root / SKELETON_NAME
    output_path = root / OUTPUT_NAME

    if not skeleton_path.is_file():
        raise SystemExit(f"ERROR: Could not find {SKELETON_NAME} in: {root}")

    skeleton_text = skeleton_path.read_text(encoding="utf-8-sig")
    front_matter, skeleton_headings = parse_skeleton(skeleton_text)
    report_heading_names = [heading for heading, _ in skeleton_headings]

    collected = defaultdict(list)
    source_files = sorted(
        p for p in root.rglob("*")
        if p.is_file()
        and p != skeleton_path
        and p != output_path
        and p.suffix.lower() in TEXT_EXTENSIONS
        and not any(part.startswith(".") for part in p.relative_to(root).parts)
    )

    for source_file in source_files:
        try:
            text = source_file.read_text(encoding="utf-8-sig")
        except (UnicodeDecodeError, OSError):
            # Skip binary or unreadable files safely.
            continue

        if source_file.suffix.lower() in {".md", ".markdown", ".rst"}:
            chunks = split_markdown_sections(text)
            if not chunks:
                chunks = [("", text.strip())]
        else:
            # Non-Markdown text/code files are scanned, but are not copied
            # automatically to avoid dumping source code into the report.
            # Add them deliberately through SECTION_RULES if required.
            continue

        for source_heading, content in chunks:
            destination = find_destination(source_heading, source_file, report_heading_names)
            if destination:
                content = rewrite_image_paths(content, source_file, root)
                content = add_unreferenced_images(content, source_file, root)
                collected[destination].append((source_file, content))

    # Build a simple table of contents from the skeleton's headings.
    toc_lines = []
    for heading, level in skeleton_headings:
        if heading in {"Rapport Planning", "Table des matières"}:
            continue
        anchor = normalize(heading).replace(" ", "-")
        indent = "  " * max(0, level - 1)
        toc_lines.append(f"{indent}- [{heading}](#{anchor})")

    output_parts = []
    if front_matter:
        output_parts.append(front_matter.rstrip())

    for heading, level in skeleton_headings:
        if heading == "Rapport Planning":
            output_parts.append(f"# {heading}")
            continue

        output_parts.append(f"{'#' * max(1, level)} {heading}")

        if heading == "Table des matières":
            output_parts.append("\n".join(toc_lines))
            continue

        entries = collected.get(heading, [])
        if entries:
            # Preserve source order and avoid repeating identical chunks.
            seen = set()
            for source_file, content in entries:
                key = (str(source_file), content)
                if key in seen:
                    continue
                seen.add(key)
                output_parts.append(content.strip())

    output_path.write_text("\n\n".join(part for part in output_parts if part is not None).rstrip() + "\n",
                            encoding="utf-8")

    total_chunks = sum(len(items) for items in collected.values())
    images = collect_images(root)
    print(f"Created: {output_path}")
    print(f"Scanned Markdown/text files: {len(source_files)}")
    print(f"Matched source sections: {total_chunks}")
    print(f"Images found in folder: {len(images)}")
    print("Note: unmatched sections were left empty; no AI or content generation was used.")


if __name__ == "__main__":
    main()
