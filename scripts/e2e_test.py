#!/usr/bin/env python3
import re
import subprocess
import sys
import time
import urllib.parse
import urllib.request
from http.cookiejar import CookieJar

BASE_URL = "http://localhost:8782"
PASSWORD = "Qweqwe123!"
NEW_PASSWORD = "Qweqwe123!New"


def fail(message: str) -> None:
    print(f"[E2E][FAIL] {message}")
    sys.exit(1)


def ok(message: str) -> None:
    print(f"[E2E][OK] {message}")


def extract_csrf(html: str) -> str:
    match = re.search(r'name="_token"\s+value="([^"]+)"', html)
    if not match:
        fail("CSRF token not found in HTML")
    return match.group(1)


def get(url: str, opener: urllib.request.OpenerDirector) -> tuple[str, str]:
    with opener.open(url) as response:
        body = response.read().decode("utf-8", errors="ignore")
        return body, response.geturl()


def post(url: str, data: dict[str, str], opener: urllib.request.OpenerDirector) -> tuple[str, str]:
    payload = urllib.parse.urlencode(data).encode("utf-8")
    request = urllib.request.Request(url, data=payload, method="POST")
    with opener.open(request) as response:
        body = response.read().decode("utf-8", errors="ignore")
        return body, response.geturl()


def artisan_link(command: str, email: str) -> str:
    cmd = f'docker compose exec -T app php artisan {command} "{email}"'
    result = subprocess.run(["bash", "-lc", cmd], capture_output=True, text=True)
    if result.returncode != 0:
        fail(f"{command} failed: {result.stderr.strip() or result.stdout.strip()}")

    url_match = re.search(r"https?://[^\s]+", result.stdout)
    if not url_match:
        fail(f"{command} did not output URL")
    return url_match.group(0)


def normalize_to_base(url: str) -> str:
    base = urllib.parse.urlparse(BASE_URL)
    parsed = urllib.parse.urlparse(url)
    return urllib.parse.urlunparse(
        (base.scheme, base.netloc, parsed.path, parsed.params, parsed.query, parsed.fragment)
    )


def run_command(command: str) -> None:
    result = subprocess.run(["bash", "-lc", command], capture_output=True, text=True)
    if result.returncode != 0:
        fail(f"Command failed: {command}\n{result.stderr.strip() or result.stdout.strip()}")


def main() -> None:
    email = f"e2e_{int(time.time())}@example.com"

    run_command('docker compose exec -T app php artisan migrate --force')
    run_command('docker compose exec -T app php artisan db:seed --force')
    ok("database prepared")

    cookie_jar = CookieJar()
    opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cookie_jar))

    body, _ = get(f"{BASE_URL}/register", opener)
    csrf = extract_csrf(body)
    _, url_after_register = post(
        f"{BASE_URL}/register",
        {
            "_token": csrf,
            "name": "E2E User",
            "email": email,
            "password": PASSWORD,
            "password_confirmation": PASSWORD,
        },
        opener,
    )
    if "/dashboard" not in url_after_register and "/verify-email" not in url_after_register:
        fail(f"Unexpected redirect after register: {url_after_register}")
    run_command(
        f'docker compose exec -T app php artisan tinker --execute="'
        f'echo \\\\App\\\\Models\\\\User::where(\'email\',\'{email}\')->count();" | grep -q "^1$"'
    )
    ok("register")

    verify_url = normalize_to_base(artisan_link("verify:link", email))
    _, verified_url = get(verify_url, opener)
    if "/dashboard" not in verified_url:
        fail(f"Email verification did not end on dashboard: {verified_url}")
    ok("email verification")

    body, _ = get(f"{BASE_URL}/dashboard", opener)
    csrf = extract_csrf(body)
    _, logout_url = post(f"{BASE_URL}/logout", {"_token": csrf}, opener)
    if not logout_url.endswith("/"):
        fail(f"Unexpected redirect after logout: {logout_url}")
    ok("logout")

    body, _ = get(f"{BASE_URL}/login", opener)
    csrf = extract_csrf(body)
    _, login_url = post(
        f"{BASE_URL}/login",
        {"_token": csrf, "email": email, "password": PASSWORD},
        opener,
    )
    if "/dashboard" not in login_url:
        fail(f"Unexpected redirect after login: {login_url}")
    ok("login")

    body, _ = get(f"{BASE_URL}/dashboard", opener)
    csrf = extract_csrf(body)
    post(f"{BASE_URL}/logout", {"_token": csrf}, opener)
    ok("logout before password reset")

    body, _ = get(f"{BASE_URL}/forgot-password", opener)
    csrf = extract_csrf(body)
    post(
        f"{BASE_URL}/forgot-password",
        {"_token": csrf, "email": email},
        opener,
    )
    ok("password reset request")

    reset_url = normalize_to_base(artisan_link("password:link", email))
    token_match = re.search(r"/reset-password/([^?]+)", reset_url)
    if not token_match:
        fail("Password reset URL token not found")
    token = token_match.group(1)

    body, _ = get(reset_url, opener)
    csrf = extract_csrf(body)
    _, reset_done_url = post(
        f"{BASE_URL}/reset-password",
        {
            "_token": csrf,
            "token": token,
            "email": email,
            "password": NEW_PASSWORD,
            "password_confirmation": NEW_PASSWORD,
        },
        opener,
    )
    if "/login" not in reset_done_url and "/dashboard" not in reset_done_url:
        fail(f"Unexpected redirect after password reset: {reset_done_url}")
    ok("password reset")

    body, _ = get(f"{BASE_URL}/dashboard", opener)
    csrf = extract_csrf(body)
    post(f"{BASE_URL}/logout", {"_token": csrf}, opener)

    body, _ = get(f"{BASE_URL}/login", opener)
    csrf = extract_csrf(body)
    _, login_url = post(
        f"{BASE_URL}/login",
        {"_token": csrf, "email": email, "password": NEW_PASSWORD},
        opener,
    )
    if "/dashboard" not in login_url:
        fail(f"Login with new password failed: {login_url}")
    ok("login with new password")

    body, _ = get(f"{BASE_URL}/dashboard", opener)
    csrf = extract_csrf(body)
    post(f"{BASE_URL}/logout", {"_token": csrf}, opener)
    ok("final logout")

    print("[E2E][PASS] Full auth flow passed")


if __name__ == "__main__":
    main()
