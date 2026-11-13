import re

with open('core/schema.sql', 'r', encoding='utf-8') as f:
    raw_sql = f.read()

# Strip comments
clean_sql = re.sub(r'--.*?$', '', raw_sql, flags=re.MULTILINE)
clean_sql = re.sub(r'/\*.*?\*/', '', clean_sql, flags=re.DOTALL)

# Check for actual SQL executable statements
forbidden_patterns = [
    (r'\bCREATE\s+DATABASE\b', 'CREATE DATABASE statement'),
    (r'\bUSE\s+[`\w]+', 'USE database statement'),
    (r'\bDROP\s+DATABASE\b', 'DROP DATABASE statement')
]

has_error = False
for pattern, desc in forbidden_patterns:
    if re.search(pattern, clean_sql, re.IGNORECASE):
        print(f"FAILED: Found forbidden executable statement: {desc}")
        has_error = True

if not has_error:
    print("PASSED: Zero forbidden database-creation or switching statements found.")

# Find all CREATE TABLE statements
tables = re.findall(r'CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?(\w+)`?', clean_sql, re.IGNORECASE)
print(f"\nDiscovered Tables ({len(tables)} total):")
canonical_12 = [
    'admin_users', 'profile', 'skill_categories', 'skills', 
    'projects', 'experience_claims', 'internships', 'education', 
    'certifications', 'social_links', 'inquiries', 'settings'
]

missing = []
for t in canonical_12:
    if t in tables:
        print(f"  [OK] Table: {t}")
    else:
        print(f"  [MISSING] Table: {t}")
        missing.append(t)

if len(missing) == 0 and len(tables) == 12:
    print("\nPASSED: All 12 canonical tables are correctly defined!")
else:
    print(f"\nFAILED: Discrepancy in table definitions. Missing: {missing}")

# Check engine and charset
engine_matches = re.findall(r'ENGINE\s*=\s*InnoDB', clean_sql, re.IGNORECASE)
charset_matches = re.findall(r'DEFAULT\s+CHARSET\s*=\s*utf8mb4', clean_sql, re.IGNORECASE)
print(f"\nInnoDB Engines specified: {len(engine_matches)}/12")
print(f"utf8mb4 Charsets specified: {len(charset_matches)}/12")

if len(engine_matches) == 12 and len(charset_matches) == 12:
    print("PASSED: All 12 tables explicitly use InnoDB and utf8mb4.")
