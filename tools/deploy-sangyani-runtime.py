#!/usr/bin/env python3
"""Scoped, hash-guarded deployment; run on VPS against a private candidate directory."""
import argparse, hashlib, json, os, tempfile
from pathlib import Path
from datetime import datetime, timezone
p=argparse.ArgumentParser()
p.add_argument('--candidate',type=Path,required=True)
p.add_argument('--manifest',type=Path,required=True)
p.add_argument('--site',type=Path,required=True)
p.add_argument('--backup',type=Path,required=True)
p.add_argument('--apply',action='store_true')
a=p.parse_args()
assert (a.site/'wp-config.php').is_file(), 'WordPress site missing'
assert all((a.backup/f).stat().st_size>0 for f in ['theme.tgz','database.sql.gz']), 'Required backup missing'
theme=a.site/'wp-content/themes/buddyboss-theme-child'
private=a.site.parent/'private/sangyani'
manifest=json.loads(a.manifest.read_text())
def sha(path): return hashlib.sha256(path.read_bytes()).hexdigest() if path.is_file() else None
for entry in manifest:
    rel=Path(entry['path'])
    assert not rel.is_absolute() and '..' not in rel.parts, 'Unsafe manifest path'
    assert sha(theme/rel)==entry['before'], 'Live file changed: '+str(rel)
    if entry['after']:
        assert sha(a.candidate/'buddyboss-theme-child'/rel)==entry['after'], 'Candidate mismatch: '+str(rel)
bank=sorted((a.candidate/'content/sangyani').glob('[0-9][0-9].txt'))
assert len(bank)==10, 'Need ten bank files'
for f in bank:
    lines=f.read_text().splitlines()
    assert len(lines)==51 and all(len(line.split('|'))==5 for line in lines[1:]), 'Invalid bank: '+f.name
print(json.dumps({'verifiedFiles':len(manifest),'bankCards':500,'apply':a.apply}))
if not a.apply: raise SystemExit(0)
owner=(theme/'template-quiz.php').stat()
created_private = not private.exists()
private.mkdir(parents=True,exist_ok=True)
os.chown(private,owner.st_uid,owner.st_gid)
# Preserve an existing deployment ACL and its access mask.
if created_private: os.chmod(private,0o750)
original={}
def replace(path,data):
    path.parent.mkdir(parents=True,exist_ok=True)
    fd,tmp=tempfile.mkstemp(prefix='.sangyani-',dir=path.parent)
    try:
        with os.fdopen(fd,'wb') as out:
            out.write(data);out.flush();os.fsync(out.fileno())
        os.chown(tmp,owner.st_uid,owner.st_gid);os.chmod(tmp,0o644);os.replace(tmp,path)
    finally:
        if os.path.exists(tmp): os.unlink(tmp)
def change(path,data):
    original[path]=path.read_bytes() if path.exists() else None
    if data is None: path.unlink(missing_ok=True)
    else: replace(path,data)
try:
    for f in bank: change(private/f.name,f.read_bytes())
    # Dependencies first; PHP reader last. Each file is replaced atomically.
    entries=sorted(manifest,key=lambda e:e['path']=='inc/quiz/quiz.php')
    for entry in entries:
        if entry['after']: change(theme/entry['path'],(a.candidate/'buddyboss-theme-child'/entry['path']).read_bytes())
    for entry in entries:
        if entry['after'] is None: change(theme/entry['path'],None)
    for entry in manifest: assert sha(theme/entry['path'])==entry['after'], 'Post-copy mismatch'
except BaseException:
    for path,data in reversed(list(original.items())):
        if data is None: path.unlink(missing_ok=True)
        else: replace(path,data)
    raise
receipt={'deployedAt':datetime.now(timezone.utc).isoformat(),'files':manifest,'privateBank':{f.name:sha(private/f.name) for f in bank},'unrelatedFilesChanged':False}
(a.backup/'deployment-receipt.json').write_text(json.dumps(receipt,indent=2)+'\n')
print('Scoped deployment verified; receipt saved outside webroot.')
