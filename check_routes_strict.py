import json
import subprocess
import re
import os

with open("/Users/darrielmarkerizal/Works/Levl/Levl-BE/admin_routes_list.json") as f:
    routes = json.load(f)

fe_dir = "/Users/darrielmarkerizal/Works/Levl/Levl-FE"
unused_routes = []

for r in routes:
    uri = r["uri"]
    uri = uri.replace("api/v1/", "")
    
    parts = [p for p in uri.split("/") if not p.startswith("{")]
    
    if not parts:
        continue
        
    # Let's search for the last part if it's longer than 3 chars, else the last two parts
    # But to be safe, let's just search the hooks/api directory for the joined last two parts.
    if len(parts) >= 2:
        search_term = f"{parts[-2]}.*{parts[-1]}"
    else:
        search_term = parts[-1]
        
    try:
        # Check if the term exists in hooks/api using ripgrep
        result = subprocess.run(["rg", "-q", "-e", search_term, "hooks/api", "lib/constants"], cwd=fe_dir)
        if result.returncode != 0:
            # If not found, let's try just the last part if we used two parts, because sometimes it's just `/${id}/publish`
            if len(parts) >= 2:
                last_part = parts[-1]
                result2 = subprocess.run(["rg", "-q", "-e", last_part, "hooks/api", "lib/constants"], cwd=fe_dir)
                if result2.returncode != 0:
                    unused_routes.append(r)
            else:
                unused_routes.append(r)
    except Exception as e:
        print(f"Error checking {uri}: {e}")

print(f"Found {len(unused_routes)} potentially unused routes:")
for r in unused_routes:
    print(f"{r['method']} api/v1/{r['uri']}")
