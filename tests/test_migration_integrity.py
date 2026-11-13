import re
import json

# 1. Parse all table definitions from schema.sql
with open('core/schema.sql', 'r', encoding='utf-8') as f:
    schema_sql = f.read()

# Clean SQL comments
clean_sql = re.sub(r'--.*?$', '', schema_sql, flags=re.MULTILINE)
clean_sql = re.sub(r'/\*.*?\*/', '', clean_sql, flags=re.DOTALL)

# Extract table schemas: {table_name: [columns]}
table_blocks = re.findall(r'CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?(\w+)`?\s*\((.*?)\)\s*ENGINE', clean_sql, re.DOTALL | re.IGNORECASE)

schema_columns = {}
for table_name, block in table_blocks:
    cols = []
    lines = block.split('\n')
    for line in lines:
        line = line.strip().rstrip(',')
        if not line or line.upper().startswith(('PRIMARY', 'UNIQUE', 'KEY', 'INDEX', 'CONSTRAINT', 'FOREIGN')):
            continue
        m = re.match(r'`?(\w+)`?\s+', line)
        if m:
            cols.append(m.group(1))
    schema_columns[table_name] = cols

print(f"Extracted schema columns for {len(schema_columns)} tables:")
for t, cols in schema_columns.items():
    print(f"  {t} ({len(cols)} columns): {', '.join(cols[:5])}...")

# 2. Parse INSERT statements from core/migrate.php
with open('core/migrate.php', 'r', encoding='utf-8') as f:
    migrate_php = f.read()

insert_matches = re.findall(r'INSERT\s+INTO\s+`?(\w+)`?\s*\((.*?)\)\s*VALUES\s*\((.*?)\)', migrate_php, re.IGNORECASE | re.DOTALL)

print(f"\nDiscovered {len(insert_matches)} INSERT statements in migrate.php:")
all_valid = True

for table, cols_raw, vals_raw in insert_matches:
    cols = [c.strip().strip('`') for c in cols_raw.split(',') if c.strip()]
    placeholders = [p.strip() for p in vals_raw.split(',') if p.strip()]
    
    # Check column count vs placeholder count
    if len(cols) != len(placeholders):
        print(f"  [ERROR] Table {table}: Column count ({len(cols)}) != placeholder count ({len(placeholders)})")
        all_valid = False
        continue

    # Check that all columns exist in schema.sql
    if table not in schema_columns:
        print(f"  [ERROR] Table {table} does not exist in schema.sql!")
        all_valid = False
        continue
    
    invalid_cols = [c for c in cols if c not in schema_columns[table]]
    if invalid_cols:
        print(f"  [ERROR] Table {table} has invalid columns: {invalid_cols}")
        all_valid = False
    else:
        print(f"  [PASS] {table}: {len(cols)} columns match schema ({', '.join(cols[:4])}...) with {len(placeholders)} placeholders.")

# 3. Verify JSON payload against migration
with open('data/portfolio_store.json', 'r', encoding='utf-8') as f:
    json_data = json.load(f)

print(f"\nValidating data/portfolio_store.json keys:")
for table in schema_columns.keys():
    if table in json_data:
        items = json_data[table]
        count = len(items) if isinstance(items, (list, dict)) else 0
        print(f"  [PASS] {table}: {count} records ready in JSON")
    else:
        print(f"  [WARN] {table}: Not present in JSON")

if all_valid:
    print("\nALL MIGRATION SQL & PAYLOAD INTEGRITY CHECKS PASSED PERFECTLY!")
else:
    print("\nMIGRATION SQL INTEGRITY CHECKS ENCOUNTERED ERRORS.")
