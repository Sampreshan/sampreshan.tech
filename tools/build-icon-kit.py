#!/usr/bin/env python3
"""Build the SampreShan icon kit from 1024px transparent PNGs.

Usage: python3 tools/build-icon-kit.py <folder-with-pngs>

Writes 64/128/256px WebP files to buddyboss-theme-child/assets/icons/kit/
and 512px masters to assets/icons/kit-src/ (masters are not deployed).
"""
import glob
import json
import os
import sys

from PIL import Image

ROOT = os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', 'buddyboss-theme-child', 'assets', 'icons')
OUT, SRC = os.path.join(ROOT, 'kit'), os.path.join(ROOT, 'kit-src')


def main(folder):
    os.makedirs(OUT, exist_ok=True)
    os.makedirs(SRC, exist_ok=True)
    for f in sorted(glob.glob(os.path.join(folder, '*.png'))):
        name = os.path.basename(f)[:-4]
        name = name[3:] if name.startswith('sp-') else name
        im = Image.open(f).convert('RGBA')
        box = im.getchannel('A').point(lambda a: 255 if a > 8 else 0).getbbox()
        if box:
            im = im.crop(box)
        side = int(max(im.size) * 1.12)  # same padding for every icon
        sq = Image.new('RGBA', (side, side), (0, 0, 0, 0))
        sq.paste(im, ((side - im.width) // 2, (side - im.height) // 2), im)
        for size in (64, 128, 256):
            sq.resize((size, size), Image.LANCZOS).save(os.path.join(OUT, f'{name}-{size}.webp'), 'WEBP', quality=88, method=4)
        sq.resize((512, 512), Image.LANCZOS).save(os.path.join(SRC, f'{name}.webp'), 'WEBP', quality=90, method=4)
        print(name)
    names = sorted({os.path.basename(p)[:-8] for p in glob.glob(os.path.join(OUT, '*-64.webp'))})
    with open(os.path.join(OUT, 'manifest.json'), 'w') as fh:
        json.dump(names, fh)
    print(f'{len(names)} icons in kit')


if __name__ == '__main__':
    if len(sys.argv) != 2:
        sys.exit(__doc__)
    main(sys.argv[1])
