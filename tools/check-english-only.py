#!/usr/bin/env python3
"""Fail if the child theme contains Devanagari display text (see docs/LANGUAGE-RULES.md)."""
import os
import re
import sys

DEVANAGARI = re.compile('[\u0900-\u097F]')
# Brand tagline (owner decision 2026-11-19) is the one Devanagari display text;
# it lives only in inc/brand-tagline.php.
TAGLINE = 'परस्परानुकथनं पावनं भगवद्यशः'
TAGLINE_FILE = os.path.join('inc', 'brand-tagline.php')
ALLOWED = re.compile(r"^\s*'name_hi'\s*=>.*$|\u0950")
ROOT = sys.argv[1] if len(sys.argv) > 1 else 'buddyboss-theme-child'
bad = []
for base, _, files in os.walk(ROOT):
    if 'node_modules' in base:
        continue
    for name in files:
        if not name.endswith(('.php', '.js', '.css', '.html', '.json', '.txt')):
            continue
        path = os.path.join(base, name)
        with open(path, encoding='utf-8', errors='ignore') as fh:
            for no, line in enumerate(fh, 1):
                if path.endswith(TAGLINE_FILE):
                    line = line.replace(TAGLINE, '')
                if DEVANAGARI.search(ALLOWED.sub('', line)):
                    bad.append(f'{path}:{no}: {line.strip()[:100]}')
if bad:
    print('Display text must be English (see docs/LANGUAGE-RULES.md):')
    print('\n'.join(bad))
    sys.exit(1)
print('No Devanagari display text found.')
