import os
import zipfile
import time

SOURCE_DIR = r"C:\xampp_new\htdocs\RAG_CHATBOT"
OUTPUT_ZIP = r"C:\xampp_new\htdocs\RAG_CHATBOT_SIAP_PAKAI.zip"

EXCLUDED_DIRS = {
    ".git",
    ".tools",
    "__pycache__",
    "node_modules",
    "playwright-report",
    "scratch",
    "test-results",
    "tests",
    ".fleet",
    ".idea",
    ".vscode",
}

EXCLUDED_FILES = {
    ".env",
    ".env.testing",
    ".phpunit.result.cache",
    "create_client_zip.py",
    "database.sqlite",
    "phpunit.xml",
    "phpunit.mysql.xml",
    "playwright.config.mjs",
    "PRD_Sistem_Modul_TKJ_RAG_Chatbot (1).md",
}

EXCLUDED_FILE_EXTENSIONS = {
    ".dump",
    ".zip",
}

EXCLUDED_REL_PATHS = {
    "public/storage",
    "database/database.sqlite",
}


def should_skip_dir(rel_dir):
    norm = rel_dir.replace("\\", "/").strip("/")
    if norm in EXCLUDED_REL_PATHS or any(norm.startswith(p + "/") for p in EXCLUDED_REL_PATHS):
        return True
    parts = norm.split("/") if norm else []
    return any(part in EXCLUDED_DIRS for part in parts)


def should_skip_file(rel_path):
    norm = rel_path.replace("\\", "/")
    parts = norm.split("/")

    if norm in EXCLUDED_REL_PATHS or any(norm.startswith(p + "/") for p in EXCLUDED_REL_PATHS):
        return True

    # Check if inside excluded dirs
    for part in parts[:-1]:
        if part in EXCLUDED_DIRS:
            return True

    if parts[-1] in EXCLUDED_FILES:
        return True

    # Check cache/session/view junk in storage
    if "storage/framework/cache/data" in norm:
        return True
    if "storage/framework/sessions" in norm and not parts[-1].startswith("."):
        return True
    if "storage/framework/views" in norm and not parts[-1].startswith("."):
        return True
    if "storage/logs" in norm and parts[-1].endswith(".log"):
        return True

    # Check file extensions
    ext = os.path.splitext(parts[-1])[1].lower()
    if ext in EXCLUDED_FILE_EXTENSIONS:
        return True

    return False


def make_zip():
    print(f"Memulai pembuatan ZIP dari: {SOURCE_DIR}")
    print(f"Target file: {OUTPUT_ZIP}")
    start_time = time.time()

    file_count = 0
    total_bytes = 0

    with zipfile.ZipFile(OUTPUT_ZIP, 'w', zipfile.ZIP_DEFLATED, compresslevel=6) as zf:
        # Ensure essential empty directories exist for client startup
        empty_dirs = [
            "storage/framework/cache/data",
            "storage/framework/sessions",
            "storage/framework/views",
            "storage/logs",
            "storage/app/public",
            "storage/app/private/modules",
        ]
        for ed in empty_dirs:
            archive_dir = f"RAG_CHATBOT/{ed}/"
            zf.writestr(archive_dir, "")

        for root, dirs, files in os.walk(SOURCE_DIR, followlinks=False):
            rel_root = os.path.relpath(root, SOURCE_DIR)
            if rel_root == ".":
                rel_root = ""

            # Prune excluded directories and Windows junctions (e.g. public/storage)
            kept_dirs = []
            for d in dirs:
                rel_dir = os.path.join(rel_root, d) if rel_root else d
                abs_dir = os.path.join(root, d)
                if os.path.islink(abs_dir) or should_skip_dir(rel_dir):
                    continue
                kept_dirs.append(d)
            dirs[:] = kept_dirs

            for file in files:
                abs_path = os.path.join(root, file)
                rel_path = os.path.relpath(abs_path, SOURCE_DIR)

                if should_skip_file(rel_path):
                    continue

                arcname = os.path.join("RAG_CHATBOT", rel_path)
                zf.write(abs_path, arcname)
                file_count += 1
                total_bytes += os.path.getsize(abs_path)

    elapsed = time.time() - start_time
    zip_size_mb = os.path.getsize(OUTPUT_ZIP) / (1024 * 1024)
    raw_size_mb = total_bytes / (1024 * 1024)

    print(f"\n[SUKSES] ZIP Berhasil Dibuat!")
    print(f"- Total file dikemas: {file_count}")
    print(f"- Ukuran asli: {raw_size_mb:.2f} MB")
    print(f"- Ukuran file ZIP: {zip_size_mb:.2f} MB")
    print(f"- Waktu proses: {elapsed:.2f} detik")
    print(f"- Lokasi: {OUTPUT_ZIP}")


if __name__ == "__main__":
    make_zip()
