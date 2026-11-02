#!/usr/bin/env python3
"""Reject credential/configuration files and database archives in tracked source."""
from pathlib import PurePosixPath
import re
import subprocess
import sys

def forbidden(path):
    p=PurePosixPath(path);name=p.name.lower()
    if name in {'wp-config.php','wp-config-local.php','auth.json','id_rsa','id_ed25519'}:
        return True
    if name=='.env' or (name.startswith('.env.') and name not in {'.env.example','.env.sample','.env.template'}):
        return True
    if re.search(r'\.(?:sql(?:\.(?:gz|bz2|xz|zst))?|wpress|key|p12)$',name):
        # Vendor schema definitions are application source, not exported customer data.
        return '/litespeed-cache/src/data_structure/' not in '/'+path
    return bool(re.search(r'wp-config\.php\.(?:bak|old|backup)',name))

if __name__=='__main__':
    files=subprocess.check_output(['git','ls-files','-z']).decode().split('\0')
    bad=[path for path in files if path and forbidden(path)]
    if bad:
        print('Forbidden private/database source paths:\n'+'\n'.join(bad),file=sys.stderr)
        raise SystemExit(1)
    print('PASS: no tracked private configuration or database archive paths')
