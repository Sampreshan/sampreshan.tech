#!/usr/bin/env python3
"""Fail if the child theme contains emoji (see docs/ICON-RULES.md)."""
import os
import re
import sys

EMOJI = re.compile('[\U0001F000-\U0001FAFF\u2600-\u27BF\u2B50\u2B55\u2B1B\u2B1C\u2934\u2935\u3030\u303D\u3297\u3299\uFE0F\u200D]')
ROOT = sys.argv[1] if len(sys.argv) > 1 else 'buddyboss-theme-child'
bad = []
for base, _, files in os.walk(ROOT):
    if 'node_modules' in base:
        continue
    for name in files:
        if not name.endswith(('.php', '.js', '.css', '.html', '.json', '.txt', '.md')):
            continue
        path = os.path.join(base, name)
        with open(path, encoding='utf-8', errors='ignore') as fh:
            for no, line in enumerate(fh, 1):
                if EMOJI.search(line):
                    bad.append(f'{path}:{no}: {line.strip()[:100]}')
if bad:
    print('Emoji are not allowed; use the SampreShan icon kit instead:')
    print('\n'.join(bad))
    sys.exit(1)
print('No emoji found.')
