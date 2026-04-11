#!/usr/bin/env python3
"""
generate_tests.py
=================
Baca output dari `php artisan blackbox:generate` lalu hasilkan:
  1. postman_collection.json   → import langsung ke Postman
  2. test_blackbox.py          → jalankan dengan pytest

Penggunaan:
    python3 generate_tests.py blackbox_tests.json --base-url http://localhost:8000 --login-url api/auth/login --email admin@example.com --password Password123!
"""

import json
import sys
import uuid
import argparse
import re
from pathlib import Path
from datetime import datetime

# ─────────────────────────────────────────────
# CLI Arguments
# ─────────────────────────────────────────────
parser = argparse.ArgumentParser(description="Generate Postman Collection + Pytest dari Laravel routes")
parser.add_argument("input",        help="File JSON dari php artisan blackbox:generate")
parser.add_argument("--base-url",   default="http://localhost:8000", help="Base URL Laravel (default: http://localhost:8000)")
parser.add_argument("--login-url",  default="api/auth/login",        help="Endpoint login JWT (default: api/auth/login)")
parser.add_argument("--email",      default="admin@example.com",     help="Email untuk auto-login")
parser.add_argument("--password",   default="Password123!",          help="Password untuk auto-login")
parser.add_argument("--out-postman",default="postman_collection.json",help="Output file Postman Collection")
parser.add_argument("--out-pytest", default="test_blackbox.py",       help="Output file Pytest")
args = parser.parse_args()

# ─────────────────────────────────────────────
# Load data
# ─────────────────────────────────────────────
try:
    with open(args.input, encoding="utf-8") as f:
        routes = json.load(f)
except FileNotFoundError:
    print(f"❌ File tidak ditemukan: {args.input}")
    sys.exit(1)

BASE_URL   = args.base_url.rstrip("/")
LOGIN_URL  = f"{BASE_URL}/{args.login_url.lstrip('/')}"
COLLECTION_NAME = f"Propertio Black Box Tests – {datetime.now().strftime('%Y-%m-%d')}"

# ─────────────────────────────────────────────────────────────────────────────
# BAGIAN 1: POSTMAN COLLECTION GENERATOR
# ─────────────────────────────────────────────────────────────────────────────

def make_postman_request(name, method, url_raw, headers, body_raw, tests_script=""):
    """Buat satu Postman request item."""
    # Parse URL jadi komponen
    url_path   = url_raw.replace(BASE_URL, "").lstrip("/")
    path_parts = url_path.split("/")

    item = {
        "name": name,
        "request": {
            "method": method.upper(),
            "header": headers,
            "url": {
                "raw":      url_raw,
                "protocol": "http" if "http://" in url_raw else "https",
                "host":     [BASE_URL.split("://")[1].split("/")[0]],
                "path":     path_parts,
            },
        },
        "response": [],
    }

    # Body hanya untuk POST/PUT/PATCH
    if method.upper() in ("POST", "PUT", "PATCH") and body_raw:
        item["request"]["body"] = {
            "mode": "raw",
            "raw":  json.dumps(body_raw, indent=2, ensure_ascii=False),
            "options": {"raw": {"language": "json"}},
        }

    # Test script Postman
    if tests_script:
        item["event"] = [{
            "listen": "test",
            "script": {
                "exec": tests_script.split("\n"),
                "type": "text/javascript",
            }
        }]

    return item


def build_postman_url(base: str, uri: str, path_params: list) -> str:
    """Ganti {param} dengan contoh nilai."""
    url = f"{base}/{uri.lstrip('/')}"
    for p in path_params:
        url = url.replace("{" + p + "}", "1")  # default ID = 1
    return url


def generate_postman(routes: list) -> dict:
    """Build Postman Collection JSON lengkap."""

    # ── Pre-request script global: auto-login & simpan token ──────────────
    pre_request_global = """
// Auto-login jika token belum ada
if (!pm.collectionVariables.get("jwt_token")) {
    pm.sendRequest({
        url: '""" + LOGIN_URL + """',
        method: 'POST',
        header: {'Content-Type': 'application/json'},
        body: {
            mode: 'raw',
            raw: JSON.stringify({
                email: '""" + args.email + """',
                password: '""" + args.password + """'
            })
        }
    }, function (err, res) {
        if (!err) {
            var body = res.json();
            // Coba berbagai struktur response JWT yang umum
            var token = body.token || body.access_token ||
                        (body.data && body.data.token) ||
                        (body.data && body.data.access_token) || '';
            pm.collectionVariables.set("jwt_token", token);
        }
    });
}
""".strip()

    # ── Group routes by module (first path segment after 'api/') ──────────
    modules = {}
    for r in routes:
        uri_parts = r["uri"].lstrip("api/").split("/")
        module    = uri_parts[0].capitalize() if uri_parts else "General"
        modules.setdefault(module, []).append(r)

    folders = []
    for module, module_routes in modules.items():
        items = []

        for r in module_routes:
            method      = r["method"]
            uri         = r["uri"]
            path_params = r.get("path_params", [])
            valid_body  = r.get("valid_body", {})
            invalid_cases = r.get("invalid_cases", [])
            requires_auth = r.get("requires_auth", False)
            url         = build_postman_url(BASE_URL, uri, path_params)

            base_headers = [{"key": "Content-Type", "value": "application/json"}]
            if requires_auth:
                base_headers.append({
                    "key":   "Authorization",
                    "value": "Bearer {{jwt_token}}",
                    "type":  "text",
                })

            # ── Skenario POSITIF ─────────────────────────────
            positive_test = """
pm.test("Status code sukses (2xx)", function () {
    pm.expect(pm.response.code).to.be.oneOf([200, 201, 204]);
});
pm.test("Response adalah JSON", function () {
    pm.response.to.be.json;
});
pm.test("Response time < 3000ms", function () {
    pm.expect(pm.response.responseTime).to.be.below(3000);
});
""".strip()

            items.append(make_postman_request(
                name         = f"✅ [{method}] {uri} – Positif (data valid)",
                method       = method,
                url_raw      = url,
                headers      = base_headers,
                body_raw     = valid_body if method in ("POST", "PUT", "PATCH") else None,
                tests_script = positive_test,
            ))

            # ── Skenario NEGATIF ─────────────────────────────
            for i, case in enumerate(invalid_cases):
                neg_test = f"""
pm.test("Status 422 – validasi gagal", function () {{
    pm.expect(pm.response.code).to.equal(422);
}});
pm.test("Response mengandung pesan error", function () {{
    var body = pm.response.json();
    pm.expect(body).to.have.any.keys(['errors', 'message', 'error']);
}});
""".strip()

                items.append(make_postman_request(
                    name         = f"❌ [{method}] {uri} – Negatif #{i+1}: {case['scenario']}",
                    method       = method,
                    url_raw      = url,
                    headers      = base_headers,
                    body_raw     = case.get("body", {}),
                    tests_script = neg_test,
                ))

            # ── Unauthenticated test (jika route perlu auth) ──
            if requires_auth:
                unauth_test = """
pm.test("Status 401 tanpa token", function () {
    pm.expect(pm.response.code).to.equal(401);
});
""".strip()
                items.append(make_postman_request(
                    name         = f"🔒 [{method}] {uri} – Tanpa Auth (harus 401)",
                    method       = method,
                    url_raw      = url,
                    headers      = [{"key": "Content-Type", "value": "application/json"}],
                    body_raw     = valid_body if method in ("POST", "PUT", "PATCH") else None,
                    tests_script = unauth_test,
                ))

        folders.append({
            "name":  module,
            "item":  items,
        })

    return {
        "info": {
            "_postman_id": str(uuid.uuid4()),
            "name":        COLLECTION_NAME,
            "description": f"Auto-generated black box tests – {datetime.now().isoformat()}",
            "schema":      "https://schema.getpostman.com/json/collection/v2.1.0/collection.json",
        },
        "event": [{
            "listen": "prerequest",
            "script": {
                "exec": pre_request_global.split("\n"),
                "type": "text/javascript",
            }
        }],
        "variable": [
            {"key": "base_url",   "value": BASE_URL},
            {"key": "jwt_token",  "value": ""},
        ],
        "item": folders,
    }


# ─────────────────────────────────────────────────────────────────────────────
# BAGIAN 2: PYTEST GENERATOR
# ─────────────────────────────────────────────────────────────────────────────

def sanitize_class_name(uri: str, method: str) -> str:
    """Buat nama class Python yang valid dari URI + method."""
    clean = re.sub(r"[^a-zA-Z0-9]", "_", uri)
    clean = re.sub(r"_+", "_", clean).strip("_")
    return f"Test{method.capitalize()}_{clean[:60]}"


def build_url_python(uri: str, path_params: list) -> str:
    """Buat ekspresi URL Python dengan path param sebagai format string."""
    url = f"{BASE_URL}/{uri.lstrip('/')}"
    for p in path_params:
        url = url.replace("{" + p + "}", "{self.sample_id}")
    return f'f"{url}"'


def generate_pytest(routes: list) -> str:
    """Build file pytest lengkap."""
    lines = [
        '"""',
        f'Auto-generated Black Box Tests',
        f'Generated: {datetime.now().isoformat()}',
        f'Total endpoints: {len(routes)}',
        '',
        'Cara menjalankan:',
        '    pip install pytest requests',
        f'    pytest {args.out_pytest} -v',
        '    pytest {args.out_pytest} -v --tb=short -x   # stop on first failure',
        '    pytest {args.out_pytest} -v -k "positif"    # hanya skenario positif',
        '    pytest {args.out_pytest} -v -k "negatif"    # hanya skenario negatif',
        '"""',
        '',
        'import pytest',
        'import requests',
        '',
        '# ─────────────────────────────────────────────',
        '# Konfigurasi',
        '# ─────────────────────────────────────────────',
        f'BASE_URL   = "{BASE_URL}"',
        f'LOGIN_URL  = "{LOGIN_URL}"',
        f'LOGIN_EMAIL    = "{args.email}"',
        f'LOGIN_PASSWORD = "{args.password}"',
        '',
        '',
        '# ─────────────────────────────────────────────',
        '# Fixture: JWT Token (sekali login untuk semua test)',
        '# ─────────────────────────────────────────────',
        '@pytest.fixture(scope="session")',
        'def jwt_token():',
        '    """Login sekali, token dipakai seluruh session test."""',
        '    res = requests.post(LOGIN_URL, json={',
        '        "email": LOGIN_EMAIL,',
        '        "password": LOGIN_PASSWORD,',
        '    })',
        '    body = res.json()',
        '    # Coba berbagai struktur response JWT',
        '    token = (',
        '        body.get("token") or',
        '        body.get("access_token") or',
        '        (body.get("data") or {}).get("token") or',
        '        (body.get("data") or {}).get("access_token") or',
        '        ""',
        '    )',
        '    assert token, f"Login gagal! Response: {body}"',
        '    return token',
        '',
        '',
        '@pytest.fixture(scope="session")',
        'def auth_headers(jwt_token):',
        '    return {',
        '        "Authorization": f"Bearer {jwt_token}",',
        '        "Content-Type": "application/json",',
        '    }',
        '',
        '',
    ]

    # Generate test class per route
    used_class_names = {}
    for r in routes:
        method       = r["method"]
        uri          = r["uri"]
        path_params  = r.get("path_params", [])
        valid_body   = r.get("valid_body", {})
        invalid_cases = r.get("invalid_cases", [])
        requires_auth = r.get("requires_auth", False)
        controller   = r.get("controller", "")

        # Unique class name
        base_name = sanitize_class_name(uri, method)
        if base_name in used_class_names:
            used_class_names[base_name] += 1
            class_name = f"{base_name}_{used_class_names[base_name]}"
        else:
            used_class_names[base_name] = 1
            class_name = base_name

        url_expr = build_url_python(uri, path_params)

        lines += [
            f'# {"─"*70}',
            f'# {method} /{uri}',
            f'# Controller: {controller}',
            f'# Requires Auth: {requires_auth}',
            f'# {"─"*70}',
            f'class {class_name}:',
            f'    """Black box tests untuk [{method}] /{uri}"""',
            f'    sample_id = 1  # Ganti dengan ID yang valid di database kamu',
            f'    url = {url_expr}',
            '',
        ]

        # ── Skenario Positif ──────────────────────────────────────────
        lines.append('    def test_positif_data_valid(self' + (', auth_headers' if requires_auth else '') + '):')
        lines.append(f'        """✅ Skenario positif: [{method}] /{uri} dengan data valid"""')

        if method in ("POST", "PUT", "PATCH"):
            lines.append(f'        payload = {json.dumps(valid_body, indent=8, ensure_ascii=False)}')
            if requires_auth:
                lines.append(f'        res = requests.{method.lower()}(self.url, json=payload, headers=auth_headers)')
            else:
                lines.append(f'        res = requests.{method.lower()}(self.url, json=payload)')
        elif method == "DELETE":
            if requires_auth:
                lines.append(f'        res = requests.delete(self.url, headers=auth_headers)')
            else:
                lines.append(f'        res = requests.delete(self.url)')
        else:  # GET
            if requires_auth:
                lines.append(f'        res = requests.get(self.url, headers=auth_headers)')
            else:
                lines.append(f'        res = requests.get(self.url)')

        lines += [
            '        assert res.status_code in (200, 201, 204), \\',
            f'            f"Expected 2xx, got {{res.status_code}}: {{res.text[:200]}}"',
            '',
        ]

        # ── Test 401 Unauthorized ─────────────────────────────────────
        if requires_auth:
            lines.append('    def test_negatif_tanpa_auth(self):')
            lines.append(f'        """🔒 Tanpa token harus 401 untuk [{method}] /{uri}"""')
            if method in ("POST", "PUT", "PATCH"):
                lines.append(f'        payload = {json.dumps(valid_body, ensure_ascii=False)}')
                lines.append(f'        res = requests.{method.lower()}(self.url, json=payload)')
            else:
                lines.append(f'        res = requests.{method.lower()}(self.url)')
            lines += [
                '        assert res.status_code == 401, \\',
                f'            f"Expected 401, got {{res.status_code}}"',
                '',
            ]

        # ── Skenario Negatif (per invalid case) ──────────────────────
        for i, case in enumerate(invalid_cases):
            scenario_slug = re.sub(r"[^a-zA-Z0-9]", "_", case["scenario"])[:50].strip("_")
            lines.append(f'    def test_negatif_{i+1}_{scenario_slug}(self' + (', auth_headers' if requires_auth else '') + '):')
            lines.append(f'        """❌ {case["scenario"]}"""')

            body = case.get("body", {})
            lines.append(f'        payload = {json.dumps(body, indent=8, ensure_ascii=False)}')

            if requires_auth:
                lines.append(f'        res = requests.{method.lower()}(self.url, json=payload, headers=auth_headers)')
            else:
                lines.append(f'        res = requests.{method.lower()}(self.url, json=payload)')

            expected_status = case.get("expected_status", 422)
            lines += [
                f'        assert res.status_code == {expected_status}, \\',
                f'            f"Expected {expected_status}, got {{res.status_code}}: {{res.text[:200]}}"',
                '',
            ]

        lines.append('')  # Spacing between classes

    # ── Summary footer ────────────────────────────────────────────────
    total_positive  = len(routes)
    total_negative  = sum(len(r.get("invalid_cases", [])) + (1 if r.get("requires_auth") else 0) for r in routes)
    lines += [
        '',
        '# ─────────────────────────────────────────────',
        '# SUMMARY',
        '# ─────────────────────────────────────────────',
        f'# Total endpoints  : {len(routes)}',
        f'# Skenario positif : {total_positive}',
        f'# Skenario negatif : {total_negative}',
        f'# Total test cases : {total_positive + total_negative}',
        '# ─────────────────────────────────────────────',
    ]

    return "\n".join(lines)


# ─────────────────────────────────────────────
# Main
# ─────────────────────────────────────────────
if __name__ == "__main__":
    print(f"📂 Membaca: {args.input}")
    print(f"🌐 Base URL: {BASE_URL}")
    print(f"🔑 Login URL: {LOGIN_URL}")
    print(f"📊 Total routes ditemukan: {len(routes)}")

    # 1. Postman Collection
    print("\n⚙️  Generating Postman Collection...")
    postman = generate_postman(routes)
    with open(args.out_postman, "w", encoding="utf-8") as f:
        json.dump(postman, f, indent=2, ensure_ascii=False)

    total_items = sum(
        len(r.get("invalid_cases", [])) + 1 + (1 if r.get("requires_auth") else 0)
        for r in routes
    )
    print(f"   ✅ {args.out_postman} ({total_items} request items)")

    # 2. Pytest
    print("⚙️  Generating Pytest script...")
    pytest_code = generate_pytest(routes)
    with open(args.out_pytest, "w", encoding="utf-8") as f:
        f.write(pytest_code)

    total_pos = len(routes)
    total_neg = sum(len(r.get("invalid_cases", [])) + (1 if r.get("requires_auth") else 0) for r in routes)
    print(f"   ✅ {args.out_pytest} ({total_pos + total_neg} test cases)")

    print(f"""
╔══════════════════════════════════════════════════════╗
║           OUTPUT BERHASIL DIGENERATE! 🎉             ║
╠══════════════════════════════════════════════════════╣
║  📮 Postman : {args.out_postman:<38}║
║  🐍 Pytest  : {args.out_pytest:<38}║
╠══════════════════════════════════════════════════════╣
║  Langkah selanjutnya:                                ║
║                                                      ║
║  Postman:                                            ║
║  → Import {args.out_postman} ke Postman              ║
║  → Klik "Run Collection"                             ║
║                                                      ║
║  Pytest:                                             ║
║  → pip install pytest requests                       ║
║  → pytest {args.out_pytest} -v                       ║
╚══════════════════════════════════════════════════════╝
""")
